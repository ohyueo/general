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
 * Time: 18:14
 */

namespace app\api\controller;

use app\BaseController;
use think\Request;
use app\models\GeneralNotiList;
use app\models\GeneralZongheImg;
use app\models\GeneralYuyueList;
use app\models\GeneralYuyueTime;
use think\facade\Db;
use app\models\GeneralWebText;
use app\models\GeneralYuyueSeat;
use app\models\GeneralYuyueTexter;
use app\models\GeneralYuyuePersonnel;
use app\models\GeneralWenWenclass;


class Index extends BaseController
{
    public function imglist(Request $request){
        $params = $request->get();
        $where=array();
        if(isset($params['type'])){
            $where=array('type'=>$params['type']);
        }
        //查询图片
        $list=GeneralZongheImg::where($where)->order('paiid desc,id desc')->select()->toArray();
        $x=$y=$z=$s=0;
        $arr=[];
        foreach ($list as $step) {
            if($step['type']==1){
                $arr['lunbo'][$x]=$step;
                $x++;
            }
            if($step['type']==2){
                $arr['kuai'][$y]=$step;
                $y++;
            }
            if($step['type']==6){
                $arr['guang'][$z]=$step;
                $z++;
            }
            if($step['type']==4){
                $arr['topkuai'][$s]=$step;
                $s++;
            }
        }
        $list=$notilist=$textlist=[];
        $bantit1='';
        $bantit2='';
        //查询模板
        $template=Db::name('general_system_diy')->where('name','template')->value('val')?:1;
        if($template==1){
            //查询公告
            $notilist=GeneralNotiList::field('id,title')->order('id desc')->select()->toArray();
            $list=GeneralYuyueList::where('status',1)
                ->where('istui',1)
                ->order('paiid desc')
                ->field(['id','title','mobile','img','address','lat','lng','is_info','recommended'])
                ->select();
            if($list){
                for($i=0;$i<count($list);$i++){
                    $list[$i]['val']=0;
                    $id=$list[$i]['id'];
                    $is_info=$list[$i]['is_info'];
                    $opst=0;//默认
                    if($is_info==1){
                        $opst=1;//显示详情页
                    }else if($is_info==2){
                        $ist=GeneralYuyueTime::where('list_id',$id)->where('status',1)->count();
                        if($ist){
                            //判断有无人员  有则打开人员和时间
                            $res=GeneralYuyuePersonnel::where('list_id',$id)->where('status',1)->count();
                            if($res){
                                $opst=5;//打开人员
                            }else{
                                $opst=2;//打开时间
                            }
                        }else{
                            //判断有无座位
                            $isz=GeneralYuyueSeat::where('list_id',$id)->where('status',1)->count();
                            if($isz){
                                $opst=4;//打开座位
                            }else{
                                $opst=3;//打开订单
                            }
                        }
                    }
                    $list[$i]['opst']=$opst;
                }
            }
            //推荐新闻
            $textlist=GeneralYuyueTexter::where('status',1)->where('istui',1)->field(['id','title','img','texter','addtime'])->select()->toArray();
            if($textlist){
                for($i=0;$i<count($textlist);$i++){
                    $textlist[$i]['img']=getFullImageUrl($textlist[$i]['img']);
                    $textlist[$i]['addtime']=date('Y年m月d日',strtotime($textlist[$i]['addtime']));
                    //从数据库获取富⽂本string
                    $string = $textlist[$i]["texter"];
                    $html_string = htmlspecialchars_decode($string);
                    $content = str_replace(" ", "", $html_string);
                    $contents = strip_tags($content);
                    $textx = mb_substr($contents, 0, 20, "utf-8");
                    $textlist[$i]["texter"]=$textx;
                }
            }
        }else if($template==2){
            //推荐新闻  模板2的推荐新闻  就是先查询分类在查询新闻
            $textlist=GeneralWenWenclass::where('status',1)->order('paiid desc,id desc')->field(['id','title'])->select()->toArray();
            if($textlist){
                for($i=0;$i<count($textlist);$i++){
                    $textlist[$i]['list']=GeneralYuyueTexter::where('status',1)->where('istui',1)->where('classid',$textlist[$i]['id'])->field(['id','title','img','openurl'])->select()->toArray();
                    if($textlist[$i]['list']){
                        for($j=0;$j<count($textlist[$i]['list']);$j++){
                            $textlist[$i]['list'][$j]['img']=getFullImageUrl($textlist[$i]['list'][$j]['img']);
                            //如果有地址 那么就打开地址
                            if($textlist[$i]['list'][$j]['openurl']){
                                $textlist[$i]['list'][$j]['style']=2;
                                $textlist[$i]['list'][$j]['url']=$textlist[$i]['list'][$j]['openurl'];
                            }else{
                                $textlist[$i]['list'][$j]['style']=4;
                                $textlist[$i]['list'][$j]['url']='../texter/texter_info?id='.$textlist[$i]['list'][$j]['id'];
                            }
                        }
                    }
                }
            }
            $bantit1='今日关闭';
            //查询日期和星期是否设置
            $riqi=date('Y-m-d');
            $id=GeneralYuyueList::where('status',1)->order('paiid desc,id desc')->value('id');
            $wek=date("w",strtotime($riqi));
            if($wek==0){
                $wek=7;
            }
            $total=0;
            //先查询当前日期是否有  为了兼容  做的比较复杂  这里查询的是多条数据
            $arrdayx=Db::name('general_yuyue_time')->where('pid',2)->where('list_id',$id)
                ->where('p_val','like','%'.$riqi.'%')->where('status',1)->order('paiid desc,id desc')
                ->count();
            if(!$arrdayx){
                //如果没有单独设置日期  那则根据星期来查询
                $arrdayx=Db::name('general_yuyue_time')->where('pid',1)->where('list_id',$id)
                    ->where('p_val','like','%'.$wek.'%')->where('status',1)->order('paiid desc,id desc')
                    ->count();
            }
            if($arrdayx){
                $bantit1='今日开放';
            }
            $bantit2=date('m月d日').' 4~18';
            $bantit2=date('m月d日');
        }
        $arr['bantit1']=$bantit1;
        $arr['bantit2']=$bantit2;
        $arr['noti']=$notilist;
        $arr['list']=$list;

        //查询appid 和 客服链接
        $appid=config('-wxsite.wx_kefu_qiyeid');//企业id
        $arr['appid']=$appid;
        $kfurl=config('-wxsite.wx_kefu_url');//客服链接
        $arr['kfurl']=$kfurl;

        //新闻文字
        $newtit=config('-systemsite.sys_indexnewwen')?:'新闻动态';
        $arr['newtit']=$newtit;

        //分享
        $imgurl='';
        if(config('-appsite.app_logoimg')){
            $imgurl=getFullImageUrl(config('-appsite.app_logoimg'));
        }
        $url=config('-appsite.app_domainname');
        $sharedata=array(
            'title' => config('-appsite.app_name'),
            'imgUrl' => $imgurl,
            'desc' => config('-appsite.app_desc'),
            'link' => $url
        );
        $arr['sharedata']=$sharedata;

        //门店数据
        $stores=array(
            'title' => config('-appsite.app_name'),
            'imgUrl' => $imgurl,
            'phone' => config('-appsite.app_phone'),
            'time' => config('-appsite.app_time'),
            'address' => config('-appsite.app_address'),
            'lat' => config('-appsite.app_lat'),
            'lng' => config('-appsite.app_lng')
        );
        $arr['stores']=$stores;

        //推荐新闻
        $arr['textlist']=$textlist;
        return $this->message('请求成功', $arr);
    }
    //公告详情
    public function noti_info(Request $request){
        $params = $request->get();
        if(!$params['id']){
            return $this->message('请求错误', [], 0);
        }
        $data=[];
        if($params['st']==1){
            $data=GeneralNotiList::where('id',$params['id'])->find();
        }else if($params['st']==2){
            $data=GeneralWebText::where('id',$params['id'])->find();
        }
        return $this->message('请求成功', $data);
    }
    //批量测算距离
    public function mapdistance(){
        $lat = strip_tags(trim(input('param.latitude')));
        $lng = strip_tags(trim(input('param.longitude')));
        $from = $lat.','.$lng;
        $to = strip_tags(trim(input('param.from')));
        $map = new \app\common\Map();
        $res = $map->mapdistance($from,$to);
        if($res && $res['status']==0){
            $val=$res['result']['rows'][0]['elements'];
            return $this->message('请求成功', $val);
        }else if($res && $res['status']>0){
            //接口异常 通知管理员
            $message=$res['message'];
            event('LogMessage', ['type'=>'getmapip','msg'=>$message]);
        }
    }

}