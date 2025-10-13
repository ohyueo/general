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
 * Date: 2021/7/6
 * Time: 10:34
 */

namespace app\api\controller;
use app\BaseController;
use think\facade\Db;
use Yansongda\Pay\Pay;
use app\handler\YuyueListmsgHandler;
use app\models\VenuesViplog;
use app\handler\MoneyLogHandler;
use app\handler\CourseKnomsgHandler;

class Payco extends BaseController
{

    //use Yansongda\Pay\Log;
    //微信配置

    protected function wxh5conf(){
        $wxconfig = [
            'app_id' => config('-wxsite.wx_gzh_appid'), // 公众号 APPID
            'mch_id' => config('-wxsite.wx_xcx_mchid'),
            'key' => config('-wxsite.wx_xcx_mchkey'),
            'cert_client' => './cert/apiclient_cert.pem', // optional，退款等情况时用到
            'cert_key' => './cert/apiclient_key.pem',// optional，退款等情况时用到
            'notify_url' => config('-appsite.app_domainname').'resource/Payco/h5wxnotify',
            'return_url' => config('-appsite.app_domainname').'index/index/pay',
        ];
        return $wxconfig;
    }

    protected function wxwapconf(){
        $wxconfig = [
            'app_id' => config('-wxsite.wx_wap_appid'), // APPID
            'mch_id' => config('-wxsite.wx_xcx_mchid'),
            'key' => config('-wxsite.wx_xcx_mchkey'),
            'cert_client' => './cert/apiclient_cert.pem', // optional，退款等情况时用到
            'cert_key' => './cert/apiclient_key.pem',// optional，退款等情况时用到
            'notify_url' => config('-appsite.app_domainname').'resource/Payco/h5wxnotify',
            'return_url' => config('-appsite.app_domainname').'index/index/pay',
        ];
        return $wxconfig;
    }

    protected function wxconf(){
        $wxconfig = [
            'miniapp_id' => config('-wxsite.wx_xcx_appid'), // 小程序 APPID
            'mch_id' => config('-wxsite.wx_xcx_mchid'),
            'key' => config('-wxsite.wx_xcx_mchkey'),
            'cert_client' => './cert/apiclient_cert.pem', // optional，退款等情况时用到
            'cert_key' => './cert/apiclient_key.pem',// optional，退款等情况时用到
            'notify_url' => config('-appsite.app_domainname').'resource/Payco/xcxwxnotify',
            'return_url' => config('-appsite.app_domainname').'index/index/pay',
        ];
        return $wxconfig;
    }

    protected function zfbconf(){
        $wxconfig = [
            'app_id' => config('-wxsite.ali_appid'), // 支付宝 APPID
            'ali_public_key' => config('-wxsite.ali_public_key'),
            'private_key' => config('-wxsite.private_key'),
            'notify_url' => config('-appsite.app_domainname').'resource/Payco/zfbnotify',
            'return_url' => config('-appsite.app_domainname').'index/index/pay',
        ];
        return $wxconfig;
    }

    public function index()
    {
        $order = [
            'out_trade_no' => time(),
            'total_amount' => '1',
            'subject' => 'test subject - 测试',
        ];
        $alipay = \Yansongda\Pay\Pay::alipay($this->config)->web($order);
        return $alipay->send();// laravel 框架中请直接 `return $alipay`
    }

