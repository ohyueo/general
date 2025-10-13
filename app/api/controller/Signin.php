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
 * Date: 2024/7/18
 * Time: 11:12s
 */

namespace app\api\controller;
use app\BaseController;
use think\facade\Db;
use think\Request;
use app\handler\YuyueListmsgHandler;
use dh2y\qrcode\QRcode;

class Signin extends BaseController
{
    //根据签到二维码和订单id来签到
    public function addsignin(){
        $user = $this->user();
        if(!$user){
            return $this->message('请登录', [],201);
        }
        $uid=$user->id;
        $id=input("param.id");
        if(!$id){
            return $this->message('参数错误', [],0);
        }
        $val=input("param.val");//签到二维码内容
        if(!$val){
            return $this->message('签到二维码错误', [],0);
        }
        if(config('-systemsite.sys_qiandao_isdingwei')==1){
            $lat=input("param.lat");
            $lng=input("param.lng");
            if(!$lat || !$lng){
                return $this->message('请开启定位', [],0);
            }
            //定位距离
            $julino=config('-systemsite.sys_qiandao_julino')??0;
            if($julino){
                $app_lat=config('-appsite.app_lat');
                $app_lng=config('-appsite.app_lng');
                if(!$app_lat || !$app_lng){
                    return $this->message('请设置门店经纬度', [],0);
                }
                $map=new \app\common\Map();
                $juli=$map->mapdistance($lat.','.$lng,$app_lat.','.$app_lng);
                if($juli['status']!=0){
                    $message=$juli['message']??'';
                    return $this->message('定位失败:'.$message, [],0);
                }
                // array(4) { ["status"]=> int(0) ["message"]=> string(7) "Success" ["request_id"]=> string(32) "5da51fe0441441b69ae42f2ba5ca09dd"
                //     ["result"]=> array(1) { ["rows"]=> array(1) { [0]=> array(1) { ["elements"]=> array(1) { [0]=> array(2) { ["distance"]=> int(14075) ["duration"]=> int(1879) } } } } } }
                //var_dump($juli);
                // if($juli['result']['elements'][0]['distance']>$julino){
                //     return $this->message('不在签到范围内', [],0);
                // }
                if($juli['result']['rows'][0]['elements'][0]['distance']>$julino){
                    return $this->message('不在签到范围内', [],0);
                }
            }
        }
        //查询这个二维码是否存在
        $code=Db::name('general_signin_codelist')->where('val|oldval',$val)->where('status',1)->find();
        if(!$code){
            return $this->message('签到二维码错误', [],0);
        }
        //如果是限时签到码则判断是否过期
        if($code['hour']){
            $time=strtotime($code['updatetime'])+$code['hour']*3600;
            if($time<time()){
                return $this->message('签到二维码已过期', [],0);
            }
        }
        //如果这个二维码=oldval 那说明是上一次签到  这个时间只有30秒过期
        if($code['oldval'] && $code['oldval']==$val){
            $time=strtotime($code['updatetime'])+30;
            if($time<time()){
                return $this->message('签到二维码已经过期了', [],0);
            }
        }
        //如果seat不为空则判断是否是这个座位的签到码
        if($code['seat']){
            $order=Db::name('general_yuyue_seatmsg')->where('orderid',$id)->find();
            if($order['biao']!=$code['seat'] && $order['title']!=$code['seat']){
                return $this->message('签到二维码和座位不匹配', [],0);
            }
        }
        //查询订单数据
        $order=Db::name('general_yuyue_order')->where('id',$id)->find();
        if(!$order){
            return $this->message('没有数据', [],0);
        }
        if($order['status']==3){
            return $this->message('订单已签到', [],0);
        }
        if($order['status']!=2){
            return $this->message('订单状态错误', [],0);
        }
        if($order['y_data']!=date('Y-m-d')){
            return $this->message('不是今天的订单', [],0);
        }

        $autotime=config('-timesite.time_autohe');//自动核销时间  
        //如果预约的时间小于当前时间减去自动核销时间则不能签到
        $starttime=Db::name('general_yuyue_ordermultiple')->where('order_id',$id)->order('id asc')->value('y_start_time');
        if(strtotime($starttime)<time()+$autotime){
            return $this->message('预约时间已过期，不可签到', [],0);
        }
        
        //记录日志
        YuyueListmsgHandler::add('用户签到-已完成',$id,3,'预约已签到完成');
        //修改订单状态s
        Db::name('general_yuyue_order')->where('id',$id)->update(['status'=>3]);
        //签到
        $data=[
            'listid'=>$order['list_id'],
            'codeid'=>$code['id'],
            'uid'=> $uid,
            'orderid'=>$id,
            'uid'=>$uid,
            'msg'=>'用户签到',
            'addtime'=>gettime(),
        ];
        $res=Db::name('general_signin_signmsg')->insert($data);
        if($res){
            return $this->message('签到成功', [],200);
        }else{
            return $this->message('签到失败', [],0);
        }
    }
    //生成签到码 /resource/Signin/signaddcode
    public function signaddcode(){
        $id=input("param.id");
        if(!$id){
            return $this->message('参数错误', [],0);
        }
        $res=Db::name('general_signin_codelist')->where('status',1)->where('id',$id)->find();
        if(!$res){
            return $this->message('没有数据', [],0);
        }
        $val=$res['val'];
        $oldval=$res['val'];
        if($val && !$res['hour']){ //如果是0则请求就更新二维码内容
            $val=createNoncestr(8);
            Db::name('general_signin_codelist')->where('id',$id)->update(['val'=>$val,'oldval'=>$oldval,'updatetime'=>gettime()]);
        }
        //否则判断当前时间是否大于更新时间加上小时数 updatetime是年月日时分秒格式
        if($res['hour']){
            //$time=$res['updatetime']+$res['hour']*3600;
            $time=strtotime($res['updatetime'])+$res['hour']*3600;
            if($time<time()){
                $val=createNoncestr(8);
                Db::name('general_signin_codelist')->where('id',$id)->update(['val'=>$val,'oldval'=>$oldval,'updatetime'=>gettime()]);
            }
        }
        //生成二维码
        $imgurl=$val;
        $code = new QRcode();
        $lujing = "storage/signin/";
        if(!is_dir($lujing)){
            mkdir(iconv("UTF-8", "GBK", $lujing),0777,true);
        }
        $img='storage/signin/sig'.$id.'.png';
        $img = $code->png($imgurl,$img, 6)
            //->logo('logo.png')
            ->entry();
        $arr['code']=$img.'?'.time();
        return $this->message('请求成功', $arr,200);
    }
}