<?php
/**
 * 이 파일은 iModule 게시판 모듈의 일부입니다. (http://www.coursemos.kr)
 *
 * 운영자를 삭제한다.
 *
 * @file /modules/board/process/@deleteAdmin.php
 * @author Eunseop Lim (eslim@naddle.net)
 * @license MIT License
 * @version 3.0.0
 * @modified 2019. 3. 4.
 */
if (defined('__IM__') == false) exit;

$bid = Request('bid');
$midx = Request('midx');

$this->db()->delete($this->table->admin)->where('bid',$bid)->where('midx',$midx)->execute();
$results->success = true;
?>