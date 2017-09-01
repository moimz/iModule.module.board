<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 게시판 댓글 템플릿
 * 
 * @file /modules/board/templets/default/ment.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0.161211
 */
if (defined('__IM__') == false) exit;
?>

<h5>댓글 <?php echo $total; ?>개</h5>

<?php echo $ment; ?>

<?php echo $pagination; ?>

<?php echo $form; ?>

<?php /*
<div class="mentBlock" style="margin-left:<?php echo $ment->depth * 3; ?>%;">
	<div class="mentDepth"></div>
	<?php if ($ment->is_delete == 'TRUE') { ?>
	<div class="deleteMent"><?php echo $me->getText('error/deletedeMent'); ?></div>
	<?php } else { ?>
	<div class="mentHeader">
		<div class="photo">
			<div class="frame"><?php echo $IM->getModule('member')->getMemberPhoto($ment->midx); ?></div>
		</div>
		
		<div class="info">
			<div class="hidden-xs">
				<div class="postDetail author">
					<span class="block"><?php echo $ment->name; ?></span>
					<span class="block reg_date"><?php echo GetTime('Y.m.d H:i:s',$ment->reg_date); ?></span>
				</div>
				
				<div class="postDetail">
					<span class="level">
						<span class="text">LV.<b><?php echo $ment->member->level->level; ?></b></span>
						<span class="bar">
							<span class="percentage" style="width:<?php echo $ment->member->level->exp/$ment->member->level->next*100 < 5 ? 5 : $ment->member->level->exp/$ment->member->level->next*100; ?>%"></span>
							
							<span class="levelDetail">
								<span class="arrowBox"><?php echo number_format($ment->member->level->exp); ?>/<?php echo number_format($ment->member->level->next); ?></span>
							</span>
						</span>
					</span>
				</div>
			</div>
			
			<div class="visible-xs">
				<div class="postDetail author">
					<span class="block"><?php echo $ment->name; ?></span>
				</div>
				
				<div class="postDetail">
					<span class="block reg_date"><?php echo GetTime('Y.m.d H:i:s',$ment->reg_date); ?></span>
				</div>
			</div>
		</div>
		
		<div class="button">
			<div>
				<button type="button" class="good<?php echo $voted == 'GOOD' ? ' selected' : ''; ?>" onclick="Board.ment.vote.good(<?php echo $ment->idx; ?>,this);"><i class="fa fa-thumbs-o-up"></i> <span class="liveUpdateBoardMentGood<?php echo $ment->idx; ?>"><?php echo number_format($ment->good); ?></span></button>
				<button type="button" class="bad<?php echo $voted == 'BAD' ? ' selected' : ''; ?>" onclick="Board.ment.vote.bad(<?php echo $ment->idx; ?>,this);"><i class="fa fa-thumbs-o-down"></i> <span class="liveUpdateBoardMentBad<?php echo $ment->idx; ?>"><?php echo number_format($ment->bad); ?></span></button>
				<button type="button" class="reply" onclick="Board.ment.reply(<?php echo $ment->idx; ?>,this);" data-cancel="<i class='fa fa-times'></i> <?php echo $me->getText('button/reply_cancel'); ?>"><i class="fa fa-reply"></i> <?php echo $me->getText('button/reply'); ?></button>
				<button type="button" class="modify" onclick="Board.ment.modify(<?php echo $ment->idx; ?>,this);" data-cancel="<i class='fa fa-times'></i> <?php echo $me->getText('button/modify_cancel'); ?>"><i class="fa fa-pencil"></i> <?php echo $me->getText('button/modify'); ?></button>
				<button type="button" class="delete" onclick="Board.ment.delete(<?php echo $ment->idx; ?>,this);"><i class="fa fa-times"></i></button>
			</div>
			<div class="ip">
				<?php echo $ment->ip; ?>
			</div>
		</div>
	</div>
	
	<div class="mentContext">
		<?php echo $ment->content; ?>
		
		<?php if (count($attachments) > 0) { ?>
		<div class="blankSpace"></div>
		
		<div class="contextTitle">
			<i class="fa fa-floppy-o"></i> <?php echo $me->getText('view/attachment'); ?> <span class="count"><?php echo number_format(count($attachments)); ?></span>
		</div>
		
		<ul class="attachment">
			<?php for ($i=0, $loop=count($attachments);$i<$loop;$i++) { $fileIcon = array('image'=>'fa-file-image-o'); ?>
			<li><a href="<?php echo $attachments[$i]->download; ?>" download="<?php echo $attachments[$i]->name; ?>"><span class="filesize">(<?php echo GetFileSize($attachments[$i]->size); ?>)</span><i class="fa <?php echo empty($fileIcon[$attachments[$i]->type]) == true ? 'fa-file-o' : $fileIcon[$attachments[$i]->type]; ?>"></i> <?php echo $attachments[$i]->name; ?></a></li>
			<?php } ?>
		</ul>
		<?php } ?>
	</div>
	<?php } ?>
</div>
*/ ?>