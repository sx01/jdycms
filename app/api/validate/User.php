<?php
namespace app\api\validate;

use think\Validate;
class User extends  Validate {
    protected $rule = [
       'username' =>'require',
       'phone_number' => 'require|isMobileNumber',
       'code' => 'require|number|min:4',
       //'type' => 'require|in:0,1,2',
       'type' => ['require',"in"=>"0,1,2"], //两种不同的方式而已
       'sex' => ["require","in"=>"0,1,2"],
       'password'=>'require',
       'captcha'=>'require|checkCaptcha',
       'nickname'=>'require'
    ];
    protected $message = [
         'username'=>'用户名必须',
         'phone_number'=>"电话号码必须",
         'code.require'=>'短信验证码必须',
         'code.number' => '短信验证码必须为数字',
         'code.min' => '短信验证码长度不得低于4',
         'type.require' => '类型必须',
         'type.in' => '类型数值错误',
         'sex.require' =>'性别必须',
         'sex.in' => '性别数值错误',
         'password'=>'密码必须',
         'captcha'=>'验证码必须',
         'nickname'=>'昵称必须'
    ];
    protected $scene = [
      'send_code' => ['phone_number'],
      'login'=>['phone_number','code','type'],
      'usernamelogin'=>['username','password','type'],
      'update_user' => ['username','sex'],
      'reg'=>['username','nickname','password']
    ];


    //验证手机号
    public function isMobileNumber($value)
    {
        if (!is_numeric($value)) {
            return "您输入的手机号不正确";
             
        }

        $rule = '^1(3|4|5|7|8)[0-9]\d{8}$^';
        $result = preg_match($rule, $value);
        if ($result) {
            return true;
        } else {
             
            return "您输入的手机号不正确";
        }
    }

    public function checkCaptcha($value,$rule,$data = []){

        if(!captcha_check($value)){
            return "您输入的验证码不正确";
        }
        return true;
     }

}