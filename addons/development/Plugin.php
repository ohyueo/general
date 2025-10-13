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
 * Time: 13:21
 */

namespace addons\development;	// 注意命名空间规范

use think\Addons;
use think\facade\Db;

/**
 * 插件测试
 * @author byron sampson
 */
class Plugin extends Addons	// 需继承think\Addons类
{
    // 该插件的基础信息
    public $info = [
        'name' => 'autocurd',	// 插件标识
        'title' => '一键生成curd',	// 插件名称
        'description' => '一键生成curd',	// 插件简介
        'status' => 1,	// 状态
        'author' => 'ohyueo',
        'version' => '1.0'
    ];

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {

        //(47,'商品管理','Shop',NULL,0,1,'layui-icon-cart',3),
        $arr=array(
            'name' => '开发工具',
            'controller' => '',
            'action' => '',
            'parent_id' => 0,
            'is_nav' => 1,
            'icon' => 'layui-icon-util',
            'p_id' => 3,
            'type' => 2,
            'addons' => 'development'
        );
        $pidx=Db::name('general_admin_permission')->insertGetId($arr);
        //插入权限
        $arr=array(
            'name' => '顶级栏目生成',
            'controller' => 'Autocurd',
            'action' => 'topnav',
            'parent_id' => $pidx,
            'is_nav' => 1,
            'icon' => '',
            'p_id' => 0,
            'type' => 2,
            'addons' => 'development'
        );
        $pid=Db::name('general_admin_permission')->insertGetId($arr);
        //添加
        $arr=array(
            'name' => '添加',
            'controller' => 'Autocurd/topnav',
            'action' => 'addtopnav',
            'parent_id' => $pid,
            'is_nav' => 0,
            'icon' => '',
            'p_id' => 0,
            'type' => 2,
            'addons' => 'develo'
        );
        Db::name('general_admin_permission')->insert($arr);
        //修改
        $arr=array(
            'name' => '修改',
            'controller' => 'Autocurd/topnav',
            'action' => 'edittopnav',
            'parent_id' => $pid,
            'is_nav' => 0,
            'icon' => '',
            'p_id' => 0,
            'type' => 2,
            'addons' => 'development'
        );
        Db::name('general_admin_permission')->insert($arr);
        //生成代码
        $arr=array(
            'name' => '修改',
            'controller' => 'Autocurd/topnav',
            'action' => 'codetopnav',
            'parent_id' => $pid,
            'is_nav' => 0,
            'icon' => '',
            'p_id' => 0,
            'type' => 2,
            'addons' => 'development'
        );
        Db::name('general_admin_permission')->insert($arr);
        //删除
        $arr=array(
            'name' => '删除',
            'controller' => 'Autocurd/topnav',
            'action' => 'deltopnav',
            'parent_id' => $pid,
            'is_nav' => 0,
            'icon' => '',
            'p_id' => 0,
            'type' => 2,
            'addons' => 'development'
        );
        Db::name('general_admin_permission')->insert($arr);
        //插入权限
        $arr=array(
            'name' => 'CURD生成列表',
            'controller' => 'Autocurd',
            'action' => 'curdlist',
            'parent_id' => $pidx,
            'is_nav' => 1,
            'icon' => '',
            'p_id' => 0,
            'type' => 2,
            'addons' => 'development'
        );
        $pid=Db::name('general_admin_permission')->insertGetId($arr);
        //添加
        $arr=array(
            'name' => '添加CURD',
            'controller' => 'Autocurd/curdlist',
            'action' => 'addcurd',
            'parent_id' => $pid,
            'is_nav' => 0,
            'icon' => '',
            'p_id' => 0,
            'type' => 2,
            'addons' => 'develo'
        );
        Db::name('general_admin_permission')->insert($arr);
        //修改
        $arr=array(
            'name' => '修改CURD',
            'controller' => 'Autocurd/curdlist',
            'action' => 'editcurd',
            'parent_id' => $pid,
            'is_nav' => 0,
            'icon' => '',
            'p_id' => 0,
            'type' => 2,
            'addons' => 'development'
        );
        Db::name('general_admin_permission')->insert($arr);
        //删除
        $arr=array(
            'name' => '删除CURD',
            'controller' => 'Autocurd/curdlist',
            'action' => 'delcurd',
            'parent_id' => $pid,
            'is_nav' => 0,
            'icon' => '',
            'p_id' => 0,
            'type' => 2,
            'addons' => 'development'
        );
        Db::name('general_admin_permission')->insert($arr);

        echo "安装插件";
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * 实现的testhook钩子方法
     * @return mixed
     */
    public function develohook($param)
    {
        if($param['type']=='install'){
            //$this->install();exit;
        }
        if($param['type']=='view'){
            $name=$param['name'];
            $assign=@$param['assign'];
            if($assign){
                foreach ($assign as $key=> $value) {
                    $this->assign($key, $value);
                }
            }
            return $this->fetch($name);exit;
        }
    }

}