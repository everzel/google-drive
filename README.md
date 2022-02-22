# Laravel Google Drive

## Installation and usage

### Installation

1. ```composer require everzel/google-drive```

2. Append to .env:
```
GOOGLE_DRIVE_CLIENT_ID=xxx.apps.googleusercontent.com
GOOGLE_DRIVE_CLIENT_SECRET=
GOOGLE_DRIVE_REFRESH_TOKEN=
GOOGLE_DRIVE_FOLDER_ID=
```

3. Append to ```config/filesystems.php``` in ```disks```:
```
'google' => [
    'driver' => 'google',
    'client_id' => env('GOOGLE_DRIVE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
    'refresh_token' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
    'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
],
```

#### How to get a credentials: [robindirksen.com](https://robindirksen.com/blog/google-drive-storage-as-filesystem-in-laravel#:~:text=the%20folder%20id-,1%2C%20create%20Google%20API%20Client,-To%20request%20an)


### Usage

#### Storage

```
app(\Everzel\GoogleDrive\GoogleDrive::class)->storage() : lluminate\Contracts\Filesystem\Filesystem
```
or
```
\Storage::disk('google');
```

#### Put file
```
app(\Everzel\GoogleDrive\GoogleDrive::class)->putFile(mixed $file, string $filename, string $patch) : bool
```

##### Example:
```putFile('test text', 'test.txt', 'github/testfile')```

#### Create folders
```
app(\Everzel\GoogleDrive\GoogleDrive::class)->checkExistsAndCreateFolders(string $patch) : string
```
##### Example:
```checkExistsAndCreateFolders('github/testfile')```

#### Get folders
```
app(\Everzel\GoogleDrive\GoogleDrive::class)->getFolders(string $originalFolderName = '/') : Collection
```
##### Example:
```getFolders('1bFb80xMJUT7yGktC4PV2w1HznWaNwjom') && getFolders('/') - get all folders```

#### Get all files from folder
```
app(\Everzel\GoogleDrive\GoogleDrive::class)->getAllFromFolder(string $originalFolderName = '/') : Collection
```
##### Example:
```getAllFromFolder('1bFb80xMJUT7yGktC4PV2w1HznWaNwjom') && getAllFromFolder('/') - get all files```

#### Get file from URL
```
app(\Everzel\GoogleDrive\GoogleDrive::class)->getFileFromUrl(string $url): mixed
```
##### Example:
```getFileFromUrl('https://drive.google.com/file/d/xxxxxxx/view?usp=sharing')```

#### Get file from patch
```
app(\Everzel\GoogleDrive\GoogleDrive::class)->getFileFromPath(string $patch, string $fileName): mixed
```
##### Example:
```getFileFromPath('github/testfile', 'test.txt')```

#### Get original file name
```
app(\Everzel\GoogleDrive\GoogleDrive::class)->getOriginalFileName(string $originalPath, string $fileName): string
```
##### Example:
```getOriginalFileName('1bFb80xMJUT7yGktC4PV2w1HznWaNwjom', 'test.txt')```


#### Delete file from url
```
app(\Everzel\GoogleDrive\GoogleDrive::class)->deleteFileFromUrl(string $url): bool
```
##### Example:
```deleteFileFromUrl('https://drive.google.com/file/d/xxxxxxx/view?usp=sharing')```

#### Delete file from patch
```
app(\Everzel\GoogleDrive\GoogleDrive::class)->deleteFileFromPatch(string $patch, string string $fileName): bool
```
##### Example:
```deleteFileFromPatch('github/testfile', 'test.txt')```

#### Get file patch from url
```
app(\Everzel\GoogleDrive\GoogleDrive::class)->getFilePathFromUrl(string $url): string
```
##### Example:
```getFilePathFromUrl('https://drive.google.com/file/d/xxxxxxx/view?usp=sharing')```


## Exceptions:

| Type | Exception |
| --- | --- |
| File not found | FileNotFoundException |
| Path not found | PatchNotFoundException |
| Url invalid format | UrlInvalidFormatException |
