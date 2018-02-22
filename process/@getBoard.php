<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 게시판 정보를 불러온다.
 *
 * @file /modules/board/process/@getBoard.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0
 * @modified 2018. 2. 18.
 */
if (defined('__IM__') == false) exit;

$bid = Request('bid');
$data = $this->db()->select($this->table->board)->where('bid',$bid)->getOne();

if ($data == null) {
	$results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND');
} else {
	$permission = json_decode($data->permission);
	unset($data->permission);
	
	if ($permission != null) {
		foreach ($permission as $key=>$value) {
			$data->{'permission_'.$key} = $value;
		}
	}
	
	unset($data->templet_configs);
	
	$attachment = json_decode($data->attachment);
	unset($data->attachment);
	$data->use_attachment = $attachment->attachment;
	$data->attachment = $data->use_attachment == true ? $attachment->templet : '#';
	
	$data->allow_secret = $data->allow_secret == 'TRUE';
	$data->allow_anonymity = $data->allow_anonymity == 'TRUE';
	
	$data->use_category = $data->use_category != 'NONE';
	if ($data->use_category == true) {
		$category = $this->db()->select($this->table->category,'idx,title,post,permission,sort')->where('bid',$bid)->orderBy('sort','asc')->get();
		for ($i=0, $loop=count($category);$i<$loop;$i++) {
			$category[$i]->permission = json_decode($category[$i]->permission);
		}
		$data->category = json_encode($category,JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
	}
	$results->success = true;
	$results->data = $data;
}
?>