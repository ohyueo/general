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
 * Date: 2021/6/9
 * Time: 13:38
 */

namespace app\api\controller;
use app\models\GeneralYuyueList;
use app\models\GeneralYuyueOrder;
use app\models\GeneralUserList;
use think\Request;
use app\BaseController;
use think\facade\Db;
use app\handler\YuyueListmsgHandler;
use app\models\GeneralYuyueEmployees;
//引入缓存
use think\facade\Cache;

class Order extends BaseController
{
    //核销订单
    public function xiaodata(Request $request){
        // 登录用户
        $user = $this->user();
        if(!$user){
            return $this->message('请登录', [],201);
        }
        $uid=$user->id;
        $data = $request->post();
        $id=intval($data['id']);
        //判断本人是不是核销员
        $ish=GeneralYuyueEmployees::where('uid',$uid)->find();
        if(!$ish){
            return $this->message('没有权限', [],0);
        }
        if($ish['status']==2){
            return $this->message('没有权限', [],0);
        }
        //判断订单是否已经核销过了
        $res=Db::name('general_yuyue_order')->where('id',$id)->field(['id','list_id','status','y_data','y_time','addtime','personid','specid','number','heno'])->find();
        if($res['y_data']!=date('Y-m-d')){
            return $this->message('该订单不是今天的,不能核销', [],0);
        }
        //查询权限
        $listval=GeneralYuyueEmployees::where('uid',$uid)->whereLike('listval','%'.$res['list_id'].'%')->find();
        if(!$listval){
            return $this->message('没有此项目的核销权限', [],0);
        }
        //如果有规格id 则查询规格权限
        $specid=$res['specid'];
        if($specid){
            $spec=GeneralYuyueEmployees::where('uid',$uid)->whereLike('role','%1%')->value('spec');//预约规格权限
            if($spec){
                $spec=explode(',',$spec);
            }
            $specid=explode(',',$specid);
            if($spec && $specid){
                $specid=array_intersect($spec,$specid);
                if(!$specid){
                    return $this->message('没有核销权限2', [],0);
                }
            }
            

            // if(!in_array($specid,$spec)){
            //     return $this->message('没有核销权限2', [],0);
            // }
            //查询多次核销下该用户是否能核销订单的该次
            $heno=$ish['heno'];
            if($heno && $res['status']!=3){
                //$heno是个字符串 需要分割,然后判断是否有权限
                $heno=explode(',',$heno);
                if(!in_array(($res['heno']+1),$heno)){
                    return $this->message('没有核销权限3', [],0);
                }
            }
        }
        $acid=$res['list_id'];
        $res['title']=Db::name('general_yuyue_list')->where('id',$acid)->value('title');
        //查询预约类型
        $res['ctit']=0;
        $resx=Db::name('general_yuyue_ord')->alias('o')
            ->join('general_yuyue_form f', 'o.form_id = f.id', 'LEFT')
            ->where('o.ord_id',$id)
            ->field('o.val,o.type,f.name')
            ->order('o.paiid desc,o.id desc')
            ->select()->toArray();
        if($resx){
            for($i=0;$i<count($resx);$i++){
                if($resx[$i]['type']==5){
                    $img=explode(',',$resx[$i]['val']);
                    if($img){
                        for($x=0;$x<count($img);$x++){
                            $img[$x]=getFullImageUrl($img[$x]);
                        }
                    }
                    $resx[$i]['val']=$img;
                }
            }
        }

        //查询是否有人员
        $personid=$res['personid'];
        if($personid){
            $tit=Db::name('general_yuyue_personnel')->where('id',$personid)->value('title');
            $trr=['name'=>'选择人员','val'=>$tit];
            array_push($resx,$trr);
        }
        //查询是否有座位
        $seat=Db::name('general_yuyue_seatmsg')->where('orderid',$id)->value('title');
        if($seat){
            $trr=['name'=>'选择座位','val'=>$seat];
            array_push($resx,$trr);
        }
        //查询是否有规格
        $specid=$res['specid'];
        if($specid){
            $tit=Db::name('general_yuyue_yuyuespec')->where('id','in',$specid)->column('title');
            $tit=implode(',',$tit);
            $trr=['name'=>'选择规格','val'=>$tit];
            array_push($resx,$trr);
        }

        $res['res']=$resx;

        if($res['status']==3){
            return $this->message('该订单已核销', $res,1);
        }
        if($res['status']!=2){
            return $this->message('该订单无需核销', [],0);
        }
        return $this->message('请求成功', $res);
    }

