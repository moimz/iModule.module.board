<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 댓글을 가져온다.
 *
 * @file /modules/board/process/getMents.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160923
 */
if (defined('__IM__') == false) exit;

$parent = Request('parent');
$page = Request('page');

$post = $this->getPost($parent);
if ($post == null) {
	$results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND');
	return;
}

$board = $this->getBoard($post->bid);
$configs = new stdClass();
$configs->templet = $board->templet;
$configs->templet_configs = $board->templet_configs;

$results->success = true;
$results->parent = $parent;

$results->lists = $this->getMentListComponent($parent,$page,$configs);
$results->total = $post->ment;
$results->pagination = $this->getMentPagination($parent,$page,$configs);
?>