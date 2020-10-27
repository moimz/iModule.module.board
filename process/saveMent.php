<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 댓글을 저장한다.
 *
 * @file /modules/board/process/saveMent.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2019. 6. 7.
 */
if (defined('__IM__') == false) exit;

$errors = array();

$idx = Request('idx');
$source = Param('source');
$parent = Param('parent');
$post = $this->getPost($parent);
$board = $this->getBoard($post->bid);
$bid = $board->bid;

if ($this->checkPermission($bid,'ment_write') == false) {
	$results->success = false;
	$results->message = $this->getErrorText('FORBIDDEN');
	return;
}

$is_secret = $board->allow_secret == true && Request('is_secret') ? 'TRUE' : 'FALSE';
$is_anonymity = $board->allow_anonymity == true && Request('is_anonymity') ? 'TRUE' : 'FALSE';
$content = Request('content') ? Request('content') : $errors['content'] = $this->getErrorText('REQUIRED');

if ($this->IM->getModule('member')->isLogged() == false) {
	$name = Request('name') ? Request('name') : $errors['name'] = $this->getErrorText('REQUIRED');
	$password = Request('password') ? Request('password') : $errors['password'] = $this->getErrorText('REQUIRED');
	$midx = 0;
} else {
	$name = $this->IM->getModule('member')->getMember()->nickname;
	$password = '';
	$midx = $this->IM->getModule('member')->getLogged();
}

$attachments = is_array(Request('attachments')) == true ? Request('attachments') : array();
for ($i=0, $loop=count($attachments);$i<$loop;$i++) {
	$attachments[$i] = Decoder($attachments[$i]);
}

if ($source) {
	$sourceData = $this->getMent($source);
	if ($sourceData == null) {
		$results->success = false;
		$results->message = $this->getErrorText('NOT_FOUND');
		return;
	}
}
$content = $this->IM->getModule('wysiwyg')->encodeContent($content,$attachments);

$field1 = Request('field1');
$field2 = Request('field2');
$field3 = Request('field3');
$field4 = Request('field4') == null || is_numeric(Request('field4')) == false ? null : Request('field4');
$field5 = Request('field5') == null || is_numeric(Request('field5')) == false ? null : Request('field5');
$field6 = Request('field6') == null || is_numeric(Request('field6')) == false ? null : Request('field6');
$extra = Request('extra') ? Request('extra') : null;

