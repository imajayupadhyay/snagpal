<?php

declare(strict_types=1);

function upload_homepage_image(string $fieldName, string $currentPath, array &$errors): string
{
    if (empty($_FILES[$fieldName]) || ! is_array($_FILES[$fieldName])) {
        return $currentPath;
    }

    $file = $_FILES[$fieldName];

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $currentPath;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $errors[] = 'One of the image uploads failed. Please try again.';
        return $currentPath;
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');

    if ($tmpName === '' || ! is_uploaded_file($tmpName)) {
        $errors[] = 'Invalid image upload.';
        return $currentPath;
    }

    $maxBytes = 5 * 1024 * 1024;

    if ((int) ($file['size'] ?? 0) > $maxBytes) {
        $errors[] = 'Images must be 5 MB or smaller.';
        return $currentPath;
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmpName);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (! isset($extensions[$mime])) {
        $errors[] = 'Only JPG, PNG, and WebP images are allowed.';
        return $currentPath;
    }

    $directory = PUBLIC_PATH . '/uploads/homepage';

    if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
        $errors[] = 'Unable to create the homepage upload directory.';
        return $currentPath;
    }

    $filename = date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . $extensions[$mime];
    $target = $directory . '/' . $filename;

    if (! move_uploaded_file($tmpName, $target)) {
        $errors[] = 'Unable to store the uploaded image.';
        return $currentPath;
    }

    return 'uploads/homepage/' . $filename;
}

function upload_homepage_video(string $fieldName, string $currentPath, array &$errors): string
{
    if (empty($_FILES[$fieldName]) || ! is_array($_FILES[$fieldName])) {
        return $currentPath;
    }

    $file = $_FILES[$fieldName];

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $currentPath;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $errors[] = 'A video upload failed. Files larger than the server limit are rejected before they finish.';
        return $currentPath;
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');

    if ($tmpName === '' || ! is_uploaded_file($tmpName)) {
        $errors[] = 'Invalid video upload.';
        return $currentPath;
    }

    $maxBytes = 64 * 1024 * 1024;

    if ((int) ($file['size'] ?? 0) > $maxBytes) {
        $errors[] = 'Videos must be 64 MB or smaller. For larger files, paste a YouTube or Vimeo link instead.';
        return $currentPath;
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmpName);
    $extensions = [
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/ogg' => 'ogv',
        'video/quicktime' => 'mov',
    ];

    if (! isset($extensions[$mime])) {
        $errors[] = 'Only MP4, WebM, OGG, and MOV videos are allowed.';
        return $currentPath;
    }

    $directory = PUBLIC_PATH . '/uploads/homepage/videos';

    if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
        $errors[] = 'Unable to create the homepage video upload directory.';
        return $currentPath;
    }

    $filename = date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . $extensions[$mime];
    $target = $directory . '/' . $filename;

    if (! move_uploaded_file($tmpName, $target)) {
        $errors[] = 'Unable to store the uploaded video.';
        return $currentPath;
    }

    return 'uploads/homepage/videos/' . $filename;
}

function upload_cohort_poster(string $fieldName, string $currentPath, array &$errors): string
{
    if (empty($_FILES[$fieldName]) || ! is_array($_FILES[$fieldName])) {
        return $currentPath;
    }

    $file = $_FILES[$fieldName];

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $currentPath;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $errors[] = 'The poster upload failed. Please try again.';
        return $currentPath;
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');

    if ($tmpName === '' || ! is_uploaded_file($tmpName)) {
        $errors[] = 'Invalid poster upload.';
        return $currentPath;
    }

    if ((int) ($file['size'] ?? 0) > 5 * 1024 * 1024) {
        $errors[] = 'Poster images must be 5 MB or smaller.';
        return $currentPath;
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmpName);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (! isset($extensions[$mime])) {
        $errors[] = 'Only JPG, PNG, and WebP poster images are allowed.';
        return $currentPath;
    }

    $directory = PUBLIC_PATH . '/uploads/cohorts';

    if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
        $errors[] = 'Unable to create the cohort upload directory.';
        return $currentPath;
    }

    $filename = date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . $extensions[$mime];
    $target = $directory . '/' . $filename;

    if (! move_uploaded_file($tmpName, $target)) {
        $errors[] = 'Unable to store the uploaded poster.';
        return $currentPath;
    }

    return 'uploads/cohorts/' . $filename;
}

