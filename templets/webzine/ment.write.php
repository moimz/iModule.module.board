<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 게시판 웹진템플릿 - 댓글작성
 * 
 * @file /modules/board/templets/webzine/ment.write.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 2. 25.
 */
if (defined('__IM__') == false) exit;
?>
<?php if ($IM->getModule('member')->isLogged() == false) { ?>
<ul data-role="form" class="black inner">
	<li>
		<label><?php echo $me->getText('text/name'); ?></label>
		<div>
			<div data-role="input">
				<input type="text" name="name" value="">
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
</ul>
<?php } ?>
<ul data-role="form" class="inner">
	<li>
		<div data-role="input">
			<?php echo $wysiwyg; ?>
			<?php echo $uploader; ?>
		</div>
	</li>
	<?php if ($board->allow_secret == true || $board->allow_anonymity == true) { ?>
	<li>
		<label><?php echo $me->getText('text/ment_option'); ?></label>
		<div>
			<?php if ($board->allow_secret == true) { ?>
			<div data-role="input">
				<label><input type="checkbox" name="is_secret" value="TRUE"><?php echo $me->getText('ment_option/secret'); ?></label>
			</div>
			<?php } ?>
			
			<?php if ($board->allow_anonymity == true) { ?>
			<div data-role="input">
				<label><input type="checkbox" name="is_anonymity" value="TRUE"><?php echo $me->getText('ment_option/anonymity'); ?></label>
			</div>
			<?php } ?>
		</div>
	</li>
	<?php } ?>
</ul>

<div data-role="button">
	<button type="submit"><?php echo $me->getText('button/ment_write'); ?></button>
</div>