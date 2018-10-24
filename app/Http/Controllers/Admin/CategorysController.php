<?php
/**
 * Created by PhpStorm.
 * User: gavinsir
 * Date: 2018/7/5
 * Time: 下午2:03
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Categorys;
use App\Modules\Users;
use Illuminate\Http\Request;

class CategorysController extends Controller{

    /**
     * 获取以及分类
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTopCategorys(Request $request){
        $total = Categorys::where('parent_id',0)->where('status',1)->count();
        $res = Categorys::where('parent_id',0)->where('status',1)->orderBy('sort','asc')->get()->toArray();
        $data = [];
        foreach ($res as $key=>$value){
            $item['c_id']= $value['category_id'];
            $item['c_name']= $value['category_name'];
            $item['sort_id']= $value['sort'];
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
        $res = $sql->orderBy('sort','asc')->get()->toArray();
        $data = [];
        foreach ($res as $key=>$value){
            $item['c_id']= $value['parent_id'];
            $item['forum_name']= $value['category_name'];
            $item['forum_id']= $value['category_id'];
            $data[] = $item;
        }
        $code = array('dec' => $this->success, 'data' => $res,'total'=>$total);
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    //管理
    /**
     * 获取以及分类
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_all_categorys(Request $request){
        $res = Categorys::orderBy('sort','asc')->get()->toArray();
        $data = [];
        foreach ($res as $key=>$value){
            if($value['parent_id']==0){
                $item['title'] = $value['category_name'];
                $item['id'] = $value['category_id'];
                $item['is_default'] = $value['is_default'];
                $item['expand']=true;
                $item['pname'] = '顶级分类';
                $item['parent_id'] = $value['parent_id'];
                $item['children'] = $this->getChildres($value['category_id'],$item['title'],$res);
                $data[] = $item;
            }
        }
        $code = array('dec' => $this->success, 'data' => $data);
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }
    private function getChildres($parent_id,$pname,$list){
        $childre = [];
        foreach ($list as $item){
            if($item['parent_id']==$parent_id){
                $it['title'] = $item['category_name'];
                $it['pname'] = $pname;
                $it['id'] = $item['category_id'];
                $it['parent_id'] = $item['parent_id'];
                $childre[] = $it;
            }
        }
        return $childre;
    }

    public function saveCategory(Request $request){
        $p_id = $request->input('pid');
        $c_name = $request->input('cname');
        $c_sort = $request->input('csort');
        $is_default = $request->input('is_default');
//        var_dump($is_default);
        if($c_name){
            $c_id = $request->input('id');
            $data['category_name'] = $c_name;
            $data['sort'] = $c_sort;
            $data['is_default'] = $is_default;
            if($p_id){
                $data['parent_id'] = $p_id;
            }
            if(!$c_id){
                $data['created_at'] = time();
                $res = Categorys::create($data);
            }else{
                $data['updated_at'] = time();
                $res = Categorys::where('category_id',$c_id)->update($data);
            }
            if($res){
                $code = array('dec'=>$this->success,'data'=>$is_default);
            }else{
                $code = array('dec'=>$this->error);
            }
        }else{
            $code = array('dec' => $this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    public function delCategory(Request $request){
        $c_id = $request->input('id');
        if($c_id){
            //删除子集
            Categorys::where('parent_id',$c_id)->delete();
            $res = Categorys::where('category_id',$c_id)->delete();
            if($res){
                $code = array('dec'=>$this->success);
            }else{
                $code = array('dec'=>$this->error);
            }
        }else{
            $code = array('dec' => $this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }


    /**
     * 供后台选择分类
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_choise_all_categorys(Request $request){
        $res = Categorys::orderBy('sort','asc')->get()->toArray();
        $data = [];
        foreach ($res as $key=>$value){
            if($value['parent_id']==0){
                $item['label'] = $value['category_name'];
                $item['value'] = $value['category_id'];
//                $item['children'] = $this->get_choise_childres($value['category_id'],$res);
                $data[] = $item;
            }
        }
        $code = array('dec' => $this->success, 'data' => $data);
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }
    private function get_choise_childres($parent_id,$list){
        $childre = [];
        foreach ($list as $item){
            if($item['parent_id']==$parent_id){
                $it['label'] = $item['category_name'];
                $it['value'] = $item['category_id'];
                $childre[] = $it;
            }
        }
        return $childre;
    }
    public function get_query_user(Request $request){
        if($request->input('key')){
              $result = Users::where('real_name', 'like', '%'.$request->input('key').'%')->select('real_name as label','user_id as value')->where('is_deleted',0)->get()->toArray();
        }else{
            $result = [];
        }
        $code = array('dec' => $this->success, 'data' => $result);
        return response()->json($code);
    }

    public function add_answer(Request $request){
        $user_id = $request->input('user_id');
        $forum_ids = $request->input('forum_ids');
        $qa_title = $request->input('qa_title');
        $content = $request->input('content');
        $courses_id = $request->input('courses_id');
        if($user_id && $forum_ids && $qa_title && $content && $courses_id){
            $request_path = '/answer/addAnswer';
            $request_url = config('C.API_URL').$request_path;
            $params = [
                'user_id'=>$user_id,
                'forum_ids'=>$forum_ids,
                'qa_title'=>$qa_title,
                'content'=>$content,
                'courses_id'=>$courses_id
            ];
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $code = json_decode($response);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
}