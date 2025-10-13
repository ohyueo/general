<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Request;
// 应用公共文件
//图片地址
function getFullImageUrl($img){
    //如果img第一个字符不是/则加上/
    if(substr($img,0,1)!='/'){
        $img='/'.$img;
    }
    return getUrl() . $img;
}
function getUrl(){
    $url=config('-appsite.app_domainname');
    if(!$url){
        //获取当前域名
        $request = Request::instance();
        $url=$request->domain();
    }
    //如果url最后一个字符是/则去掉/
    if(substr($url,-1)=='/'){
        $url=substr($url,0,-1);
    }
    return $url;
}
/**
 * @$data   需要处理的数组
 * @$pid    父级ID
 * @$level  等级
 */
function tree($data, $pid = 0, $level = 0)
{
    static $tree = array();
    foreach ($data as $key => $value) {
        if ($value['parent_id'] == $pid) {
            $value['level'] = $level;
            $tree[] = $value;
            tree($data, $value['id'], $level + 1);
        }
    }
    return $tree;
}

function is_mobile( $text ) {
    $search = '/^1[3|4|5|6|7|8|9]\d{9}$/';
    if ( preg_match( $search, $text ) ) {
        return ( true );
    } else {
        return ( false );
    }
}
/**
 *判断字符串是否全是中文
 */
function isAllChinese($str){
    if(preg_match('/^[\x7f-\xff]+$/', $str)){
        return true;//全是中文
    }else{
        return false;//不全是中文
    }
}
/**
 * 判断是否为合法的身份证号码
 * @param $mobile
 * @return int
 */
function isCreditNo($vStr){
    $vCity = array(
        '11','12','13','14','15','21','22',
        '23','31','32','33','34','35','36',
        '37','41','42','43','44','45','46',
        '50','51','52','53','54','61','62',
        '63','64','65','71','81','82','91'
    );
    if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr)) return false;
    if (!in_array(substr($vStr, 0, 2), $vCity)) return false;
    $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
    $vLength = strlen($vStr);
    if ($vLength == 18) {
        $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
    } else {
        $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
    }
    if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
    if ($vLength == 18) {
        $vSum = 0;
        for ($i = 17 ; $i >= 0 ; $i--) {
            $vSubStr = substr($vStr, 17 - $i, 1);
            $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr , 11));
        }
        if($vSum % 11 != 1) return false;
    }
    return true;
}

//生成token
function lgaddtoken($user,$v='ohyueo'){
    //生成模式
    $time=time();
    $h='@#$';
    //随机码
    $key='www.ohyu.';
    return md5($user.$v.$time.$h);
}
function gettime(){
    return date('Y-m-d H:i:s');
}
function getip(){
    return request()->ip();
}
//自定义函数手机号隐藏中间四位
function yc_phone($str){
    $str=$str;
    $resstr=substr_replace($str,'****',3,4);
    return $resstr;
}
//生成订单号
function neworderNumnew(){
    $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N','O','P','Q');
    $orderSn = $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
    return $orderSn;
}
//自定义函数手机号隐藏中间四位
function yc_phonex($str){
    $str=$str;
    $resstr=substr_replace($str,'**',3);
    return $resstr;
}
function posturl($url, array $params = array()){
    $data_string = json_encode($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt(
        $ch, CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json'
        )
    );
    $data = curl_exec($ch);
    curl_close($ch);
    return ($data);
}
function week($no){
    //获取30天的房型  预订数据
    $weekArr=array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
    $days=array();
    for($i=0;$i<$no;$i++){
        $days[$i]['time']=date("m-d",strtotime('+'.$i.'day'));
        if ($i==0) {
            $days[$i]['week']= '今天';
        }else{
            $days[$i]['week']= $weekArr[date("w",strtotime('+'.$i.'day'))];
        }
        $days[$i]['wek']= date("w",strtotime('+'.$i.'day'));
        $days[$i]['riqi'] = date("Y-m-d",strtotime('+'.$i.'day'));
        $days[$i]['day'] = date("m月d号",strtotime('+'.$i.'day'));
        $num = 0;// 这里需要去获取数量 $yuyuedb->where($da)->getField('num');
        if (empty($num)) {
            $num = 2;
        }
        $days[$i]['num']=$num;
    }
    return $days;
}
function createNoncestr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for($i = 0; $i < $length; $i ++) {
        $str .= substr ( $chars, mt_rand ( 0, strlen ( $chars ) - 1 ), 1 );
    }
    return $str;
}
// 过滤危险标签：防SS攻击  composer require ezyang/htmlpurifier
if (!function_exists('remove_xss')) {
    //使用htmlpurifier防范xss攻击
    function remove_xss($string)
    {
        //composer安装的，不需要此步骤。相对index.php入口文件，引入HTMLPurifier.auto.php核心文件
        // require_once './plugins/htmlpurifier/HTMLPurifier.auto.php';
        // 生成配置对象
        $cfg = HTMLPurifier_Config::createDefault();
        // 以下就是配置：
        $cfg->set('Core.Encoding', 'UTF-8');
        // 设置允许使用的HTML标签
        $cfg->set('HTML.Allowed', 'div,b,strong,i,em,a[href|title],ul,ol,li,br,p[style],span[style],img[width|height|alt|src],font[color],table[border],tbody,tr,td[width]');
        // 设置允许出现的CSS样式属性
        $cfg->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align');
        // 设置a标签上是否允许使用target="_blank"
        $cfg->set('HTML.TargetBlank', TRUE);
        // 使用配置生成过滤用的对象
        $obj = new HTMLPurifier($cfg);
        // 过滤字符串
        return $obj->purify($string);
    }
}
/**
 * 文件下载
 * @param $url
 * @param string $absolute_path
 */
