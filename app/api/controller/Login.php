<?php

namespace app\api\controller;

use app\BaseController;
use app\common\lib\WXBizDataCrypt;
use app\common\lib\ErrorCode;
use app\common\model\mysql\Members;
use app\common\model\mysql\Weixin_info;
use app\common\lib\Str;
use app\common\model\mysql\Token;
class Login  extends BaseController
{


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



//舌尖霸王餐
public function xiaochengxulogin2()
{
    $appid = config("siteinfo.appid"); //微信小程序appid
    $secret = config("siteinfo.appsecret"); //微信小程序secret
    $fenxiangma = input("fenxiangma");//获取分享码
    $code = input('code'); //接收code参数，换取用户唯一标识
    $nickName = input('nickName'); //接收用户名
    $sex = input('sex'); //接收用户性别
    $avatarUrl = input('avatarUrl'); //接收用户性别
    $encryptedData = input("encryptedData");
    $iv = input("iv");
    file_put_contents("ivencryptedData.log","----encryptedData:".$encryptedData."----iv:".$iv);
    //下面url是请求微信端地址获取用户唯一标识的，对应的appid和secret改成自己的
    $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $appid . "&secret=" . $secret . "&js_code=" . $code . "&grant_type=authorization_code";
    $res = $this->http_request($url); //https_request是封装的发送请求的方法
    // file_put_contents("addxiaochengxuecoderes.log",$res);
    $res = json_decode($res, true); //将返回结果JSON化
    // file_put_contents("addxiaochengxuecoderes2.log",$res['errcode']);
    if (isset($res['errcode']) && $res['errcode']) {
        //如果请求微信那边报错，就返回前端报错信息
        // return $this->ajaxReturn(show(0, "微信授权登录失败"+$res['errcode']));

        return show(config("status.error"),"微信授权登录失败!"+ $res['errcode'],[]);
    } else {
        //请求微信那边成功，获取unionid,先判断数据库有没有
        $openid = $res['openid'];
        if(!isset($res['unionid'])){
            // file_put_contents("unionid.log",json_encode($res));
            // return $this->ajaxReturn(show(0, "微信授权成功获取unionid失败"));
            $unionid = "";
        }else{
            $unionid = $res['unionid'];
        }
      
        $sessionKey = $res['session_key'];
       
        $phone = "";
        if($encryptedData && $iv){
        $pc = new WXBizDataCrypt($appid, $sessionKey);
      
        
        $errCode = $pc->decryptData(urldecode($encryptedData), urldecode($iv), $data );
     
        file_put_contents("errCode.log",$errCode);
        if ($errCode == 0) {
            // print($data . "\n");
            file_put_contents("encryptedData.log",$data);
            $tmpdata =  json_decode($data,true);
            if(isset($tmpdata["phoneNumber"]) && $tmpdata["phoneNumber"]){
                $phone = $tmpdata["phoneNumber"];
            }
          
             if(isset($tmpdata["unionId"]) && $unionid==""){
            $unionid = $tmpdata["unionId"];
            // file_put_contents("unionid3.log",$unionid);
             }

        } else {
            // print($errCode . "\n");
           
        }
       }   
    //    file_put_contents("unionid2.log",$unionid);
    //    file_put_contents("addxiaochengxuephone.log",$phone);
    //    if($unionid==""){
    //     return $this->ajaxReturn(show(0, "获取微信授权失败，登陆失败"));
    //    }
        $weixininfo['openid'] = $openid;
        $weixininfo['unionid'] = $unionid;
        $weixininfo['nickname'] = $nickName;
        $weixininfo['sex'] = $sex;
        $weixininfo['headimgurl'] = $avatarUrl;


        if($unionid){
            $tmp =  Weixin_info::where("unionid='$unionid'")->find();
             }else{
              $tmp =  Weixin_info::where("openid='$openid'")->find();
             }

        

        if ($tmp['id']) {
            $id = $tmp['id'];
            Weixin_info::where("id = $id")->update($weixininfo);
        } else {
            $addres = Weixin_info::create($weixininfo);
            $id = $addres['id'];
        }

       
        $ip = $this->request->ip();
        if($unionid){
            $member =  Members::where("`unionid` = '$unionid' ")->find();
           }else{
            $member = Members::where("`openid` = '$openid' ")->find();
           }

  
        // file_put_contents("addxiaochengxulogininfo.log",$encryptedData.$iv.$phone.json_encode($res). json_encode($member));
       
        // 设置
        $tmptime = 3600 * 24 * 31;
        if ($member['uid']) {
            $uid = $member['uid'];
            if($phone){
                Members::where("uid = $uid")->update(['phone' => $phone,'lastloginip' => $ip, 'lastlogintime' => time()]);
     
            }else{
                Members::where("uid = $uid")->update(['lastloginip' => $ip, 'lastlogintime' => time()]);
     
            }
            $token  = Str::getLoginToken($uid);
           
            $result = [
                'phone' => $phone?$phone:$member['phone'],
                'uid' => $uid,
                'nickname' => $member['nickname'],
                'avatar' => avatarchuli($member['avatar']),
                'expire' => $tmptime,
                'create_time' => time(),
                "token" => $token
            ];
        } else {

            if($fenxiangma){
                $cres = Members::create(['unionid' => $unionid, 'openid' => $openid,  'phone' => $phone, 'avatar' => $weixininfo['headimgurl'], 'nickname' => $weixininfo['nickname'], 'sex' => $weixininfo['sex'], 'regip' => $ip,'type' => 0, 'regdate' => time(),'fenxiangma'=>$fenxiangma]);
         
            }else{
                $cres = Members::create(['unionid' => $unionid, 'openid' => $openid,  'phone' => $phone, 'avatar' => $weixininfo['headimgurl'], 'nickname' => $weixininfo['nickname'], 'sex' => $weixininfo['sex'], 'regip' => $ip,'type' => 0, 'regdate' => time()]);
         
            }

       
            $uid = $cres['id'];
            $token  = Str::getLoginToken($uid);
            
            $result = [
                'phone' => $phone,
                'uid' => $uid,
                'nickname' => $weixininfo['nickname'],
                'avatar' => $weixininfo['headimgurl'],
                'expire' => $tmptime,
                'create_time' => time(),
                "token" => $token
            ];
        }

        if ($uid) {

         

            $res = Token::create($result);


            if (!$res) {
                // return $this->ajaxReturn(show(0, "记录登陆信息失败"));
                return show(config("status.error"),"记录登陆信息失败!");
                // cookie('logininfo',$result, $result['expire']);



            } else {

               
                return show(config("status.success"),"登陆成功!",$result);
     
            }
        } else {
            return show(config("status.error"),"登陆失败!");
            
        }

    }
}



