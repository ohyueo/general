<?php
declare (strict_types = 1);

namespace app\service;

use Overtrue\EasySms\EasySms;

class EasysmsService extends \think\Service
{
    /**
     * 注册服务
     *
     * @return mixed
     */
    public function register()
    {
        $this->app->bind('easysms', function () {
            return new EasySms(config('easysms'));
        });
    }

    /**
     * 执行服务
     *
     * @return mixed
     */
    public function boot()
    {
        //
    }
}
