<?php

namespace app\admin\traits;

use think\facade\Db;

trait AdminAuth
{
    protected $permission_id = '';

    protected $actions = ['add' => 'add','dayin'=>'dayin', 'del' => 'del',
        'addperson' => 'addperson','editperson' => 'editperson','delperson' => 'delperson',
        'edit' => 'edit', 'hei' => 'hei', 'info' => 'info','daochu'=>'daochu','jiaohao'=>'jiaohao','guohao'=>'guohao', 'batchdel' => 'batchdel',
        'addrole' => 'addrole' ,'editrole'=>'editrole','delrole'=>'delrole',
        'delclass' => 'delclass','addclass'=>'addclass','editclass'=>'editclass','delsorder'=>'delsorder',
        'shopst'=>'shopst','delpaylog'=>'delpaylog','delmoneylog'=>'delmoneylog','delver'=>'delver','delform'=>'delform',
        'addtime'=>'addtime','edittime'=>'edittime','deltime'=>'deltime','addeseat'=>'addeseat','editeseat'=>'editeseat','deleseat'=>'deleseat',
        'addan'=>'addan','editan'=>'editan','delan'=>'delan','addwen'=>'addwen','editwen'=>'editwen','delwen'=>'delwen',
        'addyuyuespec'=>'addyuyuespec','edityuyuespec'=>'edityuyuespec','delyuyuespec'=>'delyuyuespec',
        'addwenclass'=>'addwenclass','editwenclass'=>'editwenclass','delwenclass'=>'delwenclass',
        'addcodelist'=>'addcodelist','editcodelist'=>'editcodelist','delcodelist'=>'delcodelist',
        'addsignmsg'=>'addsignmsg','editsignmsg'=>'editsignmsg','delsignmsg'=>'delsignmsg',
        'seatlist'=>'seatlist',
        'adddefaultlist'=>'adddefaultlist','editdefaultlist'=>'editdefaultlist','deldefaultlist'=>'deldefaultlist',
    ];

    private $action = ['console','index','announce','employ','sydex','yuyuetime','edit_form', 'zhimg', 'shoplist',
        'orderlist', 'jubao', 'userlist_hei','usermoneylog' ,'wenclass',
        'verification','yuyueseat','yuyuespec',
        'invitalist','invitation','shopclass','shoplist','orderlist',
        'payorder','usermoneylog','tixian',
        'yuyuepersonnel','actionlog','home',
        'tixian',  'userlist', 'rolelist','yuyueclass','yuyuelist','dai_orderlist',
        'codelist','signmsg','defaultlist',
        'remindsite','smssite','wxsite','timesite','mapsite','website','appsite','systemsite'];
    private $immunity=[
        'getprovince','getpvtime','updatexieyi',
        'login','setStatus','setuserInfo','getMoney',
        'delyuyueimg','sethome'
    ];//这里是豁免的
    public function __construct()
    {
        //parent::__construct();
        //判断是否登录
        if(!session('admin_token')){
            echo '<script>parent.location.reload();</script>';exit;
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
                if (!in_array($permis, explode(',', $role['permission_id']))){
                    echo '你无权访问该控制器';exit;
                }
            }
            if(in_array(request()->action(), $this->actions)){
                $cont=request()->controller();
                $permis = Db::name('general_admin_permission')->where('controller','like', '%'.$cont.'%')->where('action', request()->action())->value('id');
                if (!in_array($permis, explode(',', $role['permission_id']))){
                    echo '你无权访问该方法';exit;
                }
            }
        }else if(in_array(request()->action(), $this->immunity)){
            //豁免的方法
        }
        else{
            echo '<div style="font-size: 18px;font-weight: bold;">没有此方法</div>';exit;
        }

//    	if (in_array(request()->action(), $this->action)) {
//	        $username = session('admin_name');
//	        $admin = Db::name('general_admin')->where('username', $username)->field('id, name')->find();
//	        //查看该用户的角色
//	        $role = Db::name('general_admin_role')->alias('r')->join('general_admin_role_permission p', 'r.id = p.role_id', 'LEFT')->join('general_admin_user_role u', 'u.role_id = r.id', 'LEFT')->field('r.name, p.permission_id')->where('u.admin_id', $admin['id'])->find();
//	        $this->permission_id = $role['permission_id'];
//	        if (request()->action() == 'usermo') {
//				$permis = Db::name('general_admin_permission')->where('controller', request()->controller())->where('action', 'usermo/pay_type/'.input('pay_type'))->value('id');
//	        } else {
//	        	$permis = Db::name('general_admin_permission')->where('controller', request()->controller())->where('action', request()->action())->value('id');
//	        }
//	        if (!in_array($permis, explode(',', $role['permission_id']))) {
//	            echo '你无权访问';exit;
//	        }
//    	}
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