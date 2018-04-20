<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 게시판 기본템플릿 - 댓글보기
 * 
 * @file /modules/board/templets/default/ment.item.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 3. 11.
 */
if (defined('__IM__') == false) exit;
?>
<i class="xi xi-reply-all xi-rotate-180"></i>

<?php if ($ment->is_delete == true) { ?>
<div data-delete="TRUE"><?php echo $me->getText('ment/delete'); ?></div>
<?php } else { ?>

<div class="topbar">
	<?php echo $ment->photo; ?>
	<?php echo $ment->nickname; ?>
	
	<?php if ($permission->write == true || $permission->modify == true || $permission->delete == true) { ?>
	<button type="button" data-action="action" data-type="ment" data-idx="<?php echo $ment->idx; ?>"><i class="fa fa-caret-down"></i></button>
	<ul data-role="action" data-type="ment" data-idx="<?php echo $ment->idx; ?>">
		<?php if ($permission->write == true) { ?><li><button type="button" data-action="reply" data-type="ment" data-idx="<?php echo $ment->idx; ?>"><i class="xi xi-reply-all xi-rotate-180"></i>답변</button></li><?php } ?>
		<?php if ($permission->modify == true) { ?><li><button type="button" data-action="modify" data-type="ment" data-idx="<?php echo $ment->idx; ?>"><i class="xi xi-pen"></i>수정</button></li><?php } ?>
		<?php if ($permission->delete == true) { ?><li><button type="button" data-action="delete" data-type="ment" data-idx="<?php echo $ment->idx; ?>"><i class="xi xi-trash"></i>삭제</button></li><?php } ?>
	</ul>
	<?php } ?>
</div>

<div class="content">
	<?php echo $ment->is_secret == true ? '<i class="mi mi-lock"></i>' : ''; ?>
	<?php echo $ment->content; ?>
	
	<?php if (count($attachments) > 0) { ?>
	<div data-module="attachment">
		<h5><i class="xi xi-clip"></i>첨부파일</h5>
		
		<ul>
			<?php for ($i=0, $loop=count($attachments);$i<$loop;$i++) { ?>
			<li>
				<i class="icon" data-type="<?php echo $attachments[$i]->type; ?>"></i>
				<a href="<?php echo $attachments[$i]->download; ?>"><span class="size">(<?php echo GetFileSize($attachments[$i]->size); ?>)</span><?php echo $attachments[$i]->nickname; ?></a>
			</li>
			<?php } ?>
		</ul>
	</div>
	<?php } ?>
</div>

<div class="footbar">
	<?php echo GetTime('Y-m-d H:i:s',$ment->reg_date); ?>
</div>
<?php } ?>