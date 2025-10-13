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
 * Date: 2022/3/24
 * Time: 16:01
 */

namespace app\common;
use think\facade\Db;

class WeChat
{
    //公众号
    public function getAccessTokengzh(){
        $arrContextOptions = array(
            "ssl" => array(
                "allow_self_signed" => true,
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );
        $wxAppID = config('-wxsite.wx_gzh_appid');
        $wxAppSecret = config('-wxsite.wx_gzh_key');
        $time = time()+7000; //当前时间+2小时等于过期时间
        $res = Db::name('general_access_token')->where('expires_time','>',time())->field('access_token')->find();
        if($res){
            return $res;
        }else{
            $wxAccessTokenUrl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$wxAppID.'&secret='.$wxAppSecret;
            $result = file_get_contents($wxAccessTokenUrl,false, stream_context_create($arrContextOptions));
            $jsondecode = json_decode($result); //对JSON格式的字符串进行编码
            $array = get_object_vars($jsondecode);//转换成数组
            $access_token = $array['access_token'];
            $model = Db::name('general_access_token'); //把获取的ticket存储到数据库中
            if($access_token){
                $data = array(
                    'access_token' => $access_token,
                    'expires_time' => $time
                );
                $model->insert($data); //把获得的token存储到数据库中
            }
        }

        return $array;
    }
    //公众号
    public function sendh5Message($openId,$templateId,$arr,$id)
    {
        //获取access_token
        $access_token = $this->getAccessTokengzh();
        $access_token = $access_token['access_token'];
        //要发送给微信接口的数据
        $send_data = [
            //用户openId
            "touser" => $openId,
            //模板id
            "template_id" => $templateId,
            //指定发送到开发版
            "url"=>config('-appsite.app_domainname')."#/pages/order/myorder_info?id=".$id,
            //点击跳转到小程序的页面  跳转小程序类型：developer为开发版；trial为体验版；formal为正式版；默认为正式版 Q2QJSetPtlWiMhKX1IdhRIFY9oxARidZ4IShkdFWeVc
            "data"=>$arr,
        ];
        //将路径中占位符%s替换为$access_token值
        $urls='https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$access_token;
        $ret = posturl($urls, $send_data);
        return $ret;
    }
    //小程序
    public function getAccessToken(){
        $arrContextOptions = array(
            "ssl" => array(
                "allow_self_signed" => true,
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );
        $wxAppID = config('-wxsite.wx_xcx_appid');
        $wxAppSecret = config('-wxsite.wx_xcx_key');
        $wxAccessTokenUrl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$wxAppID.'&secret='.$wxAppSecret;
        $result = file_get_contents($wxAccessTokenUrl,false, stream_context_create($arrContextOptions));
        //$wxAccessToken = json_decode($result,true);
        $jsondecode = json_decode($result); //对JSON格式的字符串进行编码
        $array = get_object_vars($jsondecode);//转换成数组
        return $array;
    }
    public function sendMessage($openId,$templateId,$arr)
    {
        //获取access_token
        $access_token = $this->getAccessToken();
        $access_token = $access_token['access_token'];
        //要发送给微信接口的数据
        $send_data = [
            //用户openId
            "touser" => $openId,
            //模板id
            "template_id" => $templateId,
            //指定发送到开发版
            "miniprogram_state"=>"formal",
            //点击跳转到小程序的页面  跳转小程序类型：developer为开发版；trial为体验版；formal为正式版；默认为正式版 Q2QJSetPtlWiMhKX1IdhRIFY9oxARidZ4IShkdFWeVc
            "page"=>'/pages/order/myorder',
            "data"=>$arr
        ];
        //将路径中占位符%s替换为$access_token值
        $urls='https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token='.$access_token;
        $ret = posturl($urls, $send_data);
        return $ret;
    }
}