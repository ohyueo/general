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
 * Time: 15:07
 */

namespace app\api\controller;
use app\models\GeneralZongheImg;
use app\models\GeneralShopList;
use app\models\GeneralShopInfo;
use app\models\YuyueList;
use app\models\GeneralShopClass;
use app\models\GeneralShopOrder;
use app\models\UserList;
use app\models\VenuesCouponReceive;
use think\Request;
use think\facade\Db;
use app\BaseController;
use app\validate\api\vshop;

class Shop extends BaseController
{
    //确认商品订单收货
    public function querenorder(){
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $uid=$user->id;
        $id=input("param.id/d");
        if(!$id){
            return $this->message('参数错误', [], 0);
        }
        $res=GeneralShopOrder::find($id);
        if(!$res || $res['user_id']!=$uid){
            return $this->message('没有权限', [], 0);
        }
        if($res['status']!=3){
            return $this->message('状态异常', [], 0);
        }
        $res->status=5;//已完成
        $res->save();
        return $this->message('收货成功', []);
    }
    public function shoplistindex(Request $request){
        $params = $request->get();
        $where=array();
        if(isset($params['type'])){
            $where=array('type'=>$params['type']);
        }
        //查询图片
        $list=GeneralZongheImg::where($where)->select()->toArray();
        $x=$y=$z=$s=0;
        $arr=[];
        foreach ($list as $step) {
            if($step['type']==4){
                $arr['lunbo'][$x]=$step;
                $x++;
            }
            if($step['type']==5){
                $arr['guang'][$z]=$step;
                $z++;
            }
        }
        $istu=0;
        $classlist=GeneralShopClass::select()->toArray();
        if($classlist){
            for($i=0;$i<count($classlist);$i++){
                if($classlist[$i]['img']){
                    $istu=1;
                    $classlist[$i]['img']=getFullImageUrl($classlist[$i]['img']);
                }
            }
        }
        $arr['istu']=$istu;
        $arr['kuai']=$classlist;
        return $this->message('请求成功', $arr);
    }

    function shoplist(Request $request){
        //查询商品分类
        $classlist=ShopClass::select();
        //查询场馆名称
        $res=YuyueList::where('status',1)->field(['id','title','img','address','mobile'])->select();

        //$list=ShopList::where('status',1)->paginate(10);
        // 获取总条数
        $data = [
            'res' => $res,
            'classlist' => $classlist,
            //'limit' => $list->toArray()['per_page'],
            //'list' => $list->toArray()['data'],
            //'count' => $list->toArray()['total']
        ];
        return $this->message('请求成功', $data);
    }
    function getshop(Request $request){
        $params = $request->get();
        $where=array();
        $id=input("get.id/d");
        if($id){
            $where=array('class_id'=>$id);
        }
        $page=input("get.page/d");
        if(!$page){
            $page=1;
        }
        $tit=input("get.tit");
        if($tit){
            $count=GeneralShopList::where('status',1)->where('title','like','%'.$tit.'%')->count();
            $list=GeneralShopList::where('status',1)->where('title','like','%'.$tit.'%')->order('id desc')->page($page,10)->select()->toArray();
        }else{
            $count=GeneralShopList::where('status',1)->where($where)->order('id desc')->count();
            $list=GeneralShopList::where('status',1)->where($where)->order('id desc')->page($page,10)->select()->toArray();
        }

        if($list){
            for($i=0;$i<count($list);$i++){
                $list[$i]['no']=0;
                $img=$list[$i]['img'];
                if($img){
                    $list[$i]['img']=getFullImageUrl($img);
                }
            }
        }
        $data = [
            'count' => $count,
            'list' => $list
        ];
        return $this->message('请求成功', $data);
    }