    public function xiaochengxulogin()
    {
        $appid = config("siteinfo.appidsj"); //微信小程序appid
        $secret = config("siteinfo.appsecretsj"); //微信小程序secret
        $code = input('code'); //接收code参数，换取用户唯一标识
        $nickName = input('nickName'); //接收用户名
        $sex = input('sex'); //接收用户性别
        $avatarUrl = input('avatarUrl'); //接收用户性别
        $encryptedData = input("encryptedData");
        $iv = input("iv");
        file_put_contents("ivencryptedData.log","----encryptedData:".$encryptedData."----iv:".$iv);
        //下面url是请求微信端地址获取用户唯一标识的，对应的appid和secret改成自己的
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $appid . "&secret=" . $secret . "&js_code=" . $code . "&grant_type=authorization_code";
        $res = $this->http_request($url); //https_request是封装的发送请求的方法
        // file_put_contents("addxiaochengxuecoderes.log",$res);
        $res = json_decode($res, true); //将返回结果JSON化
        // file_put_contents("addxiaochengxuecoderes2.log",$res['errcode']);
        if (isset($res['errcode']) && $res['errcode']) {
            //如果请求微信那边报错，就返回前端报错信息
            // return $this->ajaxReturn(show(0, "微信授权登录失败"+$res['errcode']));

            return show(config("status.error"),"微信授权登录失败!"+ $res['errcode'],[]);
        } else {
            //请求微信那边成功，获取unionid,先判断数据库有没有
            $openid = $res['openid'];
            if(!isset($res['unionid'])){
                // file_put_contents("unionid.log",json_encode($res));
                // return $this->ajaxReturn(show(0, "微信授权成功获取unionid失败"));
                $unionid = "";
            }else{
                $unionid = $res['unionid'];
            }
          
            $sessionKey = $res['session_key'];
           
            $phone = "";
            if($encryptedData && $iv){
            $pc = new WXBizDataCrypt($appid, $sessionKey);
          
            
            $errCode = $pc->decryptData(urldecode($encryptedData), urldecode($iv), $data );
         
            file_put_contents("errCode.log",$errCode);
            if ($errCode == 0) {
                // print($data . "\n");
                file_put_contents("encryptedData.log",$data);
                $tmpdata =  json_decode($data,true);
                if(isset($tmpdata["phoneNumber"]) && $tmpdata["phoneNumber"]){
                    $phone = $tmpdata["phoneNumber"];
                }
              
                 if(isset($tmpdata["unionId"]) && $unionid==""){
                $unionid = $tmpdata["unionId"];
                // file_put_contents("unionid3.log",$unionid);
                 }

            } else {
                // print($errCode . "\n");
               
            }
           }   
        //    file_put_contents("unionid2.log",$unionid);
        //    file_put_contents("addxiaochengxuephone.log",$phone);
        //    if($unionid==""){
        //     return $this->ajaxReturn(show(0, "获取微信授权失败，登陆失败"));
        //    }
            $weixininfo['openid'] = $openid;
            $weixininfo['unionid'] = $unionid;
            $weixininfo['nickname'] = $nickName;
            $weixininfo['sex'] = $sex;
            $weixininfo['headimgurl'] = $avatarUrl;


            if($unionid){
                $tmp =  Weixin_info::where("unionid='$unionid'")->find();
                 }else{
                  $tmp =  Weixin_info::where("openid='$openid'")->find();
                 }

            

            if ($tmp['id']) {
                $id = $tmp['id'];
                Weixin_info::where("id = $id")->update($weixininfo);
            } else {
                $addres = Weixin_info::create($weixininfo);
                $id = $addres['id'];
            }

           
            $ip = $this->request->ip();
            if($unionid){
                $member =  Members::where("`unionid` = '$unionid' ")->find();
               }else{
                $member = Members::where("`openid` = '$openid' ")->find();
               }

      
            // file_put_contents("addxiaochengxulogininfo.log",$encryptedData.$iv.$phone.json_encode($res). json_encode($member));
           
            // 设置
            $tmptime = 3600 * 24 * 31;
            if ($member['uid']) {
                $uid = $member['uid'];
                if($phone){
                    Members::where("uid = $uid")->update(['phone' => $phone,'lastloginip' => $ip, 'lastlogintime' => time()]);
         
                }else{
                    Members::where("uid = $uid")->update(['lastloginip' => $ip, 'lastlogintime' => time()]);
         
                }
                $token  = Str::getLoginToken($uid);
               
                $result = [
                    'phone' => $phone?$phone:$member['phone'],
                    'uid' => $uid,
                    'nickname' => $member['nickname'],
                    'avatar' => avatarchuli($member['avatar']),
                    'expire' => $tmptime,
                    'create_time' => time(),
                    "token" => $token
                ];
            } else {
                $cres = Members::create(['unionid' => $unionid, 'openid' => $openid,  'phone' => $phone, 'avatar' => $weixininfo['headimgurl'], 'nickname' => $weixininfo['nickname'], 'sex' => $weixininfo['sex'], 'regip' => $ip,'type' => 1, 'regdate' => time()]);
                $uid = $cres['id'];
                $token  = Str::getLoginToken($uid);
                
                $result = [
                    'phone' => $phone,
                    'uid' => $uid,
                    'nickname' => $weixininfo['nickname'],
                    'avatar' => $weixininfo['headimgurl'],
                    'expire' => $tmptime,
                    'create_time' => time(),
                    "token" => $token
                ];
            }

            if ($uid) {

                $res = Token::create($result);


                if (!$res) {
                    // return $this->ajaxReturn(show(0, "记录登陆信息失败"));
                    return show(config("status.error"),"记录登陆信息失败!");
                    // cookie('logininfo',$result, $result['expire']);



                } else {
                    return show(config("status.success"),"登陆成功!",$result);
         
                }
            } else {
                return show(config("status.error"),"登陆失败!");
                
            }

        }
    }


