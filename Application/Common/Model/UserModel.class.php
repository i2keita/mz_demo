<?php
namespace Common\Model;
use Common\Model\BaseModel;

/**
 * 所有用户公共模型
 * @author Jayin
 *        
 */
class UserModel extends BaseModel {

    /** 个人用户 */
    const LEVEL_PERSON = 1;

    /**  用人单位(企业)*/
    const LEVEL_EMPLOYER = 2;

    /** 培训机构用户 */
    const LEVEL_INSTITUTION = 4;

    /**  管理员 */
    const  LEVEL_ADMIN= 8;
    
    /**  超级管理员  */
    const LEVEL_SUPER_ADMIN = 16;

    /**  审核不通过 */
    const STATUS_UNPASS = - 1;

    /** 未审核 */
    const STATUS_NOT_VERTIFY = 0;
    
    /** 冻结状态*/
    const STATUS_STOP = -2;

    /**  审核通过 */
    const STATUS_PASS = 1;

    protected $_validate = array(
            // array(验证字段1,验证规则,错误提示,[验证条件,附加规则,验证时间]),
            //默认,验证条件：0存在字段就进行验证 , 验证时间：1新增/编辑 数据时候验证
            
            //插入时必须有:
            array('nickname','require','缺少昵称',self::MUST_VALIDATE,'',self::MODEL_INSERT),
            array('psw','require','缺少密码',self::MUST_VALIDATE,'',self::MODEL_INSERT),
            //插入时唯一性验证
            array('phone','','电话号码已被注册',self::EXISTS_VALIDATE,'unique',self::MODEL_INSERT),
            array('email','','邮箱已注册',self::EXISTS_VALIDATE,'unique',self::MODEL_INSERT),
            //任何时刻,字段存在就进行格式检查
            array('phone','number','手机号码必须为数字'),
            array('phone','11','电话号码长度为11',self::EXISTS_VALIDATE,'length'),
            array('email','email','邮箱格式错误'),
            array('psw','8,16','密码长度8-16,数字+字符',self::EXISTS_VALIDATE,'length')
            
    );
    
    protected $_auto = array(
    	//完成字段1,完成规则,[完成条件,附加规则]
    	array('reg_time',NOW_TIME,self::MODEL_INSERT),
        array('level','1',self::MODEL_INSERT),
        array('status','1',self::MODEL_INSERT)
    );
    
    /**
     * 根据id创建一个机构用户
     * @param unknown $uid
     * @return \Common\Model\UserModel
     */
    public function createInstution($uid){
        $user_person = $this->field(array('psw'),true)->where("uid=%d",$uid)->select();
        $user_person_info = M('UserInstitution')->where("uid=%d",$uid)->select();
        if($user_person && $user_person_info){
            $this->data(array_merge($user_person[0],$user_person_info[0]));
        }
        return $this;
    }
    
    /**
     * 注册一个普通个人用户
     * @return array() status:1为正常 0为失败 msg:失败信息
     */
    public function regPerson(){
        $res = $this->_getResult();
        if(!$this->create()){
            $res['msg'] = $this->getError();
            return $res;
        }
        $this->data['level']=UserModel::LEVEL_PERSON;
        $this->data['status']=UserModel::STATUS_PASS;
        $this->data['psw'] = md5($this->data['psw']);
        $uid = $this->add();
        if($uid){
           $_data = array(
           	    'uid' => $uid
           );
           //创建user_person资料
           $res_person = D('UserPerson')->addPerson(array_merge($_data,I('post.')));
           $res = array_merge($res,$res_person);
        }else{
            $res['msg'] = 'System Error: Not able to insert.';
        }
        return $res;
    }
    /**
     * 注册用人单位(企业)用户
     */
    public function regEmployer(){
        $res = $this->_getResult();
        if(!$this->create()){
            $res['msg'] = $this->getError();
            return $res;
        }
        if(empty($this->data['phone']) && empty($this->data['email'])){
            $res['msg'] = 'Reigister operation requires `phone` or email';
            return $res;
        }
        $this->data['level']=UserModel::LEVEL_EMPLOYER;
        $this->data['status']=UserModel::STATUS_PASS;
        $this->data['psw'] = md5($this->data['psw']);
        $uid = $this->add();
        if($uid){
            $_data = array(
                    'uid' => $uid
            );
            //创建user_person资料
            $res_person = D('UserEmployer')->addEmployer(array_merge($_data,I('post.')));
            $res = array_merge($res,$res_person);
        }else{
            $res['msg'] = 'System Error: Not able to insert.';
        }
        return $res;
    }
    
