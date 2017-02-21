<?php
/**
 * 이 파일은 iModule 게시판모듈 일부입니다. (https://www.imodule.kr)
 *
 * 게시판의 최근게시물을 가져온다.
 * 
 * @file /modules/board/widgets/board/index.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0.160910
 */
if (defined('__IM__') == false) exit;

$title = $Widget->getValue('title');
$bid = explode(',',$Widget->getValue('bid'));
$type = in_array($Widget->getValue('type'),array('post','ment')) == true ? $Widget->getValue('type') : null;
$count = $Widget->getValue('count');
$cache = $Widget->getValue('cache');

if ($type == null) return $Templet->getError('INVALID_VALUE',$Widget->getValue('type'));

if ($Widget->checkCache() < time() - $cache) {
	$lists = $me->db()->select($me->getTable($type))->where('bid',$bid,'IN')->orderBy('reg_date','desc')->limit($count)->get();
	
	for ($i=0, $loop=count($lists);$i<$loop;$i++) {
		$lists[$i] = $me->getPost($lists[$i],true);
	}
	
	$Widget->storeCache(json_encode($lists,JSON_UNESCAPED_UNICODE));
} else {
	$lists = json_decode($Widget->getCache());
}

return $Templet->getContext('index',get_defined_vars());
?>