<?php
namespace app\middleware;
use GuzzleHttp\Client;

class CheckApiRequest
{
    //定义转发Ip
    protected $zf_ip = 'https://yuyue.zx-lot.com/'; //目标
    
    public function handle($request, \Closure $next)
    {   
        // 检查是否需要转发请求
        if ($this->shouldForwardRequest($request)) {
            $client = new Client();

            // 获取当前请求的所有数据
            $data = $request->param();

            // 调用其他服务器的微服务
            // 请求需要拼接地址

            // 获取前端请求的地址
            $path = $request->pathinfo();
            // 拼接请求地址
            $posturl = $this->zf_ip .'resource/'. $path;
            //获取是post还是get
            $method = $request->method();
            if($method == 'GET'){
                $response = $client->request('GET', $posturl, [
                    'query' => $data
                ]);
            }else{
                $response = $client->request('POST', $posturl, [
                    'json' => $data
                ]);
            }

            // 获取响应内容
            $content = $response->getBody()->getContents();

            // 解析响应内容（假设它是JSON格式）
            $result = json_decode($content, true);

            // 返回结果
            return json($result);
        }

        // 如果不需要转发请求，就继续处理请求
        return $next($request);
    }

    protected function shouldForwardRequest($request)
    {
        // 在这里添加你的逻辑来决定是否需要转发请求
        // 例如，你可以检查请求的路径、方法、参数等
        //获取请求方法名
        $method = $request->action();
        //如果方法名是addorder 则判断并发 最大并发为1
        if($method == 'wxregister'){
            //return false;//不转发
        }
        return false; //全部不转发
    }

    // public function handle($request, \Closure $next)
    // {   
    //     $response = $next($request);
    //     //不验证方法
    //     $notCheckAction = ['wxregister','img_upload','getphone','getuserinfo','wxregister','wxh5register','img_upload','getSignPackage','formupload','upload','addsignin'];
    //     //判断请求方法是否是不验证方法
    //     if(in_array($request->action(),$notCheckAction)){
    //         return $response;
    //     }
    //     //判断请求的数据是否有time和sign参数
    //     if(!$request->param('ohyu_time') || !$request->param('ohyu_sign')){
    //         return json(['error' => 'Invalid request1'], 400);
    //     }
    //     //判断请求的时间是否超过5分钟 前端时间戳为13位 substr($time,0,10)
    //     if(time()-substr($request->param('ohyu_time'),0,10)>300){
    //         return json(['error' => 'Invalid request2'], 400);
    //     }
    //     //判断请求的sign是否正确
    //     $sign = $request->param('ohyu_sign');
    //     $time = $request->param('ohyu_time');
    //     $data = $request->param();
    //     unset($data['ohyu_sign']);
    //     ksort($data);
    //     $data['key'] = 'www.ohyu.cn';
    //     $str=urldecode(http_build_query($data));//为了转换提交预约时候的编码
    //     $str=urldecode($str);//为了转换提交预约的时候里面的编码的编码
    //     $newsign = md5($str);
    //     if($sign != $newsign){
    //         return json(['error' => 'Invalid request3'], 400);
    //     }
    //     // 如果请求合法，继续处理请求
    //     return $response;
    // }
}