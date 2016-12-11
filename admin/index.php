<script>
var panel = new Ext.TabPanel({
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
						Board.addBoard();
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
				sorters:[{property:"reg_date",direction:"DESC"}],
				autoLoad:true,
				pageSize:50,
				fields:["bid","title","nickname","exp","point","reg_date","last_login","display_url","count","image"],
				listeners:{
					load:function(store,records,success,e) {
						if (success == false) {
							if (e.getError()) {
								Ext.Msg.show({title:Admin.getText("alert/error"),msg:e.getError(),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
							} else {
								Ext.Msg.show({title:Admin.getText("alert/error"),msg:Admin.getText("error/load"),buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR})
							}
						}
					}
				}
			}),
			width:"100%",
			columns:[{
				text:Board.getText("admin/list/columns/bid"),
				width:80,
				dataIndex:"bid"
			},{
				text:Board.getText("admin/list/columns/title"),
				minWidth:100,
				flex:1,
				dataIndex:"title"
			}],
			selModel:new Ext.selection.CheckboxModel(),
			bbar:new Ext.PagingToolbar({
				store:null,
				displayInfo:false,
				items:[
					"->",
					{xtype:"tbtext",text:Board.getText("admin/grid_help")}
				],
				listeners:{
					beforerender:function(tool) {
						tool.bindStore(Ext.getCmp("ModuleBoardList").getStore());
					}
				}
			}),
			listeners:{
				itemdblclick:function(grid,record) {
					Board.addBoard(record.data.bid);
				}
			}
		})
	]
});
</script>