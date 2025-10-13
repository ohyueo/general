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
use app\admin\traits\AdminAuth;
use think\facade\Db;

class Admin extends Common
{
    use AdminAuth;

    public function actionlog(){
        $permis = $this->getPermissions('Admin/actionlog');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $page=input("param.page");
            $where = [];
            if(!$page){
                $page=1;
            }
            $limit=input("param.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            $name = input('param.text');
            if ($name) {
                $where[] = ['text','like', '%'.$name.'%'];
            }
            $pre = ($page-1)*$limit;//起始页数
            $str=input("param.str");
            $end=input("param.end");
            if($str && $end){
                $str=strtotime($str);
                $end=strtotime($end);
                $count = Db::name('general_admin_action_log')->whereTime('addtime', 'between', [$str, $end])->count();
                $data = Db::name('general_admin_action_log')->where($where)->whereTime('addtime', 'between', [$str, $end])->limit($pre,$limit)->order('id desc')->select();
            }else{
                $count = Db::name('general_admin_action_log')->count();
                $data = Db::name('general_admin_action_log')->where($where)->limit($pre,$limit)->order('id desc')->select();
            }
            $res = ['code' => 0, 'message' => '请求成功', 'data' => $data, 'count' => $count];
            return json($res);
        }
        return View::fetch();
    }

    public function userlist()
    {
        $permis = $this->getPermissions('Admin/userlist');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        $roles = Db::name('general_admin_role')->field('id, name')->select();
        View::assign('roles', $roles);
        if(request()->isPost()){
            $where = array();
            $username = input('param.username');
            if ($username) {
                $where[] = ['a.username','like', '%'.$username.'%'];//array('like', '%'.$username.'%');
            }
            $name = input('param.name');
            if ($name) {
                $where[] = ['a.name','like', '%'.$name.'%'];//array('like', '%'.$name.'%');
            }
            $role_id = input('role_id');
            if ($role_id) {
                $where[] = ['r.id','=',$role_id];
            }
            $page=input("param.page");
            if(!$page){
                $page=1;
            }
            $limit=input("param.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            $pre = ($page-1)*$limit;//起始页数
            $count = Db::name('general_admin')->count();
            $data = Db::name('general_admin')->alias('a')->join('general_admin_user_role ur', 'a.id = ur.admin_id', 'LEFT')
                ->join('general_admin_role r', 'ur.role_id = r.id', 'LEFT')
                ->where($where)->limit($pre,$limit)
                ->field('a.id, a.username, a.name, a.status, a.addtime, a.lastlogin, a.text, r.name role,ur.role_id')->select();
            $res = ['code' => 0, 'message' => '请求成功', 'data' => $data, 'count' => $count];
            return json($res);
        }
        return View::fetch();
    }

    public function add()
    {
        $this->getPermissions('Admin/add');
        if (request()->isGet()) {
            $roles = Db::name('general_admin_role')->where('id', '<>', 1)->field('id, name')->select();
            View::assign('roles', $roles);
            return View::fetch();
        }else{
            $data = [
                'username' => input('username'),
                'name' => input('name'),
                'password' => md5('*&>'.md5(input('password'))),
                'status' => 1,
                'lastlogin' => 0,
                'addtime' => time()
            ];
            $result = Db::name('general_admin')->insertGetId($data);
            if ($result) {
                Db::name('general_admin_user_role')->insert([
                    'admin_id' => $result,
                    'role_id' => input('role_id')
                ]);
                $text = '新增了后台人员 '.input('username');
                $this->writeActionLog($text);
                $res = ['status' => 1, 'msg' => '添加成功'];
            } else {
                $res = ['status' => 0, 'msg' => '添加失败'];
            }
            return json($res);
        }
    }

    public function edit()
    {
        $this->getPermissions('Admin/edit');
        if (request()->isGet()) {
            $id = input('id');
            $admin = Db::name('general_admin')->alias('a')->join('general_admin_user_role r', 'r.admin_id = a.Id', 'LEFT')->field('a.username, a.id, a.name, r.role_id')->where('a.Id', $id)->find();
            $roles = Db::name('general_admin_role')->where('id', '<>', 1)->select();
            View::assign('admin', $admin);
            View::assign('roles', $roles);
            return View::fetch();
        } else {
            $has = Db::name('general_admin')->where('username', input('username'))->where('id', '<>', input('id'))->find();
            if ($has) {
                $res = ['status' => 2, 'msg' => '该账号已存在'];
                return json($res);
            }
            $data = [
                'username' => input('username'),
                'name' => input('name')
            ];
            if(input('password')){
                $data['password'] = md5('*&>'.md5(input('password')));
            }
            $result = Db::name('general_admin')->where('id', input('id'))->update($data);
            if ($result !== false) {
                Db::name('general_admin_user_role')->where('admin_id', input('id'))->update(['role_id'=>input('role_id')]);
                $text = '修改了后台人员ID为 '.input('id').'的资料';
                $this->writeActionLog($text);
                $res = ['status' => 1, 'msg' => '修改成功'];
            } else {
                $res = ['status' => 0, 'msg' => '修改失败'];
            }
            return json($res);
        }
    }

    public function deluser()
    {
        $this->getPermissions('Admin/rolelist');
        $id = input('id');
        if (!$id) {
            $res = ['status' => 0, 'message' => '删除失败'];
            return json($res);
        }
        $id = explode(',', $id);
        $id = array_filter($id);
        $names = Db::name('general_admin')->where('id', 'in', $id)->column('username');
        $result = Db::name('general_admin')->delete($id);
        if ($result) {
            Db::name('general_admin_user_role')->where('admin_id', 'in', $id)->delete();
            $text = '删除了后台人员 '.implode(',', $names);
            $this->writeActionLog($text);
            $res = ['status' => 1, 'message' => '删除成功'];
        } else {
            $res = ['status' => 0, 'message' => '删除失败'];
        }

        return json($res);
    }

    public function rolelist()
    {
        $permis = $this->getPermissions('Admin/rolelist');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $page = input('page', 1);
            $where=[];
            $name = input('param.name');
            if($name){
                $where[]=['name','like','%'.$name.'%'];
            }
            $count = Db::name('general_admin_role')->where($where)->count();
            $data = Db::name('general_admin_role')->where($where)->page($page, 10)->select();
            $res = ['code' => 0, 'message' => '请求成功', 'data' => $data, 'count' => $count];
            return json($res);
        }
        return View::fetch();
    }

