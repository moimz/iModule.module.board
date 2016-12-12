<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 게시판정보를 저장한다.
 *
 * @file /modules/board/process/@saveBoard.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160923
 *
 * @post string $bid board id
 * @post int $category category idx
 * @post string $title post title
 * @post string $content post content
 * @post string $name author name (posted by guest)
 * @post string $password post password (posted by guest)
 * @post string $email author email (posted by guest)
 * @post string $is_notice set notice (TRUE or FALSE)
 * @post string $is_html_title using html tag in post title (TRUE or FALSE)
 * @post string $is_secret set secret post (TRUE or FALSE)
 * @post string $is_hidename set hide author name (TRUE or FALSE)
 * @return object $results
 */
if (defined('__IM__') == false) exit;

$errors = array();

$idx = Request('idx');
$bid = Request('bid');
$category = Request('category');
$title = Request('title') ? Request('title') : $errors['title'] = $this->getErrorText('REQUIRED');
$content = Request('content') ? Request('content') : $errors['content'] = $this->getErrorText('REQUIRED');;
$is_notice = Request('is_notice') && $this->checkPermission($bid,'notice') == true ? 'TRUE' : 'FALSE';
$is_html_title = Request('is_html_title') && $this->checkPermission($bid,'html_title') == true ? 'TRUE' : 'FALSE';
$is_secret = Request('is_secret') ? 'TRUE' : 'FALSE';
$is_hidename = Request('is_hidename') && $this->checkPermission($bid,'hidename') == true ? 'TRUE' : 'FALSE';

if ($this->IM->getModule('member')->isLogged() == false) {
	$name = Request('name') ? Request('name') : $errors['name'] = $this->getErrorText('REQUIRED');
	$password = Request('password') ? Request('password') : $errors['password'] = $this->getErrorText('REQUIRED');
	$email = Request('email');
	$midx = 0;
} else {
	$name = $password = '';
	$email = $this->IM->getModule('member')->getMember()->email;
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

if (empty($errors) == true) {
	$mHash = new Hash();
	
	$insert = array();
	$insert['bid'] = $bid;
	$insert['category'] = $category;
	$insert['title'] = $title;
	$insert['content'] = $content;
	$insert['search'] = GetString($content,'index');
	$insert['is_notice'] = $is_notice;
	$insert['is_html_title'] = $is_html_title;
	$insert['is_secret'] = $is_secret;
	$insert['is_hidename'] = $is_hidename;
	
	if ($idx == null) { // save new post
		$insert['midx'] = $midx;
		$insert['password'] = $password;
		$insert['name'] = $name;
		$insert['password'] = $password ? $mHash->password_hash($password) : '';
		$insert['email'] = $email;
		$insert['reg_date'] = time();
		$insert['ip'] = $_SERVER['REMOTE_ADDR'];
		
		$idx = $this->db()->insert($this->table->post,$insert)->execute();
		
		if ($this->IM->getModule('member')->isLogged() == true) {
			$this->IM->getModule('member')->sendPoint(null,$board->post_point,'board','post',array('idx'=>$idx));
			$this->IM->getModule('member')->addActivity(null,$board->post_exp,'board','post',array('idx'=>$idx));
		}
		
		$results->success = true;
	} else {
		$results->success = true;
		$post = $this->getPost($idx); // get original post data
		
		if ($this->checkPermission($post->bid,'post_modify') == false) { // check permission
			if ($post->midx != 0 && $post->midx != $this->IM->getModule('member')->getLogged()) {
				$results->success = false;
				$results->message = $this->getText('error/forbidden');
			} elseif ($post->midx == 0) {
				if ($mHash->password_validate($password,$post->password) == false) {
					$results->success = false;
					$results->errors = array('password'=>$this->getText('error/incorrectPassword'));
					$results->message = $this->getText('error/incorrectPassword');
				}
			}
		}
		
		if ($results->success == true) {
			$idx = $post->idx;
			
			if ($post->midx == 0 && $this->IM->getModule('member')->isLogged() == false) {
				$insert['name'] = $name;
				$insert['password'] = $password ? $mHash->password_hash($password) : '';
				$insert['email'] = $email;
				$insert['ip'] = $_SERVER['REMOTE_ADDR'];
			}
			
			$this->db()->update($this->table->post,$insert)->where('idx',$idx)->execute();
			
			if ($post->category != $category) {
				$this->updateCategory($post->category);
			}
			
			// modified others user not original author
			if ($post->midx != 0 && $post->midx != $this->IM->getModule('member')->getLogged()) {
				$this->IM->getModule('push')->sendPush($post->midx,'board','post_modify',$idx,array('from'=>$name));
			}
			
			if ($this->IM->getModule('member')->isLogged() == true) {
				$this->IM->getModule('member')->addActivity(null,0,'board','post_modify',array('idx'=>$idx));
			}
		}
	}
	
	if ($results->success == true) {
		// save attachment
		$mAttachment = $this->IM->getModule('attachment');
		for ($i=0, $loop=count($attachments);$i<$loop;$i++) {
			if ($this->db()->select($this->table->attachment)->where('idx',$attachments[$i])->count() == 0) {
				$this->db()->insert($this->table->attachment,array('idx'=>$attachments[$i],'bid'=>$bid,'type'=>'POST','parent'=>$idx))->execute();
			}
			$mAttachment->filePublish($attachments[$i]);
		}
		
		$this->updateCategory($category);
		$this->updateBoard($bid);
		$this->IM->setArticle('board',$bid,'post',$idx,time());
		
		$results->idx = $idx;
	}
} else {
	$results->success = false;
	$results->errors = $errors;
}
?>