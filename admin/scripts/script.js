var Board = {
	addBoard:function(bid) {
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
							title:Board.getText("admin/list/form/defaultSetting"),
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
								new Ext.form.ComboBox({
									fieldLabel:Board.getText("admin/list/form/templet"),
									name:"templet",
									store:new Ext.data.JsonStore({
										proxy:{
											type:"ajax",
											simpleSortMode:true,
											url:ENV.getProcessUrl("board","@getTemplets"),
											reader:{type:"json"}
										},
										remoteSort:false,
										sorters:[{property:"templet",direction:"ASC"}],
										pageSize:0,
										fields:["display","value"]
									}),
									autoLoadOnValue:true,
									editable:false,
									displayField:"display",
									valueField:"value",
									value:"default"
								}),
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
							title:Board.getText("admin/list/form/noticeSetting"),
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
						new Ext.form.FieldSet({
							title:Board.getText("admin/list/form/categorySetting"),
							checkboxName:"use_category",
							checkboxToggle:true,
							collapsed:true,
							items:[
								new Ext.grid.Panel({
									id:"ModuleBoardCategoryList",
									border:true,
									tbar:[
										new Ext.Button({
											iconCls:"fa fa-check-square-o",
											handler:function() {
												Ext.getCmp("ModuleBoardCategoryList").getSelectionModel().selectAll();
											}
										})/*,
										"->",
										new Ext.Button({
											text:Member.getText("admin/config/form/addSignupStep"),
											iconCls:"fa fa-arrow-right",
											iconAlign:"right",
											handler:function() {
												var checked = Ext.getCmp("ModuleMemberSignupStepAvailable").getSelectionModel().getSelection();
												for (var i=0, loop=checked.length;i<loop;i++) {
													checked[i].set("sort",Ext.getCmp("ModuleMemberSignupStepUsed").getStore().getCount());
													Ext.getCmp("ModuleMemberSignupStepUsed").getStore().add(checked[i]);
												}
												Ext.getCmp("ModuleMemberSignupStepAvailable").getStore().remove(checked);
												
												Member.checkSignupStep();
												
												var step = [];
												for (var i=0, loop=Ext.getCmp("ModuleMemberSignupStepUsed").getStore().getCount();i<loop;i++) {
													step.push(Ext.getCmp("ModuleMemberSignupStepUsed").getStore().getAt(i).get("step"));
												}
												Ext.getCmp("ModuleConfigForm").getForm().findField("signupStep").setValue(step.join(","));
											}
										})*/
									],
									store:new Ext.data.ArrayStore({
										fields:["idx","title","sort"],
										sorters:[{property:"sort",direction:"ASC"}],
										data:[]
									}),
									flex:1,
									height:300,
									columns:[{
										flex:1,
										dataIndex:"title"
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
							title:Board.getText("admin/list/form/permissionSetting"),
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
							title:Board.getText("admin/list/form/pointSetting"),
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
						Ext.getCmp("ModuleBoardAddBoardForm").getForm().load({
							url:ENV.getProcessUrl("board","@getBoard"),
							params:{bid:bid},
							waitTitle:Admin.getText("action/wait"),
							waitMsg:Admin.getText("action/loading"),
							success:function(form,action) {
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
};