<?php

/**
 *  * 海之心任务悬赏系统-受国家计算机软件著作权保护（登记号：2021SR0164984） - !
 * =========================================================
 * Copy right 2018-2025 成都海之心科技有限公司, 保留所有权利。
 * ----------------------------------------------
 * 官方网址: http://www.ohyu.cn
 * 这不是一个自由软件！在未得到官方有效许可的前提下禁止对程序代码进行修改和使用。
 * 任何企业和个人不允许对程序代码以任何形式任何目的再发布。
 * =========================================================
 * Date: 2021/3/24
 * Time: 9:50
 */

namespace app\models;

use think\Model;

class Message extends Model
{
    /**
     * 获取用户
     *
     * @return \think\model\relation\BelongsTo
     */
    public function userlist()
    {
        return $this->belongsTo(UserList::class);
    }
}