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
 * Date: 2022/4/21
 * Time: 23:25
 */

namespace app\admin\controller;
use think\facade\View;
use think\facade\Db;
use app\admin\traits\AdminAuth;

class Invitation extends Common
{
    use AdminAuth;

    public function invitation(){
        $permis = $this->getPermissions('Invitation/invitation');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $page=input("param.page");
            if(!$page){
                $page=1;
            }
            $limit=input("param.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            $where=[];
            $id=input("param.id");
            if($id){
                $where[]=['u.id','=',$id];
            }
            $name=input("param.name");
            if($name){
                $where[]=['u.nick','like','%'.$name.'%'];
            }
            $status=input("param.status");
            if($status){
                $where[]=['u.status','=',$status];
            }
            $rel1=input("param.rel1");
            if($rel1){
                $where[]=['u.rel1','=',$rel1];
            }
            $rel2=input("param.rel2");
            if($rel2){
                $where[]=['u.rel2','=',$rel2];
            }
            $pre = ($page-1)*$limit;//起始页数
            //查询产品
            $count = Db::name('general_user_list')->alias('u')
                ->join('general_user_info i', 'i.user_id = u.id', 'LEFT')
                ->where('i.isyao',1)
                ->where($where)->count();

            $data=Db::name('general_user_list')->alias('u')
                ->join('general_user_login l', 'u.id = l.uid', 'LEFT')
                ->join('general_user_info i', 'i.user_id = u.id', 'LEFT')
                ->where($where)
                ->where('i.isyao',1)
                ->limit($pre,$limit)
                ->field('u.id,u.nick,u.headimg,u.status,u.money,u.rel1,u.rel2,l.reg_time,l.rece_login_time,l.mobile,i.rel1_money,i.rel2_money,i.lower1_total_money,i.lower2_total_money')
                ->order('id desc')
                ->select()->all();
            if($data){
                for($i=0;$i<count($data);$i++){
                    $img=$data[$i]['headimg'];
                    if(!$img){
                        $data[$i]['headimg']=getFullImageUrl('/storage/imges/mo.png');
                    }
                    $id=$data[$i]['id'];
                    $isyao=Db::name('general_user_info')->where('user_id',$id)->value("isyao");
                    $data[$i]['isyao']=$isyao;
                    //查询徒弟
                    $data[$i]['tudi']=Db::name('general_user_list')->where('rel1',$id)->count();
                    $data[$i]['tusun']=Db::name('general_user_list')->where('rel2',$id)->count();
                }
            }
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        return View::fetch();
    }
    public function invitalist(){
        $permis = $this->getPermissions('Invitation/invitalist');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $page=input("param.page");
            if(!$page){
                $page=1;
            }
            $where=[];
            $name=input("param.name");
            if($name){
                $where[]=['l.title','like','%'.$name.'%'];
            }
            $uid=input('param.uid');
            if($uid){
                $where[]=['e.uid','=',$uid];
            }
            $status=input('param.status');
            if($status){
                $where[]=['e.status','=',$status];
            }
            $limit=input("param.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            //查询产品
            $count = Db::name('general_invitamsg')->alias('e')
                ->join('general_user_list u', 'e.uid = u.id', 'LEFT')
                ->where($where)
                ->count();
            $data=Db::name('general_invitamsg')->alias('e')
                ->join('general_user_list u', 'e.uid = u.id', 'LEFT')
                ->where($where)
                ->page($page,$limit)
                ->field('e.*,u.nick,u.headimg')
                ->order('e.id desc')
                ->select()->toArray();
            if($data){
                for($i=0;$i<count($data);$i++){
                    $img=$data[$i]['headimg'];
                    if(!$img){
                        $data[$i]['headimg']=getFullImageUrl('/storage/imges/mo.png');
                    }
                }
            }
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        return View::fetch();
    }

    //删除推荐人申请
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
                Db::name('general_invitamsg')->where('id',$arr[$i])->delete();
                $text = '删除了推荐人申请id='.$arr[$i].'的数据';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }
    public function add(){
        $data = array('status' => 0,'msg' => '未知错误');
        $id=input("param.id");
        $st=input("param.st");
        $msg=input('param.msg');
        if(!$id || !$st){
            $data['msg']='参数错误';return json($data);exit;
        }
        if($st==2){
            $arr=['error'=>$msg,'status'=>2];
            $text = '审核拒绝了推荐人='.$id.'的数据';
            $this->writeActionLog($text);
        }else if($st==1){
            //修改userinfo
            $uid=Db::name('general_invitamsg')->where('id',$id)->value('uid');
            $res=Db::name('general_user_info')->where('user_id',$uid)->find();
            if(!$res){
                //添加
                Db::name('general_user_info')->insert(['user_id'=>$uid,'isyao'=>1]);
            }else{
                //修改
                Db::name('general_user_info')->where('user_id',$uid)->update(['isyao'=>1]);
            }
            $arr=['status'=>1];
            $text = '审核通过了推荐人='.$id.'的数据';
            $this->writeActionLog($text);
        }
        $res=Db::name('general_invitamsg')->where('id',$id)->update($arr);
        if($res){
            $data['status']=1;
            $data['msg']='操作成功';return json($data);exit;
        }else{
            $data['status']=0;
            $data['msg']='操作失败';return json($data);exit;
        }
    }
    public function edit(){
        $data = array('status' => 0,'msg' => '未知错误');
        $id=input("param.id");
        $st=input("param.st");
        $msg=input('param.msg');
        if(!$id || !$st){
            $data['msg']='参数错误';return json($data);exit;
        }
        if($st==2){
            $arr=['error'=>$msg,'status'=>2];
            $text = '审核拒绝了推荐人='.$id.'的数据';
            $this->writeActionLog($text);
        }else if($st==1){
            //修改userinfo
            $uid=Db::name('general_invitamsg')->where('id',$id)->value('uid');
            $res=Db::name('general_user_info')->where('user_id',$uid)->find();
            if(!$res){
                //添加
                Db::name('general_user_info')->insert(['user_id'=>$uid,'isyao'=>1]);
            }else{
                //修改
                Db::name('general_user_info')->where('user_id',$uid)->update(['isyao'=>1]);
            }
            $arr=['status'=>1];
            $text = '审核通过了推荐人='.$id.'的数据';
            $this->writeActionLog($text);
        }
        $res=Db::name('general_invitamsg')->where('id',$id)->update($arr);
        if($res){
            $data['status']=1;
            $data['msg']='操作成功';return json($data);exit;
        }else{
            $data['status']=0;
            $data['msg']='操作失败';return json($data);exit;
        }
    }

}