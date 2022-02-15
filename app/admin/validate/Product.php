<?php 
namespace app\admin\validate;

use think\Validate;

class Product extends Validate 
{

    protected $rule = [
       'name' => 'require', 
        
       
    ];
    protected $message = [
        'name' => '标题必须', 
        
        
       
     ];

}