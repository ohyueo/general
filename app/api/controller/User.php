<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/6/5
 * Time: 12:52
 */

namespace app\api\controller;
use app\BaseController;
use app\models\GeneralYuyueOrder;
use app\models\GeneralYuyueEmployees;
use think\Request;
use app\models\GeneralPayorder;
use think\facade\Db;
use dh2y\qrcode\QRcode;
use think\facade\Cache;

class User extends BaseController
{
    public function user_info(Request $request){
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $id=$user->id;
        $name=$user->nick;
        $headimg=$user->headimg;
        $mobile=$user->userLogin->mobile;
        if($headimg){
            $headimg=getFullImageUrl($headimg);
        }else{
            $headimg=getFullImageUrl('/storage/imges/mo.png');
        }
        $xiao=0;
        //查询核销
        $ish=GeneralYuyueEmployees::where('uid',$id)->find();
        if($ish && $ish['status']==1){
            $xiao=1;
        }
        $istui=config('-systemsite.istui');
        //查询待支付订单
        $payno=GeneralYuyueOrder::where('uid',$id)->where('status',1)->count();
        //查询待核销订单
        $heno=GeneralYuyueOrder::where('uid',$id)->where('status',2)->count();

        //支付记录
        $paymsg=Cache::has('paymsg');
        if($paymsg){
            $isspay=Cache::get('paymsg');
        }else{
            $isspay=Db::name('general_system_diy')->where('name','paymsg')->value('val')?:0;
            if($isspay || $isspay==0){
                Cache::set('paymsg',$isspay);
            }
        }
        //商城订单
        $shop=Cache::has('shop');
        if($shop){
            $isshop=Cache::get('shop');
        }else{
            $isshop=Db::name('general_system_diy')->where('name','shop')->value('val')?:0;
            if($isshop || $isshop==0){
                Cache::set('shop',$isshop);
            }
        }

        $arr=array(
            'id' => $id,
            'name' => $name,
            'headimg' => $headimg,
            'mobile' => $mobile,
            'paymsg' => $isspay,
            'isshop' => $isshop,
            'istui' => $istui,
            'xiao' => $xiao,
            'heno' => $heno,
            'payno' => $payno
        );
        return $this->message('请求成功', $arr);
    }
    //修改用户昵称
    public function setnick(Request $request){
        $data = $request->post();
        $name=strip_tags(trim($data['name']));//用户昵称
        if(!$name){
            return $this->message('昵称不能为空', [],0);
        }
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $user->nick=$data['name'];
        $user->save();
        return $this->message('修改成功', [], 200);
    }

