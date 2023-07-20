<?php
class ImageManager
{
    const ERROR_MESSAGE_PREFIX = 'Error: ';

    public function uploadImages($postId, $photos)
    {
        $errors = [];

        foreach ($photos as $photo) {
            try {
                $attachmentId = $this->insertImage($photo, $postId);
                error_log("Image uploaded: " . $photo['grande'] . " with attachment ID: " . $attachmentId); // Log the uploaded image
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
                error_log("Error uploading image: " . $e->getMessage()); // Log the error
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
                try {
                    $attachmentId = $this->insertImage($photo, $postId);
                    error_log("Image uploaded: " . $photo['grande'] . " with attachment ID: " . $attachmentId); // Log the uploaded image
                } catch (Exception $e) {
                    error_log("Error uploading image: " . $e->getMessage()); // Log the error
                }
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

        // Check if the image exists and is accessible
        $file_headers = @get_headers($imageUrl);
        if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
            throw new Exception(self::ERROR_MESSAGE_PREFIX . "Image not found or not accessible at URL: $imageUrl");
        }

        $image = media_sideload_image($imageUrl, $postId, null, 'id');

        if (is_wp_error($image)) {
            throw new Exception(self::ERROR_MESSAGE_PREFIX . "Failed to upload image from URL: $imageUrl. Error: " . $image->get_error_message());
        }

        $attachmentId = $image;

        $result = add_post_meta($attachmentId, 'codigo', $photo['codigo'], true);

        if (is_wp_error($result)) {
            throw new Exception(self::ERROR_MESSAGE_PREFIX . "Failed to add post meta for attachment ID: $attachmentId. Error: " . $result->get_error_message());
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
            throw new Exception(self::ERROR_MESSAGE_PREFIX . "Failed to delete attachment with ID: " . $attachment->ID . ". Error: " . $deleted->get_error_message());
        }
    }

    public function setPostThumbnail($postId, $photos)
    {
        $attachments = $this->getPostAttachments($postId);
        $photoCodes = array_column($photos, 'codigo');

        foreach ($photoCodes as $code) {
            foreach ($attachments as $attachment) {
                if (get_post_meta($attachment->ID, 'codigo', true) == $code) {
                    set_post_thumbnail($postId, $attachment->ID);
                    return;
                }
            }
        }

        // If no matching code is found, set the oldest image as the thumbnail
        if (!empty($attachments)) {
            set_post_thumbnail($postId, reset($attachments)->ID);
        }
    }

}