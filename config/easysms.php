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
 * Date: 2021/5/16
 * Time: 16:26
 */

return [
    // HTTP 请求的超时时间（秒）
    'timeout' => 10.0,

    // 默认发送配置
    'default' => [
        // 网关调用策略，默认：顺序调用
        'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

        // 默认可用的发送网关
        'gateways' => [
            'aliyun',
            'qcloud',
            'smsbao',
            'juhe',
        ],
    ],
    // 可用的网关配置
    'gateways' => [
        'errorlog' => [
            'file' => runtime_path('logs/easy-sms.log'),
        ],
        'aliyun' => [
            'access_key_id' => config('-smssite.sms_appid'),
            'access_key_secret' => config('-smssite.sms_appkey'),
            'sign_name' => config('-smssite.sms_sign'),
            'templates' => [
                'register' => config('-smssite.sms_reg_temid'), // 注册模板
                'login' => config('-smssite.sms_login_temid'), // 登录模板
                'back' => config('-smssite.sms_back_temid'), // 找回密码模板
            ]
        ],
        'qcloud' => [
            'sdk_app_id' => config('-smssite.sms_appid'),   // 要在.env文件配置好相应的值
            'app_key' => config('-smssite.sms_appkey'),   // 要在.env文件配置好相应的值
            'sign_name' => config('-smssite.sms_sign'),   // 要在.env文件配置好相应的值
            'templates' => [
                'register' => config('-smssite.sms_reg_temid'), // 注册模板
                'login' => config('-smssite.sms_login_temid'), // 登录模板
                'back' => config('-smssite.sms_back_temid'), // 找回密码模板
            ]
        ],
        'smsbao' => [
            'user' => config('-smssite.sms_appid'),
            'password' => config('-smssite.sms_appkey'),
        ],
        'juhe' => [
            'app_key' => config('-smssite.sms_appkey'),
            'templates' => [
                'register' => config('-smssite.sms_reg_temid'), // 注册模板
                'login' => config('-smssite.sms_login_temid'), // 登录模板
                'back' => config('-smssite.sms_back_temid'), // 找回密码模板
            ]
        ]
    ],
];