    //上传用户头像
    public function upload(Request $request){
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $type=input('param.type');
        //验证码token end
        $result=[];
        $file = request()->file('file');
        try {
            validate(['file' => [
                'fileSize' => 1048576,
                'fileExt' => 'jpg,png,gif,jpeg',
                'fileMime' => 'image/jpeg,image/png,image/gif',
            ]])->check(['file' => $file]);
            //设置了七牛云的请求地址
            if(config('-cloudsite.domain')){
                // 图片存储在本地的临时路经
                $filePath = $file->getRealPath();
                // 获取图片后缀
                $ext = $file->getOriginalExtension();
                // 上传到七牛后保存的新图片名
                $tim=date('Ymd');
                $newImageName  =   'user_img/'.$tim.'/'.substr(md5($file->getOriginalName()),0,6)
                    .  rand(00000,99999) . '.'.$ext;
                Qiniu::upload($newImageName,$filePath);
                $domain=config('-cloudsite.domain');
                return json(['savename' => $domain.$newImageName,'img'=>$newImageName, 'code' => 200, 'message' => '上传成功']);
            }else{
                $savename = \think\facade\Filesystem::disk('public')->putFile( 'user_img', $file);
                if (!$savename) {
                    return json(['', 'code' => 0, 'message' => '上传失败']);
                }
                $imges='/storage/'.str_replace('\\','/',$savename);
                if(!$type){
                    //存入用户头像
                    $user->headimg=$imges;
                    $user->save();
                }
                $savename=getFullImageUrl($imges);
                return json(['savename' => $savename,'img'=>$imges, 'code' => 200, 'message' => '上传成功']);
            }
        } catch (ValidateException $e) {
            return $e->getMessage();
        }
    }
    //查询系统设置
    public function getsite(){
        $company=config('-appsite.app_company');//公司名称
        $version=config('-appsite.app_version');//版本号
        $name=config('-appsite.app_name');//名称
        $img=config('-appsite.app_logoimg');//平台logo
        if($img){
            $img=getFullImageUrl($img);
        }
        $arr=array('name'=>$name,'company'=>$company,'version'=>$version,'img'=>$img);
        return $this->message('修改成功', $arr, 200);
    }
    //我的预约
    public function my_yuyue(Request $request){
        $data = $request->post();
        $type=strip_tags(trim($data['type']));//用户昵称
        if(!$type){
            $type=0;
        }
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $page=$data['page'];
        if(!$page){
            $page=1;
        }
        $uid=$user->id;
        $list=[];
        if($type){
            $count=GeneralYuyueOrder::where('uid',$uid)->where('status',$type)->count();
            $list=GeneralYuyueOrder::where('uid',$uid)->where('status',$type)->order('id desc')->page($page,10)->select();
        }else{
            $count=GeneralYuyueOrder::where('uid',$uid)->count();
            $list=GeneralYuyueOrder::where('uid',$uid)->order('id desc')->page($page,10)->select();
        }
        $data = [
            'count' => $count,
            'list' => array()
        ];
        $list->each(function ($item) use(&$data) {
            $img=$item->yuyuelist?$item->yuyuelist->img:'';
            $title=$item->yuyuelist?$item->yuyuelist->title:'';
            $data['list'][] = [
                'id' => $item->id,
                'img' => $img,
                'title' => $title,
                'money' => $item->money,
                'paymo' => $item->paymo,
                'status' => $item->status,
                'y_data' => $item->y_data,
                'y_time' => $item->y_time,
                'addtime' => $item->addtime,
                'uptime' => $item->uptime
            ];
        });
        return $this->message('请求成功', $data);
    }
    //预约详情
    public function myorderinfo(){
        $id=input("param.id/d");
        if(!$id){
            return $this->message('参数错误', [], 0);
        }
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $uid=$user->id;
        $list=GeneralYuyueOrder::where('uid',$uid)->where('id',$id)->find();
        $arr['id']=$list['id'];
        $arr['yuyue_id']=$list['list_id'];
        $arr['riqi']=$list->y_data;
        $arr['time']=$list->y_time;
        $arr['paymo']=$list->paymo;
        $arr['address']=$list->yuyuelist->address;
        $arr['status']=$list['status'];
        $arr['number']=$list['number'];
        $arr['heno']=$list['heno'];
        $star='';
        $type=input("param.type");
        if($list['status']==1){
            $star='待付款';
        }else if($list['status']==2){
            $star='待核销';//待上门
            //生成核销二维码
            if($type==1){
                $imgurl=getUrl().'#/pages/user/hexiao?id='.$id;
                $code = new QRcode();
                $lujing = "storage/order";
                if(!is_dir($lujing)){
                    mkdir(iconv("UTF-8", "GBK", $lujing),0777,true);
                }
                $img='storage/order/h5'.$id.'.png';
                $img = $code->png($imgurl,$img, 6)
                    //->logo('logo.png')
                    ->entry();
                $arr['code']=$img;
            }else if($type==2){
                $imgurl=$id.'&1';
                $code = new QRcode();
                $lujing = "storage/order";
                if(!is_dir($lujing)){
                    mkdir(iconv("UTF-8", "GBK", $lujing),0777,true);
                }
                $img='storage/order/min'.$id.'.png';
                $img = $code->png($imgurl,$img, 6)
                    //->logo('logo.png')
                    ->entry();
                $arr['code']=$img;
            }
        }else if($list['status']==3){
            $star='已完成';
        }else if($list['status']==4){
            $star='取消订单';
        }else{
            $star='其他';
        }
        $arr['star']=$star;
        $arr['addtime']=$list['addtime'];
        $arr['img']=$list->yuyuelist->img;
        $arr['money']=$list->money;
        $arr['title']=$list->yuyuelist->title;
        $arr['texter']='';
        $arr['uptime']=$list->uptime;

        $ydate=$arr['riqi'];
        $no=0;//
        if($ydate){
            $no=Db::name('general_yuyue_order')->whereTime('y_data',$ydate)->count();
        }
        $arr['no']=$no;

        $trade_no='';
        $paytime='';
        $payorder=$list['pay_order'];
        if($payorder){
            $p=GeneralPayorder::where(array('ordernum'=>$payorder,'status'=>1))->find();
            if($p){
                $trade_no=$p['trade_no'];
                $paytime=$p['paytime'];
            }
        }
        $arr['trade_no']=$trade_no;
        $arr['paytime']=$paytime;

        $res=Db::name('general_yuyue_ord')->alias('o')
            ->join('general_yuyue_form f', 'o.form_id = f.id', 'LEFT')
            ->where('o.ord_id',$id)
            ->field('o.val,o.type,f.name')
            ->order('o.paiid desc,o.id desc')
            ->select()->toArray();
        if($res){
            for($i=0;$i<count($res);$i++){
                if($res[$i]['type']==5 && $res[$i]['val']){
                    $img=explode(',',$res[$i]['val']);
                    if($img){
                        for($x=0;$x<count($img);$x++){
                            $img[$x]=getFullImageUrl($img[$x]);
                        }
                    }
                    $res[$i]['val']=$img;
                }
            }
        }
        //查询是否有人员
        $personid=$list['personid'];
        if($personid){
            $tit=Db::name('general_yuyue_personnel')->where('id',$personid)->value('title');
            $trr=['name'=>'选择人员','val'=>$tit];
            array_push($res,$trr);
        }
        //查询是否有座位
        $seat=Db::name('general_yuyue_seatmsg')->where('orderid',$id)->value('title');
        if($seat){
            $trr=['name'=>'选择座位','val'=>$seat];
            array_push($res,$trr);
        }
        $arr['res']=$res;
        //如果有规格则查询规格名称
        $specid=$list['specid'];
        if($specid){
            $specid=explode(',',$specid);
            //查询多个规格名称
            $tit=Db::name('general_yuyue_yuyuespec')->where('id','in',$specid)->column('title');
            $arr['tit']=implode(',',$tit);
        }
        //核销方式
        $hexiaotype=config('-systemsite.sys_hexiao_type')??0;
        $arr['hexiaotype']=$hexiaotype;
        //是否定位
        if(config('-systemsite.sys_qiandao_isdingwei')==1){
            $arr['isding']=1;
        }else{
            $arr['isding']=0;
        }

        //是否显示取消或退款
        $isqushow=config('-timesite.isqutuiord')?:0;
        //如果是显示  则查询是否已经过了时间了
        if($isqushow){
            $autotime=config('-timesite.time_autohe');//自动核销时间  
            //如果预约的时间小于当前时间减去自动核销时间则不能签到
            $starttime=Db::name('general_yuyue_ordermultiple')->where('orderid',$id)->order('id asc')->value('y_start_time');
            if(strtotime($starttime)<time()-$autotime){
                $isqushow=0;
            }
        }
        $arr['isqushow']=$isqushow;
        
        return $this->message('请求成功', $arr);
    }
    //查询支付记录
    public function setpaylist(){
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $page=input("param.page/d");
        if(!$page){
            $page=1;
        }
        $type=strip_tags(trim(input("param.type/d")));//
        if(!$type){
            $type=0;
        }
        $uid=$user->id;
        $count=GeneralPayorder::where('uid',$uid)->where('status',$type)->count();
        $list=GeneralPayorder::where('uid',$uid)->where('status',$type)->order('id desc')->page($page,10)->select();
        $data = [
            'count' => $count,
            'list' => $list
        ];
        return $this->message('请求成功', $data);
    }
    //违约记录 general_default_defaultlist
    public function setdefaultlist(){
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $page=input("param.page/d");
        if(!$page){
            $page=1;
        }
        $uid=$user->id;
        $count=Db::name('general_default_defaultlist')->where('uid',$uid)->count();
        $list=Db::name('general_default_defaultlist')->where('uid',$uid)->order('id desc')->page($page,10)->select();
        $data = [
            'count' => $count,
            'list' => $list
        ];
        return $this->message('请求成功', $data);
    }
}