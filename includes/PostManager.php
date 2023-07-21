<?php

require_once plugin_dir_path(__DIR__) . 'includes/ImageManager.php';

class PostManager
{
    const ASSOCIATIONS = [
        'property_category' => [['tipo'], false],
        'property_city' => [['localizacao', 'cidade'], false],
        'property_county_state' => [['localizacao', 'estado'], false],
        'property_area' => [['localizacao', 'bairro'], false],
        'property_action_category' => [['negocio'], true],
    ];

    const TERM_MAPPING = [
        'churrasqueira' => 'Churrasqueira',
        'mobiliado' => 'Mobiliado',
        'piscina' => 'Piscina',
        'sauna' => 'Sauna',
        'playground' => 'Playground',
        'fitness' => 'Área Fitness',
        'quadra_poliesportiva' => 'Quadra Poliesportiva',
        'area_festas' => 'Salão de Festas',
        'saladejogos' => 'Sala de Jogos',
        // Add more mappings as needed
    ];

    private $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager();
    }

    public function process_item($detailed_item, $litoral)
    {
        if (!isset($detailed_item['id'])) {
            throw new Exception('Invalid item data');
        }

        $existing_post = $this->get_existing_post($detailed_item['id']);

        if ($existing_post) {
            $post_id = $existing_post->ID;
            $this->update_post_if_necessary($post_id, $detailed_item, $litoral);
        } else {
            $post_id = $this->create_new_post($detailed_item, $litoral);
        }
    }

    private function get_existing_post($api_id)
    {
        if (empty($api_id)) {
            throw new Exception('Invalid API ID');
        }

        $existing_posts = get_posts(
            array(
                'meta_key' => 'imovel_id',
                'meta_value' => $api_id,
                'post_type' => 'estate_property',
                'post_status' => 'any',
                'numberposts' => 1
            )
        );

        if (is_wp_error($existing_posts)) {
            throw new Exception('Error: ' . $existing_posts->get_error_message());
        }

        return !empty($existing_posts) ? $existing_posts[0] : null;
    }

    private function create_new_post($detailed_item, $litoral)
    {
        $post_id = wp_insert_post(
            array(
                'post_title' => $detailed_item['tipo'] . ' - ' . $detailed_item['referencia'],
                'post_content' => isset($detailed_item['texto_longo']) ? $detailed_item['texto_longo'] : 'Descrição indisponível',
                'post_status' => 'publish',
                'post_type' => 'estate_property',
                'meta_input' => array(
                    'imovel_id' => $detailed_item['id'],
                    /* 'litoral' => $litoral */
                )
            )
        );

        if (is_wp_error($post_id)) {
            throw new Exception('Error: ' . $post_id->get_error_message());
        }

        $this->update_post_meta_data($post_id, $detailed_item);
        $this->assign_terms_to_post($post_id, $detailed_item, $litoral);
        $this->imageManager->uploadImages($post_id, $detailed_item['fotos']);
        $this->imageManager->setPostThumbnail($post_id, $detailed_item['fotos']);

        return $post_id;
    }

    private function update_post_if_necessary($post_id, $detailed_item, $litoral)
    {
        if (empty($post_id) || !is_array($detailed_item)) {
            throw new Exception('Invalid post ID or item data');
        }

        $post = get_post($post_id);
        if (!$post) {
            throw new Exception('Post not found');
        }

        $post_data = array(
            'ID' => $post_id,
            'post_title' => $detailed_item['tipo'] . ' - ' . $detailed_item['referencia'],
            'post_content' => isset($detailed_item['texto_longo']) ? $detailed_item['texto_longo'] : 'Descrição indisponível',
        );

        $updated = wp_update_post($post_data, true);
        if (is_wp_error($updated)) {
            throw new Exception('Error: ' . $updated->get_error_message());
        }

        $this->update_post_meta_data($post_id, $detailed_item);
        $this->assign_terms_to_post($post_id, $detailed_item, $litoral);
        $this->imageManager->updateImages($post_id, $detailed_item['fotos']);
        $this->imageManager->setPostThumbnail($post_id, $detailed_item['fotos']);
    }

    private function update_post_meta_data($post_id, $detailed_item)
    {
        $meta_data = $this->extract_imovel_meta_data($detailed_item);
        foreach ($meta_data as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
    }

    public function extract_imovel_meta_data($imovel_data)
    {
        $meta_data = array(
            'post_content' => $imovel_data['texto_longo'],
            'post_excerpt' => $imovel_data['texto_curto'],
            'property_state' => $imovel_data['localizacao']['estado'],
            'property_latitude' => $imovel_data['localizacao']['latitude'],
            'property_longitude' => $imovel_data['localizacao']['longitude'],
            'property_bedrooms' => $imovel_data['dormitorios'],
            'property_bathrooms' => $imovel_data['banheiros'],
            'property_rooms' => $imovel_data['salas'],
            'property_garage' => $imovel_data['garagens'],
            'property_size' => $imovel_data['area_total'],
            'property_lot_size' => $imovel_data['area_terreno'],
            'property_price' => $imovel_data['valores']['valor_venda'],
            'property_county' => $imovel_data['localizacao']['cidade'],
            'property_address' => $imovel_data['localizacao']['endereco'] . ' ' . $imovel_data['localizacao']['endereco_complemento'] . ' ' . $imovel_data['localizacao']['endereco_numero'],
            'hidden_address' => $imovel_data['localizacao']['endereco'] . ' ' . $imovel_data['localizacao']['endereco_complemento'] . ' ' . $imovel_data['localizacao']['endereco_numero'],
            'property_year_tax' => 0,
            'property_hoa' => 0,
            /* 'property_size' => 0, */
            'prop_featured' => 0,
            'property_theme_slider' => 0,
            'embed_video_type' => 'youtube',
            'structure-type' => 'Not Available',
            'stories-number' => 'Not Available',
            'page_custom_zoom' => 16,
            'google_camera_angle' => 0,
            'use_floor_plans' => 0,
            'post_show_title' => 'yes',
            'header_transparent' => 'global',
            'topbar_transparent' => 'global',
            'topbar_border_transparent' => 'global',
            'header_type' => 0,
            'min_height' => 0,
            'max_height' => 0,
            'page_header_image_full_screen' => 'no',
            'page_header_image_back_type' => 'cover',
            'page_header_video_full_screen' => 'no',
            'sidebar_agent_option' => 'global',
            'local_pgpr_slider_type' => 'global',
            'local_pgpr_content_type' => 'global',
        );

        return $meta_data;
    }

    /* public function assign_terms_to_post($post_id, $imovel_data, $litoral)
    {
        $errors = [];

        foreach (self::ASSOCIATIONS as $taxonomy => $data) {
            $keys = $data[0];
            $is_multiple = $data[1];

            $values = $imovel_data;
            foreach ($keys as $key) {
                if (!isset($values[$key])) {
                    continue 2;
                }
                $values = $values[$key];
            }

            if ($is_multiple) {
                $terms_to_set = [];
                foreach ($values as $term => $value) {
                    if ($value == 1) {
                        // Map the JSON key to the term name, if a mapping exists
                        if (isset(self::TERM_MAPPING[$term])) {
                            $term = self::TERM_MAPPING[$term];
                        }
                        if (!term_exists($term, $taxonomy)) {
                            wp_insert_term($term, $taxonomy);
                        }
                        $terms_to_set[] = $term;
                    }
                }
                $values = $terms_to_set;
            } else {
                if (!term_exists($values, $taxonomy)) {
                    wp_insert_term($values, $taxonomy);
                }
                $values = [$values];
            }

            $result = wp_set_object_terms($post_id, $values, $taxonomy, false);
            if (is_wp_error($result)) {
                $errors[] = "Failed to assign terms for post {$post_id} in taxonomy {$taxonomy}: " . $result->get_error_message();
            }
        }

        // Set property_status to "À Venda"
        $result = wp_set_object_terms($post_id, "À Venda", 'property_status', false);
        if (is_wp_error($result)) {
            $errors[] = "Failed to assign terms for post {$post_id} in taxonomy property_status: " . $result->get_error_message();
        }

        // Set litoral
        $result = wp_set_object_terms($post_id, $litoral ? '1' : '0', 'litoral', false);
        if (is_wp_error($result)) {
            $errors[] = "Failed to assign terms for post {$post_id} in taxonomy litoral: " . $result->get_error_message();
        }

        // Set property_features based on individual keys in imovel_data
        $features_to_set = [];
        foreach (self::TERM_MAPPING as $key => $term) {
            if (isset($imovel_data[$key]) && $imovel_data[$key] == 1) {
                if (!term_exists($term, 'property_features')) {
                    wp_insert_term($term, 'property_features');
                }
                $features_to_set[] = $term;
            }
        }
        $result = wp_set_object_terms($post_id, $features_to_set, 'property_features', false);
        if (is_wp_error($result)) {
            $errors[] = "Failed to assign terms for post {$post_id} in taxonomy property_features: " . $result->get_error_message();
        }
        return $errors;
    } */

    public function assign_terms_to_post($post_id, $imovel_data, $litoral)
    {
        $errors = [];
        $city = ''; // Variable to store the city

        foreach (self::ASSOCIATIONS as $taxonomy => $data) {
            $keys = $data[0];
            $is_multiple = $data[1];

            $values = $imovel_data;
            foreach ($keys as $key) {
                if (!isset($values[$key])) {
                    continue 2;
                }
                $values = $values[$key];
            }

            // If the taxonomy is 'property_city', store the city
            if ($taxonomy == 'property_city') {
                $city = $values;
            }

            if ($is_multiple) {
                $terms_to_set = [];
                foreach ($values as $term => $value) {
                    if ($value == 1) {
                        if (isset(self::TERM_MAPPING[$term])) {
                            $term = self::TERM_MAPPING[$term];
                        }
                        if (!term_exists($term, $taxonomy)) {
                            wp_insert_term($term, $taxonomy);
                        }
                        $terms_to_set[] = $term;
                    }
                }
                $values = $terms_to_set;
            } else {
                // For 'property_area', create a slug that includes the city
                if ($taxonomy == 'property_area') {
                    $slug = sanitize_title($values . '-' . $city);
                    $existing_term = get_term_by('slug', $slug, $taxonomy);

                    // If the term doesn't exist, create a new one
                    if (!$existing_term) {
                        $term_info = wp_insert_term($values, $taxonomy, ['slug' => $slug]);
                        error_log("Created new term: " . $values . "-" . $slug);

                        // If the taxonomy is 'property_area', add the term meta
                        if (!is_wp_error($term_info)) {
                            update_option("taxonomy_" . $term_info['term_id'], ['cityparent' => $city]);
                            error_log("Created new term: " . $term_info['term_id'] . "-" . $city);
                        }
                        // Update $values with the new slug
                        $values = [$slug];
                    } else {
                        // If the term does exist, also update $values with the existing slug
                        $values = [$existing_term->slug];
                    }
                } else {
                    // For other taxonomies, check existence by name and create if not exist
                    if (!term_exists($values, $taxonomy)) {
                        wp_insert_term($values, $taxonomy);
                    }
                    $values = [$values];
                }
            }

            // Assign terms to post
            $result = wp_set_object_terms($post_id, $values, $taxonomy, false);
            if (is_wp_error($result)) {
                $errors[] = "Failed to assign terms for post {$post_id} in taxonomy {$taxonomy}: " . $result->get_error_message();
            }
        }

        // Set property_status to "À Venda"
        $result = wp_set_object_terms($post_id, "À Venda", 'property_status', false);
        if (is_wp_error($result)) {
            $errors[] = "Failed to assign terms for post {$post_id} in taxonomy property_status: " . $result->get_error_message();
        }

        // Set litoral
        $result = wp_set_object_terms($post_id, $litoral ? '1' : '0', 'litoral', false);
        if (is_wp_error($result)) {
            $errors[] = "Failed to assign terms for post {$post_id} in taxonomy litoral: " . $result->get_error_message();
        }

        // Set property_features based on individual keys in imovel_data
        $features_to_set = [];
        foreach (self::TERM_MAPPING as $key => $term) {
            if (isset($imovel_data[$key]) && $imovel_data[$key] == 1) {
                if (!term_exists($term, 'property_features')) {
                    wp_insert_term($term, 'property_features');
                }
                $features_to_set[] = $term;
            }
        }
        $result = wp_set_object_terms($post_id, $features_to_set, 'property_features', false);
        if (is_wp_error($result)) {
            $errors[] = "Failed to assign terms for post {$post_id} in taxonomy property_features: " . $result->get_error_message();
        }

        return $errors;
    }




}