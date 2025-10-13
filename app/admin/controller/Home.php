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
 * Date: 2021/5/17
 * Time: 10:59
 */

namespace app\admin\controller;
use think\facade\View;
use think\facade\Db;
use app\admin\traits\AdminAuth;

class Home extends Common
{
    use AdminAuth;
    public function console(){
        $this->getPermissions('Home/console');
        //总共用户
        $zuno=Db::name('general_user_list')->count();
        View::assign('zuno', $zuno);
        //今日注册
        $dayuno=Db::name('general_user_login')->whereDay('reg_time')->count();
        View::assign('dayuno', $dayuno);
        //今日预约
        $dayyu=Db::name('general_yuyue_order')->whereDay('addtime')->count();
        View::assign('dayyu', $dayyu);
        //总预约
        $zyu=Db::name('general_yuyue_order')->count();
        View::assign('zyu', $zyu);

        //今日核销
        $dayhe=Db::name('general_yuyue_verification')->whereDay('addtime')->count();
        View::assign('dayhe', $dayhe);
        //总核销
        $zhe=Db::name('general_yuyue_verification')->count();
        View::assign('zhe', $zhe);

        //查询今日活跃用户
        $dayhuo=Db::query("SELECT count(DISTINCT uid) FROM `general_yuyue_browse` WHERE TO_DAYS(addtime) = TO_DAYS(NOW())");
        if($dayhuo){
            $dayhuo=$dayhuo[0]['count(DISTINCT uid)'];
        }
        View::assign('dayhuo', $dayhuo);
        //查询昨日活跃用户
        $oldhuo=Db::query("SELECT count(DISTINCT uid) FROM `general_yuyue_browse` WHERE TO_DAYS(NOW()) - TO_DAYS(addtime) = 1 ");
        if($oldhuo){
            $oldhuo=$oldhuo[0]['count(DISTINCT uid)'];
        }
        View::assign('oldhuo', $oldhuo);
        //查询注册人数
        $province=Db::name('general_map_regprovince')->order('value desc')->limit(8)->select()->toArray();
        View::assign('province', $province);
        //查询是否同意了
        $ty=1;
        $time=0;
        $username = session('admin_name');
        $res=Db::name('general_admin')->where('username',$username)->field(['id','gid','text','updatetime'])->find();
        if($res['gid']==1 && (!$res['text'] || !$res['updatetime'])){
            $ty=0;
        }else{
            $time=$res['updatetime'];
        }
        View::assign('ty', $ty);
        View::assign('time', $time);

        return View::fetch();
    }
    public function getprovince(){
        //查询注册人数
        $province=Db::name('general_map_regprovince')->field(['name','value'])->order('value desc')->select()->toArray();
        return json($province);exit;
    }
    public function getpvtime(){
        //查询注册人数
        $d=date('Ymd');
        $datex = array();
        $zhuce=[];//注册数量
        $qingqiu=[];//请求数量
        $user=[];//活跃用户
        for($i=7;$i>=0;$i--){
            $riqi=date('Y-m-d',(mktime(0, 0, 0, substr($d,4,2) , substr($d,6,2)-$i, substr($d,0,4))));
            $datex[] = $riqi;//最近30天数据表
            $zcno=Db::name('general_user_login')->whereDay('reg_time',$riqi)->count();
            $zhuce[]=$zcno;
            $qingno=Db::name('general_yuyue_browse')->whereDay('addtime',$riqi)->field(['ip'])->count('distinct ip');
            $qingqiu[]=$qingno;
            $userno=Db::name('general_yuyue_browse')->whereDay('addtime',$riqi)->field(['uid'])->count('distinct uid');
            $user[]=$userno;
        }
        $data=[];
        $data['time']=$datex;
        $data['zhuce']=$zhuce;
        $data['qingqiu']=$qingqiu;
        $data['user']=$user;

        return json($data);exit;
    }
    public function updatexieyi(){
        $username = session('admin_name');
        $tit='系统受国家计算机软件著作权保护,请从正规渠道获得系统。本系统遵循《产品使用协议》使用本系统则认为同意该协议以及以下附加协议：系统不得用于违反国家法律法规的用途请合理合法使用本系统，使用过程中产生的任何民事刑事责任均由使用者承担，与开发者无关任何经由本系统而发布、上传的文字、资讯、资料、音乐、照片、图形、视讯、信息或其它资料（以下简称“内容”）请上传者自行审核其资质和合法性，由此产生的任何后果均由内容上传者承担责任。';
        $res=Db::name('general_admin')->where('username',$username)->field(['id','text','updatetime'])->find();
        if(!$res['text'] || !$res['updatetime']){
            Db::name('general_admin')->where('id',$res['id'])->update(['text'=>$tit,'updatetime'=>gettime()]);
        }
    }
}