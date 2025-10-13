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
 * Date: 2021/5/16
 * Time: 15:22
 */

namespace app\shop\model;

use think\Model;

class ShopList extends Model
{
    /**
     * 获取完整图片路径
     *
     * @param $value
     * @return string
     */
    public function getImgAttr($value)
    {
        return getFullImageUrl($value);
    }
}