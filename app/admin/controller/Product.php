<?php

namespace app\admin\controller;

use app\common\model\mysql\Product as ProductModel;
use think\facade\View;
use app\common\model\mysql\ProductCat;
use PHPExcel_IOFactory;
use PHPExcel;
class Product extends Base
{

    public function dcexcel()
    {
        $request = $this->request;
      
        View::assign('title', '用户管理系统');

        $data = array(
            array(NULL, 2010, 2011, 2012),
            array('Q1',   12,   15,   21),
            array('Q2',   56,   73,   86),
            array('Q3',   52,   61,   69),
            array('Q4',   30,   32,    0),
        );
        $this->create_xls($data);

 

        return View::fetch();  //渲染首页模板
    }





    /**
     * 数组转xls格式的excel文件
     * @param  array  $data      需要生成excel文件的数组
     * @param  string $filename  生成的excel文件名
     *      示例数据：
    $data = array(
    array(NULL, 2010, 2011, 2012),
    array('Q1',   12,   15,   21),
    array('Q2',   56,   73,   86),
    array('Q3',   52,   61,   69),
    array('Q4',   30,   32,    0),
    );
     */
    function create_xls($data,$filename='simple.xls'){
        ini_set('max_execution_time', '0');
//        import("Vendor.PHPExcel.PHPExcel");
//        import("Vendor.PHPExcel.PHPExcel.Writer.Excel5", '', '.php');
//        import("Vendor.PHPExcel.PHPExcel.IOFactory", '', '.php');
        // 导入Vendor类库包 Library/Vendor/Zend/Server.class.php

        $filename=str_replace('.xls', '', $filename).'.xls';


        $phpexcel =  new  PHPExcel();

        $phpexcel->getProperties()
            ->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");
        $phpexcel->getActiveSheet()->fromArray($data);
        $phpexcel->getActiveSheet()->setTitle('Sheet1');
        $phpexcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$filename");
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0 
        $objwriter = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel5');
        $objwriter->save('php://output');
        exit;
    }


