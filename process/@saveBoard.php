<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 게시판정보를 저장한다.
 *
 * @file /modules/board/process/@saveBoard.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 9. 9.
 */
if (defined('__IM__') == false) exit;

$mode = Request('mode');
$errors = array();
$insert = array();
$insert['title'] = Request('title') ? Request('title') : $errors['title'] = $this->getErrorText('REQUIRED');
$insert['templet'] = Request('templet') ? Request('templet') : $errors['templet'] = $this->getErrorText('REQUIRED');
$insert['post_limit'] = Request('post_limit') && is_numeric(Request('post_limit')) == true ? Request('post_limit') : $errors['post_limit'] = $this->getErrorText('REQUIRED');
$insert['ment_limit'] = Request('ment_limit') && is_numeric(Request('ment_limit')) == true ? Request('ment_limit') : $errors['ment_limit'] = $this->getErrorText('REQUIRED');
$insert['page_limit'] = Request('page_limit') && is_numeric(Request('page_limit')) == true ? Request('page_limit') : $errors['page_limit'] = $this->getErrorText('REQUIRED');
$insert['page_type'] = Request('page_type') && in_array(Request('page_type'),array('FIXED','CENTER')) == true ? Request('page_type') : $errors['page_type'] = $this->getErrorText('REQUIRED');

$insert['allow_secret'] = Request('allow_secret') ? 'TRUE' : 'FALSE';
$insert['allow_anonymity'] = Request('allow_anonymity') ? 'TRUE' : 'FALSE';
$insert['allow_voting'] = Request('allow_voting') ? 'TRUE' : 'FALSE';

$insert['view_notice_page'] = Request('view_notice_page') && in_array(Request('view_notice_page'),array('FIRST','ALL')) == true ? Request('view_notice_page') : $errors['view_notice_page'] = $this->getErrorText('REQUIRED');
$insert['view_notice_count'] = Request('view_notice_count') && in_array(Request('view_notice_count'),array('INCLUDE','EXCLUDE')) == true ? Request('view_notice_count') : $errors['view_notice_count'] = $this->getErrorText('REQUIRED');

$use_category = Request('use_category') == 'on';
if ($use_category == true) {
	$insert['use_category'] = 'USED';
	$category = json_decode(Request('category'));
} else {
	$insert['use_category'] = 'NONE';
	$category = array();
}

$insert['post_point'] = Request('post_point') && is_numeric(Request('post_point')) == true ? Request('post_point') : 0;
$insert['post_exp'] = Request('post_exp') && is_numeric(Request('post_exp')) == true ? Request('post_exp') : 0;
$insert['ment_point'] = Request('ment_point') && is_numeric(Request('ment_point')) == true ? Request('ment_point') : 0;
$insert['ment_exp'] = Request('ment_exp') && is_numeric(Request('ment_exp')) == true ? Request('ment_exp') : 0
$insert['vote_point'] = Request('vote_point') && is_numeric(Request('vote_point')) == true ? Request('vote_point') : 0;
$insert['vote_exp'] = Request('vote_exp') && is_numeric(Request('vote_exp')) == true ? Request('vote_exp') : 0;
$insert['voted_point'] = Request('voted_point') && is_numeric(Request('voted_point')) == true ? Request('voted_point') : 0;
$insert['voted_exp'] = Request('voted_exp') && is_numeric(Request('voted_exp')) == true ? Request('voted_exp') : 0;

$attachment = new stdClass();
$attachment->attachment = Request('use_attachment') ? true : false;
if ($attachment->attachment == true) {
	$attachment->templet = Request('attachment') ? Request('attachment') : $errors['attachment'] = $this->getErrorText('REQUIRED');
	$attachment->templet_configs = new stdClass();
}

$templetConfigs = new stdClass();
$permission = new stdClass();
foreach ($_POST as $key=>$value) {
	if (preg_match('/^permission_/',$key) == true && preg_match('/_selector$/',$key) == false) {
		if ($this->IM->checkPermissionString($value) !== true) {
			$errors[$key] = $this->IM->checkPermissionString($value);
		} else {
			$permission->{str_replace('permission_','',$key)} = $value;
		}
	}
	
	if (preg_match('/^templet_configs_/',$key) == true) {
		$templetConfigs->{str_replace('templet_configs_','',$key)} = $value;
	}
	
	if (preg_match('/^attachment_configs_/',$key) == true) {
		$attachment->templet_configs->{str_replace('attachment_configs_','',$key)} = $value;
	}
}

$insert['templet_configs'] = json_encode($templetConfigs,JSON_UNESCAPED_UNICODE);
$insert['permission'] = json_encode($permission,JSON_UNESCAPED_UNICODE);
$insert['attachment'] = json_encode($attachment,JSON_UNESCAPED_UNICODE);

if ($mode == 'add') {
	$bid = Request('bid');
	if ($this->db()->select($this->table->board)->where('bid',$bid)->has() == true) $errors['bid'] = $this->getErrorText('DUPLICATED');
	else $insert['bid'] = $bid;
}

if (count($errors) == 0) {
	if ($mode == 'add') {
		$this->db()->insert($this->table->board,$insert)->execute();
	} else {
		$bid = Request('bid');
		$this->db()->update($this->table->board,$insert)->where('bid',$bid)->execute();
	}
	
	if ($use_category == true) {
		$categories = array();
		for ($i=0, $loop=count($category);$i<$loop;$i++) {
			if ($category[$i]->idx == 0) {
				$categories[] = $this->db()->insert($this->table->category,array('bid'=>$bid,'title'=>$category[$i]->title,'permission'=>json_encode($category[$i]->permission,JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK),'sort'=>$category[$i]->sort))->execute();
			} else {
				$categories[] = $category[$i]->idx;
				$this->db()->update($this->table->category,array('title'=>$category[$i]->title,'permission'=>json_encode($category[$i]->permission,JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK),'sort'=>$category[$i]->sort))->where('idx',$category[$i]->idx)->execute();
			}
		}
		
		$this->db()->delete($this->table->category)->where('bid',$bid)->where('idx',$categories,'NOT IN')->execute();
		$this->db()->update($this->table->post,array('category'=>0))->where('bid',$bid)->where('category',$categories,'NOT IN')->execute();
	} else {
		$this->db()->delete($this->table->category)->where('bid',$bid)->execute();
		$this->db()->update($this->table->post,array('category'=>0))->where('bid',$bid)->execute();
	}
	
	
	$results->success = true;
} else {
	$results->success = false;
	$results->errors = $errors;
}
?>