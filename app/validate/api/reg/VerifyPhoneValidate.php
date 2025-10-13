<?php
//declare (strict_types = 1);

namespace app\validate\api\reg;

use think\Validate;
use app\models\UserLogin;

class VerifyPhoneValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'mobile' => 'require|mobile',
        'type' => 'require|in:login,back,register'
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'mobile.require' => '手机号不能为空',
        'mobile.mobile' => '手机号格式错误',
        'type.require' => '短信类型不能为空',
        'type.in' => '发送短信类型错误'
    ];

    // 如果是注册，验证手机号是否存在
    protected function checkType($value, $rule, $data=[])
    {

        if ($data['type'] == 'bind') return true;

        $first = UserLogin::where('mobile', $data['mobile'])->count();

        if ($data['type'] == 'register') {
            // 注册
            return $first ? '手机号已注册': true;
        } else {
            // 找回密码
            return !$first ? '手机号未注册': true;
        }
    }
}
