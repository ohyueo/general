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
 * Time: 14:17
 */

namespace app\admin\controller;
use think\facade\View;
use think\facade\Db;
use app\admin\traits\AdminAuth;
use app\handler\MoneyLogHandler;
use app\models\GeneralUserList;
use app\models\GeneralUserLogin;
use think\facade\Session;

class User extends Common
{
    use AdminAuth;
    public function login(){
        Session::delete('admin_name','think');
        Session::delete('admin_token','think');

        Session::clear();
        return redirect('/admin/Login/index');exit;
        echo '<script>parent.location.reload();</script>';exit;
    }
    public function userlist(){
        $rel1=input("param.rel1");
        View::assign('rel1', $rel1);
        $rel2=input("param.rel2");
        View::assign('rel2', $rel2);
        $permis = $this->getPermissions('User/userlist');
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
            $count = Db::name('general_user_list')->alias('u')->where($where)->count();
            $data=Db::name('general_user_list')->alias('u')
                ->join('general_user_login l', 'u.id = l.uid', 'LEFT')
                ->where($where)
                ->limit($pre,$limit)
                ->field('u.id,u.nick,u.headimg,u.status,u.money,u.rel1,u.rel2,l.reg_time,l.rece_login_time,l.mobile,l.email,l.reg_city')
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
    public function userlist_hei(){
        $permis = $this->getPermissions('User/userlist_hei');
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
            $count = Db::name('general_user_list')->alias('u')->where($where)->count();
            $data=Db::name('general_user_list')->alias('u')
                ->join('general_user_login l', 'u.id = l.uid', 'LEFT')
                ->where($where)
                ->limit($pre,$limit)
                ->field('u.id,u.nick,u.headimg,u.status,u.money,u.rel1,u.rel2,l.reg_time,l.rece_login_time,l.mobile,l.email,l.reg_city')
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
    //加入黑名单解除黑名单
    public function setStatus(){
        $st=input("param.st");
        $id=input("param.id");
        if(!$id){ $data['msg']='用户不能为空';return json($data);exit;}
        if($st==1){
            $user = GeneralUserList::find($id);
            $user->status= $st;
            $user->save();
            $msg='解除成功';
        }else if($st==2){
            $user = GeneralUserList::find($id);
            $user->status= $st;
            $user->save();
            $msg='加入成功';
        }
        $data = array('status' => 1,'msg' => $msg);
        return json($data);exit;
    }
    //用户资金记录明细
    public function getmoneyData(){
        $page=input("get.page");
        if(!$page){
            $page=1;
        }
        $where=[];
        $uid=input("get.uid");
        if($uid){
            $where[]=['user_id','=',$uid];
        }
        $limit=input("get.limit");
        if(!$limit){
            $limit=10;//每页显示条数
        }
        //查询产品
        $count = Db::name('user_moneylog')->where($where)->count();
        $data=Db::name('user_moneylog')
            ->where($where)
            ->page($page,$limit)
            ->order('id desc')
            ->select();
        $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
        return json($res);
    }
    //修改用户昵称
    public function setuserInfo(){
        $st=input("param.st");
        $id=input("param.id");
        if(!$id){ $data['msg']='用户不能为空';return json($data);exit;}

        if($st==1){//nick
            $user = GeneralUserList::find($id);
            $nick=input("param.value");
            if(!$nick){ $data['msg']='用户昵称不能为空';return json($data);exit;}
            $user->nick= $nick;
            $user->save();
            $text = '修改了用户id='.$id.'的昵称'.$nick;
            $this->writeActionLog($text);
        }else if($st==2){//mobile
            $user = GeneralUserLogin::where('uid',$id)->find();
            $mobile=input("param.value");
            if(!$mobile){ $data['msg']='手机号不能为空';return json($data);exit;}
            $user->mobile= $mobile;
            $user->save();
            $text = '修改了用户id='.$id.'的手机号'.$mobile;
            $this->writeActionLog($text);
        }else if($st==3){//email
            $user = GeneralUserLogin::where('uid',$id)->find();
            $email=input("param.value");
            if(!$email){ $data['msg']='邮箱不能为空';return json($data);exit;}
            $user->email= $email;
            $user->save();
            $text = '修改了用户id='.$id.'的邮箱'.$email;
            $this->writeActionLog($text);
        }else if($st==4){//email
            $user = GeneralUserList::find($id);
            $name=input("param.value");
            if(!$name){ $data['msg']='用户姓名不能为空';return json($data);exit;}
            $user->name= $name;
            $user->save();
            $text = '修改了用户id='.$id.'的姓名'.$name;
            $this->writeActionLog($text);
        }
        $data = array('status' => 1,'msg' => '修改成功');
        return json($data);exit;
    }
    public function zjmoney(){
        $st=input("param.st");
        $id=input("param.user");
        if(!$id){ $data['msg']='用户不能为空';return json($data);exit;}
        $money=input("param.money");
        $sm=input("param.sm");
        if(!$sm){ $data['msg']='操作说明不能为空';return json($data);exit;}
        $user = GeneralUserList::find($id);
        if($st==1){//增加
            //var_dump($id);exit;
            // 加钱
            $user->money += $money;
            $user->price += $money;
            $user->save();
            $text = '增加了用户id='.$id.'余额'.$money;
            $this->writeActionLog($text);
        }else if($st==2){//减少
            // 加钱
            $user->money -= $money;
            $user->price -= $money;
            $user->save();
            $text = '减少了用户id='.$id.'余额'.$money;
            $this->writeActionLog($text);
        }
        // 资金记录
        MoneyLogHandler::add($user,$money,$st,2,$sm);
        $data = array('status' => 1,'msg' => '修改成功');
        return json($data);exit;
    }

    public function info(){
        $id=input("get.id");
        $user=Db::name('general_user_list')->alias('u')
            ->join('general_user_login l', 'u.id = l.uid', 'LEFT')
            ->where('u.id',$id)
            ->field('u.id,u.nick,u.headimg,u.status,u.money,l.reg_time,l.rece_login_time,l.mobile,l.email')
            ->find();
        if($user){
            if(!$user['headimg']){
                $user['headimg']=getFullImageUrl('/storage/imges/mo.png');//
            }
            if(!$user['nick']){
                $user['nick']='无';
            }
            if(!$user['mobile']){
                $user['mobile']='无';
            }
            if(!$user['email']){
                $user['email']='无';
            }
        }
        View::assign('user', $user);
        return View::fetch('userinfo');
    }
    public function add(){
        $this->getPermissions('User/add');
        if(request()->isPost()){
            $data = array('status' => 0,'msg' => '未知错误');
            $nick=input("param.nick");
            //if(!$nick){ $data['msg']='用户昵称不能为空';return json($data);exit;}
            $pwd=input("param.password");
            //if(!$pwd){ $data['msg']='用户密码不能为空';return json($data);exit;}
            $img=input("param.headimg");
            //if(!$img){ $data['msg']='用户头像不能为空';return json($data);exit;}
            $mobile=input("param.mobile");
            $email=input("param.email");
            $status=input("param.status");
            $rel1=input('param.rel1');
            $rel2=input('param.rel2');
            $isyao=input('param.isyao');
            $arr = array(
                'nick' => $nick,
                'headimg' => $img,
                'rel1' => $rel1,
                'rel2' => $rel2,
                'status' => $status
            );
            $addloan=Db::name('general_user_list')->insertGetId($arr);
            if($addloan){
                $data['status']=1;
                    $t=date('Y-m-d H:i:s');
                    //添加联系人
                    Db::name('general_user_info')->insert(['user_id'=>$addloan,'isyao'=>$isyao]);
                    //登录表
                    $logarr=array(
                        'uid' => $addloan,
                        'password' => password_hash($pwd, PASSWORD_DEFAULT),
                        'reg_time' => $t,
                        'mobile' => $mobile,
                        'email' => $email
                    );
                    Db::name('general_user_login')->insert($logarr);

                $text = '添加了用户id='.$addloan;
                $this->writeActionLog($text);
            }else{
                $data['msg']='添加失败';
            }
            return json($data);exit;
        }
        return View::fetch('edit');
    }
    public function edit(){
        $this->getPermissions('User/edit');
        if(request()->isPost()){
            $data = array('status' => 0,'msg' => '未知错误');
            $id=input("param.id");
            //if(!$id){ $data['msg']='修改用户不能为空';return json($data);exit;}
            $nick=input("param.nick");
            //if(!$nick){ $data['msg']='用户昵称不能为空';return json($data);exit;}
            $pwd=input("param.password");
            $img=input("param.headimg");
            //if(!$img){ $data['msg']='用户头像不能为空';return json($data);exit;}
            $status=input("param.status");
            $mobile=input("param.mobile");
            $email=input("param.email");
            //查询用户是否存在
            $u=Db::name('general_user_list')->where('id',$id)->count();
            if(!$u){
                $data['msg']='用户不存在';return json($data);exit;
            }

            $rel1=input('param.rel1');
            $rel2=input('param.rel2');
            $isyao=input('param.isyao');
            $res=Db::name('general_user_info')->where('user_id',$id)->find();
            if(!$res){
                //添加联系人
                Db::name('general_user_info')->insert(['user_id'=>$id,'isyao'=>$isyao]);
            }else{
                //修改联系人
                Db::name('general_user_info')->where('user_id',$id)->update(['isyao'=>$isyao]);
            }
            $xg=0;
            if($pwd){
                //登录表
                $logarr=array(
                    'password' => password_hash($pwd, PASSWORD_DEFAULT)
                );
                Db::name('general_user_login')->where('uid',$id)->update($logarr);
                $text = '修改了用户id='.$id.'的密码';
                $this->writeActionLog($text);
            }
            //登录表
            $logarr=array(
                'mobile' => $mobile,
                'email' => $email
            );
            Db::name('general_user_login')->where('uid',$id)->update($logarr);
            $text = '修改了用户id='.$id.'的登录信息';
            $this->writeActionLog($text);
            //修改资料
            $xrr=array(
                'nick'=>$nick,
                'headimg' => $img,
                'status' => $status,
                'rel1' => $rel1,
                'rel2' => $rel2
            );
            $addloan=Db::name('general_user_list')->where('id',$id)->update($xrr);
            if($addloan || $xg){
                $data['status']=1;

                $text = '修改了用户资料id='.$id;
                $this->writeActionLog($text);
            }
            $data['status']=1;
            return json($data);exit;
        }
        return View::fetch();
    }
    public function del(){
        $this->getPermissions('User/del');
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            //删除数据
            if($arr[$i]){
                Db::name('general_user_list')->where('id',$arr[$i])->delete();
                $text = '删除了用户id='.$arr[$i].'的数据';
                $this->writeActionLog($text);
                Db::name('general_user_info')->where('user_id',$arr[$i])->delete();
                Db::name('general_user_login')->where('uid',$arr[$i])->delete();
                $text = '删除了用户登录数据id='.$arr[$i].'的数据';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }
}