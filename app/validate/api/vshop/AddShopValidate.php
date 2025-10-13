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
 * Date: 2022/2/17
 * Time: 11:23
 */
declare (strict_types = 1);

namespace app\validate\api\vshop;

use think\Validate;

class AddShopValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'orderdata' => 'require|chsDash',
        'message'=>'chsAlphaNum',
        'address' => 'require|chsDash',
        'mobile'=>'require|mobile',
        'name' => 'require|chsAlphaNum',
        'token' => 'require',
        'id' => 'require|number',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'orderdata.chsDash' => '数据格式不对',
        'orderdata.require' => '数据不能为空',
        'message.chsAlphaNum' => '备注类型错误，只能是汉字或字母或数字',
        'address.chsDash' => '收货地址格式不对',
        'address.require' => '收货地址不能为空',
        'mobile.mobile' => '手机号格式不对',
        'mobile.require' => '手机号不能为空',
        'name.chsAlphaNum' => '收货人格式类型不对',
        'name.require' => '收货人不能为空',
        'token.require' => 'token不能为空',
        'id.require' => 'id不能为空',
        'id.number' => 'id错误'
    ];
}