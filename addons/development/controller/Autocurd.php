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
 * Date: 2023/7/31
 * Time: 20:18
 */

namespace addons\development\controller;
use think\facade\Db;

class Autocurd extends Develocommon
{
    /**生成文件**/
    public function codetopnav(){
        $data['status']=0;
        $data['msg']='生成失败';
        $id=input('param.id/d');//顶级权限id
        if($id){
            $topx=Db::name('general_admin_permission')->where('id',$id)
                ->field(['name','icon'])->find();
            $toptit=$topx['name'];
            $icon=$topx['icon'];
            //查询顶级id是这个id的  所有 权限  然后批量生成
            $list=Db::name('general_admin_permission')->where('parent_id',$id)
                //->where('id','>','80')
                ->select()->toArray();
            if($list){
                for($i=1;$i<count($list);$i++){
                    $model=$list[$i]['controller'];
                    $action=$list[$i]['action'];
                    $title=$list[$i]['name'];
                    $table='general_'.$model.'_'.$action;
                    //兼容老系统 测试用
//                    if($i==0){
//                        $table='general_zonghe_img';
//                    }else if($i==1){
//                        $table='general_noti_list';
//                    }else if($i==2){
//                        $table='general_web_text';
//                    }
                    $table=strtolower($table);
                    $model='Mer'.strtolower($model);
                    //查询数组里面的 所有字段
                    $cuid=Db::name('ohyueo_develo_curdlist')->where('table',$table)->value('id');
                    $reslist=Db::name('ohyueo_develo_curdtable')->where('listid',$cuid)->select()->toArray();
                    if($reslist){
                        //先存入当前这个顶级权限  查询是否存在
                        $topnav=Db::name('general_addons_merchants_permission')->where('controller',$model)->where('parent_id',0)->find();
                        if(!$topnav){
                            //不存在则存入
                            $arrx=array(
                                'name' => $toptit,
                                'controller' => $model,
                                'action' => '',
                                'parent_id' => 0,
                                'is_nav'=>1,
                                'icon' => $icon,
                                'p_id' => 3
                            );
                            $topid=Db::name('general_addons_merchants_permission')->insertGetId($arrx);
                        }else{
                            $topid=$topnav['id'];
                        }
                        //先存入权限信息
                        $ro=Db::name('general_addons_merchants_permission')->where('parent_id',$topid)->where('controller',$model)->where('action',$action)->find();
                        if(!$ro){
                            $roarr=array(
                                'name' => $title,
                                'controller' => $model,
                                'action' => $action,
                                'parent_id' => $topid,
                                'is_nav'=>1,
                                'icon' => '',
                                'p_id' => 0
                            );
                            $rid=Db::name('general_addons_merchants_permission')->insertGetId($roarr);
                            //添加  修改  删除
                            $roarr=array(
                                'name' => '添加',
                                'controller' => $model.'/'.$action,
                                'action' => 'add'.$action,
                                'parent_id' => $rid,
                                'is_nav'=>0,
                                'icon' => '',
                                'p_id' => 0
                            );
                            Db::name('general_addons_merchants_permission')->insert($roarr);
                            //修改
                            $roarr=array(
                                'name' => '修改',
                                'controller' => $model.'/'.$action,
                                'action' => 'edit'.$action,
                                'parent_id' => $rid,
                                'is_nav'=>0,
                                'icon' => '',
                                'p_id' => 0
                            );
                            Db::name('general_addons_merchants_permission')->insert($roarr);
                            //删除
                            $roarr=array(
                                'name' => '删除',
                                'controller' => $model.'/'.$action,
                                'action' => 'del'.$action,
                                'parent_id' => $rid,
                                'is_nav'=>0,
                                'icon' => '',
                                'p_id' => 0
                            );
                            Db::name('general_addons_merchants_permission')->insert($roarr);
                        }
                        //循环数据
                        for($r=0;$r<count($reslist);$r++){
                            $reslist[$r]['title']=$reslist[$r]['name'];
                            $reslist[$r]['name']=$reslist[$r]['beizhu'];
                        }
                        //生成php文件
                        $this->modelcode($model,$action,$reslist,$title,$table);
                        $data['status']=1;
                        $data['msg']='生成成功';
                    }
                }
            }
        }
        return json($data);exit;
    }
    protected function modelcode($model,$action,$pararr=[],$title,$table){
        //判断是否有 此文件 php文件
        $path=app()->getRootPath().'/addons/merchants/controller/'.$model.'.php';
        if(!file_exists($path)) { //没有此文件 则生成
            $html="<?php \n/**\n*  * 系统-受国家计算机软件著作权保护 - !\n* =========================================================\n* Copy right 2018-2025 成都海之心科技有限公司, 保留所有权利。\n* ----------------------------------------------\n* 官方网址: http://www.ohyu.cn\n* 这不是一个自由软件！在未得到官方有效许可的前提下禁止对程序代码进行修改和使用。\n* 任何企业和个人不允许对程序代码以任何形式任何目的再发布。\n* =========================================================\n* User: ohyueo\n* Date: ".(date('Y/m/d'))."\n* Time: ".(date('H:i'))."\n*/\nnamespace addons\merchants\controller;\nuse think\\facade\Log;\nuse think\\facade\Db;\nuse think\\facade\\Request;
            \nclass ".$model." extends Mercommon{\n\n ";

            $biao1="\t";$biao2="\t\t";$biao3="\t\t\t";$biao4="\t\t\t\t";
            //生成列表文件
            $html.=$biao1."public function ".$action."(){\n ";
            $html.=$biao2."\$merid = \$this->getmerid();\n\t\tif(!\$merid){\n\t\t\t\$data['msg'] = '数据异常';\n\t\t\treturn json(\$data);\n\t\t\texit;\n\t\t}\n";
            $html.=$biao2."if(request()->isPost()){\n";
            $html.=$biao3."\$page=input('param.page');\n".$biao3."if(!\$page){\n".$biao4."\$page=1;\n".$biao3."}\n".$biao3."\$limit=input('param.limit');\n".$biao3."if(!\$limit){\n".$biao4."\$limit=10;//每页显示条数\n".$biao3."}\n";
            //搜索数据
            $html.=$biao3."\$where=[];\n\t\t\t\$where['merid'] = \$merid;\n";
            $html.=$biao3."\$id=input('param.id');\n".$biao3."if(\$id){\n".$biao4."\$where[]=['id','=',\$id];\n".$biao3."}\n";
            //查询产品
            $html.=$biao3."\$count = Db::name('".$table."')->where(\$where)->count();\n".$biao3."\$data=Db::name('".$table."')->where(\$where)->page(\$page,\$limit)->order('id desc')->select()->toArray();\n";
            //返回数据
            $html.=$biao3."\$res = [\"data\" => \$data,\"code\"=>0,'message'=>'请求成功','count'=>\$count];\n".$biao3."return json(\$res);\n";
            $html.=$biao2."}\n";
            //显示页面
            $html.=$biao2."\$name='".$action."';\n\t\t\$permis = \$this->getPermissions('".$model."/".$action."');\n\t\t\$actions = \$this->actions;\n\t\t\$assign=['data'=>\$actions,'permis'=>\$permis,'merid'=>\$merid];\n\t\thook('merhook', ['type'=>'view','name'=>\$name,'assign'=>\$assign]);\n";
            $html.=$biao1."}\n";

            //生成添加文件
            $html.=$biao1."public function add".$action."(){\n ";
            $html.=$biao2."\$merid = \$this->getmerid();\n\t\tif(!\$merid){\n\t\t\t\$data['msg'] = '数据异常';\n\t\t\treturn json(\$data);\n\t\t\texit;\n\t\t}\n";
            $html.=$biao2."if(request()->isPost()){\n";
            $html.=$biao3."\$data = array('status' => 0, 'msg' => '未知错误');\n";
            if($pararr){
                $arrtit=$biao3."\$arr=array(\n";
                for ($i=0;$i<count($pararr);$i++){
                    $fna=$pararr[$i]['title'];
                    $html.=$biao3.'$'.$fna."=input('param.".$fna."');\n";
                    $arrtit.=$biao4."'".$fna."'=>$".$fna.",\n";
                }
                $arrtit.=$biao4."'merid'=>\$merid,\n";
                $arrtit.=$biao3.");";
                $html.=$arrtit."\n";
            }
            $html.=$biao3."\$id=Db::name('".$table."')->insertGetId(\$arr);\n".$biao3."\$text = '添加了".$title."-id='.\$id;\n".$biao3."\$this->writeActionLog(\$text);\n".$biao3."\$data['status'] = 1;\n".$biao3."return json(\$data);exit;\n";
            $html.=$biao2."}\n";
            //显示页面
            $html.=$biao2."\$name='edit".$action."';\n\t\t\$assign=[];\n\t\thook('merhook', ['type'=>'view','name'=>\$name,'assign'=>\$assign]);\n";
            $html.=$biao1."}\n";

            //生成修改文件
            $html.=$biao1."public function edit".$action."(){\n ";
            $html.=$biao2."\$merid = \$this->getmerid();\n\t\tif(!\$merid){\n\t\t\t\$data['msg'] = '数据异常';\n\t\t\treturn json(\$data);\n\t\t\texit;\n\t\t}\n";
            $html.=$biao2."if(request()->isPost()){\n";
            $html.=$biao3."\$data = array('status' => 0, 'msg' => '未知错误');\n";
            if($pararr){
                $arrtit=$biao3."\$arr=array(\n";
                for ($i=0;$i<count($pararr);$i++){
                    $fna=$pararr[$i]['title'];
                    $html.=$biao3.'$'.$fna."=input('param.".$fna."');\n";
                    $arrtit.=$biao4."'".$fna."'=>$".$fna.",\n";
                }
                $arrtit.=$biao3.");";
                $html.=$arrtit."\n";
            }
            $html.=$biao3."\$id = input('param.id');\n".$biao3."if (\$id){\n";
            $html.=$biao4."\$id=Db::name('".$table."')->where('id',\$id)->where('merid',\$merid)->update(\$arr);\n".$biao4."\$text = '修改了".$title."-id='.\$id;\n".$biao4."\$this->writeActionLog(\$text);\n".$biao4."\$data['status'] = 1;\n".$biao4."return json(\$data);exit;\n";
            $html.=$biao3."}\n";
            $html.=$biao2."}\n";
            //显示页面
            $html.=$biao2."\$name='edit".$action."';\n\t\t\$assign=[];\n\t\thook('merhook', ['type'=>'view','name'=>\$name,'assign'=>\$assign]);\n";
            $html.=$biao1."}\n";

            //生成删除页面
            $html.=$biao1."public function del".$action."(){\n ";
            $html.=$biao2."\$merid = \$this->getmerid();\n\t\tif(!\$merid){\n\t\t\t\$data['msg'] = '数据异常';\n\t\t\treturn json(\$data);\n\t\t\texit;\n\t\t}\n";
            $html.=$biao2."\$data = array('status' => 0,'msg' => '未知错误');\n".$biao2."\$array=input(\"param.id\");\n".$biao2."if(!\$array){\n".$biao3."\$data['msg']='参数错误';return json(\$data);exit;\n".$biao2."}\n";
            $html.=$biao2."\$arr=explode(\",\",\$array);\n".$biao2."for(\$i=0;\$i<count(\$arr);\$i++){\n";
            $html.=$biao3."if(\$arr[\$i]){\n".$biao4."Db::name('".$table."')->where('merid',\$merid)->where('id',\$arr[\$i])->delete();\n".$biao4."\$text = '删除了".$title."id='.\$arr[\$i].'的数据';\n".$biao4."\$this->writeActionLog(\$text);\n".$biao3."}\n";
            $html.=$biao2."}\n".$biao2."\$data['status']=1;\n".$biao2."return json(\$data);\n";
            $html.=$biao1."}\n";

            $html.="\n} ";
            file_put_contents($path,$html);
        }else{ //如果已经有这个文件了  则直接在倒数第二行插入代码
            $html="";
            $biao1="\t";$biao2="\t\t";$biao3="\t\t\t";$biao4="\t\t\t\t";
            //生成列表文件
            $html.=$biao1."public function ".$action."(){\n ";
            $html.=$biao2."\$merid = \$this->getmerid();\n\t\tif(!\$merid){\n\t\t\t\$data['msg'] = '数据异常';\n\t\t\treturn json(\$data);\n\t\t\texit;\n\t\t}\n";
            $html.=$biao2."if(request()->isPost()){\n";
            $html.=$biao3."\$page=input('param.page');\n".$biao3."if(!\$page){\n".$biao4."\$page=1;\n".$biao3."}\n".$biao3."\$limit=input('param.limit');\n".$biao3."if(!\$limit){\n".$biao4."\$limit=10;//每页显示条数\n".$biao3."}\n";
            //搜索数据
            $html.=$biao3."\$where=[];\n\t\t\t\$where['merid'] = \$merid;\n";
            $html.=$biao3."\$id=input('param.id');\n".$biao3."if(\$id){\n".$biao4."\$where[]=['id','=',\$id];\n".$biao3."}\n";
            //查询产品
            $html.=$biao3."\$count = Db::name('".$table."')->where(\$where)->count();\n".$biao3."\$data=Db::name('".$table."')->where(\$where)->page(\$page,\$limit)->order('id desc')->select()->toArray();\n";
            //返回数据
            $html.=$biao3."\$res = [\"data\" => \$data,\"code\"=>0,'message'=>'请求成功','count'=>\$count];\n".$biao3."return json(\$res);\n";
            $html.=$biao2."}\n";
            //显示页面
            $html.=$biao2."\$name='".$action."';\n\t\t\$permis = \$this->getPermissions('".$model."/".$action."');\n\t\t\$actions = \$this->actions;\n\t\t\$assign=['data'=>\$actions,'permis'=>\$permis,'merid'=>\$merid];\n\t\thook('merhook', ['type'=>'view','name'=>\$name,'assign'=>\$assign]);\n";
            $html.=$biao1."}\n";

            //生成添加文件
            $html.=$biao1."public function add".$action."(){\n ";
            $html.=$biao2."\$merid = \$this->getmerid();\n\t\tif(!\$merid){\n\t\t\t\$data['msg'] = '数据异常';\n\t\t\treturn json(\$data);\n\t\t\texit;\n\t\t}\n";
            $html.=$biao2."if(request()->isPost()){\n";
            $html.=$biao3."\$data = array('status' => 0, 'msg' => '未知错误');\n";
            if($pararr){
                $arrtit=$biao3."\$arr=array(\n";
                for ($i=0;$i<count($pararr);$i++){
                    $fna=$pararr[$i]['title'];
                    $html.=$biao3.'$'.$fna."=input('param.".$fna."');\n";
                    $arrtit.=$biao4."'".$fna."'=>$".$fna.",\n";
                }
                $arrtit.=$biao4."'merid'=>\$merid,\n";
                $arrtit.=$biao3.");";
                $html.=$arrtit."\n";
            }
            $html.=$biao3."\$id=Db::name('".$table."')->insertGetId(\$arr);\n".$biao3."\$text = '添加了".$title."-id='.\$id;\n".$biao3."\$this->writeActionLog(\$text);\n".$biao3."\$data['status'] = 1;\n".$biao3."return json(\$data);exit;\n";
            $html.=$biao2."}\n";
            //显示页面
            $html.=$biao2."\$name='edit".$action."';\n\t\t\$assign=[];\n\t\thook('merhook', ['type'=>'view','name'=>\$name,'assign'=>\$assign]);\n";
            $html.=$biao1."}\n";

            //生成修改文件
            $html.=$biao1."public function edit".$action."(){\n ";
            $html.=$biao2."\$merid = \$this->getmerid();\n\t\tif(!\$merid){\n\t\t\t\$data['msg'] = '数据异常';\n\t\t\treturn json(\$data);\n\t\t\texit;\n\t\t}\n";
            $html.=$biao2."if(request()->isPost()){\n";
            $html.=$biao3."\$data = array('status' => 0, 'msg' => '未知错误');\n";
            if($pararr){
                $arrtit=$biao3."\$arr=array(\n";
                for ($i=0;$i<count($pararr);$i++){
                    $fna=$pararr[$i]['title'];
                    $html.=$biao3.'$'.$fna."=input('param.".$fna."');\n";
                    $arrtit.=$biao4."'".$fna."'=>$".$fna.",\n";
                }
                $arrtit.=$biao3.");";
                $html.=$arrtit."\n";
            }
            $html.=$biao3."\$id = input('param.id');\n".$biao3."if (\$id){\n";
            $html.=$biao4."\$id=Db::name('".$table."')->where('id',\$id)->where('merid',\$merid)->update(\$arr);\n".$biao4."\$text = '修改了".$title."-id='.\$id;\n".$biao4."\$this->writeActionLog(\$text);\n".$biao4."\$data['status'] = 1;\n".$biao4."return json(\$data);exit;\n";
            $html.=$biao3."}\n";
            $html.=$biao2."}\n";
            //显示页面
            $html.=$biao2."\$name='edit".$action."';\n\t\t\$assign=[];\n\t\thook('merhook', ['type'=>'view','name'=>\$name,'assign'=>\$assign]);\n";
            $html.=$biao1."}\n";

            //生成删除页面
            $html.=$biao1."public function del".$action."(){\n ";
            $html.=$biao2."\$merid = \$this->getmerid();\n\t\tif(!\$merid){\n\t\t\t\$data['msg'] = '数据异常';\n\t\t\treturn json(\$data);\n\t\t\texit;\n\t\t}\n";
            $html.=$biao2."\$data = array('status' => 0,'msg' => '未知错误');\n".$biao2."\$array=input(\"param.id\");\n".$biao2."if(!\$array){\n".$biao3."\$data['msg']='参数错误';return json(\$data);exit;\n".$biao2."}\n";
            $html.=$biao2."\$arr=explode(\",\",\$array);\n".$biao2."for(\$i=0;\$i<count(\$arr);\$i++){\n";
            $html.=$biao3."if(\$arr[\$i]){\n".$biao4."Db::name('".$table."')->where('merid',\$merid)->where('id',\$arr[\$i])->delete();\n".$biao4."\$text = '删除了".$title."id='.\$arr[\$i].'的数据';\n".$biao4."\$this->writeActionLog(\$text);\n".$biao3."}\n";
            $html.=$biao2."}\n".$biao2."\$data['status']=1;\n".$biao2."return json(\$data);\n";
            $html.=$biao1."}\n";

            //$html.="\n} ";

            //在文件末尾的第二行添加代码
            $handle = fopen($path, 'r+');
            $i = -1;
            $lastLine = '';
            while(true){
                fseek($handle, $i, SEEK_END);
                $char = fgetc($handle);
                if($char == "\n"){
                    fwrite($handle, $html. $lastLine);
                    break;
                }else{
                    $lastLine .= $char;
                }
                $i --;
            }

        }

        //生成静态文件
        $this->codehtml($model,$action,$pararr,$title,$table);
    }
    /**
     * 生成html文件
     **/
    protected function codehtml($model,$action,$pararr=[],$title,$table){
        //先判断列表文件
        $models=strtolower($model);
        $path2=app()->getRootPath().'/addons/merchants/view/'.$models.'/'.$action.'.html';
        $mk=app()->getRootPath().'/addons/merchants/view/'.$models;
        if(!file_exists($path2)){
            if(!is_dir($mk)){
                mkdir(iconv("UTF-8", "GBK", $mk),0777,true);
            }
            //顶部代码
            $html="<!DOCTYPE html>\n<html>\n<head>\n\t<meta charset=\"utf-8\">\n\t<title>管理</title>\n\t<meta name=\"renderer\" content=\"webkit\">\n\t<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\">\n\t<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0\">\n\t<link rel=\"stylesheet\" href=\"/admin/layuiadmin/layui/css/layui.css\" media=\"all\">\n\t<link rel=\"stylesheet\" href=\"/admin/layuiadmin/style/admin.css\" media=\"all\">\n\t<style>\n\t\t@font-face {\n\t\t\tfont-family: 'iconfont';  \n\t\t\tsrc: url('https://at.alicdn.com/t/font_2565954_q2snf28joy.woff2?t=1621766073287') format('woff2'),\n\t\t\turl('https://at.alicdn.com/t/font_2565954_q2snf28joy.woff?t=1621766073287') format('woff'),\n\t\t\turl('https://at.alicdn.com/t/font_2565954_q2snf28joy.ttf?t=1621766073287') format('truetype');\n\t\t}\n\t\t.iconfont{\n\t\t\tfont-family:\"iconfont\" !important;\n\t\t\tfont-size:16px;font-style:normal;\n\t\t\t-webkit-font-smoothing: antialiased;\n\t\t\t-webkit-text-stroke-width: 0.2px;\n\t\t\t-moz-osx-font-smoothing: grayscale;}\n\t\t.layui-layer-admin .layui-layer-ico {\n\t\t\tbackground: url(\"/admin/layuiadmin/gb.png\") no-repeat!important;\n\t\t\tbackground-size:16px 16px!important;\n\t\t}\n\t</style>\n</head>\n<body>\n";
            //中间代码
            $html.="<div class=\"layui-fluid\">\n\t<div class=\"layui-card\">\n\t\t<div class=\"layui-form layui-card-header layuiadmin-card-header-auto\">\n\t\t\t<div class=\"layui-form-item\">\n\t\t\t\t<div class=\"layui-inline\">\n\t\t\t\t\t<label class=\"layui-form-label\">ID</label>\n\t\t\t\t\t<div class=\"layui-input-block\">\n\t\t\t\t\t\t<input type=\"text\" name=\"id\" placeholder=\"请输入\" autocomplete=\"off\" class=\"layui-input\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"layui-inline\">\n\t\t\t\t\t<button class=\"layui-btn layuiadmin-btn-useradmin\" lay-submit lay-filter=\"LAY-user-front-search\">\n\t\t\t\t\t\t<i class=\"layui-icon layui-icon-search layuiadmin-button-btn\"></i>\n\t\t\t\t\t</button>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t\t<div class=\"layui-card-body\">\n\t\t\t<div style=\"padding-bottom: 10px;\">\n\t\t\t{in name=\"\$data.add".$action."\" value=\"\$permis\"}\n\t\t\t\t<button class=\"layui-btn layuiadmin-btn-useradmin\" data-type=\"add\">添加</button>\n\t\t\t{/in}\n\t\t\t{in name=\"\$data.del".$action."\" value=\"\$permis\"}\n\t\t\t\t<button class=\"layui-btn layuiadmin-btn-useradmin\" data-type=\"del\">删除</button>\n\t\t\t{/in}\n\t\t\t</div>\n\t\t\t<table class=\"layui-table\" lay-data=\"{ url:'/addons/merchants/".$model."/".$action."',method:'post', page:true, id:'LAY-user-managet'}\" lay-filter=\"LAY-user-managet\">\n\t\t\t<thead>\n\t\t\t\t<tr>\n";

            $html.="\t\t\t\t\t<th lay-data=\"{type: 'checkbox', fixed: 'left'}\"></th>\n";
            $html.="\t\t\t\t\t<th lay-data=\"{field:'id', width:110, sort: true}\">ID</th>\n";
            if($pararr){
                for ($i=0;$i<count($pararr);$i++){
                    $titlex=$pararr[$i]['title'];
                    $name=$pararr[$i]['name'];
                    $html.="\t\t\t\t\t<th lay-data=\"{field:'".$titlex."'}\">".$name."</th>\n";
                }
            }
            $html.="\t\t\t\t\t<th lay-data=\"{fixed: 'right', width:220, align:'center', toolbar: '#barDemo'}\">操作</th>\n\t\t\t\t</tr>\n\t\t\t</thead>\n\t\t\t</table>\n\t\t\t<script type=\"text/html\" id=\"barDemo\">\n\t\t\t{in name=\"\$data.edit".$action."\" value=\"\$permis\"}\n\t\t\t\t<a class=\"layui-btn layui-btn-normal layui-btn-xs\" lay-event=\"edit\"><i class=\"layui-icon layui-icon-edit\"></i>编辑</a>\n\t\t\t{/in}\n\t\t\t{in name=\"\$data.del".$action."\" value=\"\$permis\"}\n\t\t\t\t<a class=\"layui-btn layui-btn-danger layui-btn-xs\" lay-event=\"del\"><i class=\"layui-icon layui-icon-delete\"></i>删除</a>\n\t\t\t{/in}\n\t\t\t</script>\n\t\t</div>\n\t</div>\n</div>\n";
            //底部js部分
            $html.="<script src=\"/admin/layuiadmin/layui/layui.js\"></script>\n<script>\n";
            $html.="\tlayui.config({\n\t\tbase: '/admin/layuiadmin/' \n\t}).extend({\n\t\tindex: 'lib/index' \n\t}).use(['index', 'useradmin', 'table'], function(){\n\t\tvar $ = layui.$,form = layui.form,table = layui.table;\n";
            /**搜索**/
            $html.="\t\tform.on('submit(LAY-user-front-search)', function(data){\n\t\t\tvar field = data.field;\n\t\t\t$.post(\"{:url('/addons/merchants/".$model."/".$action."')}\", field, function(data,state){});\n\t\t\t//执行重载\n\t\t\ttable.reload('LAY-user-managet', {\n\t\t\t\twhere: field\n\t\t\t});\n\t\t});\n";
            /**操作按钮**/
            $html.="\t\ttable.on('tool(LAY-user-managet)', function(obj){\n\t\t\tvar data = obj.data; //获得当前行数据\n\t\t\tvar layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）\n\t\t\tvar tr = obj.tr; //获得当前行 tr 的DOM对象\n";
            $html.="\t\t\tif(layEvent === 'del'){ //删除\n\t\t\t\tlayer.confirm('真的删除当前数据吗', function(index){\n\t\t\t\t\tobj.del(); //删除对应行（tr）的DOM结构，并更新缓存\n\t\t\t\t\tlayer.close(index);\n\t\t\t\t\tvar dataid=data['id'];\n\t\t\t\t\t//向服务端发送删除指令\n\t\t\t\t\t$.post(\n\t\t\t\t\t\t\"{:url('/addons/merchants/".$model."/del".$action."')}\",\n\t\t\t\t\t\t{\"id\":dataid},\n\t\t\t\t\t\tfunction(data,state){\n\t\t\t\t\t\t\tif(state != \"success\"){\n\t\t\t\t\t\t\t\tlayer.msg(\"请求出错!\");\n\t\t\t\t\t\t\t}else if(data.status == 1){\n\t\t\t\t\t\t\t\ttable.reload('LAY-user-managet');\n\t\t\t\t\t\t\t\tlayer.msg('已删除');\n\t\t\t\t\t\t\t}else{\n\t\t\t\t\t\t\t\tlayer.msg(data.msg);\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t}\n\t\t\t\t\t);\n\t\t\t\t});\n\t\t\t}\n";
            /**修改**/
            $html.="\t\t\telse if(layEvent === 'edit'){\n\t\t\t\tvar id=data['id'];\n\t\t\t\tlayer.open({\n\t\t\t\t\ttype: 2\n\t\t\t\t\t,title: '修改".$title."'\n\t\t\t\t\t,content: 'edit".$action.".html'\n\t\t\t\t\t,maxmin: true\n\t\t\t\t\t,area: ['600px', '450px']\n\t\t\t\t\t,btn: ['确定', '取消']\n\t\t\t\t\t,success: function(layero,index){\n\t\t\t\t\t\tvar body = layui.layer.getChildFrame('body', index);\n\t\t\t\t\t\tbody.find(\"#id\").val(id);\n";
            if($pararr) {
                for ($i=0;$i<count($pararr);$i++) {
                    $titlex=$pararr[$i]['title'];//字段名
                    $style=$pararr[$i]['style'];
                    $val=$pararr[$i]['val'];//表单值
                    if($style==1) { //文本框
                        $html .= "\t\t\t\t\t\tbody.find(\"#" . $titlex . "\").val(data['" . $titlex . "']);\n";
                    }else if($style==2){ //单选框
                        $valarr=explode(',',$val);
                        if($valarr && count($valarr)>0){
                            for ($d=0;$d<count($valarr);$d++){
                                $html.="\t\t\t\t\t\tbody.find(\"input[name=" . $titlex . "][value=".$valarr[$d]."]\").attr(\"checked\", data['" . $titlex . "'] == ".$valarr[$d]." ? true : false);\n";
                            }
                        }else{
                            $html.="\t\t\t\t\t\tbody.find(\"input[name=" . $titlex . "][value=".$val."]\").attr(\"checked\", data['" . $titlex . "'] == ".$val." ? true : false);\n";
                        }
                    }
                }
            }
            $html.="\t\t\t\t\t\tform.render();\n\t\t\t\t\t}\n\t\t\t\t\t,yes: function(index, layero){\n\t\t\t\t\t\tvar iframeWindow = window['layui-layer-iframe'+ index],submitID = 'LAY-user-front-submit',submit = layero.find('iframe').contents().find('#'+ submitID);\n\t\t\t\t\t\t//监听提交\n\t\t\t\t\t\tiframeWindow.layui.form.on('submit('+ submitID +')', function(data){\n\t\t\t\t\t\t\tvar field = data.field; //获取提交的字段\n\t\t\t\t\t\t\t//提交 Ajax 成功后，静态更新表格中的数据\n\t\t\t\t\t\t\t//$.ajax({});\n\t\t\t\t\t\t\t$.post(\n\t\t\t\t\t\t\t\t\"{:url('/addons/merchants/".$model."/edit".$action."')}\",\n\t\t\t\t\t\t\t\tfield,\n\t\t\t\t\t\t\t\tfunction(data,state){\n\t\t\t\t\t\t\t\t\tif(state != \"success\"){\n\t\t\t\t\t\t\t\t\t\tlayer.msg(\"请求出错!\");\n\t\t\t\t\t\t\t\t\t}else if(data.status == 1){\n\t\t\t\t\t\t\t\t\t\tlayer.msg('修改成功');\n\t\t\t\t\t\t\t\t\t\ttable.reload('LAY-user-managet'); //数据刷新\n\t\t\t\t\t\t\t\t\t}else{\n\t\t\t\t\t\t\t\t\t\tlayer.msg(data.msg);\n\t\t\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t);\n\t\t\t\t\t\t\tlayer.close(index); //关闭弹层\n\t\t\t\t\t\t});\n\t\t\t\t\t\tsubmit.trigger('click');\n\t\t\t\t\t}\n\t\t\t\t});\n\t\t\t}\n";
            $html.="\t\t});\n";
            /**操作按钮结束**/

            /**事件**/
            $html.="\t\tvar active = {\n";
            /**添加**/
            $html.="\t\t\tadd:function(){\n\t\t\t\tlayer.open({\n\t\t\t\t\ttype: 2\n\t\t\t\t\t,title: '添加".$title."'\n\t\t\t\t\t,content: 'add".$action.".html'\n\t\t\t\t\t,maxmin: true\n\t\t\t\t\t,area: ['600px', '450px']\n\t\t\t\t\t,btn: ['确定', '取消']\n\t\t\t\t\t,yes: function(index, layero){\n\t\t\t\t\t\tvar iframeWindow = window['layui-layer-iframe'+ index]\n\t\t\t\t\t\t\t,submitID = 'LAY-user-front-submit'\n\t\t\t\t\t\t\t,submit = layero.find('iframe').contents().find('#'+ submitID);\n\t\t\t\t\t\t//监听提交\n\t\t\t\t\t\tiframeWindow.layui.form.on('submit('+ submitID +')', function(data){\n\t\t\t\t\t\t\tvar field = data.field; //获取提交的字段\n\t\t\t\t\t\t\t//提交 Ajax 成功后，静态更新表格中的数据\n\t\t\t\t\t\t\t//$.ajax({});\n\t\t\t\t\t\t\t$.post(\n\t\t\t\t\t\t\t\t\"{:url('/addons/merchants/".$model."/add".$action."')}\",\n\t\t\t\t\t\t\t\tfield,\n\t\t\t\t\t\t\t\tfunction(data,state){\n\t\t\t\t\t\t\t\t\tif(state != \"success\"){\n\t\t\t\t\t\t\t\t\t\tlayer.msg(\"请求出错!\");\n\t\t\t\t\t\t\t\t\t}else if(data.status == 1){\n\t\t\t\t\t\t\t\t\t\tlayer.msg('添加成功');\n\t\t\t\t\t\t\t\t\t\ttable.reload('LAY-user-managet'); //数据刷新\n\t\t\t\t\t\t\t\t\t}else{\n\t\t\t\t\t\t\t\t\t\tlayer.msg(data.msg);\n\t\t\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t);\n\t\t\t\t\t\t\tlayer.close(index); //关闭弹层\n\t\t\t\t\t\t});\n\t\t\t\t\t\tsubmit.trigger('click');\n\t\t\t\t\t}\n\t\t\t\t});\n\t\t\t},\n";
            /**删除**/
            $html.="\t\t\tdel: function(){\n\t\t\t\tvar checkStatus = table.checkStatus('LAY-user-managet'),checkData = checkStatus.data; //得到选中的数据\n\t\t\t\tif(checkData.length === 0){\n\t\t\t\t\treturn layer.msg('请选择数据');\n\t\t\t\t}\n\t\t\t\tvar dataid='';\n\t\t\t\tfor(var i=0;i<checkData.length;i++){\n\t\t\t\t\tdataid=dataid+','+checkData[i]['id'];\n\t\t\t\t}\n\t\t\t\tlayer.confirm('确定删除选中数据吗？', function(index) {\n\t\t\t\t\t$.post(\n\t\t\t\t\t\t\"{:url('/addons/merchants/".$model."/del".$action."')}\",\n\t\t\t\t\t\t{\"id\":dataid},\n\t\t\t\t\t\tfunction(data,state){\n\t\t\t\t\t\t\tif(state != \"success\"){\n\t\t\t\t\t\t\t\tlayer.msg(\"请求出错!\");\n\t\t\t\t\t\t\t}else if(data.status == 1){\n\t\t\t\t\t\t\t\ttable.reload('LAY-user-managet');\n\t\t\t\t\t\t\t\tlayer.msg('已删除');\n\t\t\t\t\t\t\t}else{\n\t\t\t\t\t\t\t\tlayer.msg(data.msg);\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t}\n\t\t\t\t\t);\n\t\t\t\t});\n\t\t\t}\n";
            $html.="\t\t}\n";
            /**事件结束**/
            $html.="\t\t$('.layui-btn.layuiadmin-btn-useradmin').on('click', function(){\n\t\t\tvar type = $(this).data('type');\n\t\t\tactive[type] ? active[type].call(this) : '';\n\t\t});\n";
            $html.="\t});\n";
            $html.="</script>\n</body>\n</html>";
            file_put_contents($path2,$html);
        }

        //生成修改文件
        $path3=app()->getRootPath().'/addons/merchants/view/'.$model.'/edit'.$action.'.html';
        $mk=app()->getRootPath().'/addons/merchants/view/'.$model;
        if(!file_exists($path3)) {
            if (!is_dir($mk)) {
                mkdir(iconv("UTF-8", "GBK", $mk), 0777, true);
            }
            /**顶部代码**/
            $html="<!DOCTYPE html>\n<html>\n<head>\n\t<meta charset=\"utf-8\">\n\t<title>管理</title>\n\t<meta name=\"renderer\" content=\"webkit\">\n\t<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\">\n\t<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0\">\n\t<link rel=\"stylesheet\" href=\"/admin/layuiadmin/layui/css/layui.css\" media=\"all\">\n\t<link rel=\"stylesheet\" href=\"/admin/css/u-ui.css\" media=\"all\">\n</head>\n<body>\n";
            /**中间部分代码**/
            $html.="<div class=\"layui-form\" lay-filter=\"layuiadmin-form-useradmin\" id=\"layuiadmin-form-useradmin\" style=\"padding: 20px 0 0 0;\">\n\t<input type=\"hidden\" name=\"id\" id=\"id\" value=\"\" />\n\t";
            if($pararr) {
                for ($i=0;$i<count($pararr);$i++) {
                    $name=$pararr[$i]['name'];
                    $titlex=$pararr[$i]['title'];//字段名
                    $style=$pararr[$i]['style'];
                    $val=$pararr[$i]['val'];//表单值
                    if($style==1){ //文本框
                        $html.="<div class=\"layui-form-item\">\n\t\t<label class=\"layui-form-label\">".$name."</label>\n\t\t<div class=\"layui-input-inline\">\n\t\t\t<input type=\"text\" name=\"".$titlex."\" id=\"".$titlex."\" lay-verify=\"required\"  placeholder=\"".$name."\" autocomplete=\"off\" class=\"layui-input\">\n\t\t</div>\n\t</div>\n\t";
                    }else if($style==2){
                        $valarr=explode(',',$val);
                        if($valarr && count($valarr)>0){
                            $html.="<div class=\"layui-form-item\" lay-filter=\"status\">\n\t\t<label class=\"layui-form-label\">".$name."</label>\n\t\t<div class=\"layui-input-block\">\n";
                            for ($d=0;$d<count($valarr);$d++){
                                $html.="\t\t\t<input type=\"radio\" name=\"".$titlex."\" checked  id=\"".$titlex."\" value=\"".$valarr[$d]."\" title=\"上线\">\n";
                            }
                            $html.="\t\t</div>\n\t</div>\n\t";
                        }else{
                            $html.="<div class=\"layui-form-item\" lay-filter=\"status\">\n\t\t<label class=\"layui-form-label\">".$name."</label>\n\t\t<div class=\"layui-input-block\">\n";
                            $html.="\t\t\t<input type=\"radio\" name=\"".$titlex."\" checked  id=\"".$titlex."\" value=\"".$val."\" title=\"上线\">\n";
                            $html.="\t\t</div>\n\t</div>\n\t";
                        }
                    }
                }
            }
            $html.="\n\t<div class=\"layui-form-item layui-hide\">\n\t\t<input type=\"button\" lay-submit lay-filter=\"LAY-user-front-submit\" id=\"LAY-user-front-submit\" value=\"确认\">\n\t</div>\n</div>\n";
            $html.="<script src=\"/admin/layuiadmin/layui/layui.js\"></script>\n<script>\n";
            $html.="\tlayui.config({\n\t\tbase: '/admin/layuiadmin/' \n\t}).extend({\n\t\tindex: 'lib/index' \n\t}).use(['index', 'useradmin', 'table','upload'], function(){\n\t\tvar $ = layui.$,form = layui.form,upload = layui.upload,table = layui.table;\n";
            $html.="\t});\n";
            $html.="</script>\n</body>\n</html>";
            file_put_contents($path3,$html);
        }
    }
    /**顶级栏目生成**/
    public function topnav(){
        if(request()->isPost()){
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
            $where[]=['parent_id','=',0];
            $count = Db::name('general_admin_permission')->where($where)->count();
            $data=Db::name('general_admin_permission')->where($where)->page($page,$limit)->order('id desc')->select()->toArray();
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);exit;
        }
        $name='topnav';
        $permis = $this->getPermissions('Autocurd/topnav');
        $actions = $this->actions;
        $assign=['data'=>$actions,'permis'=>$permis];
        hook('develohook', ['type'=>'view','name'=>$name,'assign'=>$assign]);
    }
    public function addtopnav(){
        if(request()->isPost()){
            $data = array('status' => 0, 'msg' => '未知错误');
            $name=input('param.name');
            $controllers=input('param.models');
            $icon=input('param.icon');
            $p_id=input('param.p_id');
            $type=input('param.type');
            $addons=input('param.addons');
            $arr=array(
                'name'=>$name,
                'controller'=>$controllers,
                'parent_id'=>0,
                'is_nav'=>1,
                'icon'=>$icon,
                'p_id'=>$p_id,
                'type'=>$type,
                'addons'=>$addons,
            );
            $id=Db::name('general_admin_permission')->insertGetId($arr);
            $text = '添加了顶级栏目生成-id='.$id;
            $this->writeActionLog($text);
            $data['status'] = 1;
            return json($data);exit;
        }
        $name='edittopnav';
        $assign=[];
        hook('develohook', ['type'=>'view','name'=>$name,'assign'=>$assign]);
    }
    public function edittopnav(){
        if(request()->isPost()){
            $data = array('status' => 0, 'msg' => '未知错误');
            $name=input('param.name');
            $controllers=input('param.models');
            $icon=input('param.icon');
            $p_id=input('param.p_id');
            $type=input('param.type');
            $addons=input('param.addons');
            $arr=array(
                'name'=>$name,
                'controller'=>$controllers,
                'icon'=>$icon,
                'p_id'=>$p_id,
                'type'=>$type,
                'addons'=>$addons,
            );
            $id = input('param.id');
            if ($id){
                $id=Db::name('general_admin_permission')->where('id',$id)->update($arr);
                $text = '修改了顶级栏目生成-id='.$id;
                $this->writeActionLog($text);
                $data['status'] = 1;
                return json($data);exit;
            }
        }
        $name='edittopnav';
        $assign=[];
        hook('develohook', ['type'=>'view','name'=>$name,'assign'=>$assign]);
    }
    public function deltopnav(){
        $data = array('status' => 0,'msg' => '未知错误');
        $array=input("param.id");
        if(!$array){
            $data['msg']='参数错误';return json($data);exit;
        }
        $arr=explode(",",$array);
        for($i=0;$i<count($arr);$i++){
            if($arr[$i]){
                Db::name('general_admin_permission')->where('parent_id',0)->where('id',$arr[$i])->delete();
                $text = '删除了顶级栏目生成id='.$arr[$i].'的数据';
                $this->writeActionLog($text);
            }
        }
        $data['status']=1;
        return json($data);
    }



