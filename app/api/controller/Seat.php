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
 * Date: 2022/6/7
 * Time: 18:12
 */

namespace app\api\controller;
use app\BaseController;
use think\facade\Db;
use think\Request;

class Seat extends BaseController
{
    public function seatlist($xid=0){
        $id=input("param.id");
        $user = $this->user();
        if(!$user){
            //如果后端登录了  这里可以忽略鉴权
            $username = session('admin_name');
            if(!$username){
                return $this->message('请登录', [],201);
            }
            $id=$xid;
        }
        
        if(!$id){
            return $this->message('参数错误', [],0);
        }
        //查询座位数据
        $list=Db::name('general_yuyue_seat')->where('status',1)->where('list_id',$id)->order('id desc')->find();
        if(!$list){
            return $this->message('没有数据', ['syno'=>0],0);
        }
        $row=$list['row'];//行
        $column=$list['column'];//列
        $title=$list['title'];
        $titarr=[];
        if($title){
            $titarr=explode('|',$title);
        }

        $y_data=input("param.y_data");
        $y_time=input("param.y_time");
 
        //剩余座位数量 
        $syno=0;

        $arr=[];
        $x=0;
        for($i=0;$i<$column;$i++){ //循环列表
            $xrr=[];
            for($y=0;$y<$row;$y++){ //循环行
                $biao=$y.'_'.$i;
                $xrr[$y]['biao']=$biao;
                $xrr[$y]['id']=$list['id'];
                $isbi=Db::name('general_yuyue_seat')->where('closed','like','%'.$biao.'|%')->where('id',$list['id'])->count();
                if($isbi){
                    $xrr[$y]['status']=3; //1可预约 2不可预约  3关闭
                }
                else{
                    $x++;
                    $xrr[$y]['title']=$x.'号';//这里是默认的座位名称
                    if($titarr && $titarr[$y]){
                        $xrr[$y]['title']=$titarr[$x-1]; //后台自定义座位名称
                    }
                    //查询是否被人预约了
                    if($y_data && $y_time){
                        $isse=Db::name('general_yuyue_seatmsg')
                            ->where('seatid',$list['id'])
                            ->where('list_id',$id)
                            ->where('biao',$biao)
                            ->where('status',1)
                            ->where('y_data',$y_data)
                            ->where('y_time',$y_time)->count();
                    }else{
                        $isse=Db::name('general_yuyue_seatmsg')
                            ->where('seatid',$list['id'])
                            ->where('list_id',$id)
                            ->where('status',1)
                            ->where('biao',$biao)->count();
                    }
                    if($isse){
                        $xrr[$y]['title']='约满';
                        $xrr[$y]['status']=2; //1可预约 2不可预约  3关闭
                    }else{
                        $xrr[$y]['status']=1; //1可预约 2不可预约  3关闭
                        $syno++;
                    }
                }
            }
            $arr[$i]['list']=$xrr;
        }
        $data=[];
        $data['list']=$arr;
        $data['syno']=$syno;
        return $this->message('请求成功', $data);
    }
}