<?php
declare (strict_types = 1);

namespace app\validate\api\reg;

use think\Validate;

class MobileCodeLoginValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'mobile' => 'require|mobile',
        'code' => 'require|length:6',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'mobile.require' => '手机号不能为空',
        'mobile.mobile' => '手机号格式不正确',
        'code.require' => '验证码不能为空',
        'code.length' => '验证码错误',
    ];
}
