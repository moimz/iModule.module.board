<?php
/**
 * 이 파일은 게시판모듈의 일부입니다. (http://www.coursemos.kr)
 *
 * 관리자 정보를 가져온다.
 * 
 * @file /modules/board/process/@getAdmin.php
 * @author Eunseop Lim (eslim@naddle.net)
 * @license MIT License
 * @version 3.0.0
 */
if (defined('__IM__') == false) exit;

$midx = Request('midx');
$admin = $this->db()->select($this->table->admin)->where('midx',$midx)->getOne();
if ($admin == null) {
	$results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND');
} else {
	$results->success = true;
	
	$member = $this->IM->getModule('member')->getMember($admin->midx);
	$results->text = $member->name;
	$results->text.= $member->coursemos != null && $member->coursemos->institution != null ? ' / '.$member->coursemos->institution->title : '';
	$results->text.= $member->coursemos != null && $member->coursemos->department != null ? ' / '.$member->coursemos->department->title : '';
	$results->text.= $member->coursemos != null ? ' / '.$member->coursemos->haksa : '';
    
    if ($admin->bid == '*') {
        $results->bid = $this->db()->select($this->table->board)->get('bid');
    } else {
        $results->bid = explode(',',$admin->bid);
    }
}
?>