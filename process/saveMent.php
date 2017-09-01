<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 댓글을 저장한다.
 *
 * @file /modules/board/process/saveMent.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160923
 */
if (defined('__IM__') == false) exit;

$errors = array();

$idx = Request('idx');
$source = Request('source');
$parent = Request('parent');
$post = $this->getPost($parent);
$board = $this->getBoard($post->bid);
$bid = $board->bid;

$is_secret = $board->allow_secret == true && Request('is_secret') ? 'TRUE' : 'FALSE';
$is_anonymity = $board->allow_anonymity == true && Request('is_anonymity') && $this->IM->getModule('member')->isLogged() == true ? 'TRUE' : 'FALSE';
$content = Request('content') ? Request('content') : $errors['content'] = $this->getErrorText('REQUIRED');

if ($this->IM->getModule('member')->isLogged() == false) {
	$name = Request('name') ? Request('name') : $errors['name'] = $this->getErrorText('REQUIRED');
	$password = Request('password') ? Request('password') : $errors['password'] = $this->getErrorText('REQUIRED');
	$email = Request('email');
	$midx = 0;
} else {
	$name = $this->IM->getModule('member')->getMember()->nickname;
	$password = '';
	$email = $this->IM->getModule('member')->getMember()->email;
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
		$results->message = $this->getLanguage('mentWrite/deleteSource');
		return;
	}
}
$content = $this->IM->getModule('wysiwyg')->encodeContent($content,$attachments);

if (empty($errors) == true) {
	$mHash = new Hash();
	
	$insert = array();
	$insert['bid'] = $bid;
	$insert['parent'] = $parent;
	$insert['content'] = $content;
	$insert['search'] = GetString($content,'index');
	$insert['is_secret'] = $is_secret;
	$insert['is_anonymity'] = $is_anonymity;
	
	if ($idx == null) {
		$insert['midx'] = $midx;
		$insert['password'] = $password;
		$insert['name'] = $name;
		$insert['password'] = $password ? $mHash->password_hash($password) : '';
		$insert['email'] = $email;
		$insert['reg_date'] = time();
		$insert['ip'] = $_SERVER['REMOTE_ADDR'];
		
		$idx = $this->db()->insert($this->table->ment,$insert)->execute();
		
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
		
		if ($this->IM->getModule('member')->isLogged() == true) {
//			$this->IM->getModule('member')->sendPoint(null,$board->ment_point,'board','ment',array('idx'=>$idx));
//			$this->IM->getModule('member')->addActivity(null,$board->ment_exp,'board','ment',array('idx'=>$idx));
		}
		
		if ($post->midx != 0 && $post->midx != $this->IM->getModule('member')->getLogged()) {
//			$this->IM->getModule('push')->sendPush($post->midx,'board','ment',$post->idx,array('idx'=>$idx,'from'=>($name)));
		}
		
		if ($source != 0 && $sourceData->midx != 0 && $sourceData->midx != $this->IM->getModule('member')->getLogged()) {
//			$this->IM->getModule('push')->sendPush($sourceData->midx,'board','replyment',$post->idx,array('idx'=>$idx,'from'=>($name)));
		}
	} else {
		$ment = $this->getMent($idx);
		
		if ($this->checkPermission($bid,'ment_modify') == false && ($ment->midx != 0 && $ment->midx != $this->IM->getModule('member')->getLogged())) {
			$results->success = false;
			$results->message = $this->getErrorText('FORBIDDEN');
		} elseif ($this->checkPermission($bid,'ment_modify') == false && $ment->midx == 0) {
			if ($mHash->password_validate($password,$ment->password) == false) {
				$results->success = false;
				$results->errors = array('password'=>$this->getLanguage('error/incorrectPassword'));
				$results->message = $this->getLanguage('error/incorrectPassword');
			}
		}
		
		if ($results->success == true) {
			if ($this->IM->getModule('member')->isLogged() == false) {
				$insert['name'] = $name;
				$insert['password'] = $password ? $mHash->password_hash($password) : '';
				$insert['email'] = $email;
				$insert['ip'] = $_SERVER['REMOTE_ADDR'];
			}
			$insert['modify_date'] = time();
			
			$this->db()->update($this->table->ment,$insert)->where('idx',$ment->idx)->execute();
			
			if ($ment->midx != 0 && $ment->midx != $this->IM->getModule('member')->getLogged()) {
//				$this->IM->getModule('push')->sendPush($ment->midx,'board','ment_modify',$idx,array('from'=>($name)));
			}
			
			if ($this->IM->getModule('member')->isLogged() == true) {
//				$this->IM->getModule('member')->addActivity(null,0,'board','ment_modify',array('idx'=>$idx));
			}
		}
	}
	
	for ($i=0, $loop=count($attachments);$i<$loop;$i++) {
		if ($this->db()->select($this->table->attachment)->where('idx',$attachments[$i])->count() == 0) {
			$this->db()->insert($this->table->attachment,array('idx'=>$attachments[$i],'bid'=>$bid,'type'=>'MENT','parent'=>$idx))->execute();
		}
		$this->IM->getModule('attachment')->filePublish($attachments[$i]);
	}
	
	$this->updatePost($parent);
	$this->updateBoard($bid);
	
	if ($post->is_secret != 'TRUE') {
//			$this->IM->setArticle('board',$bid,'ment',$idx,time());
//			$this->IM->setArticle('board',$bid,'post',$post->idx,time());
	}
	
	$results->success = true;
	$results->idx = $idx;
	$results->parent = $parent;
	$results->page = $this->getMentPage($idx);
	$results->message = '댓글을 성공적으로 작성하였습니다.';
} else {
	$results->success = false;
	$results->message = $this->getErrorText('REQUIRED');
	$results->errors = $errors;
}
?>