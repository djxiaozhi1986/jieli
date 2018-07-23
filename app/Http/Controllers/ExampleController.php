<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
class ExampleController extends Controller
{
    //上传图片
    public function editor_upload_pic(Request $request){
        if (!$request->hasFile('pics')) {
            $code = array('dec' => $this->client_err);
            return response()->json($code);
        }
        $file = $request->file('pics');
        if($file->isValid()){
            //检查mime
            $fi = new \finfo(FILEINFO_MIME_TYPE);
            if (!$this->_isImg($fi->file($file->getPathname()))) return 'error|您上传的不是图片';

            //上传图片
            $path = config('C.IMG_URL').'editor/';
            $time = time();
            $filename = $time.'.jpg';
            $file->move($path,$filename);
            $save_path = $path.$filename;
            $result['errno']=0;
            $result['data'][]=config('C.DOMAIN').$save_path;
            return response()->json($result);
        }
    }
    public function del_pics(Request $request){
        $imgs = $request['del_imgs'];
        foreach ($imgs as $item){
            $item=str_replace(config('C.DOMAIN'),'',$item);
            File::delete($item);
        }
        return 1;
    }
}
