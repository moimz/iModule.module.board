<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 게시물 또는 댓글을 추천한다.
 *
 * @file /modules/board/process/vote.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 9. 9.
 */
if (defined('__IM__') == false) exit;

$type = Param('type');
$idx = Param('idx');
$vote = Param('vote');

if ($this->IM->getModule('member')->isLogged() == false) {
	$results->success = false;
	$results->error = $this->getErrorText('REQUIRED_LOGIN');
	return;
}

if ($this->db()->select($this->table->activity)->where('type',$type)->where('parent',$idx)->where('code',$vote == 'GOOD' ? 'BAD' : 'GOOD')->has() == true) {
	$results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND');
	return;
}

if ($type == 'post') {
	$post = $this->getPost($idx);
	if ($post == null) {
		$results->success = false;
		$results->message = $this->getErrorText('NOT_FOUND');
		return;
	}
	
	if ($post->midx == $this->IM->getModule('member')->getLogged()) {
		$results->success = false;
		$results->message = $this->getErrorText('NOT_FOUND');
		return;
	}
	
	if ($this->db()->select($this->table->activity)->where('type',$type)->where('parent',$idx)->where('code',$vote)->has() == true) {
		$results->success = false;
		$results->message = $this->getErrorText('NOT_FOUND');
		return;
	}
} else {
	$ment = $this->getMent($idx);
	if ($ment == null) {
		$results->success = false;
		$results->message = $this->getErrorText('NOT_FOUND');
		return;
	}
	
	if ($ment->midx == $this->IM->getModule('member')->getLogged()) {
		$results->success = false;
		$results->message = $this->getErrorText('NOT_FOUND');
		return;
	}
	
	
}
?>