<?php

namespace BestIntegracaoImogestao\Includes;

class ImageManager
{
    const ERROR_MESSAGE_PREFIX = 'Error: ';

    public function uploadImages(int $postId, array $photos): array
    {
        $errors = [];
        $successfulUploads = 0;

        foreach ($photos as $photo) {
            try {
                $attachmentId = $this->insertImage($photo, $postId);
                error_log("Image uploaded: {$photo['grande']} with attachment ID: {$attachmentId}");
                $successfulUploads++;
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
                error_log("Error uploading image: {$e->getMessage()}");
            }
        }

        return ['errors' => $errors, 'successfulUploads' => $successfulUploads];
    }


    public function getPostAttachments(int $postId): array
    {
        $attachments = get_attached_media('image', $postId);

        if (is_wp_error($attachments)) {
            throw new Exception(self::ERROR_MESSAGE_PREFIX . $attachments->get_error_message());
        }

        return $attachments;
    }

    public function updateImages(int $postId, array $photos): void
    {
        $attachments = $this->getPostAttachments($postId);
        if ($attachments) {
            $this->checkImagesCorrespond($photos, $attachments, $postId);
        }
    }

    private function checkImagesCorrespond(array $photos, array $attachments, int $postId): void
    {
        $photoCodes = array_column($photos, 'codigo');
        $attachmentCodes = array_map(fn ($attachment) => get_post_meta($attachment->ID, 'codigo', true), $attachments);

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

    private function insertImage(array $photo, int $postId): int
    {
        $imageUrl = $photo['grande'];
        $file_headers = @get_headers($imageUrl);

        if (!$file_headers || $file_headers[0] === 'HTTP/1.1 404 Not Found') {
            throw new Exception(self::ERROR_MESSAGE_PREFIX . "Image not found or not accessible at URL: {$imageUrl}");
        }

        $image = media_sideload_image($imageUrl, $postId, null, 'id');

        if (is_wp_error($image)) {
            throw new Exception(self::ERROR_MESSAGE_PREFIX . "Failed to upload image from URL: {$imageUrl}. Error: {$image->get_error_message()}");
        }

        $attachmentId = $image;
        $result = add_post_meta($attachmentId, 'codigo', $photo['codigo'], true);

        if (is_wp_error($result)) {
            throw new Exception(self::ERROR_MESSAGE_PREFIX . "Failed to add post meta for attachment ID: {$attachmentId}. Error: {$result->get_error_message()}");
        }

        return $attachmentId;
    }

    private function deleteImageAttachment(object $attachment): void
    {
        if (!isset($attachment->ID)) {
            throw new Exception('Invalid attachment');
        }

        $deleted = wp_delete_attachment($attachment->ID, true);

        if (is_wp_error($deleted)) {
            throw new Exception(self::ERROR_MESSAGE_PREFIX . "Failed to delete attachment with ID: {$attachment->ID}. Error: {$deleted->get_error_message()}");
        }
    }

    public function setPostThumbnail(int $postId, array $photos): void
    {
        $attachments = $this->getPostAttachments($postId);
        $photoCodes = array_column($photos, 'codigo');

        foreach ($photoCodes as $code) {
            foreach ($attachments as $attachment) {
                if (get_post_meta($attachment->ID, 'codigo', true) === $code) {
                    set_post_thumbnail($postId, $attachment->ID);
                    return;
                }
            }
        }

        if (!empty($attachments)) {
            set_post_thumbnail($postId, reset($attachments)->ID);
        }
    }
}
