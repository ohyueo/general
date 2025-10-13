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
 * Date: 2021/9/16
 * Time: 19:21
 */

namespace app\handler;
use app\models\GeneralYuyueOrdermsg;
use think\facade\Db;

class YuyueListmsgHandler
{
    public static function add($title, $order_id, $status, $text)
    {
        GeneralYuyueOrdermsg::create([
            'title' => $title,
            'order_id' => $order_id,
            'status' => $status,
            'texter' => $text,
            'addtime' => gettime()
        ]);
        // 更新预约订单状态 general_yuyue_ordermultiple
        Db::name('general_yuyue_ordermultiple')->where('order_id', $order_id)->update(['status' => $status]);
    }
}