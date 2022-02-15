<?php 
namespace app\admin\validate;

use think\Validate;

class Dynamic extends Validate 
{

    protected $rule = [
       'title' => 'require', 
        
       
    ];
    protected $message = [
        'title' => '标题必须', 
        
        
       
     ];

}