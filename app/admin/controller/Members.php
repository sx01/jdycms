<?php

namespace app\admin\controller;

use app\common\model\mysql\Members as MembersModel;
use think\facade\View;
 
use app\common\business\MembersCat as MembersCatBus;
class Members extends Base
{
 
    public function index()
    {
        $request = $this->request;
        $member = $this->isLogin();  //判断用户是否登录
        $uid = $member['uid'];

        $data = [];
        $name = input("param.name","","trim"); 
        $type = input("type",0,"intval");
        $sql = "";
       
      
       
        if(!empty($name)){
            if($sql){
                $sql = " and nickname like '%$name%' ";
            }else{
                $sql = " nickname like '%$name%' ";
            } 
        }
         
     
        $ascdesc = "desc";
        if(input("ascdesc")){
        $ascdesc = input("ascdesc");
        }
        $order = "regdate";
        if(input("order")){
            $ascdesc = input("order");
            }
         
            $list = MembersModel::where($sql)->order($order, $ascdesc)->paginate(10);
             
 // 获取分页显示
 $page = $list->render();
 // 模板变量赋值
 View::assign('list', $list);
 View::assign('page', $page);
 

 try {
    $MembersCats = (new MembersCatBus())->getNormalMembersCats();
} catch (\Exception $e) {
    $MembersCats = [];
}
     
        return view('', ["catlist" =>  \app\common\lib\Tree::tree($MembersCats),"list" => $list,'name'=>$name,'type'=>$type,'order'=>$order,'ascdesc'=>$ascdesc]);
    }
  
    /**
     * 修改用户名和密码
     */
    public function editup(){
        $uid = input("param.uid", 0, "intval");
        $username= input("param.username");
        $password =  input("param.password");
        if ($uid==0 || $username=="" || $password=="") {
            
            return show(config('status.error'), "用户名密码不能为空！");
        }

        if(!preg_match("/^[a-zA-Z0-9]{6,16}$/",$password) ){
      
            return show(config('status.error'), "密码由6-16位数字和字母组成,请检查");
        }
         
         //查询条件
         $map = [
            'username' => $username,
            //'password' => md5($data['password'])
        ];

        //数据表查询,返回模型对象
        $user = MembersModel::where($map)->find();


        if (null === $user) {

          
            $salt = substr(uniqid(rand()), -6);
            $password = md5(md5($password).$salt);
            try {
                $res = MembersModel::where("uid=$uid")->update(["username"=>$username,"password"=>$password,"salt"=>$salt]);
    
            } catch (\Exception $e) {
                return show(config('status.error'), $e->getMessage());
            }
            if ($res!==false) {
                return show(config("status.success"), "修改成功");
            } else {
                return show(config("status.error"), "更新失败");
            }
             
        } else {

            //用户名已经存在了 只修改密码

            $salt = substr(uniqid(rand()), -6);
            $password = md5(md5($password).$salt);
            try {
                $res = MembersModel::where("uid=$uid")->update(["password"=>$password,"salt"=>$salt]);
    
            } catch (\Exception $e) {
                return show(config('status.error'), $e->getMessage());
            }
            if ($res!==false) {
                return show(config("status.success"), "修改成功");
            } else {
                return show(config("status.error"), "更新失败");
            }

            // return show(config('status.error'), "用户名已经存在了，再换一个！");
        }
        
    }
   
     /**
     * 更新状态
     */
    public function editfield()
    {
         $value = input("param.value");
        $field= input("param.field");
        $uid =  input("param.uid", 0, "intval");
        if (!$uid || !$field ) {
            
            return show(config('status.error'), "uid和字段名不能为空");
        }
        if(!$value){
        if($value == 0){

        }else{
            return show(config('status.error'), "参数错误");
        }
        }

        if($field=="status" && $uid==1){
            return show(config('status.error'), "管理员不能修改审核状态！");

        }
        try {
            $res = MembersModel::where("uid=$uid")->update([$field=>$value]);

        } catch (\Exception $e) {
            return show(config('status.error'), $e->getMessage());
        }
        if ($res!==false) {
            return show(config("status.success"), "更新成功");
        } else {
            return show(config("status.success"), "更新失败");
        }
    }

    public function del(){
        $request = $this->request;
     
        View::assign('title', '用户管理系统');

 

        if ($request->isAjax()) {

            $uid = $request->param("uid");
            $newstype =  MembersModel::where("`uid`='$uid'")->find();

            if ($newstype['uid']) {

              

                    $res =  MembersModel::where("`uid`='$uid'")->delete(); // 删除id为5的用户数据

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

}