    public function addrole()
    {
        $this->getPermissions('Admin/addrole');
        if (request()->isGet()) {
            $permis = Db::name('general_admin_permission')->select();
            $permis = tree($permis);
            View::assign('permis', $permis);
            return View::fetch();
        } else {
            $data = input('post.');
            //添加角色
            $role_id = Db::name('general_admin_role')->insertGetId([
                'name' => $data['name'],
                'text' => $data['text']
            ]);
            $permis = implode(',', $data['limits']);
            if ($role_id) {
                $result = Db::name('general_admin_role_permission')->insert([
                    'role_id' => $role_id,
                    'permission_id' => $permis
                ]);
                if ($result) {
                    $text = '新增了角色 '.input('name');
                    $this->writeActionLog($text);
                    $res = ['status' => 1, 'message' => '添加成功'];
                } else {
                    $res = ['status' => 2, 'message' => '添加失败'];
                }
            } else {
                $res = ['status' => 2, 'message' => '添加失败'];
            }
            return json($res);
        }
    }

    public function delrole()
    {
        $this->getPermissions('Admin/delrole');
        $id = input('param.id');
        if (!$id) {
            $res = ['status' => 0, 'message' => '参数错误'];
            return json($res);
        }
        $id = explode(',', $id);
        $id = array_filter($id);
        if($id){
            for($i=0;$i<count($id);$i++){
                $rid=$id[$i];
                $name=Db::name('general_admin_role')->whereIn('id',$rid)->value('name');
                $result = Db::name('general_admin_role')->delete($id);
                if ($result) {
                    Db::name('general_admin_role_permission')->where('role_id', 'in', $rid)->delete();
                    $text = '删除了角色 '.$name;
                    $this->writeActionLog($text);
                }
            }
        }
        $res = ['status' => 1, 'message' => '删除成功'];
        return json($res);
    }

    public function editrole()
    {
        $this->getPermissions('Admin/editrole');
        if (request()->isGet()) {
            $id = input('id');
            $role = Db::name('general_admin_role')->alias('r')->join('general_admin_role_permission p', 'p.role_id = r.id', 'LEFT')->where('r.id', $id)->field('r.id, r.name, r.text, p.permission_id')->find();
            $permis = Db::name('general_admin_permission')->order('p_id asc,id asc')->select();
            $permis = tree($permis);
            View::assign('role', $role);
            View::assign('permis', $permis);
            return View::fetch();
        } else {
            $data = input('post.');
            $result = Db::name('general_admin_role')->update([
                'id' => $data['id'],
                'name' => $data['name'],
                'text' => $data['text']
            ]);
            if ($result !== false) {
                $permis = implode(',', $data['limits']);
                $result = Db::name('general_admin_role_permission')->where('role_id', $data['id'])->update(['permission_id'=>$permis]);
                $text = '修改了角色资料 '.$data['name'];
                $this->writeActionLog($text);
                $res = ['status' => 1, 'message' => '修改成功'];
            } else {
                $res = ['status' => 2, 'message' => '修改失败'];
            }
            return json($res);
        }
    }
}