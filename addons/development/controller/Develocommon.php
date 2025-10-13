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
 * Date: 2023/2/5
 * Time: 22:29
 */

namespace addons\development\controller;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Session;

class develocommon
{
    protected $permission_id = '';
    protected $actions = ['addcurd'=>'addcurd','editcurd'=>'editcurd','delcurd'=>'delcurd','addtopnav'=>'addtopnav','edittopnav'=>'edittopnav','deltopnav'=>'deltopnav',
        'codetopnav'=>'codetopnav','addimglist'=>'addimglist','editimglist'=>'editimglist','delimglist'=>'delimglist'
    ];
    private $action = ['curdlist','topnav','imglist'];
    private $immunity = ['test'];

    public function __construct()
    {
        //parent::__construct();
        //判断是否登录
        if(!session('admin_token')){
            redirect('/admin/Login/index?234')->send();exit;
        }
        //查询是否有按钮的权限
        if (in_array(request()->action(), $this->action) || in_array(request()->action(), $this->actions)) {
            $username = session('admin_name');
            $admin = Db::name('general_admin')->where('username', $username)->field('id, name')->find();
            //查看该用户的角色
            $role = Db::name('general_admin_role')->alias('r')->join('general_admin_role_permission p', 'r.id = p.role_id', 'LEFT')->join('general_admin_user_role u', 'u.role_id = r.id', 'LEFT')->field('r.name, p.permission_id')->where('u.admin_id', $admin['id'])->find();
            $this->permission_id = $role['permission_id'];
            if(in_array(request()->action(), $this->action)){
                $permis = Db::name('general_admin_permission')->where('controller', request()->controller())->where('action', request()->action())->value('id');
                if (!in_array($permis, explode(',', $role['permission_id']))) {
                    echo '你无权访问该控制器';exit;
                }
            }
            if(in_array(request()->action(), $this->actions)){
                $cont=request()->controller();
                $permis = Db::name('general_admin_permission')->where('controller','like', '%'.$cont.'%')->where('action', request()->action())->value('id');
                if (!in_array($permis, explode(',', $role['permission_id']))) {
                    echo '你无权访问该方法';exit;
                }
            }
        }else if(in_array(request()->action(), $this->immunity)){

        }
        else{
            echo '<div style="font-size: 18px;font-weight: bold;">没有此方法</div>';exit;
        }
    }
    /**
     * 获取权限
     */
    public function getPermissions($controller)
    {
        $permis = Db::name('general_admin_permission')->where('controller', $controller)->where('id', 'in', $this->permission_id)->column('action');

        return implode(',', $permis);
    }
    /**
     * 纪录用户操作
     */
    public function writeActionLog($text)
    {
        $username = session('admin_name');
        $IP=request()->ip();
        Db::name('general_admin_action_log')->insert([
            'user' => $username,
            'text' => $text,
            'addtime' => time(),
            'ip' => $IP
        ]);
    }
}