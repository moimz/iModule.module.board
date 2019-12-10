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
 * @modified 2019. 12. 11.
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
				iconCls:"fa fa-file-text-o",
				title:Board.getText("admin/list/title"),
				border:false,
				tbar:[
					new Ext.Button({
						text:Board.getText("admin/list/add_board"),
						iconCls:"mi mi-plus",
						handler:function() {
							Board.list.add();
						}
					}),
					new Ext.Button({
						text:Board.getText("admin/list/delete_board"),
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
						
						menu.addTitle(record.data.title);
						
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
			<?php if ($this->isAdmin() == true) { ?>
			new Ext.grid.Panel({
				id:"ModuleBoardAdminList",
				iconCls:"xi xi-crown",
				title:"관리자 관리",
				border:false,
				tbar:[
					new Ext.Button({
						text:"관리자 추가",
						iconCls:"mi mi-plus",
						handler:function() {
							Board.admin.add();
						}
					}),
					new Ext.Button({
						text:"선택관리자 삭제",
						iconCls:"mi mi-trash",
						handler:function() {
							var selected = Ext.getCmp("ModuleBoardAdminList").getSelectionModel().getSelection();
							if (selected.length == 0) {
								Ext.Msg.show({title:Admin.getText("alert/error"),msg:"삭제할 관리자를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return;
							}
							
							var midxes = [];
							for (var i=0, loop=selected.length;i<loop;i++) {
								midxes[i] = selected[i].data.midx;
							}
							Board.admin.delete(midxes.join(','));
						}
					})
				],
				store:new Ext.data.JsonStore({
					proxy:{
						type:"ajax",
						simpleSortMode:true,
						url:ENV.getProcessUrl("board","@getAdmins"),
						extraParams:{},
						reader:{type:"json"}
					},
					remoteSort:false,
					sorters:[{property:"sort",direction:"ASC"}],
					autoLoad:true,
					pageSize:0,
					fields:[],
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
					text:Board.getText("admin/admin/columns/name"),
					dataIndex:"name",
					sortable:true,
					width:100
				},{
					text:Board.getText("admin/admin/columns/email"),
					dataIndex:"email",
					sortable:true,
					width:180
				},{
					text:Board.getText("admin/admin/columns/bid"),
					dataIndex:"bid",
					sortable:true,
					minWidth:200,
					flex:1
				}],
				selModel:new Ext.selection.CheckboxModel(),
				bbar:[
					new Ext.Button({
						iconCls:"x-tbar-loading",
						handler:function() {
							Ext.getCmp("ModuleBoardAdminList").getStore().reload();
						}
					}),
					"->",
					{xtype:"tbtext",text:Admin.getText("text/grid_help")}
				],
				listeners:{
					itemdblclick:function(grid,record) {
						Board.admin.add(record.data.midx);
					},
					itemcontextmenu:function(grid,record,item,index,e) {
						var menu = new Ext.menu.Menu();
						
						menu.addTitle(record.data.name);
						
						menu.add({
							iconCls:"xi xi-form",
							text:Board.getText("admin/admin/modify_admin"),
							handler:function() {
								Board.admin.add(record.data.midx);
							}
						});

						menu.add({
							iconCls:"xi xi-trash",
							text:Board.getText("admin/admin/delete_admin"),
							handler:function() {
								Board.admin.delete();
							}
						});
						
						e.stopEvent();
						menu.showAt(e.getXY());
					}
				}
			}),
			<?php } ?>
			null
		]
	})
); });
</script>