    //手机号加验证码登录
    public function index()
    {
        if(!$this->request->isPost()){
            return show(config("status.error"),"非法请求");
        }
        $phoneNumber = $this->request->param("phone_number", 0, "trim");
        $code  = input("param.code", 0, "intval");
        $type = input("param.type", 0, "intval");
      
        //参数校验
        $data = [
            'phone_number' => $phoneNumber,
            'code' => $code,
            'type' => $type,
            'last_login_ip'=>$this->request->ip(),
            'last_login_time'=>time()
        ];
        $validate = new \app\api\validate\User();
        if (!$validate->scene('login')->check($data)) {
            return show(config("status.error"), $validate->getError());
        }
        try{
            $result = (new \app\common\business\User())->login($data);
        }catch (\Exception $e){
              return show($e->getCode(),$e->getMessage());
        }
   
        if($result){
            return show(config('status.success'),"登录成功",$result);
        }

        return show(config('status.error'), "登录失败");
    }

    //用户名加密码登录
    public function usernamelogin()
    {
        if(!$this->request->isPost()){
            return show(config("status.error"),"非法请求");
        }
        $username = $this->request->param("username", "", "trim");
        $password  = input("param.password", "", "trim");
        $type = input("param.type", 0, "intval");
        //参数校验
        $data = [
            'username' => $username,
            'password' => $password,
            'type' => $type,
            'last_login_ip'=>$this->request->ip(),
            'last_login_time'=>time()
        ];
        $validate = new \app\api\validate\User();
        if (!$validate->scene('usernamelogin')->check($data)) {
            //验证用户名密码是否为空
            return show(config("status.error"), $validate->getError());
        }
        try{
            $result = (new \app\common\business\User())->usernamelogin($data);
        }catch (\Exception $e){
              return show($e->getCode(),$e->getMessage());
        }
   
        if($result){
            return show(config('status.success'),"登录成功",$result);
        }

        return show(config('status.error'), "登录失败");
    }
}
