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
 * Date: 2021/7/13
 * Time: 14:00
 */

namespace app\api\controller;
use app\BaseController;
use app\models\GeneralUserList;
use app\models\GeneralUserLogin;
use app\models\GeneralUserInfo;
use think\facade\Db;
use think\facade\Log;


class Wxlogin extends BaseController
{
    //公众号JSSDK
    /**
     * 添加微信分享接口
     * 第二步：用第一步拿到的access_token 采用http GET方式请求获得jsapi_ticket
     */
    public function getJsapiTicket($token){
        $time = time()+7000; //当前时间+2小时等于过期时间
        $res = Db::name('general_access_token')->where('ticket_expires_time','>',time())->field('ticket')->find();
        if($res){
            return $res;
        } else{
            $weixin = file_get_contents("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$token."&type=jsapi");
            $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
            $array = get_object_vars($jsondecode);//转换成数组
            //$res = json_decode($res, true);
            $ticket = $array['ticket'];
            // ticket不能频繁的访问接口来获取，在每次获取后，我们把它保存到数据库中。
            $model = Db::name('general_access_token'); //把获取的ticket存储到数据库中
            if($ticket){
                $data = array(
                    'access_token' => $token,
                    'expires_time' => $time,
                    'ticket' => $ticket,
                    'ticket_expires_time' => $time
                );
                $model->insert($data); //把获得的token存储到数据库中
            }else{
                $arr = array(
                    'access_token' => $token,
                    'expires_time' => $time
                );
                Db::name('general_access_token')->insert($arr);
            }
            $result['ticket'] = $ticket; //没查询到符合条件的
            return $result;
        }
    }

    // 获取签名
    public function getSignPackage() {
        // 获取token
        $wechat = new \app\common\WeChat();
        $token = $wechat->getAccessTokengzh();
        // 获取ticket
        $ticketList = $this->getJsapiTicket($token['access_token']);
        $ticket = $ticketList['ticket'];
        // 该URL为使用JSSDK接口的URL
        $url = input("param.url");
        if (!$url) {
            $url = 'http://www.liuxinfd.com/';
        }else{
            $url=urldecode($url);
        }
        $api = input("param.api");
        // 时间戳
        $timestamp = time();
        // 随机字符串
        $nonceStr = createNoncestr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序 j -> n -> t -> u
        $string = "jsapi_ticket=$ticket&noncestr=".$nonceStr."&timestamp=$timestamp&url=$url";
        $signature = sha1($string);

        $signPackage = array (
            "appId" => config('-wxsite.wx_gzh_appid'),
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            'jsApiList' => [$api],
            "signature" => $signature,
            "rawString" => $string,
            "ticket" => $ticket,
            "token" => $token['access_token']
        );
        return $this->message('请求成功', ['data'=>$signPackage], 200);
    }
    //是否关注公众号
    public function isfocuswx(){
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $openid=$user->openid;
        $access_token = $this->getAccessTokengzh();
        if($access_token){
            $access_token = $access_token['access_token'];
            $res=$this->getfocus($access_token,$openid);
            if(!$res || $res['subscribe']==0){
                $wxgzhcode=config('-wxsite.app_wxgzhcode');
                return $this->message('未关注公众号', ['wxgzhcode'=>$wxgzhcode], 2);
            }else{
                return $this->message('不需要关注了', [], 1);
            }
        }else{
            return $this->message('获取失败', [], 0);
        }
    }
    //获取关注
    public function getfocus($access_token,$openid){
        $weixin =  file_get_contents("https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN");
        $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
        $array = get_object_vars($jsondecode);//转换成数组
        return $array;//输出openid
    }
    //获取openid
    public function getopenid($code)
    {
        $appid = config('-wxsite.wx_xcx_appid');
        $secret = config('-wxsite.wx_xcx_key');
        $js_code = $code;//'011E0o000XYq4M1EZc200rnJDn2E0o0Z';//input('js_code');
        //通过code换取网页授权access_token
        $weixin =  file_get_contents("https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$secret&js_code=$js_code&grant_type=authorization_code");
        $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
        $array = get_object_vars($jsondecode);//转换成数组
        return $array;//输出openid
    }
    //获取openid
    public function geth5openid($code)
    {
        $appid = config('-wxsite.wx_gzh_appid');
        $secret = config('-wxsite.wx_gzh_key');
        $js_code = $code;//'011E0o000XYq4M1EZc200rnJDn2E0o0Z';//input('js_code');
        //通过code换取网页授权access_token
        $weixin =  file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$js_code&grant_type=authorization_code");
        $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
        $array = get_object_vars($jsondecode);//转换成数组
        return $array;//输出openid
    }
    //获取个人信息
    public function geth5user($access_token,$openid){
        $weixin =  file_get_contents("https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN");
        $jsondecode = json_decode($weixin); //对JSON格式的字符串进行编码
        $array = get_object_vars($jsondecode);//转换成数组
        return $array;//输出openid
    }
    //先注册用户置换出 code和key
    public function wxh5register(){
        $code=input('param.code');

        $res=$this->geth5openid($code);
        if($res){
            $access_token=$res['access_token'];
            $openid=$res['openid'];
            $unionid=@$res['unionid'];
            if(!$openid || !$access_token){
                return $this->message('请重新登录', [], 0);
            }
            //先查询该用户是否被注册
            $user = GeneralUserList::whereOpenid($openid)->find();
            if($user){ //如果存在 判断头像和id是否为空
                if ($user->status==2) {
                    // 账号被禁止登录
                    return $this->message( '该账户已被禁止登陆', [], 0);
                }
                if(!$user->userLogin){
                    GeneralUserLogin::create([
                        'uid' => $user->id,
                        'reg_time' => gettime(),
                        'reg_ip' => getip()
                    ]);
                }
                if(!$user['nick'] || !$user['headimg']){ //为空则获取信息
                    $info=$this->geth5user($access_token,$openid);
                    $nick=$info['nickname'];
                    $headimg=$info['headimgurl'];
                    $code = random(12);
                    $lujing = "Wx_img";
                    if(!is_dir($lujing)){
                        mkdir(iconv("UTF-8", "GBK", $lujing),0777,true);
                    }
                    $imgpath="Wx_img/".time().'-'.$code.'.jpg';
                    dowwximg($headimg,$imgpath);
                    $user->nick=$nick;
                    $user->headimg='/'.$imgpath;
                    $user->save();
                    $token = $this->saveToken($user);   //生成token
                    $uid=$user->id;
                    $userlist=array('name'=>$nick,'id'=>$uid,'headimg'=>getFullImageUrl($imgpath));
                    return $this->message('登录成功!', ['token' => $token,'userlist'=>$userlist],1);
                    //$this->decryptData($encryptedData, $iv, $data );
                }else{ //不为空 生成token 登录成功
                    $token = $this->saveToken($user);   //生成token
                    $uid=$user->id;
                    $headimg=$user->headimg;
                    $userlist=array('name'=>$user['nick'],'id'=>$uid,'headimg'=>getFullImageUrl($headimg));
                    return $this->message('登录成功', ['token' => $token,'userlist'=>$userlist],1);
                }
            }else{
                if($unionid){ //先判断唯一id是否存在
                    $uni=GeneralUserList::where('unionid',$unionid)->find();
                    if($uni){
                        $uni->openid=$openid;
                        $uni->save();
                        //如果存在 那则 直接存入openid就行
                        $token = $this->saveToken($uni);   //生成token
                        $uid=$uni->id;
                        $headimg=$uni->headimg;
                        $userlist=array('name'=>$uni['nick'],'id'=>$uid,'headimg'=>getFullImageUrl($headimg));
                        return $this->message('登录成功', ['token' => $token,'userlist'=>$userlist],1);
                    }
                }

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

                //不存在  注册用户
                $info=$this->geth5user($access_token,$openid);
                $nick=$info['nickname'];
                $headimg=$info['headimgurl'];
                $code = random(12);
                $lujing = "Wx_img";
                if(!is_dir($lujing)){
                    mkdir(iconv("UTF-8", "GBK", $lujing),0777,true);
                }
                $imgpath="Wx_img/".time().'-'.$code.'.jpg';
                $res=dowwximg($headimg,$imgpath);
                // 注册
                $user = GeneralUserList::create([
                    'nick' => $nick,
                    'headimg' => '/'.$imgpath,
                    'rel1' => $uid,
                    'rel2' => $uid2,
                    'openid' => $openid,
                    'unionid' => $unionid,
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
                }else if($res && $res['status']>0){
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
                    'reg_ip' => $ip,
                    'reg_city' => $city,
                    'reg_province' => $province
                ]);
                //详情
                GeneralUserInfo::create([
                    'user_id' => $userid
                ]);
                $token = $this->saveToken($user);   //生成token

                $userlist=array('name'=>$nick,'id'=>$userid,'headimg'=>getFullImageUrl('/'.$imgpath));
                return $this->message('注册成功', ['token' => $token,'userlist'=>$userlist],1);
            }
        }
    }
    //先注册用户置换出 code和key
    public function wxregister(){
        $code=input('param.code');
        $res=$this->getopenid($code);
        if($res){
            $session_key=$res['session_key'];
            $openid=$res['openid'];
            $unionid=@$res['unionid']; //小程序统一标识有问题
            if(!$openid || !$session_key){
                return $this->message('请重新登录', [], 0);
            }
            //先查询该用户是否被注册
            $user = GeneralUserList::where('xcx_openid',$openid)->find();
            if($user){ //如果存在 判断头像和id是否为空
                if ($user->status==2) {
                    // 账号被禁止登录
                    return $this->message( '该账户已被禁止登陆', [], 0);
                }
                if(!$user->userLogin){
                    GeneralUserLogin::create([
                        'uid' => $user->id,
                        'session_key' => $session_key,
                        'reg_time' => gettime(),
                        'reg_ip' => getip()
                    ]);
                }
                if(!$user['nick'] || !$user['headimg']){ //为空则获取信息
                    $user->userLogin->session_key=$session_key;
                    $user->userLogin->save();
                    $token = $this->saveToken($user);   //生成token
                    $uid=$user->id;
                    $headimg=$user->headimg;
                    $userlist=array('name'=>$user['nick'],'id'=>$uid,'headimg'=>getFullImageUrl($headimg));
                    return $this->message('需要获取信息', ['token' => $token,'userlist'=>$userlist], 1);
                    //$this->decryptData($encryptedData, $iv, $data );
                }else{ //不为空 生成token 登录成功
                    $user->userLogin->session_key=$session_key;
                    $user->userLogin->save();
                    $token = $this->saveToken($user);   //生成token
                    $uid=$user->id;
                    $headimg=$user->headimg;
                    $userlist=array('name'=>$user['nick'],'id'=>$uid,'headimg'=>getFullImageUrl($headimg));
                    $loginst=config('-wxsite.login_wxxcx')?:1;
                    if($loginst==1){
                        return $this->message('登录成功', ['token' => $token,'userlist'=>$userlist],1);
                    }else if($loginst==2){
                        return $this->message('登录成功', ['token' => $token,'userlist'=>$userlist],2);
                    }else if($loginst==3){
                        return $this->message('登录成功', ['token' => $token,'userlist'=>$userlist],200);
                    }
                }
            }else{
                if($unionid){ //先判断唯一id是否存在
                    $uni=GeneralUserList::where('unionid',$unionid)->find();
                    if($uni){
                        $uni->xcx_openid=$openid;
                        $uni->userLogin->session_key=$session_key;
                        $uni->save();
                        //如果存在 那则 直接存入openid就行
                        $token = $this->saveToken($uni);   //生成token
                        $uid=$uni->id;
                        $headimg=$uni->headimg;
                        $userlist=array('name'=>$uni['nick'],'id'=>$uid,'headimg'=>getFullImageUrl($headimg));
                        $loginst=config('-wxsite.login_wxxcx')?:1;
                        if($loginst==1){
                            return $this->message('登录成功', ['token' => $token,'userlist'=>$userlist],1);
                        }else if($loginst==2){
                            return $this->message('登录成功', ['token' => $token,'userlist'=>$userlist],2);
                        }else if($loginst==3){
                            return $this->message('登录成功', ['token' => $token,'userlist'=>$userlist],200);
                        }
                    }
                }
                //
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
                    'xcx_openid' => $openid,
                    'unionid' => $unionid,
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
                }else if($res && $res['status']>0){
                    //接口异常 通知管理员
                    $message=$res['message'];
                    event('LogMessage', ['type'=>'getmapip','msg'=>$message]);
                }
                // 获取自增ID
                $userid=$user->id;
                //登录表
                GeneralUserLogin::create([
                    'uid' => $userid,
                    'session_key' => $session_key,
                    'reg_time' => gettime(),
                    'rece_login_time' => gettime(),
                    'reg_ip' => $ip,
                    'reg_city' => $city,
                    'reg_province' => $province
                ]);
                //详情
                GeneralUserInfo::create([
                    'user_id' => $userid
                ]);
                $token = $this->saveToken($user);   //生成token
                return $this->message('注册成功', ['token' => $token], 1);
            }
        }
    }
    public function getinfo(){
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        if ($user->status==2) {
            // 账号被禁止登录
            return $this->message( '该账户已被禁止登陆', [], 0);
        }
        $id=$user->id;
        $session_key=$user->userLogin->session_key;
        $encryptedData=input('param.encryptedData');
        $iv=input('param.iv');
        $errCode = $this->decryptData($session_key,$encryptedData, $iv, $data );
        if ($errCode == 0) {
            $data=json_decode($data,true);
            if(!$user->nick){
                $user->nick=$data['nickName'];
                $user->save();
            }
            if(!$user->headimg){
                //没有注册过，创建新用户
                $code = random(12);
                $lujing = "Wx_img";
                if(!is_dir($lujing)){
                    mkdir(iconv("UTF-8", "GBK", $lujing),0777,true);
                }
                $imgpath="Wx_img/".time().'-'.$code.'.jpg';
                download($data['avatarUrl'],$imgpath);
                $user->headimg='/'.$imgpath;
                $user->save();
            }else{
                $imgpath=$user->headimg;
            }

            $token = $this->saveToken($user);   //生成token
            $uid=$user->id;
            $img=getFullImageUrl('/'.$imgpath);
            $userlist=array('name'=>$data['nickName'],'id'=>$uid,'headimg'=>$img);
            return $this->message('注册成功', ['token' => $token,'userlist'=>$userlist]);
            //print($data . "\n");
        } else {
            return $this->message('解密错误', [], 0);
        }
    }
    public function getuserinfo(){
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $id=$user->id;
        $nick=input('param.nick');
        if(!$nick){
            return $this->message('请填写用户昵称', [], 0);
        }
        $imgpath=input('param.img');
        if(!$imgpath){
            return $this->message('请完善用户头像', [], 0);
        }
        $errCode = 0;//$this->decryptData($session_key,$encryptedData, $iv, $data );
        //var_dump($data);
        if ($errCode == 0) {
            //没有注册过，创建新用户
            if(!$user->nick){
                $user->nick=$nick;
                $user->save();
            }else{
                $nick=$user->nick;
            }
            if(!$user->headimg){
                $user->headimg=$imgpath;
                $user->save();
            }else{
                $imgpath=$user->headimg;
            }
            $token = $this->saveToken($user);   //生成token
            $uid=$user->id;
            $img=getFullImageUrl($imgpath);
            $userlist=array('name'=>$nick,'id'=>$uid,'headimg'=>$img);
            return $this->message('注册成功', ['token' => $token,'userlist'=>$userlist]);
            //print($data . "\n");
        } else {
            return $this->message('解密错误', [], 0);
        }
    }
    public function getphone(){
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $id=$user->id;
        $session_key=$user->userLogin->session_key;
        $encryptedData=input('param.encryptedData');
        $iv=input('param.iv');
        $errCode = $this->decryptData($session_key,$encryptedData, $iv, $data );
        if ($errCode == 0) {
            $data=json_decode($data,true);
            //没有注册过，创建新用户
            $phone=$data['phoneNumber'];
            if(!$phone){
                return $this->message('手机号不能为空', [], 0);
            }
            $u=GeneralUserLogin::where('uid',$id)->find();
            $u->mobile=$phone;
            $u->save();
            return $this->message('操作成功', []);
            //print($data . "\n");
        } else {
            return $this->message('解密错误', [], 0);
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

        //修改登录时间
        $uu=GeneralUserLogin::where('uid',$user->id)->find();
        $uu->rece_login_time=gettime();
        $uu->save();

        return $token;
    }

}