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
 * Date: 2022/6/5
 * Time: 13:51
 */
declare (strict_types = 1);

namespace app\listener\api;
use app\models\GeneralYuyueEmployees;
use app\models\GeneralYuyueList;
use think\facade\Db;
use app\handler\YuyueListmsgHandler;

class SendYuyueMessageListener
{
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle($event)
    {
        $this->sendMessage($event['data'],$event['type']);
    }

    /**
     * 给用户发送消息
     *
     * @param $user
     * @param $title
     * @param $text
     */
    public function sendMessage($data,$type)
    {
        if($type==1){ //下单
            $uid=$data['uid'];
            $title=$data['title'];
            $reid=$data['id'];
            //通知模板消息  公众号
            $succ=config('-remindsite.remind_gzh_paysuccess');
            if($succ){
                $y_time=date('Y-m-d H:i:s');
                $openid=Db::name('general_user_list')->where('id',$uid)->value('openid');
                if($openid){
                    $wechat = new \app\common\WeChat();
                    $tmparr=array(
                        "first" => [
                            "value"=> '恭喜您预约成功'
                        ],
                        "keyword1" => [
                            "value"=> $title
                        ],
                        "keyword2" => [
                            "value"=> '预约成功'
                        ],
                        "keyword3" => [
                            "value"=> $y_time
                        ],
                        "remark" => [
                            "value"=> '请您准时到场馆，不要错过时间！'
                        ]
                    );

                    $wechat->sendh5Message($openid,$succ,$tmparr,$reid);
                }
            }
        }else if($type==2){ //取消订单
            $title=$data['title'];
            $y_data=$data['y_data'];
            $uid=$data['uid'];
            $id=$data['id'];
            $tit1=$data['tit1'];
            $tit2=$data['tit2'];
            //通知模板消息  公众号
            $succ=config('-remindsite.remind_gzh_tmpquxiao');
            if($succ){
                $openid=Db::name('general_user_list')->where('id',$uid)->value('openid');
                if($openid){
                    $wechat = new \app\common\WeChat();
                    $tmparr=array(
                        "first" => [
                            "value"=> $tit1?$tit1:'订单自动取消'
                        ],
                        "keyword1" => [
                            "value"=> $title
                        ],
                        "keyword2" => [
                            "value"=> $y_data
                        ],
                        "keyword3" => [
                            "value"=> $tit2?$tit2:'超时未付款'
                        ],
                        "remark" => [
                            "value"=> '您的订单已取消'
                        ]
                    );
                    $wechat->sendh5Message($openid,$succ,$tmparr,$id);
                }
            }
        }else if($type==3){
            $uid=$data['uid'];
            $id=$data['id'];
            $money=$data['money'];
            $tit=$data['tit'];
            //通知模板消息  公众号
            $succ=config('-remindsite.remind_gzh_tmptuikuan');
            if($succ){
                $openid=Db::name('general_user_list')->where('id',$uid)->value('openid');
                if($openid){
                    $wechat = new \app\common\WeChat();
                    $tmparr=array(
                        "first" => [
                            "value"=> '订单退款提醒'
                        ],
                        "keyword1" => [
                            "value"=> $id
                        ],
                        "keyword2" => [
                            "value"=> $money
                        ],
                        "remark" => [
                            "value"=> '您的订单已退款成功-无退款金额'
                        ]
                    );
                    $wechat->sendh5Message($openid,$succ,$tmparr,$id);
                }
            }
        }else if($type==4){
            $uid=$data['uid'];
            $title=$data['title'];
            $yt=$data['yt'];
            $address=$data['address'];
            $mobile=$data['mobile'];
            $id=$data['id'];
            $hour=$data['hour'];
            $tid=config('-remindsite.remind_gzh_starttimeid');
            if($tid){
                $openid=Db::name('general_user_list')->where('id',$uid)->value('openid');
                if($openid){
                    $wechat = new \app\common\WeChat();
                    $tmparr=array(
                        "first" => [
                            "value"=> $title.'提醒您：您的预约订单即将开始'
                        ],
                        "keyword1" => [
                            "value"=> $yt
                        ],
                        "keyword2" => [
                            "value"=> $address
                        ],
                        "keyword3" => [
                            "value"=> $mobile
                        ],
                        "remark" => [
                            "value"=> '您的预约订单将于'.$hour.'小时候开始，请准时前往。'
                        ]
                    );
                    $wechat->sendh5Message($openid,$tid,$tmparr,$id);
                }
                Db::name('general_yuyue_order')->where('id',$id)->update(['tz'=>1]);
                YuyueListmsgHandler::add('到期提醒-订单即将开始',$id,2,'到期提醒');
            }
        }else
        if($type==5){
            $listid=$data['listid'];
            //通知模板消息  公众号
            $succ=config('-remindsite.remind_gzh_neworder');
            if($succ){
                $title=GeneralYuyueList::where('id',$listid)->value('title');
                if($title){
                    $msg='用户预约-'.$title;
                }else{
                    $msg='用户预约';
                }
                //查询满足条件的核销员
                $res=GeneralYuyueEmployees::where('status',1)->select()->toArray();
                if($res){
                    for($i=0;$i<count($res);$i++){
                        $uid=$res[$i]['uid'];
                        $openid=Db::name('general_user_list')->where('id',$uid)->value('openid');
                        if($openid){
                            $wechat = new \app\common\WeChat();
                            $tmparr=array(
                                "first" => [
                                    "value"=> '有新的预约订单'
                                ],
                                "keyword1" => [
                                    "value"=> $msg
                                ],
                                "keyword2" => [
                                    "value"=> gettime()
                                ],
                                "remark" => [
                                    "value"=> '请注意安排！'
                                ]
                            );
                            $url=config('-appsite.app_domainname')."#/pages/index/index";
                            $wechat->sendh5Message($openid,$succ,$tmparr,$listid,$url);
                        }
                    }
                }
            }
        }
    }
}