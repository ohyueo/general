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
 * Date: 2022/8/30
 * Time: 15:48
 */

namespace app\admin\controller;
use think\facade\View;
use think\facade\Db;
use app\admin\traits\AdminAuth;
use think\facade\Log;
use think\facade\Cache;

class Diy extends Common
{
    use AdminAuth;
    public function home(){
        $permis = $this->getPermissions('Diy/home');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){

        }
        //模板样式  template
        $template=Db::name('general_system_diy')->where('name','template')->value('val')?:1;
        View::assign('template', $template);
        //主题颜色
        $zhuticolor=Db::name('general_system_diy')->where('name','zhuticolor')->find();
        View::assign('zhuticolor', $zhuticolor);
        //查询是否显示轮播图
        $lunbo=Db::name('general_system_diy')->where('name','lunbo')->value('val')?:0;
        View::assign('lunbo', $lunbo);
        //启用公告
        $gonggao=Db::name('general_system_diy')->where('name','gonggao')->value('val')?:0;
        View::assign('gonggao', $gonggao);
        //启用小方块
        $fangkuai=Db::name('general_system_diy')->where('name','fangkuai')->value('val')?:0;
        View::assign('fangkuai', $fangkuai);
        //新闻
        $news=Db::name('general_system_diy')->where('name','news')->value('val')?:0;
        View::assign('news', $news);

        //挂号预约
        $activity=Db::name('general_system_diy')->where('name','yuyue')->value('val')?:0;
        View::assign('yuyue', $activity);
        //商城
        $shop=Db::name('general_system_diy')->where('name','shop')->value('val')?:0;
        View::assign('shop', $shop);
        //问诊
//        $visits=Db::name('yuyue_system_diy')->where('name','visits')->value('val')?:0;
//        View::assign('visits', $visits);

        //支付记录
        $paymsg=Db::name('general_system_diy')->where('name','paymsg')->value('val')?:0;
        View::assign('paymsg', $paymsg);
        //门票
        $opentickets=Db::name('general_system_diy')->where('name','opentickets')->value('val')?:0;
        View::assign('opentickets', $opentickets);
        //会员卡
        $vip=Db::name('general_system_diy')->where('name','vip')->value('val')?:0;
        View::assign('vip', $vip);
        return View::fetch();
    }
    public function sethome(){
        $data=array('status' => 0,'msg' => '未知错误');
        $st=input('param.st');//类型
        $va=input('param.va');
        if($st){
            $res=Db::name('general_system_diy')->where('name',$st)->find();
            if($res){
                Db::name('general_system_diy')->where('id',$res['id'])->update(['val'=>$va]);
            }else{
                Db::name('general_system_diy')->insert(['name'=>$st,'val'=>$va]);
            }
            if($st=='template' && $res['val']!=$va){ //如果修改了值
                if($va==1){
                    Db::name('general_system_diy')->where('name','zhuticolor')->update(['val'=>'#19be6b']);
                }else if($va==2){
                    Db::name('general_system_diy')->where('name','zhuticolor')->update(['val'=>'#99783c']);
                }
            }
            Cache::set($st, $va);
            $data['msg'] = '设置成功';
            $data['status'] = 1;
        }
        return json($data);exit;
    }
}