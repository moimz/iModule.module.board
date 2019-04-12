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
 * @modified 2019. 4. 12.
 */
if (defined('__IM__') == false) exit;

$midx = explode(',', Request('midx'));

$this->db()->startTransaction();
foreach ($midx as $item) {
    $this->db()->delete($this->table->admin)->where('midx',$item)->execute();
}
$this->db()->commit();
$results->success = true;
?>