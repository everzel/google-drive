<?php

namespace Everzel\GoogleDrive;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class GoogleDrive
{
    private Filesystem $storage;

    public function __construct()
    {
        $this->storage = Storage::disk('google');
    }

    public function storage(): Filesystem
    {
        return $this->storage;
    }

    public function putFile(string $patch, mixed $file): bool
    {
        return $this->storage->put($patch, $file);
    }

    /**
     * @throws Exception
     * @throws FileNotFoundException
     */
    public function getFileFromUrl(string $url): string
    {
        $filePatch = $this->getFilePathFromUrl($url);

        return $this->storage->get($filePatch);
    }

    /**
     * @throws FileNotFoundException
     */
    public function getFileFromPath(string $path): string
    {
        return $this->storage->get($path);
    }

    /**
     * @throws Exception
     */
    public function deleteFileFromUrl(string $url): bool
    {
        $filePatch = $this->getFilePathFromUrl($url);

        return $this->storage->delete($filePatch);
    }

    public function deleteFileFromPatch(string $patch): bool
    {
        return $this->storage->delete($patch);
    }

    /**
     * @throws Exception
     */
    private function getFilePathFromUrl(string $url): string
    {
        $url = strtok($url, '?');

        $removed = [
            'https://drive.google.com/file/d/',
            '/view',
        ];

        foreach ($removed as $remove) {
            if (! str_contains($url, $remove)) {
                throw new Exception('Invalid url format.');
            }

            $url = str_replace($remove, '', $url);
        }

        return $url;
    }
}
