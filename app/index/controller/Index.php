<?php
namespace app\index\controller;
use think\facade\View;
use app\models\ZongheImg;
use app\models\ShopClass;
use app\models\ShopList;
use think\facade\Db;
use app\models\UserToken;
use think\facade\Cache;

class Index
{
    public function index()
    {
        $type=input("param.type/d");
        if($type==2){
            $code=input("param.code");
            //重定向
            $id=input("param.id/d");
            if($id){
                $url=config('-appsite.app_domainname').'#/pages/yuyue/info?code='.$code.'&id='.$id;
            }else{
                $url=config('-appsite.app_domainname').'#/pages/yuyue/info?code='.$code;
            }
            Header("Location:$url");exit;
        }
        //主题颜色
        $zhuticolor=Cache::has('zhuticolor');
        if($zhuticolor){
            $iszhuti=Cache::get('zhuticolor');
        }else{
            $iszhuti=Db::name('general_system_diy')->where('name','zhuticolor')->find();
            if($iszhuti){
                Cache::set('zhuticolor',$iszhuti);
            }
        }
        View::assign('zhuticolor', $iszhuti);
        //预约
        $yuyue=Cache::has('yuyue');
        if($yuyue){
            $isact=Cache::get('yuyue');
        }else{
            $isact=Db::name('general_system_diy')->where('name','yuyue')->value('val')?:0;
            if($isact || $isact==0){
                Cache::set('yuyue',$isact);
            }
        }
        View::assign('yuyue', $isact);
        //商城
        $shop=Cache::has('shop');
        if($shop){
            $isshop=Cache::get('shop');
        }else{
            $isshop=Db::name('general_system_diy')->where('name','shop')->value('val')?:0;
            if($isshop || $isshop==0){
                Cache::set('shop',$isshop);
            }
        }
        View::assign('shop', $isshop);
        //新闻
        $news=Cache::has('news');
        if($news){
            $iscou=Cache::get('news');
        }else{
            $iscou=Db::name('general_system_diy')->where('name','news')->value('val')?:0;
            if($iscou || $iscou==0){
                Cache::set('news',$iscou);
            }
        }
        View::assign('news', $iscou);
        //查询模板
        $template=Db::name('general_system_diy')->where('name','template')->value('val')?:1;
        if($template==1){
            return View::fetch();
        }else if($template==2){
            return View::fetch('../public/demo/v2/index.html');
        }
    }
    public function test(){
        // 统计整个APP目录下面的所有代码行数
        $rootPath = app()->getRootPath() . '前端';
        $totalLines = 0;
        $fileCount = 0;
        $fileTypes = ['php', 'html', 'js', 'css', 'vue', 'tpl']; // 要统计的文件类型
        $fileTree = [];
        
        // 统计代码行数
        $this->scanDirectory($rootPath, $totalLines, $fileCount, $fileTypes);
        
        // 生成目录树结构
        $this->generateFileTree($rootPath, $fileTree);
        
        // 输出文件目录结构
        echo "<h3>文件目录结构：</h3>";
        echo "<pre>";
        $this->displayFileTree($fileTree, 0);
        echo "</pre>";
        
        echo "<h3>统计结果：</h3>";
        echo "总文件数：" . $fileCount . "<br>";
        echo "总代码行数：" . $totalLines . "<br>";
        echo "软著申请统计结果完成！";
    }

    /**
     * 递归扫描目录统计行数
     * @param string $dir 目录路径
     * @param int &$totalLines 总行数引用
     * @param int &$fileCount 文件数引用
     * @param array $fileTypes 要统计的文件类型
     */
    private function scanDirectory($dir, &$totalLines, &$fileCount, $fileTypes) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                // 递归扫描子目录
                $this->scanDirectory($path, $totalLines, $fileCount, $fileTypes);
            } else {
                // 获取文件扩展名
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                if (in_array(strtolower($extension), $fileTypes)) {
                    // 统计行数
                    $content = file_get_contents($path);
                    $lines = count(explode("\n", $content));
                    $totalLines += $lines;
                    $fileCount++;
                    
                    // 可选：输出每个文件的行数
                    // echo $path . ": " . $lines . "行<br>";
                }
            }
        }
    }

    /**
     * 生成文件目录树结构
     * @param string $dir 目录路径
     * @param array &$fileTree 文件树结构引用
     */
    private function generateFileTree($dir, &$fileTree) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            $relativePath = str_replace(app()->getRootPath(), '', $path);
            
            if (is_dir($path)) {
                // 添加目录到树结构
                $fileTree[$relativePath] = [];
                // 递归处理子目录
                $this->generateFileTree($path, $fileTree[$relativePath]);
            } else {
                // 添加文件到树结构
                $fileTree[$relativePath] = null;
            }
        }
    }

    /**
     * 显示文件目录树结构
     * @param array $fileTree 文件树结构
     * @param int $level 当前层级
     */
    private function displayFileTree($fileTree, $level) {
        foreach ($fileTree as $path => $children) {
            // 输出缩进
            echo str_repeat("-|", $level);
            
            // 输出路径
            echo $path . PHP_EOL;
            
            // 如果有子目录或子文件，递归显示
            if (is_array($children) && !empty($children)) {
                $this->displayFileTree($children, $level + 1);
            }
        }
    }

    public function svn(){
        echo "© ohyu.cn 海之心通用预约系统 2.0";
    }
}