function download($url, $absolute_path = '')
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    $file = curl_exec($ch);
    curl_close($ch);
    $resource = fopen($absolute_path, 'a');
    fwrite($resource, $file);
    fclose($resource);
}
function dowwximg($url, $absolute_path = ''){
    $curl=curl_init();
    curl_setopt($curl,CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
    $data=curl_exec($curl);
    if(curl_errno($curl)){return 'ERROR'.curl_errno($curl);}
    curl_close($curl);
    $resource = fopen($absolute_path, 'a');
    fwrite($resource, $data);
    fclose($resource);
}

//判断是否在某个时间段内
function get_curr_time_section($str,$end)
{
    $checkDayStr = date('Y-m-d ',time());
    $timeBegin1 = strtotime($checkDayStr.$str.":00");
    $timeEnd1 = strtotime($checkDayStr.$end.":00");
    $curr_time = time();
    if($curr_time >= $timeBegin1 && $curr_time <= $timeEnd1)
    {
        return true;//是
    }
    return false;
}

/**
 * 随机字符
 * @param number $length 长度
 * @param string $type 类型
 * @param number $convert 转换大小写
 * @return string
 */
function random($length=6, $type='string', $convert=0){
    $config = array(
        'number'=>'1234567890',
        'letter'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'string'=>'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
        'all'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
    );

    if(!isset($config[$type])) $type = 'string';
    $string = $config[$type];

    $code = '';
    $strlen = strlen($string) -1;
    for($i = 0; $i < $length; $i++){
        $code .= $string{mt_rand(0, $strlen)};
    }
    if(!empty($convert)){
        $code = ($convert > 0)? strtoupper($code) : strtolower($code);
    }
    return $code;
}
//清理缓存函数
if (!function_exists('delete_dir_file'))
{
    /**
     * 循环删除目录和文件
     * @param string $dir_name
     * @return bool
     */
    function delete_dir_file($dir_name) {
        $result = false;
        if(is_dir($dir_name)){
            if ($handle = opendir($dir_name)) {
                while (false !== ($item = readdir($handle))) {
                    if ($item != '.' && $item != '..') {
                        if (is_dir($dir_name . '/' . $item)) {
                            delete_dir_file($dir_name . '/' . $item);
                        } else {
                            unlink($dir_name . '/' . $item);
                        }
                    }
                }
                closedir($handle);
                if (rmdir($dir_name)) {
                    $result = true;
                }
            }
        }
        return $result;
    }
}