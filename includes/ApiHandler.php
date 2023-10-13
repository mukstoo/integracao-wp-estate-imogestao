<?php

namespace BestIntegracaoImogestao\Includes;

require_once __DIR__ . '/PostManager.php';

class ApiHandler
{
    private const API_URL_BASE = 'https://api.imocorretor.com.br/sites/v1/';
    private PostManager $postManager;
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = get_option('my_api_key');
        $this->postManager = new PostManager();
        add_action('wp_ajax_sync_data', [$this, 'syncData']);
        add_action('wp_ajax_analyze_data', [$this, 'analyzeData']);
        add_action('wp_ajax_create_posts', [$this, 'createPosts']);
        add_action('wp_ajax_delete_posts', [$this, 'deletePosts']);
        add_action('wp_ajax_update_posts', [$this, 'updatePosts']);
    }

    public function runScheduledTask(): array
    {
        $log = [];
        try {
            $this->analyzeDataCore();
            /* $log[] = */ $this->deletePostsCore();
            /* $log[] = */ $this->createPostsCore();
            /* $log[] = */ $this->updatePostsCore();
        } catch (\Exception $e) {
            $log[] = "Error: " . $e->getMessage();
        }
        return $log;
    }

    public function analyzeData(): void
    {
        try {
            $analysisResult = $this->analyzeDataCore();
            wp_send_json_success($analysisResult);
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    private function analyzeDataCore(): array
    {
        $siteData = $this->fetchAllEstateProperties();
        $apiData = $this->fetchApiData();

        if (is_wp_error($apiData)) {
            throw new \Exception($apiData->get_error_message());
        }

        // Initialize result arrays
        $postsToDelete = [];
        $postsToCreate = [];

        // Convert siteData to associative array for efficient look-up
        $siteDataAssoc = [];
        foreach ($siteData as $item) {
            $siteDataAssoc[$item['imovel_id']] = $item['wp_id'];
        }

        // Find posts to delete and create
        foreach ($siteDataAssoc as $imovel_id => $wp_id) {
            if (!isset($apiData[$imovel_id])) {
                $postsToDelete[$imovel_id] = $wp_id;
            }
        }
        foreach ($apiData as $id => $litoral) {
            if (!isset($siteDataAssoc[$id])) {
                $postsToCreate[$id] = $litoral;
            }
        }

        $diffResult = ['postsToDelete' => $postsToDelete, 'postsToCreate' => $postsToCreate];
        set_transient('analysis_result', $diffResult, HOUR_IN_SECONDS);

        // Include posts_to_update in the response.
        $postsToUpdate = get_option('posts_to_update', []);
        // Return the analysis result instead of sending JSON
        return ['postsToDelete' => $postsToDelete, 'postsToCreate' => $postsToCreate, 'postsToUpdate' => $postsToUpdate];
    }

    public function createPosts(): void
    {
        try {
            $progress = $this->createPostsCore(); // Changed variable name to $progress
            wp_send_json_success($progress);
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    private function createPostsCore(): array
    {


        $analysisResult = get_transient('analysis_result');

        if (!$analysisResult) {
            throw new \Exception('No analysis data found');
        }

        $postsToCreate = array_keys($analysisResult['postsToCreate']);
        $litorals = array_values($analysisResult['postsToCreate']);

        $processedPosts = 0;
        $numPostsToCreate = count($postsToCreate);
        /* $processedPosts = isset($_GET['processedPosts']) ? intval($_GET['processedPosts']) : 0;
        $numPostsToCreate = isset($_GET['numPostsToCreate']) ? intval($_GET['numPostsToCreate']) : $postsToCreate; */

        $nextPostsToCreate = array_slice($postsToCreate, $processedPosts, $numPostsToCreate);
        $nextLitorals = array_slice($litorals, $processedPosts, $numPostsToCreate);

        $messages = [];

        foreach ($nextPostsToCreate as $index => $imovel_id) {
            $litoral = $nextLitorals[$index];

            // Add to posts_to_update list
            $postsToUpdate = get_option('posts_to_update', []);
            $postsToUpdate[] = $imovel_id;
            update_option('posts_to_update', $postsToUpdate);

            $apiUrl = self::API_URL_BASE . 'imovel.json?api=' . $this->apiKey . '&id=' . $imovel_id;
            $response = wp_remote_get(esc_url_raw($apiUrl));

            if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                $postCreationResult = $this->postManager->create_new_post($data, $litoral);

                if ($postCreationResult['success']) {
                    // Remove from posts_to_update list
                    $postsToUpdate = array_diff($postsToUpdate, [$imovel_id]);
                    update_option('posts_to_update', $postsToUpdate);

                    $messages[] = "Post $imovel_id created";
                } else {
                    $messages[] = "Post $imovel_id added to the posts to update list. Error: " . $postCreationResult['error'];
                }

                $processedPosts++;
            }
            set_time_limit(300);
            gc_collect_cycles();
        }

        $progress = [
            'totalPosts' => count($postsToCreate),
            'processedPosts' => $processedPosts,
            'messages' => $messages,
        ];

        return $progress;
    }


    public function updatePosts(): void
    {
        try {
            $log = $this->updatePostsCore();
            wp_send_json_success($log);
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    private function updatePostsCore(): array
    {
        $processedPosts = 0;
        /* $processedPosts = isset($_GET['processedPosts']) ? intval($_GET['processedPosts']) : 0; */
        $postsToUpdate = get_option('posts_to_update', []);

        if (empty($postsToUpdate)) {
            throw new \Exception('No posts to update');
        }

        $messages = [];
        $imovel_id = $postsToUpdate[$processedPosts];

        $postsToUpdate = get_option('posts_to_update', []);

        if (!$postsToUpdate) {
            throw new \Exception('No posts to update');
        }

        $numPostsToUpdate = count($postsToUpdate);

        $nextPostsToUpdate = array_slice($postsToUpdate, $processedPosts, $numPostsToUpdate);

        foreach ($nextPostsToUpdate as $imovel_id) {
            $apiUrl = self::API_URL_BASE . 'imovel.json?api=' . $this->apiKey . '&id=' . $imovel_id;
            $response = wp_remote_get(esc_url_raw($apiUrl));

            if (!is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                $postUpdateResult = $this->postManager->update_existing_post($data); // Assuming this method exists

                if ($postUpdateResult['success']) {
                    // Remove from posts_to_update list
                    $postsToUpdate = array_diff($postsToUpdate, [$imovel_id]);
                    update_option('posts_to_update', $postsToUpdate);

                    $messages[] = "Post $imovel_id updated";
                } else {
                    $messages[] = "Failed to update post $imovel_id. Error: " . $postUpdateResult['error'];
                }

                $processedPosts++;
            }
            set_time_limit(300);
            gc_collect_cycles();
        }

        $progress = [
            'totalPosts' => count($postsToUpdate),
            'processedPosts' => $processedPosts,
            'messages' => $messages,
        ];

        return $progress;
    }

    public function deletePosts(): void
    {
        try {
            $log = $this->deletePostsCore();
            wp_send_json_success($log);
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    private function deletePostsCore(): string
    {
        $analysisResult = get_transient('analysis_result');
        if (!$analysisResult) {
            throw new \Exception('No analysis data found');
        }

        // Moved deletePosts logic here
        $postsToDelete = array_keys($analysisResult['postsToDelete']);
        $wpIds = array_values($analysisResult['postsToDelete']);
        foreach ($postsToDelete as $index => $imovel_id) {
            $wp_id = $wpIds[$index];
            wp_delete_post($wp_id, true);
        }
        // Return a log message instead of sending JSON
        return "Posts deleted: " . implode(", ", array_keys($postsToDelete));
    }

    public function fetchAllEstateProperties(): array
    {
        $args = [
            'post_type' => 'estate_property',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => 'imovel_id',
                    'compare' => 'EXISTS',
                ],
            ],
        ];

        $query = new \WP_Query($args);
        $siteData = [];

        if ($query->have_posts()) {
            foreach ($query->posts as $wp_id) {
                $imovel_id = get_post_meta($wp_id, 'imovel_id', true);
                if ($imovel_id) {
                    $siteData[] = ['imovel_id' => $imovel_id, 'wp_id' => $wp_id];
                }
            }
        }

        return $siteData;
    }

    public function fetchApiData(): array
    {
        $apiData = [];
        $page = 0;
        do {
            $apiUrl = self::API_URL_BASE . '/imoveis.json?api=' . $this->apiKey . ($page > 0 ? '&pagina=' . ($page * 24) : '');
            $response = wp_remote_get(esc_url_raw($apiUrl));

            if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
                throw new \Exception('Error: ' . $response->get_error_message());
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);

            if (!isset($data['imoveis']) || !is_array($data['imoveis'])) {
                throw new \Exception('Error: Invalid data received from API');
            }

            foreach ($data['imoveis'] as $item) {
                $apiData[$item['id']] = $item['litoral'] ?? '';
            }

            $page++;
        } while (count($data['imoveis']) === 24);

        return $apiData;
    }
}