    //核销订单
    public function hexiao(Request $request){
        // 登录用户
        $user = $this->user();
        if(!$user){
            return $this->message('请登录', [],201);
        }
        $uid=$user->id;
        //10秒内只能核销一次，使用缓存
        $cache_key = 'hexiao_'.$uid;
        if(Cache::get($cache_key)){
            return $this->message('请勿频繁操作', [],0);
        }
        Cache::set($cache_key, 1, 10);
        
        $data = $request->post();
        $id=intval($data['id']);
        //判断本人是不是核销员
        $ish=GeneralYuyueEmployees::where('uid',$uid)->find();
        if(!$ish){
            return $this->message('你不是核销员', [],0);
        }
        if($ish['status']==2){
            return $this->message('核销员状态异常', [],0);
        }
        //判断是不是有核销当前项目的权限
        $listval=GeneralYuyueEmployees::where('uid',$uid)->whereLike('role','%1%')->value('listval');//预约项目权限
        if($listval){
            $listval=explode(',',$listval);
        }
        //判断订单是否已经核销过了
        $res=GeneralYuyueOrder::where('id',$id)->where('list_id','in',$listval)->field(['id','uid','money','status','y_data','y_time','list_id','specid','heno'])->find();
        if(!$res){
            return $this->message('没有核销权限', [],0);
        }
        if($res['y_data']!=date('Y-m-d')){
            return $this->message('该订单不是今天的,不能核销', [],0);
        }

        $autotime=config('-timesite.time_autohe');//自动核销时间  
        //如果预约的时间小于当前时间减去自动核销时间则不能签到
        $starttime=Db::name('general_yuyue_ordermultiple')->where('order_id',$id)->order('id asc')->value('y_start_time');
        if(strtotime($starttime)<time()-$autotime){
            return $this->message('预约时间已过期，不可核销', [],0);
        }

        //查询权限
        $listval=GeneralYuyueEmployees::where('uid',$uid)->whereLike('listval','%'.$res['list_id'].'%')->find();
        if(!$listval){
            return $this->message('没有此项目的核销权限', [],0);
        }
        //如果有规格id 则查询规格权限
        $specid=$res['specid'];
        if($specid){
            $spec=GeneralYuyueEmployees::where('uid',$uid)->whereLike('role','%1%')->value('spec');//预约规格权限
            if($spec){
                $spec=explode(',',$spec);
            }
            if($spec && $specid){
                $specid=explode(',',$specid);
                $specid=array_intersect($spec,$specid);
                if(!$specid){
                    return $this->message('没有核销权限2', [],0);
                }
            }
            
            // if(!in_array($specid,$spec)){
            //     return $this->message('没有核销权限2', [],0);
            // }
            //查询多次核销下该用户是否能核销订单的该次
            $heno=$ish['heno'];
            if($heno){
                //$heno是个字符串 需要分割,然后判断是否有权限
                $heno=explode(',',$heno);
                if(!in_array($res['heno']+1,$heno)){
                    return $this->message('没有核销权限3', [],0);
                }
            }
        }
        if($res['status']==3){
            return $this->message('该订单已核销', [],0);
        }
        if($res['status']!=2){
            return $this->message('该订单无需核销', [],0);
        }
        //如果有规格 查询是否有核销规格的权限
        // $specid=$res['specid'];
        // if($specid){
        //     $spec=GeneralYuyueEmployees::where('uid',$uid)->whereLike('role','%1%')->value('spec');//预约规格权限
        //     if($spec){
        //         $spec=explode(',',$spec);
        //     }
        //     if(!in_array($specid,$spec)){
        //         return $this->message('没有核销权限2', [],0);
        //     }
        // }
        $aid=$res['list_id'];
        //存入核销记录
        $arr=array(
            'order_id' => $id,
            'list_id' => $aid,
            'uid' => $uid,
            'emp_id' => $ish['id'],
            'addtime' => gettime(),
            'st' =>  0
        );
        Db::name('general_yuyue_verification')->insert($arr);
        //增加核销次数
        $res->heno+=1;
        $res->save();
        //如果有规格则查询规格设置的核销次数
        $specid=$res['specid'];
        if($specid){
            $heno=Db::name('general_yuyue_yuyuespec')->where('id','in',$specid)->sum('heno');
            if($heno){
                $henos=Db::name('general_yuyue_list')->where('id',$aid)->value('heno');
                $heno=intval($heno+$henos);//总数 规格+基础预约的核销次数
                if($res['heno']>=$heno){
                    $res->status=3;
                    $res->save();
                    YuyueListmsgHandler::add($ish['id'].'号核销员核销-已完成',$id,3,'预约已核销完成');
                    //核销之后 发放推荐奖励
                    //交易成功  客户的介绍人分销
                    $money=$res['money'];
                    $u=$res['uid'];
                    $user=GeneralUserList::find($u);
                    event('Receive', ['user'=>$user,'money'=>$money]);
                    return $this->message('核销成功', $res);
                }else{
                    YuyueListmsgHandler::add($ish['id'].'号核销员核销-未完成',$id,2,'预约已核销'.$res['heno'].'次');
                    return $this->message('核销成功', $res);
                }
            }
        }else{ //如果没有规格  则直接查询项目设置的核销次数
            $heno=Db::name('general_yuyue_list')->where('id',$aid)->value('heno');
            if($heno){
                $heno=intval($heno);
                if($res['heno']>=$heno){
                    $res->status=3;
                    $res->save();
                    YuyueListmsgHandler::add($ish['id'].'号核销员核销-已完成',$id,3,'预约已核销完成');
                    //核销之后 发放推荐奖励
                    //交易成功  客户的介绍人分销
                    $money=$res['money'];
                    $u=$res['uid'];
                    $user=GeneralUserList::find($u);
                    event('Receive', ['user'=>$user,'money'=>$money]);
                    return $this->message('核销成功', $res);
                }else{
                    YuyueListmsgHandler::add($ish['id'].'号核销员核销-未完成',$id,2,'预约已核销'.$res['heno'].'次');
                    return $this->message('核销成功', $res);
                }
            }
        }
        $res->status=3;
        $res->save();
        YuyueListmsgHandler::add($ish['id'].'号核销员核销-已完成',$id,3,'预约已核销完成');

        //核销之后 发放推荐奖励
        //交易成功  客户的介绍人分销
        $money=$res['money'];
        $u=$res['uid'];
        $user=GeneralUserList::find($u);
        event('Receive', ['user'=>$user,'money'=>$money]);

        return $this->message('核销成功', $res);
    }
    //上传表单图片
    public function upload(Request $request){
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        //验证码token end
        $result=[];
        $file = request()->file('file');
        try {
            validate(['file' => [
                'fileSize' => 3145728,
                'fileExt' => 'jpg,png,gif,jpeg',
                'fileMime' => 'image/jpeg,image/png,image/gif',
            ]])->check(['file' => $file]);
            $savename = \think\facade\Filesystem::disk('public')->putFile( 'order', $file);
            if (!$savename) {
                return json(['', 'code' => 0, 'message' => '上传失败']);
            }
            //返回图片
            $img='/storage/'.str_replace('\\', '/', $savename);
            $savename=getFullImageUrl($img);
            $savename=str_replace('\\', '/', $savename);
            return json(['savename' => $savename,'img'=>$img, 'code' => 200, 'message' => '上传成功']);
        } catch (ValidateException $e) {
            return $e->getMessage();
        }
    }
    //查询到期未核销的订单  /resource/Order/weiheord
    public function weiheord(){
        $isopen=config('-timesite.isopenauto')?:0;//是否开启
        if(!$isopen){
            echo '未开启';exit;
        }
        $autotype=config('-timesite.autotype')?:1;;//1自动核销  2自动取消
        $autotime=config('-timesite.time_autohe')?:30;
        //查询 general_yuyue_ordermultiple 里面的 status=2 的订单 y_start_time 开始时间 自动类型是time  开始后30分钟自动核销
        $riqi=date('Y-m-d');
        //我要查询  30分钟之前的订单  全部取消
        if($autotime){
            $old=date('Y-m-d H:i:s',strtotime(" -".$autotime." min"));
        }

        $res=Db::name('general_yuyue_ordermultiple')->where('status',2)
        ->where('y_data',$riqi)
        //->fetchSql()
        //->where('y_start_time', '<', $old)  //因为要减去30分钟  有可能是23:59  会出现问题 所以去掉
        ->field(['id','list_id','y_data','y_time','order_id','y_start_time','uid'])->select()->toArray();
        if($res){
            for($i=0;$i<count($res);$i++){
                $yuyueTime = $res[$i]['y_data'].' '.$res[$i]['y_start_time'];
                //echo $yuyueTime.'=='.$old;
                if ($yuyueTime < $old) {
                    $title='超时未核销-系统自动核销';
                    $id=$res[$i]['order_id'];
                    if($autotype==1){
                        echo $id.'到期核销了';
                        Db::name('general_yuyue_order')->where('id',$id)->update(['ishe'=>1,'status'=>3]);
                        YuyueListmsgHandler::add('超时未核销-系统自动核销',$id,3,'自动核销');
                    }else if($autotype==2){
                        echo $id.'到期取消了';
                        Db::name('general_yuyue_order')->where('id',$id)->update(['cancel'=>1,'status'=>4]);
                        YuyueListmsgHandler::add('超时未核销-系统自动取消',$id,4,'自动取消');
                        $title='超时未核销-系统自动取消';
                    }
                    //记录违约记录
                    $uid=$res[$i]['uid'];
                    $arrx=[
                        'title'=>$title,
                        'uid'=>$uid,
                        'listid'=>$res[$i]['list_id'],
                        'orderid'=>$id,
                        'adtime'=>gettime()
                    ];
                    Db::name('general_default_defaultlist')->insert($arrx);
                }
            }
        }
        echo "okk";
    }
    //查询即将开始的订单  /resource/Order/settimeorder
    public function settimeorder(){
        //提前1小时通知
        $hour=config('-timesite.time_advnotime')?:1;
        $tx=$hour*60*60;
        $da=date('Y-m-d');
        $res=Db::name('general_yuyue_order')->where('status',2)->where('y_data',$da)->where('tz',0)->field(['id','list_id','y_data','y_time','uid'])->select()->toArray();
        if($res){
            for($i=0;$i<count($res);$i++){
                $id=$res[$i]['id'];
                $uid=$res[$i]['uid'];
                $yuyue_id=$res[$i]['list_id'];
                $tax=$res[$i]['y_data'];
                $time=$res[$i]['y_time'];
                $rx=explode(",",$time);
                $ta=$rx[0];
                $rx=explode("-",$ta);
                $tb=$rx[0];
                $yt=$tax.' '.$tb;
                if(time()>=strtotime($yt)-$tx){
                    echo $id.'提醒到了';
                    $title=Db::name('general_yuyue_list')->where('id',$yuyue_id)->value('title');
                    $address=Db::name('general_yuyue_list')->where('id',$yuyue_id)->value('address');
                    $mobile=Db::name('general_yuyue_list')->where('id',$yuyue_id)->value('mobile');
                    //模板通知
                    $data=['uid'=>$uid,'id'=>$id,'title'=>$title,'yt'=>$yt,'address'=>$address,'mobile'=>$mobile,'hour'=>$hour];
                    event('SendYuyueMessage', ['data'=>$data,'type'=>4]);//通知预约成功

                }
            }
        }
        echo "ok";
    }
    //自动过期  30分钟未付款  /resource/Order/guoqiord
    public function guoqiord(){
        $daoqi=config('-timesite.time_paydaoqi')?:30;
        $new=date('Y-m-d H:i:s',strtotime(" -".$daoqi." min"));
        $res=Db::name('general_yuyue_order')->where('status',1)->whereTime('addtime', '<', $new)->field(['id','list_id','y_data','y_time','uid','addtime'])->select()->toArray();
        if($res){
            for($i=0;$i<count($res);$i++){
                //修改状态为取消  4
                $id=$res[$i]['id'];
                Db::name('general_yuyue_order')->where('id',$id)->update(['cancel'=>1,'status'=>4]);
                $yid=$res[$i]['list_id'];
                $uid=$res[$i]['uid'];
                $y_data=$res[$i]['y_data'];
                $title=GeneralYuyueList::where('id',$yid)->value('title');

                YuyueListmsgHandler::add('订单自动取消',$id,4,'超时未付款');

                $data=['uid'=>$uid,'id'=>$id,'title'=>$title,'y_data'=>$y_data,'tit1'=>'订单自动取消','tit2'=>'超时未付款'];
                event('SendYuyueMessage', ['data'=>$data,'type'=>2]);//通知预约成功

            }
        }
        echo 'ok';
    }
}