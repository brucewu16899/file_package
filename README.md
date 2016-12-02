
# file_package Laravel sftp 檔案上傳

起因：Laravel 已經有強大的Storage處理檔案上傳/下載等作業，且支援ftp傳送。但因專案需求，客戶只開放使用sftp，故寫了此package擴充了sftp功能  

# 參數設定

## 務必確認server已安裝ssh2套件 ([安裝方式](https://github.com/burgess1109/file_package/edit/master/ssh2.md))

1. 切換報專案目錄下，執行 composer require burgess1109/file_package:* 

2. 至 config/app.php 'providers'內加入 FilePackages\FilePackagesServiceProvider::class,

# 環境參數(.env)

FILE_CONNECT：連線方式(sftp or ftp or local)

使用ftp or sftp 需加入以下參數

FILE_HOST：file server IP

FILE_USERNAME：file server 帳號

FILE_PASSWORD：file server 密碼

FILE_ROOT：上傳目錄

config/filesystems.php 

 1.修正default參數,讓其撈取環境參數
 
 'default' => env('FILE_SERVER', 'local'),
 
 2.'disks'內增加ftp disk, 讓Storage支援FTP
 
 'ftp' => [
            
            'driver'   => 'ftp',
            
            'host' => env('FILE_HOST', 'localhost'),
            
            'username' => env('FILE_USERNAME', '預設帳號'),
            
            'password' => env('FILE_PASSWORD', '預設密碼'),

            // Optional FTP Settings...
            
            'port' => 21,
            
            'root' => env('FILE_ROOT', '上傳目錄'),
        ],


# 測試頁面

提供測試頁面 YourIP/file 

# 使用方式

可參考 packages/FilePackages/src/FileController.php

1. 取得檔案列表

 $directory='路徑';

 $FilePackages = new FilePackages($directory);

 $files = $FilePackages->getfiles();

2. 取得檔案下載

 $directory='路徑';

 $FilePackages = new FilePackages($directory);

 FilePackages->getResponse('download',實際檔名,顯示名稱);

3. 上傳檔案

 $directory='路徑';

 $FilePackages = new FilePackages($directory);

 $result=$FilePackages->uploadFile($upload_file);

4. 刪除檔案

 $directory='路徑';

 $FilePackages = new FilePackages($directory);

 $result=$FilePackages->deleteFile(實際檔名);

5. 刪除資料夾

 $directory='路徑';

 $FilePackages = new FilePackages($directory);

 $result = $FilePackages->deleteFloder(資料夾名稱);


