<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 게시판정보를 저장한다.
 *
 * @file /modules/board/process/@saveBoard.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160923
 *
 * @return object $results
 */
if (defined('__IM__') == false) exit;

$mode = Request('mode');
$errors = array();
$insert = array();
$insert['title'] = Request('title') ? Request('title') : $errors['title'] = $this->getErrorText('REQUIRED');
$insert['templet'] = Request('templet') ? Request('templet') : $errors['templet'] = $this->getErrorText('REQUIRED');
$insert['postlimit'] = Request('postlimit') && is_numeric(Request('postlimit')) == true ? Request('postlimit') : $errors['postlimit'] = $this->getErrorText('REQUIRED');
$insert['mentlimit'] = Request('mentlimit') && is_numeric(Request('mentlimit')) == true ? Request('mentlimit') : $errors['mentlimit'] = $this->getErrorText('REQUIRED');
$insert['pagelimit'] = Request('pagelimit') && is_numeric(Request('pagelimit')) == true ? Request('pagelimit') : $errors['pagelimit'] = $this->getErrorText('REQUIRED');
$insert['pagetype'] = Request('pagetype') && in_array(Request('pagetype'),array('FIXED','CENTER')) == true ? Request('pagetype') : $errors['pagetype'] = $this->getErrorText('REQUIRED');
$insert['view_notice_page'] = Request('view_notice_page') && in_array(Request('view_notice_page'),array('FIRST','ALL')) == true ? Request('view_notice_page') : $errors['view_notice_page'] = $this->getErrorText('REQUIRED');
$insert['view_notice_count'] = Request('view_notice_count') && in_array(Request('view_notice_count'),array('INCLUDE','EXCLUDE')) == true ? Request('view_notice_count') : $errors['view_notice_count'] = $this->getErrorText('REQUIRED');

if (Request('use_category') == 'on') {
	// @todo
} else {
	$insert['use_category'] = 'NONE';
}

$insert['post_point'] = Request('post_point') && is_numeric(Request('post_point')) == true ? Request('post_point') : $errors['post_point'] = $this->getErrorText('REQUIRED');
$insert['post_exp'] = Request('post_exp') && is_numeric(Request('post_exp')) == true ? Request('post_exp') : $errors['post_exp'] = $this->getErrorText('REQUIRED');
$insert['ment_point'] = Request('ment_point') && is_numeric(Request('ment_point')) == true ? Request('ment_point') : $errors['ment_point'] = $this->getErrorText('REQUIRED');
$insert['ment_exp'] = Request('ment_exp') && is_numeric(Request('ment_exp')) == true ? Request('ment_exp') : $errors['ment_exp'] = $this->getErrorText('REQUIRED');
$insert['vote_point'] = Request('vote_point') && is_numeric(Request('vote_point')) == true ? Request('vote_point') : $errors['vote_point'] = $this->getErrorText('REQUIRED');
$insert['vote_exp'] = Request('vote_exp') && is_numeric(Request('vote_exp')) == true ? Request('vote_exp') : $errors['vote_exp'] = $this->getErrorText('REQUIRED');

$permission = array();
foreach ($_POST as $key=>$value) {
	if (preg_match('/^permission_/',$key) == true && preg_match('/_selector$/',$key) == false) {
		if ($this->IM->checkPermissionString($value) !== true) {
			$errors[$key] = $this->IM->checkPermissionString($value);
		} else {
			$permission[str_replace('permission_','',$key)] = $value;
		}
	}
}

$insert['permission'] = json_encode($permission,JSON_UNESCAPED_UNICODE);

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
	
	$results->success = true;
} else {
	$results->success = false;
	$results->errors = $errors;
}
?>