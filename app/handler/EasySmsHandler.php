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

namespace app\handler;

use think\facade\Log;
use app\models\EasySms;

class EasySmsHandler
{
    public static function send($mobile, $code, $type = 'register')
    {
        $sms  =  app('easysms');
        
        $template =config('-smssite.sms_reg_temid'); //注册模板

        if ($type == 'login') {
            $template = config('-smssite.sms_login_temid'); //登录模板
        }
        if ($type == 'back') {
            $template = config('-smssite.sms_back_temid'); //找回密码模板
        }

        // 发送短信方式 2 阿里云  1 腾讯云  3 短信宝   4 聚合短信
        $type = config('-smssite.sms_sendtype');
        if ($type == 2) {
            $gateways = 'aliyun';
            $data = [
                'template' => $template,
                'data' => [
                    'code' => $code
                ],
            ];
        } elseif ($type == 1) {
            $gateways = 'qcloud';
            $data = [
                'template' => $template,   // 你在腾讯云配置的"短信正文”的模板ID
                'data' => [$code, 3]
            ];
        } elseif ($type == 3) {
            $gateways = 'smsbao';
            $data = '您的验证码为：' . $code . '(5分钟内有效)';
        } elseif ($type == 4) {
            $gateways = 'juhe';
            $data = [
                'template' => $template,
                'data' => [
                    'code' => $code
                ]
            ];
        } else {
            abort(500, '系统内部错误');
        }
        //$sms = new EasySms(config('easysms'));
        //var_dump(config('easysms'));
        try {
            // 发送短信
            $res=$sms->send($mobile, $data,[$gateways]);
            if($res){
                $arr=['code'=>1,'message'=>'发送成功'];
                return $arr;
            }else{
                $arr=['code'=>0,'message'=>'发送失败'];
                return $arr;
            }
            // 写入数据库
            // EasySms::create([
            //     'mobile' => $mobile,
            //     'code' => $code
            // ]);
        } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
            $message = $exception->getException($gateways)->getMessage();
            $arr=['code'=>0,'message'=>$message];
            return $arr;
        }
    }
}