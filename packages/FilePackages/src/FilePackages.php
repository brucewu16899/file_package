<?php
namespace FilePackages;

use App\Http\Requests;
use Config;
use Exception;
use File;
use Storage;

/**
 * Class FilePackages
 * @package Iscom\FilePackages\Controller
 * 錯誤訊息
 */
class FilePackages implements FilePackagesInterface {
    private $path="";
    private $package="";//引用的類別
    public $request_file=array();//上傳檔案內容
    public $return_type='download';//回傳類別, 直接下載:download 圖片:jpg 影片:video
    public $file_name='';//實際檔名
    public $show_name='';//檔案顯示名稱
    public $floder='';//資料夾名稱

    private $error_str=array(
        array('result'=> false,'msg' => "1.未設定上傳或下載路徑"),
        array('result'=> false,'msg' => "2.檔案無副檔名"),
        array('result'=> false,'msg' => "3.上傳檔案已存在"),
        array('result'=> false,'msg' => "4.檔案上傳失敗"),
        array('result'=> false,'msg' => "5.無檔案名稱"),
        array('result'=> false,'msg' => "6.檔案不存在"),
        array('result'=> false,'msg' => "7.參數錯誤"),
        array('result'=> false,'msg' => "8.檔案讀取錯誤"),
        array('result'=> false,'msg' => "9.檔案刪除失敗"),
        array('result'=> false,'msg' => "10.資料夾刪除失敗"),
        array('result'=> false,'msg' => "11.資料夾不存在"),
    );

    /**
     * 初始參數設定
     * @param String $path 設定上傳或下載路徑
     */
    function __construct($path)
    {
        switch(env('FILE_CONNECT')){
            case 'sftp':
                $this->package=new SFTPClass();
            default :
                $this->package=new StorageClass();
        }

        if(empty($path)){
            return $this->error_str[0];
        }else{
            //上傳或下載路徑
            $this->package->path = str_replace('//', '/', $path);//檔案路徑過濾
        }

        $this->package->error_str=$this->error_str;//錯誤資訊
    }

    /**
     * 取得檔案列表
     * @return Array
     */
    public function getList()
    {
        return $this->package->getList();
    }

    /**
     * @return array 回傳新增結果陣列
     */
    public function postFile()
    {
        $this->package->request_file=$this->request_file;//上傳資訊
        return $this->package->postFile();
    }

    /**
     * 取得檔案內容並回覆
     * @return $this 回傳下載 header
     */
    public function getFile()
    {
        if(empty($this->file_name)) return $this->error_str[4];//無檔案名稱
        if(empty($this->show_name)) $this->show_name=$this->file_name;

        //驗證檔案路徑及檔案是否已存在
        $this->package->file_name=$this->file_name;
        $this->package->show_name=$this->show_name;
        $this->package->return_type=$this->return_type;
        if(!$this->check_exist()) return $this->error_str[5]['msg'];//檔案不存在

        $response=$this->package->getFile();
        if ($response) {
            return $response;
        } else {
            return $this->error_str[7];//檔案讀取錯誤
        }
    }

    /**
     * 檔案存在驗證
     * @return bool
     * @throws Exception
     */
    public function check_exist()
    {
        return $this->package->check_exist();
    }

    /**
     * 刪除檔案
     * @return Array $result 刪除結果陣列
     */
    public function deleteFile()
    {
        $this->package->file_name=$this->file_name;//要刪除的檔名

        return $this->package->deleteFile();
    }

    /**
     * 刪除資料夾(含資料夾內檔案)
     * @return Int $result 刪除結果
     */
    public function deleteFloder()
    {
        $this->package->floder=$this->floder;//要刪除的資料夾

        return $this->package->deleteFloder();
    }
}