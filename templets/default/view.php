<?php
if (defined('__IM__') == false) exit;
?>
<article data-role="post">
	<header>
		<h5><?php echo $post->title; ?></h5>
		
		<ul>
			<li class="name"><b>작성자</b> <span rel="author"><?php echo $post->name; ?></span></li>
			<li class="date"><b>작성일자</b> <?php echo GetTime('Y-m-d H:i:s',$post->reg_date); ?></li>
			<li class="hit"><b>조회</b> <?php echo number_format($post->hit); ?></li>
		</ul>
	</header>
	
	<div class="content">
		<?php echo $post->content; ?>
	</div>
</article>

<ul class="buttons">
	<li>
		<div data-role="button">
			<a href="<?php echo $link->list; ?>">목록보기</a>
		</div>
	</li>
	<li>
		<div data-role="button">
			<button type="button" data-action="modify">수정하기</button>
			<button type="button" data-action="delete" class="danger">삭제하기</button>
		</div>
	</li>
</ul>

<script>AddDepth("<?php echo GetString($post->title,'input'); ?>","#");</script>