<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 게시물 보기 템플릿
 * 
 * @file /modules/board/templets/default/view.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0.161211
 */
if (defined('__IM__') == false) exit;
?>
<article data-role="post">
	<header>
		<h5><?php echo $post->prefix != null ? '<span class="prefix" style="color:'.$post->prefix->color.';">['.$post->prefix->title.']</span> ' : ''; ?><?php echo $post->title; ?></h5>
		
		<?php echo $post->photo; ?>
		
		<ul>
			<li class="name"><b>작성자</b> <span rel="author"><?php echo $post->name; ?></span></li>
			<li class="date"><b>작성일자</b> <?php echo GetTime('Y-m-d H:i:s',$post->reg_date); ?></li>
			<li class="hit"><b>조회</b> <?php echo number_format($post->hit); ?></li>
		</ul>
	</header>
	
	<div class="content">
		<?php echo $post->content; ?>
	</div>
	
	<?php if (count($attachments) > 0) { $IM->addHeadResource('style',$IM->getModule('attachment')->getModule()->getDir().'/styles/style.css'); ?>
	<div data-module="attachment">
		<h5><i class="xi xi-clip"></i>첨부파일</h5>
		
		<ul>
			<?php for ($i=0, $loop=count($attachments);$i<$loop;$i++) { ?>
			<li>
				<i class="icon" data-type="<?php echo $attachments[$i]->type; ?>"></i>
				<a href="<?php echo $attachments[$i]->download; ?>"><span class="size">(<?php echo GetFileSize($attachments[$i]->size); ?>)</span><?php echo $attachments[$i]->name; ?></a>
			</li>
			<?php } ?>
		</ul>
	</div>
	<?php } ?>
	
	<?php echo $ment; ?>
</article>

<ul class="buttons">
	<li>
		<div data-role="button">
			<a href="<?php echo $link->list; ?>">목록보기</a>
		</div>
	</li>
	<li>
		<div data-role="button">
			<?php if ($me->checkPermission($bid,'post_modify') == true || $post->midx == $IM->getModule('member')->getLogged()) { ?><button type="button" data-action="modify">수정하기</button><?php } ?>
			<?php if ($me->checkPermission($bid,'post_delete') == true || $post->midx == $IM->getModule('member')->getLogged()) { ?><button type="button" data-action="delete" class="danger">삭제하기</button><?php } ?>
		</div>
	</li>
</ul>