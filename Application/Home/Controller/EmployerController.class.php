<?php
namespace Home\Controller;
use Common\Controller\BaseController;

/**
 *用人单位(企业)用户接口
 * @author Jayin
 *        
 */
class EmployerController extends BaseController {
    /**
     * POST 注册
     */
    public function register() {
        $this->reqPost();
        
        $res = D('User')->regEmployer();
        if ($res['status']) {
            $this->ajaxReturn(mz_json_success('register successfully'));
        } else {
            $this->ajaxReturn(mz_json_error($res['msg']));
        }
    }

    /**
     * 更新用人单位信息
     */
    public function update() {
        $this->reqPost()->reqEmployer();
        
        $uid = session('uid');
        $data['uid'] = session('uid');
        $res = D('User')->updateEmployerInfo(array_merge($data, I('post.')));
        if ($res['status']) {
            $this->ajaxReturn(mz_json_success('update info success'));
        } else {
            $this->ajaxReturn(mz_json_error($res['msg']));
        }
    }

    /**
     * GET 获取当前用人单位信息
     */
    public function info() {
        $this->reqLogin()->reqEmployer();
        
        $data['uid'] = session('uid');
        $res = D('User')->getEmployerInfo(array_merge($data, I('post.')));
        if ($res['status']) {
            $this->ajaxReturn(mz_json_success($res['msg']));
        } else {
            $this->ajaxReturn(mz_json_error($res['msg']));
        }
    }
}

 