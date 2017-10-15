<?php
namespace Mylib;
use Think\Controller;

class CController extends Controller
{
	
	function __construct(){
		parent::__construct();
		$user_id = session('user_id');
		if(empty($user_id)){
			$this->redirect('gate/login');exit;
		}
		$roleid = session('roleid');
		if($roleid != 0){
			$authcas = getOneById('role','authcas',$roleid);
			$authcas = explode(',',$authcas);
			array_unshift($authcas,'manager-index');
			array_unshift($authcas,'admin-modipass');
			//dump($authcas);exit;
			$curca = CONTROLLER_NAME.'-'.ACTION_NAME;
			$curca = strtolower($curca);
			if(!in_array($curca,$authcas)){
				exit('没有权限!');
			}
		}
		
		
	}
	
	
	/**
	* 单表增加
	* @param array $data
	* 
	* @return
	*/
	function add($data = array()){
		if(IS_POST){
			$class_name = get_called_class();//获取调用子类的全类名（含命名空间）
			$table_name = substr(strrchr($class_name,'\\'),1,-10);//截取出表名
			$model = D($table_name);
			if(isset($model->insertFields))//设置允许添加的字段（模型中定义）
				$model->setInsertFields($model->insertFields);
			if($model->create(I('post.'),1)){
				if($model->add()){
					$this->success('添加成功',U('all'));exit;
				}
			}
			$this->error($model->getError());exit;
		}
		if($data){
			$this->assign('data',$data);
		}
		$this->display();
	}
	/**
	* 单表修改
	* @param undefined $data
	* 
	* @return
	*/
	function edit($data = array()){
		$class_name = get_called_class();//获取调用子类的全类名（含命名空间）
		$table_name = substr(strrchr($class_name,'\\'),1,-10);//截取出表名
		$model = D($table_name);
		if(IS_POST){
			//dump($_POST);exit;
			if(isset($model->updateFields))//设置允许添加的字段（模型中定义）
				$model->setUpdateFields($model->updateFields);
			if($model->create(I('post.'),2)){
				if($model->save()!==FALSE){
					$this->success('修改成功',U('all'));exit;
				}
			}
			$this->error($model->getError());exit;
		}
		if($data){
			$this->assign('data',$data);
		}else{
			$id = I($model->get_pk());
			$this->assign('data',$model->find($id));
		}
		$this->display();
	}
	
	function all(){
		$class_name = get_called_class();//获取调用子类的全类名（含命名空间）
		$table_name = substr(strrchr($class_name,'\\'),1,-10);//截取出表名
		$model = D($table_name);
		$info = $model->pageData();
		$this->assign('info',$info);
		$this->display();
	}
	
	
	/**
	* 单表删除
	* 
	* @return
	*/
	function del(){
		$class_name = get_called_class();//获取调用子类的全类名（含命名空间）
		$table_name = substr(strrchr($class_name,'\\'),1,-10);//截取出表名
		$model = D($table_name);
		$id = I($model->get_pk());
		if($model->delete($id)!== FALSE){
			$this->success('删除成功',U('all'));exit;
		}
		//dump($model);exit();
		//exit($model->pk);
		$this->error('删除失败，原因是'.$model->getError());exit;
	}
	
	
	/**
	* layui异步上传通用文件接口，可接受类型:iamge,doc,video,audio等，每种类型的文件存于上目录对应的文件夹下
	* @return array ['info'=>TRUE|FALSE,'uri'=>'image/20150223/xxxx.jpg','dir_prefix'=>uload文件夹地址,'type'=>前端传过来的类型]
	*/
	public function upload(){
		if(IS_AJAX){
			//layui前端默认以$_FILES['file']提交
			if($_FILES['file']['error'] === 0 && $_FILES['file']['size'] > 0){
				$type = $_POST['type'];//前端配置传过来
				$load = new \Mylib\Upload($type);
				$uri = $load->uploadFile($_FILES['file']);//成功返货文件的末尾地址（即不带upload部分的文件名），失败返回false
				$data = array();
				if($uri){
					$data['info'] = TRUE;//成功标记
					$data['uri'] = $uri;//文件名
					$data['dir_prefix'] = UP.'/';//目录前缀应由服务器返回
					$data['type'] = $type;//返回类型
				}else{
					$data['info'] = $load->showError();//失败返回失败原因
				}
				$this->ajaxReturn($data);
			}
		}
	}
	
	
}
