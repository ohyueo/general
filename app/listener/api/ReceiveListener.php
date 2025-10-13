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
 * Date: 2022/4/20
 * Time: 15:30
 */
declare (strict_types = 1);

namespace app\listener\api;

use app\models\GeneralUserList;
use app\handler\MoneyLogHandler;

class ReceiveListener
{
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle($event)
    {
        $this->store($event['user'], $event['money']);
    }
    public function store($user, $money)
    {
        try {
            $rel1 = $user->rel1;
            $rel2 = $user->rel2;
            $rel2_money=0;
            $rel1_money=0;
            $fmo1=config('-systemsite.sys_userrel1mo');
            if($fmo1){
                $rel1_money = round($fmo1 * $money / 100, 2);
            }
            $fmo2=config('-systemsite.sys_userrel2mo');
            if($fmo2){
                $rel2_money = round($fmo2 * $money / 100, 2);
            }
            if ($rel1 && $rel1_money > 0) {
                // 给上级分成
                $ux1=GeneralUserList::find($rel1);
                $ux1->price += $rel1_money;
                $ux1->money += $rel1_money;
                $ux1->save();
                // 增加下级给自己的贡献
                $ux1->userinfo()->inc('lower1_total_money', $rel1_money)->update();
                // 资金记录
                MoneyLogHandler::add($ux1, $rel1_money, 1, 3, '下级ID【' . $user->id . '】下单成功');
                // 增加给上级的贡献值
                $user->userinfo()->inc('rel1_money', $rel1_money)->update();
            }
            if ($rel2 && $rel2_money) {
                // 给上上级加钱
                $ux2=GeneralUserList::find($rel1);
                $ux2->price += $rel2_money;
                $ux2->money += $rel2_money;
                $ux2->save();
                // 增加下下级给自己的贡献
                $ux2->userinfo()->inc('lower2_total_money', $rel2_money)->update();
                // 资金记录
                MoneyLogHandler::add($rel2, $rel2_money, 1, 3, '下下级ID【' . $user->id . '】下单成功');
                // 增加给上级的贡献值
                $user->userinfo()->inc('rel2_money', $rel2_money)->update();
            }
        } catch (\Exception $e) {
            // 发生错误，什么都不做
        }
    }
}