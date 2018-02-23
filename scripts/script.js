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
			
			$("input[name=keyword]",$form).on("change",function() {
				$("input[name=keyword]",$form).val($(this).val());
			});
			
			$form.on("submit",function() {
				var category = $("select[name=category]",$form).length > 0 ? $("select[name=category]",$form).val() : 0;
				if (category == 0) $form.attr("action",Board.getUrl("list",1));
				else $form.attr("action",Board.getUrl("list",category+"/"+1));
				
				$("input[name=keyword]",$form).disable();
				$("input[name=keyword]",$form).first().enable();
				
				$("select[name=category]",$form).disable();
			});
			
			$("a",$form).on("click",function(e) {
				var link = document.createElement("a");
				link.href = $(this).attr("href");
				
				if (link.hash.indexOf("#secret-") == 0) {
					Board.view.secret(link.hash.replace("#secret-",""),link.search);
					e.preventDefault();
				}
			});
		}
	},
	/**
	 * 게시물 보기
	 */
	view:{
		init:function() {
			var $context = $("#ModuleBoardView");
			
			$("button[data-action=modify]",$context).on("click",function() {
				Board.view.modify($context.attr("data-idx"));
			});
			
			$("button[data-action=delete]",$context).on("click",function() {
				Board.view.delete($context.attr("data-idx"));
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
							$form.on("submit",function() {
								$form.send(ENV.getProcessUrl("board","checkPermission"),function(result) {
									if (result.success == true) {
										$("input[name=password]",$form).enable();
										$form.attr("action",Board.getUrl("write",result.idx));
										$form.attr("method","post");
										$form.off("submit");
										$form.submit();
									}
								});
								return false;
							});
							return false;
						});
					} else {
						location.href = Board.getUrl("write",idx);
					}
				}
			});
		},
		/**
		 * 게시물 수정
		 */
		secret:function(idx,query) {
			$.send(ENV.getProcessUrl("board","checkPermission"),{type:"post_secret",idx:idx},function(result) {
				if (result.success == true) {
					if (result.modalHtml) {
						iModule.modal.showHtml(result.modalHtml,function($modal,$form) {
							$form.on("submit",function() {
								$form.send(ENV.getProcessUrl("board","checkPermission"),function(result) {
									if (result.success == true) {
										$("input[name=password]",$form).enable();
										$form.attr("action",Board.getUrl("view",result.idx) + (query ? query : ""));
										$form.attr("method","post");
										$form.off("submit");
										$form.submit();
									}
								});
								return false;
							});
							return false;
						});
					} else {
						location.href = Board.getUrl("view",idx);
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
	},
	/**
	 * 댓글
	 */
	ment:{
		/**
		 * 댓글 초기화
		 */
		init:function(id) {
			if (typeof id == "string") {
				var $form = $("#"+id);
				
				if (id.search(/ModuleBoardMentForm-/) == 0) {
					$form.inits(Board.ment.submit);
				}
			} else if (typeof id == "number") {
				Board.ment.init($("div[data-module=board] div[data-role=ment][data-parent="+id+"] div[data-role=list]"));
				Board.ment.init($("div[data-module=board] div[data-role=ment][data-parent="+id+"] div[data-role=pagination]"));
			} else {
				var $container = id;
				
				if ($container.attr("data-role") == "list" || $container.attr("data-role") == "item") {
					$("button[data-ment-action]",$container).on("click",function(e) {
						var action = $(this).attr("data-ment-action");
						var $parent = $(this).parents("div[data-role=item]");
						var parent = $parent.attr("data-parent");
						
						if (action == "action") {
							$(this).toggleClass("opened");
							e.stopPropagation();
							return;
						}
						
						if ($parent.attr("data-ment-action") == action) {
							Board.ment.reset(parent);
							return;
						}
						
						if (action == "reply") {
							var source = $parent.attr("data-idx");
							
							Board.ment.reset(parent,source);
							var $form = $("#ModuleBoardMentForm-"+parent);
							$parent.attr("data-ment-action","reply");
							$("input[name=source]",$form).val(source);
						}
						
						if (action == "modify") {
							var idx = $parent.attr("data-idx");
							
							$.send(ENV.getProcessUrl("board","checkPermission"),{type:"ment_modify",idx:idx},function(result) {
								if (result.success == true) {
									if (result.modalHtml) {
										iModule.modal.showHtml(result.modalHtml,function($modal,$form) {
											$form.on("submit",function() {
												$form.send(ENV.getProcessUrl("board","checkPermission"),function(result) {
													if (result.success == true) {
														Board.ment.get(idx,"modify",$("input[name=password]",$form).val());
														iModule.modal.close();
													}
												});
												return false;
											});
											return false;
										});
									} else {
										Board.ment.get(idx,"modify");
									}
								}
							});
						}
						
						if (action == "delete") {
							var idx = $parent.attr("data-idx");
							Board.ment.delete(idx);
						}
					});
					
					$("div[data-secret=TRUE][data-password=TRUE]",$container).on("click",function(e) {
						var $parent = $(this).parents("div[data-role=item]");
						var idx = $parent.attr("data-idx");
						
						$.send(ENV.getProcessUrl("board","checkPermission"),{type:"ment_secret",idx:idx},function(result) {
							if (result.success == true) {
								if (result.modalHtml) {
									iModule.modal.showHtml(result.modalHtml,function($modal,$form) {
										$form.on("submit",function() {
											$form.send(ENV.getProcessUrl("board","checkPermission"),function(result) {
												if (result.success == true) {
													Board.ment.get(idx,"view",$("input[name=password]",$form).val());
													iModule.modal.close();
												}
											});
											return false;
										});
										return false;
									});
								} else {
									Board.ment.get(idx,"view");
								}
							}
						});
					});
				} else if ($container.attr("data-role") == "pagination") {
					$("a",$container).on("click",function(e) {
						var parent = $container.parents("div[data-role=ment]").attr("data-parent");
						var page = $(this).attr("data-page");
						if (page != $container.attr("data-page")) {
							Board.ment.page(parent,page,function(result) {
								var $container = $("div[data-role=ment][data-parent="+result.parent+"]",$("div[data-module=board]"));
								$container.children().eq(0).scroll();
							});
						}
						e.preventDefault();
					});
				}
			}
		},
		page:function(parent,page,callback) {
			iModule.disable(true);
			$.send(ENV.getProcessUrl("board","getMents"),{parent:parent,page:page},function(result) {
				if (result.success == true) {
					var $container = $("div[data-role=ment][data-parent="+result.parent+"]",$("div[data-module=board]"));
					var $lists = $(result.lists);
					var $pagination = $(result.pagination);
					
					$("span[data-role=count]",$container).html(result.total);
					$("div[data-role=list]",$container).replaceWith($lists);
					Board.ment.init($lists);
					
					$("div[data-role=pagination]",$container).replaceWith($pagination);
					Board.ment.init($pagination);
					
					if (typeof callback == "function") callback(result);
				}
				iModule.enable();
			});
		},
		reset:function(parent,position) {
			iModule.disable(true);
			var $form = $("#ModuleBoardMentForm-"+parent);
			var $container = $("div[data-module=board] div[data-role=ment][data-parent="+parent+"]");
			
			$("div[data-role=item]",$container).attr("data-ment-action","");
			$("input[name=idx]",$form).val("");
			$("input[name=source]",$form).val("");
			
			$("input[name=name]",$form).val("");
			$("input[name=password]",$form).val("");
			$("input[name=email]",$form).val("");
			
			$("input[name=is_secret]",$form).prop("checked",false);
			$("input[name=is_anonymity]",$form).prop("checked",false);
			$("textarea[name=content]",$form).val("");
			
			$("textarea[name=content]",$form).froalaEditor("destroy");
			var $clone = $form.clone(true);
			$form.remove();
			
			var $position = position ? $("div[data-idx="+position+"]",$container) : $("#ModuleBoardMentWrite-"+parent);
			$position.append($clone);
			
			$("textarea[name=content]",$clone).val("");
			$("textarea[name=content]",$clone).wysiwyg(true);
			$("textarea[name=content]",$clone).froalaEditor("html.set","");
			
			var $attachment = $("div[data-module=attachment]",$clone);
			Attachment.reset($attachment.attr("id"));
			
			$clone.status("default");
			iModule.enable();
		},
		get:function(idx,type,password) {
			iModule.disable(true);
			$.send(ENV.getProcessUrl("board","getMent"),{idx:idx,type:type,password:password},function(result) {
				if (result.success == true) {
					var $container = $("div[data-module=board] div[data-role=ment][data-parent="+result.data.parent+"]");
					var $item = $("div[data-role=item][data-idx="+result.data.idx+"]",$container);
					
					if (type == "modify") {
						Board.ment.reset(result.data.parent,result.data.idx);
						
						var $form = $("#ModuleBoardMentForm-"+result.data.parent);
						
						$item.attr("data-ment-action","modify");
						
						$("input[name=idx]",$form).val(result.data.idx);
						$("input[name=name]",$form).val(result.data.name);
						$("input[name=email]",$form).val(result.data.email);
						
						$("input[name=is_secret]").prop("checked",result.data.is_secret);
						$("input[name=is_anonymity]").prop("checked",result.data.is_anonymity);
						
						$("textarea[name=content]",$form).val(result.data.content);
						$("textarea[name=content]",$form).froalaEditor("html.set",result.data.content);
						
						var $attachment = $("div[data-module=attachment]",$form);
						Attachment.load($attachment.attr("id"),result.data.files);
						
						$item.scroll();
					} else {
						$item.replaceWith(result.item);
						$item = $("div[data-role=item][data-idx="+result.data.idx+"]",$container);
						Board.ment.init($item);
						$item.scroll();
					}
				}
				
				iModule.enable();
			});
		},
		/**
		 * 댓글 저장
		 */
		submit:function($form) {
			$form.send(ENV.getProcessUrl("board","saveMent"),function(result) {
				if (result.success == true) {
					var idx = result.idx;
					
					if ($("input[name=idx]",$form).val() == idx) {
						Board.ment.reset(result.parent);
						Board.ment.get(result.idx,"view");
					} else {
						Board.ment.reset(result.parent);
						Board.ment.page(result.parent,result.page,function(result) {
							var $container = $("div[data-role=ment][data-parent="+result.parent+"]",$("div[data-module=board]"));
							$("div[data-role=item][data-idx="+idx+"]",$container).scroll();
						});
					}
				}
			});
		},
		/**
		 * 댓글 삭제
		 */
		delete:function(idx) {
			$.send(ENV.getProcessUrl("board","getModal"),{modal:"delete",type:"ment",idx:idx},function(result) {
				if (result.success == true) {
					iModule.modal.showHtml(result.modalHtml,function($modal,$form) {
						$form.on("submit",function() {
							$form.send(ENV.getProcessUrl("board","delete"),function(result) {
								if (result.success == true) {
									var $container = $("div[data-role=ment][data-parent="+result.parent+"]",$("div[data-module=board]"));
									var $pagination = $("div[data-role=pagination]",$container);
									
									Board.ment.page(result.parent,$pagination.attr("data-page"),function(result) {
										var $container = $("div[data-role=ment][data-parent="+result.parent+"]",$("div[data-module=board]"));
										$("div[data-role=item][data-idx="+idx+"]",$container).scroll();
									});
								}
							});
							return false;
						});
						return false;
					});
				}
			});
		}
	}
};

$(document).ready(function() {
	$(document).on("click",function() {
		$("button[data-ment-action=action]",$("div[data-module=board]")).removeClass("opened");
	});
})