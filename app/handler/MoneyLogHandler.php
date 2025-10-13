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
 * Date: 2021/5/25
 * Time: 11:02
 */
namespace app\handler;

use app\models\GeneralUserList;

class MoneyLogHandler
{
    /**
     * 加入记录
     *
     * @param User $user
     * @param      $money
     * @param int  $motype
     * @param      $type
     * @param      $text
     */
    public static function add(GeneralUserList $user, $money, $motype = 1, $type, $text)
    {
        $user->moneyLogs()->save([
            'user_id' => $user->id,
            'money' => $money,
            'type' => $type,
            'motype' => $motype,
            'text' => $text,
            'addtime' => gettime(),
            'yue' => $user->money
        ]);
    }
}