<?php
namespace Home\Controller;
use Common\Controller\BaseController;
use Common\Model\UserModel;

/**
 * 管理员接口
 * @author Jayin
 *
 */
class AdminController extends BaseController {

    /**
     * 创建一个管理员
     */
    public function create(){
    	$this->reqPost(array('nickname','email','psw','per_categorys_post','per_categorys_check','per_institution_check','per_person_man','per_employer_man'))->reqSuperAdmin();
        $nickname = I('post.nickname');
        $phone = I('post.phone',null);
        $email = I('post.email');
        $psw = I('post.psw');
        $per_categorys_post = I('post.per_categorys_post','[]');
        $per_categorys_check = I('post.per_categorys_check','[]');
        $per_institution_check = I('post.per_institution_check','0');
        $per_person_man = I('post.per_person_man','0');
        $per_employer_man = I('post.per_employer_man','0');
        $res = D('UserAdmin')->createAdmin($nickname,$phone,$email,$psw,$per_categorys_post,$per_categorys_check,$per_institution_check,$per_person_man,$per_employer_man);
        if($res['status']){
            $this->ajaxReturn(mz_json_success());
        }else{
            $this->ajaxReturn(mz_json_error($res['msg']));
        }
    } 
     
    /**
     * 修改管理员权限 
     * @author wharry
     */
    public function update(){
    	$this->reqPost(array('admin_id','per_categorys_post','per_categorys_check','per_institution_check','per_person_man','per_employer_man','status'))->reqSuperAdmin();
        $admin_id = I('post.admin_id');
        $per_categorys_post = I('post.per_categorys_post','[]');
        $per_categorys_check = I('post.per_categorys_check','[]');
        $per_institution_check = I('post.per_institution_check','0');
        $per_person_man = I('post.per_person_man','0');
        $per_employer_man = I('post.per_employer_man','0');
        $status = I('status','1');
        $res = D('UserAdmin')->updateAdmin($admin_id,$per_categorys_post,$per_categorys_check,$per_institution_check,$per_person_man,$per_employer_man,$status);
        if($res['status']){
            $this->ajaxReturn(mz_json_success());
        }else{
            $this->ajaxReturn(mz_json_error($res['msg']));
        }
    } 
     
    /**
     * 验证（更换权限）
     * 需要超级管理员权限
     * @post admin_id
     * @post 1正常 -2冻结
     */
    public function vertify(){
        $this->reqPost(array('admin_id','op'))->reqSuperAdmin();
        $res = D('UserAdmin')->vertify(I('post.admin_id'),I('post.op'));
        if($res['status']){
            $this->ajaxReturn(mz_json_success('vertify successfully'));
        }else{
            $this->ajaxReturn(mz_json_error($res['msg']));
        }
        
    }
    /**
     * 删除一个管理员
     */
    public function deleteAdmin(){
        $this->reqPost(array('admin_id'))->reqSuperAdmin();
        //调用model
        $res = D('User')->deleteUser(I('post.admin_id'),UserModel::LEVEL_ADMIN);
        if($res['status']){
            $this->ajaxReturn(mz_json_success());
        }else{
            $this->ajaxReturn(mz_json_error($res['msg']));
        }
        
    }
    
    /**
     * 获得管理员的信息
     * @param unknown $admin_id
     */
    public function getInfo($admin_id){
        $this->reqAdmin();
        $res = D('UserAdmin')->info($admin_id);
        if($res['status']){
            $this->ajaxReturn(mz_json_success($res['msg']));
        }else{
            $this->ajaxReturn(mz_json_error($res['msg']));
        }
    }
    /**
     * 获得管理员列表
     * @param string $status
     * @param string $nickname
     * @param number $page
     * @param number $limit
     */
    public function lists($status=null,$nickname=null,$page=1,$limit=10){
        $res = D('UserAdmin')->search($status,$nickname,$page,$limit);
        $this->ajaxReturn(mz_json_success($res['msg']));
    }
}

 