    private function regAdmin(){
            //see UserAdminModel.createAdmin
    }

    /**
     * 注册一个机构用户
     * 
     * @return string Ambigous multitype:number string >
     */
    public function regInstitution() {
        $res = $this->_getResult();
        if (! $this->create()) {
            $res['msg'] = $this->getError();
            return $res;
        }
        if(empty($this->data['phone']) && empty($this->data['email'])){
            $res['msg'] = 'Reigister operation requires `phone` or email';
            return $res;
        }
        $this->data['level'] = UserModel::LEVEL_INSTITUTION;
        $this->data['status'] = UserModel::STATUS_NOT_VERTIFY;
        $this->data['psw'] = md5($this->data['psw']);
        $uid = $this->add();
        if ($uid) {
            $_data = array(
                    'uid' => $uid
            );
            // 创建user_institution资料
            $user_institution = D('UserInstitution')->addInstitution(
                    array_merge($_data, I('post.')));
            $res = array_merge($res, $user_institution);
        } else {
            $res['msg'] = 'System Error: Not able to insert.';
        }
        return $res;
    }

    /**
     * 登录
     * @param unknown $account_type 账号(邮箱/手机号码)
     * @param unknown $account
     * @param unknown $psw
     */
    public function login($account_type,$account,$psw){
       $res = $this->_getResult();
       if($account_type === 'phone' ){
           $map['phone'] =$account;
       }else{
           $map['email'] =$account;
       }
       $u = $this->where($map)->limit(1)->select();
       if($u){
       	 	if($psw == $u[0]['psw']){
       	 		$res['status'] = 1;
       	 		unset($u[0]['psw']);
       	 		$res['msg']  = $u[0];
       	 	}else{
       	 		$res['msg'] = '密码不正确';
       	 	}
       }else{
           $res['msg']='账号尚未注册';
       }
       return $res;
    }
    /**
     * 获得一个用户的信息
     * @param unknown $uid 用户id
     */
    public function getUserInfo($uid){
        $res = $this->_getResult();
        $user_person= $this->field('nickname,phone,email,reg_time,level,status')->where("uid='%s' AND level=%d",$uid,UserModel::LEVEL_PERSON)->select();
        if($user_person){
            $_result = M('UserPerson')->field('sex,work_place')->where("uid='%s'",$uid)->select();
            $res['status'] = 1;
            $res['msg'] = array_merge($user_person[0],$_result[0]);
        }else{
              $res['msg'] = 'user info not found';
        }
        return $res;
    }
    /**
     * 根据id获得机构信息
     * @param unknown $uid
     * @return Ambigous <string, multitype:, multitype:number string >
     */
    public function getInsInfo($uid){
        $res = $this->_getResult();
        $user_ins = M('User')->field('nickname,phone,email,reg_time,level,status')->where("uid='%s' AND level=%d",$uid,UserModel::LEVEL_INSTITUTION)->select();
        $user_ins_files = D('DocumentFile')->getDocFilesByIns($uid)['msg'];
        if($user_ins){
            $_result = M('UserInstitution')->where("uid='%s'",$uid)->select();
            $res['status'] = 1;
            $res['msg'] = array_merge($user_ins[0],$_result[0]);
            if($user_ins_files){
                $res['msg'] = array_merge($res['msg'],array('files'=>$user_ins_files));
            }else{
                $res['msg'] = array_merge($res['msg'],array('files'=>array()));
            }
        }else{
            $res['msg'] = 'user info not found';
        }
        return $res;
    }
    
