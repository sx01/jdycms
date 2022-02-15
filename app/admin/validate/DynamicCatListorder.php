<?php 
namespace app\admin\validate;

use think\Validate;

class DynamicCatListorder extends Validate 
{

    protected $rule = [
       'id' => 'require',
       'listorder' => 'require',
     
     
    ];
    protected $message = [
        'id' => '分类ID必须',
        'listorder' => '排序必须',
       
     ];

}