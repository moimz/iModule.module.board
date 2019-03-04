<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodules.io)
 *
 * 게시판모듈 관리자패널을 구성한다.
 * 
 * @file /modules/board/admin/index.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0
 * @modified 2019. 2. 6.
 */
if (defined('__IM__') == false) exit;
?>
<script>
Ext.onReady(function () { Ext.getCmp("iModuleAdminPanel").add(
	new Ext.TabPanel({
		id:"ModuleBoard",
		border:false,
		tabPosition:"bottom",
		items:[
			new Ext.grid.Panel({
				id:"ModuleBoardList",
				title:Board.getText("admin/list/title"),
				border:false,
				tbar:[
					new Ext.Button({
						text:Board.getText("admin/list/addBoard"),
						iconCls:"fa fa-plus",
						handler:function() {
							Board.list.add();
						}
					}),
					new Ext.Button({
						text:"선택 게시판 삭제",
						iconCls:"mi mi-trash",
						handler:function() {
							Board.list.delete();
						}
					})
				],
				store:new Ext.data.JsonStore({
					proxy:{
						type:"ajax",
						simpleSortMode:true,
						url:ENV.getProcessUrl("board","@getBoards"),
						reader:{type:"json"}
					},
					remoteSort:true,
					sorters:[{property:"bid",direction:"ASC"}],
					autoLoad:true,
					pageSize:50,
					fields:["bid","title","nickname","exp","point","reg_date","last_login","display_url","count","image"],
					listeners:{
						load:function(store,records,success,e) {
							if (success == false) {
								if (e.getError()) {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								} else {
									Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getText("error/load"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								}
							}
						}
					}
				}),
				columns:[{
					text:Board.getText("admin/list/columns/bid"),
					width:120,
					sortable:true,
					dataIndex:"bid"
				},{
					text:Board.getText("admin/list/columns/title"),
					minWidth:200,
					flex:1,
					sortable:true,
					dataIndex:"title"
				},{
					text:Board.getText("admin/list/columns/category"),
					width:80,
					align:"right",
					dataIndex:"category",
					renderer:function(value,p) {
						if (value == 0) {
							p.style = "text-align:center;";
							return "-";
						}
						return Ext.util.Format.number(value,"0,000");
					}
				},{
					text:Board.getText("admin/list/columns/post"),
					width:80,
					align:"right",
					dataIndex:"post",
					sortable:true,
					renderer:function(value,p) {
						if (value == 0) {
							p.style = "text-align:center;";
							return "-";
						}
						return Ext.util.Format.number(value,"0,000");
					}
				},{
					text:Board.getText("admin/list/columns/latest_post"),
					width:130,
					align:"center",
					dataIndex:"latest_post",
					sortable:true,
					renderer:function(value) {
						return value > 0 ? moment(value * 1000).format("YYYY-MM-DD HH:mm") : "-";
					}
				},{
					text:Board.getText("admin/list/columns/ment"),
					width:80,
					align:"right",
					dataIndex:"ment",
					sortable:true,
					renderer:function(value,p) {
						if (value == 0) {
							p.style = "text-align:center;";
							return "-";
						}
						return Ext.util.Format.number(value,"0,000");
					}
				},{
					text:Board.getText("admin/list/columns/latest_ment"),
					width:130,
					align:"center",
					dataIndex:"latest_ment",
					sortable:true,
					renderer:function(value) {
						return value > 0 ? moment(value * 1000).format("YYYY-MM-DD HH:mm") : "-";
					}
				},{
					text:Board.getText("admin/list/columns/file"),
					width:80,
					align:"right",
					dataIndex:"file",
					renderer:function(value,p) {
						if (value == 0) {
							p.style = "text-align:center;";
							return "-";
						}
						return Ext.util.Format.number(value,"0,000");
					}
				},{
					text:Board.getText("admin/list/columns/file_size"),
					width:100,
					align:"right",
					dataIndex:"file_size",
					renderer:function(value) {
						return iModule.getFileSize(value);
					}
				}],
				selModel:new Ext.selection.CheckboxModel(),
				bbar:new Ext.PagingToolbar({
					store:null,
					displayInfo:false,
					items:[
						"->",
						{xtype:"tbtext",text:"항목 더블클릭 : 게시판보기 / 항목 우클릭 : 상세메뉴"}
					],
					listeners:{
						beforerender:function(tool) {
							tool.bindStore(Ext.getCmp("ModuleBoardList").getStore());
						}
					}
				}),
				listeners:{
					itemdblclick:function(grid,record) {
						Board.list.view(record.data.bid,record.data.title);
					},
					itemcontextmenu:function(grid,record,item,index,e) {
						var menu = new Ext.menu.Menu();
						
						menu.add('<div class="x-menu-title">'+record.data.title+'</div>');
						
						menu.add({
							iconCls:"xi xi-form",
							text:"게시판 수정",
							handler:function() {
								Board.list.add(record.data.bid);
							}
						});
						
						menu.add({
							iconCls:"mi mi-trash",
							text:"게시판 삭제",
							handler:function() {
								Board.list.delete();
							}
						});
						
						e.stopEvent();
						menu.showAt(e.getXY());
					}
				}
			}),
			<?php if ($this->isAdmin(null, 'ADMIN') == true) { ?>
			new Ext.Panel({
				id:"ModuleBoardAdminPanel",
				iconCls:"xi xi-user",
				title:"관리자 관리",
				border:false,
				layout:{type:"hbox",align:"stretch"},
				padding:5,
				items:[
					new Ext.grid.Panel({
						id:"ModuleBoardAdminBoardList",
						title:"게시판 리스트",
						width:500,
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:ENV.getProcessUrl("board","@getAdminBoards"),
								reader:{type:"json"}
							},
							remoteSort:false,
							sorters:[{property:"sort",direction:"ASC"}],
							autoLoad:true,
							pageSize:0,
							fields:["bid",{name:"operator",type:"int"}],
							listeners:{
								load:function(store,records,success,e) {
									if (success == true) {
										Ext.getCmp("ModuleBoardAdminList").disable();
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
							text:"게시판 ID",
							width:120,
							dataIndex:"bid",
							sortable:true,
							align:"left"
						},{
							text:"게시판명",
							sortable:true,
							dataIndex:"title",
							flex:1
						},{
							text:"관리자수",
							width:80,
							dataIndex:"count",
							sortable:true,
							align:"right"
						}],
						bbar:[
							new Ext.Button({
								iconCls:"x-tbar-loading",
								handler:function() {
									Ext.getCmp("ModuleBoardAdminBoardList").getStore().reload();
								}
							}),
							{xtype:"tbtext",text:"게시판을 선택하여 게시판별 관리자를 확인할 수 있습니다."}
						],
						listeners:{
							select:function(grid,record) {
								Ext.getCmp("ModuleBoardAdminList").enable();
								Ext.getCmp("ModuleBoardAdminList").getStore().getProxy().setExtraParam("bid",record.data.bid);
								Ext.getCmp("ModuleBoardAdminList").getStore().reload();
							}
						}
					}),
					new Ext.grid.Panel({
						id:"ModuleBoardAdminList",
						title:"관리자 목록",
						flex:1,
						margin:"0 0 0 5",
						tbar:[
							new Ext.Button({
								iconCls:"mi mi-plus",
								text:"관리자 추가",
								handler:function() {
									Coursemos.member(function(member) {
										var selection = Ext.getCmp("ModuleBoardAdminBoardList").getSelectionModel().getSelection();
										if (selection.length != 1) return;
										selection = selection.pop();

										Ext.Msg.show({title:Admin.getText("alert/info"),msg:member.name+"님을 "+ selection.data.title+"의 관리자로 추가하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
											if (button == "ok") {
												Ext.Msg.wait(Admin.getText("action/working"),Admin.getText("action/wait"));
												$.send(ENV.getProcessUrl("board","@saveAdmin"),{bid:selection.data.bid,midx:member.idx,status:"CONFIRMED"},function(result) {
													if (result.success == true) {
														Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("action/worked"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
															Ext.getCmp("ModuleBoardAdminList").getStore().reload();
														}});
													} else {
														Ext.Msg.show({title:Admin.getText("alert/error"),msg:result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
													}

													return false;
												});
											}
										}});
									});
	//								Eco.essential.add( 0, Ext.getCmp("ModulePollEssentialList2").getStore().getProxy().extraParams.parent );
								}
							})
						],
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:ENV.getProcessUrl("board","@getAdmins"),
								extraParams:{parent:-1},
								reader:{type:"json"}
							},
							remoteSort:false,
							sorters:[{property:"name",direction:"ASC"}],
							autoLoad:false,
							pageSize:0,
							fields:[{name:"sort",type:"int"}],
							listeners:{
								load:function(store,records,success,e) {
									if (success == false) {
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
							text:"구분",
							width:100,
							align:"center",
							dataIndex:"role"
						},{
							text:"교번/사번",
							width:100,
							dataIndex:"code"
						},{
							text:"이름",
							minWidth:100,
							flex:1,
							dataIndex:"name"
						},{
							text:"단과대학",
							width:120,
							dataIndex:"institution"
						},{
							text:"학과",
							width:150,
							dataIndex:"department"
						},{
							text:"연락처",
							width:120,
							dataIndex:"cellphone"
						},{
							text:"이메일주소",
							width:200,
							dataIndex:"email"
						}/*,{
							text:"상태",
							width:100,
							dataIndex:"status",
							align:"center",
							renderer:function(value) {
								console.log("value = ",value);
								// return Eco.getText("status/"+value);
							}
						}*/],
						bbar:[
							new Ext.Button({
								iconCls:"x-tbar-loading",
								handler:function() {
									Ext.getCmp("ModuleBoardAdminList").getStore().reload();
								}
							}),
							"->",
							{xtype:"tbtext",text:"항목 우클릭 : 상세메뉴"}
						],
						listeners:{
							itemcontextmenu:function(grid,record,item,index,e) {
								var menu = new Ext.menu.Menu();

								menu.add('<div class="x-menu-title">'+record.data.name+'</div>');

								menu.add({
									iconCls:"mi mi-trash",
									text:"관리자 삭제",
									handler:function() {
										Ext.Msg.show({title:Admin.getText("alert/info"),msg:"관리자를 삭제하시겠습니까?<br>해당 관리자는 선택된 게시판을 더이상 관리할 수 없습니다.",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
											if (button == "ok") {
												Ext.Msg.wait(Admin.getText("action/working"),Admin.getText("action/wait"));
												$.send(ENV.getProcessUrl("board","@deleteAdmin"),{bid:record.data.bid,midx:record.data.midx},function(result) {
													if (result.success == true) {
														Ext.Msg.show({title:Admin.getText("alert/info"),msg:Admin.getText("action/worked"),buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
															Ext.getCmp("ModuleBoardAdminList").getStore().reload();
														}});
													} else {
														Ext.Msg.show({title:Admin.getText("alert/error"),msg:result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
													}
													return false;
												});
											}
										}});
									}
								});

								e.stopEvent();
								menu.showAt(e.getXY());
							}
						}
					}),
				]
			})
			<?php } ?>
		]
	})
); });
</script>