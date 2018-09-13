<?php
/**
 * Created by PhpStorm.
 * User: gavinsir
 * Date: 2018/7/5
 * Time: 下午2:03
 */
namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Modules\Categorys;
use App\Modules\UserCategorys;
use Illuminate\Http\Request;
use Overtrue\Socialite\User;

class CategorysController extends Controller{

    /**
     * 获取以及分类
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTopCategorys(Request $request){
        $total = Categorys::where('parent_id',0)->where('status',1)->count();
        $res = Categorys::where('parent_id',0)->where('status',1)->select('category_id','category_name','sort')->orderBy('sort','asc')->get()->toArray();
        $data = [];
        foreach ($res as $key=>$value){
            $item['c_id']= $value['category_id']."";
            $item['c_name']= $value['category_name']."";
            $item['sort_id']= $value['sort']."";
            $data[] = $item;
        }
        $code = array('dec' => $this->success, 'data' => $data,'total'=>$total);
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    /**
     * 获取二级分类
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChildCategorys(Request $request){
        $parent_id = $request->input('c_id');
        $sql = Categorys::where('status',1);
        if($parent_id){
            $sql = $sql->where('parent_id',$parent_id);
        }else{
            $sql = $sql->where('parent_id','<>',0);
        }
        $total = $sql->count();
        $res = $sql->select('category_id','category_name','parent_id')->orderBy('sort','asc')->get()->toArray();
        $data = [];
        foreach ($res as $key=>$value){
            $item['c_id']= $value['parent_id']."";
            $item['forum_name']= $value['category_name']."";
            $item['forum_id']= $value['category_id']."";
            $data[] = $item;
        }
        $code = array('dec' => $this->success, 'data' => $data,'total'=>$total);
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    /**
     * 获取用户分类（如没有，取默认分类）
     * @param Request $request
     */
    public function get_user_categorys(Request $request){
        $user_id = $request->input('login_user');
        if($user_id){
            $res = UserCategorys::where('user_id',$user_id)->select('c_id')->get()->toArray();
//            $res = UserCategorys::where('user_id',$user_id)->select('p_id')->get()->toArray();
            if(count($res)>0){
                $cids = array_flatten($res);
                $data = Categorys::select('category_name as c_name','category_id as c_id')
                    ->whereIn('category_id',$cids)
                    ->orWhere(function($query)
                    {
                        $query->where('is_default', 1)
                            ->where('status', 1)
                            ->where('parent_id',0);
                    })->get()->toArray();
//                var_dump($data);die;
            }else{
                //取默认分类
                $data = Categorys::where('is_default',1)->where('status',1)->where('parent_id',0)->select('category_name as c_name','category_id as c_id')->get()->toArray();
            }
            $result = [];
            foreach ($data as $key=>$value){
                $item['c_name']= $value['c_name']."";
                $item['c_id']= $value['c_id']."";
                $result[] = $item;
            }
            $code = array('dec' => $this->success,'data'=>$data);
        }else{
            $code = array('dec' => $this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    /**
     * 用户添加微课分类
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add_user_category(Request $request){
        $user_id = $request->input('login_user');
        $c_id = $request->input('c_id');
//        $p_id = $request->input('p_id');
        if($user_id && $c_id){
            $data['user_id'] = $user_id;
            $data['c_id'] = $c_id;
//            $data['p_id'] = $p_id;
            $data['created_at'] = time();
            // 通过属性找到相匹配的数据并更新，如果不存在即创建
            $res = UserCategorys::updateOrCreate(array('user_id' => $user_id,'c_id'=>$c_id), $data);
            if($res){
                $code = array('dec' => $this->success);
            }else{
                $code = array('dec' => $this->error);
            }
        }else{
            $code = array('dec' => $this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }
    /**
     * 用户取消微课分类
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function del_user_category(Request $request){
        $user_id = $request->input('login_user');
        $c_id = $request->input('c_id');
        if($user_id && $c_id){
            $data['user_id'] = $user_id;
            $data['c_id'] = $c_id;
            $res = UserCategorys::where('user_id',$user_id)->where('c_id',$c_id)->delete();
            if($res){
                $code = array('dec' => $this->success);
            }else{
                $code = array('dec' => $this->error);
            }
        }else{
            $code = array('dec' => $this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }
}