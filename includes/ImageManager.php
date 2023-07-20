<?php

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