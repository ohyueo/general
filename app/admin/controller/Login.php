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
 * Date: 2021/5/17
 * Time: 10:34
 */

namespace app\admin\controller;
use think\facade\View;
use think\facade\Db;
use think\captcha\facade\Captcha;

class Login
{
    public function index(){
        //查询今日该ip登录错误次数
        $yan=0;
        $IP=request()->ip();
        $log=Db::name('general_admin_login')->where('status',2)->where('loginip',$IP)->whereDay('logintime')->count();
        if($log && $log>2){
            $yan=1;
        }
        View::assign('yan',$yan);
        return View::fetch();
    }

    protected function getpass($pass){
        return md5('*&>'.md5($pass) );
    }

    public function login()
    {
        $data=array('status' => 0,'msg' => '未知错误');
        $username=input("param.username");
        $password=input("param.password");
        $vercode=input("param.vercode");
        if(!$username || !$password){
            $data['msg'] = "参数错误!";
            return json($data);exit;
        }
        //ip
        $IP=request()->ip();
        //今日
        $log=Db::name('general_admin_login')->where('status',2)->where('loginip',$IP)->whereDay('logintime')->count();
        if($log && $log>2){
            if(!$vercode){
                $data['msg'] = "请输入验证码";
                return json($data);exit;
            }
            //验证码
            // 检测输入的验证码是否正确，$value为用户输入的验证码字符串
            if(!captcha_check($vercode))
            {
                // 验证失败
                $data['msg'] = "验证码错误".$vercode;
                return json($data);exit;
            }
        }
        //判断该ip地址今天登录的次数不能大于3
        //今日
        $log=Db::name('general_admin_login')->where('status',2)->where('loginip',$IP)->whereDay('logintime')->count();
        if($log && $log>2){
            //写入登录记录
            Db::name('general_admin_login')->insert(array(
                'username'  => $username,
                'logintime' => time(),
                'loginip'	=> $IP,
                'status' => 2,
                'info' => '登录次数超过限制,拒绝登录！'
            ));
            //超过三次了
            $data['msg'] = "登录次数过多,请稍后再试";
            $this->writeActionLog($username,"登录次数过多,请稍后再试");
            return json($data);exit;
        }
        $scno=2-$log;
        $password = $this->getpass($password);
        $tmp = Db::name('general_admin')->where(array('username' => $username,'password' => $password))
            ->find();
        if($tmp){
            if($tmp['status']){

                $city='';
                $map = new \app\common\Map();
                $res = $map->getmapip('110.184.30.102');
                if($res && $res['status']==0){
                    $city=$res['result']['ad_info']['city'];
                }

                $token=lgaddtoken($username);
                //写入登录记录
                $add=Db::name('general_admin_login')->insert(array(
                    'username'  => $username,
                    'logintime' => time(),
                    'loginip'	=> $IP,
                    'status' => 1,
                    'info' => '登录成功',
                    'token' => $token,
                    'city' => $city
                ));
                if($add){
                    session('admin_name',$username,'think');
                    session('admin_token',$token,'think');
                    
                    $this->writeActionLog($username,"登录成功");
                    
                    $data['status']=1;
                }else{
                    $data['msg'] = "存入失败";
                    return json($data);exit;
                }
            }else{
                $data['msg'] = "该账户已被禁用";
                $this->writeActionLog($username,"该账户已被禁用");
                return json($data);exit;
            }
        }else{
            //写入登录记录
            Db::name('general_admin_login')->insert(array(
                'username'  => $username,
                'logintime' => time(),
                'loginip'	=> $IP,
                'status' => 2,
                'info' => '用户名或密码错误'
            ));
            $data['msg'] = "用户名或密码错误,剩余次数".$scno;
            $this->writeActionLog($username,"用户名或密码错误,剩余次数".$scno);
            return json($data);exit;
        }
        //判断是否登录
        return json($data);
    }

    public function verify()
    {
        ob_clean();
        return Captcha::create();
    }

    /**
     * 清除缓存
     */
    public function clear() {
        $CACHE_PATH = config('cache.runtime_path').'/cache/';
        $TEMP_PATH = config('cache.runtime_path').'/temp/';
        //$LOG_PATH = config('cache.runtime_path').'/log/';
        if (delete_dir_file($CACHE_PATH) && delete_dir_file($TEMP_PATH)) {
            $data = array('status' => 1,'msg' => '清除缓存成功');
        } else {
            $data = array('status' => 0,'msg' => '清除缓存失败');
        }
        return json($data);
    }
    
    /**
     * 纪录用户操作
     */
    public function writeActionLog($user,$text)
    {
        $IP=request()->ip();
        Db::name('general_admin_action_log')->insert([
            'user' => $user,
            'text' => $text,
            'addtime' => time(),
            'ip' => $IP
        ]);
    }

    public function svn()
    {
        $version=Db::query('SELECT VERSION() AS ver');
        $ip=gethostbyname($_SERVER['SERVER_NAME']);
        $info=array(
            '操作系统'=>PHP_OS,
            'PHP版本'=>PHP_VERSION,
            'MYSQL版本'=>$version[0]['ver'],
            '运行环境'=>$_SERVER['SERVER_SOFTWARE'],
            'PHP运行方式'=>php_sapi_name(),
            '上传附件限制'=>ini_get('upload_max_filesize'),
            '执行时间限制'=>ini_get('max_execution_time'),
            '服务器时间'=>date('Y年n月j日 H:i:s'),
            '服务器域名/IP'=>$_SERVER['SERVER_NAME'].'/'.$ip,
            '剩余空间'=>round((disk_free_space(".")/(1024*1024)),2).'M',
            '版权信息'=>'© ohyu.cn 海之心制作'
        );
        View::assign('info',$info);
        return View::fetch('system/get');
    }
}