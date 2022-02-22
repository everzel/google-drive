<?php

namespace Everzel\GoogleDrive;

use Everzel\GoogleDrive\Exceptions\PatchNotFoundException;
use Everzel\GoogleDrive\Exceptions\UrlInvalidFormatException;
use Exception;
use Everzel\GoogleDrive\Exceptions\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
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

    public function putFile(mixed $file, string $fileName, string $patch = ''): bool
    {
        $patch = $this->checkExistsAndCreateFolders($patch);

        return $this->storage->put($patch . '/' . $fileName, $file);
    }

    public function checkExistsAndCreateFolders(string $patch): string
    {
        $folders = $this->explodePatch($patch);
        $cloudFolders = $this->getFolders();

        return $this->createFolders($folders, $cloudFolders);
    }

    private function createFolders(array $folders, Collection $cloudFolders)
    {
        $prevCloudFolder = null;
        $isReturned = false;

        for ($i = 0; $i < count($folders); $i++) {
            $currentFolder = $folders[$i];

            if (is_null($prevCloudFolder)) {
                $currentCloudFolders = $cloudFolders;
            } else {
                $currentCloudFolders = collect($prevCloudFolder['child'] ?? []);
            }

            if ($this->checkExistsFolder($currentCloudFolders->toArray(), $currentFolder)) {
                if (is_null($prevCloudFolder)) {
                    $this->makeDirectory('/' . $currentFolder);
                } else {
                    $this->makeDirectory( $prevCloudFolder['cloud_patch'] . '/' . $currentFolder);
                }

                $isReturned = true;

                break;
            }

            $prevCloudFolder = $currentCloudFolders->where('name', $currentFolder)->first();
        }

        if ($isReturned) {
            return $this->createFolders($folders, $this->getFolders());
        }

        return $prevCloudFolder['cloud_patch'] ?? '/';
    }

    public function getFolders(string $originalFolderName = '/'): Collection
    {
        $folders = $this->storage()->listContents($originalFolderName);

        $folders = $this->filterFolders($folders);

        return $this->getFoldersList($folders);
    }

    private function getFoldersList(array $folders): Collection
    {
        $newFolders = [];

        foreach ($folders as $key => $folder) {
            $newFolders[$key] = $this->getMetaDataFolder($folder);

            $childFolders = $this->storage()->listContents($folder['path']);

            if (! empty($childFolders)) {
                foreach ($childFolders as $childFolder) {
                    $newItem = $this->getMetaDataFolder($childFolder);

                    $childChildrenFolder = $this->storage()->listContents($childFolder['path']);

                    $newItem['child'] = $this->getFoldersList($childChildrenFolder);

                    $newFolders[$key]['child'][] = $newItem;
                }
            }
        }

        return collect($newFolders);
    }

    private function filterFolders(array $folders): array
    {
        foreach ($folders as $key => $folder) {
            if ($folder['type'] !== 'dir') {
                unset($folders[$key]);
            }
        }

        return $folders;
    }

    private function getMetaDataFolder(array $folder): array
    {
        return [
            'name' => $folder['name'],
            'cloud_patch' => $folder['path'],
            'timestamp' => $folder['timestamp'],
            'size' => $folder['size'],
            'child' => [],
        ];
    }

    private function explodePatch(string $path): array
    {
        $path = str_replace('\\', '/', $path);

        return explode('/', $path);
    }

    private function checkExistsFolder(array $cloudFolders, string $folderName): bool
    {
        $cloudFolders = collect($cloudFolders);

        return $cloudFolders->where('name', $folderName)->isEmpty();
    }

    public function getAllFromFolder(string $originalFolderName = '/'): Collection
    {
        return collect($this->storage()->listContents($originalFolderName));
    }

    private function makeDirectory(string $name): void
    {
        $this->storage->makeDirectory($name);
    }

    /**
     * @throws Exception
     * @throws FileNotFoundException
     */
    public function getFileFromUrl(string $url): mixed
    {
        $filePatch = $this->getFilePathFromUrl($url);

        return $this->storage->get($filePatch);
    }

    /**
     * @throws FileNotFoundException
     */
    public function getFileFromPath(string $path, string $fileName): string
    {
        $filePatch = $this->getOriginalFileName($this->getFilePatch($path), $fileName);

        return $this->storage->get($filePatch);
    }

    private function getFilePatch(string $path): string
    {
        $localPatch = explode('/', $path);
        $cloudFolders = collect($this->getFolders());

        if (count($localPatch) <= 1) {
            return '/';
        }

        $currentFolder = $cloudFolders;

        for ($i = 0; $i < count($localPatch); $i++) {
            $item = $currentFolder->where('name', $localPatch[$i])->first();

            if (empty($item)) {
                throw new PatchNotFoundException;
            }

            if ($i === count($localPatch) - 1) {
                return $item['cloud_patch'];
            }

            $currentFolder = collect($item['child'] ?? []);
        }

        return '/';
    }

    public function getOriginalFileName(string $originalPath, string $fileName): string
    {
        $items = collect($this->storage()->listContents($originalPath));

        $findItem = $items->where('name', $fileName);

        if ($findItem->isEmpty()) {
            throw new FileNotFoundException;
        }

        return $findItem->first()['path'];
    }

    /**
     * @throws Exception
     */
    public function deleteFileFromUrl(string $url): bool
    {
        $filePatch = $this->getFilePathFromUrl($url);

        return $this->storage->delete($filePatch);
    }

    public function deleteFileFromPatch(string $patch, string $fileName): bool
    {
        $filePatch = $this->getOriginalFileName($this->getFilePatch($patch), $fileName);

        return $this->storage->delete($filePatch);
    }

    /**
     * @throws Exception
     */
    public function getFilePathFromUrl(string $url): string
    {
        $url = strtok($url, '?');

        $removed = [
            'https://drive.google.com/file/d/',
            '/view',
        ];

        foreach ($removed as $remove) {
            if (! str_contains($url, $remove)) {
                throw new UrlInvalidFormatException;
            }

            $url = str_replace($remove, '', $url);
        }

        return $url;
    }
}
