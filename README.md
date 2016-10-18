
# file_package Laravel sftp 檔案上傳

起因：Laravel 已經有強大的Storage處理檔案上傳/下載等作業，且支援ftp傳送。但因專案需求，客戶只開放使用sftp，故寫了此package擴充了sftp功能  

# 使用方式

切換報專案目錄下，執行 composer require burgess1109/file_package:* 

並至 config/app.php 'providers'內加入 FilePackages\FilePackagesServiceProvider::class,
