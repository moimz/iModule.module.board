<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 게시판 기본템플릿 - 게시물보기
 * 
 * @file /modules/board/templets/default/view.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 3. 10.
 */
if (defined('__IM__') == false) exit;
?>
<article data-role="post">
	<header>
		<h5><?php echo $post->prefix != null ? '<span class="prefix" style="color:'.$post->prefix->color.';">['.$post->prefix->title.']</span> ' : ''; ?><?php echo $post->title; ?></h5>
		
		<?php echo $post->photo; ?>
		
		<ul>
			<li class="name"><b>작성자</b><i class="xi xi-user"></i><span rel="author"><?php echo $post->name; ?></span></li>
			<li class="date"><b>작성일자</b><i class="xi xi-time"></i><?php echo GetTime('Y-m-d H:i:s',$post->reg_date); ?></li>
			<li class="hit"><b>조회</b><i class="xi xi-eye"></i><?php echo number_format($post->hit); ?></li>
		</ul>
	</header>
	
	<div class="content">
		<?php echo $post->content; ?>
	</div>
	
	<?php if (count($attachments) > 0) { ?>
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
	
	<div data-role="button">
		<div class="author">
			<?php echo $post->photo; ?>
			<?php echo $post->name; ?>
			<div class="level">
				<div class="level">LV.<b><?php echo $post->member->level->level; ?></b></div>
				<div class="progress">
					<div class="on" style="width:<?php echo sprintf('%0.2f',$post->member->level->exp / $post->member->level->next * 100); ?>%;"></div>
				</div>
			</div>
		</div>
		
		<?php if ($permission->modify == true || $permission->delete == true) { ?>
		<button type="button" data-action="action" data-type="post" data-idx="<?php echo $post->idx; ?>"><i class="fa fa-caret-down"></i></button>
		<ul data-role="action" data-type="post" data-idx="<?php echo $post->idx; ?>">
			<?php if ($permission->modify == true) { ?>
			<li><button type="button" data-action="modify" data-type="post" data-idx="<?php echo $post->idx; ?>">수정하기</button></li>
			<?php } ?>
			
			<?php if ($permission->delete == true) { ?>
			<li><button type="button" data-action="delete" data-type="post" data-idx="<?php echo $post->idx; ?>">삭제하기</button></li>
			<?php } ?>
		</ul>
		<?php } ?>
	</div>
	
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
			<?php if ($permission->modify == true) { ?><button type="button" data-action="modify" data-type="post" data-idx="<?php echo $post->idx; ?>">수정하기</button><?php } ?>
			<?php if ($permission->delete == true) { ?><button type="button" data-action="delete" data-type="post" data-idx="<?php echo $post->idx; ?>" class="danger">삭제하기</button><?php } ?>
		</div>
	</li>
</ul>