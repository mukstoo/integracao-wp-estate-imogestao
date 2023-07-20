<?php

require_once plugin_dir_path(__DIR__) . 'includes/PostManager.php';

class ApiHandler
{
    const API_URL = 'https://api.imocorretor.com.br/sites/v1/imoveis.json?api=';
    const MAX_LOOP_ITERATIONS = 400;

    private $postManager;
    private $apiKey;

    public function __construct()
    {
        $this->postManager = new PostManager();
    }

    public function fetch_data()
    {
        $this->apiKey = get_option('my_api_key'); // Retrieve the API key from the options here

        $numItemsFetched = 0;
        $page = isset($_GET['page']) ? absint($_GET['page']) : 0;
        $itemsProcessed = isset($_GET['items_processed']) ? absint($_GET['items_processed']) : 0;
        $itemsToProcess = isset($_GET['max_items']) ? absint($_GET['max_items']) : 0;
        $items = [];

        $loopCounter = 0;

        do {
            if ($loopCounter > self::MAX_LOOP_ITERATIONS) {
                wp_send_json_error('Error: Exceeded maximum loop iterations');
            }

            $apiUrl = self::API_URL . $this->apiKey . ($page > 0 ? '&pagina=' . ($page * 24) : ''); // Use the stored API key
            $response = wp_remote_get(esc_url_raw($apiUrl));

            if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
                wp_send_json_error('Error: ' . $response->get_error_message());
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);

            if (!isset($data['imoveis']) || !is_array($data['imoveis'])) {
                wp_send_json_error('Error: Invalid data received from API');
            }

            $itemIndex = $itemsProcessed % 24;

            for (; $itemIndex < count($data['imoveis']); $itemIndex++) {
                if (count($items) >= $itemsToProcess - $itemsProcessed) {
                    break;
                }

                $item = $this->fetch_and_process_item($data['imoveis'][$itemIndex]);
                if ($item !== null) {
                    $items[] = $item;
                    $numItemsFetched++;
                }
            }

            if ($itemIndex == 24) {
                $page++;
            }

            $loopCounter++;
        } while (count($items) < $itemsToProcess - $itemsProcessed && count($data['imoveis']) == 24);

        wp_send_json_success(['message' => sprintf('Finished fetching data. Total items fetched: %s', $numItemsFetched), 'numItemsFetched' => $numItemsFetched]);
    }

    private function fetch_and_process_item($item)
    {
        if (!isset($item['id'])) {
            error_log('Error: Invalid item data'); // Log the error
            return null;
        }

        $single_item_url = str_replace('imoveis.json', 'imovel.json', self::API_URL) . $this->apiKey . '&id=' . $item['id'];
        $response = wp_remote_get(esc_url_raw($single_item_url));
        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            error_log('Error: ' . $response->get_error_message()); // Log the error
            return null;
        }

        $detailed_item = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($detailed_item)) {
            return null;
        }

        $this->postManager->process_item($detailed_item, $item['litoral']);

        return $detailed_item;
    }

}