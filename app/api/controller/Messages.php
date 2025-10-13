<?php
/**
 *  * 系统-受国家计算机软件著作权保护 - !
 * =========================================================
 * Copy right 2018-2025 成都海之心科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: http://www.ohyu.cn
 * 这不是一个自由软件！在未得到官方有效许可的前提下禁止对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 * User: ohyueo
 * Date: 2021/5/16
 * Time: 18:14
 */

namespace app\api\controller;

use app\BaseController;
use think\Request;
use app\models\Message;


class Messages extends BaseController
{   
    //我的消息
    public function mymesslist(Request $request){
        $data = $request->post();
        $page=$data['page'];
        if(!$page){
            $page=1;
        }
        
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $uid=$user->id;
        //查询
        $count=Message::where('uid',$uid)->count();
        $list=Message::where('uid',$uid)->order('id desc')->page($page,10)->select();
        if($list){
            for($i=0;$i<count($list);$i++){
                $list[$i]['addtime']=date('m-d H:i',strtotime($list[$i]['addtime']));
            }
        }
        $data = [
            'count' => $count,
            'list' => $list
        ];
        return $this->message('请求成功', $data);
    }
    //详细详情
    public function mymessinfo(Request $request){
        $data = $request->post();
        $id=$data['id'];
        if(!$id){
            return $this->message('参数错误', [], 0);
        }
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $uid=$user->id;
        //查询
        $list=Message::where('uid',$uid)->where('id',$id)->find();
        if($list && !$list['is_du']){
            $list->is_du=1;
            $list->save();
        }
        return $this->message('请求成功', $list);
    }
    //查询消息未读数量
    public function mymessweidu(Request $request){
        $no=0;
        $user = $this->user();
        if($user){
            $uid=$user->id;
            //查询
            $no=Message::where('uid',$uid)->where('is_du',0)->count();
        }
        $list=array('weidu'=>$no);
        return $this->message('请求成功', $list);
    }
    //一键已读
    public function messyidu(){
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $uid=$user->id;
        //查询
        $list=Message::where('uid',$uid)->where('is_du',0)->update(['is_du'=>1]);
        return $this->message('已读成功', $list);
    }
    
}