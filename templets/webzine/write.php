<?php
if (defined('__IM__') == false) exit;

$IM->addHeadResource('script',$Templet->getDir().'/scripts/jquery.cropit.min.js');

$cover = $post != null && $post->field1 && is_numeric($post->field1) == true ? $IM->getModule('attachment')->getFileInfo($post->field1) : null;
if ($cover == null || $cover->module != 'board' || $cover->target != 'cover') $cover = null;
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
			<div data-role="input">
				<input type="text" name="title" value="<?php echo $post == null ? '' : GetString($post->title,'input'); ?>">
			</div>
		</div>
	</li>
	<li>
		<div data-role="input">
			<?php echo $wysiwyg; ?>
			<?php echo $uploader; ?>
		</div>
	</li>
	<li>
		<label>표지이미지</label>
		<div>
			<div class="cover">
				<ul data-role="webzine">
					<li>
						<div>
							<div class="preview">
								<div class="photo-editor">
									<input type="file" class="cropit-image-input">
									<div class="cropit-image-preview-container">
										<div class="cropit-image-preview">
											<div class="box">
												<label data-role="category">카테고리</label>
												
												<h4>제목</h4>
												<time data-moment="YYYY-MM-DD" data-time="<?php echo time(); ?>"></time>
											</div>
										</div>
									</div>
								
									<div class="cropit-image-zoom-container">
										<span class="cropit-image-zoom-out"><i class="fa fa-picture-o"></i></span>
										<input type="range" class="cropit-image-zoom-input">
										<span class="cropit-image-zoom-in"><i class="fa fa-picture-o"></i></span>
									</div>
								</div>
							</div>
						</div>
					</li>
				</ul>
				
				
				
				<input type="hidden" name="cover">
				<button type="button" data-action="cover"><i class="xi xi-book"></i><span>이미지 선택</span></button>
				<button type="button" data-action="cover-reset" data-code="<?php echo $cover == null ? '' : $cover->code; ?>"><i class="xi xi-trash"></i><span>이미지 초기화</span></button>
				
				<p>이미지를 선택하고 적절하게 확대/축소 및 이동하여 목록에서 보일 표지이미지를 설정할 수 있습니다.<br>표지 이미지가 설정되지 않은 경우, 단색배경으로 표시됩니다.</p>
			</div>
		</div>
	</li>
</ul>

<div data-role="button">
	<button type="submit"><?php echo $me->getText('button/write'); ?></button>
	<a href="<?php echo $me->getUrl('list',false); ?>"><?php echo $me->getText('button/cancel'); ?></a>
</div>

<script>
$(document).ready(function() {
	var $form = $("form[id!=ModuleBoardWriteForm]");
	
	$("button[data-action=cover]",$form).on("click",function() {
		$("input.cropit-image-input",$("form[id|=ModuleBoardWriteForm]")).click();
	});
	
	$("button[data-action=cover-reset]",$form).on("click",function() {
		if ($(this).attr("data-code")) {
			$.send(ENV.getProcessUrl("attachment","delete"),{code:$(this).attr("data-code")},function(result) {
				if (result.success == true) {
					$(".photo-editor",$form).cropit("resetImage");
				}
			});
		} else {
			$(".photo-editor",$form).cropit("resetImage");
		}
	});
	
	if ($("select[name=category]",$form).length > 0) {
		$("div.box > label[data-role=category]").html($("select[name=category] > option:selected",$form).text());
		$("select[name=category]",$("form[id!=ModuleBoardWriteForm]")).on("change",function() {
			$("div.box > label[data-role=category]").html($("select[name=category] > option:selected",$form).text());
		});
	}
	
	if ($("input[name=title]",$form).val()) {
		$("div.box > h4").html($("input[name=title]",$form).val());
	}
	
	$("input[name=title]",$form).on("change",function() {
		if ($("input[name=title]",$form).val()) {
			$("div.box > h4").html($("input[name=title]",$form).val());
		} else {
			$("div.box > h4").html("제목");
		}
	});
	
	
	$(".photo-editor",$("form[id!=ModuleBoardWriteForm]")).cropit({
		exportZoom:1.5,
		imageBackground:true,
		imageBackgroundBorderWidth:20,
		imageState:{
			<?php echo $cover == null ? '' : 'src:"'.$cover->path.'"'; ?>
		}
	});
	
	$("form[id!=ModuleBoardWriteForm]").on("beforesubmit",function(e,$form) {
		var cover = $(".photo-editor",$form).cropit("export");
		if (cover != null) {
			$("input[name=cover]",$form).val(cover);
		}
	});
});
</script>