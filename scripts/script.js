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
	 * 게시물 목록보기
	 */
	list:{
		init:function() {
			var $form = $("#ModuleBoardListForm");
			
			$("select[name=category]",$form).on("change",function() {
				if ($(this).val() == 0) location.href = Board.getUrl("list",1);
				else location.href = Board.getUrl("list",$(this).val()+"/1");
			});
			
			$form.on("submit",function() {
				var category = $("select[name=category]",$form).length > 0 ? $("select[name=category]",$form).val() : 0;
				if (category == 0) $form.attr("action",Board.getUrl("list",1));
				else $form.attr("action",Board.getUrl("list",category+"/"+1));
				
				$("select[name=category]",$form).disable();
			});
		}
	},
	/**
	 * 게시물 보기
	 */
	view:{
		init:function() {
			var $form = $("#ModuleBoardViewForm");
			
			$("button[data-action=modify]",$form).on("click",function() {
				Board.view.modify($("input[name=idx]",$form).val());
			});
			
			$("button[data-action=delete]",$form).on("click",function() {
				Board.view.delete($("input[name=idx]",$form).val());
			});
		},
		/**
		 * 게시물 수정
		 */
		modify:function(idx) {
			$.send(ENV.getProcessUrl("board","checkPermission"),{type:"post_modify",idx:idx},function(result) {
				if (result.success == true) {
					if (result.modalHtml) {
						iModule.modal.showHtml(result.modalHtml,function($modal,$form) {
						});
					} else {
						location.href = Board.getUrl("write",idx);
					}
				}
			});
		},
		/**
		 * 게시물 삭제
		 */
		delete:function(idx) {
			$.send(ENV.getProcessUrl("board","getModal"),{modal:"delete",type:"post",idx:idx},function(result) {
				if (result.success == true) {
					iModule.modal.showHtml(result.modalHtml,function($modal,$form) {
						$form.on("submit",function() {
							$form.send(ENV.getProcessUrl("board","delete"),function(result) {
								if (result.success == true) {
									location.href = Board.getUrl("list",1);
								}
							});
							return false;
						});
						return false;
					});
				}
			});
		}
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