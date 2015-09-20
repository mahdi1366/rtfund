<script type="text/javascript">
//---------------------------
// programmer:	Jafarkhani
// create Date:	94.06
//---------------------------

FGR_Form.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function FGR_Form()
{
	this.formPanel = new Ext.form.Panel({
		renderTo: this.get("newForm"),                  
		frame: true,
		title: 'اطلاعات فرم',
		bodyPadding: ' 10 10 12 10',
		width:500,
		
		items: [{
				xtype:'textfield',
				fieldLabel: 'عنوان فرم',
				name: 'FormName',
				anchor : "100%",
				allowBlank : false	
			},{
				xtype : "container",
				layout : "hbox",
				anchor : "100%",
				items :[{
					xtype:'filefield',
					fieldLabel: 'فایل فرم',
					name: 'attach',
					width : 440
				},{
					xtype : "button",
					iconCls : 'info',
					listeners : {
						render : function(){
							new Ext.ToolTip({
								target: this.getEl(),
								html : "فایل فرم باید به صورت یک فایل html بوده و فاقد تگهای ابتدایی از جمله " +
									"html ، head ، body و غیره باشد. در مکان هایی از فایل که اجزاء فرم باید قرار گیرند ، کد جزء را بین دو علامت # قرار دهید." + 
									"به این ترتیب مقدار آن جزء در آن مکان قرار می گیرد.",
								title: 'توجه',
								autoHide: false,
								closable: true
							})
						}
					}
				}]
			},{
				xtype : "combo",
				fieldLabel: 'آیتم وابسته',
				name: 'reference',
				store: new Ext.data.SimpleStore({
					fields : ['id','title'],
					data : [ 
						['loan', 'وام']
					]
				}),   
				displayField: 'title',
				valueField: 'id',
				anchor : "100%"
			},{
				xtype : "hidden",
				name : "FormID"
			}
		],		
		buttons: [{
				text : "ذخیره",
				iconCls : "save",
				handler : function(){
					me = FGR_FormObject;
					
					me.formPanel.getForm().submit({
						clientValidation: true,
						isUpload : true,
						url :  me.address_prefix + 'form.data.php?task=formSave',
						method : "POST",
					
						success : function(form,action){
							if(action.result.success)
							{
								FGR_FormObject.grid.getStore().load();
							}
							else
							{
								alert("عملیات مورد نظر با شکست مواجه شد.");
							}
							FGR_FormObject.formPanel.hide();
						}
					});
				}
			},{
				text : "انصراف",
				iconCls : "undo",
				handler : function(){
					FGR_FormObject.formPanel.hide();
				}
			}]
	});
	this.formPanel.hide();
	
}

FGR_FormObject = new FGR_Form();

FGR_Form.FileRender = function(v,p,record)
{
	if(v == 'NO')
		return "";
	return "<div align='center' title='فایل' class='attach' "+
		"onclick='window.open(\"/attachment/office/forms/" + record.data.FormID + ".html\");' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

FGR_Form.editRender = function()
{
	return "<div align='center' title='ویرایش' class='edit' onclick='FGR_FormObject.LoadFormInfo();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

FGR_Form.prototype.LoadFormInfo = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
		
	this.formPanel.loadRecord(record);
	this.formPanel.show();
}

FGR_Form.deleteRender = function()
{
	return "<div align='center' title='حذف' class='remove' onclick='FGR_FormObject.DeleteForm();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

FGR_Form.prototype.DeleteForm = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟",function(btn){
		if(btn == "no")
			return;
		
		me = FGR_FormObject;
		var mask = new Ext.LoadMask(me.grid, {msg:'در حال ذخيره سازي...'});
		mask.show();
		
		Ext.Ajax.request({
		  	url : me.address_prefix + "form.data.php",
		  	method : "POST",
		  	params : {
		  		task : "formDelete",
		  		FormID : record.data.FormID
		  	},
		  	success : function(response)
		  	{
				mask.hide();
				var result = Ext.decode(response.responseText);
		  		if(!result.success)
		  		{
		  			Ext.MessageBox.alert("", result.data);
		  			return;
		  		}
		  		FGR_FormObject.grid.getStore().load();
		  	}	
		});
	});
}

FGR_Form.elementRender = function()
{
	return "<div align='center' title='اجزاء' class='info' onclick='FGR_FormObject.ElementWin();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

FGR_Form.workflowRender = function()
{
	return "<div align='center' title='تعریف گردش فرم' class='refresh' onclick='workflowInfo();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

//........................................................

FGR_Form.prototype.ElementWin = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	this.ElementsGrid.getStore().proxy.extraParams = {
		FormID : record.data.FormID
	};
	
	if(!this.elementsWin)
	{
		this.elementsWin = new Ext.window.Window({
			autoScroll : true,
			width : 300,
			modal : true,
			title : "اجزای فرم",
			closeAction : "hide",
			items : new Ext.form.Panel({
				plain: true,
				border: 0,
				bodyPadding: 5,
				items : [this.ElementsGrid]
			})
		});
	}
	
	if(this.ElementsGrid.rendered)
		this.ElementsGrid.getStore().load();
	
	this.elementsWin.show();
}

FGR_Form.deleteElementRender = function()
{
	return "<div align='center' title='حذف' class='remove' onclick='FGR_FormObject.DeleteElement();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

FGR_Form.prototype.AddElement = function()
{
	var modelClass = this.ElementsGrid.getStore().model;
	var record = new modelClass({
		ElementID : "",
		ElementTitle : ""
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

FGR_Form.prototype.DeleteElement = function()
{
	var record = this.ElementsGrid.getSelectionModel().getLastSelected();
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = FGR_FormObject;
		var mask = new Ext.LoadMask(me.ElementsGrid, {msg:'در حال ذخيره سازي...'});
		mask.show();
		
		Ext.Ajax.request({
		  	url : me.address_prefix + "form.data.php",
		  	method : "POST",
		  	params : {
		  		task : "DeleteElement",
		  		ElementID : record.data.ElementID
		  	},
		  	success : function(response,o)
		  	{
				mask.hide();
		  		FGR_FormObject.ElementsGrid.getStore().load();
		  	}	
		});
	});
	
}





FGR_Form.prototype.workflowInfo = function()
{
	var record = dg_grid.selModel.getSelected();
	OpenPage("../formGenerator/workflow.php?FormID=" + record.data.FormID);
}

FGR_Form.prototype.LoadInfo = function(mode)
{
	this.formPanel.show();
	
	if(mode == "new")
		this.formPanel.getForm().reset();
	else
	{
		var record = this.grid.getSelectionModel().getLastSelected();
		this.formPanel.loadRecord(record);
	}
}

FGR_Form.prototype.RemoveFile = function()
{
	Ext.Ajax.request({
	  	url : "../formGenerator/form.data.php",
	  	method : "POST",
	  	params : {
	  		task : "RemoveFile",
	  		FormID: document.getElementById("FormID").value,
	  		FileType: document.getElementById("FileType").value
	  		},
	  	
	  	success : function(response){
  			dg_store.reload();
  			document.getElementById("newForm").style.display = "none";
	  	}		  	
	});
}

FGR_Form.referenceRender = function(value)
{
	switch(value)
	{
		case "devotions": return "موقوفه";
		case "states": return "رقبه";
		case "rents": return "اجاره";
	}
}

</script>