<?php 
namespace app\admin\validate;

use think\Validate;

class Gongyingshang extends Validate 
{

    protected $rule = [
       'name' => 'require',  
       
    ];
    protected $message = [
        'name' => '名称必须',  
       
     ];

}