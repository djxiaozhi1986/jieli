<?php
/**
 * Created by PhpStorm.
 * User: gavinsir
 * Date: 2018/11/22
 * Time: 5:24 PM
 */

namespace App\Http\Controllers;
use App\Modules\Articles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticlesController extends Controller
{

    //小程序测试
    public function get_mini_articles(Request $request){
        $page_index = $request->input('page_index')??1;//页码
        $page_number = $request->input('page_number')??10;//每页显示
        //初始化sql
        $sql = Articles::orderBy('created_at','desc');
        //附加条件,模糊查询 课程标题、讲师姓名或昵称
        if($request->input('keyword')){
            $key = $request->input('keyword');
            $sql = $sql->where('title','like','%'.$key.'%');
        }
        $total = $sql->count();
        $list = $sql->select('article_id', 'title','description','created_at','keywords','thumb')->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();
        $code = array('dec' => $this->success, 'data' => $list,'total'=>$total);
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    public function get_articles(Request $request){
        $page_index = $request->input('page_index')??1;//页码
        $page_number = $request->input('page_number')??10;//每页显示
        //初始化sql
        $sql = Articles::orderBy('created_at','desc');
        //附加条件,模糊查询 课程标题、讲师姓名或昵称
        if($request->input('keyword')){
            $key = $request->input('keyword');
            $sql = $sql->where('title','like','%'.$key.'%');
        }
        $total = $sql->count();
        $list = $sql->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();
        $code = array('dec' => $this->success, 'data' => $list,'total'=>$total);
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    public function get_article_detail(Request $request){
        if($request->input('article_id')){
            $article = Articles::where('article_id',$request->input('article_id'))->first();
            if($article){
                $result['article_id'] = $article->article_id;
                $result['title'] = $article->title;
                $result['description'] = $article->description;
                $result['content'] = $article->content;
                $result['keywords'] = $article->keywords;
                $result['thumb'] = $article->thumb;
                $code = array('dec'=>$this->success,'data'=>$result);
            }else{
                $code = array('dec'=>array('code' => '060009', 'msg' => '不存在的文章'));
            }
        }else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    public function save(Request $request){
        $article_id = $request->input('article_id');
        $title = $request->input('title');
        $description = $request->input('description');
        $keywords = $request->input('keywords');
        $content = $request->input('content');
        $thumb = $request->input('thumb');
        if($title && $description && $keywords && $content){
           $save_data['title'] = $title;
           $save_data['description'] = $description;
           $save_data['keywords'] = $keywords;
           $save_data['content'] = $content;
           $save_data['thumb'] = $thumb;
           if($article_id){
               //更新
               $save_data['updated_at'] = time();
               $res = Articles::where('article_id',$article_id)->update($save_data);
               if($res){
                   $code = array('dec'=>$this->success);
               }else{
                   $code = array('dec'=>array('code' => '060009', 'msg' => '文章没有改变，请确认是否进行过编辑'));
               }
           }else{
               //新增
               $save_data['created_at'] = time();
               $res = Articles::create($save_data);
               if($res){
                   $code = array('dec'=>$this->success);
               }else{
                   $code = array('dec'=>$this->error);
               }
           }
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }

    public function pass(Request $request){
        if($request->input('article_id')){
            $article = Articles::where('article_id',$request->input('article_id'))->first();
            if($article){
                $article->is_pass = 1;
                $res =$article->save();
                if($res){
                    $code = array('dec'=>$this->success);
                }else{
                    $code = array('dec'=>$this->error);
                }
            }else{
                $code = array('dec'=>array('code' => '060009', 'msg' => '不存在的文章'));
            }
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function refuse(Request $request){
        if($request->input('article_id')){
            $article = Articles::where('article_id',$request->input('article_id'))->first();
            if($article){
                $article->is_pass = 0;
                $res =$article->save();
                if($res){
                    $code = array('dec'=>$this->success);
                }else{
                    $code = array('dec'=>$this->error);
                }
            }else{
                $code = array('dec'=>array('code' => '060009', 'msg' => '不存在的文章'));
            }
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function delete(Request $request){
        if($request->input('article_id')){
            $article = Articles::where('article_id',$request->input('article_id'))->first();
            if($article){
                $res =$article->delete();
                if($res){
                    $code = array('dec'=>$this->success);
                }else{
                    $code = array('dec'=>$this->error);
                }
            }else{
                $code = array('dec'=>array('code' => '060009', 'msg' => '不存在的文章'));
            }
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }

    //上传图片
    public function upload_thumb(Request $request){
        if (!$request->hasFile('file')) {
            $code = array('dec' => $this->client_err);
            return response()->json($code);
        }
        $file = $request->file('file');
        if($file->isValid()){
            //检查mime
            $extension = $file->extension();
            if (!$this->_isImg($extension)){
                $result['errno']=-1;
                $result['msg']='您上传的不是图片';
                return response()->json($result);
            }

            //上传图片
            $path = config('C.IMG_URL').'editor/';
            $time = time();
            $filename = $time.'.jpg';
            $file->move($path,$filename);
            $save_path = $path.$filename;
            $result['errno']=0;
            $result['data'][]=config('C.DOMAIN1').$save_path;
            return response()->json($result);
        }
    }

    public function api_articles(Request $request){
        $token = 'bAkqCmQuhL7djLYqeraQ2pJwGfKzcyFCMdotryfFyzuaKXykphQtTwDBiNnnuGo9';
        $req_token = $request->input('token');
        if($req_token){
            if($req_token == $token){
                $is_pass = $request->input('is_pass')??1;
                $date_req = $request->input('created_at')??time();
                $today_start_str = date("Y-m-d",$date_req);
                $today_end_str = date("Y-m-d 23:59:59",$date_req);
                $today_start = strtotime($today_start_str);
                $today_end = strtotime($today_end_str);
                //返回数据
                $result = Articles::where('is_pass',$is_pass)
                    ->where('created_at','>=',$today_start)
                    ->where('created_at','<=',$today_end)
                    ->select('article_id', 'title','description','content','created_at','is_pass','keywords','thumb')
                    ->get()->toArray();
                if(count($result)>0){
                    $code = array('dec'=>array('code'=>0,'msg'=>'SUCCESS','data'=>$result));
                    //记录已经拉取结束
//                    foreach ($result as $key=>$value){
//                        $result[$key]['is_send']=1;
//                    }
//                    $this->updateBatch('articles',$result);
                }else{
                    $code = array('dec'=>array('code'=>-3,'msg'=>$today_start_str.',没有新审核通过的文章'));
                }
            }else{
                $code = array('dec'=>array('code'=>-2,'msg'=>'token无效'));
            }
        }else{
            $code = array('dec'=>array('code'=>-1,'msg'=>'无token'));
        }
        return response()->json($code);
    }

    public function updateBatch($tableName = "", $multipleData = array()){
        if( $tableName && !empty($multipleData) ) {
            // column or fields to update
            $updateColumn = array_keys($multipleData[0]);
            $referenceColumn = $updateColumn[0]; //e.g id
            unset($updateColumn[0]);
            $whereIn = "";

            $q = "UPDATE ".$tableName." SET ";
            foreach ( $updateColumn as $uColumn ) {
                $q .=  $uColumn." = CASE ";

                foreach( $multipleData as $data ) {
                    $q .= "WHEN ".$referenceColumn." = ".$data[$referenceColumn]." THEN '".$data[$uColumn]."' ";
                }
                $q .= "ELSE ".$uColumn." END, ";
            }
            foreach( $multipleData as $data ) {
                $whereIn .= "'".$data[$referenceColumn]."', ";
            }
            $q = rtrim($q, ", ")." WHERE ".$referenceColumn." IN (".  rtrim($whereIn, ', ').")";

            // Update
            return DB::update(DB::raw($q));

        } else {
            return false;
        }
    }
}