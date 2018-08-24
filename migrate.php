<?php
REQUIRE_ONCE '../../configs/init.config.php';

$IM = new iModule();
$mBoard = $IM->getModule('board');

$posts = $mBoard->db()->select($mBoard->getTable('post'),'idx')->get('idx');
foreach ($posts as $post) {
	$mBoard->updatePost($post,true);
	echo $post.'<br>';
	ForceFlush();
}
?>