<?php
/**
 * Created by PhpStorm.
 * User: gavinsir
 * Date: 2018/7/5
 * Time: 下午2:03
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Carts;
use App\Modules\Comments;
use App\Modules\Courses;
use App\Modules\Favorites;
use App\Modules\Foots;
use App\Modules\Lecturers;
use App\Modules\Praises;
use App\Modules\Users_courses_relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CoursesController extends Controller{

    /***
     * 获取推荐课程列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function get_home_courses_list(Request $request){
        $page_index = $request->input('page_index')??1;//页码
        $page_number = $request->input('page_number')??10;//每页显示
        //初始化sql
        $sql = Courses::where('status',1)->where('is_home',1)->orderBy('opened_at','desc')->orderBy('created_at','desc');
        //附加条件,模糊查询 课程标题、讲师姓名或昵称
        if($request->input('keyword')){
            $key = $request->input('keyword');
            $sql = $sql->where(function ($query) use($key){
                $query->where('title','like','%'.$key.'%')
                    ->orWhere('lecturer_name','like','%'.$key.'%');
            });
        }
        $total = $sql->count();
        $list = $sql->select('course_id','title','description','lecturer_name','cover','old_price','now_price','video_url','opened_at','closed_at','created_at')
            ->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();

//        array_walk_recursive($list, $this->convertNull());
        foreach ($list as $key=>$value){
            //此微课的点赞数量
            $list[$key]['praise_num'] = Praises::where('course_id',$value['course_id'])->count();
            //当前登录用户是否已经点过赞
            if($request->input('login_user')){
                $exits = Praises::where('from_user',$request->input('login_user'))->where('course_id',$value['course_id'])->exists();
                if($exits){
                    $list[$key]['is_praise'] = 1;//已经点赞
                }else{
                    $list[$key]['is_praise'] = 0;//未点赞
                }
            }
        }
        $code = array('dec' => $this->success, 'data' => $list,'total'=>$total);
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }
    /***
     * 获取课程列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function get_courses_list(Request $request){
        $page_index = $request->input('page_index')??1;//页码
        $page_number = $request->input('page_number')??10;//每页显示
        //初始化sql
        $sql = Courses::where('status',1)->orderBy('opened_at','desc')->orderBy('created_at','desc');
        //附加条件,模糊查询 课程标题、讲师姓名或昵称
        if($request->input('keyword')){
            $key = $request->input('keyword');
            $sql = $sql->where(function ($query) use($key){
                $query->where('title','like','%'.$key.'%')
                    ->orWhere('lecturer_name','like','%'.$key.'%');
            });
        }
        $total = $sql->count();
        $list = $sql->select('course_id','title','description','lecturer_name','cover','old_price','now_price','video_url','opened_at','closed_at','created_at')
            ->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();
        foreach ($list as $key=>$value){
            //此微课的点赞数量
            $list[$key]['praise_num'] = Praises::where('course_id',$value['course_id'])->count();
            //当前登录用户是否已经点过赞
            if($request->input('login_user')){
                $exits = Praises::where('from_user',$request->input('login_user'))->where('course_id',$value['course_id'])->exists();
                if($exits){
                    $list[$key]['is_praise'] = 1;//已经点赞
                }else{
                    $list[$key]['is_praise'] = 0;//未点赞
                }
            }
        }
        $code = array('dec' => $this->success, 'data' => $list,'total'=>$total);
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    /***
     * 获取课程详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function get_course_detail(Request $request){
        if($request->input('course_id')){
            $course = Courses::where('course_id',$request->input('course_id'))->first();
            if($course){
                if($course->status==0){
                    $code = array('dec'=>$this->course_disable_err);
                }elseif ($course->status==2){
                    $code = array('dec'=>$this->course_close_err);
                }else{
                    $result['course_id']=$course->course_id;
                    $result['title']=$course->title;
                    $result['description']=$course->description;
                    $result['lecturer_id']=$course->lecturer_id;
                    $result['lecturer_name']=$course->lecturer_name;
                    $result['cover']=$course->cover;
                    $result['old_price']=$course->old_price;
                    $result['now_price']=$course->now_price;
                    $result['video_url']=$course->video_url;
                    $result['is_home']=$course->is_home;
                    $result['opened_at']=$course->opened_at;
                    $result['closed_at']=$course->closed_at;
                    //课程讲师信息
                    if($course->lecturer_id){
                        $lecturer = Lecturers::where('lecturer_id',$course->lecturer_id)->select('description')->first();
                        if($lecturer){
                            $result['lecturer_desc']=$lecturer->description??'没有简介';
                        }else{
                            $result['lecturer_desc']='没有简介';
                        }
                    }
                    //此微课的点赞数量
                    $result['praise_num'] = Praises::where('course_id',$course->course_id)->count();
                    //当前登录用户是否已经点过赞
                    if($request->input('login_user')){
                        $exits = Praises::where('from_user',$request->input('login_user'))->where('course_id',$course->course_id)->exists();
                        if($exits){
                            $result['is_praise'] = 1;//已经点赞
                        }else{
                            $result['is_praise'] = 0;//未点赞
                        }
                    }
                    $code = array('dec'=>$this->success,'data'=>$result);
                }
            }else{
                $code = array('dec'=>$this->course_nothing_err);
            }
        }
        else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    /***
     * 获取微课评论列表(N条)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function get_course_comments(Request $request){
        if($request->input('course_id')){
            //查询只针对课程的评价
            $page_index = $request->input('page_index')??1;//页码
            $page_number = $request->input('page_number')??5;
            $sql = Comments::where('course_id',$request->input('course_id'))->whereNull('parent_id');
            $total = $sql->count();
            $list =$sql->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();
            //查询列表的回复列表
            foreach($list as $key=>$value){
                //此评论的点赞数量
                $list[$key]['praise_num'] = Praises::where('comment_id',$value['comment_id'])->count();
                //当前登录用户是否已经点过赞
                if($request->input('login_user')){
                    $exits = Praises::where('from_user',$request->input('login_user'))->where('comment_id',$value['comment_id'])->exists();
                    if($exits){
                        $list[$key]['is_praise'] = 1;//已经点赞
                    }else{
                        $list[$key]['is_praise'] = 0;//未点赞
                    }
                }
                //此条评论的评论
                $list[$key]['childrens']  = $this->get_children_comments($value['comment_id']);
            }
            $code = array('dec'=>$this->success,'data'=>$list,'total'=>$total);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    /***
     *课程点赞
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function add_course_praise(Request $request){
        if($request->input('course_id') && $request->input('login_user')){
            //判断是否重复点赞
            $exist = Praises::where('course_id',$request->input('course_id'))->where('from_user',$request->input('login_user'))->exists();
            if($exist){
                $code = array('dec'=>$this->praise_err);
            }else{
                $praise['course_id'] = $request->input('course_id');
                $praise['from_user'] = $request->input('login_user');
                $praise['created_at'] = time();
                $res = Praises::create($praise);
                if($res){
                    $code = array('dec'=>$this->success);
                }else{
                    $code = array('dec'=>$this->error);
                }
            }
        }else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    /***
     * 评论点赞
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function add_comment_praise(Request $request){
        if($request->input('comment_id') && $request->input('login_user')){
            //判断是否重复点赞
            $exist = Praises::where('comment_id',$request->input('course_id'))->where('from_user',$request->input('login_user'))->exists();
            if($exist){
                $code = array('dec'=>$this->praise_err);
            }else{
                $praise['comment_id'] = $request->input('comment_id');
                $praise['from_user'] = $request->input('login_user');
                $praise['created_at'] = time();
                $res = Praises::create($praise);
                if($res){
                    $code = array('dec'=>$this->success);
                }else{
                    $code = array('dec'=>$this->error);
                }
            }
        }else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }
    /***
     * 提交评论
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function add_course_comment(Request $request){
        if($request->input('course_id') && $request->input('login_user')){
            $savedata['course_id'] = $request->input('course_id');
            $savedata['from_user'] = $request->input('login_user');
            $savedata['content'] = $request->input('content');
            if($request->input('comment_id')){
                //针对于某条评论进行评论
                $savedata['parent_id']  = $request->input('comment_id');
                //针对于某条评论的评论进行评论
                if($request->input('to_user')){
                    $savedata['to_user'] = $request->input('to_user');
                }
            }
            $savedata['created_at'] = time();
            $res = Comments::create($savedata);
            if($res){
                $code= array('dec'=>$this->success);
            }else{
                $code= array('dec'=>$this->error);
            }
        }else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    /***
     * 获取讲师列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function get_lecturer_list(Request $request){
        $page_index = $request->input('page_index')??1;//页码
        $page_number = $request->input('page_number')??10;//每页显示
        //初始化sql
        $sql = Lecturers::where('status',1)->orderBy('created_at','desc');
        //附加条件,模糊查询 课程标题、讲师姓名或昵称
        if($request->input('keyword')){
            $key = $request->input('keyword');
            $sql = $sql->where(function ($query) use($key){
                $query->where('lecturer_name','like','%'.$key.'%')
                    ->orWhere('lecturer_title','like','%'.$key.'%')
                    ->orWhere('description','like','%'.$key.'%');
            });
        }
        $total = $sql->count();
        $list = $sql->select('lecturer_id','lecturer_name','description','lecturer_title','created_at','status')
            ->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();
        $code = array('dec' => $this->success, 'data' => $list,'total'=>$total);
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }
    /**
     * 上传文件（课程封皮，讲师头像）
     * @param Request $request [description]
     */
    public function Upload(Request $request){
        if (!$request->hasFile('file')) {
            $code = array('dec' => $this->http_file_err);
        } else {
            $file = $request->file('file');
            if ($file->isValid()) {
                //检查mime
                $fi = new \finfo(FILEINFO_MIME_TYPE);
                if (!$this->_isImg($fi->file($file->getPathname()))) return response()->json(['dec' => $this->http_mime_err]);
                $path = config('C.IMG_URL');
                // if(!$request->input('file_type')){
                //     $path = $path . $request->input('file_type').'/';
                // }else{
                //     $path = $path;
                // }
                $file_prefix = date("YmdHis").rand(100, 200);
                $extension = $file->getClientOriginalExtension();
                $filename = $file_prefix . '.' . $extension;
                $file->move($path, $filename);
                $avator = $path.$filename;
                $code = array('dec' => $this->success,'data'=>$avator);
            }else{
                $code = array('dec' => $this->http_file_err);
            }
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }
    /**
     * 删除文件
     * @param Request $request [description]
     */
    public function RemoveFile(Request $request){
        if($request->input("file_path")){
            $path = $request->input("file_path");
            //删除图片
            try{
//                \Illuminate\Support\Facades\File::
                if(File::exists($path)){
                    File::delete($path);
                }
                $code = array('dec' => $this->success);
            }catch(\Excepiton $e){
                $code = array('dec'=>$this->error);
            }
        }else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }
    /**
     * 保存微课
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function save_course(Request $request){
        if($request->input("title") && $request->input("description") && $request->input("lecturer_id") && $request->input("lecturer_name") && $request->input("cover") && $request->input("video_url") && $request->input('opened_at') && $request->input('closed_at')){
            $save_data['title']         = $request->input('title');
            $save_data['description']   = $request->input('description');
            $save_data['lecturer_id']   = $request->input('lecturer_id');
            $save_data['lecturer_name'] = $request->input('lecturer_name');
            $save_data['cover']         = $request->input('cover');
            $save_data['video_url']     = $request->input('video_url');
            $save_data['opened_at']     = $request->input('opened_at');
            $save_data['closed_at']     = $request->input('closed_at');
            if($request->input('old_price')){
                $save_data['old_price'] = $request->input('old_price');
            }
            if($request->input('now_price')){
                $save_data['now_price'] = $request->input('now_price');
            }
            if($request->input('is_live')){
                $save_data['is_live'] = $request->input('is_live');
            }
            if($request->input('is_good')){
                $save_data['is_good'] = $request->input('is_good');
            }
            if($request->input('is_home')){
                $save_data['is_home'] = $request->input('is_home');
            }
            $res = false;
            if($request->input('course_id')){
                //修改
                $res = Courses::where('course_id',$request->input("course_id"))->update($save_data);
            }else{
                //添加
                $save_data['created_at'] = $save_data['updated_at'] = time();
                $res = Courses::create($save_data);
            }
            if($res){
                $code = array('dec' => $this->success);
            }else{
                $code = array('dec'=>$this->error);
            }
        }else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    /**
     * 修改微课状态
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function edit_course_status(Request $request){
        $status = $request->input("status");
        if($request->input("course_id") && isset($status)){
            $data["status"] = status;
            $res = Courses::where('course_id',$request->input("course_id"))->update($$data);

            if($res){
                $code = array('dec' => $this->success);
            }else{
                $code = array('dec'=>$this->error);
            }
        }else{
            $code = array('dec'=>$this->client_err);
        }

        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }
}