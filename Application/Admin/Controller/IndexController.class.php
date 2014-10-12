<?php
namespace Admin\Controller;
use Common\Controller\BaseController;
use Common\Model\UserAdminModel;
use Common\Model\UserModel;
use Common\Model\DocumentModel;

class IndexController extends BaseController {
    
    private $admin = null;

    /**
     * 需要获得用户信息
     * 
     * @return \Admin\Controller\IndexController
     */
    protected function reqAdmin() {
        $this->admin = (new UserAdminModel())->createAdminById(session('uid'));
        if (! $this->admin) {
            $this->redirect('admin/index/index');
        }
        $this->assign('admin',$this->admin->getData());
        return $this;
    }
    
    public function index() {
        if($this->isLogin()){
            $this->redirect('admin/index/manage');
        }
        $this->display();
    }
    
    /**
     * POST 登录
     */
    public function login(){
        $this->reqPost();
        
        $account = I('post.account');
        $psw = md5(I('post.psw'));
        $User = D('User');
        if(strstr($account,'@')){
            $res = $User->login('email',$account,$psw);
        }else{
            $res = $User->login('phone',$account,$psw);
        }
        if($res['status']){
            session('uid',$res['msg']['uid']);
            $this->ajaxReturn(mz_json_success(U('admin/index/manage')));
        }else{
            $this->ajaxReturn(mz_json_error($res['msg']));
        }
    }

    public function manage() {
       $this->reqAdmin();
      
       $this->display();
    }
    
    public function checkInstitution(){
        $this->reqAdmin();
        //TODO MZ::改为watting
        $res = D('UserInstitution')->search(UserModel::STATUS_PASS);
        $institutions = $res['msg'];
        $this->assign('institutions', $institutions);
        $this->display();
    }

    public function checkDocument($categoryId = 1) {
        // TODO MZ:: 注意文档状态
        $this->reqAdmin();
        $res = D('Document')->search($categoryId,null,null,DocumentModel::VERIFY_WAITING);
        print_r(DocumentModel::VERIFY_WAITING);
        $documents = $res['msg'];
        $this->assign('documents',$documents);
        $this->display();
    }
    
    public function postDocument(){
        $this->reqAdmin();
        $this->display();
    }
    
    public function viewDocument($doc_id = 0){
        //TODO 处理没有找到该文章的情况
        // TODO 处理禁止显示的情况
        $res =D('Document')->getDocumentInfo($doc_id);
        $document = $res['msg'];
        $this->assign('document',$document);
        $this->display();
    }
}