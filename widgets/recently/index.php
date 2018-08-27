<?php
/**
 * 이 파일은 iModule 게시판모듈 일부입니다. (https://www.imodule.kr)
 *
 * 게시판의 최근게시물을 가져온다.
 * 
 * @file /modules/board/widgets/recently/index.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 8. 26.
 */
if (defined('__IM__') == false) exit;

$title = $Widget->getValue('title');
$bid = $Widget->getValue('bid') ? $Widget->getValue('bid') : array();
$bid = is_array($bid) == true ? $bid : array($bid);
$category = $Widget->getValue('category') ? $Widget->getValue('category') : array();
$category = is_array($category) == true ? $category : array($category);

$type = in_array($Widget->getValue('type'),array('post','ment')) == true ? $Widget->getValue('type') : null;
$count = $Widget->getValue('count');
$cache = $Widget->getValue('cache');

if ($type == null) return $Templet->getError('INVALID_VALUE',$Widget->getValue('type'));

if ($Widget->checkCache() < time() - $cache) {
	$lists = $me->db()->select($me->getTable($type));
	if (count($bid) > 0) $lists->where('bid',$bid,'IN');
	if (count($category) > 0) $lists->where('category',$category,'IN');
	$lists = $lists->limit($count)->orderBy('idx','desc')->get();
	
	for ($i=0, $loop=count($lists);$i<$loop;$i++) {
		$lists[$i] = $me->getPost($lists[$i],true);
		$lists[$i]->category = $lists[$i]->category == 0 ? null : $me->getCategory($lists[$i]->category);
	}
	
	$Widget->storeCache(json_encode($lists,JSON_UNESCAPED_UNICODE));
} else {
	$lists = json_decode($Widget->getCache());
}

if (count($bid) == 1) {
	$options = count($category) == 1 ? array('category'=>$category[0]) : array();
	$more = $IM->getContextUrl('board',$bid[0],array(),$options,true);
} else {
	$more = null;
}

return $Templet->getContext('index',get_defined_vars());
?>