<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2021/6/4
 * Time: 17:33
 */
namespace app\models;

use think\Model;
class GeneralYuyueOrder extends Model
{
    public function yuyuelist()
    {
        return $this->belongsTo(GeneralYuyueList::class,'list_id');
    }
    public function user()
    {
        return $this->belongsTo(GeneralUserList::class,'uid');
    }
    public function yuyuespec()
    {
        return $this->belongsTo(GeneralYuyueYuyuespec::class,'specid');
    }
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