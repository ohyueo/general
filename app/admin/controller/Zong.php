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
 * Date: 2021/5/18
 * Time: 10:08
 */

namespace app\admin\controller;
use think\facade\View;
use app\admin\traits\AdminAuth;
use think\facade\Db;

class Zong extends Common
{
    use AdminAuth;

    public function zhimg(){
        $permis = $this->getPermissions('Zong/zhimg');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $page=input("param.page");
            $where=array();
            $title=input("param.title");
            if($title){
                $where[]=['title','like','%'.$title.'%'];
            }
            if(!$page){
                $page=1;
            }
            $limit=input("get.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            $pre = ($page-1)*$limit;//起始页数
            $count = Db::name('general_zonghe_img')->where($where)->count();
            $data=Db::name('general_zonghe_img')->where($where)->order('id Desc')->limit($pre,$limit)->select();
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        return View::fetch();
    }
    public function add(){
        if(request()->isPost()){
            $data = array('status' => 0,'msg' => '未知错误');
            $name=input("param.img");
            if(!$name){ $data['msg']='图片不能为空';return json($data);exit;}
            $cont=input("param.url");
            if(!$cont){ $data['msg']='地址不能为空';return json($data);exit;}
            $type=input("param.type");
            if(!$type){ $data['msg']='分类不能为空';return json($data);exit;}
            $st=input("param.style");
            $title=input("param.title");
            $paiid=input("param.paiid");
            $arr = array(
                'img' => $name,
                'title' => $title,
                'url'	   => $cont,
                'type'=>$type,
                'style' => $st,
                'paiid' => $paiid,
            );
            $addloan=Db::name('general_zonghe_img')->insertGetId($arr);
            if($addloan){
                $data['status']=1;
                $text = '添加了轮播图id='.$addloan;
                $this->writeActionLog($text);
            }else{
                $data['msg']='添加失败';
            }
            return json($data);exit;
        }
        return View::fetch('imgform');
    }
    public function edit(){
        if(request()->isPost()) {
            $data = array('status' => 0, 'msg' => '未知错误');
            $id = input("param.id");
            if (!$id) {
                $data['msg'] = 'id不能为空';
                return json($data);
                exit;
            }
            $name = input("param.img");
            if (!$name) {
                $data['msg'] = '图片不能为空';
                return json($data);
                exit;
            }
            $cont = input("param.url");
            if (!$cont) {
                $data['msg'] = '地址不能为空';
                return json($data);
                exit;
            }
            $type = input("param.type");
            if (!$type) {
                $data['msg'] = '分类不能为空';
                return json($data);
                exit;
            }
            $st = input("param.style");
            $title = input("param.title");
            $paiid = input("param.paiid");
            $arr = array(
                'img' => $name,
                'title' => $title,
                'url' => $cont,
                'type' => $type,
                'style' => $st,
                'paiid' => $paiid,
            );
            $u = Db::name('general_zonghe_img')->where(array('id' => $id))->find();
            if (!$u) {
                $data['msg'] = "该数据不存在";
                return json($data);
                exit;
            }
            $addloan = Db::name('general_zonghe_img')->where(array('id' => $id))->update($arr);
            if ($addloan) {
                $data['status'] = 1;
                $text = '修改了轮播图id=' . $id . '的数据';
                $this->writeActionLog($text);
            } else {
                $data['msg'] = '修改失败';
            }
            return json($data);
            exit;
        }
        return View::fetch('imgform');
    }

    public function del(){
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            //删除数据
            if($arr[$i]){
                Db::name('general_zonghe_img')->where('id',$arr[$i])->delete();
                $text = '删除了轮播图id='.$arr[$i].'的数据';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }
    public function announce(){
        $permis = $this->getPermissions('Zong/announce');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $page=input("param.page");
            $where=array();
            $str=input("param.str");
            $end=input("param.end");
            if($str && $end){
                $str=strtotime($str);
                $end=strtotime($end);
                //$where['addtime'] = array(array('egt',$str),array('elt',$end)) ;
            }
            if(!$page){
                $page=1;
            }
            $limit=input("param.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            $pre = ($page-1)*$limit;//起始页数
            $count = Db::name('general_noti_list')->where($where)->count();
            $data=Db::name('general_noti_list')->where($where)->order('id Desc')->limit($pre,$limit)->select();
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        return View::fetch();
    }
    public function addan(){
        if(request()->isPost()){
            $data = array('status' => 0,'msg' => '未知错误');
            $title=input("param.title");
            if(!$title){ $data['msg']='公告标题不能为空';return json($data);exit;}
            //$cont=input("param.text");
            $cont=input('param.text','','remove_xss');
            if(!$cont){ $data['msg']='公告内容不能为空';return json($data);exit;}
            $arr = array(
                'title' => $title,
                'text'	   => $cont
            );
            $id=input("param.id");
            $arr['addtime']=gettime();
            $addloan=Db::name('general_noti_list')->insertGetId($arr);
            if($addloan){
                $data['status']=1;
                $text = '添加了公告id='.$addloan;
                $this->writeActionLog($text);
            }else{
                $data['msg']='添加失败';
            }
            return json($data);exit;
        }
        return View::fetch('anform');
    }
    public function editan(){
        if(request()->isPost()){
            $data = array('status' => 0,'msg' => '未知错误');
            $title=input("param.title");
            if(!$title){ $data['msg']='公告标题不能为空';return json($data);exit;}
            //$cont=input("param.text");
            $cont=input('param.text','','remove_xss');
            if(!$cont){ $data['msg']='公告内容不能为空';return json($data);exit;}
            $arr = array(
                'title' => $title,
                'text'	   => $cont
            );
            $id=input("param.id");
            if($id){
                $addloan=Db::name('general_noti_list')->where(array('id'=>$id))->update($arr);
                if($addloan){
                    $data['status']=1;
                    $text = '修改了公告id='.$id;
                    $this->writeActionLog($text);
                }else{
                    $data['msg']='添加失败';
                }
            }
            return json($data);exit;
        }
        return View::fetch('anform');
    }
    public function delan(){
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            //删除数据
            if($arr[$i]){
                Db::name('general_noti_list')->where('id',$arr[$i])->delete();
                $text = '删除了公告id='.$arr[$i].'的数据';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }
}