    /**二级栏目生成**/
    /**一键生成curd**/
    public function curdlist(){
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
            $title=input('param.title');
            if($title){
                $where[]=['title','like','%'.$title.'%'];
            }
            $limit=input("param.limit");
            if(!$limit){
                $limit=10;//每页显示条数
            }
            //查询产品
            $count = Db::name('ohyueo_develo_curdlist')
                ->where($where)
                ->count();
            $data=Db::name('ohyueo_develo_curdlist')
                ->where($where)
                ->page($page,$limit)
                ->order('id desc')
                ->select()->toArray();
            if($data){
                for($i=0;$i<count($data);$i++){
                    $id=$data[$i]['id'];//
                    $data[$i]['no']=0;
                }
            }
            $res = ["data" => $data,"code"=>0,'message'=>'请求成功','count'=>$count];
            return json($res);exit;
        }
        $name='curdlist';
        $permis = $this->getPermissions('Autocurd/curdlist');
        $actions = $this->actions;
        $assign=['data'=>$actions,'permis'=>$permis];
        hook('develohook', ['type'=>'view','name'=>$name,'assign'=>$assign]);
    }
    public function addcurd(){
        if(request()->isPost()){
            $title=input('param.title');
            $model=input('param.model');
            $action=input('param.actions');
            $table=input('param.table');
            $beizhu=input('param.beizhus');
            $navid=input('param.navid');//一级菜单id
            $role_type=input('param.role_type');//生成权限
            $daochu=input('param.daochu');//导出
            if(!$table || $table=='general_'){
                $table='general_'.$model.'_'.$action;
                $table=strtolower($table);
            }
            $listid=0;
            $isc=Db::name('ohyueo_develo_curdlist')->where('model',$model)->where('action',$action)->find();
            if(!$isc){
                $arr=array(
                    'title'=>$title,'model'=>$model,'action'=>$action,'beizhu'=>$beizhu,'navid'=>$navid,'role_type'=>$role_type,
                    'table'=>$table,'addtime'=>gettime(),'daochu'=>$daochu
                );
                $listid=Db::name('ohyueo_develo_curdlist')->insertGetId($arr);
            }
            $name=input('param.name');
            $type=input('param.type');
            $fid=input('param.fid');
            $length=input('param.length');
            $default=input('param.default');
            $style=input('param.style');
            $val=input('param.val');
            $beizhux=input('param.beizhu');
            $search=input('param.search');
            $newarr=[];//生成数据
            if($listid && $table && count($name)>0){
                $sql = "CREATE TABLE `".$table."` ( `id` int(11) NOT NULL AUTO_INCREMENT,";
                for($i=0;$i<count($name);$i++){
                    $gid=$fid[$i];
                    $iseach=0;
                    if(isset($search[$i]) && $search[$i]=='on'){
                        $iseach=1;
                    }
                    array_push($newarr,array('title'=>$name[$i],'type'=>$type[$i],'name'=>$beizhux[$i],'style' => $style[$i],'val' => $val[$i],'search'=>$iseach));
                    $arr=array(
                        'listid' => $listid,
                        'name'  => $name[$i],
                        'type' => $type[$i],
                        'length' => $length[$i],
                        'default' => $default[$i],
                        'style' => $style[$i],
                        'val' => $val[$i],
                        'beizhu' => $beizhux[$i],
                        'search' => $iseach,
                    );
                    if($gid){//修改
                        Db::name('ohyueo_develo_curdtable')->where('id',$gid)->update($arr);
                        $data['msg']='修改成功';
                        $data['status']=1;
                        $text = '修改了curd表信息 id='.$gid;
                        $this->writeActionLog($text);
                    }else{//添加
                        $gid=Db::name('ohyueo_develo_curdtable')->insertGetId($arr);
                        $text = '添加了curd表信息id='.$gid;
                        $this->writeActionLog($text);
                        $data['msg']='添加成功';
                        $data['status']=1;
                    }
                    //拼接sql
                    if($type[$i]==1){
                        $moren=$default[$i]?:'NULL';
                        $sql.="`".$name[$i]."` varchar(".$length[$i].") DEFAULT '".$moren."' COMMENT '".$beizhux[$i]."',";
                    }else if($type[$i]==2){
                        $moren=$default[$i]?$default[$i]:'0';
                        $sql.="`".$name[$i]."` int(".$length[$i].") DEFAULT '".$moren."' COMMENT '".$beizhux[$i]."',";
                    }else if($type[$i]==3){
                        $moren=$default[$i]?:'NULL';
                        $sql.="`".$name[$i]."` text COMMENT '".$beizhux[$i]."',";
                    }else if($type[$i]==4){
                        $moren=$default[$i]?:'0';
                        $sql.="`".$name[$i]."` decimal(".$length[$i].") DEFAULT '".$moren."' COMMENT '".$beizhux[$i]."',";
                    }else if($type[$i]==5){
                        $moren='NULL';
                        $sql.="`".$name[$i]."` date DEFAULT ".$moren." COMMENT '".$beizhux[$i]."',";
                    }else if($type[$i]==6){
                        $moren='NULL';
                        $sql.="`".$name[$i]."` datetime DEFAULT ".$moren." COMMENT '".$beizhux[$i]."',";
                    }
                }
                $sql.=" PRIMARY KEY (`id`) ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='".$beizhu."';";
                Db::query($sql);//创建数据库
                //写入sql到文件
                @file_put_contents('../auto.sql', $sql, FILE_APPEND);
            }

            if($role_type==1){
                //先生成权限表数据
                $ro=Db::name('general_admin_permission')->where('parent_id',$navid)->where('controller',$model)->where('action',$action)->find();
                if(!$ro){
                    $roarr=array(
                        'name' => $title,
                        'controller' => $model,
                        'action' => $action,
                        'parent_id' => $navid,
                        'is_nav'=>1,
                        'icon' => '',
                        'p_id' => 0,
                        'type' => 1,
                        'addons' => ''
                    );
                    $rid=Db::name('general_admin_permission')->insertGetId($roarr);
                    //添加  修改  删除
                    $roarr=array(
                        'name' => '添加',
                        'controller' => $model.'/'.$action,
                        'action' => 'add'.$action,
                        'parent_id' => $rid,
                        'is_nav'=>0,
                        'icon' => '',
                        'p_id' => 0,
                        'type' => 1,
                        'addons' => ''
                    );
                    Db::name('general_admin_permission')->insert($roarr);
                    //修改
                    $roarr=array(
                        'name' => '修改',
                        'controller' => $model.'/'.$action,
                        'action' => 'edit'.$action,
                        'parent_id' => $rid,
                        'is_nav'=>0,
                        'icon' => '',
                        'p_id' => 0,
                        'type' => 1,
                        'addons' => ''
                    );
                    Db::name('general_admin_permission')->insert($roarr);
                    //删除
                    $roarr=array(
                        'name' => '删除',
                        'controller' => $model.'/'.$action,
                        'action' => 'del'.$action,
                        'parent_id' => $rid,
                        'is_nav'=>0,
                        'icon' => '',
                        'p_id' => 0,
                        'type' => 1,
                        'addons' => ''
                    );
                    Db::name('general_admin_permission')->insert($roarr);
                }
            }
            //生成php文件
            $this->modelaction($model,$action,$newarr,$title,$table,$daochu);

            $data['status']=1;
            $data['msg']='添加成功';
            return json($data);exit;
        }
        //查询所有一级菜单
        $navlist=Db::name('general_admin_permission')->where('parent_id',0)->select()->toArray();
        $name='addcurd';
        $assign=['navlist'=>$navlist,'list'=>[]];
        hook('develohook', ['type'=>'view','name'=>$name,'assign'=>$assign]);
    }
    public function editcurd(){
        if(request()->isPost()){
            $title=input('param.title');
            $model=input('param.model');
            $action=input('param.actions');
            $table=input('param.table');
            $beizhu=input('param.beizhus');
            $navid=input('param.navid');//一级菜单id
            $role_type=input('param.role_type');//生成权限
            $daochu=input('param.daochu');//导出
            if(!$table || $table=='general_'){
                $table='general_'.$model.'_'.$action;
                $table=strtolower($table);
            }

            $listid=0;
            $isc=Db::name('ohyueo_develo_curdlist')->where('model',$model)->where('action',$action)->find();
            if(!$isc){
                $arr=array(
                    'title'=>$title,'model'=>$model,'action'=>$action,'beizhu'=>$beizhu,'navid'=>$navid,'role_type'=>$role_type,
                    'table'=>$table,'addtime'=>gettime(),'daochu'=>$daochu
                );
                $listid=Db::name('ohyueo_develo_curdlist')->insertGetId($arr);
            }else{
                $listid=$isc['id'];
                $arr=array(
                    'title'=>$title,'model'=>$model,'action'=>$action,'beizhu'=>$beizhu,'navid'=>$navid,'role_type'=>$role_type,
                    'table'=>$table,'addtime'=>gettime(),'daochu'=>$daochu
                );
                Db::name('ohyueo_develo_curdlist')->where('id',$listid)->update($arr);
            }
            $name=input('param.name');
            $type=input('param.type');
            $fid=input('param.fid');
            $length=input('param.length');
            $default=input('param.default');
            $style=input('param.style');
            $val=input('param.val');
            $beizhux=input('param.beizhu');
            $search=input('param.search');
            $newarr=[];//生成数据
            if(count($name)>0){
                /*如果table存在则拼接sql语句*/
                if($table){
                    $sql = "CREATE TABLE `".$table."` ( `id` int(11) NOT NULL AUTO_INCREMENT,";
                }
                for($i=0;$i<count($name);$i++){
                    $gid=$fid[$i];
                    $iseach=0;
                    if(isset($search[$i]) && $search[$i]=='on'){
                        $iseach=1;
                    }
                    array_push($newarr,array('title'=>$name[$i],'type'=>$type[$i],'name'=>$beizhux[$i],'style' => $style[$i],'val' => $val[$i],'search'=>$iseach));
                    $arr=array(
                        'listid' => $listid,
                        'name'  => $name[$i],
                        'type' => $type[$i],
                        'length' => $length[$i],
                        'default' => $default[$i],
                        'style' => $style[$i],
                        'val' => $val[$i],
                        'beizhu' => $beizhux[$i],
                        'search' => $iseach,
                    );
                    if($gid){//修改
                        Db::name('ohyueo_develo_curdtable')->where('id',$gid)->update($arr);
                        $data['msg']='修改成功';
                        $data['status']=1;
                        $text = '修改了curd表信息 id='.$gid;
                        $this->writeActionLog($text);
                    }else{//添加
                        $gid=Db::name('ohyueo_develo_curdtable')->insertGetId($arr);
                        $text = '添加了curd表信息id='.$gid;
                        $this->writeActionLog($text);
                        $data['msg']='添加成功';
                        $data['status']=1;
                    }
                    if($table){
                        //拼接sql
                        if($type[$i]==1){
                            $moren=$default[$i]?:'NULL';
                            $sql.="`".$name[$i]."` varchar(".$length[$i].") DEFAULT '".$moren."' COMMENT '".$beizhux[$i]."',";
                        }else if($type[$i]==2){
                            $moren=$default[$i]?$default[$i]:'0';
                            $sql.="`".$name[$i]."` int(".$length[$i].") DEFAULT '".$moren."' COMMENT '".$beizhux[$i]."',";
                        }else if($type[$i]==3){
                            $moren=$default[$i]?:'NULL';
                            $sql.="`".$name[$i]."` text COMMENT '".$beizhux[$i]."',";
                        }else if($type[$i]==4){
                            $moren=$default[$i]?:'0';
                            $sql.="`".$name[$i]."` decimal(".$length[$i].") DEFAULT '".$moren."' COMMENT '".$beizhux[$i]."',";
                        }else if($type[$i]==5){
                            $moren='NULL';
                            $sql.="`".$name[$i]."` date DEFAULT ".$moren." COMMENT '".$beizhux[$i]."',";
                        }else if($type[$i]==6){
                            $moren='NULL';
                            $sql.="`".$name[$i]."` datetime DEFAULT ".$moren." COMMENT '".$beizhux[$i]."',";
                        }
                    }
                }
                if($table){
                    $sql.=" PRIMARY KEY (`id`) ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='".$beizhu."';";
                    //如果数据库库不存在则创建
                    $isc=Db::query("show tables like '".$table."'");
                    if(!$isc){
                        Db::query($sql);//创建数据库
                    }else{
                        //如果存在则不处理  避免数据丢失
                    }
                }
                
            }else{
                for($i=0;$i<count($name);$i++) {
                    array_push($newarr, array('title' => $name[$i], 'type' => $type[$i], 'name' => $beizhux[$i], 'style' => $style[$i], 'val' => $val[$i],'search'=>$search[$i]));
                }
            }
            //生成php文件
            $this->modelaction($model,$action,$newarr,$title,$table,$daochu);
  
            $data['status']=1;
            $data['msg']='添加成功';
            return json($data);exit;
        }
        $id=input('param.id/d');
        //查询所有一级菜单
        $navlist=Db::name('general_admin_permission')->where('parent_id',0)->select()->toArray();
        //查询添加的字段
        $list=Db::name('ohyueo_develo_curdtable')->where('listid',$id)->select()->toArray();
        $name='addcurd';
        $assign=['navlist'=>$navlist,'list'=>$list];
        hook('develohook', ['type'=>'view','name'=>$name,'assign'=>$assign]);
    }
    protected function modelaction($model,$action,$pararr=[],$title,$table,$daochu=0){
        //判断是否有 此文件 php文件
        $path=app()->getRootPath().'/app/admin/controller/'.$model.'.php';
        if(!file_exists($path)){ //没有此文件 则生成
            $html="<?php \n/**\n*  * 系统-受国家计算机软件著作权保护 - !\n* =========================================================\n* Copy right 2018-2025 成都海之心科技有限公司, 保留所有权利。\n* ----------------------------------------------\n* 官方网址: http://www.ohyu.cn\n* 这不是一个自由软件！在未得到官方有效许可的前提下禁止对程序代码进行修改和使用。\n* 任何企业和个人不允许对程序代码以任何形式任何目的再发布。\n* =========================================================\n* User: ohyueo\n* Date: ".(date('Y/m/d'))."\n* Time: ".(date('H:i'))."\n*/\nnamespace app\admin\controller;\nuse think\\facade\View;\nuse think\\facade\Db;\nuse app\admin\\traits\AdminAuth;\nuse PHPExcel_IOFactory;\nuse PHPExcel;
            \nclass ".$model." extends Common{\n\tuse AdminAuth;\n\n ";

            $biao1="\t";$biao2="\t\t";$biao3="\t\t\t";$biao4="\t\t\t\t";
            //生成列表文件
            $html.=$biao1."public function ".$action."(){\n ";
            //接收dao这个参数
            $html.=$biao2."\$dao=input('param.dao');\n";
            $html.=$biao2."if(request()->isPost() || \$dao==1){\n";
            $html.=$biao3."\$page=input('param.page');\n".$biao3."if(!\$page){\n".$biao4."\$page=1;\n".$biao3."}\n".$biao3."\$limit=input('param.limit');\n".$biao3."if(!\$limit){\n".$biao4."\$limit=10;//每页显示条数\n".$biao3."}\n";
            //搜索数据
            $html.=$biao3."\$where=[];\n";
            $html.=$biao3."\$id=input('param.id');\n".$biao3."if(\$id){\n".$biao4."\$where[]=['id','=',\$id];\n".$biao3."}\n";
            //循环生成搜索条件
            if($pararr){
                for ($i=0;$i<count($pararr);$i++){
                    $fna=$pararr[$i]['title'];
                    $search=$pararr[$i]['search'];
                    if($search=='on' || $search==1){
                        $html.=$biao3.'$'.$fna."=input('param.".$fna."');\n";
                        $html.=$biao3."if(\$".$fna."){\n".$biao4."\$where[]=['".$fna."','like','%'.\$".$fna.".'%'];\n".$biao3."}\n";
                    }
                }
            }
            //查询产品
            $html.=$biao3."\$count = Db::name('".$table."')->where(\$where)->count();\n".$biao3."\$data=Db::name('".$table."')->where(\$where)->page(\$page,\$limit)->order('id desc')->select()->toArray();\n";
            //返回数据
            $html.=$biao3."\$res = [\"data\" => \$data,\"code\"=>0,'message'=>'请求成功','count'=>\$count];\n".$biao3."return json(\$res);\n";
            $html.=$biao2."}\n";
            //权限
            $html.=$biao2."\$permis = \$this->getPermissions('".$model."/".$action."');\n".$biao2."View::assign('data', \$this->actions);\n".$biao2."View::assign('permis', \$permis);\n";
            //显示页面
            $html.=$biao2."return View::fetch();\n";
            $html.=$biao1."}\n";

            //生成添加文件
            $html.=$biao1."public function add".$action."(){\n ";
            $html.=$biao2."if(request()->isPost()){\n";
            $html.=$biao3."\$data = array('status' => 0, 'msg' => '未知错误');\n";
            if($pararr){
                $arrtit=$biao3."\$arr=array(\n";
                for ($i=0;$i<count($pararr);$i++){
                    $fna=$pararr[$i]['title'];
                    $html.=$biao3.'$'.$fna."=input('param.".$fna."');\n";
                    $arrtit.=$biao4."'".$fna."'=>$".$fna.",\n";
                }
                $arrtit.=$biao3.");";
                $html.=$arrtit."\n";
            }
            $html.=$biao3."\$id=Db::name('".$table."')->insertGetId(\$arr);\n".$biao3."\$text = '添加了".$title."-id='.\$id;\n".$biao3."\$this->writeActionLog(\$text);\n".$biao3."\$data['status'] = 1;\n".$biao3."return json(\$data);exit;\n";
            $html.=$biao2."}\n";
            //显示页面
            $html.=$biao2."return View::fetch('edit".$action."');\n";
            $html.=$biao1."}\n";

            //生成修改文件
            $html.=$biao1."public function edit".$action."(){\n ";
            $html.=$biao2."if(request()->isPost()){\n";
            $html.=$biao3."\$data = array('status' => 0, 'msg' => '未知错误');\n";
            if($pararr){
                $arrtit=$biao3."\$arr=array(\n";
                for ($i=0;$i<count($pararr);$i++){
                    $fna=$pararr[$i]['title'];
                    $html.=$biao3.'$'.$fna."=input('param.".$fna."');\n";
                    $arrtit.=$biao4."'".$fna."'=>$".$fna.",\n";
                }
                $arrtit.=$biao3.");";
                $html.=$arrtit."\n";
            }
            $html.=$biao3."\$id = input('param.id');\n".$biao3."if (\$id){\n";
            $html.=$biao4."\$id=Db::name('".$table."')->where('id',\$id)->update(\$arr);\n".$biao4."\$text = '修改了".$title."-id='.\$id;\n".$biao4."\$this->writeActionLog(\$text);\n".$biao4."\$data['status'] = 1;\n".$biao4."return json(\$data);exit;\n";
            $html.=$biao3."}\n";
            $html.=$biao2."}\n";
            //显示页面
            $html.=$biao2."return View::fetch('edit".$action."');\n";
            $html.=$biao1."}\n";

            //生成删除页面
            $html.=$biao1."public function del".$action."(){\n ";
            $html.=$biao2."\$data = array('status' => 0,'msg' => '未知错误');\n".$biao2."\$array=input(\"param.id\");\n".$biao2."if(!\$array){\n".$biao3."\$data['msg']='参数错误';return json(\$data);exit;\n".$biao2."}\n";
            $html.=$biao2."\$arr=explode(\",\",\$array);\n".$biao2."for(\$i=0;\$i<count(\$arr);\$i++){\n";
            $html.=$biao3."if(\$arr[\$i]){\n".$biao4."Db::name('".$table."')->where('id',\$arr[\$i])->delete();\n".$biao4."\$text = '删除了".$title."id='.\$arr[\$i].'的数据';\n".$biao4."\$this->writeActionLog(\$text);\n".$biao3."}\n";
            $html.=$biao2."}\n".$biao2."\$data['status']=1;\n".$biao2."return json(\$data);\n";
            $html.=$biao1."}\n";

            $html.="\n} ";
            file_put_contents($path,$html);
        }else{ //如果已经有这个文件了  则直接在倒数第二行插入代码
            $html="";
            $biao1="\t";$biao2="\t\t";$biao3="\t\t\t";$biao4="\t\t\t\t";
            //生成列表文件
            $html.=$biao1."public function ".$action."(){\n ";
            //接收dao这个参数
            $html.=$biao2."\$dao=input('param.dao');\n";
            $html.=$biao2."if(request()->isPost() || \$dao==1){\n";
            $html.=$biao3."\$page=input('param.page');\n".$biao3."if(!\$page){\n".$biao4."\$page=1;\n".$biao3."}\n".$biao3."\$limit=input('param.limit');\n".$biao3."if(!\$limit){\n".$biao4."\$limit=10;//每页显示条数\n".$biao3."}\n";
            //搜索数据
            $html.=$biao3."\$where=[];\n";
            $html.=$biao3."\$id=input('param.id');\n".$biao3."if(\$id){\n".$biao4."\$where[]=['id','=',\$id];\n".$biao3."}\n";
            //循环生成搜索条件
            if($pararr){
                for ($i=0;$i<count($pararr);$i++){
                    $fna=$pararr[$i]['title'];
                    $search=$pararr[$i]['search'];
                    if($search=='on' || $search==1){
                        $html.=$biao3.'$'.$fna."=input('param.".$fna."');\n";
                        $html.=$biao3."if(\$".$fna."){\n".$biao4."\$where[]=['".$fna."','like','%'.\$".$fna.".'%'];\n".$biao3."}\n";
                    }
                }
            }
            //查询产品
            $html.=$biao3."\$count = Db::name('".$table."')->where(\$where)->count();\n";
            //如果dao=1则不查询分页数据
            $html.=$biao3."if(\$dao!=1){\n".$biao4."\$data=Db::name('".$table."')->where(\$where)->page(\$page,\$limit)->order('id desc')->select()->toArray();\n".$biao3."}\n";
            //如果dao=1则查询全部数据
            $html.=$biao3."if(\$dao==1){\n".$biao4."\$data=Db::name('".$table."')->where(\$where)->order('id desc')->select()->toArray();\n".$biao3."}\n";
            //$html.=$biao3."\$data=Db::name('".$table."')->where(\$where)->page(\$page,\$limit)->order('id desc')->select()->toArray();\n";
            //返回数据
            $html.=$biao3."\$res = [\"data\" => \$data,\"code\"=>0,'message'=>'请求成功','count'=>\$count];\n".$biao3."return json(\$res);\n";
            $html.=$biao2."}\n";
            //权限
            $html.=$biao2."\$permis = \$this->getPermissions('".$model."/".$action."');\n".$biao2."View::assign('data', \$this->actions);\n".$biao2."View::assign('permis', \$permis);\n";
            //显示页面
            $html.=$biao2."return View::fetch();\n";
            $html.=$biao1."}\n";

            //生成添加文件
            $html.=$biao1."public function add".$action."(){\n ";
            $html.=$biao2."if(request()->isPost()){\n";
            $html.=$biao3."\$data = array('status' => 0, 'msg' => '未知错误');\n";
            if($pararr){
                $arrtit=$biao3."\$arr=array(\n";
                for ($i=0;$i<count($pararr);$i++){
                    $fna=$pararr[$i]['title'];
                    $html.=$biao3.'$'.$fna."=input('param.".$fna."');\n";
                    $arrtit.=$biao4."'".$fna."'=>$".$fna.",\n";
                }
                $arrtit.=$biao3.");";
                $html.=$arrtit."\n";
            }
            $html.=$biao3."\$id=Db::name('".$table."')->insertGetId(\$arr);\n".$biao3."\$text = '添加了".$title."-id='.\$id;\n".$biao3."\$this->writeActionLog(\$text);\n".$biao3."\$data['status'] = 1;\n".$biao3."return json(\$data);exit;\n";
            $html.=$biao2."}\n";
            //显示页面
            $html.=$biao2."return View::fetch('edit".$action."');\n";
            $html.=$biao1."}\n";

            //生成修改文件
            $html.=$biao1."public function edit".$action."(){\n ";
            $html.=$biao2."if(request()->isPost()){\n";
            $html.=$biao3."\$data = array('status' => 0, 'msg' => '未知错误');\n";
            if($pararr){
                $arrtit=$biao3."\$arr=array(\n";
                for ($i=0;$i<count($pararr);$i++){
                    $fna=$pararr[$i]['title'];
                    $html.=$biao3.'$'.$fna."=input('param.".$fna."');\n";
                    $arrtit.=$biao4."'".$fna."'=>$".$fna.",\n";
                }
                $arrtit.=$biao3.");";
                $html.=$arrtit."\n";
            }
            $html.=$biao3."\$id = input('param.id');\n".$biao3."if (\$id){\n";
            $html.=$biao4."\$id=Db::name('".$table."')->where('id',\$id)->update(\$arr);\n".$biao4."\$text = '修改了".$title."-id='.\$id;\n".$biao4."\$this->writeActionLog(\$text);\n".$biao4."\$data['status'] = 1;\n".$biao4."return json(\$data);exit;\n";
            $html.=$biao3."}\n";
            $html.=$biao2."}\n";
            //显示页面
            $html.=$biao2."return View::fetch('edit".$action."');\n";
            $html.=$biao1."}\n";

            //生成删除页面
            $html.=$biao1."public function del".$action."(){\n ";
            $html.=$biao2."\$data = array('status' => 0,'msg' => '未知错误');\n".$biao2."\$array=input(\"param.id\");\n".$biao2."if(!\$array){\n".$biao3."\$data['msg']='参数错误';return json(\$data);exit;\n".$biao2."}\n";
            $html.=$biao2."\$arr=explode(\",\",\$array);\n".$biao2."for(\$i=0;\$i<count(\$arr);\$i++){\n";
            $html.=$biao3."if(\$arr[\$i]){\n".$biao4."Db::name('".$table."')->where('id',\$arr[\$i])->delete();\n".$biao4."\$text = '删除了".$title."id='.\$arr[\$i].'的数据';\n".$biao4."\$this->writeActionLog(\$text);\n".$biao3."}\n";
            $html.=$biao2."}\n".$biao2."\$data['status']=1;\n".$biao2."return json(\$data);\n";
            $html.=$biao1."}\n";

            //在文件末尾的第二行添加代码
            $handle = fopen($path, 'r+');
            $i = -1;
            $lastLine = '';
            while(true){
                fseek($handle, $i, SEEK_END);
                $char = fgetc($handle);
                if($char == "\n"){
                    fwrite($handle, $html. $lastLine);
                    break;
                }else{
                    $lastLine .= $char;
                }
                $i --;
            }
            //file_put_contents($path,$html);
        }

        //生成静态文件
        $this->generatehtml($model,$action,$pararr,$title,$table,$daochu);
    }
    /**
     * 生成html文件
     **/
    protected function generatehtml($model,$action,$pararr=[],$title,$table,$daochu=0){
        //先判断列表文件
        $models=strtolower($model);
        $path2=app()->getRootPath().'/app/admin/view/'.$models.'/'.$action.'.html';
        $mk=app()->getRootPath().'/app/admin/view/'.$models;
        if(!file_exists($path2)){
            if(!is_dir($mk)){
                mkdir(iconv("UTF-8", "GBK", $mk),0777,true);
            }
            //顶部代码
            $html="<!DOCTYPE html>\n<html>\n<head>\n\t<meta charset=\"utf-8\">\n\t<title>管理</title>\n\t<meta name=\"renderer\" content=\"webkit\">\n\t<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\">\n\t<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0\">\n\t<link rel=\"stylesheet\" href=\"/admin/layuiadmin/layui/css/layui.css\" media=\"all\">\n\t<link rel=\"stylesheet\" href=\"/admin/layuiadmin/style/admin.css\" media=\"all\">\n\t<style>\n\t\t@font-face {\n\t\t\tfont-family: 'iconfont';  \n\t\t\tsrc: url('https://at.alicdn.com/t/font_2565954_q2snf28joy.woff2?t=1621766073287') format('woff2'),\n\t\t\turl('https://at.alicdn.com/t/font_2565954_q2snf28joy.woff?t=1621766073287') format('woff'),\n\t\t\turl('https://at.alicdn.com/t/font_2565954_q2snf28joy.ttf?t=1621766073287') format('truetype');\n\t\t}\n\t\t.iconfont{\n\t\t\tfont-family:\"iconfont\" !important;\n\t\t\tfont-size:16px;font-style:normal;\n\t\t\t-webkit-font-smoothing: antialiased;\n\t\t\t-webkit-text-stroke-width: 0.2px;\n\t\t\t-moz-osx-font-smoothing: grayscale;}\n\t\t.layui-layer-admin .layui-layer-ico {\n\t\t\tbackground: url(\"/admin/layuiadmin/gb.png\") no-repeat!important;\n\t\t\tbackground-size:16px 16px!important;\n\t\t}\n\t</style>\n</head>\n<body>\n";
            //中间代码
            $html.="<div class=\"layui-fluid\">\n\t<div class=\"layui-card\">\n\t\t<div class=\"layui-form layui-card-header layuiadmin-card-header-auto\">\n\t\t\t<div class=\"layui-form-item\">\n";
            $html.="\t\t\t\t<div class=\"layui-inline\">\n\t\t\t\t\t<label class=\"layui-form-label\">ID</label>\n\t\t\t\t\t<div class=\"layui-input-block\">\n\t\t\t\t\t\t<input type=\"text\" name=\"id\" placeholder=\"请输入\" autocomplete=\"off\" class=\"layui-input\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n";
            //循环搜索条件
            if($pararr){
                for ($i=0;$i<count($pararr);$i++){
                    $fna=$pararr[$i]['title'];
                    $search=$pararr[$i]['search'];
                    $name=$pararr[$i]['name'];
                    if($search=='on' || $search==1){
                        $html.="\t\t\t\t<div class=\"layui-inline\">\n\t\t\t\t\t<label class=\"layui-form-label\">".$name."</label>\n\t\t\t\t\t<div class=\"layui-input-block\">\n\t\t\t\t\t\t<input type=\"text\" name=\"".$fna."\" placeholder=\"请输入\" autocomplete=\"off\" class=\"layui-input\">\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n";
                    }
                }
            }
            $html.="\t\t\t\t<div class=\"layui-inline\">\n\t\t\t\t\t<button class=\"layui-btn layuiadmin-btn-useradmin\" lay-submit lay-filter=\"LAY-user-front-search\">\n\t\t\t\t\t\t<i class=\"layui-icon layui-icon-search layuiadmin-button-btn\"></i>\n\t\t\t\t\t</button>\n";
            //如果需要导出则增加导出按钮
            if($daochu==1){
                $html.="\t\t\t\t\t<button class=\"layui-btn layuiadmin-btn-useradmin\" lay-submit lay-filter=\"order-daochu\">\n\t\t\t\t\t\t导出数据\n\t\t\t\t\t</button>\n";
            }
            $html.="\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t\t<div class=\"layui-card-body\">\n\t\t\t<div style=\"padding-bottom: 10px;\">\n\t\t\t{in name=\"\$data.add".$action."\" value=\"\$permis\"}\n\t\t\t\t<button class=\"layui-btn layuiadmin-btn-useradmin\" data-type=\"add\">添加</button>\n\t\t\t{/in}\n\t\t\t{in name=\"\$data.del".$action."\" value=\"\$permis\"}\n\t\t\t\t<button class=\"layui-btn layuiadmin-btn-useradmin\" data-type=\"del\">删除</button>\n\t\t\t{/in}\n\t\t\t</div>\n\t\t\t<table class=\"layui-table\" lay-data=\"{ url:'/admin/".$model."/".$action."',method:'post', page:true, id:'LAY-user-managet'}\" lay-filter=\"LAY-user-managet\">\n\t\t\t<thead>\n\t\t\t\t<tr>\n";

            $html.="\t\t\t\t\t<th lay-data=\"{type: 'checkbox', fixed: 'left'}\"></th>\n";
            $html.="\t\t\t\t\t<th lay-data=\"{field:'id', width:110, sort: true}\">ID</th>\n";
            $isimg=false;//是否有图片
            if($pararr){
                for ($i=0;$i<count($pararr);$i++){
                    $style=$pararr[$i]['style'];
                    $titlex=$pararr[$i]['title'];
                    $name=$pararr[$i]['name'];
                    if($style==3){
                        $isimg=true;
                        $html.="\t\t\t\t\t<th lay-data=\"{field:'".$titlex."', templet: '#imgDemo'}\">".$name."</th>\n";
                    }else{
                        $html.="\t\t\t\t\t<th lay-data=\"{field:'".$titlex."'}\">".$name."</th>\n";
                    }
                }
            }
            $html.="\t\t\t\t\t<th lay-data=\"{fixed: 'right', width:220, align:'center', toolbar: '#barDemo'}\">操作</th>\n\t\t\t\t</tr>\n\t\t\t</thead>\n\t\t\t</table>\n";
            //如果有图片则显示图片
            if($isimg){
                $html.="\t\t\t<script type=\"text/html\" id=\"imgDemo\">\n\t\t\t\t<img src=\"{{d.img}}\" onclick=\"openimg('{{d.img}}')\" style=\"height: 30px;\">\n\t\t\t</script>\n";
            }
            $html.="\t\t\t<script type=\"text/html\" id=\"barDemo\">\n\t\t\t{in name=\"\$data.edit".$action."\" value=\"\$permis\"}\n\t\t\t\t<a class=\"layui-btn layui-btn-normal layui-btn-xs\" lay-event=\"edit\"><i class=\"layui-icon layui-icon-edit\"></i>编辑</a>\n\t\t\t{/in}\n\t\t\t{in name=\"\$data.del".$action."\" value=\"\$permis\"}\n\t\t\t\t<a class=\"layui-btn layui-btn-danger layui-btn-xs\" lay-event=\"del\"><i class=\"layui-icon layui-icon-delete\"></i>删除</a>\n\t\t\t{/in}\n\t\t\t</script>\n\t\t</div>\n\t</div>\n</div>\n";
            //底部js部分
            $html.="<script src=\"/admin/layuiadmin/layui/layui.js\"></script>\n";
            $html.="<script src=\"/admin/js/public.js\"></script>\n<script>\n";
            $html.="\tlayui.config({\n\t\tbase: '/admin/layuiadmin/' \n\t}).extend({\n\t\tindex: 'lib/index' \n\t}).use(['index', 'useradmin', 'table'], function(){\n\t\tvar $ = layui.$,form = layui.form,table = layui.table;\n";
            /**搜索**/
            $html.="\t\tform.on('submit(LAY-user-front-search)', function(data){\n\t\t\tvar field = data.field;\n\t\t\t$.post(\"{:url('admin/".$model."/".$action."')}\", field, function(data,state){});\n\t\t\t//执行重载\n\t\t\ttable.reload('LAY-user-managet', {\n\t\t\t\twhere: field\n\t\t\t});\n\t\t});\n";
            //如果需要导出则增加导出
            if($daochu==1){
                $html.="\t\tform.on('submit(order-daochu)', function(data){\n\t\t\tvar field = data.field;\n\t\t\tvar par=toQueryString(field);\n\t\t\twindow.location.href='/admin/".$model."/".$action."?dao=1&'+par;\n\t\t});\n";
            }
            /**操作按钮**/
            $html.="\t\ttable.on('tool(LAY-user-managet)', function(obj){\n\t\t\tvar data = obj.data; //获得当前行数据\n\t\t\tvar layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）\n\t\t\tvar tr = obj.tr; //获得当前行 tr 的DOM对象\n";
            $html.="\t\t\tif(layEvent === 'del'){ //删除\n\t\t\t\tlayer.confirm('真的删除当前数据吗', function(index){\n\t\t\t\t\tobj.del(); //删除对应行（tr）的DOM结构，并更新缓存\n\t\t\t\t\tlayer.close(index);\n\t\t\t\t\tvar dataid=data['id'];\n\t\t\t\t\t//向服务端发送删除指令\n\t\t\t\t\t$.post(\n\t\t\t\t\t\t\"{:url('admin/".$model."/del".$action."')}\",\n\t\t\t\t\t\t{\"id\":dataid},\n\t\t\t\t\t\tfunction(data,state){\n\t\t\t\t\t\t\tif(state != \"success\"){\n\t\t\t\t\t\t\t\tlayer.msg(\"请求出错!\");\n\t\t\t\t\t\t\t}else if(data.status == 1){\n\t\t\t\t\t\t\t\ttable.reload('LAY-user-managet');\n\t\t\t\t\t\t\t\tlayer.msg('已删除');\n\t\t\t\t\t\t\t}else{\n\t\t\t\t\t\t\t\tlayer.msg(data.msg);\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t}\n\t\t\t\t\t);\n\t\t\t\t});\n\t\t\t}\n";
            /**修改**/
            $html.="\t\t\telse if(layEvent === 'edit'){\n\t\t\t\tvar id=data['id'];\n\t\t\t\tlayer.open({\n\t\t\t\t\ttype: 2\n\t\t\t\t\t,title: '修改".$title."'\n\t\t\t\t\t,content: 'edit".$action.".html'\n\t\t\t\t\t,maxmin: true\n\t\t\t\t\t,area: ['80%', '80%']\n\t\t\t\t\t,btn: ['确定', '取消']\n\t\t\t\t\t,success: function(layero,index){\n\t\t\t\t\t\tvar body = layui.layer.getChildFrame('body', index);\n\t\t\t\t\t\tbody.find(\"#id\").val(id);\n";
            if($pararr) {
                for ($i=0;$i<count($pararr);$i++) {
                    $titlex=$pararr[$i]['title'];//字段名
                    $style=$pararr[$i]['style'];
                    $val=$pararr[$i]['val'];//表单值
                    if($style==1) { //文本框
                        $html .= "\t\t\t\t\t\tbody.find(\"#" . $titlex . "\").val(data['" . $titlex . "']);\n";
                    }else if($style==2){ //单选框
                        $valarr=explode(',',$val);
                        if($valarr && count($valarr)>0){
                            for ($d=0;$d<count($valarr);$d++){
                                $html.="\t\t\t\t\t\tbody.find(\"input[name=" . $titlex . "][value=".$valarr[$d]."]\").attr(\"checked\", data['" . $titlex . "'] == ".$valarr[$d]." ? true : false);\n";
                            }
                        }else{
                            $html.="\t\t\t\t\t\tbody.find(\"input[name=" . $titlex . "][value=".$val."]\").attr(\"checked\", data['" . $titlex . "'] == ".$val." ? true : false);\n";
                        }
                    }else if($style==3) { //上传图片
                        $html .= "\t\t\t\t\t\tbody.find(\"#" . $titlex . "\").val(data['" . $titlex . "']);\n";
                        //除了赋值图片 还需要显示图片
                        $html .= "\t\t\t\t\t\tvar imgurl=data['".$titlex."'];\n\t\t\t\t\t\tif(imgurl){\n\t\t\t\t\t\t\tbody.find(\"#".$titlex."\").next().attr(\"src\",imgurl);\n\t\t\t\t\t\t}\n";
                    }else if($style==4) { //富文本
                        $html .= "\t\t\t\t\t\tbody.find(\"#" . $titlex . "\").val(data['" . $titlex . "']);\n";
                    }
                }
            }
            $html.="\t\t\t\t\t\tform.render();\n\t\t\t\t\t}\n\t\t\t\t\t,yes: function(index, layero){\n\t\t\t\t\t\tvar iframeWindow = window['layui-layer-iframe'+ index],submitID = 'LAY-user-front-submit',submit = layero.find('iframe').contents().find('#'+ submitID);\n\t\t\t\t\t\t//监听提交\n\t\t\t\t\t\tiframeWindow.layui.form.on('submit('+ submitID +')', function(data){\n\t\t\t\t\t\t\tvar field = data.field; //获取提交的字段\n\t\t\t\t\t\t\t//提交 Ajax 成功后，静态更新表格中的数据\n\t\t\t\t\t\t\t//$.ajax({});\n\t\t\t\t\t\t\t$.post(\n\t\t\t\t\t\t\t\t\"{:url('/admin/".$model."/edit".$action."')}\",\n\t\t\t\t\t\t\t\tfield,\n\t\t\t\t\t\t\t\tfunction(data,state){\n\t\t\t\t\t\t\t\t\tif(state != \"success\"){\n\t\t\t\t\t\t\t\t\t\tlayer.msg(\"请求出错!\");\n\t\t\t\t\t\t\t\t\t}else if(data.status == 1){\n\t\t\t\t\t\t\t\t\t\tlayer.msg('修改成功');\n\t\t\t\t\t\t\t\t\t\ttable.reload('LAY-user-managet'); //数据刷新\n\t\t\t\t\t\t\t\t\t}else{\n\t\t\t\t\t\t\t\t\t\tlayer.msg(data.msg);\n\t\t\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t);\n\t\t\t\t\t\t\tlayer.close(index); //关闭弹层\n\t\t\t\t\t\t});\n\t\t\t\t\t\tsubmit.trigger('click');\n\t\t\t\t\t}\n\t\t\t\t});\n\t\t\t}\n";
            $html.="\t\t});\n";
            /**操作按钮结束**/

            /**事件**/
            $html.="\t\tvar active = {\n";
            /**添加**/
            $html.="\t\t\tadd:function(){\n\t\t\t\tlayer.open({\n\t\t\t\t\ttype: 2\n\t\t\t\t\t,title: '添加".$title."'\n\t\t\t\t\t,content: 'add".$action.".html'\n\t\t\t\t\t,maxmin: true\n\t\t\t\t\t,area: ['80%', '80%']\n\t\t\t\t\t,btn: ['确定', '取消']\n\t\t\t\t\t,yes: function(index, layero){\n\t\t\t\t\t\tvar iframeWindow = window['layui-layer-iframe'+ index]\n\t\t\t\t\t\t\t,submitID = 'LAY-user-front-submit'\n\t\t\t\t\t\t\t,submit = layero.find('iframe').contents().find('#'+ submitID);\n\t\t\t\t\t\t//监听提交\n\t\t\t\t\t\tiframeWindow.layui.form.on('submit('+ submitID +')', function(data){\n\t\t\t\t\t\t\tvar field = data.field; //获取提交的字段\n\t\t\t\t\t\t\t//提交 Ajax 成功后，静态更新表格中的数据\n\t\t\t\t\t\t\t//$.ajax({});\n\t\t\t\t\t\t\t$.post(\n\t\t\t\t\t\t\t\t\"{:url('/admin/".$model."/add".$action."')}\",\n\t\t\t\t\t\t\t\tfield,\n\t\t\t\t\t\t\t\tfunction(data,state){\n\t\t\t\t\t\t\t\t\tif(state != \"success\"){\n\t\t\t\t\t\t\t\t\t\tlayer.msg(\"请求出错!\");\n\t\t\t\t\t\t\t\t\t}else if(data.status == 1){\n\t\t\t\t\t\t\t\t\t\tlayer.msg('添加成功');\n\t\t\t\t\t\t\t\t\t\ttable.reload('LAY-user-managet'); //数据刷新\n\t\t\t\t\t\t\t\t\t}else{\n\t\t\t\t\t\t\t\t\t\tlayer.msg(data.msg);\n\t\t\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t\t}\n\t\t\t\t\t\t\t);\n\t\t\t\t\t\t\tlayer.close(index); //关闭弹层\n\t\t\t\t\t\t});\n\t\t\t\t\t\tsubmit.trigger('click');\n\t\t\t\t\t}\n\t\t\t\t});\n\t\t\t},\n";
            /**删除**/
            $html.="\t\t\tdel: function(){\n\t\t\t\tvar checkStatus = table.checkStatus('LAY-user-managet'),checkData = checkStatus.data; //得到选中的数据\n\t\t\t\tif(checkData.length === 0){\n\t\t\t\t\treturn layer.msg('请选择数据');\n\t\t\t\t}\n\t\t\t\tvar dataid='';\n\t\t\t\tfor(var i=0;i<checkData.length;i++){\n\t\t\t\t\tdataid=dataid+','+checkData[i]['id'];\n\t\t\t\t}\n\t\t\t\tlayer.confirm('确定删除选中数据吗？', function(index) {\n\t\t\t\t\t$.post(\n\t\t\t\t\t\t\"{:url('/admin/".$model."/del".$action."')}\",\n\t\t\t\t\t\t{\"id\":dataid},\n\t\t\t\t\t\tfunction(data,state){\n\t\t\t\t\t\t\tif(state != \"success\"){\n\t\t\t\t\t\t\t\tlayer.msg(\"请求出错!\");\n\t\t\t\t\t\t\t}else if(data.status == 1){\n\t\t\t\t\t\t\t\ttable.reload('LAY-user-managet');\n\t\t\t\t\t\t\t\tlayer.msg('已删除');\n\t\t\t\t\t\t\t}else{\n\t\t\t\t\t\t\t\tlayer.msg(data.msg);\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t}\n\t\t\t\t\t);\n\t\t\t\t});\n\t\t\t}\n";
            $html.="\t\t}\n";
            /**事件结束**/
            $html.="\t\t$('.layui-btn.layuiadmin-btn-useradmin').on('click', function(){\n\t\t\tvar type = $(this).data('type');\n\t\t\tactive[type] ? active[type].call(this) : '';\n\t\t});\n";
            $html.="\t});\n";
            //如果有图片则添加预览图片的openimg方法
            if($isimg){
                $html.="\tfunction openimg(img){\n\t\tlayer.open({\n\t\t\ttype: 1,\n\t\t\ttitle: false,\n\t\t\tcloseBtn: 0,\n\t\t\tarea: '516px',\n\t\t\tskin: 'layui-layer-nobg', //没有背景色\n\t\t\tshadeClose: true,\n\t\t\tcontent: '<img src=\"'+img+'\" style=\"width:100%;\" />'\n\t\t});\n\t}\n";
            }
            $html.="</script>\n";
            $html.="</body>\n</html>";
            file_put_contents($path2,$html);
        }

        //生成修改文件
        $path3=app()->getRootPath().'/app/admin/view/'.$model.'/edit'.$action.'.html';
        $mk=app()->getRootPath().'/app/admin/view/'.$model;
        if(!file_exists($path3)) {
            if (!is_dir($mk)) {
                mkdir(iconv("UTF-8", "GBK", $mk), 0777, true);
            }
            //是否需要上传图片
            $isimg=false;
            //是否设置了富文本
            $iscontent=false;
            $fuid='';
            /**顶部代码**/
            $html="<!DOCTYPE html>\n<html>\n<head>\n\t<meta charset=\"utf-8\">\n\t<title>管理</title>\n\t<meta name=\"renderer\" content=\"webkit\">\n\t<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\">\n\t<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0\">\n\t<link rel=\"stylesheet\" href=\"/admin/layuiadmin/layui/css/layui.css\" media=\"all\">\n\t<link rel=\"stylesheet\" href=\"/admin/css/u-ui.css\" media=\"all\">\n</head>\n<body>\n";
            /**中间部分代码**/
            $html.="<div class=\"layui-form\" lay-filter=\"layuiadmin-form-useradmin\" id=\"layuiadmin-form-useradmin\" style=\"padding: 20px 0 0 0;\">\n\t<input type=\"hidden\" name=\"id\" id=\"id\" value=\"\" />\n\t";
            if($pararr) {
                for ($i=0;$i<count($pararr);$i++) {
                    $name=$pararr[$i]['name'];
                    $titlex=$pararr[$i]['title'];//字段名
                    $style=$pararr[$i]['style'];
                    $val=$pararr[$i]['val'];//表单值
                    if($style==1){ //文本框
                        $html.="<div class=\"layui-form-item\">\n\t\t<label class=\"layui-form-label\">".$name."</label>\n\t\t<div class=\"layui-input-inline\">\n\t\t\t<input type=\"text\" name=\"".$titlex."\" id=\"".$titlex."\" lay-verify=\"required\"  placeholder=\"".$name."\" autocomplete=\"off\" class=\"layui-input\">\n\t\t</div>\n\t</div>\n\t";
                    }else if($style==2){ //单选框
                        $valarr=explode(',',$val);
                        if($valarr && count($valarr)>0){
                            $html.="<div class=\"layui-form-item\" lay-filter=\"status\">\n\t\t<label class=\"layui-form-label\">".$name."</label>\n\t\t<div class=\"layui-input-block\">\n";
                            for ($d=0;$d<count($valarr);$d++){
                                $html.="\t\t\t<input type=\"radio\" name=\"".$titlex."\" checked  id=\"".$titlex."\" value=\"".$valarr[$d]."\" title=\"上线\">\n";
                            }
                            $html.="\t\t</div>\n\t</div>\n\t";
                        }else{
                            $html.="<div class=\"layui-form-item\" lay-filter=\"status\">\n\t\t<label class=\"layui-form-label\">".$name."</label>\n\t\t<div class=\"layui-input-block\">\n";
                            $html.="\t\t\t<input type=\"radio\" name=\"".$titlex."\" checked  id=\"".$titlex."\" value=\"".$val."\" title=\"上线\">\n";
                            $html.="\t\t</div>\n\t</div>\n\t";
                        }
                    }else if($style==3){ //上传图片
                        $isimg=true;
                        $html.="<div class=\"layui-form-item\">\n\t\t<label class=\"layui-form-label\">".$name."</label>\n\t\t<div class=\"layui-input-inline\">\n\t\t\t<input type=\"text\" name=\"".$titlex."\" id=\"".$titlex."\" lay-verify=\"required\"  placeholder=\"".$name."\" autocomplete=\"off\" class=\"layui-input\">\n\t\t\t<img style=\"width:100px;\">\n\t\t</div>\n\t\t<div class=\"layui-input-inline\">\n\t\t\t<button type=\"button\" class=\"layui-btn\" id=\"".$titlex."btn\">上传图片</button>\n\t\t</div>\n\t</div>\n\t";
                    }else if($style==4){ //富文本
                        $iscontent=true;
                        $fuid=$titlex;
                        $html.="<div class=\"layui-form-item\">\n\t\t<label class=\"layui-form-label\">".$name."</label>\n\t\t<div class=\"layui-input-block\" style=\"width: 80%;\">\n\t\t\t<textarea name=\"".$titlex."\" id=\"".$titlex."\" lay-verify=\"content\" style=\"display: none;\" class=\"layui-textarea\"></textarea>\n\t\t</div>\n\t</div>\n\t";
                    }
                }
            }
            $html.="\n\t<div class=\"layui-form-item layui-hide\">\n\t\t<input type=\"button\" lay-submit lay-filter=\"LAY-user-front-submit\" id=\"LAY-user-front-submit\" value=\"确认\">\n\t</div>\n</div>\n";
            
            /*如果是富文本那么这个js不一样*/
            if($iscontent){
                $html.="<script src=\"/admin/layuiadmin/layui/Layui-KnifeZ/layui.js\"></script>\n<script src=\"/admin/layuiadmin/layui/ace/ace.js\"></script>\n<script>\n";
            }else{
                $html.="<script src=\"/admin/layuiadmin/layui/layui.js\"></script>\n<script>\n";
            }

            $html.="\tlayui.config({\n\t\tbase: '/admin/layuiadmin/' \n\t}).extend({\n\t\tindex: 'lib/index' \n\t}).use(['index', 'useradmin', 'form','upload','layedit'], function(){\n\t\tvar $ = layui.$,form = layui.form,upload = layui.upload,form = layui.form,layedit = layui.layedit;\n";
            
            /**上传图片**/
            if($isimg){
                $html.="\t\tvar uploadInst = upload.render({\n\t\t\telem: '#imgbtn'\n\t\t\t,url: '/admin/index/upload'\n\t\t\t,ext: 'jpg|png|gif'\n\t\t\t,done: function(res){\n\t\t\t\tif(res.code == 0){\n\t\t\t\t\t$(this.item).parent().prev(\"div\").children(\"input\").val(res.filename);\n\t\t\t\t\t$(this.item).parent().prev(\"div\").children(\"img\").attr(\"src\",res.img);\n\t\t\t\t\tlayer.msg(\"上传成功\");\n\t\t\t\t}\n\t\t\t\tlayer.msg(res.msg);\n\t\t\t}\n\t\t});\n";
            }

            /**富文本**/
            if($iscontent){
                $html.="layedit.set({
                    uploadImage: {
                        url: \"{:url('/admin/Index/upload')}\",
                        accept: 'image',
                        acceptMime: 'image/*',
                        exts: 'jpg|png|gif|bmp|jpeg',
                        size: '10240'
                    }
                    , devmode: false
                    , autoSync: true
                    ,onchange: function (content) {
                        console.log(content);
                    }
                    , codeConfig: {
                        hide: true,  //是否显示编码语言选择框
                        default: 'javascript' //hide为true时的默认语言格式
                    }
                    , tool: [
                        'html', //显示富文本源码
                        'undo', //撤销
                        'redo',//重做
                        'code', //代码
                        'strong',//加粗
                        'italic', //斜体
                        'underline', //下划线
                        'del', //删除线
                        'addhr', //水平线
                        '|',
                        'removeformat', //清楚文字样式
                        'fontFomatt',//段落格式
                        'fontfamily',//字体
                        'fontSize', //字体大小
                        'fontBackColor',//字体背景色
                        'colorpicker',//字体颜色
                        'face',//表情
                        '|',
                        'left', //左对齐
                        'center', //居中
                        'right', //右对齐
                        '|',
                        'image_alt', //图片alt
                        'anchors'//添加锚点
                        , '|'
                        , 'table',//表格
                        'customlink'//自定义链接
                        , 'fullScreen'//全屏
                        ]
                        , height: '90%'
                    });
                    var ieditor = layedit.build('".$fuid."');
                    layedit.sync(ieditor);
                    form.verify({
                        content: function(value) {
                            return layedit.sync(ieditor);
                        }
                    });";
                
            }

            
                $html.="\t});\n";
            $html.="</script>\n</body>\n</html>";
            file_put_contents($path3,$html);
        }
    }
    public function test(){

        $arr=[
            0=>array('title'=>'name','type'=>1,'name'=>'姓名'),
            1=>array('title'=>'title','type'=>1,'name'=>'名称'),
            2=>array('title'=>'texter','type'=>3,'name'=>'内容'),
        ];
        $model='Offer';
        $action='material';
        $pararr=Db::name('ohyueo_develo_curdtable')->where('listid',7)->select()->toArray();
        if($pararr){
            for ($i=0;$i<count($pararr);$i++){
                $pararr[$i]['title']=$pararr[$i]['name'];
                $pararr[$i]['name']=$pararr[$i]['beizhu'];
            }
        }
        $title='商品订单';
        $table='general_offer_material';
        //生成静态文件
        //$this->generatehtml($model,$action,$pararr,$title,$table);
        //$this->modelaction('Logins','custlist',$arr,'客户类别','ohyueo_develo_curdtable');
    }
}