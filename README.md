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
app(\Everzel\GoogleDrive\GoogleDrive::class)->putFile(string $patch, mixed $file')
```

#### Get file from url
```
app(\Everzel\GoogleDrive\GoogleDrive::class)->getFileFromUrl(string $url)
```

#### Get file from patch
```
app(\Everzel\GoogleDrive\GoogleDrive::class)->getFileFromPath(string $patch)
```

#### Delete file from url
```
app(\Everzel\GoogleDrive\GoogleDrive::class)->deleteFileFromUrl(string $url)
```

#### Delete file from patch
```
app(\Everzel\GoogleDrive\GoogleDrive::class)->deleteFileFromPatch(string $patch)
```
