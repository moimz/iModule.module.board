/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodules.io)
 *
 * 게시판 관리자 UI를 처리한다.
 * 
 * @file /modules/board/admin/scripts/script.js
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0
 * @modified 2019. 7. 27.
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
									Admin.templetField(Board.getText("admin/list/form/templet"),"templet","module","board",false,ENV.getProcessUrl("board","@getTempletConfigs"),{},["bid"]),
									new Ext.form.FieldContainer({
										layout:"hbox",
										items:[
											new Ext.form.NumberField({
												fieldLabel:Board.getText("admin/list/form/post_limit"),
												name:"post_limit",
												value:20,
												flex:1
											}),
											new Ext.form.NumberField({
												fieldLabel:Board.getText("admin/list/form/ment_limit"),
												name:"ment_limit",
												value:50,
												flex:1
											})
										]
									}),
									new Ext.form.FieldContainer({
										layout:"hbox",
										items:[
											new Ext.form.ComboBox({
												fieldLabel:Board.getText("admin/list/form/page_type"),
												name:"page_type",
												store:new Ext.data.ArrayStore({
													fields:["display","value"],
													data:[[Board.getText("admin/list/page_type/FIXED"),"FIXED"],[Board.getText("admin/list/page_type/CENTER"),"CENTER"]]
												}),
												editable:false,
												displayField:"display",
												valueField:"value",
												value:"FIXED",
												flex:1,
												listeners:{
													change:function(form,value) {
														var form = Ext.getCmp("ModuleBoardAddBoardForm").getForm();
														if (value == "CENTER" && form.findField("page_limit").getValue() % 2 == 0) {
															form.findField("page_limit").setValue(form.findField("page_limit").getValue() + 1);
															
															Ext.Msg.show({title:Admin.getText("alert/info"),msg:Board.getText("admin/list/page_type/help"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
														}
													}
												}
											}),
											new Ext.form.NumberField({
												fieldLabel:Board.getText("admin/list/form/page_limit"),
												name:"page_limit",
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
									Admin.templetField(Board.getText("admin/list/form/attachment_templet"),"attachment","module","attachment",Board.getText("admin/list/form/attachment_templet_default"),ENV.getProcessUrl("board","@getTempletConfigs"),{},["bid"]),
								]
							}),
							new Ext.form.FieldSet({
								title:Board.getText("admin/list/form/option"),
								items:[
									new Ext.form.Checkbox({
										name:"allow_secret",
										boxLabel:Board.getText("admin/list/form/allow_secret"),
										checked:true
									}),
									new Ext.form.Checkbox({
										name:"allow_anonymity",
										boxLabel:Board.getText("admin/list/form/allow_anonymity"),
										checked:true
									}),
									new Ext.form.Checkbox({
										name:"allow_voting",
										boxLabel:Board.getText("admin/list/form/allow_voting"),
										checked:true,
										listeners:{
											change:function(form,checked) {
												form.getForm().findField("vote_point").ownerCt.setHidden(!checked);
												form.getForm().findField("voted_point").ownerCt.setHidden(!checked);
											}
										}
									}),
									new Ext.form.Checkbox({
										name:"use_content_list",
										boxLabel:Board.getText("admin/list/form/use_content_list"),
										checked:false
									})
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
											data:[[Board.getText("admin/list/notice_type/FIRST"),"FIRST"],[Board.getText("admin/list/notice_type/ALL"),"ALL"]]
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
													Ext.Msg.show({title:Admin.getText("alert/info"),msg:Board.getText("admin/list/notice_type/help"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
												}
											}
										}
									}),
									new Ext.form.ComboBox({
										name:"view_notice_count",
										store:new Ext.data.ArrayStore({
											fields:["display","value"],
											data:[[Board.getText("admin/list/notice_type/INCLUDE"),"INCLUDE"],[Board.getText("admin/list/notice_type/EXCLUDE"),"EXCLUDE"]]
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
													Ext.Msg.show({title:Admin.getText("alert/info"),msg:Board.getText("admin/list/notice_type/help"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
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
												iconCls:"mi mi-plus",
												text:"카테고리추가",
												handler:function() {
													Board.category.add();
												}
											}),
											new Ext.Button({
												iconCls:"mi mi-trash",
												text:"선택 카테고리 삭제",
												handler:function() {
													Board.category.delete();
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
												if (value == null || value == "") return "게시판 권한설정 사용";
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
											}),
											"->",
											{xtype:"tbtext",text:"더블클릭 : 카테고리수정 / 마우스우클릭 : 상세메뉴"}
										],
										selModel:new Ext.selection.CheckboxModel(),
										listeners:{
											itemdblclick:function(grid,record,td,index) {
												Board.category.add(index);
											},
											itemcontextmenu:function(grid,record,item,index,e) {
												var menu = new Ext.menu.Menu();
												
												menu.add('<div class="x-menu-title">'+record.data.title+'</div>');
												
												menu.add({
													iconCls:"xi xi-form",
													text:"카테고리 수정",
													handler:function() {
														Board.category.add(index);
													}
												});
												
												menu.add({
													iconCls:"mi mi-trash",
													text:"카테고리 삭제",
													handler:function() {
														Board.category.delete();
													}
												});
												
												e.stopEvent();
												menu.showAt(e.getXY());
											}
										}
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
									}),
									new Ext.form.FieldContainer({
										fieldLabel:Board.getText("admin/list/form/voted"),
										layout:"hbox",
										items:[
											new Ext.form.NumberField({
												name:"voted_point",
												value:10,
												flex:1
											}),
											new Ext.form.NumberField({
												name:"voted_exp",
												margin:"0 0 0 5",
												value:3,
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
										Ext.getCmp("ModuleBoardAddBoardWindow").close();
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
							Ext.getCmp("ModuleBoardAddBoardForm").getForm().findField("templet").setValue("#");
							Ext.getCmp("ModuleBoardAddBoardForm").getForm().findField("attachment").setValue("#");
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
		},
		view:function(bid,title) {
			new Ext.Window({
				id:"ModuleBoardViewBoardWindow",
				title:title,
				modal:true,
				width:950,
				height:600,
				border:false,
				layout:"fit",
				maximizable:true,
				items:[
					new Ext.Panel({
						border:false,
						html:'<iframe src="'+ENV.getModuleUrl("board",bid,"list")+'" style="width:100%; height:100%; border:0px;" frameborder="0" scrolling="1"></iframe>'
					})
				]
			}).show();
		},
		delete:function() {
			var selected = Ext.getCmp("ModuleBoardList").getSelectionModel().getSelection();
			if (selected.length == 0) {
				Ext.Msg.show({title:Admin.getText("alert/error"),msg:"삭제할 게시판을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
				return;
			}
			
			var bids = [];
			for (var i=0, loop=selected.length;i<loop;i++) {
				bids.push(selected[i].get("bid"));
			}
			
			Ext.Msg.show({title:Admin.getText("alert/info"),msg:"선택하신 게시판을 정말 삭제하시겠습니까?<br>게시판에 포함된 모든 게시물/댓글/첨부파일이 함께 삭제됩니다.",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
				if (button == "ok") {
					Ext.Msg.wait(Admin.getText("action/working"),Admin.getText("action/wait"));
					$.send(ENV.getProcessUrl("board","@deleteBoard"),{bid:bids.join(",")},function(result) {
						if (result.success == true) {
							Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("action/worked"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
								Ext.getCmp("ModuleBoardList").getStore().reload();
							}});
						}
					});
				}
			}});
		}
	},
	/**
	 * 카테고리 관리
	 *
	 * @param object data 카테고리 데이터
	 */
	category:{
		add:function(index) {
			var data = index !== undefined ? Ext.getCmp("ModuleBoardCategoryList").getStore().getAt(index).data : null;
			
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
								value:(data ? data.title : null),
								validator:function(value) {
									var check = Ext.getCmp("ModuleBoardCategoryList").getStore().findExact("title",value);
									if (check == -1 || check == index) return true;
									else return "카테고리명이 중복됩니다.";
								}
							}),
							new Ext.form.FieldSet({
								title:"카테고리 개별권한 사용",
								checkboxName:"use_permission",
								checkboxToggle:true,
								collapsed:(data && data.permission ? false : true),
								items:[
									Admin.permissionField(Board.getText("admin/list/form/permission_view"),"permission_view",(data && data.permission && data.permission.view ? data.permission.view : "true")),
									Admin.permissionField(Board.getText("admin/list/form/permission_post_write"),"permission_post_write",(data && data.permission && data.permission.post_write ? data.permission.post_write : "true")),
									Admin.permissionField(Board.getText("admin/list/form/permission_ment_write"),"permission_ment_write",(data && data.permission && data.permission.ment_write ? data.permission.ment_write : "true")),
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
								var title = form.findField("title").getValue();
								
								if (form.findField("use_permission").checked == true) {
									var permission = {};
									permission.view = form.findField("permission_view").getValue();
									permission.post_write = form.findField("permission_post_write").getValue();
									permission.ment_write = form.findField("permission_ment_write").getValue();
								} else {
									var permission = null;
								}
								
								if (index === undefined) {
									var idx = 0;
									var post = 0;
									var sort = list.getStore().max("sort") === undefined ? 0 : Ext.getCmp("ModuleBoardCategoryList").getStore().max("sort") + 1;
									list.getStore().add({idx:0,title:title,post:post,permission:permission,sort:sort});
								} else {
									list.getStore().getAt(index).set({title:title,permission:permission});
								}
								
								Ext.getCmp("ModuleBoardAddCategoryWindow").close();
							}
						}
					})
				]
			}).show();
		},
		delete:function() {
			var selected = Ext.getCmp("ModuleBoardCategoryList").getSelectionModel().getSelection();
			if (selected.length == 0) {
				Ext.Msg.show({title:Admin.getText("alert/error"),msg:"삭제할 카테고리를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
				return;
			}
			
			Ext.Msg.show({title:Admin.getText("alert/info"),msg:"선택하신 카테고리를 삭제하시겠습니까?<br>삭제되는 카테고리의 게시물이 기본 카테고리로 이동됩니다.",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
				if (button == "ok") {
					var store = Ext.getCmp("ModuleBoardCategoryList").getStore();
					store.remove(selected);
					for (var i=0, loop=store.getCount();i<loop;i++) {
						store.getAt(i).set({sort:i});
					}
				}
			}});
		}
	},
	/**
	 * 관리자관리
	 */
	admin:{
		add:function(midx) {
			var midx = midx ? midx : 0;
			
			new Ext.Window({
				id:"ModuleBoardAdminAddWindow",
				title:(midx ? Board.getText("admin/admin/modify_admin") : Board.getText("admin/admin/add_admin")),
				width:600,
				height:500,
				modal:true,
				border:false,
				layout:"fit",
				items:[
					new Ext.Panel({
						border:false,
						layout:"fit",
						tbar:[
							new Ext.form.Hidden({
								id:"ModuleBoardAdminAddMidx",
								name:"midx",
								value:midx,
								disabled:midx
							}),
							new Ext.form.TextField({
								id:"ModuleBoardAdminAddText",
								name:"text",
								emptyText:"검색버튼을 클릭하여 관리자로 지정할 회원을 검색하세요.",
								readOnly:true,
								flex:1,
								listeners:{
									focus:function() {
										Member.search(function(member) {
											var text = member.name + "(" + member.nickname + ") / " + member.email;
											Ext.getCmp("ModuleBoardAdminAddText").setValue(text);
											Ext.getCmp("ModuleBoardAdminAddMidx").setValue(member.idx);
										});
									}
								}
							}),
							new Ext.Button({
								iconCls:"mi mi-search",
								text:"검색",
								disabled:midx,
								handler:function() {
									Member.search(function(member) {
										var text = member.name + "(" + member.nickname + ") / " + member.email;
										Ext.getCmp("ModuleBoardAdminAddText").setValue(text);
										Ext.getCmp("ModuleBoardAdminAddMidx").setValue(member.idx);
									});
								}
							}),
							"-",
							new Ext.form.Checkbox({
								id:"ModuleBoardAdminAddAll",
								boxLabel:Board.getText("admin/admin/admin_all"),
								listeners:{
									change:function(form,value) {
										Ext.getCmp("ModuleBoardAdminAddList").setDisabled(value);
									}
								}
							})
						],
						items:[
							new Ext.grid.Panel({
								id:"ModuleBoardAdminAddList",
								border:false,
								selected:[],
								layout:"fit",
								autoScroll:true,
								store:new Ext.data.JsonStore({
									proxy:{
										type:"ajax",
										simpleSortMode:true,
										url:ENV.getProcessUrl("board","@getBoards"),
										extraParams:{depth:"group",parent:"NONE"},
										reader:{type:"json"}
									},
									remoteSort:false,
									sorters:[{property:"title",direction:"ASC"}],
									autoLoad:false,
									pageSize:0,
									fields:["idx","title"],
									listeners:{
										load:function(store,records,success,e) {
											if (success == true) {
												Ext.getCmp("ModuleBoardAdminAddList").getSelectionModel().deselectAll(true);
												var selected = Ext.getCmp("ModuleBoardAdminAddList").selected;
												for (var i=0, loop=store.getCount();i<loop;i++) {
													if ($.inArray(store.getAt(i).get("bid"),selected) > -1) {
														Ext.getCmp("ModuleBoardAdminAddList").getSelectionModel().select(i,true);
													}
												}
											} else {
												if (e.getError()) {
													Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												} else {
													Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getErrorText("LOAD_DATA_FAILED"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												}
											}
										}
									}
								}),
								columns:[{
									text:Board.getText("admin/list/columns/bid"),
									width:180,
									dataIndex:"bid",
								},{
									text:Board.getText("admin/list/columns/title"),
									flex:1,
									dataIndex:"title"
								}],
								selModel:new Ext.selection.CheckboxModel({mode:"SIMPLE"})
							})
						]
					})
				],
				buttons:[
					new Ext.Button({
						text:Admin.getText("button/confirm"),
						handler:function() {
							var midx = Ext.getCmp("ModuleBoardAdminAddMidx").getValue();
							if (Ext.getCmp("ModuleBoardAdminAddAll").getValue() == true) {
								var bid = "*";
							} else {
								var bids = Ext.getCmp("ModuleBoardAdminAddList").getSelectionModel().getSelection();
								for (var i=0, loop=bids.length;i<loop;i++) {
									bids[i] = bids[i].get("bid");
								}
								var bid = bids.join(",");
							}
							
							if (!midx) {
								Ext.Msg.show({title:Admin.getText("alert/error"),msg:"관리자로 추가할 회원을 검색하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
							} else {
								Ext.Msg.wait(Admin.getText("action/working"),Admin.getText("action/saving"));
								$.send(ENV.getProcessUrl("board","@saveAdmin"),{midx:midx,bid:bid},function(result) {
									if (result.success == true) {
										Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("action/saved"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
											Ext.getCmp("ModuleBoardAdminList").getStore().reload();
											Ext.getCmp("ModuleBoardAdminAddWindow").close();
										}});
									}
								});
							}
						}
					}),
					new Ext.Button({
						text:Admin.getText("button/close"),
						handler:function() {
							Ext.getCmp("ModuleBoardAdminAddWindow").close();
						}
					})
				],
				listeners:{
					show:function() {
						if (midx == 0) {
							Ext.getCmp("ModuleBoardAdminAddList").getStore().load();
						} else {
							Ext.Msg.wait(Admin.getText("action/working"),Admin.getText("action/loading"));
							$.send(ENV.getProcessUrl("board","@getAdmin"),{midx:midx},function(result) {
								if (result.success == true) {
									Ext.Msg.hide();
									Ext.getCmp("ModuleBoardAdminAddText").setValue(result.member.name+"("+result.member.nickname+") / "+result.member.email);
									if (result.bid == "*") {
										Ext.getCmp("ModuleBoardAdminAddAll").setValue(true);
									} else {
										Ext.getCmp("ModuleBoardAdminAddAll").setValue(false);
										Ext.getCmp("ModuleBoardAdminAddList").selected = result.bid;
									}
									Ext.getCmp("ModuleBoardAdminAddList").getStore().load();
								}
							});
						}
					}
				}
			}).show();
		},
		/**
		 * 관리자 삭제
		 */
		delete:function(midx) {
			Ext.Msg.show({title:Admin.getText("alert/info"),msg:"관리자를 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
				if (button == "ok") {
					Ext.Msg.wait(Admin.getText("action/working"),Admin.getText("action/loading"));
					$.send(ENV.getProcessUrl("board","@deleteAdmin"),{midx:midx},function(result) {
						if (result.success == true) {
							Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("action/worked"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
								Ext.getCmp("ModuleBoardAdminList").getStore().reload();
							}});
						}
					});
				}
			}});
		},
	}
};