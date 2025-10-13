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
 * Date: 2022/5/10
 * Time: 11:41
 */

namespace app\common;
use think\facade\Db;

class Map
{
    //根据ip查询城市
    public function getmapip($ip){
        $key=config('-mapsite.map_appkey');
        $url="https://apis.map.qq.com/ws/location/v1/ip?ip=".$ip."&key=$key";
        $result = file_get_contents($url);
        $jsondecode = json_decode($result,true); //对JSON格式的字符串进行编码
        return $jsondecode;
    }
    //批量测算距离
    public function mapdistance($from,$to){
        $key=config('-mapsite.map_appkey');
        $url='https://apis.map.qq.com/ws/distance/v1/matrix?mode=driving&from='.$from.'&to='.$to.'&key='.$key;
        $result = file_get_contents($url);
        $jsondecode = json_decode($result,true); //对JSON格式的字符串进行编码
        return $jsondecode;
    }
}