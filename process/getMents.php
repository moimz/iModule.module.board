<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 댓글을 가져온다.
 *
 * @file /modules/board/process/getMents.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 9. 9.
 */
if (defined('__IM__') == false) exit;

$parent = Param('parent');
$page = Param('page');

$post = $this->getPost($parent);
if ($post == null) {
	$results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND');
	return;
}

if ($this->checkPermission($post->bid,'view') == false) {
	$results->success = false;
	$results->message = $this->getErrorText('FORBIDDEN');
	return;
}

$results->success = true;
$results->parent = $parent;

$configs = json_decode(Request('configs'));
$results->lists = $this->getMentListComponent($parent,$page,$configs);
$results->total = $post->ment;
$results->pagination = $this->getMentPagination($parent,$page,$configs);
?>