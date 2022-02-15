<?php

namespace app\index\controller;


use think\facade\Db;
use think\facade\Request;
use think\facade\View;
use app\common\lib\IdWork;
use app\common\model\mysql\Product as ProductModel;
use app\common\model\mysql\ProductCat;

class Index  extends IndexBase
{
    

    public function index()
    {
 

        $menuname = "首页";
        View::assign('menuname', $menuname);
        
        return view('', []);
    }
    public function anli()
    {
 

        return view('', []);
    }
    public function fuwu()
    {
 

        return view('', []);
    }

    public function about()
    {
 

        return view('', []);
    }


    public function _empty($method)
    {

        return '访问的方法' . $method . '不存在';
    }
}
