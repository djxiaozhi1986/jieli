<?php
/**
 * Created by PhpStorm.
 * User: gavinsir
 * Date: 2018/7/5
 * Time: 下午2:03
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\libraries\HttpClient;
use App\Modules\Carts;
use App\Modules\Comments;
use App\Modules\Courses;
use App\Modules\Favorites;
use App\Modules\Foots;
use App\Modules\Lecturers;
use App\Modules\Praises;
use App\Modules\Sections;
use App\Modules\Users;
use App\Modules\Users_courses_relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $list = $sql->select('course_id','title','description','lecturer_name','cover','coin_price','now_price','audio_url','opened_at','closed_at','created_at')
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
        $type = $request->input('type')."";
        $status = $request->input('status')."";
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
        if($type!="-1"){
            switch ($type){
                case "0":
                    $sql = $sql->where('is_good',1);
                    break;
                case "1":
                    $sql = $sql->where('is_live',1);
                    break;
            }
        }
        if($status!="-1"){
            $now = time();
            switch ($status){
                case "0":
                    //未开始
                    $sql = $sql->where('opened_at','>',$now);
                    break;
                case "1":
                    //进行中
                    $sql = $sql->where('opened_at','<=',$now)->where('closed_at','>=',$now);
                    break;
                case "2":
                    //已经结束
                    $sql = $sql->where('closed_at','<',$now);
                    break;
            }
        }
        $total = $sql->count();
        $list = $sql->select('course_id','title','description','lecturer_name','cover','is_live','is_good','coin_price','now_price','audio_url','opened_at','closed_at','created_at','is_publish','im_group_id')
            ->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();
        foreach ($list as $key=>$value){
            //计算课程状态
            $now = time();
            if($now<=$value['closed_at'] && $now>=$value['opened_at']){
                //课程正在直播
                $list[$key]['status']='进行中';
            }else if($now<$value['opened_at']){
                //未开始
                $list[$key]['status']='未开始';
            }else if($now>$value['closed_at']){
                //已经结束
                $list[$key]['status']='已结束';
            }else{
                //未知
                $result['status']='未知';
            }
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
//        course_id:undefined,
//        title:'',
//        description:'',
//        lecturer_id:undefined,
//        lecturer_name:'',
//        cover:undefined,
//        coin_price:0,
//        now_price:0,
//        is_good:false,
//        is_home:false,
//        is_oa:false,
//        is_try:false,
//        opened_at:undefined,
//        closed_at:undefined,
//        c_ids:[],
//        opened_at_date:new Date(),
//        opened_at_time:new Date(),
//        closed_at_date:new Date((new Date()/1000+86400)*1000),
//        closed_at_time:new Date()

        if($request->input('course_id')){
            $course = Courses::where('course_id',$request->input('course_id'))->first();
            if($course){
                $result['course_id']=$course->course_id;
                $result['title']=$course->title;
                $result['description']=$course->description;
                $result['lecturer_id']=$course->lecturer_id;
                $result['lecturer_name']=$course->lecturer_name;
                $result['coin_price']=$course->coin_price;
                $result['now_price']=$course->now_price;
                if($course->cover){
                    $result['cover']=$course->cover;
                }
                $result['is_home']=$course->is_home;
                $result['is_live']=$course->is_live;
                $result['opened_at_date']=$course->opened_at;
                $result['opened_at_time']=$course->opened_at;
                $result['closed_at_date']=$course->closed_at;
                $result['closed_at_time']=$course->created_at;
                $result['is_oa']=$course->is_oa;//是否开源
                $result['is_good']=$course->is_good;
                $result['is_try']= $course->is_try;
                $result['view_level']= $course->view_level;
                $result['c_ids'][] = $course->c_id;

                $code = array('dec'=>$this->success,'data'=>$result);
            }
        }
        else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }


    public function get_course_detail1(Request $request){
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
                    $result['coin_price']=$course->coin_price;
                    $result['now_price']=$course->now_price;
                    if($course->cover){
                        $result['cover']=config('C.DOMAIN').$course->cover;
                    }
                    $result['is_home']=$course->is_home;
                    $result['is_live']=$course->is_live;
                    $result['opened_at']=$course->opened_at;
                    $result['closed_at']=$course->closed_at;
                    $result['created_at']=$course->created_at;
                    $result['is_oa']=$course->is_oa;//是否开源
                    $result['is_publish']=$course->is_publish;//是否开源
                    //计算课程是否是主讲人自己的课程
                    $result['is_me']=0;
                    $result['is_fav']= 0;
                    $result['is_buy'] = 0;
                    if($request->input('login_user')){
                        if($request->input('login_user')==$course->lecturer_id){
                            $result['is_me']=1;
                        }else{
                            //计算当前用户是否已经购买了此课程
                            //微课订单中查看,不包含讲师自己，该课程该用户已经购买成功
                            $order = Orders::where('course_id',$course->course_id)->where('user_id',$request->input('login_user'))->where('order_status',1)->first();
                            if($order){
                                $result['is_buy'] = 1;
                            }
                        }
                        //判断是否收藏
                        $fav = Favorites::where('course_id',$course->course_id)->where('user_id',$request->input('login_user'))->first();
                        if($fav){
                            $result['is_fav'] = 1;
                        }
                    }
                    //判断是否为线下课程
                    $result['is_online'] = 1;//线上
                    $sec_count = Sections::where('course_id',$course->course_id)->count();
                    if($sec_count>0){
                        $result['is_online'] = 0;//线下
                    }
                    //计算课程状态
                    $now = time();
                    if($now<=$course->closed_at && $now>=$course->opened_at){
                        //课程正在直播
                        $result['status']=1;
                    }else if($now<$course->opened_at){
                        //未开始
                        $result['status']=0;
                    }else if($now>$course->closed_at){
                        //已经结束
                        $result['status']=2;
                    }else{
                        //未知
                        $result['status']=-1;
                    }

                    //课程讲师信息
                    if($course->lecturer_id){
                        //获取讲师信息
                        $lecturer = Users::where('user_type',2)->where('user_id',$course->lecturer_id)->select('user_id','nick_name','user_title','real_name','user_level','user_face','phone','intro','award')->first();
//                        $lecturer = Lecturers::where('lecturer_id',$course->lecturer_id)->select('description','lecturer_avator')->first();
                        if($lecturer){
                            $result['lecturer_name']=$lecturer->real_name;
                            $result['lecturer_avator']=$lecturer->user_face;
                            $result['lecturer_phone']=$lecturer->phone;
                            $result['lecturer_intro']=$lecturer->intro??'没有简介';
                            $result['lecturer_award'] = $lecturer->award;
                            $result['lecturer_title'] = $lecturer->user_title;
                        }else{
                            $result['lecturer_name']='';
                            $result['lecturer_avator']='';
                            $result['lecturer_phone']='';
                            $result['lecturer_intro']='';
                            $result['lecturer_award']='';
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
    /**
     * 课程章节列表
     * @param Request $request page_index 可选，不传获取所有，pagesize=10
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_course_sections(Request $request){
        $course_id = $request->input('course_id');
        $page_index = $request->input('page_index')??1;//页码
        $page_number = $request->input('page_number')??10;//每页显示
        if($course_id){
            $sql = Sections::where('course_id',$course_id);
            $total = $sql->count();
//            var_dump($request->input('course_id'));die;
            $sort = 'asc';
            if($request->input('sort')!=0){
                $sort = 'desc';
            }
            $sql = $sql->orderBy('order_index',$sort);
            $sql = $sql->skip(($page_index - 1) * $page_number)->take($page_number);

            $result = $sql->get()->toArray();

            $code = array('dec'=>$this->success,'data'=>$result,'total'=>$total);
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
//    public function get_lecturer_list(Request $request){
//        $page_index = $request->input('page_index')??1;//页码
//        $page_number = $request->input('page_number')??10;//每页显示
//        //初始化sql
//        $sql = Lecturers::where('status',1)->orderBy('created_at','desc');
//        //附加条件,模糊查询 课程标题、讲师姓名或昵称
//        if($request->input('keyword')){
//            $key = $request->input('keyword');
//            $sql = $sql->where(function ($query) use($key){
//                $query->where('lecturer_name','like','%'.$key.'%')
//                    ->orWhere('lecturer_title','like','%'.$key.'%')
//                    ->orWhere('description','like','%'.$key.'%');
//            });
//        }
//        $total = $sql->count();
//        $list = $sql->select('lecturer_id','lecturer_name','description','lecturer_title','created_at','status')
//            ->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();
//        $code = array('dec' => $this->success, 'data' => $list,'total'=>$total);
//        $json_str = json_encode($code);
//        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
//        return response()->json($res_json);
//    }

    /***
     * 获取所有讲师
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_lecturer_list(Request $request){
        $res = Users::where('user_type',2)->select('user_id','nick_name','user_title','real_name','user_level','user_face','phone','intro','award')->get()->toArray();
        $code = array('dec' => $this->success, 'data' => $res);
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
//                $fi = new \finfo(FILEINFO_MIME_TYPE);
//                if (!$this->_isImg($fi->file($file->getPathname()))) return response()->json(['dec' => $this->http_mime_err]);

                // if(!$request->input('file_type')){
                //     $path = $path . $request->input('file_type').'/';
                // }else{
                //     $path = $path;
                // }
                $path = config('C.IMG_URL');
                if($request->input('type') && $request->input('type')=="1"){
                    $path = config('C.FILE_URL');
                }
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
     * 上传文件（课程封皮，讲师头像）
     * @param Request $request [description]
     */
    public function UploadAudio(Request $request){
        if (!$request->hasFile('file')) {
            $code = array('dec' => $this->http_file_err);
        } else {
            $file = $request->file('file');
            if ($file->isValid()) {
                //检查mime
//                $fi = new \finfo(FILEINFO_MIME_TYPE);
//                if (!$this->_isAudio($fi->file($file->getPathname()))) return response()->json(['dec' => $this->http_mime_err]);

                // if(!$request->input('file_type')){
                //     $path = $path . $request->input('file_type').'/';
                // }else{
                //     $path = $path;
                // }
                $path = config('C.FILE_URL');
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
        if($request->input("title") && $request->input("description") && $request->input("lecturer_id") && $request->input("lecturer_name") && $request->input("cover")  && $request->input('opened_at') && $request->input('closed_at')){
            $save_data['title']         = $request->input('title');
            $save_data['description']   = $request->input('description');
            $save_data['lecturer_id']   = $request->input('lecturer_id');
            $save_data['lecturer_name'] = $request->input('lecturer_name');
            $save_data['cover']         = $request->input('cover');
//            $save_data['audio_url']     = $request->input('audio_url');
            $save_data['opened_at']     = $request->input('opened_at');
            $save_data['closed_at']     = $request->input('closed_at');
            $save_data['c_id']     = $request->input('c_id');
            if($request->input('coin_price')){
                $save_data['coin_price'] = $request->input('coin_price');
            }
            if($request->input('now_price')){
                $save_data['now_price'] = $request->input('now_price');
            }

            $save_data['is_good'] = $request->input('is_good');
            $save_data['is_home'] = $request->input('is_home');
            $save_data['is_oa'] = $request->input('is_oa');
            $save_data['is_live'] = $request->input('is_live');
            $save_data['is_try'] = $request->input('is_try');
            $save_data['view_level'] = $request->input('view_level');

            $res = false;
            if($request->input('course_id')){
                //修改
                $res = Courses::where('course_id',$request->input("course_id"))->update($save_data);
            }else{
                //添加
                $save_data['created_at'] = $save_data['updated_at'] = time();
                $res = Courses::insertGetId($save_data);
                if($res){
                    //创建微课群组
                    $channel = $this->create_im_group($request->input('title'),$request->input('lecturer_id'),$request->input('lecturer_name'));
                    $up_data['im_group_id'] = $channel->channelId;
                    Courses::where('course_id',$res)->update($up_data);
                }
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

    public function create_im_group_by_course_id(Request $request){
        if($request->input('course_id')){
            $course = Courses::where('course_id',$request->input('course_id'))->select('title','lecturer_id','lecturer_name','im_group_id')->first();
            if($course){
                if($course->im_group_id){
                    $code = array('dec'=> array('code' => '060112', 'msg' => '该微课已经创建过问答群组'));
                }else {
                    $channel = $this->create_im_group($course->title,$course->lecturer_id,$course->lecturer_name);
                    if($channel){
                        $up_data['im_group_id'] = $channel->channelId;
                        $res = Courses::where('course_id',$request->input('course_id'))->update($up_data);
                        if($res){
                            $code = array('dec'=>$this->success);
                        }else{
                            $code = array('dec'=>$this->error);
                        }
                    }else{
                        $code = array('dec'=> array('code' => '060111', 'msg' => '问答群组创建失败'));
                    }
                }
            }else{
                $code = array('dec'=>$this->course_nothing_err);
            }
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function create_im_group($group_name,$expert_id,$expert_name){
        $request_path = '/app/createChannel';
        $request_url = config('C.API_URL') . $request_path;
        $members = array();
        $item['id'] = $expert_id;
        $item['nickname'] = $expert_name;
        $item['index'] = 0;
        $members[] = $item;
        $response = HttpClient::api_request($request_url, ['name'=>$group_name,'purpose'=>'微课《'.$group_name.'》实时问答','members'=>$members], 'POST', true);
        $res = json_decode($response);
        $channel = null;
        if($res->dec->code=='000000'){
            $channel = $res->data;
        }
        return $channel;
    }

    /**
     * 直播改为线下微课
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function edit_course_audio(Request $request)
    {
        if($request->input("course_id") && $request->input("audio_url")){
            $save_data['is_live'] = 0;//停止直播
            $save_data['audio_url'] = $request->input("audio_url");//线下组合音频文件
            $res = Courses::where('course_id',$request->input("course_id"))->update($save_data);
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
            $res = Courses::where('course_id',$request->input("course_id"))->update($data);

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
     * 删除微课
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function del_course(Request $request){
        if($request->input("course_id")){
            $res = Courses::where('course_id',$request->input("course_id"))->delete();
            if($res){
                //删除章节
                Sections::where('course_id',$request->input("course_id"))->delete();
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
     * 保存讲师信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save_lecturer(Request $request){
        if($request->input("lecturer_title") && $request->input("description")
            && $request->input("lecturer_name") && $request->input("lecturer_avator")){
            $save_data['lecturer_title']  = $request->input('lecturer_title');
            $save_data['description']     = $request->input('description');
            $save_data['lecturer_name']   = $request->input('lecturer_name');
            $save_data['lecturer_avator']  = $request->input('lecturer_avator');
            $res = false;
            if($request->input('lecturer_id')){
                //修改
                $res = Lecturers::where('lecturer_id',$request->input("lecturer_id"))->update($save_data);
            }else{
                //添加
                $save_data['created_at'] = $save_data['updated_at'] = time();
                $res = Lecturers::create($save_data);
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
     * 删除微课
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function del_lecturer(Request $request){
        if($request->input("lecturer")){
            $res = Lecturers::where('lecturer',$request->input("lecturer"))->delete();
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

    public function get_all_category(Request $request){
        $request_path = '/classify/allList';
        $request_url = config('C.API_URL').$request_path;
        $response = HttpClient::api_request($request_url,[],'POST',true);
        $code = json_decode($response);
        return response()->json($code);
    }

    public function add_section(Request $request){
        $course_id = $request->input('course_id');
        $audio_url = $request->input('audio_url');
        $title = $request->input('title');
        $price = $request->input('price');
        $cion = $request->input('cion');
        $is_try = $request->input('is_try');
        $order_index = $request->input('order_index');
        if($course_id && $audio_url && $title){
            $data['course_id'] = $course_id;
            $data['audio_url'] = $audio_url;
            $data['title'] = $title;
            $data['price'] = $price??0;
            $data['cion'] = $cion??0;
            $data['is_try'] = $is_try??0;
            $data['order_index'] = $order_index??0;
            $data['created_at'] = time();
            $res = Sections::create($data);
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
    public function del_section(Request $request){
        $id = $request->input('id');
        if($id){
            $res = Sections::where('section_id',$id)->delete();
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
    public function publish(Request $request){
        $course_id = $request->input('course_id');
        if($course_id){
            $data['is_publish'] = 1;
            $res = Courses::where('course_id',$course_id)->update($data);
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
    public function get_check_course_comments(Request $request){
        if($request->input('course_id')){
            //查询只针对课程的评价
            $sql = Comments::where('course_id',$request->input('course_id'))->where('is_verify',0);
            $total = $sql->count();
            $list =$sql ->select('comment_id','course_id', 'parent_id', 'content', 'from_user','from_user_name','to_user','to_user_name','created_at','praise_count as praise_num',DB::raw('CONCAT("http://118.26.164.109:81/uploads/face/",jl_user.user_face)  as from_user_face'))
                ->leftJoin('user','user.user_id','courses_comments.from_user')
                ->get()->toArray();
            $code = array('dec'=>$this->success,'data'=>$list,'total'=>$total);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }

    public function get_refuse_course_comments(Request $request){
        if($request->input('course_id')){
            //查询只针对课程的评价
            $sql = Comments::where('course_id',$request->input('course_id'))->where('is_verify',-1);
            $total = $sql->count();
            $list =$sql ->select('comment_id','course_id', 'parent_id', 'content', 'from_user','from_user_name','to_user','to_user_name','created_at','praise_count as praise_num',DB::raw('CONCAT("http://118.26.164.109:81/uploads/face/",jl_user.user_face)  as from_user_face'))
                ->leftJoin('user','user.user_id','courses_comments.from_user')
                ->get()->toArray();
            $code = array('dec'=>$this->success,'data'=>$list,'total'=>$total);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function get_pass_course_comments(Request $request){
        if($request->input('course_id')){
            //查询只针对课程的评价
            $sql = Comments::where('course_id',$request->input('course_id'))->where('is_verify',1);
            $total = $sql->count();
            $list =$sql ->select('comment_id','course_id', 'parent_id', 'content', 'from_user','from_user_name','to_user','to_user_name','created_at','praise_count as praise_num',DB::raw('CONCAT("http://118.26.164.109:81/uploads/face/",jl_user.user_face)  as from_user_face'))
                ->leftJoin('user','user.user_id','courses_comments.from_user')
                ->get()->toArray();
            $code = array('dec'=>$this->success,'data'=>$list,'total'=>$total);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }

    private function get_children_comments($parent_id,&$childrens){
        if($parent_id){
            $result = Comments::where('parent_id',$parent_id)
                ->select('courses_comments.comment_id','courses_comments.content','courses_comments.from_user','courses_comments.from_user_name','courses_comments.to_user','courses_comments.to_user_name','courses_comments.created_at','courses_comments.praise_count as praise_num',DB::raw('CONCAT("http://118.26.164.109:81/uploads/face/",jl_user.user_face)  as from_user_face'))
                ->leftJoin('user','user.user_id','courses_comments.from_user')->where('is_verify',0)->get()->toArray();
            foreach ($result as $key=>$value){
                $childrens[] = $value;
                //评论加到同级别
                $this->get_children_comments($value['comment_id'],$childrens);
            }
        }
    }

    public function comment_verify(Request $request){
        $comment_id = $request->input('comment_id');
        $verify = $request->input('verify');
        if($comment_id){
            $saved["is_verify"] = $verify;
            $res = Comments::where('comment_id',$comment_id)->update($saved);
            if($res){
                $code = array('dec'=>$this->success);
            }
            else{
                $code = array('dec'=>$this->error);
            }
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
}