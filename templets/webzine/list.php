<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodules.io)
 *
 * 게시판 웹진템플릿 - 목록보기
 *
 * @file /modules/board/templets/webzine/list.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 3. 11.
 */
if (defined('__IM__') == false) exit;
?>
<div data-role="toolbar">
	<?php if (count($categories) > 0) { ?>
	<div data-role="input">
		<select name="category">
			<option value="0"><?php echo $me->getText('text/category_all'); ?></option>
			<?php for ($i=0, $loop=count($categories);$i<$loop;$i++) { ?>
			<option value="<?php echo $categories[$i]->idx; ?>"<?php echo $category == $categories[$i]->idx ? ' selected="selected"' : ''; ?>><?php echo $categories[$i]->title; ?></option>
			<?php } ?>
		</select>
	</div>
	<?php } ?>

	<div data-role="search">
		<?php if ($board->allow_search_detail) { ?>
		<div data-role="input">
			<select name="search_type">
				<?php foreach ($me->getText('search') as $key => $value) { ?>
					<option value="<?php echo $key; ?>"<?php echo $key == $search_type ? ' selected="selected"' : ''; ?>><?php echo $value; ?></option>
				<?php } ?>
			</select>
		</div>
		<?php } ?>
		<div data-role="input">
			<input type="search" name="keyword" value="<?php echo GetString($keyword,'input'); ?>">
		</div>
		<button type="submit"><i class="mi mi-search"></i></button>
	</div>

	<a href="<?php echo $link->write; ?>"><i class="xi xi-pen"></i><span>게시물등록</span></a>
</div>

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
	<li class="tbody notice">
		<span class="loopnum">공지</span>
		<span class="title">
			<a href="<?php echo $data->link; ?>"><?php echo $data->ment > 0 ? ('<span class="ment">'.number_format($data->ment).($data->latest_ment > time() - 60 * 60 * 24 ? '+' : '').'</span>') : ''; ?><?php echo $data->is_secret == true ? '<i class="xi xi-lock"></i>' : ''; ?><?php echo count($categories) > 0 && $data->category != null ? '<span class="category">['.$data->category->title.']</span> ' : ''; ?><?php echo $data->prefix != null ? '<span class="prefix" style="color:'.$data->prefix->color.';">['.$data->prefix->title.']</span> ' : ''; ?><?php echo $data->title; ?></a>
		</span>
		<span class="name"><?php echo $data->photo; ?><?php echo $data->name; ?></span>
		<span class="reg_date"><?php echo GetTime('Y-m-d',$data->reg_date); ?></span>
		<span class="hit"><?php echo number_format($data->hit); ?></span>
	</li>
	<?php } ?>
</ul>
<?php } ?>

<?php if (count($lists) == 0) { ?>
<div class="empty">
	게시물이 없습니다.
</div>
<?php } else { ?>
<div class="list">
	<ul data-role="webzine">
		<?php foreach ($lists as $data) { ?>
		<li>
			<div>
				<div class="item" style="background-image:url(<?php echo $data->image != null ? $data->image->path : $Templet->getDir().'/images/pattern'.($data->idx % 4 + 1).'.png'; ?>);">
					<a href="<?php echo $data->link; ?>" class="box">
						<?php if ($data->category != null) { ?><label><?php echo $data->category->title; ?></label><?php } ?>

						<div class="title">
							<h4><?php echo $data->title; ?></h4>
							<time data-moment="YYYY-MM-DD" data-time="<?php echo $data->reg_date; ?>"></time>
						</div>
					</a>
				</div>
			</div>
		</li>
		<?php } ?>
	</ul>
</div>
<?php } ?>

<div data-role="searchbar">
	<div data-role="search">
		<div data-role="input">
			<input type="search" name="keyword" value="<?php echo GetString($keyword,'input'); ?>">
		</div>
		<button type="submit"><i class="mi mi-search"></i></button>
	</div>

	<a href="<?php echo $link->write; ?>"><i class="xi xi-pen"></i><span>게시물등록</span></a>
</div>

<div class="pagination">
	<?php echo $pagination; ?>
</div>