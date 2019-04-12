<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (http://www.coursemos.kr)
 *
 * 운영자를 추가한다
 *
 * @file /modules/board/process/@saveAdmin.php
 * @author Eunseop Lim (eslim@naddle.net)1
 * @license MIT License
 * @version 3.0.0
 * @modified 2019. 4. 12.
 */
if (defined('__IM__') == false) exit;

$bid = Request('bid');
$midx = Request('midx');

$count = count(explode(',', $bid));
$board_count = $this->db()->select($this->table->board, 'COUNT(*) AS count')->getOne()->count;

if ($count == $board_count) {
    $bid = '*';
}

$this->db()->replace($this->table->admin,array('bid'=>$bid,'midx'=>$midx))->execute();
$results->success = true;
?>