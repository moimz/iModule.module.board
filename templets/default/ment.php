<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 게시판 기본템플릿 - 댓글
 * 
 * @file /modules/board/templets/default/ment.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0
 * @modified 2018. 2. 25.
 */
if (defined('__IM__') == false) exit;
?>

<h5>댓글 <?php echo $total; ?>개</h5>

<?php echo $ment; ?>

<?php echo $pagination; ?>

<?php echo $form; ?>