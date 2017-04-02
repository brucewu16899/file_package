<?php
namespace FilePackages;

use App\Http\Requests;
use Config;
use Exception;
use File;

/**
 * Class SFTPClass
 * @package Iscom\FilePackages\Controller
 * 錯誤訊息
 */
class SFTPClass extends SFTPConnection implements FilePackagesInterface {
    public $path="";
    public $request_file=array();//上傳檔案內容
    public $return_type='download';//回傳類別, 直接下載:download 圖片:jpg 影片:video
    public $file_name='';//實際檔名
    public $show_name='';//檔案顯示名稱
    public $floder='';//資料夾名稱

    public $error_str=array();//錯誤資訊

    /**
     * 初始參數設定
     */
    function __construct()
    {
        #SFTP
        parent::__construct(env('FILE_HOST'),env('FILE_PORT'));
        $this->login(env('FILE_USERNAME'), env('FILE_PASSWORD'));
    }

    /**
     * 取得檔案列表
     * @return array
     */
    public function getList()
    {
        return $this->scanFilesystem($this->path);
    }

    /**
     * 上傳檔案
     * @return array 回傳新增結果陣列
     */
    public function postFile()
    {
        if (is_array($this->request_file)) {
            foreach ($this->request_file as $value) {
                if(!empty($value)) $result[] = $this->FileUpdate($value);
            }
        } else {
            if(!empty($value)) $result[] = $this->FileUpdate($this->request_file);
        }
        return $result;
    }

    /**
     * 上傳檔案 由function uploadFile呼叫
     * @param $requestfile 上傳資訊
     * @return array|mixed 執行結果陣列
     */
    private function FileUpdate($requestfile)
    {
        //取黨名及副檔名
        $extend = $requestfile->getClientOriginalExtension(); //laraval方法
        /*傳統方式
        $extend=pathinfo($requestfile, PATHINFO_EXTENSION);
        $this->show_name=$requestfile;
        */
        if (empty($extend))  return $this->error_str[1]; //檔案無副檔名

        //新檔名(時間戳)
        $this->file_name = strtotime("now") . "." . $extend;

        //驗證檔案是否已存在
        if($this->check_exist()) return $this->error_str[2]; //上傳檔案已存在

        //把上傳檔案移到目的資料夾
        $mode = 0777;

        if (!$this->checkDir($this->path)) {
            $this->makeDir($this->path,$mode);
        }
        if ($this->uploadFile(File::get($requestfile), $this->path . '/' . $this->file_name, '0777')) {
            return array('result' => true, 'msg' => "檔案上傳成功",'file_name'=>$this->file_name);
        } else {
            $this->error_str[3]['file_name']=$this->file_name;
            return $this->error_str[3];//檔案上傳失敗
        }
    }

    /**
     * 取得檔案內容並回覆
     * @return $this 回傳下載 header
     */
    public function getFile()
    {
        $result = $this->receiveFile($this->path.'/'.$this->file_name);
        if($result){
            ob_clean();
            switch($this->return_type){
                case 'download':
                    $response = response($result, 200)
                        ->header('Content-Type', 'application/force-download')
                        ->header('Content-Disposition', 'attachment;filename=' . $this->show_name);
                    return $response;
                case 'jpg':
                    $tmp_file ='';
                    while (!feof($result) and (connection_status() == 0)) {
                        $tmp_file .= (fread($result, 8192));
                        flush();
                    }
                    @fclose($result);
                    return Response::make($result, 200, ['Content-Type' => 'image/jpeg']);

                case 'video':
                    $tmp_file ='';
                    while (!feof($result) and (connection_status() == 0)) {
                        $tmp_file .= (fread($result, 8192));
                        flush();
                    }
                    @fclose($result);

                    $response = response($result, 200)
                        ->header('Content-Type', 'application/octet-stream')
                        ->header('Content-Type', 'video/mpeg4');
                    return $response;
                default:
                    return false;//參數錯誤
            }
        }else{
            return false;//檔案讀取錯誤
        }
    }

    /**
     * 檔案存在驗證
     * @return bool
     */
    public function check_exist()
    {
        $check_path=$this->path.'/'.$this->file_name;
        $check_path = str_replace('//', '/', $check_path); //檔案路徑過濾

        return $this->checkFile($check_path);
    }

    /**
     * 刪除檔案
     * @return Array $result 刪除結果陣列
     */
    public function deleteFile()
    {
        if(is_array($this->file_name)){
            foreach($this->file_name as $name){
                $result[] = $this->FileDelete($name);
            }
        }else{
            $result[] = $this->FileDelete($this->file_name);
        }
        return $result;
    }

    /**
     * 刪除檔案 由function deleteFile 呼叫
     * @param String $file_name 檔名
     * @return 刪除結果陣列
     */
    private function FileDelete($file_name)
    {
        $result = $this->deleteFiles($this->path.'/'.$file_name);
        if ($result) {
            return array('result' => true, 'msg' => "檔案刪除成功",'file_name'=>$file_name);
        } else {
            $this->error_str[8]['file_name']=$file_name;
            return $this->error_str[8];//檔案刪除失敗
        }
    }

    /**
     * 刪除資料夾(含資料夾內檔案)
     * @return Int $result 刪除結果
     */
    public function deleteFloder()
    {
        if(is_array($this->floder)){
            foreach($this->floder as $name){
                $result[] = $this->FloderDelete($name);
            }
        }else{
            $result[] = $this->FloderDelete($this->floder);
        }
        return $result;
    }

    /**
     * 刪除資料夾 由function deleteFloder 呼叫
     * @param String $floder 欲刪除之資料夾
     * @return Int $result 刪除結果
     */
    public function FloderDelete($floder)
    {
        //驗證檔案路徑及檔案是否已存在
        if(!$this->check_exist($this->path.'/'.$floder)){
            $this->error_str[10]['floder']=$floder;
            return $this->error_str[10];//資料夾不存在
        }

        //刪除資料夾內的檔案
        $SFTPConnection = new SFTPConnection(env('FILE_HOST'));
        $SFTPConnection->login(env('FILE_USERNAME'), env('FILE_PASSWORD'));
        $result = $SFTPConnection->deleteDirectory($this->path.'/'.$floder);

        if ($result) {
            return array('result' => true, 'msg' => "資料夾刪除成功",'floder'=>$floder);
        } else {
            $this->error_str[9]['floder']=$floder;
            return $this->error_str[9];//資料夾刪除失敗
        }
    }
}