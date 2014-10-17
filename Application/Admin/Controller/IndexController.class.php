<?php
namespace Admin\Controller;
use Common\Controller\BaseController;
use Common\Model\UserAdminModel;
use Common\Model\UserModel;
use Common\Model\DocumentModel;
/**
 * 管理员页面控制
 * @author Jayin
 *
 */
class IndexController extends BaseController {

    private $admin = null;

    /**
     * 需要获得管理员用户信息
     *
     * @return \Admin\Controller\IndexController
     */
    protected function reqAdmin() {
        $this->admin = (new UserAdminModel())->createAdminById(session('uid'));
        if (! $this->admin) {
            $this->logout();
            $this->redirect('admin/index/index');
        }
        $this->admin = $this->admin->getData();
        $this->assign('admin', $this->admin);
        return $this;
    }

    public function index() {
        if ($this->isLogin() && $this->reqAdmin()) {
            $this->redirect('admin/index/manage');
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
            $this->ajaxReturn(mz_json_success(U('admin/index/manage')));
        } else {
            $this->ajaxReturn(mz_json_error($res['msg']));
        }
    }

    public function manage() {
        $this->reqAdmin();
        
        $this->display();
    }

    public function checkInstitution($page = 1) {
        $this->reqAdmin();
        // TODO MZ::改为watting
        $res = D('UserInstitution')->search(UserModel::STATUS_PASS,null,null,$page);
        $institutions = $res['msg'];
        $this->assign('institutions', $institutions);
        $this->assign('page',$page);
        $this->display();
    }

    public function checkDocument($categoryId = 1,$page = 1) {
        // TODO MZ:: 注意文档状态
        $this->reqAdmin();
        $res = D('Document')->search($categoryId, null, null, 
                DocumentModel::VERIFY_WAITING,$page);
        $documents = $res['msg'];
        $this->assign('documents', $documents);
        $this->assign('categoryId',$categoryId);
        $this->assign('page',$page);
        $this->display();
    }

    public function postDocument($doc_id = 0) {
        $this->reqAdmin();
        if($doc_id || $doc_id !== 0) {
            $res = D('Document')->getDocumentInfo($doc_id);
            if($res['status']){
                $doc = $res['msg'];
                $this->assign('document',$doc);
                foreach ($doc['files'] as $file){
                    $file_ids[$file['id']] =  $file['id'];
                }
                $this->assign('file_ids',json_encode($file_ids));
                
            }
        }
        $this->display();
    }

    public function viewDocument($doc_id = 0) {
        // TODO MZ:: 处理没有找到该文章的情况
        // TODO MZ:: 处理禁止显示的情况
        //document
        $res = D('Document')->getDocumentInfo($doc_id);
        $document = $res['msg'];
        //category
        $res = D('DocumentCategory')->getCategoryById($document['category_id']);
        $category = $res['msg'];
        //doc files
        $res = D('DocumentFile')->getDocFiles($document['id'],'application');
        $files = $res['msg'];
        for($i=0;$i<count($files);$i++){
            $files[$i]['url'] = mz_get_docfile_path($files[$i]['save_path'], $files[$i]['save_name']);
        }
        //image
        $res = D('DocumentFile')->getDocFiles($document['id'],'image');
        $images = $res['msg'];
        for($i=0;$i<count($images);$i++){
            $images[$i]['url'] = mz_get_docfile_path($images[$i]['save_path'], $images[$i]['save_name']);
        }
        $this->assign('document', $document);
        $this->assign('category', $category);
        $this->assign('files', $files);
        $this->assign('images', $images);
        $this->display();
    }

    public function viewInstitution($institutionId = 0) {
        $this->reqAdmin();
        $res = D('User')->getInsInfo($institutionId);
        $ins = $res['msg'];
        $this->assign('ins',$ins);
        $this->display();
    }
}