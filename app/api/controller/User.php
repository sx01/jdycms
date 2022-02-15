<?php 
namespace app\api\controller;
use app\common\model\mysql\Members;
use app\common\model\mysql\Token;
use app\common\lib\WXBizDataCrypt;

use app\common\model\mysql\Fankui;


class User extends AuthBase{

  
    //更新用户信息
    public function fankui(){
        $result = [];
        $uid=$this->uid;

        $data =  $this->request->param();
        $data['uid'] = $uid;
        $data['addtime'] = time();

        $res = Fankui::create($data);
        if($res['id']){
            return show(config("status.success"),"OK", $result); 
        }else{
            return show(config("status.error"),"更新信息失败!");
        }

    }

    //更新用户信息
    public function updateinfo(){
        $result = [];
        $uid=$this->uid;
        $find = Members::where("`uid`='$uid'")->find();
       
        if ($this->request->isPost()) {
            $data =  $this->request->param();
            $avatarbase64 = "";
            if(isset($data['avatarbase64'])){
            $avatarbase64 = $data['avatarbase64'];
            unset($data['avatarbase64']);
            }
          
            $data['birthdate'] = strtotime($data['birthdate']);
            $res = Members::where("uid=$uid")->update($data);


         
            if ($res !== false) {
                $tmptime = 3600*24*31;
                $this->nickname = $data['nickname'];
          
                if($avatarbase64){

                    if (!file_exists('./uploads/')) {
                        mkdir('./uploads/');
                        //echo '创建文件夹flower成功';
                    }

                    if (!file_exists('./uploads/avatar/')) {
                        mkdir('./uploads/avatar/');
                        //echo '创建文件夹flower成功';
                    }
                    $yue = date("Y-m",time());
                    if (!file_exists('./uploads/avatar/'.$yue)) {
                        mkdir('./uploads/avatar/'.$yue);
                        //echo '创建文件夹flower成功';
                    }

                    $rootPath = "./uploads/avatar/".$yue."/";
                   

                  
                    $avatarbase64 = base64_image_content( $avatarbase64,  $rootPath, $uid);
                    $data_img['avatar'] = $rootPath.$avatarbase64;
                    $this->avatar =  avatarchuli($data_img['avatar']); 
                     
                  $res =  Members::where("`uid`=$uid")->save($data_img); //
                  if ($res !== false) {
                      if($find['avatar']){
                      @unlink(".".$find['avatar']);
                      }
                  }
                }
              

                
                $userInfo = Token::where("token = '$this->accessToken'") ->find();
                  
                   $result = [
                       'phone' => $find['phone'],
                       'uid' =>   $uid,
                       'nickname' => $data['nickname'],
                       'avatar' => $this->avatar ,
                       'expire' => $userInfo['expire'],
                       'create_time' => $userInfo['create_time'],
                       "token" => $userInfo['token']
                   ];
                   $res = Token::where(" id = $userInfo[id]")->update($result); 
                  
                   return show(config("status.success"),"OK", $result); 
                 
            } else {

                return show(config("status.error"),"更新信息失败!");
               
            }
        }else{

            return show(config("status.error"),"更新信息失败!");
           
        }
    }

     //获得用户信息
    public function getinfo(){
        $result = [];
        $uid=$this->uid;
        $find =  Members::where("`uid`='$uid'")->find();
        $find['avatar'] = avatarchuli($find['avatar']);
        unset($find['openid']);
        unset($find['unionid']);
        unset($find['password']);
        unset($find['regdate']);
        unset($find['regip']);
        $find['birthdate'] = date("Y-m-d",$find['birthdate']);
      
    
        $result['find'] = $find;
        return show(config("status.success"),"OK", $result); 
    }


     //检查是否绑定了手机号
     public function  checkbangdingphonebyuid()
     {
      $uid = $this->uid;
      if ($uid) {
        //   if (IS_AJAX) {
              
                  $tmpadmin = Members::where("`uid`='$uid'")->find();

                  $tmpid = $tmpadmin['phone'];
                  $password = $tmpadmin['password'];

                  if ($tmpid) { 
                      if($password){
                        $data['status'] = 0;
                        $data['tishi'] = "手机号已经绑定，已经设置登录密码！";
                        return show(config("status.success"),"OK", $data);
                      }else{
                        $data['status'] = 2;
                        $data['tishi'] = "手机号已经绑定,未设置登录密码！";
                        return show(config("status.success"),"OK", $data);
                      }
                     
                  } else {
                      $data['status'] = 1;
                      $data['tishi'] = "手机号待绑定！";
                      return show(config("status.success"),"OK", $data);
                     
                  }
              
        //   } else {
        //       $data['status'] = 0;
        //       $data['tishi'] = "非法提交！";

        //       $this->ajaxReturn($data, 'JSON');
        //   }
      } else {
        $data['status'] = 0;
        $data['tishi'] = "没有登录!";
          return show(config("status.error"),"OK", $data);
      }
     }




