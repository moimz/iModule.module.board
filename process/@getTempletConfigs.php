<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 * 
 * 게시판 템플릿의 환경설정폼을 가져온다.
 *
 * @file /modules/board/process/@getTempletConfigs.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0.160923
 *
 * @return object $results
 */
if (defined('__IM__') == false) exit;

$bid = Request('bid');
$name = Request('name');
$templet = Request('templet');

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