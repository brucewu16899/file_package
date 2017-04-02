<?php
namespace FilePackages;

use Illuminate\Database\Eloquent\Collection;

/**
 * Interface FilePackagesInterface
 * @package FilePackages
 */
interface FilePackagesInterface
{
    /**
     * 檔案列表 Get List
     * @return mixed
     */
    public function getList();

    /**
     * 上傳檔案 Upload files
     * @return mixed
     */
    public function postFile();

    /**
     * 下傳檔案 Download files
     * @return mixed
     */
    public function getFile();

    /**
     * 刪除檔案 Delete files
     * @return mixed
     */
    public function deleteFile();

    /**
     * 檔案存在驗證 Check exist
     * @return mixed
     */
    public function check_exist();
}