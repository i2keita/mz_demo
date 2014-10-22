<?php
namespace Admin\Controller;
use Common\Controller\BaseController;
use Common\Model\UserAdminModel;
use Common\Model\AdvertisementModel;
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
    /**
     * 登录页
     * @see \Common\Controller\BaseController::index()
     */
    public function index() {
        if ($this->isLogin() && $this->reqAdmin()) {
            $this->redirect('admin/index/manage');
        }
        $this->display();
    }

    /**
     * 管理员管理页
     */
    public function manage() {
        $this->reqAdmin();
        
        $this->display();
    }
    /**
     * 审核机构页面
     * @param number $checked -1审核未通过 0待审核 1已审核
     * @param number $page 页码
     */
    public function checkInstitution($checked = 0,$page = 1) {
        $this->reqAdmin();
        
        //默认为等待审核
        if($checked != 0 && $checked != 1 && $checked != -1){
            $checked = 0;
        }
        $res = D('UserInstitution')->search($checked,null,null,$page);
        $institutions = $res['msg'];
        $this->assign('institutions', $institutions);
        $this->assign('checked',$checked);
        $this->assign('page',$page);
        $this->display();
    }
    /**
     * 审核文档页面
     * @param number $checked -1审核未通过 0待审核 1已审核
     * @param number $categoryId 文档目录id
     * @param number $page 页码
     */
    public function checkDocument($checked = 0, $categoryId = 1, $page = 1) {
        // TODO MZ:: 注意文档状态
        $this->reqAdmin();
        // 默认为等待审核
        if ($checked != 0 && $checked != 1 && $checked != - 1) {
            $checked = 0;
        }
        $categoryId = (int)$categoryId;
        if($categoryId <1 || $categoryId > 7 ){
            $categoryId = 1;
        }
        $res = D('Document')->search($categoryId, null, null, 
                $checked,null, $page);
        $documents = $res['msg'];
        for($i=0;$i<count($documents);$i++){
            $authors = D('UserAdmin')->createAdminById($documents[$i]['uid'])->getData();
            $vertifyers =null;
            if(!empty($documents[$i]['vertify_uid'])){
                D('UserAdmin')->createAdminById($documents[$i]['vertify_uid'])->getData();
            }
            if($authors){
                $documents[$i]['author'] = $authors;
                
            }else{
                $documents[$i]['author'] = null;
            }
            if($vertifyers){
                $documents[$i]['vertifyer'] = $vertifyers;
            }else{
                $documents[$i]['vertifyer'] = null;
            }
        }
        
        $this->assign('documents', $documents);
        $this->assign('checked',$checked);
        $this->assign('categoryId', $categoryId);
        $this->assign('page', $page);
        $this->display();
    }
    /**
     * 发布/编辑 文档
     * @param number $doc_id 文档id
     */
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
    /**
     * 查看文档详情页
     * @param number $doc_id 文档id
     */
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
    /**
     * 查看机构页
     * @param number $institutionId 机构id
     */
    public function viewInstitution($institutionId = 0) {
        $this->reqAdmin();
        $res = D('User')->getInsInfo($institutionId);
        $ins = $res['msg'];
        $this->assign('ins',$ins);
        $this->display();
    }
    /**
     * 广告管理页
     */
    public function advertisements(){
        $this->reqAdmin();
        
        $ad_model = new AdvertisementModel();
        $ad_display = $ad_model->search(AdvertisementModel::STATUS_DISPLAY);
        $ad_undisplay = $ad_model->search(AdvertisementModel::STATUS_UNDISPLAY);
        
        $this->assign('ad_display',$ad_display['msg']);
        $this->assign('ad_undisplay',$ad_undisplay['msg']);
        $this->display();
    }
}