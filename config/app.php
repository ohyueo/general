<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用地址
    'app_host'         => env('app.host', ''),
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 默认应用
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',

    // 应用映射（自动多应用模式有效）
    'app_map'          => [
        //'web'     => 'index',
        'resource'  => 'api',  // 接口应用
        //'ohyueo'    => 'admin',  // 后台应用
    ],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => [],

    // 异常页面的模板文件
    'exception_tmpl'   => app()->getThinkPath() . 'tpl/think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => false,
    'alipay' => [
        'app_id' => '2021001144619611',
        'notify_url' => 'http://yansongda.cn/resource/Pay/notify',
        'return_url' => 'http://yansongda.cn/index/index/pay',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAgq3RNa388+FiEGb14Vhd571kGYulAowfXNNHC7Zm6mY+fzALYvTyV18B2LF/o2aqYT49Ie6cKIPRpAhqWFJRIA06w7TOPfw5ySlONpf1UBRsu2pHEeWZdacR3g9LqXwAebrJQiSN6YzSk8UMEg7RCch/B+dIWS4MMX1+5T7ibtaVqudkxX3vWzIbgW20QMsm/FTXnMLVJpzbkohHF7FvHgyCxnkKTa3IUjJkrfbpn+cnbHG1jKdy3w3Q3+d6xSs56HmCascOqbJK666VtSOHgWHYXbps21X4UWLq7w/6cL24sNE1UMeYEGFBh/EoP5O02i1qoAbsA/4EDrX8by8UVwIDAQAB',
        // 加密方式： **RSA2**
        'private_key' => 'MIIEowIBAAKCAQEAsiL8IIBDWURm4mZ4ZnXZwlxXr9mAhZ4zU4sufolFVSERoAW/q5CwFFYGSLhOFTINBEpPytwFGETndqtn7W1f8Isf6VfeqtlOCpxuat6BuXj1STfEohKkcOIXWs6vlssgtUe7evVR0r7c7vECtEmayLn/pnQGKbKVKECmYTwjrG4QTSC+f+bRv54uP02gCcyYWRoE3UtHNYLJ0FtClvZ1FAttB1lIJaTudJ+lpGAYWl/ORdKf9oGz3/MmIWOLUL4R01AFqLz5/YHFDE0jPK9tw594h0doCHLxSPnN9RFEq1IsS/u71EqQucLt63UQ3JysleTlrx9Desp6x47Lbfl4oQIDAQABAoIBAGVMZ7vDtIjK0Qbl5om9cruH74VcVi2lrOJRP3tNbFPgnjk1i61wdfDainBH/n8nGyfZak6dl3gZltZw2oS1sd0EAH2dLk5RjIOUOWYkafiixDmCsRrJJyHaMBW/ezJxCISN6hsd7g29470s6LDFOnPy8WIikG6d0xw0x2h73n1qkwFEETmvFd5X9xK/6icHUXeia6M0gyog/LVddZbFd13gwSbYZyLMJZ6d5yUgXvQQYq/5YQ/90qmReEjss4Oiip8lQPR6qpfIvK8qshPJ2xH42iRtjCTROoMuX1SjLCspew+UvPiU5Po0drSMEQVEc5Q0gYo9US/J3FcRlL2lAZECgYEA/WvMAsvsEQp0KWP+p44/fi0ICuF8qaiVQ0QMRP2lCKKiPBI9nV/X1Cvt53GSqI2uZG5gs8lX61Hqouh94Tp3UhCppIoorPVU53KuU4igVaUFh+HEKSqS+yfUntqKZ+elbkjzT5xUfRh2oF5Km1G5cK9Trdvf2bbtHPmf5gAjQpsCgYEAs/MPT2DHLqHHzsOTWpTf4tBamjBXZ8m7jbQNaTkvCR6RZXDMG8olvU+7SvDHh1gWLc14dHOC2DF2p112N1CRVSq0clzmIfyVr5PMsFXelApaAEWfDEsc06vCRzwkGxC8R1xAQGJfVDvfL9ebyiJNJpMCmiw5l4+jXMEBhtUN93MCgYEA32Ql65UtHHx1l4LxGWzYZXZ2r3jDtp7ILQqQMkTRNQKANqnxrIevLvYmei3wlSqHvzpZfkKPgP2t8zs0s9UhF7Neh4/OeWCuHnRGXZaDl9unO3f1vkAsXr9muz10YlGdz9D4woXRKJDnCUj1RWNOL5ouDoVTF40cCb22iT431t8CgYAHJmU5VjMT20HBKWw1cSzkKjsvXTRb31wgaUMPJ0KDLj/AtBaoFAQj3YMnmyGScyGOeFeM6PoN9Qkt5TzJPd5IcAXzdVV/jDW2YD0F1KOfZjnlQN7s8h8XGgACLJWSrbuNalDzPfVjt21KzVpXUb2XBshXr+Ip738iNFkHLnf7mQKBgERf1nHXrfF5OZ4Uqp2AG5FE07V//zAqZ1+dwnEp5YOYhkqM5Npkv/3sRF0m32Mgxsk+L7oGYfpREojTfnqMnMG6w6ckA6x1AhVo2i/wEAFcKzz2ub8u0hKg+sxvmuCp88AY+iUQdAabHtpGLVt7Hrk0rWJY27urclvd8VT2DNPH',
        'log' => [ // optional
            'file' => './logs/alipay.log',
            'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
        //'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
    ]
];