 //资讯小程序 微信授权绑定手机号
 public function xiaochengxubangdingphone2(){
    $uid = $this->uid;
    $appid = config("siteinfo.appid"); //微信小程序appid
    $secret = config("siteinfo.appsecret"); //微信小程序secret
    $code = input('code'); //接收code参数，换取用户唯一标识
    $encryptedDataphone = input("encryptedDataphone");
    $ivphone = input("ivphone");
    $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $appid . "&secret=" . $secret . "&js_code=" . $code . "&grant_type=authorization_code";
    $res = $this->http_request($url); //https_request是封装的发送请求的方法
    // file_put_contents("addxiaochengxuecoderespaiming.log", $res);
    $res = json_decode($res, true); //将返回结果JSON化
    // file_put_contents("addxiaochengxuecoderes2paiming.log", $res['errcode']);
    if (isset($res['errcode']) && $res['errcode']) {
        //如果请求微信那边报错，就返回前端报错信息
        // return $this->ajaxReturn(show(0, "微信授权失败" + $res['errcode']));

        return show(config("status.error"),"微信授权失败!"+ $res['errcode'],[]);

    } else {
        //请求微信那边成功，获取unionid,先判断数据库有没有
        $openid = $res['openid'];
        $sessionKey = $res['session_key'];

        $pc = new WXBizDataCrypt($appid, $sessionKey);
        $phone = "";
        if ($encryptedDataphone && $ivphone) {



            $errCode = $pc->decryptData(urldecode($encryptedDataphone), urldecode($ivphone), $data);

            // file_put_contents("errCodepaimingphone.log", $errCode);
            if ($errCode == 0) {
                // print($data . "\n");
               file_put_contents("encryptedDatapaimingphone.log", $data);
                $tmpdata =  json_decode($data, true);
                if (isset($tmpdata["phoneNumber"]) && $tmpdata["phoneNumber"]) {
                    $phone = $tmpdata["phoneNumber"];
                }
            } else {
                // print($errCode . "\n");

            }
        }
        if($phone){
            $tmpdata = array('phone'=>$phone);
            $res = Members::where("`uid`= $uid ")->update($tmpdata);
            if ($res === false) {
            
                
                return show(config("status.error"),"绑定手机号失败!",[]);
                
            } else {
              Token::where("`uid`= $uid ")->update($tmpdata);
               
              return show(config("status.success"),"绑定手机号成功！",['phone'=>$phone]);
            }
        }else{
            // $this->ajaxReturn(show(0, "获取手机号失败！"));
            return show(config("status.error"),"获取手机号失败！",[]);

        }
    } 
}


 //资讯小程序 微信授权绑定手机号
 public function xiaochengxubangdingphone(){
    $uid = $this->uid;
    $appid = config("siteinfo.appidsj"); //微信小程序appid
    $secret = config("siteinfo.appsecretsj"); //微信小程序secret
    $code = input('code'); //接收code参数，换取用户唯一标识
    $encryptedDataphone = input("encryptedDataphone");
    $ivphone = input("ivphone");
    $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $appid . "&secret=" . $secret . "&js_code=" . $code . "&grant_type=authorization_code";
    $res = $this->http_request($url); //https_request是封装的发送请求的方法
    // file_put_contents("addxiaochengxuecoderespaiming.log", $res);
    $res = json_decode($res, true); //将返回结果JSON化
    // file_put_contents("addxiaochengxuecoderes2paiming.log", $res['errcode']);
    if (isset($res['errcode']) && $res['errcode']) {
        //如果请求微信那边报错，就返回前端报错信息
        // return $this->ajaxReturn(show(0, "微信授权失败" + $res['errcode']));

        return show(config("status.error"),"微信授权失败!"+ $res['errcode'],[]);

    } else {
        //请求微信那边成功，获取unionid,先判断数据库有没有
        $openid = $res['openid'];
        $sessionKey = $res['session_key'];

        $pc = new WXBizDataCrypt($appid, $sessionKey);
        $phone = "";
        if ($encryptedDataphone && $ivphone) {



            $errCode = $pc->decryptData(urldecode($encryptedDataphone), urldecode($ivphone), $data);

            // file_put_contents("errCodepaimingphone.log", $errCode);
            if ($errCode == 0) {
                // print($data . "\n");
               file_put_contents("encryptedDatapaimingphone.log", $data);
                $tmpdata =  json_decode($data, true);
                if (isset($tmpdata["phoneNumber"]) && $tmpdata["phoneNumber"]) {
                    $phone = $tmpdata["phoneNumber"];
                }
            } else {
                // print($errCode . "\n");

            }
        }
        if($phone){
            $tmpdata = array('phone'=>$phone);
            $res = Members::where("`uid`= $uid ")->update($tmpdata);
            if ($res === false) {
            
                
                return show(config("status.error"),"绑定手机号失败!",[]);
                
            } else {
              Token::where("`uid`= $uid ")->update($tmpdata);
               
              return show(config("status.success"),"绑定手机号成功！",['phone'=>$phone]);
            }
        }else{
            // $this->ajaxReturn(show(0, "获取手机号失败！"));
            return show(config("status.error"),"获取手机号失败！",[]);

        }
    } 
}

//HTTP请求（支持HTTP/HTTPS，支持GET/POST）
public function http_request($url, $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}


    public function index(){
     
         return show(config("status.success"),"OK",[]);
    }

    public function update(){
        
        //如果用户名被修改， redis里面的数据也需要同步一下
        return show(config('status.success'),"ok");
    }
}