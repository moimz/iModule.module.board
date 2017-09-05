<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 게시판 댓글 템플릿
 * 
 * @file /modules/board/templets/webzine/ment.php
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