    //商品订单
    public function shopord(Request $request){
        $data = $request->post();
        $type=strip_tags(trim($data['type']));//
        if(!$type){
            $type=0;
        }
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $page=$data['page'];
        if(!$page){
            $page=1;
        }
        $uid=$user->id;
        $list=[];
        if($type){
            if($type==4){
                $count=Db::name('general_shop_order')->where('user_id',$uid)->where('status','>',3)->count();
                $list=Db::name('general_shop_order')->where('user_id',$uid)->where('status','>',3)->field(['id','status','addtime','list_id'])->order('id desc')->page($page,10)->select()->toArray();
            }else{
                $count=Db::name('general_shop_order')->where('user_id',$uid)->where('status',$type)->count();
                $list=Db::name('general_shop_order')->where('user_id',$uid)->where('status',$type)->field(['id','status','addtime','list_id'])->order('id desc')->page($page,10)->select()->toArray();
            }
        }else{
            $count=Db::name('general_shop_order')->where('user_id',$uid)->count();
            $list=Db::name('general_shop_order')->where('user_id',$uid)->field(['id','status','addtime','list_id'])->order('id desc')->page($page,10)->select()->toArray();
        }
        if($list){
            for($i=0;$i<count($list);$i++){
                $id=$list[$i]['id'];
                //查询所有的商品
                $ress=Db::name('general_shop_data')->alias('d')
                    ->join('general_shop_list l', 'd.shop_id = l.id', 'LEFT')
                    ->where('d.shop_orderid',$id)
                    ->field('l.title,l.img,d.no')
                    ->order('l.id desc')
                    ->select()->toArray();
                //$ress=Db::name('shop_data')->where('shop_orderid',$id)->select()->toArray();
                if($ress){
                    for($v=0;$v<count($ress);$v++){
                        $img=$ress[$v]['img'];
                        $img=getFullImageUrl($img);
                        $ress[$v]['img']=$img;
                    }
                }
                $list[$i]['res']=$ress;
            }
        }
        $data = [
            'count' => $count,
            'list' => $list
        ];
        return $this->message('请求成功', $data);
    }

    public function shopordinfo(){
        $id=input("param.id/d");
        if(!$id){
            return $this->message('参数错误', [], 0);
        }
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $uid=$user->id;
        $list=Db::name('general_shop_order')->where('user_id',$uid)->where('id',$id)->find();
        $listid=$list['list_id'];
        $star='';
        if($list['status']==1){
            $star='待付款';
        }else if($list['status']==2){
            $star='待发货';//待上门
        }else if($list['status']==3){
            $star='已发货';
        }else if($list['status']==4){
            $star='已收货';
        }else if($list['status']==5){
            $star='已完成';
        }else if($list['status']==6){
            $star='已关闭';
        }else{
            $star='其他';
        }

        $ress=Db::name('general_shop_data')->alias('d')
            ->join('general_shop_list l', 'd.shop_id = l.id', 'LEFT')
            ->where('d.shop_orderid',$id)
            ->field('l.title,l.img,d.no,d.money')
            ->order('l.id desc')
            ->select()->toArray();
        if($ress){
            for($v=0;$v<count($ress);$v++){
                $img=$ress[$v]['img'];
                $img=getFullImageUrl($img);
                $ress[$v]['img']=$img;
            }
        }
        $arr['res']=$ress;

        $arr['star']=$star;
        $arr['addtime']=$list['addtime'];
        $arr['money']=$list['money'];
        $arr['paymo']=$list['pay_mo'];
        $arr['name']=$list['name'];
        $arr['id']=$list['id'];
        $arr['mobile']=$list['mobile'];
        $arr['address']=$list['address'];
        $arr['message']=$list['message'];
        $arr['status']=$list['status'];
        $arr['texter']=$list['texter'];
        $arr['pay_order']=$list['pay_order'];
        //$arr['address']=Db::name('general_yuyue_list')->where('id',$listid)->value('address');

        //查询商家
        return $this->message('请求成功', $arr);
    }

    function shopinfo(Request $request){
        $params = $request->get();
        $where=array();
        if(isset($params['id'])){
            $where=array('id'=>$params['id']);
        }
        $id=$params['id'];
        $list=GeneralShopList::where($where)->find();
        $list->pv=$list->pv+1;
        $list->save();
        $info=GeneralShopInfo::where('list_id',$id)->find();
        $list['texter']=$info->content;
        $list['img']=$list['img']?getFullImageUrl($list['img']):'';
        // 获取总条数
        $data = [
            'list' => $list
        ];
        //查询appid 和 客服链接
        $appid=config('-wxsite.wx_kefu_qiyeid');//企业id
        $data['appid']=$appid;
        $kfurl=config('-wxsite.wx_kefu_url');//客服链接
        $data['kfurl']=$kfurl;
        return $this->message('请求成功', $data);
    }
    function shoporder(Request $request){
        //$params = $request->get();
        //$where=array();
        //if(isset($params['id'])){
        //    $where=array('id'=>$params['id']);
        //}
        //$list=ShopList::where($where)->find();
        //名称和地址
        $name=config('-hosplsite.app_company');
        $list['name']=$name;
        $address=config('-hosplsite.address');
        $list['address']=$address;
        $latitude=config('-hosplsite.latitude');//维度
        $list['latitude']=$latitude;
        $longitude=config('-hosplsite.longitude');//经度
        $list['longitude']=$longitude;
        // 获取总条数
        //$data = ['list' => $list];
        //查询appid 和 客服链接
        $appid=config('-wxsite.wx_kefu_qiyeid');//企业id
        $data['appid']=$appid;
        $kfurl=config('-wxsite.wx_kefu_url');//客服链接
        $data['kfurl']=$kfurl;
        //查询该用户的姓名和手机号
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $name='';
        $mobile='';
        if($user->userinfo){
            $name=$user->userinfo->name;
            $mobile=$user->userinfo->mobile;
        }
        $data['name']=$name;
        $data['mobile']=$mobile;
        //是否开启共享地址
        $gxadd=config('-systemsite.wxgxadd');
        $data['isgx']=$gxadd;
        return $this->message('请求成功', $data);
    }
    function addshop(Request $request){
        $data = $request->post();
        // 检验数据
        validate(vshop\AddShopValidate::class)->check($data);
        $id=$data['id'];//场馆id
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }

