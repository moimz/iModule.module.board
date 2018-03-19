<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 게시판 웹진템플릿 - 헤더
 * 
 * @file /modules/board/templets/webzine/header.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 2. 25.
 */
if (defined('__IM__') == false) exit;

$IM->loadWebFont('XEIcon');
?>
<style>
div[data-module=board] ul[data-role=webzine] > li {width:<?php echo sprintf('%.010f',100 / $Templet->getConfig('columns')); ?>%;}
div[data-module=board] ul[data-role=webzine] > li > div {padding-bottom:<?php echo $Templet->getConfig("height"); ?>%;}
div[data-module=board] div[data-role=cover] {padding:0; padding-bottom:<?php echo $Templet->getConfig("height"); ?>%;}
</style>