    /**
     * 根据id获得用人单位(企业)信息
     * @param unknown $uid
     * @return Ambigous <string, multitype:, multitype:number string >
     */
    public function getEmployerInfo($uid){
        $res = $this->_getResult();
        $user_employers = M('User')->field('nickname,phone,email,reg_time,level,status')->where("uid='%s' AND level=%d",$uid,UserModel::LEVEL_EMPLOYER)->select();
        if($user_employers){
            $_result = M('UserEmployer')->where("uid='%s'",$uid)->select();
            $res['status'] = 1;
            $res['msg'] = array_merge($user_employers[0],$_result[0]);
        }else{
            $res['msg'] = 'No session info,login please';
        }
        return $res;
    }

    /**
     * 更新用户信息
     * 
     * @param unknown $uid            
     * @return Ambigous <number, multitype:number string , string>
     */
    public function updateUserInfo($data) {
        $res = $this->_getResult();
        if ($this->create($data)) {
            // 除了uid还有更新其他项,那么更新，不然会报SQL错误
            if (count($this->data()) > 1) {
                $this->save();
            }
            $res_person = D('UserPerson')->updateInfo($data);
            $res = array_merge($res,$res_person);
        } else {
            $res['msg'] = $this->getError();
        }
        // $res['msg']为'' 说明操作成功
        if (! $res['msg']) {
            $res['status'] = 1;
        }
        return $res;
    }
    /**
     * 更新机构信息
     * @param unknown $data
     * @return Ambigous <number, multitype:number string >
     */
    public function updateInsInfo($data){
        $res = $this->_getResult();  
        if($this->create($data)){
            if(count($this->data) > 1){
                $this->save();
            }
            $res_ins = D('UserInstitution')->updateInfo($data);
            $res = array_merge($res,$res_ins);
        }else{
            $res['msg'] = $this->getError();
        }
        // $res['msg']为'' 说明操作成功
        if (! $res['msg']) {
            $res['status'] = 1;
        }
        return $res;
    }
    /**
     * 更新用人单位(企业)信息
     * @param unknown $data
     * @return number
     */
    public function updateEmployerInfo($data){
        $res = $this->_getResult();  
        if($this->create($data)){
            if(count($this->data) > 1){
                $this->save();
            }
            $res_employer = D('UserEmployer')->updateInfo($data);
            $res = array_merge($res,$res_employer);
        }else{
            $res['msg'] = $this->getError();
        }
        // $res['msg']为'' 说明操作成功
        if (! $res['msg']) {
            $res['status'] = 1;
        }
        return $res;
    }
    /**
     * 删除一用户
     * @param unknown $uid
     * @param unknown $type 类型（admin person，institution，employer）
     * @return Ambigous <number, string>
     */
    public function deleteUser($uid,$type){
        $res = $this->_getResult();
        switch ($type){
        	case UserModel::LEVEL_ADMIN:
        	    if($this->where("uid='%s'",$uid)->delete() && M('UserAdmin')->where("uid='%s'",$uid)->delete()){
        	        $res['status'] = 1;
        	    }else{
        	        $res['msg'] = '删除失败';
        	    }
        	    break;
        	case UserModel::LEVEL_PERSON:
        	    if($this->where("uid='%s'",$uid)->delete() && M('UserPerson')->where("uid='%s'",$uid)->delete()){
        	        $res['status'] = 1;
        	    }
        	    else
        	    {
        	        $res['msg'] = '删除失败';
        	    }
        	    break;
            case UserModel::LEVEL_INSTITUTION:
                if($this->where("uid='%s'",$uid)->delete() && M('UserInstitution')->where("uid='%s'",$uid)->delete()){
                   M('DocumentFile')->where("ins_id='%s'",$uid)->delete();
                   M('Course')->where("institution_id='%s'",$uid)->delete();
                   $res['status'] = 1;
                }else{
                    $res['msg'] = '删除失败';
                }
                break;
            case UserModel::LEVEL_EMPLOYER:
                if($this->where("uid='%s'",$uid)->delete() && M('UserEmployer')->where("uid='%s'",$uid)->delete()){
                    $res['status'] = 1;
                }else{
                    $res['msg'] = '删除失败';
                }
                break;
        }
        return $res;
    }
}

