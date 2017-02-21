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
	getUrl:function(view,idx) {
		var url = $("div[data-module=board]").attr("data-base-url") ? $("div[data-module=board]").attr("data-base-url") : ENV.getUrl(null,null,false);
		if (!view || view == false) return url;
		url+= "/"+view;
		if (!idx || idx == false) return url;
		return url+"/"+idx;
	},
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
		},
		/**
		 * 게시물 저장
		 */
		submit:function($form) {
			$form.send(ENV.getProcessUrl("board","savePost"),function(result) {
				if (result.success == true) {
					location.href = Board.getUrl("view",result.idx);
				}
			});
		}
	}
};