<?php
// 事件定义文件
return [
    'bind'      => [
    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
        'SendMessage' => [app\listener\api\SendMessageListener::class],  // 给用户发送消息
        'LogMessage' => [app\listener\common\LogMessageListener::class],  // 统一保存日志  方便做通知
        'RegProvince' => [app\listener\common\RegProvinceListener::class],  // 注册省份统计
        'Receive' => [app\listener\api\ReceiveListener::class],  // 分销分成
        'SendYuyueMessage' => [app\listener\api\SendYuyueMessageListener::class],  // 发送预约通知
    ],

    'subscribe' => [
    ],
];
