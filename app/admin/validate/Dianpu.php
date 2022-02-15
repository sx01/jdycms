<?php 
namespace app\admin\validate;

use think\Validate;

class Dianpu extends Validate 
{

    protected $rule = [
       'name' => 'require', 
       'phone' => 'require', 
       
    ];
    protected $message = [
        'name' => '标题必须', 
        'phone' => '手机号必须', 
        
       
     ];

}