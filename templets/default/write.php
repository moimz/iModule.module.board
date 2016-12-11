<?php
if (defined('__IM__') == false) exit;
?>
<ul data-role="form" class="black inner">
	<?php if (true || $IM->getModule('member')->getLogged() == false) { ?>
	<li>
		<label>작성자</label>
		<div>
			<div data-role="input">
				<input type="text" name="name" value="<?php echo $post == null ? '' : GetString($post->title,'input'); ?>">
			</div>
		</div>
	</li>
	<li>
		<label>패스워드</label>
		<div>
			<div data-role="input" data-default="게시물 수정/삭제시 필요합니다.">
				<input type="password" name="password" value="<?php echo $post == null ? '' : GetString($post->title,'input'); ?>">
			</div>
		</div>
	</li>
	<li>
		<label>이메일</label>
		<div>
			<div data-role="input" data-default="댓글알림을 받기 위한 이메일 주소를 입력하여 주십시오.">
				<input type="password" name="password" value="<?php echo $post == null ? '' : GetString($post->title,'input'); ?>">
			</div>
		</div>
	</li>
	<?php } ?>
	<li>
		<label>제목</label>
		<div>
			<div data-role="input">
				<input type="text" name="title" value="<?php echo $post == null ? '' : GetString($post->title,'input'); ?>">
			</div>
		</div>
	</li>
	<li>
		<div data-role="wywiwyg">
			
		</div>
	</li>
</ul>