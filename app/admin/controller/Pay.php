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
 * Date: 2021/5/25
 * Time: 15:36
 */

namespace app\admin\controller;
use think\facade\View;
use think\facade\Db;
use app\admin\traits\AdminAuth;
use app\models\UserMoneylog;

class Pay extends Common
{
    use AdminAuth;

    public function usermoneylog(){
        $uid=input("param.uid");
        View::assign('uid', $uid);

        $permis = $this->getPermissions('Pay/usermoneylog');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        //增加
        $zjmo=Db::name('general_user_moneylog')->where('mo_type','=',1)->sum('money');
        //减少
        $jsmo=Db::name('general_user_moneylog')->where('mo_type','=',2)->sum('money');
        View::assign('zjmo', $zjmo);
        View::assign('jsmo', $jsmo);
        if(request()->isPost()){
            $page=input("param.page");
            if(!$page){
                $page=1;
            }
            $where=[];
            $uid=input("param.uid");
            if($uid){
                $where[]=['l.user_id','=',$uid];
            }
            $nick=input("param.nick");
            if($nick){
                $where[]=['u.nick','like','%'.$nick.'%'];
            }
            $limit=input("param.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            $str=input('param.str');
            $end=input('param.end');
            if($str && $end){
                //查询产品
                $count = Db::name('general_user_moneylog')->alias('l')
                    ->whereBetweenTime('addtime', $str, $end)
                    ->join('general_user_list u', 'u.id = l.user_id', 'LEFT')->where($where)->count();
                $data=Db::name('general_user_moneylog')->alias('l')
                    ->join('general_user_list u', 'u.id = l.user_id', 'LEFT')
                    ->where($where)
                    ->whereBetweenTime('addtime', $str, $end)
                    ->page($page,$limit)
                    ->field('u.nick,l.*')
                    ->order('id desc')
                    ->select();
            }else{
                //查询产品
                $count = Db::name('general_user_moneylog')->alias('l')->join('general_user_list u', 'u.id = l.user_id', 'LEFT')->where($where)->count();
                $data=Db::name('general_user_moneylog')->alias('l')
                    ->join('general_user_list u', 'u.id = l.user_id', 'LEFT')
                    ->where($where)
                    ->page($page,10)
                    ->field('u.nick,l.*')
                    ->order('id desc')
                    ->select();
            }
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        return View::fetch();
    }
    public function tixian(){
        $permis = $this->getPermissions('Pay/tixian');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        //可提现金额
        $ktmo=Db::name('general_user_list')->where('money','>',0)->sum('money');
        View::assign('ktmo', $ktmo);
        //已提现金额
        $ytmo=Db::name('general_user_info')->where('timo','>',0)->sum('timo');
        View::assign('ytmo', $ytmo);
        //提现中
        $txz=Db::name('general_user_withdrawal')->where('status','=',1)->sum('money');
        View::assign('txz', $txz);
        //本周会员提现
        $weekmo=Db::name('general_user_withdrawal')->whereWeek('addtime')->where('status','=',2)->sum('money');
        View::assign('weekmo', $weekmo);
        //本月会员提现
        $monthmo=Db::name('general_user_withdrawal')->whereMonth('addtime')->where('status','=',2)->sum('money');
        View::assign('monthmo', $monthmo);
        if(request()->isPost()){
            $page=input("get.page");
            if(!$page){
                $page=1;
            }
            $where=[];
            $uid=input("get.uid");
            if($uid){
                $where[]=['l.user_id','=',$uid];
            }
            $nick=input("get.nick");
            if($nick){
                $where[]=['u.nick','like','%'.$nick.'%'];
            }
            $limit=input("get.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            $str=input('get.str');
            $end=input('get.end');
            if($str && $end){
                //查询产品
                $count = Db::name('general_user_withdrawal')->alias('l')
                    ->whereBetweenTime('addtime', $str, $end)
                    ->join('general_user_list u', 'u.id = l.uid', 'LEFT')->where($where)->count();
                $data=Db::name('general_user_withdrawal')->alias('l')
                    ->join('general_user_list u', 'u.id = l.uid', 'LEFT')
                    ->where($where)
                    ->whereBetweenTime('addtime', $str, $end)
                    ->page($page,$limit)
                    ->field('u.nick,l.*')
                    ->order('id desc')
                    ->select();
            }else{
                //查询产品
                $count = Db::name('general_user_withdrawal')->alias('l')->join('general_user_list u', 'u.id = l.uid', 'LEFT')->where($where)->count();
                $data=Db::name('general_user_withdrawal')->alias('l')
                    ->join('general_user_list u', 'u.id = l.uid', 'LEFT')
                    ->where($where)
                    ->page($page,10)
                    ->field('u.nick,l.*')
                    ->order('id desc')
                    ->select();
            }
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        return View::fetch();
    }
    public  function getMoney(){
        //查询
        $st=input("param.st");
        $zjmo=0;
        $jsmo=0;
        $type=input("param.type");
        if($type==2){
            $tba='general_user_moneylog2';
        }else{
            $tba='general_user_moneylog';
        }
        if($st==1){//全部
            //增加
            $zjmo=Db::name($tba)->where('mo_type','=',1)->sum('money');
            //减少
            $jsmo=Db::name($tba)->where('mo_type','=',2)->sum('money');
        }else if($st==2){//7天
            //增加
            $zjmo=Db::name($tba)->whereWeek('addtime')->where('mo_type','=',1)->sum('money');
            //减少
            $jsmo=Db::name($tba)->whereWeek('addtime')->where('mo_type','=',2)->sum('money');
        }else if($st==3){//30天
            //增加
            $zjmo=Db::name($tba)->whereMonth('addtime')->where('mo_type','=',1)->sum('money');
            //减少
            $jsmo=Db::name($tba)->whereMonth('addtime')->where('mo_type','=',2)->sum('money');
        }
        $res = ["jsmo" => $jsmo,"status"=>1,'message'=>'请求成功','zjmo'=>$zjmo];
        return json($res);
    }

    //提现处理
    public function add(){
        $st=input("param.st");
        $id=input("param.id");
        $type=input("param.type");
        if(!$id){ $data['msg']='id不能为空';return json($data);exit;}
        if($st==1){
            if($type==2) {
                Db::name('general_user_withdrawal2')->where('id', $id)->update(['status' => 1, 'uptime' => gettime()]);
            }else{
                Db::name('general_user_withdrawal')->where('id', $id)->update(['status' => 1, 'uptime' => gettime()]);
            }
        }
        $msg='操作成功';
        $data = array('status' => 1,'msg' => $msg);
        return json($data);exit;
    }
    public function edit(){
        $st=input("param.st");
        $id=input("param.id");
        $type=input("param.type");
        if(!$id){ $data['msg']='id不能为空';return json($data);exit;}
        if($st==2){
            $msg=input('param.msg');
            if(!$msg){
                $data['msg']='拒绝理由不能为空';return json($data);exit;
            }
            if($type==2){
                Db::name('general_user_withdrawal2')->where('id',$id)->update(['status'=>2,'uptime'=>gettime(),'msg'=>$msg]);
            }else{
                Db::name('general_user_withdrawal')->where('id',$id)->update(['status'=>2,'uptime'=>gettime(),'msg'=>$msg]);
            }
        }
        $msg='操作成功';
        $data = array('status' => 1,'msg' => $msg);
        return json($data);exit;
    }
    public function del(){
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        $type=input("param.type");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            //删除数据
            if($arr[$i]){
                if($type==2){
                    Db::name('general_user_withdrawal2')->where('id',$arr[$i])->delete();
                }else{
                    Db::name('general_user_withdrawal')->where('id',$arr[$i])->delete();
                }
                $text = '删除了支付记录id='.$arr[$i].'的数据';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }

    public function delmoneylog(){
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        $type=input("param.type");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            //删除数据
            if($arr[$i]){
                if($type==2){
                    Db::name('general_user_moneylog2')->where('id',$arr[$i])->delete();
                }else{
                    Db::name('general_user_moneylog')->where('id',$arr[$i])->delete();
                }
                $text = '删除了资金记录id='.$arr[$i].'的数据';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }
    public function payorder(){
        //支付成功
        $zmo=Db::name('general_payorder')->where(array('status'=>1))->sum('paymo');
        View::assign('zmo', $zmo);
        //支付失败
        $daymo=Db::name('general_payorder')->where(array('status'=>0))->sum('paymo');
        View::assign('daymo', $daymo);
        //退款成功
        $weekmo=Db::name('general_payorder')->where(array('status'=>2))->sum('paymo');
        View::assign('weekmo', $weekmo);
        $permis = $this->getPermissions('Pay/payorder');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $page=input("param.page");
            if(!$page){
                $page=1;
            }
            $where=[];
            $uid=input("param.uid");
            if($uid){
                $where[]=['uid','=',$uid];
            }
            $style=input('param.style');
            if($style){
                $where[]=['style','=',$style];
            }
            $ordernum=input("param.ordernum");
            if($ordernum){
                $where[]=['ordernum','=',$ordernum];
            }
            $limit=input("param.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            $str=input('param.str');
            $end=input('param.end');
            if($str && $end){
                //查询产品
                $count = Db::name('general_payorder')->where($where)
                    ->whereBetweenTime('addtime', $str, $end)
                    ->count();
                $data=Db::name('general_payorder')->where($where)
                    ->whereBetweenTime('addtime', $str, $end)
                    ->page($page,$limit)
                    ->order('id desc')
                    ->select()->toArray();
                //支付成功
                $zmo1=Db::name('general_payorder')->where(array('status'=>1))->where($where)->whereBetweenTime('addtime', $str, $end)->sum('paymo');
                //支付失败
                $zmo2=Db::name('general_payorder')->where(array('status'=>0))->where($where)->whereBetweenTime('addtime', $str, $end)->sum('paymo');
                //退款成功
                $zmo3=Db::name('general_payorder')->where(array('status'=>2))->where($where)->whereBetweenTime('addtime', $str, $end)->sum('paymo');
            }else{
                //查询产品
                $count = Db::name('general_payorder')->where($where)->count();
                $data=Db::name('general_payorder')->where($where)
                    ->page($page,10)
                    ->order('id desc')
                    ->select()->toArray();
                //支付成功
                $zmo1=Db::name('general_payorder')->where(array('status'=>1))->where($where)->sum('paymo');
                //支付失败
                $zmo2=Db::name('general_payorder')->where(array('status'=>0))->where($where)->sum('paymo');
                //退款成功
                $zmo3=Db::name('general_payorder')->where(array('status'=>2))->where($where)->sum('paymo');
            }
            if($data){
                for($i=0;$i<count($data);$i++){

                    //查询用户
                    $uid=$data[$i]['uid'];
                    $nick=Db::name('general_user_list')->where('id',$uid)->value('nick');
                    if(!$nick){
                        $nick='默认用户'.$uid;
                    }
                    $data[$i]['nick']=$nick;
                    $headimg=Db::name('general_user_list')->where('id',$uid)->value('headimg');
                    if(!$headimg){
                        $data[$i]['headimg']=getFullImageUrl('/storage/imges/mo.png');
                    }else{
                        $data[$i]['headimg']=$headimg;
                    }

                }
            }
            $res = ["data" => $data,"code"=>0,'zmo1'=>$zmo1,'zmo2'=>$zmo2,'zmo3'=>$zmo3,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        return View::fetch();
    }

    public function delpaylog(){
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            //删除数据
            if($arr[$i]){
                Db::name('general_payorder')->where('id',$arr[$i])->delete();
                $text = '删除了支付记录id='.$arr[$i].'的数据';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }
}