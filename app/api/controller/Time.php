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
 * Date: 2022/6/28
 * Time: 21:26
 */

namespace app\api\controller;
use app\BaseController;
use think\Request;
use think\facade\Db;
use app\models\GeneralYuyuePersonnel;
use app\models\GeneralYuyueList;

class Time extends BaseController
{
    public function getperson(){
        //查询这个id的所有人员
        $id=input("param.id/d");
        if(!$id){
            return $this->message('id不能为空', [],0);
        }
        $riqi=strip_tags(trim(input("param.riqi")));
        $wek=date("w",strtotime($riqi));
        if($wek==0){
            $wek=7;
        }
        $total=0;
        //先查询当前日期是否有  为了兼容  做的比较复杂  这里查询的是多条数据
        $arr=Db::name('general_yuyue_time')->where('pid',2)->where('list_id',$id)
            ->where('p_val','like','%'.$riqi.'%')->where('status',1)->order('paiid desc,id desc')
            ->select()->toArray();
        if(!$arr){
            //如果没有单独设置日期  那则根据星期来查询
            $arr=Db::name('general_yuyue_time')->where('pid',1)->where('list_id',$id)
                ->where('p_val','like','%'.$wek.'%')->where('status',1)->order('paiid desc,id desc')
                ->select()->toArray();
        }
        $nowtime=time();
        if($arr){
            for($y=0;$y<count($arr);$y++){
                $arrx=explode('|',$arr[$y]['t_val']);
                //遍历时间
                if($arrx){
                    for($s=0;$s<count($arrx);$s++){
                        $time=$arrx[$s];
                        $new=explode("-",$time);
                        if(count($new)>1){
                            $newt=$new[0];
                        }else{
                            $newt=$time;
                        }
                        $xzt=strtotime(date($riqi.' '.$newt));
                        if($nowtime>=$xzt){

                        }else{
                            $total++;
                        }
                    }
                }
            }
        }

        $res=GeneralYuyueList::find($id);
        $money=$res->money;
        //查询今日 可预约总数
        $list=GeneralYuyuePersonnel::where('list_id',$id)->select()->toArray();
        if($list){
            for($i=0;$i<count($list);$i++){
                $pid=$list[$i]['id'];
                $list[$i]['money']=$money;
                $list[$i]['img']=getFullImageUrl($list[$i]['img']);
                $list[$i]['total']=$total;
                //已经预约数量 use_no
                $uno=Db::name('general_yuyue_order')->where('list_id',$id)
                    ->where('y_data',$riqi)->where('personid',$pid)->where('status','<',4)->count();
                $list[$i]['use_no']=$uno;
            }
        }
        $data = [
            'list' => $list
        ];
        return $this->message('请求成功', $data);
    }
    //查询时间和是否可以预约
    public function getytimelist(Request $request){
        $params = $request->post();
        $pid=input("param.pid/d");
        if(!$params['id'] || !$pid){
            return $this->message('请求错误', [], 0);
        }
        if(!$params['riqi']){
            return $this->message('请求时间错误', [], 0);
        }
        $list_id=intval($params['id']);
        $riqi=$params['riqi'];
        $wek=date("w",strtotime($riqi));
        if($wek==0){
            $wek=7;
        }
        //先查询当前日期是否有  为了兼容  做的比较复杂  这里查询的是多条数据
        $res=Db::name('general_yuyue_time')->where('pid',2)->where('list_id',$list_id)
            ->where('p_val','like','%'.$riqi.'%')->where('status',1)->order('paiid desc,id desc')->select()->toArray();
        if(!$res){
            //如果没有单独设置日期  那则根据星期来查询
            $res=Db::name('general_yuyue_time')->where('pid',1)->where('list_id',$list_id)
                ->where('p_val','like','%'.$wek.'%')->where('status',1)->order('paiid desc,id desc')->select()->toArray();
        }
        $nowtime=time();
        //查询时间
        if($res){
            $no=0;
            for($i=0;$i<count($res);$i++){
                $t_val=$res[$i]['t_val'];//时间的值
                $tval=explode("|",$t_val);
                $number=$res[$i]['number'];//可预约人数
                $closed=$res[$i]['closed'];//2关闭  1开放
                //遍历时间
                $yval=[];
                if($tval){

                    for($y=0;$y<count($tval);$y++){
                        $yval[$y]['time']=$tval[$y];
                        $no++;
                        if($closed==2){ //如果闭店
                            $yval[$y]['title']='闭店';
                            $yval[$y]['status']=2;
                            $yval[$y]['sno']=0;
                            $yval[$y]['no']=$no;
                            continue;
                        }else{ //不闭店
                            $yval[$y]['title']=$tval[$y];
                            //查询预约当前时间的数量
                            $uno=Db::name('general_yuyue_order')->where('list_id',$list_id)->where('y_data',$riqi)
                                ->where('y_time',$tval[$y])->where('personid',$pid)
                                ->where('status','<',4)->count();
                            if($uno>=$number){
                                $yval[$y]['time']=$tval[$y];
                                $yval[$y]['title']='约满';
                                $yval[$y]['status']=2;
                                $yval[$y]['sno']=0;
                                $yval[$y]['no']=$no;
                            }else{
                                $new=explode("-",$tval[$y]);
                                if(count($new)>1){
                                    $isguo=config('-venuessite.yue_guoqitime')?:0;
                                    if($isguo==1){
                                        $xzt=strtotime(date($riqi.' '.$new[0]));
                                    }
                                    else{
                                        $xzt=strtotime(date($riqi.' '.$new[1]));
                                    }
                                }else{
                                    $xzt=date($riqi.' '.$tval[$y]);
                                }
                                if($nowtime>=$xzt){
                                    $yval[$y]['time']=$tval[$y];
                                    $yval[$y]['title']='过期';
                                    $yval[$y]['status']=3;
                                    $yval[$y]['sno']=0;
                                    $yval[$y]['no']=$no;
                                    continue;
                                }
                                $yval[$y]['time']=$tval[$y];
                                $yval[$y]['status']=1;
                                $yval[$y]['sno']=$number-$uno;
                                $yval[$y]['no']=$no;
                                $yval[$y]['title']='余:'.$yval[$y]['sno'];
                            }
                        }
                    }
                }
                $res[$i]['list']=$yval;
            }
        }
        return $this->message('请求成功', $res);
    }
}