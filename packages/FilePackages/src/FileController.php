<?php
namespace FilePackages;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Storage;
use Illuminate\Http\Request;

/**
 * Class FileController
 * @package FilePackage
 */
class FileController extends Controller
{
    public function index()
    {
        $test = new SFTPConnection(env('FILE_HOST'));
        dd($test);
        //$directory='/public/filepackages';
        $directory='/home/wwwroot/file_test';
        $FilePackages = new FilePackages($directory);
        $files = $FilePackages->getList();

        $show_name=array();
        foreach ($files as $key=>$val){
            $extend=pathinfo($val, PATHINFO_EXTENSION);
            $show_name[]='測試文件'.($key+1).'.'.$extend;
        }

        $msg='FilePackages Test';
        return view('FilePackages::file',['msg'=>$msg,'show_name'=>$show_name]);
    }

    public function getDownload(Request $request)
    {
        $id = $request->input('id', null);
        if(!isset($id)){
            echo '參數錯誤';
            exit;
        }

        //$directory='/public/filepackages';
        $directory='/home/wwwroot/file_test';
        $FilePackages = new FilePackages($directory);
        $files = $FilePackages->getList();
        foreach ($files as $key=>$val){
            $extend=pathinfo($val, PATHINFO_EXTENSION);
            $show_name[]='測試文件'.($key+1).'.'.$extend;
            $tmp_arr=explode("/",$val);
            $file_name[$key]=$tmp_arr[(count($tmp_arr)-1)];
        }

        $FilePackages->return_type='download';//回傳類別, 直接下載:download 圖片:jpg 影片:video
        $FilePackages->file_name=$file_name[$id];//實際檔名
        $FilePackages->show_name=$show_name[$id];//檔案顯示名稱
        return $FilePackages->getFile();
    }

    public function postUpload(Request $request)
    {
        $upload_file = $request->file('upload_file');

        //$directory='/public/filepackages';
        $directory='/home/wwwroot/file_test';
        $FilePackages = new FilePackages($directory);
        $FilePackages->request_file=$upload_file;
        $result=$FilePackages->postFile();

        foreach($result as $val){
            echo $val['file_name'].$val['msg'].'<br>';
        }
    }

    public function postDelete(Request $request)
    {
        $key = $request->input('key', null);
        //$directory='/public/filepackages';
        $directory='/home/wwwroot/file_test';
        $FilePackages = new FilePackages($directory);
        $files = $FilePackages->getList();
        foreach ($files as $k=>$val){
            $tmp_arr=explode("/",$val);
            $file_name[$k]=$tmp_arr[(count($tmp_arr)-1)];
        }

        $FilePackages->file_name=$file_name[$key];//要刪除的檔名
        $result=$FilePackages->deleteFile();

        return json_encode($result);
    }

    public function postDeletefloder(Request $request)
    {
        $floder = $request->input('floder', null);//欲刪除之資料夾
        if(empty($floder)) dd('參數錯誤');

        $directory='/home/wwwroot/file_test';
        $FilePackages = new FilePackages($directory);
        $FilePackages->floder=$floder;
        $result = $FilePackages->deleteFloder();

        foreach($result as $val){
            echo $val['floder'].$val['msg'].'<br>';
        }

    }

}
