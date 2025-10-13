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
 * Date: 2021/5/22
 * Time: 17:56
 */

namespace app\admin\controller;
use app\models\GeneralUserList;
use think\facade\View;
use think\facade\Db;
use app\admin\traits\AdminAuth;
use app\handler\YuyueListmsgHandler;
use PHPExcel_IOFactory;
use PHPExcel;
use app\models\GeneralYuyueYuyuespec;

class Order extends Common
{
    use AdminAuth;
    //订单列表
    public function orderlist(){
        $lid=input("param.lid");
        View::assign('lid', $lid);
        $permis = $this->getPermissions('Order/orderlist');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        //查询
        $list=[];
        View::assign('listid', $lid);
        if($lid){
            $list=Db::name('general_yuyue_form')->where('list_id',$lid)->order('paiid desc,id desc')->select();
        }
        View::assign('list', $list);
        
        if(request()->isPost()){
            $page=input("param.page");
            if(!$page){
                $page=1;
            }

            $where=[];
            $id=input('param.id');
            if($id){
                $where[]=['id','=',$id];
            }
            $acid=input('param.acid');
            if($acid){
                $where[]=['list_id','=',$acid];
            }
            $uid=input('param.uid');
            if($uid){
                $where[]=['uid','=',$uid];
            }
            $status=input('param.status');
            if($status){
                $where[]=['status','=',$status];
            }
            $y_data=input('param.y_data');
            if($y_data){
                $where[]=['y_data','=',$y_data];
            }
            $form_id=input('param.form_id');
            if($form_id){
                $orderids=Db::name('general_yuyue_ord')->where('form_id',$form_id)->column('ord_id');
                $where[]=['id','in',$orderids];
            }
            $val=input('param.val');
            if($val){
                $orderids=Db::name('general_yuyue_ord')->where('val','like','%'.$val.'%')->column('ord_id');
                $where[]=['id','in',$orderids];
            }
            $str=input('param.str');
            $end=input('param.end');
            if($str && $end){
                $where[]=['addtime','between',[$str,$end]];
            }
            $field=input('param.field');
            $pai=input('param.order');
            $limit=input("param.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            //查询产品
            $count = Db::name('general_yuyue_order')->where($where)->count();
            $data=Db::name('general_yuyue_order')->where($where)->page($page,$limit)->order('id desc')->select()->toArray();


        //     $sql="SELECT o.*,GROUP_CONCAT(f.`val`) FROM general_yuyue_order o 
        // LEFT JOIN general_yuyue_ord f ON o.`id` = f.ord_id 
        //  WHERE  o.`status`>0   ";
        //     $id=input('param.id');
        //     if($id){
        //         $sql.=" AND o.id = ".$id;
        //     }
        //     $acid=input('param.acid');
        //     if($acid){
        //         $sql.=" AND o.list_id = ".$acid;
        //     }
        //     $uid=input('param.uid');
        //     if($uid){
        //         $sql.=" AND o.uid = ".$uid;
        //     }
        //     $status=input('param.status');
        //     if($status){
        //         $sql.=" AND o.status = ".$status;
        //     }
        //     $y_data=input('param.y_data');
        //     if($y_data){
        //         $sql.=" AND o.y_data = '".$y_data."'";
        //     }

        //     //查询单个字段
        //     $form_id=input('param.form_id');
        //     if($form_id){
        //         $sql.=" AND f.form_id = ".$form_id;
        //     }
        //     $val=input('param.val');
        //     if($val){
        //         $sql.=" AND f.val like '%".$val."%'";
        //     }
        //     //查询时间
        //     $str=input('param.str');
        //     $end=input('param.end');
        //     if($str && $end){
        //         $sql.=" AND o.addtime between '".$str."' and '".$end."'";
        //     }


        //     $field=input('param.field');
        //     $pai=input('param.order');
        //     if($field){
        //         $sql.=' GROUP BY o.id desc ORDER BY o.'.$field.' '.$pai;
        //     }else{
        //         $sql.="  GROUP BY o.id desc";
        //     }
        //     //echo $sql;exit;
        //     $res=Db::query($sql);
        //     $count=count($res);//数量


        //     $limit=input("param.limit");
        //     if(!$limit){
        //         $limit=10;//每页显示条数
        //     }
        //     $pre = ($page-1)*$limit;//起始页数
        //     $sql.=" limit ".$pre.",".$limit.' ';
        //     $data=Db::query($sql);
            if($data){
                for($i=0;$i<count($data);$i++){
                    $id=$data[$i]['list_id'];//预约id
                    $ord=$data[$i]['id'];
                    $data[$i]['title']=Db::name('general_yuyue_list')->where('id',$id)->value('title');
                    $uid=$data[$i]['uid'];
                    $data[$i]['nick']=Db::name('general_user_list')->where('id',$uid)->value('nick');
                    $headimg=Db::name('general_user_list')->where('id',$uid)->value('headimg');
                    if($headimg){
                        $data[$i]['headimg']=$headimg;
                    }else{
                        $data[$i]['headimg']=getFullImageUrl('/storage/imges/mo.png');
                    }

                    if(!$data[$i]['y_time']){
                        $data[$i]['y_time']='无';
                    }
                    if(strtotime($data[$i]['y_data'])<12017576){
                        $data[$i]['y_data']='无';
                    }
                    //查询是否有选择座位 seat
                    $data[$i]['seat']=Db::name('general_yuyue_seatmsg')->where('orderid',$ord)->value('title')?:'无';
                    //查询是否有选择人员 seat
                    $personid=$data[$i]['personid'];
                    $person='';
                    if($personid){
                        $person=Db::name('general_yuyue_personnel')->where('id',$personid)->value('title');
                    }
                    $data[$i]['person']=$person?:'无';
                    $classid='无';
                    $data[$i]['classid']=$classid;
                    //查询规格
                    $specid=$data[$i]['specid'];
                    $spec='';
                    if($specid){
                        $spec=Db::name('general_yuyue_yuyuespec')->where('id',$specid)->column('title');
                        $spec=implode(',',$spec);
                    }
                    $data[$i]['spec']=$spec?:'无';
                    //先查询数量
                    $ordno=Db::name('general_yuyue_ord')->where('ord_id',$ord)->count();
                    $data[$i]['ordno']=$ordno;
                    //再查具体数据
                    $res = Db::name('general_yuyue_ord')->alias('o')
                        ->join('general_yuyue_form f', 'o.form_id = f.id', 'LEFT')
                        ->where('o.ord_id', $ord)
                        ->field('o.val,o.type,f.name')
                        ->order('o.paiid desc,o.id desc')
                        ->select()->toArray();
                    if($res){
                        for($x=0;$x<count($res);$x++){
                            if($res[$x]['type']==5){
                                $img=explode(',',$res[$x]['val']);
                                if($img){
                                    for($s=0;$s<count($img);$s++){
                                        $img[$s]=getFullImageUrl($img[$s]);
                                    }
                                }
                                $res[$x]['val']=$img;
                            }
                        }
                    }
                    $data[$i]['res']=$res;
                }
            }
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        //查询有没有座位
        $zwno=Db::name('general_yuyue_seat')->count();
        View::assign('zwno', $zwno);
        //查询有没有人员
        $ryno=Db::name('general_yuyue_personnel')->count();
        View::assign('ryno', $ryno);
        //查询有没有规格
        $gno=GeneralYuyueYuyuespec::count();
        View::assign('gno', $gno);
        return View::fetch();
    }

    public function dayin(){
        return View::fetch();
    }

    public function edit(){
        $data = array('status' => 0,'msg' => '未知错误');
        $id=input("param.id");
        if(!$id){
            $data['msg']='参数错误';return json($data);exit;
        }
        $st=input("param.st");
        //id开始叫号  修改状态为2
        $new=gettime();
        if($st==3){
            $ordheno=Db::name('general_yuyue_order')->where('id',$id)->value('heno');
            //查询订单是否有规格 规格的核销次数是多少次
            $specid=Db::name('general_yuyue_order')->where('id',$id)->value('specid');
            if($specid){
                $heno=Db::name('general_yuyue_yuyuespec')->where('id',$specid)->value('heno');
                //如果核销次数小于了订单已核销次数则提示成功  继续核销
                if($ordheno+1<$heno){
                    $ordheno=$ordheno+1;
                    Db::name('general_yuyue_order')->where('id',$id)->update(['heno'=>$ordheno]);
                    $text = '修改了预约订单id='.$id.'的数据-未完成-核销'.$ordheno.'次';
                    YuyueListmsgHandler::add('后台修改数据-未完成-核销'.$ordheno.'次',$id,2,$text);
                    $data['msg']='操作成功';
                    $data['status']=1;
                    return json($data);
                }
            }
            Db::name('general_yuyue_order')->where('id',$id)->update(['status'=>3]);
            Db::name('general_yuyue_order')->where('id',$id)->inc('heno')->update();
            $text = '修改了预约订单id='.$id.'的数据-已完成';
            YuyueListmsgHandler::add('后台修改数据-已完成',$id,3,$text);
            $res=Db::name('general_yuyue_order')->where('id',$id)->field(['money','uid'])->find();
            if($res){
                //交易成功  客户的介绍人分销
                $uid=$res['uid'];
                $user=GeneralUserList::find($uid);
                $money=$res['money'];
                event('Receive', ['user'=>$user,'money'=>$money]);
            }
        }else if($st==2){
            Db::name('general_yuyue_order')->where('id',$id)->update(['status'=>2]);
            $text = '线下支付了预约订单id='.$id.'的数据-已支付';
            YuyueListmsgHandler::add('后台修改数据-已支付',$id,2,$text);
        }else if($st==4){
            Db::name('general_yuyue_order')->where('id',$id)->update(['status'=>4]);
            $text = '取消了预约订单id='.$id.'的数据-已取消';

            //查询是否有座位  有座位则取消座位
            Db::name('general_yuyue_seatmsg')->where('orderid',$id)->update(['status'=>2]);

            YuyueListmsgHandler::add('后台修改数据-已取消',$id,4,$text);
        }
        $this->writeActionLog($text);
        $data['msg']='操作成功';
        $data['status']=1;
        return json($data);
    }
    public function info(){
        $id=input('get.id');
        if(!$id){
            $data['msg']='id错误';return json($data);exit;
        }
        $list=Db::name('general_yuyue_order')
            ->where('id',$id)
            ->find();
        //查询场地
        $listid=$list['list_id'];
        $list['title']=Db::name('general_yuyue_list')->where('id',$listid)->value('title');
        View::assign('list', $list);
        //查询状态列表
        $statuslist=Db::name('general_yuyue_ordermsg')->where('order_id',$id)->select();
        View::assign('statuslist', $statuslist);
        return View::fetch('order_info');
    }

    public function daochu(){


        $where=[];
        $id=input('param.id');
        if($id){
            $where[]=['id','=',$id];
        }
        $acid=input('param.acid');
        if($acid){
            $where[]=['list_id','=',$acid];
        }
        $uid=input('param.uid');
        if($uid){
            $where[]=['uid','=',$uid];
        }
        $status=input('param.status');
        if($status){
            $where[]=['status','=',$status];
        }
        $y_data=input('param.y_data');
        if($y_data){
            $where[]=['y_data','=',$y_data];
        }
        $form_id=input('param.form_id');
        if($form_id){
            $orderids=Db::name('general_yuyue_ord')->where('form_id',$form_id)->column('ord_id');
            $where[]=['id','in',$orderids];
        }
        $val=input('param.val');
        if($val){
            $orderids=Db::name('general_yuyue_ord')->where('val','like','%'.$val.'%')->column('ord_id');
            $where[]=['id','in',$orderids];
        }
        $str=input('param.str');
        $end=input('param.end');
        if($str && $end){
            $where[]=['addtime','between',[$str,$end]];
        }
        $field=input('param.field');
        $pai=input('param.order');
        $limit=input("param.limit");
        if(!$limit){
            $limit=10;//每页显示条数
        }   
        //查询产品
        $data=Db::name('general_yuyue_order')->where($where)->order('id desc')->select()->toArray();

        // $sql="SELECT o.*,GROUP_CONCAT(f.`val`) FROM general_yuyue_order o 
        // LEFT JOIN general_yuyue_ord f ON o.`id` = f.ord_id
        //  WHERE  o.`status`>0  ";
        // $id=input('param.id');
        // if($id){
        //     $sql.=" AND o.id = ".$id;
        // }
        // $acid=input('param.acid');
        // if($acid){
        //     $sql.=" AND o.list_id = ".$acid;
        // }
        // //先查询字段
        // if(!$acid){
        //     echo "请从预约里面点订单";exit;
        // }
        // $uid=input('param.uid');
        // if($uid){
        //     $sql.=" AND o.uid = ".$uid;
        // }
        // $status=input('param.status');
        // if($status){
        //     $sql.=" AND o.status = ".$status;
        // }
        // $y_data=input('param.y_data');
        // if($y_data){
        //     $sql.=" AND o.y_data = '".$y_data."'";
        // }
        // //查询单个字段
        // $form_id=input('param.form_id');
        // if($form_id){
        //     $sql.=" AND f.form_id = ".$form_id;
        // }
        // $val=input('param.val');
        // if($val){
        //     $sql.=" AND f.val like '%".$val."%'";
        // }
        // //查询时间
        // $str=input('param.str');
        // $end=input('param.end');
        // if($str && $end){
        //     $sql.=" AND o.addtime between '".$str."' and '".$end."'";
        // }

        // $field=input('param.field');
        // $pai=input('param.order');
        // if($field){
        //     $sql.=' GROUP BY o.id desc ORDER BY o.'.$field.' '.$pai;
        // }else{
        //     $sql.="  GROUP BY o.id desc";
        // }
        // $data=Db::query($sql);


        $forms=Db::name('general_yuyue_form')->where('list_id', $acid)
            ->field('name')
            ->order('paiid desc,id desc')
            ->select()->toArray();

//        //查询产品
//        $data=Db::name('general_yuyue_order')
//            ->where($where)
//            ->order($order)
//            ->select()->toArray();

        if($data){
            for($i=0;$i<count($data);$i++){
                $ord=$data[$i]['id'];
                $data[$i]['nick']=Db::name('general_user_list')->where('id',$uid)->value('nick');
                //再查具体数据
                $classid='无';

                //查询是否有选择座位 seat
                $data[$i]['seat']=Db::name('general_yuyue_seatmsg')->where('orderid',$ord)->value('title')?:'无';

                $data[$i]['classid']=$classid;
                $res = Db::name('general_yuyue_ord')->where('ord_id', $ord)
                    ->field('type,val')
                    ->order('paiid desc,id desc')
                    ->select()->toArray();
                if($res){
                    for($x=0;$x<count($res);$x++){
                        if($res[$x]['type']==5){
                            $xximg='';
                            $img=explode(',',$res[$x]['val']);
                            if($img){
                                for($s=0;$s<count($img);$s++){
                                    $xximg.=getFullImageUrl($img[$s]);
                                    if($s<count($img)-1){
                                        $xximg.=',';
                                    }
                                }
                            }
                            $res[$x]['val']=$xximg;
                        }
                    }
                }
                $data[$i]['res']=$res;
            }
        }



        //导出数据
        $excel = new \PHPExcel(); //引用phpexcel
        $excel->setActiveSheetIndex(0);
        $namex = '预约订单';
        $excel->getActiveSheet()->setTitle($namex); //设置表名
        $excel->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);//所有单元格（列）默认宽度
        $excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(50);
        $excel->getDefaultStyle()->getFont()->setName('微软雅黑');//字体
        $styleThinBlackBorderOutline = array(
            'borders' => array(
                'allborders' => array( //设置全部边框
                    'style' => \PHPExcel_Style_Border::BORDER_THIN //粗的是thick
                ),
            ),
        );
        // 设置单元格的宽度
//        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);#设置单元格宽度
//        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);#设置单元格宽度
//        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);#设置单元格宽度
//        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);#设置单元格宽度
//        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(50);#设置单元格宽度
//        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);#设置单元格宽度
//        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);#设置单元格宽度

        $cno=count($data)+1;

        //修改一下 动态增加  表格字段
        if($forms){
            $shino=count($forms)+4;
            $zi="A B C D E F G H I J K L M N O P Q R S T U V W X Y Z";
            $fgrr=explode(" ",$zi);
            $end=$fgrr[$shino-1];
            $excel->getActiveSheet()->getStyle('A1:'.$end.$cno)->applyFromArray($styleThinBlackBorderOutline);
            for($i=0;$i<26;$i++){
                $newzi=$fgrr[$i];
                if($i<$shino){
                    if($i==0){
                        $excel->setActiveSheetIndex(0)->setCellValue($newzi.'1', 'ID');
                    }else{
                        if($i==($shino-3)){
                            $excel->setActiveSheetIndex(0)->setCellValue($newzi.'1', '选择座位');
                        }else
                            if($i==($shino-2)){
                                $excel->setActiveSheetIndex(0)->setCellValue($newzi.'1', '预约时间');
                            }else{
                                if($i==($shino-1)){
                                    $excel->setActiveSheetIndex(0)->setCellValue($newzi.'1', '提交时间');
                                }else{
                                    $excel->setActiveSheetIndex(0)->setCellValue($newzi.'1', $forms[$i-1]['name']);
                                }
                            }
                    }
                }
                foreach ($data as $key => $value) {
                    if($i<$shino) {
                        $res = $value['res']; //结果

                        if ($i == 0) {
                            $excel->setActiveSheetIndex(0)->setCellValue($newzi . ($key + 2), $value['id']);
                        } else{
                            if ($i == ($shino - 3)) {
                                $excel->setActiveSheetIndex(0)->setCellValue($newzi . ($key + 2), $value['seat']);
                            }else
                                if ($i == ($shino - 2)) {
                                    if(!$value['y_time']){
                                        $value['y_time']='无';
                                    }
                                    if(strtotime($value['y_data'])<12017576){
                                        $value['y_data']='无';
                                    }
                                    //$excel->setActiveSheetIndex(0)->setCellValue($newzi.($key+2), $value['nick'].'(ID:'.$value['uid'].')');
                                    $excel->setActiveSheetIndex(0)->setCellValue($newzi . ($key + 2), $value['y_data'].' '.$value['y_time']);
                                } else {
                                    if ($i == ($shino - 1)) {
                                        $excel->setActiveSheetIndex(0)->setCellValue($newzi . ($key + 2), $value['addtime'] ?: '无');
                                    } else {
                                        if ($res) {
                                            if ($res[$i - 1]['type'] == 5) {
                                                $excel->setActiveSheetIndex(0)->setCellValue($newzi . ($key + 2), $res[$i - 1]['val'] ?: '无');
                                                $excel->setActiveSheetIndex(0)->getCell($newzi . ($key + 2))->getHyperlink()->setUrl($res[$i - 1]['val']);
                                                $excel->setActiveSheetIndex(0)->getStyle($newzi . ($key + 2))->getFont()->setBold(false)->setName('Verdana')->setSize(10)->getColor()->setRGB('87CEEB');
                                            } else {
                                                $excel->setActiveSheetIndex(0)->setCellValue($newzi . ($key + 2), $res[$i - 1]['val'] ?: '无');
                                            }
                                        } else {
                                            $excel->setActiveSheetIndex(0)->setCellValue($newzi . ($key + 2), '无');
                                        }
                                    }
                                }
                        }
                    }
                }
            }
        }else{
            $excel->setActiveSheetIndex(0)->setCellValue('A1', 'ID');
            $excel->setActiveSheetIndex(0)->setCellValue('B1', '预约座位');
            $excel->setActiveSheetIndex(0)->setCellValue('C1', '预约时间');
            $excel->setActiveSheetIndex(0)->setCellValue('D1', '提交时间');
            foreach ($data as $key => $value) {
                if(!$value['y_time']){
                    $value['y_time']='无';
                }
                if(strtotime($value['y_data'])<12017576){
                    $value['y_data']='无';
                }
                $excel->setActiveSheetIndex(0)->setCellValue('A' . ($key + 2), $value['id']);
                $excel->setActiveSheetIndex(0)->setCellValue('B' . ($key + 2), $value['seat']);
                $excel->setActiveSheetIndex(0)->setCellValue('C' . ($key + 2), $value['y_data'].' '.$value['y_time']);
                $excel->setActiveSheetIndex(0)->setCellValue('D' . ($key + 2), $value['addtime'] ?: '无');
            }

        }
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $namex . '.xls"');
        header('Cache-Control: max-age=0');
        $res_excel = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $res_excel->save('php://output');

    }
    public function export_excel($file_name, $tile = [], $data = [])
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 0);
        ob_end_clean();
        ob_start();
        header("Content-Type: text/csv");
        header("Content-Disposition:filename=" . $file_name);
        $fp = fopen('php://output', 'w');
        // 转码 防止乱码(比如微信昵称)
        fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($fp, $tile);
        $index = 0;
        foreach ($data as $item) {
            if ($index == 1000) {
                $index = 0;
                ob_flush();
                flush();
            }
            $index++;
            fputcsv($fp, $item);
        }
        ob_flush();
        flush();
        ob_end_clean();
    }
    public function del(){
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            //删除数据
            if($arr[$i]){
                Db::name('general_yuyue_order')->where('id',$arr[$i])->delete();
                $text = '删除了订单id='.$arr[$i].'的数据';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }

}