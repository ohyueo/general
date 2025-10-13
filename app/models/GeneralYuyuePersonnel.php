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
 * Date: 2022/6/28
 * Time: 21:42
 */
namespace app\models;

use think\Model;
class GeneralYuyuePersonnel extends Model
{
    // 定义全局的查询范围
    public static function base($query)
    {
        // 获取当前的商户ID
        $merchantId = session('merchant_id');
        if($merchantId){
           // 添加一个条件，这个条件会在每次查询时自动应用
            $query->where('merchant_id', $merchantId);
        }
    }
    //定义写入前
    public static function onBeforeInsert($query)
    {
        // 获取当前的商户ID
        $merchantId = session('merchant_id');
        if($merchantId){
            // 设置到模型的merchant_id属性
            $query->merchant_id = $merchantId;
        }
    }
    //定义更新前
    public static function onBeforeUpdate($query)
    {
        // 获取当前的商户ID
        $merchantId = session('merchant_id');
        // 检查模型的merchant_id属性是否匹配
        if ($merchantId && $query->merchant_id != $merchantId) {
            // 如果不匹配，抛出一个异常
            throw new \Exception('No permission to update this record');
        }
    }
    //定义删除前
    public static function onBeforeDelete($query)
    {
        // 获取当前的商户ID
        $merchantId = session('merchant_id');
        // 检查模型的merchant_id属性是否匹配
        if ($merchantId && $query->merchant_id != $merchantId) {
            // 如果不匹配，抛出一个异常
            throw new \Exception('No permission to delete this record');
        }
    }
}
