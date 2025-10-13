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
 * Date: 2022/3/14
 * Time: 14:07
 */
namespace app\handler;

use app\models\GeneralYuyueBrowse;

class BrowseHandler
{
    public static function add($uid=0)
    {
        //先查询ip是否存在 每日储存
        $ip=getip();
        $res=GeneralYuyueBrowse::where('ip',$ip)->whereDay('addtime')->find();
        if(!$res){ //没有直接存
            GeneralYuyueBrowse::create([
                'ip' => $ip,
                'uid' => $uid,
                'no' => 1,
                'addtime' => gettime()
            ]);
        }else if($res && !$res['uid']){
            $res->uid=$uid;
            $res->no+=1;
            $res->save();
        }else{
            $res->no+=1;
            $res->save();
        }
    }
}