<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodules.io)
 *
 * 게시판 목록을 가져온다.
 * 
 * @file /modules/board/api/posts.get.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 9. 14.
 */
if (defined('__IM__') == false) exit;

$bid = Request('bid');
$category = Request('category');
$start = Request('start') ? Request('start') : 0;
$limit = Request('limit') ? Request('limit') : 20;
$sort = Request('sort') ? Request('sort') : 'idx';
$dir = Request('dir') ? Request('dir') : 'desc';

$posts = $this->db()->select($this->table->post);
if ($bid) $posts->where('bid',$bid);
if ($category) $posts->where('category',$category);
$total = $posts->copy()->count();
$posts = $posts->orderBy($sort,$dir)->limit($start,$limit)->get();
for ($i=0, $loop=count($posts);$i<$loop;$i++) {
	/**
	 * 글 목록을 볼 수 있는 권한이 없을 경우
	 */
	if ($this->checkPermission($posts[$i]->bid,'list') == false) {
		$post = $posts[$i]->idx;
		$posts->message = $this->getErrorText('FORBIDDEN');
		$posts[$i] = $post;
		continue;
	}
	
	$posts[$i] = $this->getPost($posts[$i],true);
	unset($posts[$i]->email);
	unset($posts[$i]->name);
	
	if (isset($posts[$i]->member) == true) {
		$member = new stdClass();
		$member->idx = $posts[$i]->member->idx;
		$member->nickname = $posts[$i]->member->nickname;
		$member->exp = $posts[$i]->member->exp;
		$member->photo = $posts[$i]->member->photo;
		
		$posts[$i]->member = $member;
		$posts[$i]->nickname = $posts[$i]->is_anonymity == false ? $member->nickname : $posts[$i]->name;
		$posts[$i]->photo = $member->photo;
	} else {
		$posts[$i]->photo = null;
	}
	
	/**
	 * 글을 볼 수 있는 권한이 없을 경우
	 */
	if ($this->checkPermission($posts[$i]->bid,'view') == false || ($posts[$i]->is_secret == true && $this->checkPermission($posts[$i]->bid,'post_secret') == false)) {
		$posts[$i]->content = null;
	}
	
	if ($posts[$i]->link != '#' && preg_match('/^http(s)?:\/\//',$posts[$i]->link) == false) {
		$posts[$i]->link = $this->IM->getHost().$posts[$i]->link;
	}
	
	unset($posts[$i]->search,$posts[$i]->is_Rendered);
	if ($this->isAdmin(null,$posts[$i]->bid) == false) {
		unset($posts[$i]->ip);
	}
}

$data->success = true;
$data->start = $start;
$data->limit = $limit;
$data->total = $total;
$data->posts = $posts;
?>