        $uid=$user->id;
        $name=$data['name'];
        $mobile=$data['mobile'];
        $address=$data['address'];
        $message=$data['message'];
        $orderdata=$data['orderdata'];

        $cid=intval($data['cid']);
        $money=0;//定义订单价格
        $zno=0;//商品数量
        //通过计算得出应该付款的金额
        $res1=explode("_",$orderdata);

        if($res1){
            for($i=0;$i<count($res1);$i++){
                $list=$res1[$i];
                //分割字符串
                $res2=explode("-",$list);
                if($res2){
                    $sid=$res2[0];//商品id
                    $sno=$res2[1];//商品数量
                    $dmo=Db::name('general_shop_list')->where('id',$sid)->value("money");
                    $zmo=$dmo*$sno;
                    $money+=$zmo;
                    $zno+=$sno;
                }
            }
        }

        $neiwtime=time();
        //开启事务  查询是否已满  是否已经报名过了
        Db::startTrans();
        try {

            //查询他是否有订单
            $res=Db::name('general_shop_order')->where('user_id',$uid)
                ->lock(true)->order('id desc')->find();
            if($res && $neiwtime-strtotime($res['addtime'])<15){
                Db::commit();
                return $this->message('提交过快请稍后再试', [], 0);
            }

            $code=200;
            $payid=0;
            if($money>0){
                $status=1;//待支付
            }else{
                $status=2;//待发货
            }

            $arr=[
                'list_id' => $id,
                'user_id' => $uid,
                'money' =>$money,
                'shop_no' =>$zno,
                'pay_order' => $payid,
                'addtime' => gettime(),
                'status' => $status,
                'pay_mo' => 0,
                'name' => $name,
                'mobile' => $mobile,
                'address' => $address,
                'message' => $message
            ];
            $reid=Db::name('general_shop_order')->insertGetId($arr);
            if($reid){
                if($res1){
                    for($i=0;$i<count($res1);$i++){
                        $list=$res1[$i];
                        //分割字符串
                        $res2=explode("-",$list);
                        if($res2){
                            $sid=$res2[0];//商品id
                            $sno=$res2[1];//商品数量
                            $dmo=Db::name('general_shop_list')->where('id',$sid)->value("money");//商品价格
                            $title=Db::name('general_shop_list')->where('id',$sid)->value("title");//商品标题
                            $arrx=array(
                                'shop_orderid' => $reid,
                                'shop_id' => $sid,
                                'title' => $title,
                                'money' => $dmo,
                                'no' => $sno
                            );
                            Db::name('general_shop_data')->insert($arrx);
                        }
                    }
                }
            }

            $msg='购买成功';
            $rearr['id']=$reid;
            $rearr['money']=$money;
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            $err=$e->getMessage();
            $msg='购买失败：'.$err;
            $rearr=[];
            $code=0;
            Db::rollback();
        }
        return $this->message($msg, $rearr, $code);
    }
    public function shop_quxiao(Request $request){
        $params = $request->post();
        if(!$params['id']){
            return $this->message('参数错误', [], 0);
        }
        $user = $this->user();
        if(!$user){
            return $this->message('重新登录', [], 201);
        }
        $uid=$user->id;
        //查询是否是本人
        $d=ShopOrder::find($params['id']);
        if($d->user_id!=$uid){
            return $this->message('权限不足', [], 0);
        }
        if($d->status!=1){
            return $this->message('只有待付款可取消', [], 0);
        }
        $d->status=4;//取消预约
        $d->save();
        return $this->message('取消成功', [], 200);
    }
}