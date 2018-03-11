<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 댓글을 가져온다.
 *
 * @file /modules/board/process/getMent.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 2. 25.
 */
if (defined('__IM__') == false) exit;

$type = Request('type');
$idx = Request('idx');
$ment = $this->db()->select($this->table->ment_depth.' d','d.*,m.*')->join($this->table->ment.' m','d.idx=m.idx','LEFT')->where('m.idx',$idx)->getOne();

if ($ment == null) {
	$results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND');
	return;
}

if ($type == 'view') {
	$board = $this->getBoard($ment->bid);
	$configs = json_decode(Request('configs'));
	
	if ($this->checkPermission($ment->bid,'view') == false) {
		$results->success = false;
		$results->message = $this->getErrorText('FORBIDDEN');
		return;
	}
	
	$item = $this->getMentItemComponent($ment,$configs);

	if ($ment->is_secret == 'TRUE' && $this->checkSecretMentPermission($ment->idx) !== true) {
		$results->success = false;
		$results->message = $this->getErrorText('FORBIDDEN');
		return;
	}
	
	$results->success = true;
	$results->item = $item;
	$results->data = $ment;
}

if ($type == 'modify') {
	$ment->content = $this->IM->getModule('wysiwyg')->decodeContent($ment->content,false);
	$ment->is_secret = $ment->is_secret == 'TRUE';
	$ment->is_anonymity = $ment->is_anonymity == 'TRUE';
	$ment->files = $this->IM->getProcessUrl('board','getFiles',array('idx'=>Encoder(json_encode(array('type'=>'MENT','idx'=>$ment->idx)))));
	
	if ($this->checkPermission($ment->bid,'ment_modify') == true) {
		$results->success = true;
		$results->data = $ment;
	} elseif ($ment->midx == 0) {
		$password = Request('password');
		
		if ($password) {
			$mHash = new Hash();
			if ($mHash->password_validate($password,$ment->password) == true) {
				$results->success = true;
				$results->data = $ment;
			} else {
				$results->success = false;
				$results->errors = array('password'=>$this->getErrorText('INCORRENT_PASSWORD'));
			}
		} else {
			$results->success = true;
			$results->modalHtml = $this->getPasswordModal('ment_modify',$idx);
		}
	} elseif ($ment->midx == $this->IM->getModule('member')->getLogged()) {
		$results->success = true;
		$results->data = $ment;
	} else {
		$results->success = false;
		$results->message = $this->getErrorText('FORBIDDEN');
	}
}
?>