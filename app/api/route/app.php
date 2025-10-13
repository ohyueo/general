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
 * Date: 2021/5/25
 * Time: 9:44
 */

use think\facade\Route;

Route::group(function () {
    Route::get('zong_imglist', 'imglist');  // 首页
    Route::get('noti_info', 'noti_info');  // 公告详情
    Route::post('mapdistance', 'mapdistance');
})->prefix('Index/')->middleware(\app\middleware\CheckApiRequest::class);

Route::group(function () {
    Route::get('getclass', 'getclass');  // 查询分类
    Route::get('yuyue_info', 'yuyue_info');  // 预约详情
    Route::get('getydate', 'getydate');  // 预约的日期
    Route::post('getytime', 'getytime');  // 查询各时间情况
    Route::get('getyuyue', 'getyuyue');  // 查询分类
    Route::post('add_yuyue', 'add_yuyue');  // 添加预约
    Route::post('quxiao_yuyue', 'quxiao_yuyue');  // 取消预约
    Route::post('yuelist', 'yuelist');
    Route::post('get_spec', 'get_spec');
})->prefix('Yuyue/')->middleware(\app\middleware\CheckApiRequest::class);

Route::group(function () {
    Route::post('user_info', 'user_info');  // 用户详情
    Route::post('img_upload', 'upload');  // 上传用户头像
    Route::post('setnick', 'setnick');  // 修改用户昵称
    Route::post('my_yuyue', 'my_yuyue');  // 我的预约
    Route::post('getsite', 'getsite');  // 获取系统设置
    Route::post('myorderinfo', 'myorderinfo');  // 我的预约
    Route::post('setpaylist', 'setpaylist');  // 我的支付
    Route::post('setdefaultlist', 'setdefaultlist');  // 违约记录
})->prefix('User/')->middleware(\app\middleware\CheckApiRequest::class);

Route::group(function () {
    Route::post('my_messlist', 'mymesslist');  // 我的消息
    Route::post('my_messinfo', 'mymessinfo');  // 消息详情
    Route::post('my_mess_weidu', 'mymessweidu');  // 我的未读消息
    Route::post('messyidu', 'messyidu');  // 我的未读消息
})->prefix('Messages/')->middleware(\app\middleware\CheckApiRequest::class);

Route::group(function () {
    Route::post('xiaodata', 'xiaodata');
    Route::post('hexiao', 'hexiao');
    Route::post('hexiaolist', 'hexiaolist');
    Route::post('formupload', 'upload');
})->prefix('Order/')->middleware(\app\middleware\CheckApiRequest::class);

Route::group(function(){
    Route::post('sendmsg', 'sendmsg');  // 发送验证码
    Route::post('phonelogin', 'mobileCodeLogin');  // 手机号+验证码 注册/登录
    Route::post('register', 'register');  // 手机+密码注册
    Route::post('reg_user', 'reg_username');  // 用户+密码注册
    Route::post('login_user', 'login_username');  // 用户+密码注册
})->prefix('Login/')->middleware(\app\middleware\CheckApiRequest::class);

Route::group(function(){
    Route::get('wxregister', 'wxregister');  //
    Route::post('getinfo', 'getinfo');  //
    Route::post('getuserinfo', 'getuserinfo'); //新版本
    Route::post('getphone', 'getphone');  //
    Route::get('wxh5register', 'wxh5register');
})->prefix('Wxlogin/')->middleware(\app\middleware\CheckApiRequest::class);

Route::group(function (){
    Route::post('getinvitation', 'getinvitation');
    Route::post('addcode', 'addcode');
    Route::post('invitalist', 'invitalist');
    Route::post('addinvata', 'addinvata');
    Route::post('getinvata', 'getinvata');
})->prefix('Invitation/')->middleware(\app\middleware\CheckApiRequest::class);

Route::group(function (){
    Route::get('texterlist', 'texterlist');
    Route::get('textinfo', 'textinfo');
})->prefix('Texter/')->middleware(\app\middleware\CheckApiRequest::class);

Route::group(function (){
    Route::post('seatlist', 'seatlist');
})->prefix('Seat/')->middleware(\app\middleware\CheckApiRequest::class);

Route::group(function (){
    Route::post('platelist', 'platelist');
    Route::post('platetime', 'platetime');
    Route::post('merlist', 'merlist');
    Route::post('merorderlist', 'merorderlist');
    Route::post('platetime', 'platetime');
})->prefix('Merch/')->middleware(\app\middleware\CheckApiRequest::class);


Route::group(function (){
    Route::post('getperson', 'getperson');
    Route::post('getytimelist', 'getytimelist');

})->prefix('Time/')->middleware(\app\middleware\CheckApiRequest::class);


Route::group(function () {
    Route::get('shop_list', 'shoplist');  // 商品列表
    Route::get('shop_info', 'shopinfo');  // 商品详情
    Route::get('shoporder', 'shoporder');  // 商品详情
    Route::get('getshop', 'getshop');  // 商品详情
    Route::post('addshop', 'addshop');  // 添加商品
    Route::post('shopord', 'shopord');  // 添加商品
    Route::post('shopordinfo', 'shopordinfo');
    Route::post('shop_quxiao', 'shop_quxiao');
    Route::post('shoplistindex', 'shoplistindex');
    Route::post('querenorder', 'querenorder');

})->prefix('Shop/')->middleware(\app\middleware\CheckApiRequest::class);

//Route::miss(function() {
//    abort(404, '请求地址不存在');
//});

Route::group(function (){
    Route::post('addsignin', 'addsignin');
})->prefix('Signin/')->middleware(\app\middleware\CheckApiRequest::class);