if (empty($errors) == true) {
	$mHash = new Hash();
	
	$insert = array();
	$insert['bid'] = $bid;
	$insert['parent'] = $parent;
	$insert['content'] = $content;
	$insert['search'] = GetString($content,'index');
	$insert['is_secret'] = $is_secret;
	$insert['is_anonymity'] = $is_anonymity;
	
	if ($field1 !== null) $insert['field1'] = $field1;
	if ($field2 !== null) $insert['field2'] = $field2;
	if ($field3 !== null) $insert['field3'] = $field3;
	if ($field4 !== null) $insert['field4'] = $field4;
	if ($field5 !== null) $insert['field5'] = $field5;
	if ($field6 !== null) $insert['field6'] = $field6;
	if ($extra) $insert['extra'] = $extra;
	
	if ($idx == null) {
		$insert['midx'] = $midx;
		$insert['password'] = $password;
		$insert['name'] = $name;
		$insert['password'] = $password ? $mHash->password_hash($password) : '';
		$insert['reg_date'] = time();
		$insert['ip'] = $_SERVER['REMOTE_ADDR'];
		
		$idx = $this->db()->insert($this->table->ment,$insert)->execute();
		if ($idx === false) {
			$results->success = false;
			$results->message = $this->getErrorText('DATABASE_INSERT_ERROR');
			return;
		}
		
		if ($source) {
			$sourceData = $this->getMent($source);
			$head = $sourceData->head;
			$depth = $sourceData->depth + 1;
			$source = $sourceData->idx;
			
			if ($depth > 1) {
				$depthData = $this->db()->select($this->table->ment_depth)->where('head',$sourceData->head)->where('arrange',$sourceData->arrange,'>')->where('depth',$sourceData->depth,'<=')->orderBy('arrange','asc')->getOne();
				
				if ($depthData == null) {
					$arrange = $idx;
				} else {
					$arrange = $depthData->arrange;
					$this->db()->update($this->table->ment_depth,array('arrange'=>$this->db()->inc()))->where('head',$sourceData->head)->where('arrange',$arrange,'>=')->execute();
				}
			} else {
				$arrange = $idx;
			}
		} else {
			$head = $idx;
			$arrange = $idx;
			$depth = 0;
			$source = 0;
		}
		
		$this->db()->insert($this->table->ment_depth,array('idx'=>$idx,'parent'=>$parent,'head'=>$head,'arrange'=>$arrange,'depth'=>$depth,'source'=>$source))->execute();
		
		/**
		 * 포인트 및 활동내역을 기록한다.
		 */
		if ($this->IM->getModule('member')->isLogged() == true) {
			$this->IM->getModule('member')->sendPoint($this->IM->getModule('member')->getLogged(),$board->ment_point,$this->getModule()->getName(),'ment',array('idx'=>$idx,'parent'=>$post->idx,'title'=>$post->title));
			$this->IM->getModule('member')->addActivity($this->IM->getModule('member')->getLogged(),$board->ment_exp,$this->getModule()->getName(),'ment',array('idx'=>$idx,'parent'=>$post->idx,'title'=>$post->title));
		}
		
		/**
		 * 게시물 작성자에게 알림메세지를 전송한다.
		 */
		if ($post->midx != 0 && $post->midx != $this->IM->getModule('member')->getLogged()) {
			$this->IM->getModule('push')->sendPush($post->midx,$this->getModule()->getName(),'post',$post->idx,'new_ment',array('idx'=>$idx,'parent'=>$post->idx,'title'=>$post->title));
		}
		
		/**
		 * 댓글의 댓글일 경우 원 댓글자에게 알림메세지를 전송한다.
		 */
		if ($source != 0 && $sourceData->midx != 0 && $sourceData->midx != $this->IM->getModule('member')->getLogged()) {
			$this->IM->getModule('push')->sendPush($sourceData->midx,$this->getModule()->getName(),'ment',$sourceData->idx,'new_reply_ment',array('idx'=>$idx,'parent'=>$post->idx,'source'=>$sourceData->idx,'title'=>$post->title));
		}
		
		$message = '댓글을 성공적으로 작성하였습니다.';
	} else {
		$ment = $this->getMent($idx);
		
		if ($this->checkPermission($bid,'ment_modify') == false && ($ment->midx != 0 && $ment->midx != $this->IM->getModule('member')->getLogged())) {
			$results->success = false;
			$results->message = $this->getErrorText('FORBIDDEN');
			return;
		} elseif ($ment->midx == 0) {
			if ($mHash->password_validate($password,$ment->password) == false) {
				$results->success = false;
				$results->errors = array('password'=>$this->getErrorText('INCORRENT_PASSWORD'));
				return;
			}
		}
		
		if ($ment->midx == 0) {
			$insert['name'] = $name;
			$insert['password'] = $password ? $mHash->password_hash($password) : '';
			$insert['ip'] = $_SERVER['REMOTE_ADDR'];
		}
		
		$this->db()->update($this->table->ment,$insert)->where('idx',$ment->idx)->execute();
		
		/**
		 * 댓글작성자와 수정한 사람이 다를 경우 알림메세지를 전송한다.
		 */
		if ($ment->midx != 0 && $ment->midx != $this->IM->getModule('member')->getLogged()) {
			$this->IM->getModule('push')->sendPush($ment->midx,$this->getModule()->getName(),'ment',$idx,'ment_modify',array('idx'=>$idx,'parent'=>$post->idx,'from'=>$this->IM->getModule('member')->getLogged(),'title'=>$post->title));
		}
		
		/**
		 * 활동내역을 기록한다.
		 */
		if ($this->IM->getModule('member')->isLogged() == true) {
			$this->IM->getModule('member')->addActivity($this->IM->getModule('member')->getLogged(),0,$this->getModule()->getName(),'ment_modify',array('idx'=>$idx,'parent'=>$post->idx,'title'=>$post->title));
		}
		
		$message = '댓글을 성공적으로 수정하였습니다.';
	}
	
	$mAttachment = $this->IM->getModule('attachment');
	for ($i=0, $loop=count($attachments);$i<$loop;$i++) {
		if ($this->db()->select($this->table->attachment)->where('idx',$attachments[$i])->count() == 0) {
			$this->db()->insert($this->table->attachment,array('idx'=>$attachments[$i],'bid'=>$bid,'type'=>'MENT','parent'=>$idx))->execute();
		}
		$mAttachment->filePublish($attachments[$i]);
	}
	
	$deleteds = $this->db()->select($this->table->attachment)->where('bid',$bid)->where('type','MENT')->where('parent',$idx);
	if (count($attachments) > 0) $deleteds->where('idx',$attachments,'NOT IN');
	$deleteds = $deleteds->get('idx');
	foreach ($deleteds as $deleted) {
		$mAttachment->fileDelete($deleted);
	}
	
	$this->updatePost($parent);
	$this->updateBoard($bid);
	
	if ($is_secret == 'TRUE') {
		$permittedSecretMents = Request('ModuleBoardPermittedSecretMents','session') ? Request('ModuleBoardPermittedSecretMents','session') : array();
		$permittedSecretMents[] = $idx;
		
		$permittedSecretMents = array_unique($permittedSecretMents);
		$_SESSION['ModuleBoardPermittedSecretMents'] = $permittedSecretMents;
	}
	
	$results->success = true;
	$results->idx = $idx;
	$results->parent = $parent;
	$results->page = $this->getMentPage($idx);
	$results->message = $message;
} else {
	$results->success = false;
	$results->errors = $errors;
}
?>