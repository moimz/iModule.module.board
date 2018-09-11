<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 게시판을 삭제한다.
 *
 * @file /modules/board/process/@deleteBoard.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 9. 9.
 */
if (defined('__IM__') == false) exit;

$bids = Request('bid') ? explode(',',Request('bid')) : array();
foreach ($bids as $bid) {
	$this->db()->delete($this->table->post)->where('bid',$bid)->execute();
	
	$ments = $this->db()->select($this->table->ment)->where('bid',$bid)->get('idx');
	if (count($ments) > 0) $this->db()->delete($this->table->ment_depth)->where('idx',$ments,'IN')->execute();
	$this->db()->delete($this->table->ment)->where('bid',$bid)->execute();
	
	$files = $this->db()->select($this->table->attachment)->where('bid',$bid)->get();
	foreach ($files as $file) {
		$this->IM->getModule('attachment')->fileDelete($file->idx);
	}
	
	$this->db()->delete($this->table->category)->where('bid',$bid)->execute();
	$this->db()->delete($this->table->board)->where('bid',$bid)->execute();
}

$results->success = true;
?>