    public function del(){
        $request = $this->request;
        
 

        if ($request->isAjax()) {

            $id = $request->param("id");
            $newstype =  ProductModel::where("`id`='$id'")->find();

            if ($newstype['id']) {

              

                    $res =  ProductModel::where("`id`='$id'")->delete(); // 删除id为5的用户数据

                    if ($res) {
                        $r['status'] = 1;
                        $r['tishi'] = "删除成功！";
                    } else {

                        $r['status'] = 0;
                        $r['tishi'] = "删除失败！";
                    }
                
            } else {
                $r['status'] = 0;
                $r['tishi'] = "要删除的id不存在！";
            }
            return json($r);
        }
    }
    //导出实际库存
    public function daochukucun(){
        $request = $this->request;
        $member = $this->isLogin();  //判断用户是否登录
        $uid = $member['uid'];

        $data = [];
        $name = input("param.name","","trim");
        $time = input("param.time","","trim");
        $cat_id = input("cat_id",0,"intval");
        $brand_id = input("brand_id",0,"intval");
        $gongyingshang_id = input("gongyingshang_id",0,"intval");
        $dianpu_id = input("dianpu_id",0,"intval");
        $sql = "";
        if($cat_id){

            $zicatlist = ProductCat::where("pid=$cat_id")->select();
           
            foreach($zicatlist as $v){
              $cat_id .= ",".$v['id'];
            }
             if($sql){
                 $sql .= " and cat_id in($cat_id) ";
             }else{
                 $sql .= " cat_id in($cat_id) ";
             } 

           
        }

        if($brand_id){
            if($sql){
                $sql = " and brand_id = $brand_id ";
            }else{
                $sql = " brand_id = $brand_id ";
            } 
        }
        if($gongyingshang_id){
            if($sql){
                $sql = " and gongyingshang_id = $gongyingshang_id ";
            }else{
                $sql = " gongyingshang_id = $gongyingshang_id ";
            } 
        }

        if($dianpu_id){
            if($sql){
                $sql = " and dianpu_id = $dianpu_id ";
            }else{
                $sql = " dianpu_id = $dianpu_id ";
            } 
        }
        if(!empty($name)){
            if($sql){
                $sql = " and name like '%$name%' ";
            }else{
                $sql = " name like '%$name%' ";
            } 
        }
        if(!empty($time)){
            $timearr = explode("到",$time);
            foreach($timearr as $k=>$v){
                $timearr[$k] = strtotime(trim($v));
            }

            if($sql){
                $sql = " and addtime >$timearr[0] ";
            }else{
                $sql = "addtime < $timearr[1] ";
            } 
 
        }
     
        $ascdesc = "asc";
        if(input("ascdesc")){
        $ascdesc = input("ascdesc");
        }
        $order = "paixu";
        if(input("order")){
            $ascdesc = input("order");
            }
         
            $list = ProductModel::where($sql)->order($order, $ascdesc)->paginate(200);
            $ProductCatList = ProductCat::select()->toArray();
 // 获取分页显示
 $page = $list->render();
 // 模板变量赋值
 View::assign('list', $list);
 View::assign('page', $page);


            $ProductCatList = array_column($ProductCatList, NULL, 'id'); //二维下的id字段 为一维数组的key
            View::assign('productCatList', $ProductCatList); // 赋值数据集


             
            $data = array(
                array("品牌","供应商","名称", "规格型号", "进销库存","实际库存", "标准库存","建议进货","计量单位","进货单价","店铺"),
            );

            foreach($list as $k=>$v){
                $data[]= array(getBrandNameById($v['brand_id']),getGongyingshangNameById($v['gongyingshang_id']),$v['name'], $v['guige'], $v['jinxiaokucun'],$v['kucun'], $v['yujingkucun'],$v['jianyijinhuo'], $v['danwei'], $v['jinhuojia'],getDianpuNameById($v['dianpu_id']));
            }

            $this->create_xls($data,date("Y-m-d-His",time())."产品库存表.xls");
    
     
    
            return View::fetch();  //渲染首页模板
 
     
 
  
    }
    public function index()
    {
        $request = $this->request;
        $member = $this->isLogin();  //判断用户是否登录
        $uid = $member['uid'];

        $data = [];
        $name = input("param.name","","trim");
        $time = input("param.time","","trim");
        $cat_id = input("cat_id",0,"intval");
        $brand_id = input("brand_id",0,"intval");
        $gongyingshang_id = input("gongyingshang_id",0,"intval");
        $dianpu_id = input("dianpu_id",0,"intval");
        $sql = "";
        if($cat_id){

            $zicatlist = ProductCat::where("pid=$cat_id")->select();
           
            foreach($zicatlist as $v){
              $cat_id .= ",".$v['id'];
            }
             if($sql){
                 $sql .= " and cat_id in($cat_id) ";
             }else{
                 $sql .= " cat_id in($cat_id) ";
             } 

           
        }

        if($brand_id){
            if($sql){
                $sql = " and brand_id = $brand_id ";
            }else{
                $sql = " brand_id = $brand_id ";
            } 
        }
        if($gongyingshang_id){
            if($sql){
                $sql = " and gongyingshang_id = $gongyingshang_id ";
            }else{
                $sql = " gongyingshang_id = $gongyingshang_id ";
            } 
        }

        if($dianpu_id){
            if($sql){
                $sql = " and dianpu_id = $dianpu_id ";
            }else{
                $sql = " dianpu_id = $dianpu_id ";
            } 
        }
        if(!empty($name)){
            if($sql){
                $sql = " and name like '%$name%' ";
            }else{
                $sql = " name like '%$name%' ";
            } 
        }
        if(!empty($time)){
            $timearr = explode("到",$time);
            foreach($timearr as $k=>$v){
                $timearr[$k] = strtotime(trim($v));
            }

            if($sql){
                $sql = " and addtime >$timearr[0] ";
            }else{
                $sql = "addtime < $timearr[1] ";
            } 
 
        }
     
        $ascdesc = "asc";
        if(input("ascdesc")){
        $ascdesc = input("ascdesc");
        }
        $order = "paixu";
            if(input("order")){
            $ascdesc = input("order");
            }
         
            $list = ProductModel::where($sql)->order($order, $ascdesc)->paginate(200);
            $ProductCatList = ProductCat::select()->toArray();
 // 获取分页显示
 $page = $list->render();
 // 模板变量赋值
 View::assign('list', $list);
 View::assign('page', $page);


            $ProductCatList = array_column($ProductCatList, NULL, 'id'); //二维下的id字段 为一维数组的key
            View::assign('productCatList', $ProductCatList); // 赋值数据集


             
 
     
        return view('', ["list" => $list,'name'=>$name,'time'=>$time,'order'=>$order,'ascdesc'=>$ascdesc]);
    }
    public function add()
    {
        $request = $this->request;
        $member = $this->isLogin();  //判断用户是否登录
        $uid = $member['uid'];

        try {
            $ProductCats = ProductCat::select();
        } catch (\Exception $e) {
            $ProductCats = [];
        }

        try {
            $brandList = \app\common\model\mysql\Brand::select();
        } catch (\Exception $e) {
            $brandList = [];
        }
        try {
            $gongyingshangList = \app\common\model\mysql\Gongyingshang::select();
        } catch (\Exception $e) {
            $gongyingshangList = [];
        }
        try {
            $dianpuList = \app\common\model\mysql\Dianpu::select();
        } catch (\Exception $e) {
            $dianpuList = [];
        }
        return View::fetch('',['dianpulist'=>$dianpuList,'gongyingshanglist'=>$gongyingshangList,'brandlist'=>$brandList,'fanhuiurl'=>url("index"),'catlist'=>\app\common\lib\Tree::tree($ProductCats)]);
     
    }