function upload_cohort_video(string $fieldName, string $currentPath, array &$errors): string
{
    if (empty($_FILES[$fieldName]) || ! is_array($_FILES[$fieldName])) {
        return $currentPath;
    }

    $file = $_FILES[$fieldName];

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $currentPath;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $errors[] = 'The cohort video upload failed. Files larger than the server limit are rejected before they finish.';
        return $currentPath;
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');

    if ($tmpName === '' || ! is_uploaded_file($tmpName)) {
        $errors[] = 'Invalid cohort video upload.';
        return $currentPath;
    }

    if ((int) ($file['size'] ?? 0) > 64 * 1024 * 1024) {
        $errors[] = 'Cohort videos must be 64 MB or smaller. For larger files, paste a YouTube or Vimeo link instead.';
        return $currentPath;
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmpName);
    $extensions = [
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/ogg' => 'ogv',
        'video/quicktime' => 'mov',
    ];

    if (! isset($extensions[$mime])) {
        $errors[] = 'Only MP4, WebM, OGG, and MOV cohort videos are allowed.';
        return $currentPath;
    }

    $directory = PUBLIC_PATH . '/uploads/cohorts/videos';

    if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
        $errors[] = 'Unable to create the cohort video upload directory.';
        return $currentPath;
    }

    $filename = date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . $extensions[$mime];
    $target = $directory . '/' . $filename;

    if (! move_uploaded_file($tmpName, $target)) {
        $errors[] = 'Unable to store the uploaded cohort video.';
        return $currentPath;
    }

    return 'uploads/cohorts/videos/' . $filename;
}

function upload_event_poster(string $fieldName, string $currentPath, array &$errors): string
{
    if (empty($_FILES[$fieldName]) || ! is_array($_FILES[$fieldName])) {
        return $currentPath;
    }

    $file = $_FILES[$fieldName];

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $currentPath;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $errors[] = 'The poster upload failed. Please try again.';
        return $currentPath;
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');

    if ($tmpName === '' || ! is_uploaded_file($tmpName)) {
        $errors[] = 'Invalid poster upload.';
        return $currentPath;
    }

    if ((int) ($file['size'] ?? 0) > 5 * 1024 * 1024) {
        $errors[] = 'Poster images must be 5 MB or smaller.';
        return $currentPath;
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmpName);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (! isset($extensions[$mime])) {
        $errors[] = 'Only JPG, PNG, and WebP poster images are allowed.';
        return $currentPath;
    }

    $directory = PUBLIC_PATH . '/uploads/events';

    if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
        $errors[] = 'Unable to create the event upload directory.';
        return $currentPath;
    }

    $filename = date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . $extensions[$mime];
    $target = $directory . '/' . $filename;

    if (! move_uploaded_file($tmpName, $target)) {
        $errors[] = 'Unable to store the uploaded poster.';
        return $currentPath;
    }

    return 'uploads/events/' . $filename;
}

function upload_event_video(string $fieldName, string $currentPath, array &$errors): string
{
    if (empty($_FILES[$fieldName]) || ! is_array($_FILES[$fieldName])) {
        return $currentPath;
    }

    $file = $_FILES[$fieldName];

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $currentPath;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $errors[] = 'The event video upload failed. Files larger than the server limit are rejected before they finish.';
        return $currentPath;
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');

    if ($tmpName === '' || ! is_uploaded_file($tmpName)) {
        $errors[] = 'Invalid event video upload.';
        return $currentPath;
    }

    if ((int) ($file['size'] ?? 0) > 64 * 1024 * 1024) {
        $errors[] = 'Event videos must be 64 MB or smaller. For larger files, paste a YouTube or Vimeo link instead.';
        return $currentPath;
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmpName);
    $extensions = [
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/ogg' => 'ogv',
        'video/quicktime' => 'mov',
    ];

    if (! isset($extensions[$mime])) {
        $errors[] = 'Only MP4, WebM, OGG, and MOV event videos are allowed.';
        return $currentPath;
    }

    $directory = PUBLIC_PATH . '/uploads/events/videos';

    if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
        $errors[] = 'Unable to create the event video upload directory.';
        return $currentPath;
    }

    $filename = date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . $extensions[$mime];
    $target = $directory . '/' . $filename;

    if (! move_uploaded_file($tmpName, $target)) {
        $errors[] = 'Unable to store the uploaded event video.';
        return $currentPath;
    }

    return 'uploads/events/videos/' . $filename;
}
