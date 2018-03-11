<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 모달창을 가져온다.
 *
 * @file /modules/board/process/getModal.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 2. 27.
 */
if (defined('__IM__') == false) exit;

$modal = Request('modal');

if ($modal == 'delete') {
	$type = Request('type');
	$idx = Request('idx');
	
	if ($type == 'post') {
		$post = $this->getPost($idx);
		
		if ($post == null) {
			$results->success = false;
			$results->message = $this->getErrorText('NOT_FOUND');
		} elseif ($this->checkPermission($post->bid,'post_delete') == true || $post->midx == $this->IM->getModule('member')->getLogged()) {
			$results->success = true;
			$results->modalHtml = $this->getDeleteModal('post',$idx);
		} else {
			$results->success = false;
			$results->message = $this->getErrorText('FORBIDDEN');
		}
	}
	
	if ($type == 'ment') {
		$ment = $this->getMent($idx);
		
		if ($ment == null) {
			$results->success = false;
			$results->message = $this->getErrorText('NOT_FOUND');
		} elseif ($this->checkPermission($ment->bid,'ment_delete') == true || $ment->midx == $this->IM->getModule('member')->getLogged()) {
			$results->success = true;
			$results->modalHtml = $this->getDeleteModal('ment',$idx);
		} else {
			$results->success = false;
			$results->message = $this->getErrorText('FORBIDDEN');
		}
	}
}
?>