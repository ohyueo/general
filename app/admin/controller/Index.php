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
 * Time: 10:49
 */

namespace app\admin\controller;
use think\facade\View;
use think\facade\Db;

class Index extends Common
{
    public function __construct()
    {
        parent::initialize();
    }

    public function index(){
        $username = session('admin_name');
        $admin = Db::name('general_admin')->where('username', $username)->field('id, name')->find();
        if(!$admin){
            echo '用户不存在';
            return redirect('/admin/Login/index?st=2')->send();
        }
        //查看该用户的角色
        $role = Db::name('general_admin_role')->alias('r')->join('general_admin_role_permission p', 'r.id = p.role_id', 'LEFT')->join('general_admin_user_role u', 'u.role_id = r.id', 'LEFT')->field('r.name, p.permission_id')->where('u.admin_id', $admin['id'])->find();
        //获取侧边栏菜单
        //一级菜单
        $top = Db::name('general_admin_permission')->where('is_nav', 1)->where('parent_id', 0)->where('Id', 'in', $role['permission_id'])->order('p_id asc,id asc')->select();
        //二级菜单
        $sec = Db::name('general_admin_permission')->where('is_nav', 2)->where('parent_id', '<>', 0)->where('Id', 'in', $role['permission_id'])->order('p_id asc,id asc')->select();
        $menu = Db::name('general_admin_permission')->where('is_nav', 1)->where('parent_id', '<>', 0)->where('Id', 'in', $role['permission_id'])->order('p_id asc,id asc')->select();

        View::assign('sec', $sec);
        View::assign('top', $top);
        View::assign('menu', $menu);
        View::assign('admin', $admin);

        $quan=1;//$this->isquan();
        View::assign('quan',$quan);
        return View::fetch();
    }
    public function yuyueimg(){
        //验证码token end
        $id=input("param.id/d");
        if(!$id){
            $result = [
                'code' => -1,
                'msg'  => '参数错误'
            ];
            return $result;
        }
        //查询数量 不得大于5
        $cno=Db::name('general_yuyue_img')->where('acid',$id)->count();
        if($cno>4){
            $result = [
                'code' => -1,
                'msg'  => '上传数量限制，介绍轮播图最多五张'
            ];
            return $result;
        }
        $result=[];
        $files = request()->file();
        try {
            validate(['file' => [
                'fileSize' => 3145728,
                'fileExt' => 'jpg,png,gif,jpeg',
                'fileMime' => 'image/jpeg,image/png,image/gif',
            ]])->check(['file' => $files]);
            $savename = [];
            foreach($files as $file) {
                $savename[] = \think\facade\Filesystem::disk('public')->putFile( 'admin_images', $file);
            }
            $img='/storage/'.str_replace('\\','/',$savename[0]);
            $gid=Db::name('general_yuyue_img')->insertGetId(['acid'=>$id,'img'=>$img]);
            $result = [
                'id'   => $gid,
                'code'     => 0,
                'msg'      => '上传成功',
                'filename' => $img
            ];
        } catch (\think\exception\ValidateException $e) {
            $result = [
                'code' => -1,
                'msg'  => $e->getMessage()
            ];
        }
        return $result;
    }

