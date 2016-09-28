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
class FilePackages {
    private $path="";
    private $file_name="";
    private $error_str=array(
        array('result'=> false,'msg' => "未設定上傳或下載路徑"),
        array('result'=> false,'msg' => "檔案無副檔名"),
        array('result'=> false,'msg' => "上傳檔案已存在"),
        array('result'=> false,'msg' => "檔案上傳失敗"),
        array('result'=> false,'msg' => "無檔案名稱"),
        array('result'=> false,'msg' => "檔案不存在"),
        array('result'=> false,'msg' => "參數錯誤"),
        array('result'=> false,'msg' => "檔案讀取錯誤"),
        array('result'=> false,'msg' => "檔案刪除失敗"),
        array('result'=> false,'msg' => "資料夾刪除失敗"),
        array('result'=> false,'msg' => "資料夾不存在"),
    );
    /**
     * 初始參數設定
     * @param String $path 設定上傳或下載路徑
     */
    function __construct($path)
    {
        if(empty($path)){
            return $this->error_str[0];
        }else{
            //上傳或下載路徑
            $this->path = str_replace('//', '/', $path);//檔案路徑過濾
        }
    }

    /**
     * 上傳檔案
     * @param Class $requestfile 上傳request->file class
     * @return array 回傳新增的file ID陣列
     */
    public function uploadFile($requestfile)
    {
        if (is_array($requestfile)) {
            foreach ($requestfile as $value) {
                if(!empty($value)) $result[] = $this->FileUpdate($value);
            }
        } else {
            if(!empty($value)) $result[] = $this->FileUpdate($requestfile);
        }
        return $result;
    }

    /**
     * 上傳檔案 由function uploadFile呼叫
     * @param Class $requestfile 上傳request->file class
     * @return Array $result 執行結果陣列
     */
    private function FileUpdate($requestfile)
    {
        //取黨名及副檔名
        //laraval方法
        $extend = $requestfile->getClientOriginalExtension();
        //傳統方式
        /*
        $extend=pathinfo($requestfile, PATHINFO_EXTENSION);
        $this->show_name=$requestfile;
        */
        if (empty($extend))  return $this->error_str[1]; //檔案無副檔名

        //新檔名(時間戳)
        $this->file_name = strtotime("now") . "." . $extend;

        //驗證檔案路徑及檔案是否已存在
        if($this->file_check($this->path.'/'.$this->file_name)) return $this->error_str[2]; //上傳檔案已存在

        //把上傳檔案移到目的資料夾
        $mode = 0777;

        if (env('FILE_CONNECT') == 'sftp') {
            $SFTPConnection = new SFTPConnection(env('FILE_HOST'));
            $SFTPConnection->login(env('FILE_USERNAME'), env('FILE_PASSWORD'));
            if (!$SFTPConnection->checkDir($this->path)) {
                $SFTPConnection->makeDir($this->path);
            }
            if ($SFTPConnection->uploadFile(File::get($requestfile), $this->path . '/' . $this->file_name, '0777')) {
                return array('result' => true, 'msg' => "檔案上傳成功",'file_name'=>$this->file_name);
            } else {
                $this->error_str[3]['file_name']=$this->file_name;
                return $this->error_str[3];//檔案上傳失敗
            }
        } else {
            if (!Storage::exists($this->path)) {
                Storage::makeDirectory($this->path, $mode);//新增目錄
            }
            //laravel函式 move(實際路徑,檔名)
            // if ($requestfile->move($base_path, $this->file_name)) {
            if (Storage::put($this->path . '/' . $this->file_name, File::get($requestfile))) {
                return array('result' => true, 'msg' => "檔案上傳成功",'file_name'=>$this->file_name);
            } else {
                $this->error_str[3]['file_name']=$this->file_name;
                return $this->error_str[3];//檔案上傳失敗
            }
        }
    }

    /**
     *取得檔案列表
     * @return Array $file
     */
    public function getfiles(){
        if (env('FILE_CONNECT') == 'sftp') {
            #SFTP
            require('SFTPConnection.php');
            $SFTPConnection = new SFTPConnection(env('FILE_HOST'));
            $SFTPConnection->login(env('FILE_USERNAME'), env('FILE_PASSWORD'));
            $files = $SFTPConnection->scanFilesystem($this->path);
            //$files = $SFTPConnection->scanFilesystem(storage_path('app/public').$this->path);
        }else{
            $files = Storage::files($this->path);
        }

        return $files;
    }

