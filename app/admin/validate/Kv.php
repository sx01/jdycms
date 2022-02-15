<?php 
namespace app\admin\validate;

use think\Validate;

class Kv extends Validate 
{

    protected $rule = [
   
       'key' => 'require',
  
     
     
    ];
    protected $message = [
     
        'key' => 'key内容名称必须',
       
       
     ];

}