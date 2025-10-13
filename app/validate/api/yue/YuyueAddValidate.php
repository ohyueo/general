<?php
declare (strict_types = 1);

namespace app\validate\api\yue;

use think\Validate;

class YuyueAddValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'token' => 'require',
        'id' => 'require',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'token.require' => 'token不能为空',
        'id.require' => 'id不能为空',
    ];
}
