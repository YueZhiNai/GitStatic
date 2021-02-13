<?
   //https://docs.nabo.krait.cn/#/expand-http
        //来自KRAIT提供思路和部分代码
/**  因为put原因废弃
function  request_api($url,$data=array(),$token="", $method=Typecho_Http_Client::METHOD_POST){
        $http = Typecho_Http_Client::get();
        $http->setHeader('User-Agent', 'GitStatic');
          $http->setHeader('Authorization', 'token  '.$token);
       //var_dump($token);
       // $http->setHeader('accept','application/json');
        //$http->setHeader( 'User-Agent","GitStatic');   
        //$http->setHeader('Authorization','token  '.$token);
        $http->setData($data);
        $http->setMethod($method);
        $http->send($url);
        $response = $http->getResponseBody();
       file_put_contents(md5($url).".txt",$url.$response);
         return $response;
        }
**/
function request_api($url, $data=null,$token="",  $method='get', $header = array(""), $https=true, $timeout = 5){
    $method = strtoupper($method);
    $ch = curl_init();//初始化
    $token_array=array('Authorization: token  '.$token,'User-Agent: GitStatic');
    curl_setopt($ch, CURLOPT_URL, $url);//访问的URL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//只获取页面内容，但不输出
    if($https){
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//https请求 不验证证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//https请求 不验证HOST
    }
    if ($method != "GET") {
        if($method == 'POST'){
            curl_setopt($ch, CURLOPT_POST, true);//请求方式为post请求
        }
        if ($method == 'PUT' || strtoupper($method) == 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); //设置请求方式
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//请求数据
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $header=array_merge($header,$token_array);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //模拟的header头
    //curl_setopt($ch, CURLOPT_HEADER, false);//设置不需要头信息
    $result = curl_exec($ch);//执行请求
    curl_close($ch);//关闭curl，释放资源
file_put_contents(md5($url).".txt",$url.$result);
    return $result;
}
function Github_user_info($username,$token) {return json_decode(request_api("https://api.github.com/users/".$username,array(),$token)); }
        //不存在会返回一个message Not Found 获取用户基本信息
function Github_user_login($token) {return json_decode(request_api("https://api.github.com/user", array(),$token)); }
        //不存在会返回一个message Not Found 获取用户基本信息

function Github_repos_all($username,$token) {return request_api("https://api.github.com/users/".$username."/repos",array(),$token,Typecho_Http_Client::METHOD_GET); }
          //获取所有repos

function Github_repos_info($username,$reposname,$token) {return json_decode(request_api("https://api.github.com/repos/".$username."/".$reposname,array(),$token)); }
            //获取repos info

function Github_repos_path($username,$reposname,$path,$token) {return json_decode(request_api("https://api.github.com/repos/".$username."/".$reposname."/contents".$path,array(),$token)); }
              //获取repos 目录内容

function Github_files_upload($username,$token,$repos,$path,$files)
                {
                  $data=array("message"=>"upload by GitStatic","content"=>base64_encode($files));
                  $json=(array)json_decode(request_api("https://api.github.com/repos/".$username."/".$repos."/contents".$path,json_encode($data),$token,"PUT"));
                   //var_dump("https://api.github.com/repos/".$username."/".$repos."/contents".$path);
                  return !isset($json["message"]);
                  //上传需要判断失败或者成功
                } 
function Github_files_updata($username,$token,$repos,$path,$files,$sha)
                  {
                    $data=array("message"=>"updata by GitStatic","content"=>base64_encode($files),"sha"=>$sha);
                    $json=(array)json_decode(request_api("https://api.github.com/repos/".$username."/".$repos."/contents".$path,json_encode($data),$token,"PUT"));
                    //更新需要判断失败或者成功
                    // var_dump($data);
                    return !isset($json["message"]);
                  }
function Github_files_del($username,$token,$repos,$path,$sha)
                    {
                      $data=array("message"=>"del by GitStatic","sha"=>$sha);
                      $json=(array)json_decode(request_api("https://api.github.com/repos/".$username."/".$repos."/contents".$path,json_encode($data),$token,"DELETE"));
                      //var_dump($json);
                      return !isset($json["message"]);
                      //删除需要判断失败或者成功
                    }
function Github_get_sha($username,$repos,$path,$token){
                        $json=(array)Github_repos_path($username,$repos,$path,$token);
                        // var_dump($json);
                        return $json["sha"];
                      }