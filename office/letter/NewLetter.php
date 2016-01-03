<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.10
//-----------------------------
require_once '../header.inc.php';
require_once inc_dataGrid;

$LetterID = !empty($_POST["LetterID"]) ? $_POST["LetterID"] : "";

?>

<script>

Letter.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	LetterID : '<?= $LetterID ?>',
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function Letter(){
	 /*new ImageViewer({
			renderTo : this.get("mainForm"),
			width : 600,
			height : 600,
			src: '../xx.jpg'
		});*/
	this.BuildForms();
	if(this.LetterID > 0)
	{
		this.mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال بارگذاری...'});
		this.mask.show();

		this.LoadLetter();
	}
}

Letter.prototype.LoadLetter = function(){
		
	this.store = new Ext.data.Store({
		proxy : {
			type: 'jsonp',
			url: this.address_prefix + "letter.data.php?task=SelectLetter&LetterID=" + this.LetterID,
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields : ["LetterID","LetterType","LetterTitle","SubjectID","summary","context"],
		autoLoad : true,
		listeners : {
			load : function(){
				me = LetterObject;
				//..........................................................
				record = this.getAt(0);
				me.letterPanel.loadRecord(record);
				
				me.letterPanel.down("[itemId=pagesView]").getStore().proxy.extraParams = {
					LetterID : record.data.LetterID
				};
				me.letterPanel.down("[itemId=pagesView]").getStore().load();
				
				CKEDITOR.on('instanceReady', function( ev ) {
					if(LetterObject.LetterID > 0)
					{
						ev.editor.setData(record.data.context);
						LetterObject.mask.hide();
					}					
				});				
			}
		}
	});
}

Letter.prototype.BuildForms = function(){
	
	this.letterPanel = new Ext.form.FormPanel({
		renderTo : this.get("mainForm"),
		title : "مشخصات نامه",
		frame : true,
		height : 540,
		layout : {
			type : "table",
			columns : 2
		},
		defaults : {
			labelWidth : 60,
			width : 350
		},
		width: 780,
		items : [{
			xtype :"container",
			layout : "hbox",
			items : [{
				xtype : "radio",
				fieldLabel : "نوع نامه",
				labelWidth : 60,
				boxLabel: 'نامه داخلی',
				name: 'LetterType',
				style : "margin-right : 20px",
				checked : true,
				inputValue: 'INNER'
			},{
				xtype : "radio",
				boxLabel: 'نامه صادره',
				name: 'LetterType',
				inputValue: 'OUTER'
			}]
		},{
			xtype : "textarea",
			fieldLabel : "چکیده",
			name : "summary",
			width : 400,
			rows : 3,
			rowspan : 3
		},{
			xtype : "textfield",
			name : "LetterTitle",
			fieldLabel : "عنوان نامه",
			allowBlank : false
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + 'letter.data.php?task=selectSubjects',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['InfoID','InfoDesc'],
				autoLoad : true					
			}),
			fieldLabel : "موضوع نامه",
			displayField : "InfoDesc",
			valueField : "InfoID",
			name : "SubjectID"
		},{
			xtype : "tabpanel",
			colspan : 2,
			plain: true,
			height : 400,
			width : 760,
			items :[{
				title : "نامه تایپی",
				html : "<div id='Div_context'></div>"
			},{
				title : "نامه تصویری",
				style : "margin-top:10px",
				items : [{
					xtype : "textfield",
					fieldLabel : "عنوان صفحه",
					name : "PageTitle",
					width : 300
				},{
					xtype : "container",
					layout : "hbox",
					items : [{
						xtype : "filefield",
						width : 300,
						fieldLabel : "انتخاب تصویر",
						name : "PageFile"
					},{
						xtype : "button",
						iconCls : "add",
						handler : function(){
							if(this.up('panel').down("[name=PageTitle]").getValue() == "" || 
								this.up('panel').down("[name=PageFile]").getValue() == "")
							{
								Ext.MessageBox.alert("","ورود عنوان صفحه و فایل الزامی است");
								return;
							}
							LetterObject.SaveLetter();
						}
					}]
				},new Ext.Panel({
					frame: true,
					width : 730,
					height : 320,
					style : "margin:10px",
					items : new Ext.view.View({		
						itemId : "pagesView",
						store: new Ext.data.SimpleStore({
							proxy: {
								type: 'jsonp',
								url: this.address_prefix + 'letter.data.php?task=selectLetterPages',
								reader: {root: 'rows',totalProperty: 'totalCount'}
							},
							fields : ['DocumentID','DocDesc']
						}),
						tpl: [
							'<tpl for=".">',
								'<div style="float: right;padding:5px;width:100px;margin:10px">',
								'<div class="thumb"><img style="width:100px;height:100px" ',
									'src="{url}" title="{DocumentTitle}"></div>',
								'<div style="width:100%;text-align:center">{DocDesc}</div></div>',
							'</tpl>',
							'<div class="x-clear"></div>'
						],
						trackOver: true,
						overItemCls: 'x-item-over'
					}) 
				})]
			}]
		}],
		buttons :[{
			text : "ذخیره",
			iconCls : "save",
			handler : function(){
				LetterObject.SaveLetter();
			}
		}]
	});
	
	if ( CKEDITOR.env.ie && CKEDITOR.env.version < 9 )
		CKEDITOR.tools.enableHtml5Elements( document );

	CKEDITOR.config.width = 'auto';
	CKEDITOR.config.height = 270;
	CKEDITOR.config.autoGrow_minHeight = 200;
	
	CKEDITOR.document.getById( 'Div_context' );
	CKEDITOR.replace( 'Div_context' );	
}

LetterObject = new Letter();

Letter.prototype.SaveLetter = function(){

	mask = new Ext.LoadMask(this.letterPanel, {msg:'در حال ذخيره سازي...'});
	mask.show();  
	
	this.letterPanel.getForm().submit({
		clientValidation: true,
		url: this.address_prefix + 'letter.data.php?task=SaveLetter' , 
		method: "POST",
		params : {
			LetterID : this.LetterID,
			context : CKEDITOR.instances.Div_context.getData()
		},
		
		success : function(form,action){
			mask.hide();
			LetterObject.LetterID = action.result.data;
			LetterObject.letterPanel.down("[name=PageTitle]").setValue();
			LetterObject.letterPanel.down("[itemId=pagesView]").getStore().proxy.extraParams = {
				LetterID : LetterObject.LetterID
			};
			LetterObject.letterPanel.down("[itemId=pagesView]").getStore().load();
		},
		failure : function(){
			mask.hide();
		}
	});
}

</script>
<script>
	



	</script>
<center>
	<br>
	<div id="mainForm"></div>
	<div id="div_grid"></div>
</center>