<?php
/**
 * Created by PhpStorm.
 * User: gavinsir
 * Date: 2018/7/5
 * Time: 下午2:03
 */
namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Modules\Carts;
use App\Modules\Comments;
use App\Modules\Courses;
use App\Modules\Favorites;
use App\Modules\Foots;
use App\Modules\Lecturers;
use App\Modules\Orders;
use App\Modules\Praises;
use App\Modules\Sections;
use App\Modules\Users;
use App\Modules\Users_courses_relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoursesController extends Controller{

    /**
     * 分类课程
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_courses_by_category(Request $request){
        $page_index = $request->input('page_index')??1;//页码
        $page_number = $request->input('page_number')??10;//每页显示
        if($request->input('c_id')){
            //初始化sql
            $sql = Courses::where('is_publish',1)->where('c_id',$request->input('c_id'))->orderBy('opened_at','desc')->orderBy('created_at','desc');
            //附加条件,模糊查询 课程标题、讲师姓名或昵称
            if($request->input('keyword')){
                $key = $request->input('keyword');
                $sql = $sql->where(function ($query) use($key){
                    $query->where('title','like','%'.$key.'%')
                        ->orWhere('lecturer_name','like','%'.$key.'%');
                });
            }
            $total = $sql->count();
            $list = $sql->select('course_id','title','description','lecturer_name',DB::raw('CONCAT("'.config('C.DOMAIN').'",cover) as cover'),'is_live','is_oa','coin_price','now_price','audio_url','opened_at','closed_at','created_at')
                ->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();
            foreach ($list as $key=>$value){
                //此微课的点赞数量
                $list[$key]['praise_num'] = Praises::where('course_id',$value['course_id'])->count();
                $list[$key]['is_praise'] = 0;
                //当前登录用户是否已经点过赞
                if($request->input('login_user')){
                    $exits = Praises::where('from_user',$request->input('login_user'))->where('course_id',$value['course_id'])->exists();
                    if($exits){
                        $list[$key]['is_praise'] = 1;//已经点赞
                    }
                }
            }
            $code = array('dec' => $this->success, 'data' => $list,'total'=>$total);
        }else{
            $code = array('dec'=>$this->client_err);
        }

        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }
    /***
     * 获取推荐课程列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function get_home_courses_list(Request $request){
        $page_index = $request->input('page_index')??1;//页码
        $page_number = $request->input('page_number')??10;//每页显示
        //初始化sql
        $sql = Courses::where('status',1)->where('is_home',1)->where('is_publish',1)->orderBy('opened_at','desc')->orderBy('created_at','desc');
        //附加条件,模糊查询 课程标题、讲师姓名或昵称
        if($request->input('keyword')){
            $key = $request->input('keyword');
            $sql = $sql->where(function ($query) use($key){
                $query->where('title','like','%'.$key.'%')
                    ->orWhere('lecturer_name','like','%'.$key.'%');
            });
        }
        $total = $sql->count();
        $list = $sql->select('course_id','title','description','lecturer_name',DB::raw('CONCAT("'.config('C.DOMAIN').'",cover)  as cover'),'is_live','is_oa','coin_price','now_price','audio_url','opened_at','closed_at','created_at')
            ->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();

//        array_walk_recursive($list, $this->convertNull());
        foreach ($list as $key=>$value){
            //此微课的点赞数量
            $list[$key]['praise_num'] = Praises::where('course_id',$value['course_id'])->count();
            $list[$key]['is_praise'] = 0;//未点赞
            //当前登录用户是否已经点过赞
            if($request->input('login_user')){
                $exits = Praises::where('from_user',$request->input('login_user'))->where('course_id',$value['course_id'])->exists();
                if($exits){
                    $list[$key]['is_praise'] = 1;//已经点赞
                }
            }
        }
        $code = array('dec' => $this->success, 'data' => $list,'total'=>$total);
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    /***
     * 登录讲师的课程
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function get_lecturer_courses_list(Request $request){
        $page_index = $request->input('page_index')??1;//页码
        $page_number = $request->input('page_number')??10;//每页显示
        $login_user = $request->input('login_user');//讲师id
        if($login_user){
            //初始化sql
            $sql = Courses::where('status',1)->where('is_home',1)->where('is_publish',1)->where('lecturer_id',$login_user)->orderBy('opened_at','desc')->orderBy('created_at','desc');
            $total = $sql->count();
            $list = $sql->select('course_id','title','description','lecturer_name',DB::raw('CONCAT("'.config('C.DOMAIN').'",cover)  as cover'),'is_live','is_oa','coin_price','now_price','audio_url','opened_at','closed_at','created_at')
                ->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();
            foreach ($list as $key=>$value){
                //此微课的点赞数量
                $list[$key]['praise_num'] = Praises::where('course_id',$value['course_id'])->count();
                $list[$key]['is_praise'] = 0;//未点赞
                //当前登录用户是否已经点过赞
                if($request->input('login_user')){
                    $exits = Praises::where('from_user',$request->input('login_user'))->where('course_id',$value['course_id'])->exists();
                    if($exits){
                        $list[$key]['is_praise'] = 1;//已经点赞
                    }
                }
            }
            $code = array('dec' => $this->success, 'data' => $list,'total'=>$total);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }
    /***
     * 登录用户购买的课程
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function get_user_courses_list(Request $request){
        $page_index = $request->input('page_index')??1;//页码
        $page_number = $request->input('page_number')??10;//每页显示
        $login_user = $request->input('login_user');//讲师id
        if($login_user){
            //初始化sql
            $sql = Orders::where('courses_orders.user_id',$login_user)->where('courses_orders.order_status',1)->orderBy('courses_orders.completed_at','desc')
                    ->leftJoin('courses','courses.course_id','courses_orders.course_id');
            $total = $sql->count();
            $list = $sql->select('courses.course_id','courses.title','courses.description','courses.lecturer_name',DB::raw('CONCAT("'.config('C.DOMAIN').'",jl_courses.cover)  as cover'),'courses.is_live','courses.is_oa','courses.coin_price','courses.now_price','courses.audio_url','courses.opened_at','courses.closed_at','courses.created_at')
                ->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();
            foreach ($list as $key=>$value){
                //此微课的点赞数量
                $list[$key]['praise_num'] = Praises::where('course_id',$value['course_id'])->count();
                $list[$key]['is_praise'] = 0;//未点赞
                //当前登录用户是否已经点过赞
                if($request->input('login_user')){
                    $exits = Praises::where('from_user',$request->input('login_user'))->where('course_id',$value['course_id'])->exists();
                    if($exits){
                        $list[$key]['is_praise'] = 1;//已经点赞
                    }
                }
            }
            $code = array('dec' => $this->success, 'data' => $list,'total'=>$total);
        }else{
            $code = array('dec'=>$this->client_err);
        }
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
        $list = $sql->select('course_id','title','description','lecturer_name',DB::raw('CONCAT("'.config('C.DOMAIN').'",cover)  as cover'),'is_live','is_oa','coin_price','now_price','audio_url','opened_at','closed_at','created_at')
            ->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();
        foreach ($list as $key=>$value){
            //此微课的点赞数量
            $list[$key]['praise_num'] = Praises::where('course_id',$value['course_id'])->count();
            $list[$key]['is_praise'] = 0;//未点赞
            //当前登录用户是否已经点过赞
            if($request->input('login_user')){
                $exits = Praises::where('from_user',$request->input('login_user'))->where('course_id',$value['course_id'])->exists();
                if($exits) {
                    $list[$key]['is_praise'] = 1;//已经点赞
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
//                    $result['description']=$course->description;
                    $dec= htmlspecialchars_decode($course->description)//把一些预定义的 HTML 实体转换为字符
                    $dec = str_replace("&nbsp;","",$dec);//将空格替换成空
                    $result['description'] = strip_tags($dec);//函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容
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
                    //收藏总数
                    $result['fav_count'] = Favorites::where('course_id',$course->course_id)->count();
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
                            if($lecturer->user_face){
                                $index = strpos($lecturer->user_face,"http");
                                if($index === false){
                                    $result['lecturer_avator']="http://118.26.164.109:81/uploads/face/".$lecturer->user_face;
                                }else{
                                    $result['lecturer_avator']=$lecturer->user_face;
                                }
                            }else{
                                $result['lecturer_avator']='';
                            }
                            $result['lecturer_phone']=$lecturer->phone;
                            $result['lecturer_intro']=$lecturer->intro;
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
                    $result['is_praise'] = 0;//未点赞
                    //当前登录用户是否已经点过赞
                    if($request->input('login_user')){
                        $exits = Praises::where('from_user',$request->input('login_user'))->where('course_id',$course->course_id)->exists();
                        if($exits){
                            $result['is_praise'] = 1;//已经点赞
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
    /**
     * 同类购买（购买此课程还购买了哪些）
     * @param Request $request
     */
    public function similar_course_list(Request $request){
        if($request->input('course_id')){
            $order_users = Orders::where('course_id',$request->input('course_id'))->where('order_status',1)->select('user_id')->get()->toArray();
            $user_ids = [];
            foreach ($order_users as $u){
                if(!in_array($u['user_id'],$user_ids)){
                    $user_ids[] = $u['user_id'];
                }
            }
            $order_courses = Orders::whereIn('user_id',$user_ids)->where('course_id',$request->input('course_id'))->where('order_status',1)->select('course_id')->orderBy('completed_at','desc')->skip(0)->take(5)->get()->toArray();
            $course_ids = [];
            foreach ($order_courses as $c){
                if(!in_array($c['course_id'],$course_ids)){
                    $course_ids[] = $c['course_id'];
                }
            }
            $result = Courses::whereIn('course_id',$course_ids)->select('course_id','title','description','lecturer_name',DB::raw('CONCAT("'.config('C.DOMAIN').'",cover)  as cover'),'is_live','is_oa','coin_price','now_price','audio_url','opened_at','closed_at','created_at')
                ->get()->toArray();
            $code = array('dec'=>$this->success,'data'=>$result);
        }
        else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    /**
     * 热门评论
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_hot_comments(Request $request){
        if($request->input('course_id')){
            $total = Comments::where('course_id',$request->input('course_id'))->count();
            $overplus = 0;
            $res = Comments::where('course_id',$request->input('course_id'))->orderBy('praise_count','desc')->skip(0)->take(5)->get()->toArray();
            foreach ($res as $key=>$value){
                //查询评论的评论
                $children = $this->get_children_comments($value['comment_id']);
                $res[$key]['childrens'] = $children;
            }
            if($total>=2){
                $overplus = $total-2;
            }
            $code = array('dec'=>$this->success,'data'=>$res,'overplus'=>$overplus);
        }else{
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
        if($course_id){
            $sql = Sections::where('course_id',$course_id);
            $total = $sql->count();
            $sort = 'asc';
            $sort_str = $request->input('sort')??null;
            if($sort_str!=null && $sort_str!=0){
                //正序
                $sort = 'desc';
            }
            $sql = $sql->orderBy('order_index',$sort);
            if($request->input('page_index')!=null){
                $page_index = $request['page_index'];
                $page_number = 10;
                $sql = $sql->skip(($page_index - 1) * $page_number)->take($page_number);
            }
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
                    //点赞叠加
                    Courses::where('course_id',$request->input('course_id'))->increment('praise_count');
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
            $exist = Praises::where('comment_id',$request->input('comment_id'))->where('from_user',$request->input('login_user'))->exists();
            if($exist){
                $code = array('dec'=>$this->praise_err);
            }else{
                $praise['comment_id'] = $request->input('comment_id');
                $praise['from_user'] = $request->input('login_user');
                $praise['created_at'] = time();
                $res = Praises::create($praise);
                if($res){
                    //点赞叠加
                    Comments::where('comment_id',$request->input('comment_id'))->increment('praise_count');
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
     * 添加到收藏列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function add_favorites(Request $request){
        if($request->input('course_id') && $request->input('login_user')){
            $savedata['course_id'] = $request->input('course_id');
            $savedata['user_id'] = $request->input('login_user');
            $savedata['created_at'] = time();
            $res = Favorites::create($savedata);
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
     * 从收藏列表中移除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function del_favorites(Request $request){
        if($request->input('course_id') && $request->input('login_user')){
            $res = Favorites::where('course_id',$request->input('course_id'))->where('user_id',$request->input('login_user'))->delete();
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
     * 我的收藏
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function my_favorites(Request $request){
        if($request->input('login_user')){
            $page_index = $request->input('page_index')??1;//页码
            $page_number = $request->input('page_number')??10;//每页显示
            //初始化sql
            $sql = Favorites::where('courses_favorites.user_id',$request->input('login_user'))->where('courses.is_publish',1)->orderBy('courses_favorites.created_at','desc')
                    ->leftJoin('courses','courses.course_id','courses_favorites.course_id');

            $total = $sql->count();
            $result = $sql->select('courses.course_id','courses.title','courses.description','courses.lecturer_name',DB::raw('CONCAT("'.config('C.DOMAIN').'",jl_courses.cover)  as cover'),'courses.coin_price','courses.now_price','courses.created_at','courses.opened_at','courses.closed_at','courses.is_oa')
                    ->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();
            foreach ($result as $key=>$value){
                //计算课程状态
                $now = time();
                if($now<=$value['closed_at'] && $now>=$value['opened_at']){
                    //课程正在直播
                    $result[$key]['status']=1;
                }else if($now<$value['opened_at']){
                    //未开始
                    $result[$key]['status']=0;
                }else if($now>$value['closed_at']){
                    //已经结束
                    $result[$key]['status']=2;
                }else{
                    //未知
                    $result[$key]['status']=-1;
                }
            }
            $code = array('dec' => $this->success, 'data' => $result,'total'=>$total);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    /***
     * 我的购物车
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function my_cart(Request $request){
        if($request->input('login_user')){
            $page_index = $request->input('page_index')??1;//页码
            $page_number = $request->input('page_number')??10;//每页显示
            $sql = Carts::where('courses_carts.user_id',$request->input('login_user'));
            $total = $sql->count();
            $result = $sql->where('courses.status',1)->orderBy('courses_carts.add_time','desc')
                ->leftJoin('courses','courses.course_id','courses_carts.course_id')
                ->select('courses.course_id','courses.title','courses.description','courses.lecturer_name',DB::raw('CONCAT("'.config('C.DOMAIN').'",jl_courses.cover)  as cover'),'courses.opened_at','courses.closed_at','courses_carts.add_time')
                ->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();

            $code = array('dec' => $this->success, 'data' => $result,'total'=>$total);
            return response()->json($code);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    /***
     * 添加到购物车
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function add_to_cart(Request $request){
        if($request->input('login_user') && $request->input('course_id')){
            $exists = Carts::where('user_id',$request->input('login_user'))->where('course_id',$request->input('course_id'))->exists();
            if($exists){
                //购物车中已经存在。微课产品数量不进行递增，直接返回
                return response()->json(array('dec' => $this->cart_repeat_err));
            }
            $cart['user_id'] = $request->input('login_user');
            $cart['course_id'] = $request->input('course_id');
            $cart['add_time'] = time();
            $res = Carts::create($cart);
            if($res){
                $code= array('dec'=>$this->success);
            }else{
                $code= array('dec'=>$this->error);
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
     * 从购物车中移除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function del_from_cart(Request $request){
        if($request->input('course_id') && $request->input('login_user')){
            $res = Carts::where('course_id',$request->input('course_id'))->where('user_id',$request->input('login_user'))->delete();
            if($res){
                $code= array('dec'=>$this->success);
            }else{
                $code= array('dec'=>$this->error);
            }
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }

    private function get_children_comments($parent_id){
        $result = array();
        if($parent_id){
            $result = Comments::where('parent_id',$parent_id)->select('content','from_user','from_user_name','to_user','to_user_name','created_at')->get()->toArray();
        }
        return $result;
    }

    /***
     * 我的全部课程
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function my_courses(Request $request){
        if($request->input('login_user')){
            $page_index = $request->input('page_index')??1;//页码
            $page_number = $request->input('page_number')??10;//每页显示
            $sql = Users_courses_relation::where('courses_users_relaction.user_id',$request->input('login_user'));
            $total = $sql->count();
            $result = $sql->orderBy('courses_users_relaction.created_at','desc')
                    ->leftJoin('courses','courses.course_id','courses_users_relaction.course_id')
                    ->select('courses.course_id','courses.title','courses.description','courses.lecturer_name',DB::raw('CONCAT("'.config('C.DOMAIN').'",jl_courses.cover)  as cover'),'courses.opened_at','courses.closed_at','courses_users_relaction.created_at','courses.is_oa','courses.coin_price','courses.now_price')
                    ->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();
            foreach ($result as $key=>$value){
                //计算课程状态
                $now = time();
                if($now<=$value['closed_at'] && $now>=$value['opened_at']){
                    //课程正在直播
                    $result[$key]['status']=1;
                }else if($now<$value['opened_at']){
                    //未开始
                    $result[$key]['status']=0;
                }else if($now>$value['closed_at']){
                    //已经结束
                    $result[$key]['status']=2;
                }else{
                    //未知
                    $result[$key]['status']=-1;
                }
            }
            $code = array('dec' => $this->success, 'data' => $result,'total'=>$total);
            return response()->json($code);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }
    /***
     * 直播课程
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function my_live_courses(Request $request){
        if($request->input('login_user')){
            $page_index = $request->input('page_index')??1;//页码
            $page_number = $request->input('page_number')??10;//每页显示
            $sql = Users_courses_relation::where('courses_users_relaction.user_id',$request->input('login_user'))->where('courses.is_live',1)
                ->leftJoin('courses','courses.course_id','courses_users_relaction.course_id');
            $total = $sql->count();
            $result = $sql->orderBy('courses_users_relaction.created_at','desc')
                ->select('courses.course_id','courses.title','courses.description','courses.lecturer_name',DB::raw('CONCAT("'.config('C.DOMAIN').'",jl_courses.cover)  as cover'),'courses.opened_at','courses.closed_at','courses_users_relaction.created_at','courses.is_oa')
                ->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();
            foreach ($result as $key=>$value){
                //计算课程状态
                $now = time();
                if($now<=$value['closed_at'] && $now>=$value['opened_at']){
                    //课程正在直播
                    $result[$key]['status']=1;
                }else if($now<$value['opened_at']){
                    //未开始
                    $result[$key]['status']=0;
                }else if($now>$value['closed_at']){
                    //已经结束
                    $result[$key]['status']=2;
                }else{
                    //未知
                    $result[$key]['status']=-1;
                }
            }
            $code = array('dec' => $this->success, 'data' => $result,'total'=>$total);
            return response()->json($code);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }
    /***
     * 精品课程
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function my_good_courses(Request $request){
        if($request->input('login_user')){
            $page_index = $request->input('page_index')??1;//页码
            $page_number = $request->input('page_number')??10;//每页显示
            $sql = Users_courses_relation::where('courses_users_relaction.user_id',$request->input('login_user'))->where('courses.is_good',1)
                ->leftJoin('courses','courses.course_id','courses_users_relaction.course_id');
            $total = $sql->count();
            $result = $sql->orderBy('courses_users_relaction.created_at','desc')
                ->select('courses.course_id','courses.title','courses.description','courses.lecturer_name',DB::raw('CONCAT("'.config('C.DOMAIN').'",jl_courses.cover)  as cover'),'courses.opened_at','courses.closed_at','courses_users_relaction.created_at','courses.is_oa','courses.coin_price','courses.now_price')
                ->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();
            foreach ($result as $key=>$value){
                //计算课程状态
                $now = time();
                if($now<=$value['closed_at'] && $now>=$value['opened_at']){
                    //课程正在直播
                    $result[$key]['status']=1;
                }else if($now<$value['opened_at']){
                    //未开始
                    $result[$key]['status']=0;
                }else if($now>$value['closed_at']){
                    //已经结束
                    $result[$key]['status']=2;
                }else{
                    //未知
                    $result[$key]['status']=-1;
                }
            }
            $code = array('dec' => $this->success, 'data' => $result,'total'=>$total);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }
    /***
     * 获取精品课程
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function get_good_courses(Request $request){
            $page_index = $request->input('page_index')??1;//页码
            $page_number = $request->input('page_number')??10;//每页显示
            $sql = Courses::where('status',1)->where('is_good',1);
            $total = $sql->count();
            $result = $sql->orderBy('opened_at','desc')->orderBy('created_at','desc')
                ->select('course_id','title','description','lecturer_name',DB::raw('CONCAT("'.config('C.DOMAIN').'",cover)  as cover'),'opened_at','closed_at','created_at','is_oa','coin_price','now_price')
                ->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();
            foreach ($result as $key=>$value){
                //计算课程状态
                $now = time();
                if($now<=$value['closed_at'] && $now>=$value['opened_at']){
                    //课程正在直播
                    $result[$key]['status']=1;
                }else if($now<$value['opened_at']){
                    //未开始
                    $result[$key]['status']=0;
                }else if($now>$value['closed_at']){
                    //已经结束
                    $result[$key]['status']=2;
                }else{
                    //未知
                    $result[$key]['status']=-1;
                }
            }
            $code = array('dec' => $this->success, 'data' => $result,'total'=>$total);
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

    /***
     * 添加足迹
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function add_foots(Request $request){
        if($request->input('course_id') && $request->input('login_user')){
            $foot['user_id'] = $request->input('login_user');
            $foot['course_id'] = $request->input('course_id');
            $foot['in_time'] = time();
            $foot_id = Foots::insertGetId($foot);
            if($foot_id){
                $code= array('dec'=>$this->success,'data'=>$foot_id);
            }else{
                $code= array('dec'=>$this->error);
            }
            $json_str = json_encode($code);
            $res_json = json_decode(\str_replace(':null', ':""', $json_str));
            return response()->json($res_json);
        }
    }


    /***
     * 离开微课详情页使用
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function foot_stay(Request $request){
        if($request->input('foot_id')){
            $foot = Foots::where('foot_id',$request->input('foot_id'))->first();
            $foot->out_time = time();
            $foot->save();
        }
    }

    /***
     * 我的足迹，默认最近10条
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function my_foots(Request $request){
        if($request->input('login_user')){
            $page_index = $request->input('page_index')??1;//页码
            $page_number = $request->input('page_number')??10;//每页显示
            $sql = Foots::where('foots.user_id',$request->input('login_user'));
            $total = $sql->count();
            $result = $sql->where('courses.status',1)->orderBy('foots.in_time','desc')
                ->leftJoin('courses','courses.course_id','foots.course_id')
                ->select('courses.course_id','courses.title','courses.description','courses.lecturer_name','courses.cover','courses.opened_at','courses.closed_at','foots.in_time')
                ->skip(($page_index - 1) * $page_number)->take($page_number)->get()->toArray();
            $code = array('dec' => $this->success, 'data' => $result,'total'=>$total);
//            return response()->json($code);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        $json_str = json_encode($code);
        $res_json = json_decode(\str_replace(':null', ':""', $json_str));
        return response()->json($res_json);
    }

}