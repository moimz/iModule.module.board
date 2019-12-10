<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodules.io)
 *
 * 관리자목록을 가져온다.
 *
 * @file /modules/board/process/@getAdmins.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2019. 12. 11.
 */
if (defined('__IM__') == false) exit;

$lists = $this->db()->select($this->table->admin)->get();
for ($i=0, $loop=count($lists);$i<$loop;$i++) {
	$member = $this->IM->getModule('member')->getMember($lists[$i]->midx);
	
	$lists[$i]->name = $member->name;
	$lists[$i]->email = $member->email;

	if ($lists[$i]->bid == '*') {
		$lists[$i]->bid = $this->getText('admin/admin/admin_all');
	} else {
		$bids = explode(',',$lists[$i]->bid);
		foreach ($bids as &$bid) {
			$board = $this->getBoard($bid);
			$bid = $board == null ? 'Unknown('.$bid.')' : $board->title.'('.$bid.')';
		}
		$lists[$i]->bid = implode(', ',$bids);
	}
}

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>