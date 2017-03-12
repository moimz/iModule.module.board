/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodule.kr)
 *
 * 게시판과 관련된 모든 기능을 제어한다.
 * 
 * @file /modules/board/ModuleBoard.class.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.0.0.161211
 */
var Board = {
	/**
	 * 게시판 목록관리
	 */
	list:{
		/**
		 * 게시판 추가/삭제
		 *
		 * @param string bid 게시판아이디 (없을 경우 추가)
		 */
		add:function(bid) {
			new Ext.Window({
				id:"ModuleBoardAddBoardWindow",
				title:(bid ? Board.getText("admin/list/window/modify") : Board.getText("admin/list/window/add")),
				modal:true,
				width:750,
				border:false,
				autoScroll:true,
				items:[
					new Ext.form.Panel({
						id:"ModuleBoardAddBoardForm",
						border:false,
						bodyPadding:"10 10 0 10",
						fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:false},
						items:[
							new Ext.form.Hidden({
								name:"mode",
								value:(bid ? "modify" : "add")
							}),
							new Ext.form.FieldSet({
								collapsible:true,
								collapsed:false,
								title:Board.getText("admin/list/form/default_setting"),
								items:[
									new Ext.form.TextField({
										fieldLabel:Board.getText("admin/list/form/bid"),
										name:"bid",
										maxLength:20,
										readOnly:bid ? true : false
									}),
									new Ext.form.TextField({
										fieldLabel:Board.getText("admin/list/form/title"),
										name:"title",
										maxLength:50
									})
								]
							}),
							new Ext.form.FieldSet({
								collapsible:true,
								collapsed:false,
								title:Board.getText("admin/list/form/designSetting"),
								items:[
									Admin.templetField(Board.getText("admin/list/form/templet"),"templet","board",false,ENV.getProcessUrl("board","@getTempletConfigs"),["bid"]),
									new Ext.form.FieldContainer({
										layout:"hbox",
										items:[
											new Ext.form.NumberField({
												fieldLabel:Board.getText("admin/list/form/postlimit"),
												name:"postlimit",
												value:20,
												flex:1
											}),
											new Ext.form.NumberField({
												fieldLabel:Board.getText("admin/list/form/mentlimit"),
												name:"mentlimit",
												value:50,
												flex:1
											})
										]
									}),
									new Ext.form.FieldContainer({
										layout:"hbox",
										items:[
											new Ext.form.ComboBox({
												fieldLabel:Board.getText("admin/list/form/pagetype"),
												name:"pagetype",
												store:new Ext.data.ArrayStore({
													fields:["display","value"],
													data:[[Board.getText("admin/list/pagetype/FIXED"),"FIXED"],[Board.getText("admin/list/pagetype/CENTER"),"CENTER"]]
												}),
												editable:false,
												displayField:"display",
												valueField:"value",
												value:"FIXED",
												flex:1,
												listeners:{
													change:function(form,value) {
														var form = Ext.getCmp("ModuleBoardAddBoardForm").getForm();
														if (value == "CENTER" && form.findField("pagelimit").getValue() % 2 == 0) {
															form.findField("pagelimit").setValue(form.findField("pagelimit").getValue() + 1);
															
															Ext.Msg.show({title:Admin.getText("alert/info"),msg:Board.getText("admin/list/pagetype/help"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
														}
													}
												}
											}),
											new Ext.form.NumberField({
												fieldLabel:Board.getText("admin/list/form/pagelimit"),
												name:"pagelimit",
												value:10,
												flex:1
											})
										]
									})
								]
							}),
							new Ext.form.FieldSet({
								title:Board.getText("admin/list/form/attachment_setting"),
								checkboxName:"use_attachment",
								checkboxToggle:true,
								collapsed:false,
								items:[
									Admin.templetField(Board.getText("admin/list/form/attachment_templet"),"attachment","attachment",Board.getText("admin/list/form/attachment_templet_default"),ENV.getProcessUrl("board","@getTempletConfigs"),["bid"]),
								]
							}),
							new Ext.form.FieldSet({
								title:Board.getText("admin/list/form/notice_setting"),
								collapsible:true,
								collapsed:true,
								items:[
									new Ext.form.ComboBox({
										name:"view_notice_page",
										store:new Ext.data.ArrayStore({
											fields:["display","value"],
											data:[[Board.getText("admin/list/noticetype/FIRST"),"FIRST"],[Board.getText("admin/list/noticetype/ALL"),"ALL"]]
										}),
										editable:false,
										displayField:"display",
										valueField:"value",
										value:"FIRST",
										flex:1,
										listeners:{
											change:function(form,value) {
												var form = Ext.getCmp("ModuleBoardAddBoardForm").getForm();
												if (form.findField("view_notice_page").getValue() == "ALL" && form.findField("view_notice_count").getValue() == "INCLUDE") {
													Ext.Msg.show({title:Admin.getText("alert/info"),msg:Board.getText("admin/list/noticetype/help"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
												}
											}
										}
									}),
									new Ext.form.ComboBox({
										name:"view_notice_count",
										store:new Ext.data.ArrayStore({
											fields:["display","value"],
											data:[[Board.getText("admin/list/noticetype/INCLUDE"),"INCLUDE"],[Board.getText("admin/list/noticetype/EXCLUDE"),"EXCLUDE"]]
										}),
										editable:false,
										displayField:"display",
										valueField:"value",
										value:"INCLUDE",
										flex:1,
										listeners:{
											change:function(form,value) {
												var form = Ext.getCmp("ModuleBoardAddBoardForm").getForm();
												if (form.findField("view_notice_page").getValue() == "ALL" && form.findField("view_notice_count").getValue() == "INCLUDE") {
													Ext.Msg.show({title:Admin.getText("alert/info"),msg:Board.getText("admin/list/noticetype/help"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
												}
											}
										}
									})
								]
							}),
							new Ext.form.Hidden({
								name:"category"
							}),
							new Ext.form.FieldSet({
								title:Board.getText("admin/list/form/category_setting"),
								checkboxName:"use_category",
								checkboxToggle:true,
								collapsed:true,
								items:[
									new Ext.grid.Panel({
										id:"ModuleBoardCategoryList",
										border:true,
										tbar:[
											new Ext.Button({
												text:"카테고리추가",
												handler:function() {
													Board.category.add();
												}
											})
										],
										store:new Ext.data.ArrayStore({
											fields:["idx","title","permission","post",{name:"sort",type:"int"}],
											sorters:[{property:"sort",direction:"ASC"}],
											data:[]
										}),
										flex:1,
										height:300,
										columns:[{
											text:"카테고리명",
											dataIndex:"title",
											flex:1
										},{
											text:"게시물수",
											dataIndex:"post",
											width:100,
											align:"right",
											renderer:function(value) {
												return Ext.util.Format.number(value,"0,000")+"개";
											}
										},{
											text:"권한설정",
											dataIndex:"permission",
											width:150,
											renderer:function(value) {
												if (value == null) return "게시판 권한설정 사용";
												else return "카테고리 개별권한 사용"
											}
										}],
										bbar:[
											new Ext.Button({
												iconCls:"fa fa-caret-up",
												handler:function() {
													Admin.gridSort(Ext.getCmp("ModuleBoardCategoryList"),"sort","up");
												}
											}),
											new Ext.Button({
												iconCls:"fa fa-caret-down",
												handler:function() {
													Admin.gridSort(Ext.getCmp("ModuleBoardCategoryList"),"sort","down");
												}
											})
										],
										selModel:new Ext.selection.CheckboxModel()
									})
								]
							}),
							new Ext.form.FieldSet({
								title:Board.getText("admin/list/form/permission_setting"),
								collapsible:true,
								collapsed:true,
								items:[
									Admin.permissionField(Board.getText("admin/list/form/permission_list"),"permission_list","true"),
									Admin.permissionField(Board.getText("admin/list/form/permission_view"),"permission_view","true"),
									Admin.permissionField(Board.getText("admin/list/form/permission_post_write"),"permission_post_write","true"),
									Admin.permissionField(Board.getText("admin/list/form/permission_ment_write"),"permission_ment_write","true"),
									Admin.permissionField(Board.getText("admin/list/form/permission_post_modify"),"permission_post_modify","{$member.type} == 'ADMINISTRATOR'"),
									Admin.permissionField(Board.getText("admin/list/form/permission_ment_modify"),"permission_ment_modify","{$member.type} == 'ADMINISTRATOR'"),
									Admin.permissionField(Board.getText("admin/list/form/permission_post_secret"),"permission_post_secret","{$member.type} == 'ADMINISTRATOR'"),
									Admin.permissionField(Board.getText("admin/list/form/permission_ment_secret"),"permission_ment_secret","{$member.type} == 'ADMINISTRATOR'"),
									Admin.permissionField(Board.getText("admin/list/form/permission_post_delete"),"permission_post_delete","{$member.type} == 'ADMINISTRATOR'"),
									Admin.permissionField(Board.getText("admin/list/form/permission_ment_delete"),"permission_ment_delete","{$member.type} == 'ADMINISTRATOR'"),
									Admin.permissionField(Board.getText("admin/list/form/permission_html_title"),"permission_html_title","{$member.type} == 'ADMINISTRATOR'"),
									Admin.permissionField(Board.getText("admin/list/form/permission_notice"),"permission_notice","{$member.type} == 'ADMINISTRATOR'"),
									Admin.permissionField(Board.getText("admin/list/form/permission_hidename"),"permission_hidename","{$member.type} != 'GUEST'",false),
									Admin.permissionField(Board.getText("admin/list/form/permission_ip"),"permission_ip","{$member.type} == 'ADMINISTRATOR'"),
									new Ext.Panel({
										border:false,
										html:'<div class="helpBlock">'+Board.getText("admin/list/form/permission_help")+'</div>'
									})
								]
							}),
							new Ext.form.FieldSet({
								title:Board.getText("admin/list/form/point_setting"),
								collapsible:true,
								collapsed:false,
								items:[
									new Ext.form.FieldContainer({
										margin:"0 0 0 105",
										layout:"hbox",
										items:[
											new Ext.form.DisplayField({
												value:Board.getText("admin/list/form/point"),
												flex:1
											}),
											new Ext.form.DisplayField({
												value:Board.getText("admin/list/form/exp"),
												margin:"0 0 0 5",
												flex:1
											})
										]
									}),
									new Ext.form.FieldContainer({
										fieldLabel:Board.getText("admin/list/form/post_write"),
										layout:"hbox",
										items:[
											new Ext.form.NumberField({
												name:"post_point",
												value:30,
												flex:1
											}),
											new Ext.form.NumberField({
												name:"post_exp",
												margin:"0 0 0 5",
												value:10,
												flex:1
											})
										]
									}),
									new Ext.form.FieldContainer({
										fieldLabel:Board.getText("admin/list/form/ment_write"),
										layout:"hbox",
										items:[
											new Ext.form.NumberField({
												name:"ment_point",
												value:10,
												flex:1
											}),
											new Ext.form.NumberField({
												name:"ment_exp",
												margin:"0 0 0 5",
												value:5,
												flex:1
											})
										]
									}),
									new Ext.form.FieldContainer({
										fieldLabel:Board.getText("admin/list/form/vote"),
										layout:"hbox",
										items:[
											new Ext.form.NumberField({
												name:"vote_point",
												value:5,
												flex:1
											}),
											new Ext.form.NumberField({
												name:"vote_exp",
												margin:"0 0 0 5",
												value:1,
												flex:1
											})
										]
									})
								]
							})
						]
					})
				],
				buttons:[
					new Ext.Button({
						text:Board.getText("button/confirm"),
						handler:function() {
							if (Ext.getCmp("ModuleBoardAddBoardForm").getForm().findField("use_category").checked == true) {
								Ext.getCmp("ModuleBoardAddBoardForm").getForm().findField("category").setValue(JSON.stringify(Admin.grid(Ext.getCmp("ModuleBoardCategoryList"))));
							}
							
							Ext.getCmp("ModuleBoardAddBoardForm").getForm().submit({
								url:ENV.getProcessUrl("board","@saveBoard"),
								submitEmptyText:false,
								waitTitle:Admin.getText("action/wait"),
								waitMsg:Admin.getText("action/saving"),
								success:function(form,action) {
									Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("action/saved"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
//										Ext.getCmp("ModuleBoardAddBoardWindow").close();
										Ext.getCmp("ModuleBoardList").getStore().reload();
									}});
								},
								failure:function(form,action) {
									if (action.result) {
										if (action.result.message) {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										} else {
											Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getText("error/save"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										}
									} else {
										Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getText("error/form"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									}
								}
							});
						}
					}),
					new Ext.Button({
						text:Board.getText("button/cancel"),
						handler:function() {
							Ext.getCmp("ModuleBoardAddBoardWindow").close();
						}
					})
				],
				listeners:{
					show:function() {
						if (bid !== undefined) {
							Ext.getCmp("ModuleBoardAddBoardForm").getForm().load({
								url:ENV.getProcessUrl("board","@getBoard"),
								params:{bid:bid},
								waitTitle:Admin.getText("action/wait"),
								waitMsg:Admin.getText("action/loading"),
								success:function(form,action) {
									if (form.findField("use_category").checked == true) {
										var category = JSON.parse(form.findField("category").getValue());
										for (var i=0, loop=category.length;i<loop;i++) {
											Ext.getCmp("ModuleBoardCategoryList").getStore().add(category[i]);
										}
									}
									/*
									form.findField("permission_list").fireEvent("blur",form.findField("permission_list"));
									form.findField("permission_view").fireEvent("blur",form.findField("permission_view"));
									form.findField("permission_write").fireEvent("blur",form.findField("permission_write"));
									form.findField("permission_ment_write").fireEvent("blur",form.findField("permission_ment_write"));
									form.findField("permission_modify").fireEvent("blur",form.findField("permission_modify"));
									form.findField("permission_secret").fireEvent("blur",form.findField("permission_secret"));
									form.findField("permission_delete").fireEvent("blur",form.findField("permission_delete"));
									form.findField("permission_ip").fireEvent("blur",form.findField("permission_ip"));
									*/
								},
								failure:function(form,action) {
									if (action.result && action.result.message) {
										Ext.Msg.show({title:Admin.getText("alert/error"),msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									} else {
										Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getText("error/load"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
									}
									Ext.getCmp("ModuleBoardAddBoardWindow").close();
								}
							});
						}
					}
				}
			}).show();
		}
	},
	/**
	 * 카테고리 관리
	 *
	 * @param object data 카테고리 데이터
	 */
	category:{
		add:function(data) {
			var data = data !== undefined && typeof data == "object" ? data : null;
			
			new Ext.Window({
				id:"ModuleBoardAddCategoryWindow",
				title:(data == null ? "카테고리추가" : "카테고리수정"),
				modal:true,
				width:600,
				border:false,
				autoScroll:true,
				items:[
					new Ext.form.Panel({
						id:"ModuleBoardAddCategoryForm",
						border:false,
						bodyPadding:"10 10 0 10",
						fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:false},
						items:[
							new Ext.form.TextField({
								name:"title",
								emptyText:"카테고리명",
								allowBlank:false,
								validator:function(value) {
									if (Ext.getCmp("ModuleBoardCategoryList").getStore().findExact("title",value) == -1) return true;
									else return "카테고리명이 중복됩니다.";
								}
							}),
							new Ext.form.FieldSet({
								title:"카테고리 개별권한 사용",
								checkboxName:"use_permission",
								checkboxToggle:true,
								collapsed:true,
								items:[
									Admin.permissionField(Board.getText("admin/list/form/permission_view"),"permission_view","true"),
									Admin.permissionField(Board.getText("admin/list/form/permission_post_write"),"permission_post_write","true"),
									Admin.permissionField(Board.getText("admin/list/form/permission_ment_write"),"permission_ment_write","true"),
									new Ext.Panel({
										border:false,
										html:'<div class="helpBlock">카테고리 개별적으로 권한을 설정할 수 있습니다.<br>개별 카테고리 권한을 사용하지 않을 경우 게시판 권한설정을 따르게 됩니다.</div>'
									})
								]
							})
						]
					})
				],
				buttons:[
					new Ext.Button({
						text:"확인",
						handler:function() {
							var form = Ext.getCmp("ModuleBoardAddCategoryForm").getForm();
							var list = Ext.getCmp("ModuleBoardCategoryList");
							
							if (form.isValid() == true) {
								var idx = 0;
								var title = form.findField("title").getValue();
								
								if (form.findField("use_permission").checked == true) {
									var permission = {};
									permission.view = form.findField("permission_view").getValue();
									permission.post_write = form.findField("permission_post_write").getValue();
									permission.ment_write = form.findField("permission_ment_write").getValue();
								} else {
									var permission = null;
								}
								
								var post = 0;
								var sort = list.getStore().max("sort") === undefined ? 0 : Ext.getCmp("ModuleBoardCategoryList").getStore().max("sort") + 1;
								
								list.getStore().add({idx:0,title:title,post:post,permission:permission,sort:sort});
								Ext.getCmp("ModuleBoardAddCategoryWindow").close();
							}
						}
					})
				]
			}).show();
		}
	}
};