    public function upload(){
        //验证码token end
        $result=[];
        $type=input('param.type');
        $files = request()->file();
        // foreach($files as $file) {
        //     $isse=check_illegal($file);
        //     if(!$isse){
        //         $result = [
        //             'code' => -1,
        //             'msg'  => '上传异常'
        //         ];
        //         return $result;exit;
        //     }
        // }
        try {
            validate(['file' => [
                'fileSize' => 3145728,
                'fileExt' => 'jpg,png,gif,jpeg,mp4,pdf',
                'fileMime' => 'image/jpeg,image/png,image/gif,video/mp4,application/pdf,application/x-pdf',
            ]])->check(['file' => $files]);
            $cloud_type=config('-cloudsite.cloud_type')??0;//云存储类型  0本地 1七牛 2腾讯云
            if($cloud_type==2){
                //腾讯云储存
                $file=$files['file']??$files['edit'];
                // 图片存储在本地的临时路经
                $filePath = $file->getRealPath();
                // 获取图片后缀
                $ext = $file->getOriginalExtension();
                // 上传到腾讯云后保存的新图片名
                $tim=date('Ymd');
                $newImageName  =   'order/'.$tim.'/'.substr(md5($file->getOriginalName()),0,6)
                    .  rand(00000,99999) . '.'.$ext;
                TencentCloud::upload($newImageName,$filePath);
                $domain=getMerConfig('-cloudsite.domain');
                //如果domain后面没有/则加上
                if(substr($domain,-1)!='/'){
                    $domain.='/';
                }
                $result = [
                    'code'     => 0,
                    'msg'      => '上传成功',
                    'data' => $domain.$newImageName,
                    'img' => $domain.$newImageName,
                    'filename' => $newImageName
                ];
                if($type){
                    $result['filename']=$domain.$newImageName;
                }
            }else if($cloud_type==1){
                //七牛云地址
                $file=$files['file'];
                // 图片存储在本地的临时路经
                $filePath = $file->getRealPath();
                // 获取图片后缀
                $ext = $file->getOriginalExtension();
                // 上传到七牛后保存的新图片名
                $tim=date('Ymd');
                $newImageName  =   'order/'.$tim.'/'.substr(md5($file->getOriginalName()),0,6)
                    .  rand(00000,99999) . '.'.$ext;
                Qiniu::upload($newImageName,$filePath);
                $domain=getMerConfig('-cloudsite.domain');
                //如果domain后面没有/则加上
                if(substr($domain,-1)!='/'){
                    $domain.='/';
                }
                $result = [
                    'code'     => 0,
                    'msg'      => '上传成功',
                    'data' => $domain.$newImageName,
                    'img' => $domain.$newImageName,
                    'filename' => $newImageName
                ];
                if($type){
                    $result['filename']=$domain.$newImageName;
                }
            }else{
                $savename = [];
                foreach($files as $file) {
                    $savename[] = \think\facade\Filesystem::disk('public')->putFile( 'admin_images', $file);
                }
                $img='/storage/'.str_replace('\\','/',$savename[0]);
                $result = [
                    'code'     => 0,
                    'msg'      => '上传成功',
                    'data' => getFullImageUrl($img),
                    'img' => getFullImageUrl($img),
                    'filename' => $img
                ];
                if($type){
                    $result['filename']=getFullImageUrl($img);
                }
            }
        } catch (\think\exception\ValidateException $e) {
            $result = [
                'code' => -1,
                'msg'  => $e->getMessage()
            ];
        }
        return json($result);exit;
    }
    public function uploadtxt(){
        //验证码token end
        $result=[];
        $files = request()->file();
        try {
            validate(['file' => [
                'fileSize' => 3145728,
                'fileExt' => 'jpg,png,gif,jpeg',
                'fileMime' => 'image/jpeg,image/png,image/gif',
            ]])->check(['file' => $files]);
            $savename = [];
            foreach($files as $file) {
                $savename[] = \think\facade\Filesystem::disk('public')->putFile( 'admin_images', $file);
            }
            $img='/storage/'.str_replace('\\','/',$savename[0]);

            $result = [
                'code'     => 0,
                'msg'      => '上传成功',
                'filename' => getFullImageUrl($img)
            ];
        } catch (\think\exception\ValidateException $e) {
            $result = [
                'code' => -1,
                'msg'  => $e->getMessage()
            ];
        }
        return $result;
    }
    //上传表单视频
    public function uploadvideo(){
        //验证码token end
        $result=[];
        $size=config('-appsite.app_videocache')?:1024;
        $sz=10*$size*1024;
        $file = request()->file('file');
        try {
            validate(['file' => [
                'fileSize' => $sz,
                'fileExt' => 'mp4,3gp,avi',
                'fileMime' => 'video/mp4,video/3gpp,video/x-msvideo',
            ]])->check(['file' => $file]);
            //设置了七牛云的请求地址
            if(config('-cloudsite.domain')) {
                // 图片存储在本地的临时路经
                $filePath = $file->getRealPath();
                // 获取图片后缀
                $ext = $file->getOriginalExtension();
                // 上传到七牛后保存的新图片名
                $tim=date('Ymd');
                $newImageName  =   'order/'.$tim.'/'.substr(md5($file->getOriginalName()),0,6)
                    .  rand(00000,99999) . '.'.$ext;
                Qiniu::upload($newImageName,$filePath);
                $domain=config('-cloudsite.domain');
                return json(['savename' => $domain.$newImageName,'img'=>$newImageName, 'code' => 200, 'message' => '上传成功']);
            }else{
                $savename = \think\facade\Filesystem::disk('public')->putFile( 'order', $file);
                if (!$savename) {
                    return json(['', 'code' => 0, 'message' => '上传失败']);
                }
                //返回图片
                $img='/storage/'.str_replace('\\', '/', $savename);
                $savename=getFullImageUrl($img);
                $savename=str_replace('\\', '/', $savename);
                $result = [
                    'code'     => 0,
                    'msg'      => '上传成功',
                    'filename' => $savename
                ];
                return $result;
                return json(['savename' => $savename,'img'=>$img, 'code' => 200, 'message' => '上传成功']);
            }
        } catch (ValidateException $e) {
            return $e->getMessage();
        }
    }
}