<?php

use app\AppService;

// 系统服务定义文件
// 服务在完成全局初始化之后执行
return [
    AppService::class,
    app\service\EasysmsService::class, // 绑定发送短信服务
    //app\service\PayService::class,  // 支付服务
];