    //微信小程序
    public function wxxcx(){
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $uid=$user->id;
        //接收一个订单id
        $id=input("param.id/d");
        if(!$id){
            return $this->message('参数错误', [], 0);
        }
        $st=input("param.st/d");
        if(!$st){
            $st=1;
        }
        $tit='服务订单';
        if($st==1){
            $order = Db::name('general_yuyue_order')->where('id',$id)->field(['money','status'])->find();
            $money=$order['money'];
            if($money<=0){
                return $this->message('该订单无需支付2', [], 0);
            }
            if($order['status']>1){
                return $this->message('该订单无需支付1', [], 0);
            }
        }else if($st==2){ //保证金
            $money=config('-systemsite.sys_baomo')?:0;
        }else if($st==3){
            $order = Db::name('general_shop_order')->where('id',$id)->field(['money','status'])->find();
            $tit='商品订单';
            $money=$order['money'];
            if($money<=0){
                return $this->message('该订单无需支付2', [], 0);
            }
            if($order['status']>1){
                return $this->message('该订单无需支付2', [], 0);
            }
        }
        $ordernum=neworderNumnew();
        $openid=$user->xcx_openid;

        $arr=array(
            'orderid' => $id,
            'money' => $money,
            'paymo' => 0,
            'uid' => $uid,
            'addtime' => gettime(),
            'ordernum' => $ordernum,
            'status' => 0,
            'type' => 2,
            'style' => $st
        );
        Db::name('general_payorder')->insert($arr);
        $config_biz = [
            'out_trade_no'     => $ordernum, // 订单号 php strtotime 为10位时间戳
            'total_fee'        => $money * 100, // 订单金额，**单位：分**
            'body'             => $tit, // 订单描述
            'openid'           => $openid // 用户 openid ID
        ];
        // 更多参数:https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_1
        $conf=$this->wxconf();
        $wechat=Pay::wechat($conf);
        $result = $wechat->miniapp($config_biz);
        return $this->message('请求成功',$result,200);
        return $result->send(); // laravel 框架中请直接 return $alipay->wap($order)
    }

    //微信公众号
    public function wxh5(){
        //接收一个订单id
        $id=input("param.id/d");
        if(!$id){
            return $this->message('参数错误', [], 0);
        }
        $st=input("param.st/d");
        if(!$st){
            $st=1;
        }
        $tit='服务订单';
        if($st==1){
            $order = Db::name('general_yuyue_order')->where('id',$id)->field(['money','status'])->find();
        }else if($st==2){
            $order = Db::name('venues_activityord')->where('id',$id)->field(['money','status'])->find();
        }else if($st==3){
            $order = Db::name('general_shop_order')->where('id',$id)->field(['money','status'])->find();
            $tit='商品订单';
        }
        if($order['status']>1){
            return $this->message('该订单已支付', [], 0);
        }
        $money=$order['money'];
        if($money<=0){
            return $this->message('该订单无需支付', [], 0);
        }
        $ordernum=neworderNumnew();
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $uid=$user->id;
        $openid=$user->openid;
        $arr=array(
            'orderid' => $id,
            'money' => $money,
            'paymo' => 0,
            'uid' => $uid,
            'addtime' => gettime(),
            'ordernum' => $ordernum,
            'status' => 0,
            'type' => 1,
            'style' => $st
        );
        Db::name('general_payorder')->insert($arr);

        $config_biz = [
            'out_trade_no'     => $ordernum, // 订单号 php strtotime 为10位时间戳
            'total_fee'        => $money * 100, // 订单金额，**单位：分**
            'body'             => $tit, // 订单描述
            'openid'           => $openid// 用户 openid ID
        ];
        // 更多参数:https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_1
        $conf=$this->wxh5conf();
        $result = Pay::wechat($conf)->mp($config_biz);
        return json($result);exit;
    }

