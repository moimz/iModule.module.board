/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 게시판과 관련된 자바스크립트를 정의한다.
 * 
 * @file /modules/board/scripts/script.js
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0.161211
 */
var Board = {
	/**
	 * 게시물 작성
	 */
	write:{
		/**
		 * 작성폼 초기화
		 */
		init:function(id) {
			var $form = $("#"+id);
			
			$form.inits(Board.write.submit);
		}
	}
};