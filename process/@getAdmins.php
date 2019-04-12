<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (http://www.coursemos.kr)
 *
 * 운영자 리스트를 가져옵니다.
 *
 * @file /modules/board/process/@deletePoll.php
 * @author Eunseop Lim (eslim@naddle.net)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 4. 12.
 */
if (defined('__IM__') == false) exit;

$lists = $this->db()->select($this->table->admin)->get();
for ($i=0, $loop=count($lists);$i<$loop;$i++) {
	$member = $this->IM->getModule('member')->getMember($lists[$i]->midx);
	
	$lists[$i]->name = $member->name;
	$lists[$i]->institution = $member->coursemos != null && $member->coursemos->institution != null ? $member->coursemos->institution->title : '';
	$lists[$i]->department = $member->coursemos != null && $member->coursemos->department != null ? $member->coursemos->department->title : '';
	$lists[$i]->haksa = $member->coursemos != null ? $member->coursemos->haksa : '';

	if ($lists[$i]->bid == '*') {
		$lists[$i]->bid = '모든 게시판';
	} else {
		$bids = explode(',',$lists[$i]->bid);
		$lists[$i]->bid = '';

		foreach ($bids as $bid) {
			$lists[$i]->bid.= $lists[$i]->bid ? ', ' : '';
			$lists[$i]->bid.= $bid == '' ? '' : $this->getBoard($bid)->title;
		}
	}
}

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>