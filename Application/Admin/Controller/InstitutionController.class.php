<?php
namespace Admin\Controller;
use Common\Controller\BaseController;
use Common\Model\UserInstitutionModel;

/**
 * 机构用户管理
 * 
 * @author Jayin
 *        
 */
class InstitutionController extends BaseController {

    private $institution = null;

    /**
     * 需要获得机构用户信息
     * 
     * @return \Admin\Controller\InstitutionController
     */
    protected function reqInstituion() {
        $this->institution = (new UserInstitutionModel())->createInsById(
                session('uid'));
        if (! $this->institution) {
            $this->logout();
            $this->redirect('admin/institution/index');
        }
        $this->institution = $this->institution->getData();
        $this->assign('institution', $this->institution);
        return $this;
    }

    public function index() {
        if($this->isLogin() && $this->reqInstituion()){
            $this->redirect('admin/institution/manage');
        }
        $this->display();
    }

    /**
     * POST 登录
     */
    public function login() {
        $this->reqPost();
        
        $account = I('post.account');
        $psw = md5(I('post.psw'));
        $User = D('User');
        if (strstr($account, '@')) {
            $res = $User->login('email', $account, $psw);
        } else {
            $res = $User->login('phone', $account, $psw);
        }
        if ($res['status']) {
            session('uid', $res['msg']['uid']);
            $this->ajaxReturn(mz_json_success(U('admin/institution/manage')));
        } else {
            $this->ajaxReturn(mz_json_error($res['msg']));
        }
    }

    public function manage() {
        $this->reqInstituion();
        $this->display();
    }

    public function updateInfo() {
        $this->reqInstituion();
        $this->display();
    }

    public function postCourse($institution_id = 0) {
        $this->reqInstituion();
        $this->display();
    }
    
    public function courseList() {
        $this->reqInstituion();
        $res  = D('Course')->search($this->institution['uid']);
        $this->assign('courses',$res['msg']);
        $this->display();
    }
}

 