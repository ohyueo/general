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
 * Date: 2021/9/16
 * Time: 16:57
 */

namespace app\admin\controller;
use think\facade\View;
use think\facade\Db;
use app\admin\traits\AdminAuth;
use app\models\GeneralYuyueYuyuespec;

class Employ extends Common
{
    use AdminAuth;
    public function employ(){
        $permis = $this->getPermissions('Employ/employ');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $page=input("param.page");
            if(!$page){
                $page=1;
            }
            $where=[];
            $id=input('param.id');
            if($id){
                $where[]=['id','=',$id];
            }
            $uid=input('param.uid');
            if($uid){
                $where[]=['uid','=',$uid];
            }
            $list_id=input('param.status');
            if($list_id){
                $where[]=['status','=',$list_id];
            }
            $limit=input("param.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            $order="id desc";
            //查询产品
            $count = Db::name('general_yuyue_employees')->where($where)->count();
            $data=Db::name('general_yuyue_employees')
                ->where($where)
                ->page($page,$limit)
                ->order($order)
                ->select()->toArray();
            if($data){
                for($i=0;$i<count($data);$i++){
                    $uid=$data[$i]['uid'];
                    $data[$i]['nick']=Db::name('general_user_list')->where('id',$uid)->value('nick')?:'默认用户'.$uid;
                    $headimg=Db::name('general_user_list')->where('id',$uid)->value('headimg');
                    if(!$headimg){
                        $headimg=getFullImageUrl('/storage/imges/mo.png');//
                    }
                    $data[$i]['headimg']=$headimg;
                }
            }
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        return View::fetch();
    }
    public function verification(){
        $permis = $this->getPermissions('Employ/verification');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $page=input("param.page");
            if(!$page){
                $page=1;
            }
            $where=[];
            $id=input('param.id');
            if($id){
                $where[]=['id','=',$id];
            }
            $uid=input('param.uid');
            if($uid){
                $where[]=['uid','=',$uid];
            }
            $list_id=input('param.status');
            if($list_id){
                $where[]=['status','=',$list_id];
            }
            $limit=input("param.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            $order="id desc";
            //查询产品
            $count = Db::name('general_yuyue_verification')->where($where)->count();
            $data=Db::name('general_yuyue_verification')
                ->where($where)
                ->page($page,$limit)
                ->order($order)
                ->select()->toArray();
            if($data){
                for($i=0;$i<count($data);$i++){
                    $uid=$data[$i]['uid'];
                    $data[$i]['nick']=Db::name('general_user_list')->where('id',$uid)->value('nick')?:'默认用户'.$uid;
                    $headimg=Db::name('general_user_list')->where('id',$uid)->value('headimg');
                    if(!$headimg){
                        $headimg=getFullImageUrl('/storage/imges/mo.png');//
                    }
                    $data[$i]['headimg']=$headimg;
                    $list_id=$data[$i]['list_id'];
                    $title=Db::name('general_yuyue_list')->where('id',$list_id)->value('title');
                    $data[$i]['title']=$title;
                }
            }
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        return View::fetch();
    }

    public function delver(){
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            //删除数据
            if($arr[$i]){
                Db::name('general_yuyue_verification')->where('id',$arr[$i])->delete();
                $text = '删除了核销记录='.$arr[$i].'的数据';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }
    public function edit(){
        if(request()->isPost()){
            $id=input("param.id");
            $data = array('status' => 0,'msg' => '未知错误');
            $nick=input("param.name");
            if(!$nick){ $data['msg']='姓名不能为空';return json($data);exit;}
            $pwd=input("param.center");
            if(!$pwd){ $data['msg']='备注不能为空';return json($data);exit;}
            $img=input("param.uid");
            if(!$img){ $data['msg']='用户不能为空';return json($data);exit;}
            $status=input("param.status");
            $listval=input('param.listval');
            if($listval){
                $perarr = implode(',', $listval);
            }
            $spec=input('param.spec');
            if($spec){
                $spec = implode(',', $spec);
            }
            $heno=input('param.heno');
            $arr = array(
                'name' => $nick,
                'center' => $pwd,
                'uid' => $img,
                'status' => $status,
                'listval' => $perarr,
                'spec' => $spec,
                'heno' => $heno,
                'role' => 1
            );
            if($id){
                $data['status']=1;
                Db::name('general_yuyue_employees')->where('id',$id)->update($arr);
                $text = '修改了核销员'.$nick.'id='.$id;
                $this->writeActionLog($text);
            }
            return json($data);exit;
        }
        $id=input("param.id");
        $role='';
        $listval='';
        $spec='';
        if($id){
            $role=Db::name('general_yuyue_employees')->where('id',$id)->value('role');
            $listval=Db::name('general_yuyue_employees')->where('id',$id)->value('listval');
            $spec=Db::name('general_yuyue_employees')->where('id',$id)->value('spec');
        }
        View::assign('role', $role);
        View::assign('listval', $listval);
        View::assign('spec', $spec);
        $venueslist=Db::name('general_yuyue_list')->select()->toArray();
        View::assign('venueslist',$venueslist);
        //查询是否有规格
        $isspec=GeneralYuyueYuyuespec::count()?1:0;
        View::assign('isspec',$isspec);
        //查询规格列表
        $speclist=GeneralYuyueYuyuespec::select()->toArray();
        View::assign('speclist',$speclist);
        return View::fetch();
    }
    public function add(){
        if(request()->isPost()){
            $id=input("param.id");
            $data = array('status' => 0,'msg' => '未知错误');
            $nick=input("param.name");
            if(!$nick){ $data['msg']='姓名不能为空';return json($data);exit;}
            $pwd=input("param.center");
            if(!$pwd){ $data['msg']='备注不能为空';return json($data);exit;}
            $img=input("param.uid");
            if(!$img){ $data['msg']='用户不能为空';return json($data);exit;}
            $status=input("param.status");
            $ux=Db::name('general_yuyue_employees')->where('uid',$img)->find();
            if($ux){
                $data['msg']='该用户请不要重复绑定';return json($data);
            }
            $listval=input('param.listval');
            if($listval){
                $perarr = implode(',', $listval);
            }
            $spec=input('param.spec');
            if($spec){
                $spec = implode(',', $spec);
            }
            $heno=input('param.heno');
            $arr = array(
                'name' => $nick,
                'center' => $pwd,
                'uid' => $img,
                'status' => $status,
                'listval' => $perarr,
                'spec' => $spec,
                'heno' => $heno,
                'role' => 1
            );
            $data['status']=1;
            $arr['addtime']=gettime();
            $addloan=Db::name('general_yuyue_employees')->insertGetId($arr);
            $text = '添加了核销员'.$nick.'id='.$addloan;
            $this->writeActionLog($text);
            return json($data);exit;
        }
        $role='';
        $listval='';
        $spec='';
        View::assign('role', $role);
        View::assign('listval', $listval);
        View::assign('spec', $spec);
        $venueslist=Db::name('general_yuyue_list')->select()->toArray();
        View::assign('venueslist',$venueslist);
        //查询是否有规格
        $isspec=GeneralYuyueYuyuespec::count()?1:0;
        View::assign('isspec',$isspec);
        //查询规格列表
        $speclist=GeneralYuyueYuyuespec::select()->toArray();
        View::assign('speclist',$speclist);
        return View::fetch('edit');
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
                Db::name('general_yuyue_employees')->where('id',$arr[$i])->delete();
                $text = '删除了核销员id='.$arr[$i].'的数据';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }
}