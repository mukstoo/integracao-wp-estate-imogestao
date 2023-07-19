<?php

/* class ImageManager
{
    public function upload_images($post_id, $fotos)
    {
        $errors = [];

        foreach ($fotos as $foto) {
            try {
                $this->insert_image($foto, $post_id);
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        return $errors;
    }

    public function get_post_attachments($post_id)
    {
        $attachments = get_attached_media('image', $post_id);

        if (is_wp_error($attachments)) {
            throw new Exception('Error: ' . $attachments->get_error_message());
        }

        return $attachments;
    }

    public function update_images($post_id, $fotos)
    {
        $attachments = $this->get_post_attachments($post_id);
        if ($attachments) {
            $this->check_images_correspond($fotos, $attachments, $post_id);
        }
    }

    public function check_images_correspond($fotos, $attachments, $post_id)
    {
        $foto_codigos = array_column($fotos, 'codigo');
        $attachment_codigos = array_map(function ($attachment) {
            return get_post_meta($attachment->ID, 'codigo', true);
        }, $attachments);

        $fotos_to_upload = array_diff($foto_codigos, $attachment_codigos);
        $attachments_to_delete = array_diff($attachment_codigos, $foto_codigos);

        foreach ($fotos as $foto) {
            if (in_array($foto['codigo'], $fotos_to_upload)) {
                $this->insert_image($foto, $post_id);
            }
        }

        foreach ($attachments as $attachment) {
            if (in_array(get_post_meta($attachment->ID, 'codigo', true), $attachments_to_delete)) {
                $this->delete_image_attachment($attachment);
            }
        }
    }

    public function insert_image($foto, $post_id)
    {
        $image_url = $foto['grande'];

        $image = media_sideload_image($image_url, $post_id, null, 'id');

        if (is_wp_error($image)) {
            throw new Exception('Error: ' . $image->get_error_message());
        }

        $attachment_id = $image;

        $result = add_post_meta($attachment_id, 'codigo', $foto['codigo'], true);

        if (is_wp_error($result)) {
            throw new Exception('Error: ' . $result->get_error_message());
        }
        return $attachment_id;
    }

    public function delete_image_attachment($attachment)
    {
        if (!is_object($attachment) || !isset($attachment->ID)) {
            throw new Exception('Invalid attachment');
        }

        $deleted = wp_delete_attachment($attachment->ID, true);

        if (is_wp_error($deleted)) {
            throw new Exception('Error: ' . $deleted->get_error_message());
        }
    }

    public function set_post_thumbnail($post_id, $codigo)
    {
        $attachments = $this->get_post_attachments($post_id);

        foreach ($attachments as $attachment) {
            if (get_post_meta($attachment->ID, 'codigo', true) == $codigo) {
                set_post_thumbnail($post_id, $attachment->ID);
                break;
            }
        }
    }
}
 */
class ImageManager
{
    const ERROR_MESSAGE_PREFIX = 'Error: ';

    public function uploadImages($postId, $photos)
    {
        $errors = [];

        foreach ($photos as $photo) {
            try {
                $this->insertImage($photo, $postId);
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        return $errors;
    }

    public function getPostAttachments($postId)
    {
        $attachments = get_attached_media('image', $postId);

        if (is_wp_error($attachments)) {
            throw new Exception(self::ERROR_MESSAGE_PREFIX . $attachments->get_error_message());
        }

        return $attachments;
    }

    public function updateImages($postId, $photos)
    {
        $attachments = $this->getPostAttachments($postId);
        if ($attachments) {
            $this->checkImagesCorrespond($photos, $attachments, $postId);
        }
    }

    public function checkImagesCorrespond($photos, $attachments, $postId)
    {
        $photoCodes = array_column($photos, 'codigo');
        $attachmentCodes = array_map(function ($attachment) {
            return get_post_meta($attachment->ID, 'codigo', true);
        }, $attachments);

        $photosToUpload = array_diff($photoCodes, $attachmentCodes);
        $attachmentsToDelete = array_diff($attachmentCodes, $photoCodes);

        foreach ($photos as $photo) {
            if (in_array($photo['codigo'], $photosToUpload)) {
                $this->insertImage($photo, $postId);
            }
        }

        foreach ($attachments as $attachment) {
            if (in_array(get_post_meta($attachment->ID, 'codigo', true), $attachmentsToDelete)) {
                $this->deleteImageAttachment($attachment);
            }
        }
    }

    public function insertImage($photo, $postId)
    {
        $imageUrl = $photo['grande'];

        $image = media_sideload_image($imageUrl, $postId, null, 'id');

        if (is_wp_error($image)) {
            throw new Exception(self::ERROR_MESSAGE_PREFIX . $image->get_error_message());
        }

        $attachmentId = $image;

        $result = add_post_meta($attachmentId, 'codigo', $photo['codigo'], true);

        if (is_wp_error($result)) {
            throw new Exception(self::ERROR_MESSAGE_PREFIX . $result->get_error_message());
        }
        return $attachmentId;
    }

    public function deleteImageAttachment($attachment)
    {
        if (!is_object($attachment) || !isset($attachment->ID)) {
            throw new Exception('Invalid attachment');
        }

        $deleted = wp_delete_attachment($attachment->ID, true);

        if (is_wp_error($deleted)) {
            throw new Exception(self::ERROR_MESSAGE_PREFIX . $deleted->get_error_message());
        }
    }

    public function setPostThumbnail($postId, $code)
    {
        $attachments = $this->getPostAttachments($postId);

        foreach ($attachments as $attachment) {
            if (get_post_meta($attachment->ID, 'codigo', true) == $code) {
                set_post_thumbnail($postId, $attachment->ID);
                break;
            }
        }
    }
}
