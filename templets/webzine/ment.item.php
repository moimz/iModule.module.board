<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 게시판 댓글 템플릿
 * 
 * @file /modules/board/templets/webzine/ment.item.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0.161211
 */
if (defined('__IM__') == false) exit;
?>
<i class="xi xi-reply-all xi-rotate-180"></i>

<?php if ($ment->is_delete == true) { ?>
<div data-delete="TRUE"><?php echo $me->getText('ment/delete'); ?></div>
<?php } else { ?>

<?php if ($ment->is_secret == true) { ?><i class="xi xi-lock"></i><?php } ?>
<?php echo $ment->photo; ?>

<div>
	<div class="info">
		<?php echo $ment->name; ?><?php echo GetTime('Y-m-d H:i:s',$ment->reg_date); ?>
		
		<button type="button" data-ment-action="action"><i class="fa fa-caret-down"></i></button>
		<ul>
			<?php if ($me->checkPermission($ment->bid,'ment_write') == true) { ?><li><button type="button" data-ment-action="reply"><i class="xi xi-reply-all xi-rotate-180"></i>답변</button></li><?php } ?>
			<?php if ($me->checkPermission($ment->bid,'ment_modify') == true || $ment->midx == $IM->getModule('member')->getLogged()) { ?><li><button type="button" data-ment-action="modify"><i class="xi xi-pen"></i>수정</button></li><?php } ?>
			<?php if ($me->checkPermission($ment->bid,'ment_delete') == true || $ment->midx == $IM->getModule('member')->getLogged()) { ?><li><button type="button" data-ment-action="delete"><i class="xi xi-trash"></i>삭제</button></li><?php } ?>
		</ul>
	</div>
	
	<?php echo $ment->content; ?>
	
	<?php if (count($attachments) > 0) { $IM->addHeadResource('style',$IM->getModule('attachment')->getModule()->getDir().'/styles/style.css'); ?>
	<div data-module="attachment">
		<h5><i class="xi xi-clip"></i>첨부파일</h5>
		
		<ul>
			<?php for ($i=0, $loop=count($attachments);$i<$loop;$i++) { ?>
			<li>
				<i class="icon" data-type="<?php echo $attachments[$i]->type; ?>"></i>
				<a href="<?php echo $attachments[$i]->download; ?>"><span class="size">(<?php echo GetFileSize($attachments[$i]->size); ?>)</span><?php echo $attachments[$i]->name; ?></a>
			</li>
			<?php } ?>
		</ul>
	</div>
	<?php } ?>
</div>
<?php } ?>