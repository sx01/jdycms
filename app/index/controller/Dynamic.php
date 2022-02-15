<?php
namespace app\index\controller;

 use \app\common\model\mysql\Article_title;
 use \app\common\model\mysql\Portal_article_title;
use  think\facade\View;

class Dynamic  extends IndexBase
{

    public function index()
    {
        $request = $this->request;
    
        View::assign('title', '动态内容_'.config("siteinfo.websitename"));
       
      
        $id = input("id",0,"intval");
       
        if($id){
          $find = \app\common\model\mysql\Dynamic::where("id = $id")->find();
          \app\common\model\mysql\Dynamic::where("`id`=$id")->update(["view"=>$find['view']+1]);
         View::assign('find', $find);// 赋值数据集
         $menuname = $find['title'];
         View::assign('menuname', $menuname);
         View::assign('title', $find['title']);

          $dynamicCatList =  getAllDynamicCatList();
         
          $dynamicCatList = array_column($dynamicCatList, NULL, 'id'); //二维下的id字段 为一维数组的key
          View::assign('dynamicCatList', $dynamicCatList); // 赋值数据集
          $cat_id = $find['cat_id'];
          $dynamic_id = $find['id'];
          $catFieldList =  getAllDynamicCatFieldListByCatId($cat_id);

                if ($dynamic_id) {
                    $catFieldContent = \app\common\model\mysql\DynamicFieldContent::where("cat_id=$cat_id and dynamic_id=$dynamic_id")->select();
                    $catFieldContent = array_column($catFieldContent->toArray(), NULL, 'cat_field_id');
                    foreach ($catFieldList as $k => $v) {
                        if (isset($catFieldContent[$v['id']])) {
                            $v['content'] = $catFieldContent[$v['id']]['content'];
                        } else {
                            $v['content'] = "";
                        }
                        $find[$v['name']] =$v['content'];//把每个拓展字段 加道find里面
                        $catFieldList[$k] = $v;
                    }
                }


           View::assign('catFieldList', $catFieldList); // 赋值数据集
  
       
            return View::fetch("");  //渲染首页模板
    
          

        }else{
          $this->error("id不能为空",url('index/index/index'));
        } 

    }


    public function catcontentlist(){

      $data = [];
      $name = input("param.name", "", "trim");
      $time = input("param.time", "", "trim");
      $cat_id = input("cat_id", 0, "intval");
      if ($cat_id) {
          $data['cat_id'] = $cat_id;
      }
      if (!empty($name)) {
          $data['title'] = $name;
      }
      if (!empty($time)) {
          $timearr = explode("到", $time);
          foreach ($timearr as $k => $v) {
              $timearr[$k] = strtotime(trim($v));
          }
          $data['create_time'] = $timearr;
      }



      $dynamicCatList =  getAllDynamicCatList();
      $dynamicCatList = array_column($dynamicCatList, NULL, 'id'); //二维下的id字段 为一维数组的key


      View::assign('dynamicCatList', $dynamicCatList); // 赋值数据集

       $cat_name = "";
      if($cat_id){
        $cat_name = isset($dynamicCatList[$cat_id])?$dynamicCatList[$cat_id]['name']:'';
      }
      View::assign('cat_name', $cat_name); // 赋值数据集
 
      $list = \app\common\model\mysql\Dynamic::where($data)->order('id', 'desc')->paginate(10);

      
      // 获取分页显示
      $page = $list->render();
      $count = $list->count();


      return view('', ['count'=>$count,"list" => $list->toArray(), 'page' => $page, 'name' => $name, 'time' => $time]);
    }


    



     
}
