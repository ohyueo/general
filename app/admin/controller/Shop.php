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
 * Date: 2021/5/21
 * Time: 10:27
 */

namespace app\admin\controller;
use think\facade\View;
use think\facade\Db;
use app\admin\traits\AdminAuth;

class Shop extends Common
{
    use AdminAuth;
    public function orderlist(){
        $permis = $this->getPermissions('Shop/orderlist');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        //查询待发货数量
        $daino=Db::name('general_shop_order')->where('status',2)->count();
        View::assign('daino', $daino);
        //查询已发货数量
        $yino=Db::name('general_shop_order')->where('status',3)->count();
        View::assign('yino', $yino);
        //已完成数量
        $yiwcno=Db::name('general_shop_order')->where('status',5)->count();
        View::assign('yiwcno', $yiwcno);
        //今日付款订单
        $jrno=Db::name('general_shop_order')->whereDay('addtime')->where('status','>',1)->count();
        View::assign('jrno', $jrno);
        //今日付款订单金额
        $jrmo=Db::name('general_shop_order')->whereDay('addtime')->where('pay_mo','>',0)->sum('pay_mo');
        View::assign('jrmo', $jrmo);
        if(request()->isPost()){
            $page=input("param.page");
            if(!$page){
                $page=1;
            }
            $where=[];
            $name=input("param.name");
            if($name){
                $where[]=['l.title','like','%'.$name.'%'];
            }
            $id=input('param.id');
            if($id){
                $where[]=['s.id','=',$id];
            }
            $uid=input('param.uid');
            if($uid){
                $where[]=['s.user_id','=',$uid];
            }
            $nick=input('param.nick');
            if($nick){
                $where[]=['u.nick','like','%'.$nick.'%'];
            }
            $namex=input('param.namex');
            if($namex){
                $where[]=['s.name','like','%'.$namex.'%'];
            }
            $mobile=input('param.mobile');
            if($mobile){
                $where[]=['s.mobile','=',$mobile];
            }
            //查询产品
            $count = Db::name('general_shop_order')->alias('s')
                ->join('general_user_list u', 's.user_id = u.id', 'LEFT')
                ->join('general_shop_list l', 's.list_id = l.id', 'LEFT')
                ->where($where)
                ->count();
            $data=Db::name('general_shop_order')->alias('s')
                ->join('general_shop_list l', 's.list_id = l.id', 'LEFT')
                ->join('general_user_list u', 's.user_id = u.id', 'LEFT')
                ->where($where)
                ->page($page,10)
                ->field('s.id,l.title,l.img,s.money,s.status,s.shop_no,s.pay_mo,s.addtime,s.list_id,s.user_id,u.nick,s.name,s.mobile,s.address,s.texter')
                ->order('s.id desc')
                ->select()->toArray();
            if($data){
                for($i=0;$i<count($data);$i++){
                    $data[$i]['phone']=$data[$i]['mobile'];
                    if($data[$i]['mobile']){
                        $data[$i]['mobile']=yc_phonex($data[$i]['mobile']);
                    }
                    $uid=$data[$i]['user_id'];
                    $data[$i]['nick']=Db::name('general_user_list')->where('id',$uid)->value('nick')?:'默认用户';
                    $data[$i]['headimg']=Db::name('general_user_list')->where('id',$uid)->value('headimg')?:'/storage/imges/mo.png';
                    //查询商品数量
                    $sid=$data[$i]['id'];
                    $titx='';
                    $res=Db::name('general_shop_data')->where('shop_orderid',$sid)->select()->toArray();
                    if($res){
                        for($x=0;$x<count($res);$x++){
                            $no=$res[$x]['no'];
                            $title=$res[$x]['title'];
                            $titx=$title.'*'.$no."<br/>";
                        }
                    }

                    $data[$i]['titx']=$titx;
                    if(!$data[$i]['texter']){
                        $data[$i]['texter']='无';
                    }
                }
            }
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        return View::fetch();
    }
    public function shopst(){
        $data = array('status' => 0,'msg' => '未知错误');
        $id=input("param.id");
        if(!$id){
            $data['msg']='参数错误';return json($data);exit;
        }
        $st=input("param.st");
        $val=input("param.val");
        //id开始叫号  修改状态为2
        $new=gettime();
        if($st==5){
            Db::name('general_shop_order')->where('id',$id)->update(['status'=>5,'texter'=>$val]);
            $text = '修改了商品订单id='.$id.'的数据-已完成';
        }else if($st==1){
            Db::name('general_shop_order')->where('id',$id)->update(['status'=>2]);
            $text = '线下支付了商品订单id='.$id.'的数据-已支付';
        }else if($st==2){
            Db::name('general_shop_order')->where('id',$id)->update(['status'=>6]);
            $text = '取消了商品订单id='.$id.'的数据-已取消';
        }
        $this->writeActionLog($text);
        $data['msg']='操作成功';

        $data['status']=1;
        return json($data);
    }

    public function delsorder(){
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            //删除数据
            if($arr[$i]){
                Db::name('general_shop_order')->where('id',$arr[$i])->delete();
                $text = '删除了商品订单id='.$arr[$i].'的数据';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }

    public function shoplist(){
        $id=input('param.id');
        if(!$id){
            $id='';
        }
        View::assign('id', $id);
        $permis = $this->getPermissions('Shop/shoplist');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $page=input("param.page");
            if(!$page){
                $page=1;
            }
            $limit=input("param.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            $where=[];
            $name=input("param.name");
            if($name){
                $where[]=['s.title','like','%'.$name.'%'];
            }
            $id=input('param.id');
            if($id){
                $where[]=['s.id','=',$id];
            }
            $uid=input('param.uid');
            if($uid){
                $where[]=['s.uid','=',$uid];
            }
            $status=input('param.status');
            if($status && $status!=10){
                $where[]=['s.status','=',$status];
            }
            $pre = ($page-1)*$limit;//起始页数
            //查询产品
            $count = Db::name('general_shop_list')->alias('s')->where($where)->count();
            $data=Db::name('general_shop_list')->alias('s')
                ->join('general_shop_info l', 's.id = l.list_id', 'LEFT')
                ->join('general_shop_class c', 'c.id = s.class_id', 'LEFT')
                ->where($where)
                ->page($page,10)
                ->field('s.id,s.title,s.img,s.money,s.status,s.sales,s.inventory,s.sorting,s.zmo,s.class_id,l.content,l.addtime,c.title as name')
                ->order('s.sorting desc,s.id desc')
                ->select()->toArray();
            if($data){
                for($i=0;$i<count($data);$i++){

                }
            }
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        return View::fetch();
    }
    public function shopclass(){
        $permis = $this->getPermissions('Shop/shopclass');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()){
            $page=input("param.page");
            if(!$page){
                $page=1;
            }
            $where=[];
            $name=input("param.name");
            if($name){
                $where[]=['title','like','%'.$name.'%'];
            }
            $id=input('param.id');
            if($id){
                $where[]=['id','=',$id];
            }

            //查询产品
            $count = Db::name('general_shop_class')
                ->where($where)
                ->count();
            $data=Db::name('general_shop_class')
                ->where($where)
                ->page($page,10)
                ->order('id desc')
                ->select()->toArray();
            if($data){
                for ($i=0;$i<count($data);$i++){

                }
            }
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        return View::fetch();
    }

    public function addclass(){
        $this->getPermissions('Shop/addclass');
        if(request()->isPost()){
            $data = array('status' => 0,'msg' => '未知错误');
            $title=input("param.title");
            if(!$title){ $data['msg']='分类名称不能为空';return json($data);exit;}
            $paiid=input("param.paiid");
            $img=input('param.img');
            $arr = array(
                'title' => $title,
                'img' => $img,
                'paiid' => $paiid
            );
            $addloan=Db::name('general_shop_class')->insertGetId($arr);
            if($addloan){
                $data['status']=1;
                $text = '添加了分类'.$title.'id='.$addloan;
                $this->writeActionLog($text);
            }else{
                $data['msg']='添加失败';
            }
            return json($data);exit;
        }
        return View::fetch('class_edit');
    }
    public function editclass(){
        $this->getPermissions('Shop/editclass');
        if(request()->isPost()){
            $data = array('status' => 0,'msg' => '未知错误');
            $title=input("param.title");
            if(!$title){ $data['msg']='分类名称不能为空';return json($data);exit;}
            $paiid=input("param.paiid");
            $img=input('param.img');
            $arr = array(
                'title' => $title,
                'img' => $img,
                'paiid' => $paiid
            );
            $id = input('param.id');
            if($id){
                $addloan=Db::name('general_shop_class')->where('id',$id)->update($arr);;
                if($addloan){
                    $data['status']=1;
                    $text = '修改了分类'.$title.'id='.$addloan;
                    $this->writeActionLog($text);
                }else{
                    $data['msg']='修改失败';
                }
            }else{
                $addloan=Db::name('general_shop_class')->insertGetId($arr);
                if($addloan){
                    $data['status']=1;
                    $text = '添加了分类'.$title.'id='.$addloan;
                    $this->writeActionLog($text);
                }else{
                    $data['msg']='添加失败';
                }
            }
            return json($data);exit;
        }
        return View::fetch('class_edit');
    }
    public function delclass(){
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            //删除数据
            if($arr[$i]){
                Db::name('general_shop_class')->where('id',$arr[$i])->delete();
                $text = '删除了商品分类id='.$arr[$i].'的数据shop_class';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }
    public function add(){
        $this->getPermissions('Shop/orderlist');
        $id=input("param.id");
        $data=Db::name('general_shop_info')->where('list_id',$id)->value('content');
        View::assign('data_val', $data);
        //查询顶级分类
        $list=Db::name('general_shop_class')->select();
        View::assign('list', $list);
        if(request()->isPost()){
            $data = array('status' => 0,'msg' => '未知错误');
            $title=input("param.title");
            if(!$title){ $data['msg']='商城名称不能为空';return json($data);exit;}
            $money=input("param.money");
            if(!$money){ $data['msg']='商品价格不能为空';return json($data);exit;}
            $img=input("param.img");
            if(!$img){ $data['msg']='商品图片不能为空';return json($data);exit;}
            $sales=input("param.sales");
            $inventory=input("param.inventory");
            $sorting=input("param.sorting");
            $status=input("param.status");
            $zmo=input('param.zmo');
            $class_id=input('param.class_id');
            $arr = array(
                'title' => $title,
                'img' => $img,
                'zmo' => $zmo,
                'class_id' => $class_id,
                'money' => $money,
                'status' => $status,
                'sales' => $sales,
                'inventory' => $inventory,
                'sorting' => $sorting
            );
            $addloan=Db::name('general_shop_list')->insertGetId($arr);
            if($addloan){
                $data['status']=1;
                $t=date('Y-m-d H:i:s');
                $content=$_POST['content'];
                //登录表
                $logarr=array(
                    'content' => $content,
                    'addtime' => $t,
                    'list_id' => $addloan
                );
                Db::name('general_shop_info')->insert($logarr);

                $text = '添加了商品id='.$addloan;
                $this->writeActionLog($text);
            }else{
                $data['msg']='添加失败';
            }
            return json($data);exit;
        }
        return View::fetch('edit');
    }
    public function edit(){
        //查询顶级分类
        $list=Db::name('general_shop_class')->select();
        View::assign('list', $list);
        if(request()->isPost()){
            $data = array('status' => 0,'msg' => '未知错误');
            $id=input("param.id");
            if(!$id){ $data['msg']='商品id不能为空';return json($data);exit;}
            $title=input("param.title");
            if(!$title){ $data['msg']='商城名称不能为空';return json($data);exit;}
            $money=input("param.money");
            if(!$money){ $data['msg']='商品价格不能为空';return json($data);exit;}
            $img=input("param.img");
            if(!$img){ $data['msg']='商品图片不能为空';return json($data);exit;}
            $sales=input("param.sales");
            $inventory=input("param.inventory");
            $sorting=input("param.sorting");
            $status=input("param.status");
            $zmo=input('param.zmo');
            $class_id=input('param.class_id');
            $arr = array(
                'title' => $title,
                'img' => $img,
                'money' => $money,
                'zmo' => $zmo,
                'class_id' => $class_id,
                'status' => $status,
                'sales' => $sales,
                'inventory' => $inventory,
                'sorting' => $sorting
            );
            $addloan=Db::name('general_shop_list')->where('id',$id)->update($arr);
            $data['status']=1;
            $content=$_POST['content'];
            //$content=input("param.content");
            //登录表
            $logarr=array(
                'content' => $content
            );
            Db::name('general_shop_info')->where('list_id',$id)->update($logarr);
            $text = '修改了商品id='.$addloan;
            $this->writeActionLog($text);
            return json($data);exit;
        }
        return View::fetch();
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
                Db::name('general_shop_list')->where('id',$arr[$i])->delete();
                $text = '删除了商品id='.$arr[$i].'的数据shop_list';
                $this->writeActionLog($text);
                Db::name('general_shop_info')->where('list_id',$arr[$i])->delete();
                $text = '删除了商品数据id='.$arr[$i].'的详情数据shop_info';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }

}