    public function edit()
    {
         // 模板变量赋值
         $request = $this->request;
         $member = $this->isLogin();  //判断用户是否登录
         $uid = $member['uid'];
         
         $id = input("param.id", 0, "intval");
         if (!$id) {
             $find = [];
         } else {
             try {
                 $find = ProductModel::where("id=$id")->find();
                  
                 $find['pic'] = str_replace(request()->domain(),"",$find['pic']);
             } catch (\Exception $e) {
                 $find = [];
             }
         }
 
         try {
             $ProductCats = ProductCat::select();
         } catch (\Exception $e) {
             $ProductCats = [];
         }
 
         try {
            $brandList = \app\common\model\mysql\Brand::select();
        } catch (\Exception $e) {
            $brandList = [];
        }
        try {
            $gongyingshangList = \app\common\model\mysql\Gongyingshang::select();
        } catch (\Exception $e) {
            $gongyingshangList = [];
        }
        try {
            $dianpuList = \app\common\model\mysql\Dianpu::select();
        } catch (\Exception $e) {
            $dianpuList = [];
        }
        
         // $find["create_time"] = date("Y-m-d H:i:s",$find["create_time"]);

        
         return view('', [ 
             "find" => $find,
             "id" => $id,
             'fanhuiurl'=>url("index"),
             "catlist" =>  \app\common\lib\Tree::tree($ProductCats),
             'dianpulist'=>$dianpuList,
             'gongyingshanglist'=>$gongyingshangList,
             'brandlist'=>$brandList,
         ]);
       
    }

