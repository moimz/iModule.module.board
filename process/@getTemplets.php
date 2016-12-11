<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 서버에 존재하는 게시판모듈의 템플릿 목록을 가져온다.
 * 
 * @file /modules/board/process/@getTemplets.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0.161211
 */
if (defined('__IM__') == false) exit;

$templets = $this->getModule()->getTemplets();
$lists = array();
for ($i=0, $loop=count($templets);$i<$loop;$i++) {
	$lists[] = array('display'=>$templets[$i]->getTitle().' ('.$templets[$i]->getDir().')','value'=>$templets[$i]->getName());
}

$results->success = true;
$results->lists = $lists;
$results->total = count($lists);
?>