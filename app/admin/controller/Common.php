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
 * Time: 15:07
 */

namespace app\admin\controller;
use think\facade\Db;
use think\facade\View;
use think\facade\Session;

class Common
{
    protected function initialize()
    {
        //判断是否登录
        if(!session('admin_token')){
            return redirect('/admin/Login/index?22')->send();
        }else{
            $token=session('admin_token');
            //查询
            $x=Db::name('general_admin_login')->where(array('token'=>$token))->find();
            if(!$x){
                session('admin_token',null);
                return redirect('/admin/Login/index?33')->send();
            }
            //是否过期 默认7天
            $nowtime=time();//现在的时间
            $lodtime=$x['logintime'];//之前的时间
            $stimg=strtotime("+1day",$lodtime);
            if($nowtime > $stimg){
                session('admin_token',null);
                return redirect('/admin/Login/index?st=2')->send();
            }else{
                $admin=$x['username'];
                View::assign('admin',$admin);
            }
        }
    }
}