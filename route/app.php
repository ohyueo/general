<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP6!';
});

Route::get('ohyueo_svn', function () {
    return "<div style='line-height: 30px;'><span style='font-size: 14px;color:green;'>© 海之心通用预约系统</span> <br/><span style='font-size: 14px;color:#2979ff;'>成都海之心科技有限公司权利所有</span> <br/><span style='font-size: 14px;color:red;'>官方网址: <a href='https://www.ohyu.cn/' style='color: red' target='_blank'>www.ohyu.cn</a><br/>在未得到官方有效许可的前提下禁止对程序代码进行修改和使用,以及任何形式任何目的再发布.</span></div>";
});

Route::get('hello/:name', 'index/hello');