    public function h5wxnotify()
    {

        $conf=$this->wxh5conf();
        $pay = Pay::wechat($conf);
        // $notifiedData = file_get_contents('php://input');
        // $log = "<br />\r\n\r\n".'==================='."\r\n".date("Y-m-d H:i:s")."\r\n".json_encode($notifiedData);
        // @file_put_contents('wx.txt', $log, FILE_APPEND);
        try{
            $data = $pay->verify(); // 是的，验签就这么简单！
            $orderno=$data->out_trade_no;
            $paymo=$data->total_fee;
            if($paymo){
                $paymo=$paymo/100;
            }
            $trade_no=$data->transaction_id;
            $info=Db::name('general_payorder')->where('ordernum',$orderno)->find();
            if(!$info){
                exit('success');//订单号不存在
            }
            if($info['status']==1){
                exit('success');//已经修改过状态了
            }
            $oid=$info['orderid'];
            $st=$info['style'];
            //修改订单状态
            $newtime=gettime();
            Db::name('general_payorder')->where('id',$info['id'])->update(['status'=>1,'paymo'=>$paymo,'trade_no'=>$trade_no,'paytime'=>$newtime]);
            //修改订单状态 和 金额
            if($st==1){
                Db::name('general_yuyue_order')->where('id',$oid)->update(['paymo'=>$paymo,'status'=>2,'pay_order'=>$orderno]);
                YuyueListmsgHandler::add('订单支付完成-待核销',$oid,2,'已支付');
                $ord=Db::name('general_yuyue_order')->where('id',$oid)->field(['id','yuyue_id','uid'])->find();
                $uid=$ord['uid'];
                $id=$ord['yuyue_id'];
                $title=Db::name('general_yuyue_list')->where('id',$id)->value('title');
                $data=['uid'=>$uid,'id'=>$oid,'title'=>$title];
                event('SendYuyueMessage', ['data'=>$data,'type'=>1]);//通知预约成功
                event('SendYuyueMessage', ['data'=>['listid'=>$id],'type'=>5]);//通知新订单
            }else if($st==3){
                Db::name('general_shop_order')->where('id',$oid)->update(['pay_mo'=>$paymo,'status'=>2,'pay_order'=>$orderno]);
            }
        } catch (\Exception $e) {
            // $e->getMessage();
        }
        return $pay->success()->send();// laravel 框架中请直接 `return $alipay->success()`
    }

    public function xcxwxnotify()
    {

        $conf=$this->wxconf();
        $pay = Pay::wechat($conf);
        // $notifiedData = file_get_contents('php://input');
        // $log = "<br />\r\n\r\n".'==================='."\r\n".date("Y-m-d H:i:s")."\r\n".json_encode($notifiedData);
        // @file_put_contents('wx.txt', $log, FILE_APPEND);
        try{
            $data = $pay->verify(); // 是的，验签就这么简单！
            // 5、其它业务逻辑情况
            $orderno=$data->out_trade_no;

            $paymo=$data->total_fee;
            if($paymo){
                $paymo=$paymo/100;
            }

            $trade_no=$data->transaction_id;
            $info=Db::name('general_payorder')->where('ordernum',$orderno)->find();
            if(!$info){
                exit('success');//订单号不存在
            }
            if($info['status']==1){
                exit('success');//已经修改过状态了
            }
            $oid=$info['orderid'];
            $st=$info['style'];
            $uid=$info['uid'];
            //$msg=$info['msg'];
            //修改订单状态
            $newtime=gettime();
            Db::name('general_payorder')->where('id',$info['id'])->update(['status'=>1,'paymo'=>$paymo,'trade_no'=>$trade_no,'paytime'=>$newtime]);
            //修改订单状态 和 金额
            if($st==1){
                Db::name('general_yuyue_order')->where('id',$oid)->update(['paymo'=>$paymo,'uid'=>$uid,'status'=>2,'pay_order'=>$orderno]);
                YuyueListmsgHandler::add('订单支付完成-待核销',$oid,2,'已支付');
                $ord=Db::name('general_yuyue_order')->where('id',$oid)->field(['id','yuyue_id','uid'])->find();
                $id=$ord['yuyue_id'];
                event('SendYuyueMessage', ['data'=>['listid'=>$id],'type'=>5]);//通知新订单
            }else if($st==3){
                Db::name('general_shop_order')->where('id',$oid)->update(['pay_mo'=>$paymo,'status'=>2,'pay_order'=>$orderno]);
            }

        } catch (\Exception $e) {
            // $e->getMessage();
        }
        return $pay->success()->send();// laravel 框架中请直接 `return $alipay->success()`
    }

