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
 * Time: 16:26
 */

namespace app;
use think\App;
use think\Validate;
use think\Request;
use app\handler\AuthHandler;
use app\handler\BrowseHandler;
use think\exception\ValidateException;

class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;
    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];
    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }
    // 初始化
    protected function initialize()
    {}
    /**
     * 请求
     * @var \think\message
     */
    public function message($msg = '请求成功', $data = array(), $code = 200)
    {
        return json([
            'message' => $msg,
            'data' => $data,
            'code' =>$code
        ]);
    }
    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                //[$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }
    /**
     * 获取当前登录用户
     *
     * @return bool|mixed
     */
    public function user()
    {
        $user=AuthHandler::user($this->request->param('token'));
        if($user){
            $uid=$user->id;
            BrowseHandler::add($uid);
        }
        return $user;
    }
    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *  *    <li>-41001: encodingAesKey 非法</li>
     *    <li>-41003: aes 解密失败</li>
     *    <li>-41004: 解密后得到的buffer非法</li>
     *    <li>-41005: base64加密失败</li>
     *    <li>-41016: base64解密失败</li>
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData( $sessionKey,$encryptedData, $iv, &$data )
    {

        if (strlen($sessionKey) != 24) {
            return '-41001';
        }
        $aesKey=base64_decode($sessionKey);
        if (strlen($iv) != 24) {
            return '-41002';
        }
        $aesIV=base64_decode($iv);

        $aesCipher=base64_decode($encryptedData);

        $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        //var_dump($result);exit;
        $dataObj=json_decode( $result );
        if( $dataObj  == NULL )
        {
            return '-41003';
        }
//        $appid = config('-wxsite.wx_xcx_appid');
//        if( $dataObj->watermark->appid != $appid )
//        {
//            return '-41003';
//        }
        $data = $result;
        return '0';
    }
}