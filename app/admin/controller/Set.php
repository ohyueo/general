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
 * Date: 2021/5/29
 * Time: 16:05
 */

namespace app\admin\controller;
use think\facade\View;
use app\admin\traits\AdminAuth;
use think\facade\Db;

class Set extends Common
{
    use AdminAuth;
    public function dowwxapp(){
        $file_name = "wxapp.zip";     //下载文件名
        $file_dir = "../programs/";        //下载文件存放目录    //检查文件是否存在
        if (! file_exists ( $file_dir . $file_name )) {
            header('HTTP/1.1 404 NOT FOUND');
        } else {
            //以只读和二进制模式打开文件
            $file = fopen ( $file_dir . $file_name, "rb" );
            //告诉浏览器这是一个文件流格式的文件
            Header ( "Content-type: application/octet-stream" );
            //请求范围的度量单位
            Header ( "Accept-Ranges: bytes" );
            //Content-Length是指定包含于请求或响应中数据的字节长度
            Header ( "Accept-Length: " . filesize ( $file_dir . $file_name ) );
            //用来告诉浏览器，文件是可以当做附件被下载，下载后的文件名称为$file_name该变量的值。
            Header ( "Content-Disposition: attachment; filename=" . $file_name );
            //读取文件内容并直接输出到浏览器
            echo fread ( $file, filesize ( $file_dir . $file_name ) );
            fclose ( $file );
            exit ();
        }
    }
    public function systemsite(){
        $permis = $this->getPermissions('Set/systemsite');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $data = input("param.");
            $str = '<?php return [';
            foreach ($data as $key => $value) {
                $str .= '\'' . $key . '\'' . '=>' . '\'' . $value . '\'' . ',';
            }
            $str .= ']; ';
            $file = "../config/-systemsite.php";
            if (file_put_contents($file, $str)) {
                return "<script>alert('修改成功');window.location.href =location.href;</script>";
            } else {
                return "<script>alert('修改失败');window.location.href =location.href;</script>";
            }
        }
        return View::fetch();
    }
    public function smssite(){
        $permis = $this->getPermissions('Set/smssite');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $data = input("param.");
            $str = '<?php return [';
            foreach ($data as $key => $value) {
                $str .= '\'' . $key . '\'' . '=>' . '\'' . $value . '\'' . ',';
            }
            $str .= ']; ';
            $file = "../config/-smssite.php";//config_path('smssite.php');
            if (file_put_contents($file, $str)) {
                return "<script>alert('修改成功');window.location.href =location.href;</script>";
            } else {
                return "<script>alert('修改失败');window.location.href =location.href;</script>";
            }
        }
        return View::fetch();
    }
    public function remindsite(){
        $permis = $this->getPermissions('Set/remindsite');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $data = input("param.");
            $str = '<?php return [';
            foreach ($data as $key => $value) {
                $str .= '\'' . $key . '\'' . '=>' . '\'' . $value . '\'' . ',';
            }
            $str .= ']; ';
            $file = "../config/-remindsite.php";//config_path('smssite.php');
            if (file_put_contents($file, $str)) {
                return "<script>alert('修改成功');window.location.href =location.href;</script>";
            } else {
                return "<script>alert('修改失败');window.location.href =location.href;</script>";
            }
        }
        return View::fetch();
    }
    public function wxsite(){
        $permis = $this->getPermissions('Set/wxsite');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $data = input("param.");
            $str = '<?php return [';
            foreach ($data as $key => $value) {
                $str .= '\'' . $key . '\'' . '=>' . '\'' . $value . '\'' . ',';
            }
            $str .= ']; ';
            $file = "../config/-wxsite.php";//config_path('smssite.php');
            if (file_put_contents($file, $str)) {
                return "<script>alert('修改成功');window.location.href =location.href;</script>";
            } else {
                return "<script>alert('修改失败');window.location.href =location.href;</script>";
            }
        }
        return View::fetch();
    }
    public function timesite(){
        $permis = $this->getPermissions('Set/timesite');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $data = input("param.");
            $str = '<?php return [';
            foreach ($data as $key => $value) {
                $str .= '\'' . $key . '\'' . '=>' . '\'' . $value . '\'' . ',';
            }
            $str .= ']; ';
            $file = "../config/-timesite.php";//config_path('smssite.php');
            if (file_put_contents($file, $str)) {
                return "<script>alert('修改成功');window.location.href =location.href;</script>";
            } else {
                return "<script>alert('修改失败');window.location.href =location.href;</script>";
            }
        }
        return View::fetch();
    }

    public function mapsite(){
        $permis = $this->getPermissions('Set/mapsite');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $data = input("param.");
            $str = '<?php return [';
            foreach ($data as $key => $value) {
                $str .= '\'' . $key . '\'' . '=>' . '\'' . $value . '\'' . ',';
            }
            $str .= ']; ';
            $file = "../config/-mapsite.php";//config_path('smssite.php');
            if (file_put_contents($file, $str)) {
                return "<script>alert('修改成功');window.location.href =location.href;</script>";
            } else {
                return "<script>alert('修改失败');window.location.href =location.href;</script>";
            }
        }
        return View::fetch();
    }
    public function website(){
        $permis = $this->getPermissions('Set/website');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        return View::fetch();
    }
    public function appsite(){
        $permis = $this->getPermissions('Set/appsite');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $data = input("param.");
            $str = '<?php return [';
            foreach ($data as $key => $value) {
                $str .= '\'' . $key . '\'' . '=>' . '\'' . $value . '\'' . ',';
            }
            $str .= ']; ';
            $file = "../config/-appsite.php";//config_path('smssite.php');
            if (file_put_contents($file, $str)) {
                return "<script>alert('修改成功');window.location.href =location.href;</script>";
            } else {
                return "<script>alert('修改失败');window.location.href =location.href;</script>";
            }
        }
        return View::fetch();
    }
    public function password()
    {
        if(request()->isPost()){
            //修改密码
            $data=array('status' => 0,'msg' => '未知错误');
            $oldPassword=input("param.oldPassword");
            $password=input("param.password");
            $repassword=input("param.repassword");

            //判断后两次密码是否一致
            if($password!=$repassword){
                $data['msg'] = "两次新密码不一致!";
                return json($data);exit;
            }
            if(!$oldPassword || !$password || !$repassword){
                $data['msg'] = "参数错误!";
                return json($data);exit;
            }
            //先比对旧密码
            $username=session('admin_name');

            $tmp = Db::name('general_admin')->where(array('username' => $username))->find();
            if(!$tmp){
                $data['msg'] = "用户不存在!";
                return json($data);exit;
            }
            if(!$tmp['status']){
                $data['msg'] = "该用户被禁用!";
                return json($data);exit;
            }
            //比对密码
            $oldPassword = $this->getpass($oldPassword);
            if($oldPassword!=$tmp['password']){
                $data['msg'] = "旧密码错误!";
                return json($data);exit;
            }
            //修改新密码
            $password = $this->getpass($password);
            $tmpxx = Db::name('general_admin')->where(array('username' => $username))->update(['password' => $password]);
            if($tmpxx){
                $data['status']=1;
                //$data['url'] = '/' . config('api.admin_address');
                session('admin_name', null);
                session('admin_token', null);
            }
            return json($data);exit;
        }
        return View::fetch();
    }
    protected function getpass($pass){
        return md5('*&>'.md5($pass) );
    }
}