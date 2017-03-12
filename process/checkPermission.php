<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 권한을 확인한다.
 *
 * @file /modules/board/process/checkPermission.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160923
 */
if (defined('__IM__') == false) exit;

$type = Request('type');

if ($type == 'post_modify') {
	$idx = Request('idx');
	$post = $this->getPost($idx);
	
	if ($this->checkPermission($post->bid,'post_modify') == true) {
		$results->success = true;
	} elseif ($post->midx == 0) {
		
	} elseif ($post->midx == $this->IM->getModule('member')->getLogged()) {
		$results->success = true;
	} else {
		$results->success = false;
		$results->message = $this->getErrorText('FORBIDDEN');
	}
}
?>