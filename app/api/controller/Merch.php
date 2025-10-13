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
use app\models\GeneralYuyueEmployees;
use app\models\GeneralYuyueList;
use app\models\GeneralYuyueTime;
use app\models\GeneralYuyueOrder;
use app\models\GeneralYuyueYuyuespec;
use app\models\GeneralYuyueOrd;

class Merch extends BaseController
{  
    //核销列表数据
    public function merlist(){
        $user = $this->user();
        if(!$user){
            return $this->message('请登录', [],201);
        }
        $uid=$user->id;
        $role=GeneralYuyueEmployees::where('uid',$uid)->whereLike('role','%1%')->find();
        if(!$role){
            return $this->message('没有核销权限', [],0);
        }
        $listval=GeneralYuyueEmployees::where('uid',$uid)->whereLike('role','%1%')->value('listval');//预约项目权限
        if($listval){
            $listval=explode(',',$listval);
        }
        //查询核销的规格
        $spec=GeneralYuyueEmployees::where('uid',$uid)->whereLike('role','%1%')->value('spec');//预约规格权限
        if($spec){
            $spec=explode(',',$spec);
        }
        //查询我可以核销的次数

        //查询多次核销下该用户是否能核销订单的该次
        $heno=$role['heno'];
        if($heno){
            //$heno是个字符串 需要分割,然后判断是否有权限
            $heno=explode(',',$heno);
            //循环并+1
            for($i=0;$i<count($heno);$i++){
                $heno[$i]=$heno[$i]-1;
            }
        }

        //查询该项目和该规格下面的未核销订单有多少个
        $whno=0;
        if($spec){
            //这段代码首先创建一个空的$conditions数组，然后对于$spec中的每个元素，都添加一个FIND_IN_SET条件到$conditions数组。然后，我们使用implode函数将$conditions数组转换为一个字符串，其中每个条件都用OR逻辑连接。最后，我们在查询中使用这个字符串作为条件。
            $conditions = [];
            foreach ($spec as $s) {
                $conditions[] = "FIND_IN_SET($s, specid)";
            }
            $conditionStr = implode(' OR ', $conditions);
            $conditionStr = $conditionStr.' OR specid=0';
            $whno = GeneralYuyueOrder::where('status',2)
                                    ->where('list_id','in',$listval)
                                    ->whereRaw($conditionStr)
                                    ->where('heno','in',$heno)
                                    ->count();
        }else{
            $whno = GeneralYuyueOrder::where('status',2)
                                    ->where('list_id','in',$listval)
                                    ->where('specid',0)
                                    ->where('heno','in',$heno)
                                    ->count();
        }
        //查询该项目和该规格下面的已核销订单有多少个
        $whyes=0;
        if($listval){
            // foreach($listval as $k=>$v){
            //     $whyes+=GeneralYuyueOrder::where('status',3)->where('list_id',$v)->where('specid','in',$spec)->where('heno','in',$heno)->count();
            // }

            if($spec){
            $conditions = [];
            foreach ($spec as $s) {
                $conditions[] = "FIND_IN_SET($s, specid)";
            }
            $conditionStr = implode(' OR ', $conditions);
            $conditionStr = $conditionStr.' OR specid=0';
            $whyes = GeneralYuyueOrder::where('status',3)
            ->where('list_id','in',$listval)
            ->whereRaw($conditionStr)
            ->where('heno','in',$heno)
            ->count();
            }else{
                $whyes = GeneralYuyueOrder::where('status',3)
                ->where('list_id','in',$listval)
                ->where('specid',0)
                ->where('heno','in',$heno)
                ->count();
            }
        }
        //返回数据
        $datas['whno']=$whno;
        $datas['whyes']=$whyes;
        return $this->message('请求成功', $datas,200);
    }
    //订单列表
    public function merorderlist(){
        $user = $this->user();
        if(!$user){
            return $this->message('请登录', [],201);
        }
        $uid=$user->id;
        $role=GeneralYuyueEmployees::where('uid',$uid)->whereLike('role','%1%')->find();
        if(!$role){
            return $this->message('没有核销权限', [],0);
        }
        $listval=GeneralYuyueEmployees::where('uid',$uid)->whereLike('role','%1%')->value('listval');//预约项目权限
        if($listval){
            $listval=explode(',',$listval);
        }
        //查询核销的规格
        $spec=GeneralYuyueEmployees::where('uid',$uid)->whereLike('role','%1%')->value('spec');//预约规格权限
        if($spec){
            $spec=explode(',',$spec);
        }
        $page=input('param.page/d');
        if(!$page){
            $page=1;
        }
        $where=[];
        $type=input('param.type/d');
        if($type){
            $where[]=['status','=',$type];
        }
        $str=input('param.str');
        $end=input('param.end');
        if($str && $end && $str!='请选择开始时间' && $end!='请选择结束时间'){
            $where[]=['addtime','between',[$str,$end]];
        }
        //查询多次核销下该用户是否能核销订单的该次
        $heno=$role['heno'];
        if($heno && $type!=3){
            //$heno是个字符串 需要分割,然后判断是否有权限
            $heno=explode(',',$heno);
            //循环并+1
            for($i=0;$i<count($heno);$i++){
                $heno[$i]=$heno[$i]-1;
            }
            $where[]=['heno','in',$heno];
        }
        $conditions = [];
        if($spec){
            foreach ($spec as $s) {
                $conditions[] = "FIND_IN_SET($s, specid)";
            }
            $conditionStr = implode(' OR ', $conditions);
            $conditionStr = $conditionStr.' OR specid=0';
            $count = GeneralYuyueOrder::where($where)
                                ->where('list_id','in',$listval)
                                ->whereRaw($conditionStr)
                                ->count();
            $list = GeneralYuyueOrder::where($where)
            ->where('list_id','in',$listval)
            ->whereRaw($conditionStr)
            ->order('id desc')->page($page,10)
            ->select();
        }else{
            $count = GeneralYuyueOrder::where($where)
                                ->where('list_id','in',$listval)
                                ->where('specid',0)
                                ->count();
            $list = GeneralYuyueOrder::where($where)
            ->where('list_id','in',$listval)
            ->where('specid',0)
            ->order('id desc')->page($page,10)
            ->select();
        }
        
        //$count=GeneralYuyueOrder::where($where)->where('list_id','in',$listval)->where('specid','in',$spec)->count();
        //$list=GeneralYuyueOrder::where($where)->where('list_id','in',$listval)->where('specid','in',$spec)->order('id desc')->page($page,10)->select();

        $data = [
            'count' => $count,
            'list' => array()
        ];
        $list->each(function ($item) use(&$data) {
            $img='';
            $title='';
            if($item->yuyuelist){
                $title=$item->yuyuelist->title;
            }
            if($item->yuyuespec){
                //$title.='('.$item->yuyuespec->title.')';
            }
            $nick=$item->user?$item->user->nick:'无';
            $img=$item->user?getFullImageUrl($item->user->headimg):getFullImageUrl('');
            $data['list'][] = [
                'id' => $item->id,
                'img' => $img,
                'title' => $title,
                'nick' => $nick,
                'name' => $item->name,
                'mobile' => $item->mobile,
                'y_data' => $item->y_data,
                'y_time' => $item->y_time,
                'money' => $item->money,
                'paymo' => $item->paymo,
                'status' => $item->status,
                'addtime' => $item->addtime
            ];
        });
        return $this->message('请求成功', $data);
    }
    public function platelist(){
        $user = $this->user();
        if(!$user){
            return $this->message('请登录', [],201);
        }
        $uid=$user->id;
        $role=GeneralYuyueEmployees::where('uid',$uid)->whereLike('role','%1%')->find();
        if(!$role){
            return $this->message('没有核销权限', [],0);
        }
        $listval=GeneralYuyueEmployees::where('uid',$uid)->whereLike('role','%1%')->value('listval');//预约项目权限
        if($listval){
            $listval=explode(',',$listval);
        }
        //查询核销的规格
        $spec=GeneralYuyueEmployees::where('uid',$uid)->whereLike('role','%1%')->value('spec');//预约规格权限
        if($spec){
            $spec=explode(',',$spec);
        }
        $where=[];
        $listid=input('param.listid/d');
//        if($listid){
//            $where[]=['id','=',$listid];
//        }
        //查询项目
        $venues=GeneralYuyueList::whereIn('id',$listval)->where($where)->field(['id','title'])->select()->toArray();
        if(!$listid && count($venues)>0){
            $listid=$venues[0]['id'];
        }
        $datas['listid']=$listid;
        //查询日期
        $days=config('-timesite.time_yuedayno')?:14;
        $date=week($days);
        $newdate=[];
        //查看这个是否有设置这个星期
        for($i=0;$i<count($date);$i++){
            $date[$i]['day']=date('m月d号',strtotime($date[$i]['riqi']));
            //查询有没有该时间
            $riqi=$date[$i]['riqi'];
            $wek=$date[$i]['wek'];
            if($wek==0){
                $wek=7;
            }
            //先查询是否有设置这个日期的时间
            $res=GeneralYuyueTime::where('pid',2)->where('p_val','like','%'.$riqi.'%')
                ->where('list_id',$listid)->where('status',1)->order('paiid desc,id desc')->find();
            if(!$res){
                //查询是否有设置这个星期的 时间
                $res=GeneralYuyueTime::where('pid',1)->where('p_val','like','%'.$wek.'%')
                ->where('list_id',$listid)->where('status',1)->order('paiid desc,id desc')->find();
            }
            if($res){
                array_push($newdate,$date[$i]);
            }
        }
        $datas['date']=$newdate;
        //查询场地和时间
        $riqi='';
        if($newdate && count($newdate)>0){
            $riqi=$newdate[0]['riqi'];
        }
        if(!$riqi){
            $riqi=date('Y-m-d');
        }
        $wek=date('w');
        if($wek==0){
            $wek=7;
        }
        $datas['riqi']=$riqi;
        $datas['wek']=$wek;

        $datas['venues']=$venues;
        //查询这个项目下面的所有规格
        $speclist=GeneralYuyueYuyuespec::where('list_id',$listid)->where('id','in',$spec)->field(['id','title'])->select()->toArray();
        //在speclist最前面增加一个无规格的
        $arr=['id'=>0,'title'=>'无规格'];
        array_unshift($speclist,$arr);
        $datas['speclist']=$speclist;
        return $this->message('请求成功', $datas,200);
    }
    public function platetime(){
        $user = $this->user();
        if(!$user){
            return $this->message('请登录', [],201);
        }
        $uid=$user->id;
        $role=GeneralYuyueEmployees::where('uid',$uid)->whereLike('role','%1%')->find();
        if(!$role){
            return $this->message('没有核销权限', [],0);
        }
        $listval=GeneralYuyueEmployees::where('uid',$uid)->whereLike('role','%1%')->value('listval');//预约项目权限
        if($listval){
            $listval=explode(',',$listval);
        }
        //查询核销的规格
        $spec=GeneralYuyueEmployees::where('uid',$uid)->whereLike('role','%1%')->value('spec');//预约规格权限
        if($spec){
            $spec=explode(',',$spec);
        }
        $listid=input('param.listid/d');
        $riqi=input('param.riqi');
        $cid=input('param.classxid/d');
        
        $where=[];
        if($listid){
            $where[]=['list_id','=',$listid];
        }
        if($riqi){
            $where[]=['y_data','=',$riqi];
        }
        if($cid){
            //$where[]=['specid','=',$cid];
        }
        //查询这个项目下面的所有订单
        if($spec){
            $conditions = [];
            foreach ($spec as $s) {
                $conditions[] = "FIND_IN_SET($s, specid)";
            }
            $conditionStr = implode(' OR ', $conditions);
        }
        if($cid){
            $orderlist=GeneralYuyueOrder::where('list_id',$listid)->where("FIND_IN_SET($cid, specid)")
            ->where('status','in',[2,3])->where($where)->field(['id','status','y_data','y_time','heno'])->select()->toArray();
        }else{
            $orderlist=GeneralYuyueOrder::where('list_id',$listid)
            //->whereRaw($conditionStr)
            ->where('specid',0)
            ->where('status','in',[2,3])->where($where)->field(['id','status','y_data','y_time','heno'])->select()->toArray();
        }
        //循环
        for($i=0;$i<count($orderlist);$i++){
            //根据订单id查询预约人姓名
            $oid=$orderlist[$i]['id'];
            $name=GeneralYuyueOrd::where('ord_id',$oid)->where('validate',1)->value('val')?:'未核销';
            $heno=$orderlist[$i]['heno'];
            if($heno>0){
                $name.='-'.$heno.'次';
            }
            //姓名
            $orderlist[$i]['name']=$name;
            //手机号
            $mobile=GeneralYuyueOrd::where('ord_id',$oid)->where('validate',2)->value('val')?:'无';
            $orderlist[$i]['mobile']=$mobile;
        }
        //返回数据
        $data['orderlist']=$orderlist;
        return $this->message('请求成功', $data,200);
    }
}