    /**
     * 新增保存数据
     */
    public function save()
    {

        $request = $this->request;
        $member = $this->isLogin();  //判断用户是否登录
        $uid = $member['uid'];
        //判断是否post情况
        if (!$this->request->isPost()) {
            return show(config("status.error"), "参数不合法");
        }
        $id = input("param.id", 0, "intval");
        $data = input("param.");
        $check = $this->request->checkToken('__token__');
        if(!$check){
            return show(config('status.error'),"非法请求");
        }
         unset($data['__token__']);
        $validate = new \app\admin\validate\Product();
        if (!$validate->check($data)) {
            return show(config("status.error"), $validate->getError());
        }
   
        //  $data['create_time'] = strtotime($data['create_time']);
       
       
        if ($id) {

            $data['updatetime'] = time();

           $find = \app\common\model\mysql\Product::where(" `brand_id`= '$data[brand_id]' and `gongyingshang_id`= '$data[gongyingshang_id]' and `guige`='$data[guige]' and name='$data[name]'")->find();
          if($find && $find['id']!=$id){
            return show(config("status.error"), "修改失败，同一名称 品牌 供应商 规格型号 完全一样的已经存在！");
          }
            $result =  \app\common\model\mysql\Product::where("id = $id ")->update($data);
            
            
            if ($result!==false) {

                      //前台logo开始
            // 捕获异常
            try {
                // 此时可能会报错
                // 比如:上传的文件过大,超出了配置文件中限制的大小
                $file = request()->file('pic');
                // 如果表单没有设置文件上传需要的编码 $file始终是null
                if (is_null($file)) {
                    // 请检查请求类型和表单编码
                    // 不是post请求或没有指定enctype="multipart/form-data"会进入这里
                    // throw new \think\Exception('没有文件上传');
                } else {
                    $dir = $this->app->getRootPath() . 'uploads';

                    !is_dir($dir) && mkdir($dir, 0777); // 如果文件夹不存在，将以递归方式创建该文件夹 

                    $dir = $this->app->getRootPath() . 'uploads/product';

                    !is_dir($dir) && mkdir($dir, 0777); // 如果文件夹不存在，将以递归方式创建该文件夹 

                    // 使用验证器验证上传的文件
                    validate(
                        [
                            'pic' => [
                                // 限制文件大小(单位b)，这里限制为4M
                                'fileSize' => 1 * 1024 * 1024,
                                // 限制文件后缀，多个后缀以英文逗号分割
                                'fileExt'  => 'gif,jpg,png'
                            ]
                        ],
                        [
                            'pic.fileSize' => '文件太大',
                            'pic.fileExt' => '不支持的文件后缀',
                        ]
                    )->check(['pic' => $file]);

                    $savename = "product_".$id;

                    if ($savename) {
                        $filename = \think\facade\Filesystem::disk("uploads")->putFileAs('product', $file, $savename . '.' . $file->extension());
                    } else {
                        $filename = \think\facade\Filesystem::disk("uploads")->putFile("product", $file);
                    }



                    // 直接这样即可：
                    // $dir = ROOT_PATH .    'uploads' . DS . "logo";
                    // is_dir($dir) OR mkdir($dir, 0755, true);   // 如果文件夹不存在，将以递归方式创建该文件夹
                    // $info = $file->validate(['size' => 2 * 1024 * 1024, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . "logo", "logo");

                    if ($filename) {
                        // 成功上传后 获取上传信息
                        // 输出 jpg
                        // echo $info->getExtension();
                        // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                        //echo $info->getSaveName();
                        // 输出 42a79759f284b767dfcb2a0197904287.jpg
                        //echo $info->getFilename();

                        $path = '/uploads/' . $filename;


                        $data_img['pic'] = $path;

                      

                        $result = \app\common\model\mysql\Product::where("id = $id ")->update($data_img);
                         
                    }
                }
            } catch (\think\Exception $e) {
                // 获取异常错误信息
                // halt($e->getMessage());
            }
            //前台logo结束
                // return show(config("status.success"), "修改成功");
                $this->success("修改成功!", url('index'), 3);
            } else {
                // return show(config("status.error"), "修改失败");
                $this->error("修改失败!", url('edit',['id'=>$id]), 3);
            }
        } else {

            $data['addtime'] = time();
            $find = \app\common\model\mysql\Product::where(" `brand_id`= '$data[brand_id]' and `gongyingshang_id`= '$data[gongyingshang_id]' and `guige`='$data[guige]' and name='$data[name]'")->find();
            if($find){
              return show(config("status.error"), "添加失败，同一名称 品牌 供应商 规格型号 完全一样的已经存在！");
            }
           $res = \app\common\model\mysql\Product::create($data);
            $resid = $res['id'];
            
            if (!$resid) {
                return show(config("status.error"), "新增内容失败");
                $this->error("新增内容失败!", url('add'), 3);
            }

                 //前台logo开始
            // 捕获异常
            try {
                // 此时可能会报错
                // 比如:上传的文件过大,超出了配置文件中限制的大小
                $file = request()->file('pic');
                // 如果表单没有设置文件上传需要的编码 $file始终是null
                if (is_null($file)) {
                    // 请检查请求类型和表单编码
                    // 不是post请求或没有指定enctype="multipart/form-data"会进入这里
                    // throw new \think\Exception('没有文件上传');
                } else {
                    $dir = $this->app->getRootPath() . 'uploads';

                    !is_dir($dir) && mkdir($dir, 0777); // 如果文件夹不存在，将以递归方式创建该文件夹 

                    $dir = $this->app->getRootPath() . 'uploads/product';

                    !is_dir($dir) && mkdir($dir, 0777); // 如果文件夹不存在，将以递归方式创建该文件夹 

                    // 使用验证器验证上传的文件
                    validate(
                        [
                            'pic' => [
                                // 限制文件大小(单位b)，这里限制为4M
                                'fileSize' => 1 * 1024 * 1024,
                                // 限制文件后缀，多个后缀以英文逗号分割
                                'fileExt'  => 'gif,jpg,png'
                            ]
                        ],
                        [
                            'pic.fileSize' => '文件太大',
                            'pic.fileExt' => '不支持的文件后缀',
                        ]
                    )->check(['pic' => $file]);

                    $savename = "product_".$resid;

                    if ($savename) {
                        $filename = \think\facade\Filesystem::disk("uploads")->putFileAs('product', $file, $savename . '.' . $file->extension());
                    } else {
                        $filename = \think\facade\Filesystem::disk("uploads")->putFile("product", $file);
                    }



                    // 直接这样即可：
                    // $dir = ROOT_PATH . 'public' . DS . 'uploads' . DS . "logo";
                    // is_dir($dir) OR mkdir($dir, 0755, true);   // 如果文件夹不存在，将以递归方式创建该文件夹
                    // $info = $file->validate(['size' => 2 * 1024 * 1024, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . "logo", "logo");

                    if ($filename) {
                        // 成功上传后 获取上传信息
                        // 输出 jpg
                        // echo $info->getExtension();
                        // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                        //echo $info->getSaveName();
                        // 输出 42a79759f284b767dfcb2a0197904287.jpg
                        //echo $info->getFilename();

                        $path = '/uploads/' . $filename;


                        $data_img['pic'] = $path;

                        $result = \app\common\model\mysql\Product::where("id = $resid ")->update($data_img);
                         
                    }
                }
            } catch (\think\Exception $e) {
                // 获取异常错误信息
                // halt($e->getMessage());
            }
            //前台logo结束
            $this->success("添加成功！", url('index'), 3);
          
        }
       
    }

   
   
     /**
     * 更新状态
     */
    public function editfield()
    {
         $value = input("param.value");
        $field= input("param.field");
        $id =  input("param.id", 0, "intval");
        if (!$id || !$field ) {
            
       
        }
        if(!$value){
        if($value == 0){

        }else{
            return show(config('status.error'), "参数错误");
        }
        }
        try {
            $res = ProductModel::where("id=$id")->update([$field=>$value]);

        } catch (\Exception $e) {
            return show(config('status.error'), $e->getMessage());
        }
        if ($res) {
            return show(config("status.success"), "更新成功");
        } else {
            return show(config("status.success"), "更新失败");
        }
    }

    

}
