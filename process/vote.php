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
 * @modified 2019. 11. 27.
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

$midx = $this->IM->getModule('member')->getLogged();

if ($this->db()->select($this->table->activity)->where('type',$type)->where('parent',$idx)->where('midx',$midx)->has() == true) {
	$results->success = false;
	$results->error = $this->getErrorText('ALREADY_VOTED');
	return;
}

if ($type == 'post') {
	$post = $this->getPost($idx);
	$board = $this->getBoard($post->bid);
	
	if ($post == null) {
		$results->success = false;
		$results->message = $this->getErrorText('NOT_FOUND');
		return;
	}
	
	if ($post->midx == $midx) {
		$results->success = false;
		$results->error = $this->getErrorText('DISABLED_VOTE_MYSELF');
		return;
	}
	
	$this->db()->insert($this->table->activity,array('type'=>$type,'parent'=>$idx,'midx'=>$midx,'code'=>$vote,'reg_date'=>time()))->execute();
	$updated = $this->updatePost($idx);
	
	$this->IM->getModule('member')->sendPoint($midx,$board->vote_point,$this->getModule()->getName(),'post_vote',array('idx'=>$idx,'title'=>$post->title));
	$this->IM->getModule('member')->addActivity($midx,$board->vote_exp,$this->getModule()->getName(),'post_vote',array('idx'=>$idx,'title'=>$post->title));
	
	if ($post->midx > 0) {
		$this->IM->getModule('member')->sendPoint($midx,$board->voted_point,$this->getModule()->getName(),'post_voted',array('idx'=>$idx,'title'=>$post->title));
		$this->IM->getModule('member')->addActivity($midx,$board->voted_exp,$this->getModule()->getName(),'post_voted',array('idx'=>$idx,'title'=>$post->title));
		
		$this->IM->getModule('push')->sendPush($post->midx,$this->getModule()->getName(),'post',$post->idx,'post_voted',array('idx'=>$idx,'from'=>$midx,'title'=>$post->title));
	}
	
	$results->success = true;
	$results->vote = $vote;
	$results->good = $updated->good;
	$results->bad = $updated->bad;
} else {
	$ment = $this->getMent($idx);
	if ($ment == null) {
		$results->success = false;
		$results->message = $this->getErrorText('NOT_FOUND');
		return;
	}
	
	if ($ment->midx == $midx) {
		$results->success = false;
		$results->error = $this->getErrorText('DISABLED_VOTE_MYSELF');
		return;
	}
	
	
}
?>