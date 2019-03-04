<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (http://www.coursemos.kr)
 *
 * 게시판 목록및 운영자수를 집계합니다.
 *
 * @file /modules/board/process/@getAdminBoards.php
 * @author Eunseop Lim (eslim@naddle.net)
 * @license MIT License
 * @version 3.0.0
 * @modified 2019. 3. 4.
 */
if (defined('__IM__') == false) exit;

$lists = $this->db()->select($this->table->board.' b', 'b.bid, b.title, count(a.bid) as count')->join($this->table->admin.' a', 'b.bid=a.bid', 'LEFT')->groupBy('b.bid, b.title')->get();

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);

?>