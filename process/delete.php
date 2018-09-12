<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 게시물 / 댓글을 삭제한다.
 *
 * @file /modules/board/process/delete.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 9. 9.
 */
if (defined('__IM__') == false) exit;

$type = Param('type');
$idx = Param('idx');

if ($type == 'post') {
	$post = $this->getPost($idx);
	
	if ($post == null) {
		$results->success = false;
		$results->message = $this->getErrorText('NOT_FOUND');
	} elseif ($this->checkPermission($post->bid,'post_delete') == true) {
		$this->deletePost($idx);
		$results->success = true;
	} elseif ($post->midx != 0 && $post->midx != $this->IM->getModule('member')->getLogged()) {
		$this->deletePost($idx);
		$results->success = false;
	} elseif ($post->midx == 0) {
		$password = Request('password');
		
		$mHash = new Hash();
		if ($mHash->password_validate($password,$post->password) == true) {
			$this->deletePost($idx);
			$results->success = true;
		} else {
			$results->success = false;
			$results->errors = array('password'=>$this->getErrorText('INCORRENT_PASSWORD'));
		}
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
	} elseif ($this->checkPermission($ment->bid,'ment_delete') == true) {
		$this->deleteMent($idx);
		$results->success = true;
		$results->parent = $ment->parent;
	} elseif ($ment->midx != 0 && $ment->midx != $this->IM->getModule('member')->getLogged()) {
		$this->deleteMent($idx);
		$results->success = false;
		$results->parent = $ment->parent;
	} elseif ($ment->midx == 0) {
		$password = Request('password');
		
		$mHash = new Hash();
		if ($mHash->password_validate($password,$ment->password) == true) {
			$this->deleteMent($idx);
			$results->success = true;
			$results->parent = $ment->parent;
		} else {
			$results->success = false;
			$results->errors = array('password'=>$this->getErrorText('INCORRENT_PASSWORD'));
		}
	} else {
		$results->success = false;
		$results->message = $this->getErrorText('FORBIDDEN');
	}
}
?>