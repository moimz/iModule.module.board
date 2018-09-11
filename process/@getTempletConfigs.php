<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodules.io)
 * 
 * 게시판 템플릿의 환경설정폼을 가져온다.
 *
 * @file /modules/board/process/@getTempletConfigs.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 9. 9.
 */
if (defined('__IM__') == false) exit;

$bid = Param('bid');
$name = Param('name');
$templet = Param('templet');

if ($name == 'templet') {
	$Templet = $this->getModule()->getTemplet($templet);
	$board = $this->getBoard($bid);
	
	if ($board !== null && $board->templet == $templet) $Templet->setConfigs($board->templet_configs);
	$configs = $Templet->getConfigs();
}

if ($name == 'attachment') {
	$Templet = $this->IM->getModule('attachment')->getTemplet($templet);
	$board = $this->getBoard($bid);
	
	if ($board !== null && $board->use_attachment == true && $board->attachment->templet == $templet) $Templet->setConfigs($board->attachment->templet_configs);
	$configs = $Templet->getConfigs();
}

$results->success = true;
$results->configs = $configs;
?>