<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 게시판 기본템플릿 - 게시물 작성
 * 
 * @file /modules/board/templets/default/write.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 2. 18.
 */
if (defined('__IM__') == false) exit;
?>
<ul data-role="form" class="black inner">
	<?php if ($IM->getModule('member')->isLogged() == false) { ?>
	<li>
		<label><?php echo $me->getText('text/name'); ?></label>
		<div>
			<div data-role="input">
				<input type="text" name="name" value="<?php echo $post == null ? '' : GetString($post->name,'input'); ?>">
			</div>
		</div>
	</li>
	<li>
		<label><?php echo $me->getText('text/password'); ?></label>
		<div>
			<div data-role="input" data-default="<?php echo $me->getText('text/password_help'); ?>">
				<input type="password" name="password">
			</div>
		</div>
	</li>
	<li>
		<label><?php echo $me->getText('text/email'); ?></label>
		<div>
			<div data-role="input" data-default="<?php echo $me->getText('text/email_help'); ?>">
				<input type="email" name="email">
			</div>
		</div>
	</li>
	<?php } ?>
	<?php if (count($categories) > 0) { ?>
	<li>
		<label><?php echo $me->getText('text/category'); ?></label>
		<div>
			<div data-role="input">
				<select name="category">
					<?php for ($i=0, $loop=count($categories);$i<$loop;$i++) { ?>
					<option value="<?php echo $categories[$i]->idx; ?>"<?php echo $post != null && $post->category == $categories[$i]->idx ? ' selected="selected"' : ''; ?>><?php echo $categories[$i]->title; ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
	</li>
	<?php } ?>
	<li>
		<label><?php echo $me->getText('text/title'); ?></label>
		<div>
			<?php if (count($prefixes) == 0) { ?>
			<div data-role="input">
				<input type="text" name="title" value="<?php echo $post == null ? '' : GetString($post->title,'input'); ?>">
			</div>
			<?php } else { ?>
			<div data-role="inputset" class="flex">
				<div data-role="input">
					<select name="prefix">
						<option value="0">선택안함</option>
						<?php for ($i=0, $loop=count($prefixes);$i<$loop;$i++) { ?>
						<option value="<?php echo $prefixes[$i]->idx; ?>"<?php echo $post != null && $post->prefix == $prefixes[$i]->idx ? ' selected="selected"' : ''; ?>><?php echo $prefixes[$i]->title; ?></option>
						<?php } ?>
					</select>
				</div>
				
				<div data-role="input">
					<input type="text" name="title" value="<?php echo $post == null ? '' : GetString($post->title,'input'); ?>">
				</div>
			</div>
			<?php } ?>
		</div>
	</li>
	<li>
		<div data-role="input">
			<?php $wysiwyg->doLayout(); ?>
			<?php $uploader->doLayout(); ?>
		</div>
	</li>
	<?php if ($board->allow_secret == true || $board->allow_anonymity == true) { ?>
	<li>
		<label><?php echo $me->getText('text/post_option'); ?></label>
		<div>
			<?php if ($me->checkPermission($board->bid,'notice') == true) { ?>
			<div data-role="input">
				<label><input type="checkbox" name="is_notice" value="TRUE"><?php echo $me->getText('post_option/notice'); ?></label>
			</div>
			<?php } ?>
			
			<?php if ($board->allow_secret == true) { ?>
			<div data-role="input">
				<label><input type="checkbox" name="is_secret" value="TRUE"><?php echo $me->getText('post_option/secret'); ?></label>
			</div>
			<?php } ?>
			
			<?php if ($board->allow_anonymity == true) { ?>
			<div data-role="input">
				<label><input type="checkbox" name="is_anonymity" value="TRUE"><?php echo $me->getText('post_option/anonymity'); ?></label>
			</div>
			<?php } ?>
		</div>
	</li>
	<?php } ?>
</ul>

<div data-role="button">
	<a href="<?php echo $me->getUrl('list',false); ?>"><?php echo $me->getText('button/cancel'); ?></a>
	<button type="submit"><?php echo $me->getText('button/post_write'); ?></button>
</div>