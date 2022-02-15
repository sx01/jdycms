<?php 
namespace app\admin\validate;

use think\Validate;

class ProductCat extends Validate 
{

    protected $rule = [
       'name' => 'require',
       'pid' => 'require',
     
     
    ];
    protected $message = [
        'name' => '分类名称必须',
        'pid' => '父类ID必须',
       
     ];

}