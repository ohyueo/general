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
 * Date: 2021/5/29
 * Time: 15:16
 */

namespace app\api\controller;
use app\BaseController;
use app\models\GeneralUserList;
use app\models\GeneralUserLogin;
use app\models\GeneralUserInfo;
use app\validate\api\reg;
use think\Facade\Db;
use think\facade\Log;
use think\Request;
use app\handler\EasySmsHandler;


class Login extends BaseController
{
    //发送验证码
    public function sendmsg(Request $request){
        $data = $request->post();
        // 检验数据
        validate(reg\VerifyPhoneValidate::class)->check($data);
        if (env('app_debug')) {
            // 如果是调试模式，则不用发短信
            $code = 123456;
        }else{
            // 随机验证码
            $code = str_pad(random_int(1, 999999), 6, 0, STR_PAD_RIGHT);
            // 发送短信
            $params = [
                'mobile' => $data['mobile'],
                'code' => $code,
                'type' => $data['type']
            ];
            //发送验证码
            $res=EasySmsHandler::send($params['mobile'], $params['code'], $params['type']);
            if(!$res && !$res['code']){
                return $this->message($res['message'],[],403);
            }
        }
        // 缓存30分钟
        cache($data['mobile'] . '_verification', $code, 1800);
        return $this->message('发送成功');
    }
    //手机号登录
    public function mobileCodeLogin(Request $request){
        $data = $request->post();
        // 检验数据
        validate(reg\MobileCodeLoginValidate::class)->check($data);
        $code = cache($data['mobile'] . '_verification');
        if (!$code) {
            return $this->message('验证码已过期', [], 0);
        }
        if (!hash_equals($data['code'], $code)) {
            // 返回401
            return $this->message('验证码错误', [], 0);
        }

        // 删除缓存
        cache($data['mobile'] . '_verification', null);
        try {
            $user = GeneralUserLogin::whereMobile($data['mobile'])->find();
            if ($user) {
                $user=GeneralUserList::find($user->uid);
                $userid=$user->id;
                $name=$user->nick;
                if(!$name){
                    $name='默认用户'.$userid;
                }
                $headimg=$user->headimg;
                if($headimg){
                    $himg=getFullImageUrl($headimg);
                }else{
                    $himg=getFullImageUrl('/storage/imges/mo.png');
                }
                // 登录成功
                if ($user->status==2) {
                    // 账号被禁止登录
                    return $this->message($user->forbid_msg ?: '该账户已被禁止登陆', [], 0);
                }
            } else {

                $uid2=0;
                $uid=input('param.uid');
                Log::info('上级师傅uid='.$uid);
                if($uid){
                    $sj=GeneralUserList::find($uid);
                    if($sj){
                        $uid2=$sj->rel1;
                        if(!$sj->userinfo->isyao){
                            $uid=0;$uid2=0;//没有邀请权限
                            Log::info($uid.'=用户没有邀请权限');
                        }
                    }
                }

                // 注册
                $user = GeneralUserList::create([
                    'nick' => '默认用户',
                    'rel1' => $uid,
                    'rel2' => $uid2,
                    'money' => 0
                ]);
                $city='';
                $province='';
                $ip=getip();
                $map = new \app\common\Map();
                $res = $map->getmapip($ip);
                if($res && $res['status']==0){
                    $city=$res['result']['ad_info']['city'];
                    $province=$res['result']['ad_info']['province'];
                    event('RegProvince', ['province'=>$province]);//统计注册省份
                }else{
                    //接口异常 通知管理员
                    $message=$res['message'];
                    event('LogMessage', ['type'=>'getmapip','msg'=>$message]);
                }
                // 获取自增ID
                $userid=$user->id;
                //登录表
                GeneralUserLogin::create([
                    'uid' => $userid,
                    'reg_time' => gettime(),
                    'mobile' => $data['mobile'],
                    'reg_ip' => $ip,
                    'reg_city' => $city,
                    'reg_province' => $province
                ]);
                //详情
                GeneralUserInfo::create([
                    'user_id' => $userid
                ]);
                $name='默认用户'.$userid;
                $himg=getFullImageUrl('/storage/imges/mo.png');
            }
            $token = $this->saveToken($user);   //生成token
            $userlist=array('name'=>$name,'id'=>$userid,'headimg'=>$himg);
            return $this->message('登录成功', ['token' => $token,'userlist'=>$userlist]);
         } catch (\Exception $e) {
             return $this->message('登录失败'.$e->getMessage, [], 0);
         }
    }
    /**
     * 生成并保存token
     *
     * @param $user
     * @return string
     */
    public function saveToken($user)
    {
        // 登录成功,生成新的 token
        $token = md5(time() . $user->id);
        // 插入一条 token 数据
        $new=gettime();
        $user->userToken()->save(['token' => md5(time() . $user->id),'user_id'=>$user->id,'update_time'=>$new]);
        //兼容PC网页版 把token存入cookie
        cookie('user_cookie', $token, 7*24*3600);//七天
        return $token;
    }
}