    /***退款订单***/
    public function h5refund(){
        //接收一个订单id
        $type=input("param.type/d");//退款类型
        if(!$type){
            $type=1;
        }
        $id=input("param.id/d");
        if(!$id){
            return $this->message('参数错误', [], 0);
        }
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $uid=$user->id;

        $order = Db::name('general_yuyue_order')->where('id',$id)->field(['id','paymo','money','y_data','y_time','status','pay_order','uid','heno'])->find();
        if(!$order){
            return $this->message('订单不存在', [], 0);
        }
        if($order['status']!=2){
            return $this->message('该订单状态不允许退款', [], 0);
        }
        if($order['uid']!=$uid){
            return $this->message('没有权限', [], 0);
        }
        if($order['paymo']<=0 && $order['money']>0){
            return $this->message('没有可退款金额', [], 0);
        }
        if($order['heno']>0){
            return $this->message('已经核销过的订单不允许退款', [], 0);
        }
        $ntit=' 00:00:00';
        //判断第一个预约的时间
        $yt=$order['y_time'];
        $tval=explode(",",$yt);
        if($tval){
            $tx=$tval[0];
            if($tx){
                $r=explode("-",$tx);
                if($r){
                    $ntit=' '.$r[0].':00';
                }
            }
        }
        $tktit=config('-timesite.yue_tuitimetit')?:'不满足提前退款时间';
        $newtime=date('Y-m-d H:i:s');
        //判断订单是否还有三天到预约时间
        $oldtime=$order['y_data'].$ntit;//预约时间
        if(strtotime($newtime)>=strtotime($oldtime)){
            return $this->message($tktit, [], 0);
        }
        $tqh=config('-timesite.yue_tuitime');
        if($tqh){
            $new=strtotime("+".$tqh." hours",strtotime($newtime));
            if($new>=strtotime($oldtime)){
                return $this->message($tktit, [], 0);
            }
        }
        $money=$order['paymo'];
        $pay_order=$order['pay_order'];
        $id=$order['id'];
        $paymo=$order['paymo']*100;

        if($order['paymo']==0 && $order['money']==0){
            //无需支付的订单  退款  直接修改订单状态
            Db::name('general_yuyue_order')->where('id',$id)->update(['status'=>5]);
            YuyueListmsgHandler::add('用户退款完成-无退款金额',$id,5,'已退款');
            //通知模板消息  公众号
            $data=['uid'=>$uid,'id'=>$id,'tit'=>'您的订单已退款成功-无退款金额','money'=>$money];
            event('SendYuyueMessage', ['data'=>$data,'type'=>3]);//通知预约成功

            return $this->message('退款成功-无退款金额', []);
        }

        $orderx = [
            'out_trade_no' => $pay_order,
            'out_refund_no' => $pay_order,
            'total_fee' => $paymo,
            'refund_fee' => $paymo,
            'refund_desc' => '用户主动退款',
        ];
        if($type==1){
            $conf=$this->wxh5conf();
            $wechat = Pay::wechat($conf);
            $result = $wechat->refund($orderx);
        }else if($type==2){
            $conf=$this->wxwapconf();
            $wechat = Pay::wechat($conf);
            $result = $wechat->refund($orderx);
        }else{
            $conf=$this->wxconf();
            $wechat = Pay::wechat($conf);
            $orderx['type']= 'miniapp';
            $result = $wechat->refund($orderx);
        }
        if($result['return_code']=='SUCCESS'){
            //修改订单状态为  已退款  修改支付状态为2 已退款
            Db::name('general_yuyue_order')->where('id',$id)->update(['status'=>5]);
            Db::name('general_payorder')->where('ordernum',$pay_order)->update(['status'=>2]);

            //通知模板消息  公众号

            $data=['uid'=>$uid,'id'=>$id,'tit'=>'您的订单已退款成功','money'=>$money];
            event('SendYuyueMessage', ['data'=>$data,'type'=>3]);//通知预约成功

            //查询是否有座位  有座位则取消座位
            Db::name('general_yuyue_seatmsg')->where('orderid',$id)->update(['status'=>2]);

            YuyueListmsgHandler::add('用户退款完成',$id,5,'已退款');
            return $this->message('退款成功', []);
        }else{
            return $this->message('退款失败，请联系客服'.$result['return_msg'], [], 0);
        }

    }
}