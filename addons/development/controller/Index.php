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
 * Date: 2023/3/10
 * Time: 19:02
 */

namespace addons\development\controller;


class Index
{
    public function index(){
        echo "海之心系统-开发者模块 ";
    }
    public function svn(){
        return "<div style='line-height: 30px;'><span style='font-size: 14px;color:green;'>© 海之心系统</span> <br/><span style='font-size: 14px;color:#2979ff;'>成都海之心科技有限公司权利所有</span> <br/><span style='font-size: 14px;color:red;'>官方网址: <a href='https://www.ohyu.cn/' style='color: red' target='_blank'>www.ohyu.cn</a><br/>在未得到官方有效许可的前提下禁止对程序代码进行修改和使用,以及任何形式任何目的再发布.</span></div>";
    }
    public function install(){
        hook('develohook', ['type'=>'install']);
    }
}