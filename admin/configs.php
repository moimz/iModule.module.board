<?php
/**
 * 이 파일은 iModule 게시판모듈의 일부입니다. (https://www.imodules.io)
 *
 * 모듈 환경설정 패널을 가져온다.
 * 
 * @file /modules/board/admin/configs.php
 * @author Arzz (arzz@arzz.com)
 * @license GPLv3
 * @version 3.0.0
 * @modified 2020. 10. 20.
 */
if (defined('__IM__') == false) exit;
?>
<script>
new Ext.form.Panel({
	id:"ModuleConfigForm",
	border:false,
	bodyPadding:"10 10 5 10",
	width:500,
	fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:false},
	items:[
		new Ext.form.FieldSet({
			title:Board.getText("admin/configs/form/point_setting"),
			collapsible:true,
			collapsed:false,
			items:[
				new Ext.form.FieldContainer({
					margin:"0 0 0 105",
					layout:"hbox",
					items:[
						new Ext.form.DisplayField({
							value:Board.getText("admin/configs/form/point"),
							flex:1
						}),
						new Ext.form.DisplayField({
							value:Board.getText("admin/configs/form/exp"),
							margin:"0 0 0 5",
							flex:1
						})
					]
				}),
				new Ext.form.FieldContainer({
					fieldLabel:Board.getText("admin/configs/form/post_write"),
					layout:"hbox",
					items:[
						new Ext.form.NumberField({
							name:"post_point",
							flex:1
						}),
						new Ext.form.NumberField({
							name:"post_exp",
							margin:"0 0 0 5",
							flex:1
						})
					]
				}),
				new Ext.form.FieldContainer({
					fieldLabel:Board.getText("admin/configs/form/ment_write"),
					layout:"hbox",
					items:[
						new Ext.form.NumberField({
							name:"ment_point",
							flex:1
						}),
						new Ext.form.NumberField({
							name:"ment_exp",
							margin:"0 0 0 5",
							flex:1
						})
					]
				}),
				new Ext.form.FieldContainer({
					fieldLabel:Board.getText("admin/configs/form/vote"),
					layout:"hbox",
					items:[
						new Ext.form.NumberField({
							name:"vote_point",
							flex:1
						}),
						new Ext.form.NumberField({
							name:"vote_exp",
							margin:"0 0 0 5",
							flex:1
						})
					]
				}),
				new Ext.form.FieldContainer({
					fieldLabel:Board.getText("admin/configs/form/voted"),
					layout:"hbox",
					items:[
						new Ext.form.NumberField({
							name:"voted_point",
							flex:1
						}),
						new Ext.form.NumberField({
							name:"voted_exp",
							margin:"0 0 0 5",
							flex:1
						})
					]
				})
			]
		})
	]
});
</script>