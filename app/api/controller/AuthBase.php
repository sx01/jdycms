<?php
namespace app\api\controller; 

use app\common\lib\Show;
use app\common\model\mysql\Token;
class AuthBase extends ApiBase{

   


     public $uid = 0;
    public $username = "";
    public $nickname = "";
    public $avatar = "";
    public $accessToken = "";
    public $isLogin = 0;

    public function initialize(){
        parent::initialize();
        // if($this->isLogin == 1){
        //     $this->userId = 20;
        //     return true;
        // }
         
        //判断是否登陆
        $this->accessToken = $this->request->header("access-token");
       
     
        if(!$this->accessToken  || !$this->isLogin()){
          $json = $this->show(config("status.not_login"),"没有登陆");
          $json->send();
          exit;
        }
 
    }
/**
 * 判断用户是否登陆
 */
    public function isLogin(){

        $userInfo = Token::where("token = '$this->accessToken'") ->find(); 

        // $userInfo = cache(config("redis.token_pre").$this->accessToken);
        if(!$userInfo){
            return false;
        }else{
           
           if($userInfo['uid']){
            $this->phone = $userInfo['phone'];
            $this->uid = $userInfo['uid'];
            $this->nickname = $userInfo['nickname'];
            $this->avatar = $userInfo['avatar'];
            return true;
              
           }else{
            return false;
           }

        }


    }
    public function show(...$args) {
    
        return   Show::success($args[0],$args[1]);

         
        
      
   }
  

   
}