<?php
if (defined('__IM__') == false) exit;
?>
<ul data-role="table" class="black">
	<li class="thead">
		<span class="loopnum">번호</span>
		<span class="title center">제목</span>
		<span class="name">작성자</span>
		<span class="reg_date">등록일</span>
		<span class="hit">조회</span>
	</li>
	<?php foreach ($lists as $data) { ?>
	<li class="tbody">
		<span class="loopnum"><?php echo $idx == $data->idx ? '<i class="fa fa-caret-right"></i>' : $data->loopnum; ?></span>
		<span class="title"><span><a href="<?php echo $data->link; ?>"><?php echo $data->title; ?></a></span></span>
		<span class="name"><?php echo $data->name; ?></span>
		<span class="reg_date"><?php echo GetTime('Y-m-d',$data->reg_date); ?></span>
		<span class="hit"><?php echo number_format($data->hit); ?></span>
	</li>
	<?php } ?>
</ul>

<div class="searchbar">
	<a href="<?php echo $link->write; ?>"><i class="xi xi-marquee-add"></i><span>게시물등록</span></a>
	
	<div class="search">
		<div data-role="input">
			<input type="search" name="keyword">
		</div>
		<button type="submit"><i class="mi mi-search"></i></button>
	</div>
</div>

<div class="pagination">
	<?php echo $pagination; ?>
</div>