    /**
     * 取得檔案內容並回覆
     * @return $this 回傳下載 header
     */
    public function getResponse($return_type='download',$file_name='',$show_name='')
    {
        if(empty($file_name)) return $this->error_str[4];//無檔案名稱
        if(empty($show_name)) $show_name=$file_name;
        //驗證檔案路徑及檔案是否已存在
        if(!$this->file_check($this->path.'/'.$file_name)) return $this->error_str[5]['msg'];//檔案不存在

        if (env('FILE_CONNECT') == 'sftp') {
            #SFTP
            $SFTPConnection = new SFTPConnection(env('FILE_HOST'));
            $SFTPConnection->login(env('FILE_USERNAME'), env('FILE_PASSWORD'));

            $new_file = $SFTPConnection->receiveFile($this->path.'/'.$file_name);
            if($new_file){
                ob_clean();
                switch($return_type){
                    case 'download':
                        $response = response($new_file, 200)
                            ->header('Content-Type', 'application/force-download')
                            ->header('Content-Disposition', 'attachment;filename=' . $show_name);
                        break;
                    case 'jpg':
                        $tmp_file ='';
                        while (!feof($new_file) and (connection_status() == 0)) {
                            $tmp_file .= (fread($new_file, 8192));
                            flush();
                        }
                        @fclose($new_file);
                        return Response::make($new_file, 200, ['Content-Type' => 'image/jpeg']);

                    case 'video':
                        $tmp_file ='';
                        while (!feof($new_file) and (connection_status() == 0)) {
                            $tmp_file .= (fread($new_file, 8192));
                            flush();
                        }
                        @fclose($new_file);

                        $response = response($tmp_file, 200)
                            ->header('Content-Type', 'application/octet-stream')
                            ->header('Content-Type', 'video/mpeg4');
                        break;
                    default:
                        return $this->error_str[6];//參數錯誤
                }
                return $response;
            }else{
                return $this->error_str[7];//檔案讀取錯誤
            }
        } else {
            $new_file = Storage::get($this->path.'/'.$file_name);
            if ($new_file) {
                ob_clean();
                $response = response($new_file, 200)
                    ->header('Content-Type', 'application/force-download')
                    ->header('Content-Disposition', 'attachment;filename=' . $show_name);
                return $response;
            } else {
                return $this->error_str[7];//檔案讀取錯誤
            }
        }
        /*fopen方法(舊)
        if ($fp = fopen($path, 'rb')) {
            ob_end_clean();
            while (!feof($fp) and (connection_status() == 0)) {
                $new_file .= (fread($fp, 8192));
                flush();
            }
            @fclose($fp);

            $response = response($new_file, 200)
                ->header('Content-Type', 'application/force-download')
                ->header('Content-Disposition', 'attachment;filename=' . $this->show_name);
            return $response;
        } else {
            return array('result' => 2, 'msg' => "檔案無法讀取");
        }
        */
    }

    /**
     * 檔案驗證
     * @param $path_check
     * @return bool
     * @throws Exception
     */
    public function file_check($path_check)
    {
        $path_check = str_replace('//', '/', $path_check); //檔案路徑過濾
        if (env('FILE_CONNECT') == 'sftp') {
            $SFTPConnection = new SFTPConnection(env('FILE_HOST'));
            $SFTPConnection->login(env('FILE_USERNAME'), env('FILE_PASSWORD'));
            $file_exists = $SFTPConnection->checkFile($path_check);
        } else {
            $file_exists = Storage::exists($path_check);//Storage 預設指向 storage/uploads
        }

        //檔案存在驗證
        if ($file_exists) {
            return true;
        }else{
            return false;
        }

    }

    /**
     * 刪除檔案
     * @param String $file_name 檔名
     * @return Array $result 刪除結果陣列
     */
    public function deleteFile($file_name=array())
    {
        if(is_array($file_name)){
            foreach($file_name as $name){
                $result[] = $this->FileDelete($name);
            }
        }else{
            $result[] = $this->FileDelete($file_name);
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
        //驗證檔案路徑及檔案是否已存在
        if(!$this->file_check($this->path.'/'.$file_name)){
            $this->error_str[5]['file_name']=$file_name;
            return $this->error_str[5];//檔案不存在
        }

        //刪除資料夾內的檔案
        if (env('FILE_CONNECT') == 'sftp') {
            #SFTP
            $SFTPConnection = new SFTPConnection(env('FILE_HOST'));
            $SFTPConnection->login(env('FILE_USERNAME'), env('FILE_PASSWORD'));
            $result = $SFTPConnection->deleteFile($this->path.'/'.$file_name);
        } else {
            $result = Storage::delete($this->path.'/'.$file_name);
        }
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
    public function deleteFloder($floder)
    {
        if(is_array($floder)){
            foreach($floder as $name){
                $result[] = $this->FloderDelete($name);
            }
        }else{
            $result[] = $this->FloderDelete($floder);
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
        if(!$this->file_check($this->path.'/'.$floder)){
            $this->error_str[10]['floder']=$floder;
            return $this->error_str[10];//資料夾不存在
        }

        //刪除資料夾內的檔案
        if (env('FILE_CONNECT') == 'sftp') {
            #SFTP
            $SFTPConnection = new SFTPConnection(env('FILE_HOST'));
            $SFTPConnection->login(env('FILE_USERNAME'), env('FILE_PASSWORD'));
            $result = $SFTPConnection->deleteDirectory($this->path.'/'.$floder);
        } else {
            $result = Storage::deleteDirectory($this->path.'/'.$floder);
        }
        if ($result) {
            return array('result' => true, 'msg' => "資料夾刪除成功",'floder'=>$floder);
        } else {
            $this->error_str[9]['floder']=$floder;
            return $this->error_str[9];//資料夾刪除失敗
        }
    }
}