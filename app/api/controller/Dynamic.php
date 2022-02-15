<?php

namespace app\api\controller;

use app\common\model\mysql\Dynamic as MysqlDynamic;

class Dynamic  extends ApiBase
{

     

 // 舌尖霸王餐商家首页信息
    public function detail()
    {
        // $this -> isLogin();  //判断用户是否登录
        // $this->view->assign('title', config("websitename"));

        $result = [];
        $time = time(); 
        // $tupianlist= Dynamic::where("status = 0 and cat_id=3 ")->order(" create_time desc ")->limit("0,8")->select();
          
    //   $websiteurl = config("siteinfo.websiteurl")."/";
        // foreach($tupianlist as $k=>$v){
        //  $v['pic_path'] = str_replace("./","",$v['pic_path']);
        //  $v['pic_path'] = $websiteurl. $v['pic_path'];
        //  // $v['content'] = contentTupianChuli($v['content']);
        //  $v['content']  = "";
        //   $tupianlist[$k]=$v;
        // }
        // $result['tupianlist'] = $tupianlist;// 获取内容头条
 
        $id = input("id");
        if($id){
            $websiteurl = config("siteinfo.websiteurl");
            $result['find'] = getContentById($id);// 获取内容头条
            $result['find']['pic_path']  =  $websiteurl.str_replace("./","", $result['find']['pic_path']);
            $result['find']['content']=contentTupianChuli($result['find']['content']);
     
             
            return show(config("status.success"),"绑定手机号成功！",$result);
    
        }else{
            return show(config("status.error"),"id不能为多空");
        }
 
       
     
    }

// 更加分类ID获得动态内容列表
public function catlist()
{
    $cat_id = input("cat_id");
   
    if ($cat_id) {

        $sql = " status=0 and  cat_id=$cat_id ";
        $searchword = "";
        $searchword = input("keyword");

        if ($searchword) {
            $sql .= " and title like '%$searchword%' ";
        }

        // 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
        $list = MysqlDynamic::where($sql)->order('paixu asc')->paginate(10);
     
   
         $datalist = $list->toArray();

         $websiteurl = config("siteinfo.websiteurl");

        //给结果集对象数组中的每个模板对象添加班级关联数据,非常重要
        foreach ( $datalist['data'] as $k => $v) {

          
            $v['pic_path'] = str_replace("./","",$v['pic_path']);
            //   $v['pic_path'] = $websiteurl. "/" .$v['pic_path'];
          
              $v['content']  = ""; 
            $datalist['data'][$k]=$v;
            
        }
     
        return show(config("status.success"),"获取内容成功！",$datalist);
     
    }else{
        return show(config("status.error"),"分类ID不能为空！");
    }

}



    public function _empty($method)
    {

        return '访问的方法' . $method . '不存在';
    }
}
