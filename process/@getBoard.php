<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 게시판 정보를 불러온다.
 *
 * @file /modules/board/process/@getBoard.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160923
 *
 * @return object $results
 */
if (defined('__IM__') == false) exit;
if (defined('__IM__') == false) exit;

$bid = Request('bid');
$data = $this->db()->select($this->table->board)->where('bid',$bid)->getOne();

if ($data == null) {
	$results->success = false;
	$results->message = $this->getErrorText('NOT_FOUND');
} else {
	$permission = json_decode($data->permission);
	unset($data->permission);
	
	if ($permission != null) {
		foreach ($permission as $key=>$value) {
			$data->{'permission_'.$key} = $value;
		}
	}
	$results->success = true;
	$results->data = $data;
}
?>