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
 * Date: 2022/4/20
 * Time: 17:25
 */

namespace app\api\controller;
use app\BaseController;
use think\facade\Db;
use think\Request;
use app\models\GeneralUserList;
use app\models\GeneralInvitamsg;
use dh2y\qrcode\QRcode;


class Invitation extends BaseController
{
    public function getinvitation(){
        $user = $this->user();
        if(!$user){
            return $this->message('请登录', [],201);
        }
        $uid=$user->id;
        $msg=config('-systemsite.sys_invitationmsg');
        //查询徒弟多少人
        $tuno=GeneralUserList::where('rel1',$uid)->count();
        //查询徒孙
        $sunno=GeneralUserList::where('rel2',$uid)->count();
        if($user->userinfo){
            $tumo=$user->userinfo->lower1_total_money;
            $sunmo=$user->userinfo->lower2_total_money;
            $isshen=$user->userinfo->isyao;//申请
        }else{
            $tumo=0;
            $sunmo=0;
            $isshen=0;//申请
        }
        $zmo=$tumo+$sunmo;
        $arr=array(
            'msg' => nl2br($msg),
            'tuno' => $tuno,
            'sunno' => $sunno,
            'tumo' => $tumo,
            'sunmo' => $sunmo,
            'zmo' => $zmo,
            'isshen' => $isshen
        );
        return $this->message('请求成功',$arr,200);
    }
    public function addcode(){
        $user = $this->user();
        if(!$user){
            return $this->message('请登录', [],201);
        }
        $uid=$user->id;
        $type=input("param.type");
        if($type==2){
            $wechat = new \app\common\WeChat();
            $token = $wechat->getAccessToken();
            $url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$token['access_token'];
            $data=[
                'path'=>"/pages/index/index",
                'scene' => 'uid='.$uid,
                'width'=>'430'
            ];
            $res=posturl($url,$data);
            $lujing = "storage/wxcode";
            if(!is_dir($lujing)){
                mkdir(iconv("UTF-8", "GBK", $lujing),0777,true);
            }
            $img='/storage/wxcode/'.$uid.'.png';
            $file=$_SERVER['DOCUMENT_ROOT'].$img;
            if(!file_exists($file)){
                file_put_contents($file,$res);
            }
            $arr=['img'=>getFullImageUrl($img)];
        }else if($type==1){ //h5直接合成二维码
            $url=getUrl().'#/pages/index/index?uid='.$uid;
            $code = new QRcode();
            $lujing = "storage/wxcode";
            if(!is_dir($lujing)){
                mkdir(iconv("UTF-8", "GBK", $lujing),0777,true);
            }
            $img='storage/wxcode/h5'.$uid.'.png';
            $img = $code->png($url,$img, 6)
                //->logo('logo.png')
                ->entry();
            $arr=['img'=>$img];
        }
        return $this->message('请求成功',$arr,200);
    }
    //查询下级列表 和 下下级列表
    public function invitalist(){
        $user = $this->user();
        if(!$user){
            return $this->message('请登录', [],201);
        }
        $page=input("param.page/d");
        if(!$page){
            $page=1;
        }
        $uid=$user->id;
        $type=strip_tags(trim(input("param.type/d")));
        if($type==1){
            $count=GeneralUserList::where('rel1',$uid)->count();
            $list=GeneralUserList::where('rel1',$uid)->order('id desc')->page($page,10)->select();
        }else if($type==2){
            $count=GeneralUserList::where('rel2',$uid)->count();
            $list=GeneralUserList::where('rel2',$uid)->order('id desc')->page($page,10)->select();
        }
        $data = [
            'count' => $count,
            'list' => array()
        ];
        $list->each(function ($item) use(&$data) {
            $img='';
            $title='';
            if($item->yuyuelist){
                $img=$item->yuyuelist->img;
                $title=$item->yuyuelist->title;
            }
            $data['list'][] = [
                'id' => $item->id,
                'nick' => $item->nick,
                'rel1_money' => $item->userinfo->rel1_money,
                'rel2_money' => $item->userinfo->rel2_money,
                'reg_time' => $item->userLogin->reg_time,
                'status' => $item->status
            ];
        });
        return $this->message('请求成功', $data);
    }
    //查询推广员申请
    public function getinvata(){
        $user = $this->user();
        if(!$user){
            return $this->message('请登录', [],201);
        }
        $uid=$user->id;
        $istui=0;
        $ist=GeneralInvitamsg::where('uid',$uid)->find();
        if($ist){
            $istui=1;
        }
        $arr=array(
            'list'=>$ist,
            'istui' => $istui
        );
        return $this->message('请求成功', $arr);
    }
    //提交邀请申请
    public function addinvata(){
        $user = $this->user();
        if(!$user){
            return $this->message('请登录', [],201);
        }
        $istui=config('-systemsite.istui');
        if(!$istui){
            return $this->message('推广未开启', [],0);
        }
        $uid=$user->id;
        $ist=GeneralInvitamsg::where('uid',$uid)->find();
        if($ist){
            return $this->message('请不要重复提交', [],0);
        }
        $name=strip_tags(trim(input("param.name")));
        if(!$name){
            return $this->message('姓名不能为空', [],0);
        }
        $phone=strip_tags(trim(input("param.phone")));
        if(!$phone){
            return $this->message('手机号不能为空', [],0);
        }
        $msg=strip_tags(trim(input("param.msg")));
        if(!$msg){
            return $this->message('申请说明不能为空', [],0);
        }
        GeneralInvitamsg::create([
            'uid' => $uid,
            'name' => $name,
            'phone' => $phone,
            'msg' => $msg,
            'addtime' => gettime(),
            'status' => 0
        ]);
        return $this->message('提交成功', []);
    }
}