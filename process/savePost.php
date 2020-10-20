<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodules.io)
 *
 * 게시물을 저장한다.
 *
 * @file /modules/board/process/savePost.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2019. 6. 7.
 */
if (defined('__IM__') == false) exit;

$errors = array();

$idx = Request('idx');
$bid = Param('bid');
$board = $this->getBoard($bid);

if ($this->checkPermission($bid,'post_write') == false) {
	$results->success = false;
	$results->message = $this->getErrorText('FORBIDDEN');
	return;
}

$category = Request('category');
$title = Request('title') ? Request('title') : $errors['title'] = $this->getErrorText('REQUIRED');
$content = Request('content') ? Request('content') : $errors['content'] = $this->getErrorText('REQUIRED');;
$is_notice = Request('is_notice') && $this->checkPermission($bid,'notice') == true ? 'TRUE' : 'FALSE';
$is_html_title = Request('is_html_title') && $this->checkPermission($bid,'html_title') == true ? 'TRUE' : 'FALSE';
$is_secret = $board->allow_secret == true && Request('is_secret') ? 'TRUE' : 'FALSE';
$is_anonymity = $board->allow_anonymity == true && Request('is_anonymity') ? 'TRUE' : 'FALSE';

if ($this->IM->getModule('member')->isLogged() == false) {
	$name = Request('name') ? Request('name') : $errors['name'] = $this->getErrorText('REQUIRED');
	$password = Request('password') ? Request('password') : $errors['password'] = $this->getErrorText('REQUIRED');
	$midx = 0;
} else {
	$name = $password = '';
	$midx = $this->IM->getModule('member')->getLogged();
}

$attachments = is_array(Request('attachments')) == true ? Request('attachments') : array();
for ($i=0, $loop=count($attachments);$i<$loop;$i++) {
	$attachments[$i] = Decoder($attachments[$i]);
}

$content = $this->IM->getModule('wysiwyg')->encodeContent($content,$attachments);
$board = $this->getBoard($bid);

if ($board->use_category != 'NONE') {
	if ($board->use_category == 'FORCE' && ($category == null || preg_match('/^[1-9]+[0-9]*$/',$category) == false)) {
		$errors['category'] = $this->getErrorText('REQUIRED');
	}
} else {
	$category = 0;
}

if ($board->use_prefix == 'TRUE') {
	$prefix = Request('prefix') ? Request('prefix') : 0;
	if ($prefix != 0 && $this->db()->select($this->table->prefix)->where('idx',$prefix)->has() == false) {
		$errors['prefix'] = $this->getErrorText('NOT_FOUND');
	}
} else {
	$prefix = 0;
}

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
	$insert['category'] = $category;
	$insert['prefix'] = $prefix;
	$insert['title'] = $title;
	$insert['content'] = $content;
	$insert['search'] = GetString($content,'index');
	$insert['is_notice'] = $is_notice;
	$insert['is_html_title'] = $is_html_title;
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

		$idx = $this->db()->insert($this->table->post,$insert)->execute();
		if ($idx === false) {
			$results->success = false;
			$results->message = $this->getErrorText('DATABASE_INSERT_ERROR');
			return;
		}

		/**
		 * 포인트 및 활동내역을 기록한다.
		 */
		if ($this->IM->getModule('member')->isLogged() == true) {
			$this->IM->getModule('member')->sendPoint($this->IM->getModule('member')->getLogged(),$board->post_point,$this->getModule()->getName(),'post',array('idx'=>$idx,'title'=>$title));
			$this->IM->getModule('member')->addActivity($this->IM->getModule('member')->getLogged(),$board->post_exp,$this->getModule()->getName(),'post',array('idx'=>$idx,'title'=>$title));
		}
	} else {
		$post = $this->getPost($idx);

		if ($this->checkPermission($post->bid,'post_modify') == false) {
			if ($post->midx != 0 && $post->midx != $this->IM->getModule('member')->getLogged()) {
				$results->success = false;
				$results->message = $this->getErrorText('FORBIDDEN');
				return;
			} elseif ($post->midx == 0) {
				if ($mHash->password_validate($password,$post->password) == false) {
					$results->success = false;
					$results->errors = array('password'=>$this->getErrorText('INCORRENT_PASSWORD'));
					$results->message = $this->getErrorText('INCORRENT_PASSWORD');
					return;
				}
			}
		}


		$idx = $post->idx;

		if ($post->midx == 0 && $this->IM->getModule('member')->isLogged() == false) {
			$insert['name'] = $name;
			$insert['password'] = $password ? $mHash->password_hash($password) : '';
			$insert['ip'] = $_SERVER['REMOTE_ADDR'];
		}

		$this->db()->update($this->table->post,$insert)->where('idx',$idx)->execute();

		if ($post->category != $category) {
			$this->updateCategory($post->category);
		}

		if ($post->prefix != $prefix) {
			$this->updatePrefix($post->prefix);
		}

		/**
		 * 글작성자와 수정한 사람이 다를 경우 알림메세지를 전송한다.
		 */
		if ($post->midx != 0 && $post->midx != $this->IM->getModule('member')->getLogged()) {
			$this->IM->getModule('push')->sendPush($post->midx,$this->getModule()->getName(),'post',$idx,'post_modify',array('idx'=>$idx,'from'=>$this->IM->getModule('member')->getLogged(),'title'=>$post->title));
		}

		/**
		 * 회원의 경우
		 */
		if ($this->IM->getModule('member')->isLogged() == true) {
			$this->IM->getModule('member')->addActivity($this->IM->getModule('member')->getLogged(),0,$this->getModule()->getName(),'post_modify',array('idx'=>$idx,'title'=>$title));
		}
	}

	$mAttachment = $this->IM->getModule('attachment');
	for ($i=0, $loop=count($attachments);$i<$loop;$i++) {
		$file = $mAttachment->getFileInfo($attachments[$i]);

		if ($file != null) {
			$this->db()->replace($this->table->attachment,array('idx'=>$file->idx,'bid'=>$bid,'type'=>'POST','parent'=>$idx))->execute();
		}
		$mAttachment->filePublish($attachments[$i]);
	}

	$deleteds = $this->db()->select($this->table->attachment)->where('bid',$bid)->where('type','POST')->where('parent',$idx);
	if (count($attachments) > 0) $deleteds->where('idx',$attachments,'NOT IN');
	$deleteds = $deleteds->get('idx');
	foreach ($deleteds as $deleted) {
		$mAttachment->fileDelete($deleted);
	}

	$templet = Request('templet');
	if (is_file($this->getTemplet($templet)->getPath().'/process/savePost.php') == true) {
		INCLUDE $this->getTemplet($templet)->getPath().'/process/savePost.php';
	}

	$this->updatePost($idx,true);
	$this->updateCategory($category);
	$this->updatePrefix($prefix);
	$this->updateBoard($bid);

	$results->success = true;
	$results->idx = $idx;
} else {
	$results->success = false;
	$results->errors = $errors;
}
?>