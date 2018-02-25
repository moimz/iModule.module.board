<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 권한을 확인한다.
 *
 * @file /modules/board/process/checkPermission.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 2. 24.
 */
if (defined('__IM__') == false) exit;

$type = Request('type');

if (strpos($type,'post_') === 0) {
	$idx = Request('idx');
	$post = $this->getPost($idx);
	
	if ($post == null) {
		$results->success = false;
		$results->message = $this->getErrorText('NOT_FOUND');
		return;
	}
	
	if ($this->checkPermission($post->bid,$type) == true) {
		$results->success = true;
	} elseif ($post->midx == 0) {
		$password = Request('password');
		
		if ($password) {
			$mHash = new Hash();
			if ($mHash->password_validate($password,$post->password) == true) {
				$results->success = true;
				$results->idx = $idx;
			} else {
				$results->success = false;
				$results->errors = array('password'=>$this->getErrorText('INCORRENT_PASSWORD'));
			}
		} else {
			$results->success = true;
			$results->modalHtml = $this->getPasswordModal($type,$idx);
		}
	} elseif ($post->midx == $this->IM->getModule('member')->getLogged()) {
		$results->success = true;
	} else {
		$results->success = false;
		$results->message = $this->getErrorText('FORBIDDEN');
	}
}

if (strpos($type,'ment_') === 0) {
	$idx = Request('idx');
	$ment = $this->getMent($idx);
	
	if ($ment == null) {
		$results->success = false;
		$results->message = $this->getErrorText('NOT_FOUND');
		return;
	}
	
	if ($type == 'ment_secret') {
		$permission = $this->checkSecretMentPermission($ment->idx);
		
		if ($permission === true) {
			$results->success = true;
		} elseif ($permission === false) {
			$results->success = false;
			$results->message = $this->getErrorText('FORBIDDEN');
		} else {
			$permittedSecretMents = Request('ModuleBoardPermittedSecretMents','session') ? Request('ModuleBoardPermittedSecretMents','session') : array();
			if ($ment->source == 0) {
				$password = Request('password');
				
				if ($password) {
					$post = $this->getPost($ment->parent);
					$mHash = new Hash();
					if ($mHash->password_validate($password,$ment->password) == true || $mHash->password_validate($password,$post->password) == true) {
						$results->success = true;
						$results->idx = $idx;
						$permittedSecretMents[] = $idx;
						$permittedSecretMents = array_unique($permittedSecretMents);
						$_SESSION['ModuleBoardPermittedSecretMents'] = $permittedSecretMents;
					} else {
						$results->success = false;
						$results->errors = array('password'=>$this->getErrorText('INCORRENT_PASSWORD'));
					}
				} else {
					$results->success = true;
					$results->modalHtml = $this->getPasswordModal($type,$idx);
				}
			} else {
				$password = Request('password');
				
				if ($password) {
					$parent = $this->getMent($ment->source);
					$mHash = new Hash();
					if ($mHash->password_validate($password,$ment->password) == true || $mHash->password_validate($password,$parent->password) == true) {
						$results->success = true;
						$results->idx = $idx;
						$permittedSecretMents[] = $idx;
						$permittedSecretMents = array_unique($permittedSecretMents);
						$_SESSION['ModuleBoardPermittedSecretMents'] = $permittedSecretMents;
					} else {
						$results->success = false;
						$results->errors = array('password'=>$this->getErrorText('INCORRENT_PASSWORD'));
					}
				} else {
					$results->success = true;
					$results->modalHtml = $this->getPasswordModal($type,$idx);
				}
			}
		}
	} else {
		if ($this->checkPermission($ment->bid,$type) == true) {
			$results->success = true;
		} elseif ($ment->midx == 0) {
			$password = Request('password');
			
			if ($password) {
				$mHash = new Hash();
				if ($mHash->password_validate($password,$ment->password) == true) {
					$results->success = true;
					$results->idx = $idx;
				} else {
					$results->success = false;
					$results->errors = array('password'=>$this->getErrorText('INCORRENT_PASSWORD'));
				}
			} else {
				$results->success = true;
				$results->modalHtml = $this->getPasswordModal($type,$idx);
			}
		} elseif ($ment->midx == $this->IM->getModule('member')->getLogged()) {
			$results->success = true;
		} else {
			$results->success = false;
			$results->message = $this->getErrorText('FORBIDDEN');
		}
	}
}
?>