<?php
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
			<?php echo $wysiwyg; ?>
			<?php echo $uploader; ?>
		</div>
	</li>
</ul>

<div data-role="button">
	<button type="submit"><?php echo $me->getText('button/write'); ?></button>
	<a href="<?php echo $me->getUrl('list',false); ?>"><?php echo $me->getText('button/cancel'); ?></a>
</div>