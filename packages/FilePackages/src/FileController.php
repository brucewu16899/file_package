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
        //$directory='/public/filepackages';
        $directory='/home/wwwroot/file_test';
        $FilePackages = new FilePackages($directory);
        $files = $FilePackages->getfiles();

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
        $files = $FilePackages->getfiles();

        foreach ($files as $key=>$val){
            $extend=pathinfo($val, PATHINFO_EXTENSION);
            $show_name[]='測試文件'.($key+1).'.'.$extend;
            $tmp_arr=explode("/",$val);
            $file_name[$key]=$tmp_arr[(count($tmp_arr)-1)];
        }
        $FilePackages = new FilePackages($directory);
        return $FilePackages->getResponse('download',$file_name[$id],$show_name[$id]);
    }

    public function postUpload(Request $request)
    {
        $upload_file = $request->file('upload_file');

        //$directory='/public/filepackages';
        $directory='/home/wwwroot/file_test';
        $FilePackages = new FilePackages($directory);
        $result=$FilePackages->uploadFile($upload_file);

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
        $files = $FilePackages->getfiles();
        foreach ($files as $k=>$val){
            $tmp_arr=explode("/",$val);
            $file_name[$k]=$tmp_arr[(count($tmp_arr)-1)];
        }

        $result=$FilePackages->deleteFile($file_name[$key]);

        return json_encode($result);
    }

    public function postDeletefloder(Request $request)
    {
        $floder = $request->input('floder', null);//欲刪除之資料夾
        $directory='/home/wwwroot/file_test';
        $FilePackages = new FilePackages($directory);
        $result = $FilePackages->deleteFloder($floder);

        foreach($result as $val){
            echo $val['floder'].$val['msg'].'<br>';
        }

    }

}
