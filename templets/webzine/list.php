<?php
if (defined('__IM__') == false) exit;
?>
<?php if (count($categories) > 0) { ?>
<div data-role="toolbar">
	<div data-role="input">
		<select name="category">
			<option value="0"><?php echo $me->getText('text/category_all'); ?></option>
			<?php for ($i=0, $loop=count($categories);$i<$loop;$i++) { ?>
			<option value="<?php echo $categories[$i]->idx; ?>"<?php echo $category == $categories[$i]->idx ? ' selected="selected"' : ''; ?>><?php echo $categories[$i]->title; ?></option>
			<?php } ?>
		</select>
	</div>
</div>
<?php } ?>

<?php if (count($notices) > 0) { ?>
<ul data-role="table" class="black">
	<li class="thead">
		<span class="loopnum">번호</span>
		<span class="title center">제목</span>
		<span class="name">작성자</span>
		<span class="reg_date">등록일</span>
		<span class="hit">조회</span>
	</li>
	<?php foreach ($notices as $data) { ?>
	<li class="tbody">
		<span class="notice">공지</span>
		<span class="title">
			<span><?php echo $data->ment > 0 ? ('<span class="ment">['.number_format($data->ment).($data->latest_ment > time() - 60 * 60 * 24 ? '+' : '').']</span>') : ''; ?><?php echo $data->is_secret == true ? '<i class="xi xi-lock"></i>' : ''; ?><?php echo count($categories) > 0 && $data->category != null ? '<span class="category">['.$data->category->title.']</span> ' : ''; ?><a href="<?php echo $data->link; ?>"><?php echo $data->prefix != null ? '<span class="prefix" style="color:'.$data->prefix->color.';">['.$data->prefix->title.']</span> ' : ''; ?><?php echo $data->title; ?></a></span>
		</span>
		<span class="name"><?php echo $data->photo; ?><?php echo $data->name; ?></span>
		<span class="reg_date"><?php echo GetTime('Y-m-d',$data->reg_date); ?></span>
		<span class="hit"><?php echo number_format($data->hit); ?></span>
	</li>
	<?php } ?>
</ul>
<?php } ?>

<div class="list">
	<ul data-role="webzine">
		<?php foreach ($lists as $data) { ?>
		<li>
			<div>
				<div class="item"<?php echo $data->image != null ? ' data-cover="TRUE" style="background-image:url('.$data->image->path.');"' : 'data-cover="FALSE"'; ?>>
					<a href="<?php echo $data->link; ?>" class="box">
						<?php if (count($categories) > 0) { ?><label><?php echo $data->category->title; ?></label><?php } ?>
						
						<h4><?php echo $data->title; ?></h4>
						<time data-moment="YYYY-MM-DD" data-time="<?php echo $data->reg_date; ?>"></time>
					</a>
				</div>
			</div>
		</li>
		<?php } ?>
	</ul>
</div>

<div class="searchbar">
	<?php if ($me->checkPermission($bid,'post_write') == true) { ?><a href="<?php echo $link->write; ?>"><i class="xi xi-marquee-add"></i><span>게시물등록</span></a><?php } ?>
	
	<div class="search">
		<div data-role="input">
			<input type="search" name="keyword" value="<?php echo GetString($keyword,'input'); ?>">
		</div>
		<button type="submit"><i class="mi mi-search"></i></button>
	</div>
</div>

<div class="pagination">
	<?php echo $pagination; ?>
</div>