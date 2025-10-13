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
 * Date: 2021/7/23
 * Time: 15:37
 */

namespace app\api\controller;
use app\models\GeneralYuyueTexter;
use app\models\GeneralZongheImg;
use app\models\GeneralWenWenclass;
use app\BaseController;
use think\facade\Db;

class Texter extends BaseController
{
    public function texterlist(){
        $page=input("param.page/d");
        if(!$page){
            $page=1;
        }
        $order='id desc';
        $type=input("param.type/d");
        if($type==1){
            $order="pv desc,id desc";
        }else if($type==2){
            $order="addtime desc,id desc";
        }
        $where=[];
        $title='新闻动态';
        $classid=input('param.classid/d');
        if($classid){
            $where['classid']=$classid;
            $title=GeneralWenWenclass::where('id',$classid)->value('title');
        }
        //查询模板
        $template=Db::name('general_system_diy')->where('name','template')->value('val')?:1;
        if($template==1){
            $count=GeneralYuyueTexter::where('status',1)->where($where)->count();
            $list=GeneralYuyueTexter::where('status',1)->where($where)->page($page,10)->field(['id','title','pv','addtime','img','texter'])->order($order)->select();
            if($list){
                for($i=0;$i<count($list);$i++){
                    $list[$i]['img']=getFullImageUrl($list[$i]['img']);
                    $list[$i]['addtime']=date('Y年m月d日',strtotime($list[$i]['addtime']));
                    //从数据库获取富⽂本string
                    $string = $list[$i]["texter"];
                    $html_string = htmlspecialchars_decode($string);
                    $content = str_replace(" ", "", $html_string);
                    $contents = strip_tags($content);
                    $textx = mb_substr($contents, 0, 20, "utf-8");
                    $list[$i]["texter"]=$textx;
                }
            }
        }else if($template==2){
            $count=GeneralYuyueTexter::where('status',1)->where($where)->count();
            $list=GeneralYuyueTexter::where('status',1)->where($where)->page($page,10)->field(['id','title','pv','addtime','img','texter','openurl'])->order($order)->select();
            if($list){
                for($i=0;$i<count($list);$i++){
                    $list[$i]['img']=getFullImageUrl($list[$i]['img']);
                    $list[$i]['addtime']=date('Y年m月d日',strtotime($list[$i]['addtime']));
                    //从数据库获取富⽂本string
                    $string = $list[$i]["texter"];
                    $html_string = htmlspecialchars_decode($string);
                    $content = str_replace(" ", "", $html_string);
                    $contents = strip_tags($content);
                    $textx = mb_substr($contents, 0, 20, "utf-8");
                    $list[$i]["texter"]=$textx;
                    if($list[$i]['openurl']){
                        $list[$i]['style']=2;
                        $list[$i]['url']=$list[$i]['openurl'];
                    }else{
                        $list[$i]['style']=4;
                        $list[$i]['url']='./texter_info?id='.$list[$i]['id'];
                    }
                }
            }
        }
        $arr['list']=$list;
        $arr['count'] = $count;
        $arr['title'] = $title;
        //查询轮播
        $lun=GeneralZongheImg::where('type',3)->select();
        $arr['lun'] = $lun;
        return $this->message('请求成功', $arr);
    }
    public function textinfo(){
        $data=[];
        $id=input("param.id/d");
        if($id){
            $data=GeneralYuyueTexter::where('id',$id)->field(['id','title','pv','addtime','img','texter'])->find();
        }
        GeneralYuyueTexter::where('id', $id)->inc('pv', 1)->update();
        return $this->message('请求成功', $data);
    }
}