<?php
/**
 * Project: QazJumys
 * File: Upload.php
 * Author: Beck Sarbassov
 * Version: 1.2.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Validates and stores protected project upload files outside the public web root.
 * RU: Проверяет и сохраняет защищенные файлы проектов вне публичной директории.
 */

declare(strict_types=1);

namespace QazJumys\Core;

use RuntimeException;

final class Upload
{
    /**
     * EN: Validates and moves one uploaded file into storage/uploads.
     * RU: Проверяет и переносит один загруженный файл в storage/uploads.
     *
     * @param array<string, mixed> $file Uploaded file array / Массив загруженного файла
     * @param array<string, mixed> $config App config / Конфигурация приложения
     * @return array<string, mixed> Stored metadata / Сохраненные метаданные
     */
    public static function store(array $file, array $config): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Файл жүктелмеді немесе тым үлкен.');
        }

        $maxBytes = (int) ($config['upload_max_bytes'] ?? 5242880);
        $size = (int) ($file['size'] ?? 0);

        if ($size < 1 || $size > $maxBytes) {
            throw new RuntimeException('Файл өлшемі рұқсат етілген шектен асып кетті.');
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new RuntimeException('Жүктеу уақытша файлы жарамсыз.');
        }

        $mime = self::mimeType($tmpName);
        $allowed = $config['upload_allowed_mimes'] ?? [];

        if (!in_array($mime, $allowed, true)) {
            throw new RuntimeException('Бұл файл түріне рұқсат жоқ.');
        }

        $originalName = basename((string) ($file['name'] ?? 'upload.bin'));
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $safeExtension = preg_replace('/[^a-z0-9]/', '', $extension) ?: 'bin';
        $storedName = date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . $safeExtension;
        $directory = PROJECT_ROOT . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads';

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Файл сақтау директориясын жасау мүмкін болмады.');
        }

        $target = $directory . DIRECTORY_SEPARATOR . $storedName;

        if (!move_uploaded_file($tmpName, $target)) {
            throw new RuntimeException('Файлды сақтау мүмкін болмады.');
        }

        return [
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'mime_type' => $mime,
            'file_size' => $size,
        ];
    }

    /**
     * EN: Detects a file MIME type using finfo.
     * RU: Определяет MIME-тип файла через finfo.
     *
     * @param string $path Temporary file path / Путь временного файла
     * @return string
     */
    private static function mimeType(string $path): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($path);

        return $mime ?: 'application/octet-stream';
    }
}
