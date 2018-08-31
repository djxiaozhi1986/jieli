<?php
namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\libraries\HttpClient;
use Illuminate\Http\Request;

class AnswerController extends Controller{
    public function api_answer_list(Request $request){
        $user_id = $request->input('login_user');
        $page_index = $request->input('page_index');
        $follow_id = $request->input('follow_id');
        $order = $request->input('order');
        $qa_title = $request->input('qa_title');
        if($user_id && $page_index && $order && $follow_id){
            $request_path = '/answer/list';
            $request_url = config('C.API_URL').$request_path;
            $params = ['user_id'=>$user_id,'page_index'=>$page_index,'follow_id'=>$follow_id,'order'=>$order];
            if($qa_title){
                $params['qa_title'] = $qa_title;
            }
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $code = json_decode($response);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function api_answer_detail(Request $request){
        $qa_id = $request->input('qa_id');
        $user_id = $request->input('login_user');
        if($qa_id && $user_id){
            $request_path = '/answer/detail';
            $request_url = config('C.API_URL').$request_path;
            $params = ['qa_id'=>$qa_id,'user_id'=>$user_id];
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $code = json_decode($response);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function api_answer_add(Request $request){
        $user_id = $request->input('login_user');
        $reward = $request->input('reward');
        $jieli_coin = $request->input('jieli_coin')??0;
        $forum_ids = $request->input('forum_ids');
        $content = $request->input('content');
        $qa_title = $request->input('qa_title');
        $exper_ids = $request->input('exper_ids');
        $imgs = $request->input('imgs');
        if($user_id && isset($reward) && isset($jieli_coin) && $forum_ids && $content && $qa_title && $exper_ids){
            $request_path = '/answer/addAnswer';
            $request_url = config('C.API_URL').$request_path;
            $params = ['user_id'=>$user_id,'reward'=>$reward,'jieli_coin'=>$jieli_coin,'forum_ids'=>$forum_ids,'content'=>$content,'qa_title'=>$qa_title,'exper_ids'=>$exper_ids];
            if($imgs){
                $params['imgs'] = $imgs;
            }
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $code = json_decode($response);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function api_answer_favorite(Request $request){
        $user_id = $request->input('login_user');
        $answer_id = $request->input('answer_id');
        if($user_id && $answer_id){
            $request_path = '/answer/favorite';
            $request_url = config('C.API_URL').$request_path;
            $params = ['user_id'=>$user_id,'answer_id'=>$answer_id];
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $code = json_decode($response);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function api_answer_unfavorite(Request $request){
        $user_id = $request->input('login_user');
        $answer_id = $request->input('answer_id');
        if($user_id && $answer_id){
            $request_path = '/answer/delfavorite';
            $request_url = config('C.API_URL').$request_path;
            $params = ['user_id'=>$user_id,'answer_id'=>$answer_id];
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $code = json_decode($response);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function api_answer_reply(Request $request){
//        user_id 用户ID
//        qa_id 问题ID
//        content 问题内容
        $user_id = $request->input('login_user');
        $qa_id = $request->input('qa_id');
        $content = $request->input('content');
        if($user_id && $qa_id && $content){
            $request_path = '/answer/replyAnswer';
            $request_url = config('C.API_URL').$request_path;
            $params = ['user_id'=>$user_id,'qa_id'=>$qa_id,'content'=>$content];
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $code = json_decode($response);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function api_answer_adopt(Request $request){
        $user_id = $request->input('login_user');
        $qa_id = $request->input('qa_id');
        $answer_id = $request->input('answer_id');
        if($user_id && $qa_id && $answer_id){
            $request_path = '/answer/adopt';
            $request_url = config('C.API_URL').$request_path;
            $params = ['user_id'=>$user_id,'qa_id'=>$qa_id,'answer_id'=>$answer_id];
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $code = json_decode($response);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function api_answer_zan(Request $request){
        $user_id = $request->input('login_user');
        $qa_id = $request->input('qa_id');
        $answer_id = $request->input('answer_id');
        if($user_id && $qa_id && $answer_id){
            $request_path = '/answer/zan';
            $request_url = config('C.API_URL').$request_path;
            $params = ['user_id'=>$user_id,'qa_id'=>$qa_id,'answer_id'=>$answer_id];
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $code = json_decode($response);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function api_answer_reply_list(Request $request){
        $user_id = $request->input('login_user');
//        $qa_id = $request->input('qa_id');
        $answer_id = $request->input('answer_id');
//        $page_index = $request->input('page_index');
        if($user_id && $answer_id){
            $request_path = '/answer/replyAnswerList';
            $request_url = config('C.API_URL').$request_path;
            $params = ['user_id'=>$user_id,'answer_id'=>$answer_id];
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $code = json_decode($response);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function api_answer_reply_comment(Request $request){
        $user_id = $request->input('login_user');
        $qa_id = $request->input('qa_id');
        $answer_id = $request->input('answer_id');
        $reply_user_id = $request->input('reply_user_id');
        $content = $request->input('content');
        if($user_id && $qa_id && $answer_id && $content){
            $request_path = '/answer/replyAnswerComment';
            $request_url = config('C.API_URL').$request_path;
            $params = ['user_id'=>$user_id,'qa_id'=>$qa_id,'answer_id'=>$answer_id,'content'=>$content];
            if($reply_user_id){
                $params['reply_user_id'] = $reply_user_id;
            }
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $code = json_decode($response);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function api_answer_comment_list(Request $request){
        $user_id = $request->input('login_user');
        $qa_id = $request->input('qa_id');
        $answer_id = $request->input('answer_id');
        $page_index = $request->input('page_index');
        if($user_id && $qa_id && $answer_id && $page_index){
            $request_path = '/answer/commentList';
            $request_url = config('C.API_URL').$request_path;
            $params = ['user_id'=>$user_id,'qa_id'=>$qa_id,'answer_id'=>$answer_id,'page_index'=>$page_index];
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $code = json_decode($response);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function api_answer_reply_detail(Request $request){
        $user_id = $request->input('login_user');
        $answer_id = $request->input('answer_id');
        if($user_id && $answer_id){
            $request_path = '/answer/replyAnswerDetail';
            $request_url = config('C.API_URL').$request_path;
            $params = ['user_id'=>$user_id,'answer_id'=>$answer_id];
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $code = json_decode($response);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
    public function api_answer_report(Request $request){
        $user_id = $request->input('login_user');
        $qa_id = $request->input('qa_id');
        $answer_id = $request->input('answer_id');
        $answer_comment_id = $request->input('answer_comment_id');
        $inform_type = $request->input('inform_type');
        if($user_id && $qa_id && $answer_id && isset($inform_type)){
            $request_path = '/answer/report';
            $request_url = config('C.API_URL').$request_path;
            $params = ['user_id'=>$user_id,'qa_id'=>$qa_id,'answer_id'=>$answer_id,'inform_type'=>$inform_type];
            if($answer_comment_id){
                $params['answer_comment_id'] = $answer_comment_id;
            }
            $response = HttpClient::api_request($request_url,$params,'POST',true);
            $code = json_decode($response);
        }else{
            $code = array('dec'=>$this->client_err);
        }
        return response()->json($code);
    }
}