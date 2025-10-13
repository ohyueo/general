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
 * Date: 2021/7/26
 * Time: 16:11
 */

namespace app\admin\controller;
use think\facade\View;
use think\facade\Db;
use app\admin\traits\AdminAuth;

class Wen extends Common
{
    use AdminAuth;

    public function index(){
        $permis = $this->getPermissions('Wen/index');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()) {
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
            $limit=input("param.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            //查询产品
            $count = Db::name('general_yuyue_texter')
                ->where($where)
                ->count();
            $data=Db::name('general_yuyue_texter')
                ->where($where)
                ->page($page,$limit)
                ->order('id desc')
                ->select()->toArray();
            //循环分类名称
            if($data){
                foreach($data as $k=>$v){
                    $classid=$v['classid'];
                    $class=Db::name('general_wen_wenclass')->where('id',$classid)->find();
                    $data[$k]['classname']=$class['title'];
                }
            }
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        return View::fetch();
    }
    public function addwen(){
        if(request()->isPost()) {
            $data = array('status' => 0, 'msg' => '未知错误');
            $id = input("param.id");
            $title = input('param.title');
            if (!$title) {
                $data['msg'] = '姓名不能为空';
                return json($data);
            }
            $img=input('param.img');
            if(!$img){
                $data['msg']='照片不能为空';
                return json($data);
            }
            $texter=$_POST['texter'];//未做过滤  比较危险
            //$texter=input('param.texter');
            //$texter=input('param.texter','','remove_xss');//无法过滤video标签所以废弃
            if(!$texter){
                $data['msg']='内容不能为空';
                return json($data);
            }
            //过滤一些危险标签
            $texter=str_replace('script','',$texter);
            $texter=str_replace('iframe','',$texter);
            $texter=str_replace('onload','',$texter);
            $texter=str_replace('onerror','',$texter);
            $texter=str_replace('onmouseover','',$texter);
            $texter=str_replace('onmouseout','',$texter);
            $texter=str_replace('onfocus','',$texter);
            $texter=str_replace('onblur','',$texter);
            $texter=str_replace('onclick','',$texter);
            $texter=str_replace('ondblclick','',$texter);
            $texter=str_replace('onmousedown','',$texter);
            $texter=str_replace('onmouseup','',$texter);
            $texter=str_replace('onmousemove','',$texter);
            $texter=str_replace('onkeypress','',$texter);
            $texter=str_replace('onkeydown','',$texter);
            $texter=str_replace('onkeyup','',$texter);
            $texter=str_replace('onresize','',$texter);
            $texter=str_replace('onselect','',$texter);
            $texter=str_replace('onsubmit','',$texter);
            $texter=str_replace('onunload','',$texter);
            $texter=str_replace('onchange','',$texter);
            $texter=str_replace('oncontextmenu','',$texter);
            $texter=str_replace('oninput','',$texter);
            $texter=str_replace('oninvalid','',$texter);
            $texter=str_replace('onreset','',$texter);
            $texter=str_replace('onsearch','',$texter);
            $texter=str_replace('onselectstart','',$texter);
            $texter=str_replace('onmousewheel','',$texter);
            $texter=str_replace('oncopy','',$texter);
            //过滤完毕
            $pv=input('param.pv');
            $status=input('param.status');
            $istui=input('param.istui');
            $classid=input('param.classid');
            $openurl=input('param.openurl');
            $arr=array('title'=>$title,'img'=>$img,'texter'=>$texter,'pv'=>$pv,'status'=>$status,'istui'=>$istui,'classid'=>$classid,'openurl'=>$openurl);
            $arr['status']=1;
            $arr['addtime']=gettime();
            $xid=Db::name('general_yuyue_texter')->insertGetId($arr);
            $text = '添加了文章 id='.$xid;
            $this->writeActionLog($text);
            $data['msg']='添加成功';
            $data['status']=1;
            return json($data);
        }
        $permis = $this->getPermissions('Wen/edit_wen');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        //查询分类yuyue_class
        $list=Db::name('general_wen_wenclass')->select();
        View::assign('list',$list);
        return View::fetch('edit_wen');
    }
    public function editwen(){
        if(request()->isPost()) {
            $data = array('status' => 0, 'msg' => '未知错误');
            $id = input("param.id");
            $title = input('param.title');
            if (!$title) {
                $data['msg'] = '姓名不能为空';
                return json($data);
            }
            $img=input('param.img');
            if(!$img){
                $data['msg']='照片不能为空';
                return json($data);
            }
            $texter=$_POST['texter'];//未做过滤  比较危险
            //$texter=input('param.texter');
            //$texter=input('param.texter','','remove_xss');//无法过滤video标签所以废弃
            if(!$texter){
                $data['msg']='内容不能为空';
                return json($data);
            }
            //过滤一些危险标签
            $texter=str_replace('script','',$texter);
            $texter=str_replace('iframe','',$texter);
            $texter=str_replace('onload','',$texter);
            $texter=str_replace('onerror','',$texter);
            $texter=str_replace('onmouseover','',$texter);
            $texter=str_replace('onmouseout','',$texter);
            $texter=str_replace('onfocus','',$texter);
            $texter=str_replace('onblur','',$texter);
            $texter=str_replace('onclick','',$texter);
            $texter=str_replace('ondblclick','',$texter);
            $texter=str_replace('onmousedown','',$texter);
            $texter=str_replace('onmouseup','',$texter);
            $texter=str_replace('onmousemove','',$texter);
            $texter=str_replace('onkeypress','',$texter);
            $texter=str_replace('onkeydown','',$texter);
            $texter=str_replace('onkeyup','',$texter);
            $texter=str_replace('onresize','',$texter);
            $texter=str_replace('onselect','',$texter);
            $texter=str_replace('onsubmit','',$texter);
            $texter=str_replace('onunload','',$texter);
            $texter=str_replace('onchange','',$texter);
            $texter=str_replace('oncontextmenu','',$texter);
            $texter=str_replace('oninput','',$texter);
            $texter=str_replace('oninvalid','',$texter);
            $texter=str_replace('onreset','',$texter);
            $texter=str_replace('onsearch','',$texter);
            $texter=str_replace('onselectstart','',$texter);
            $texter=str_replace('onmousewheel','',$texter);
            $texter=str_replace('oncopy','',$texter);
            //过滤完毕 只是简单过滤 还有很多标签没有过滤 比较危险
            $pv=input('param.pv');
            $status=input('param.status');
            $istui=input('param.istui');
            $classid=input('param.classid');
            $openurl=input('param.openurl');
            $arr=array('title'=>$title,'img'=>$img,'texter'=>$texter,'pv'=>$pv,'status'=>$status,'istui'=>$istui,'classid'=>$classid,'openurl'=>$openurl);
            if($id){
                Db::name('general_yuyue_texter')->where('id',$id)->update($arr);
                $data['msg']='修改成功';
                $data['status']=1;
                $text = '修改了文章列表 id='.$id;
                $this->writeActionLog($text);
            }
            return json($data);
        }
        $permis = $this->getPermissions('Wen/edit_wen');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        //查询分类yuyue_class
        $list=Db::name('general_wen_wenclass')->select();
        View::assign('list',$list);
        return View::fetch('edit_wen');
    }
    public function delwen(){
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            //删除数据
            if($arr[$i]){
                Db::name('general_yuyue_texter')->where('id',$arr[$i])->delete();
                $text = '删除了文章列表id='.$arr[$i].'的数据';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }
    public function sydex(){
        $permis = $this->getPermissions('Wen/sydex');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        if(request()->isPost()) {
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
            $limit=input("param.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            //查询产品
            $count = Db::name('general_web_text')
                ->where($where)
                ->count();
            $data=Db::name('general_web_text')
                ->where($where)
                ->page($page,$limit)
                ->order('id desc')
                ->select()->toArray();
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);
        }
        return View::fetch();
    }
    public function sydata(){
        $page=input("get.page");
        if(!$page){
            $page=1;
        }
        $where=[];
        $name=input("get.name");
        if($name){
            $where[]=['title','like','%'.$name.'%'];
        }
        $id=input('get.id');
        if($id){
            $where[]=['id','=',$id];
        }
        $limit=input("get.limit");
        if(!$limit){
            $limit=10;//每页显示条数
        }
        //查询产品
        $count = Db::name('general_web_text')
            ->where($where)
            ->count();
        $data=Db::name('general_web_text')
            ->where($where)
            ->page($page,$limit)
            ->order('id desc')
            ->select()->toArray();
        $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
        return json($res);
    }
    public function edit(){
        if(request()->isPost()) {
            $data = array('status' => 0, 'msg' => '未知错误');
            $id = input("param.id");
            $title = input('param.title');
            if (!$title) {
                $data['msg'] = '姓名不能为空';
                return json($data);
            }
            //$texter=$_POST['texter'];
            $texter=input('param.texter','','remove_xss');
            if(!$texter){
                $data['msg']='内容不能为空';
                return json($data);
            }
            $arr=array('title'=>$title,'texter'=>$texter);
            if($id){
                Db::name('general_web_text')->where('id',$id)->update($arr);
                $data['msg']='修改成功';
                $data['status']=1;
                $text = '修改了系统文章 id='.$id;
                $this->writeActionLog($text);
            }
            return json($data);
        }
        $permis = $this->getPermissions('Wen/edit_sywen');
        View::assign('data', $this->actions);
        View::assign('permis', $permis);
        return View::fetch();
    }
    public function delsywen(){
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            //删除数据
            if($arr[$i]){
                Db::name('general_web_text')->where('id',$arr[$i])->delete();
                $text = '删除了系统文章id='.$arr[$i].'的数据web_text';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }
	public function wenclass(){
 		$dao=input('param.dao');
		if(request()->isPost() || $dao==1){
			$page=input('param.page');
			if(!$page){
				$page=1;
			}
			$limit=input('param.limit');
			if(!$limit){
				$limit=10;//每页显示条数
			}
			$where=[];
			$id=input('param.id');
			if($id){
				$where[]=['id','=',$id];
			}
			$title=input('param.title');
			if($title){
				$where[]=['title','like','%'.$title.'%'];
			}
			$status=input('param.status');
			if($status){
				$where[]=['status','like','%'.$status.'%'];
			}
			$paiid=input('param.paiid');
			if($paiid){
				$where[]=['paiid','like','%'.$paiid.'%'];
			}
			$count = Db::name('general_wen_wenclass')->where($where)->count();
			if($dao!=1){
				$data=Db::name('general_wen_wenclass')->where($where)->page($page,$limit)->order('id desc')->select()->toArray();
			}
			if($dao==1){
				$data=Db::name('general_wen_wenclass')->where($where)->order('id desc')->select()->toArray();
			}
			$res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
			return json($res);
		}
		$permis = $this->getPermissions('Wen/wenclass');
		View::assign('data', $this->actions);
		View::assign('permis', $permis);
		return View::fetch();
	}
	public function addwenclass(){
 		if(request()->isPost()){
			$data = array('status' => 0, 'msg' => '未知错误');
			$title=input('param.title');
			$status=input('param.status');
			$paiid=input('param.paiid');
			$arr=array(
				'title'=>$title,
				'status'=>$status,
				'paiid'=>$paiid,
			);
			$id=Db::name('general_wen_wenclass')->insertGetId($arr);
			$text = '添加了新闻分类-id='.$id;
			$this->writeActionLog($text);
			$data['status'] = 1;
			return json($data);exit;
		}
		return View::fetch('editwenclass');
	}
	public function editwenclass(){
 		if(request()->isPost()){
			$data = array('status' => 0, 'msg' => '未知错误');
			$title=input('param.title');
			$status=input('param.status');
			$paiid=input('param.paiid');
			$arr=array(
				'title'=>$title,
				'status'=>$status,
				'paiid'=>$paiid,
			);
			$id = input('param.id');
			if ($id){
				$id=Db::name('general_wen_wenclass')->where('id',$id)->update($arr);
				$text = '修改了新闻分类-id='.$id;
				$this->writeActionLog($text);
				$data['status'] = 1;
				return json($data);exit;
			}
		}
		return View::fetch('editwenclass');
	}
	public function delwenclass(){
 		$data = array('status' => 0,'msg' => '未知错误');
		$array=input("param.id");
		if(!$array){
			$data['msg']='参数错误';return json($data);exit;
		}
		$arr=explode(",",$array);
		for($i=0;$i<count($arr);$i++){
			if($arr[$i]){
				Db::name('general_wen_wenclass')->where('id',$arr[$i])->delete();
				$text = '删除了新闻分类id='.$arr[$i].'的数据';
				$this->writeActionLog($text);
			}
		}
		$data['status']=1;
		return json($data);
	}
}