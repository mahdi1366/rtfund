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
					
					var mask = new Ext.LoadMask(me.formPanel, {msg:'در حال ذخيره سازي...'});
					mask.show();
					
					me.formPanel.getForm().submit({
						clientValidation: true,
						isUpload : true,
						url :  me.address_prefix + 'form.data.php?task=formSave',
						method : "POST",
					
						success : function(form,action){
							mask.hide();
							FGR_FormObject.grid.getStore().load();
							FGR_FormObject.formPanel.hide();
						},
						failure : function(form,action){
							mask.hide();
							Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد.");
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
	
	this.PostCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			proxy:{
				type: 'jsonp',
				url: this.address_prefix + '../domain.data.php?task=SelectPosts',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			autoLoad : true,
			fields :  ['PostID','PostName']
		}),
		allowBlank : false,
		displayField: 'PostName',
		valueField : "PostID",
		queryMode : 'local'
	});
	
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

FGR_Form.stepsRender = function()
{
	return "<div align='center' title='تعریف گردش فرم' class='refresh' onclick='FGR_FormObject.StepsWin();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

FGR_Form.prototype.Return = function(el)
{
	eval("this." + el + '.hide();');	
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
			width : 780,
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
	var record = this.grid.getSelectionModel().getLastSelected();
	
	var modelClass = this.ElementsGrid.getStore().model;
	var record = new modelClass({
		FormID : record.data.FormID,
		ElementID : "",
		ElementTitle : ""
	});

	this.ElementsGrid.plugins[0].cancelEdit();
	this.ElementsGrid.getStore().insert(0, record);
	this.ElementsGrid.plugins[0].startEdit(0, 0);
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

FGR_Form.prototype.SaveElement = function(store,record){

	mask = new Ext.LoadMask(this.ElementsGrid, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'SaveElement',
			record : Ext.encode(record.data)
		},
		url: this.address_prefix + 'form.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				FGR_FormObject.ElementsGrid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

//........................................................

FGR_Form.prototype.StepsWin = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	this.StepsGrid.getStore().proxy.extraParams = {
		FormID : record.data.FormID
	};
	
	if(!this.stepsWin)
	{
		this.stepsWin = new Ext.window.Window({
			autoScroll : true,
			width : 780,
			modal : true,
			title : "اجزای فرم",
			closeAction : "hide",
			items : new Ext.form.Panel({
				plain: true,
				border: 0,
				bodyPadding: 5,
				items : [this.StepsGrid]
			})
		});
	}
	
	if(this.StepsGrid.rendered)
		this.StepsGrid.getStore().load();
	
	this.stepsWin.show();
}

FGR_Form.DeleteStepRender = function()
{
	return "<div align='center' title='حذف' class='remove' onclick='FGR_FormObject.DeleteStep();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

FGR_Form.prototype.AddStep = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	
	var modelClass = this.StepsGrid.getStore().model;
	var record = new modelClass({
		FormID : record.data.FormID,
		ElementID : "",
		ElementTitle : ""
	});

	this.StepsGrid.plugins[0].cancelEdit();
	this.StepsGrid.getStore().insert(0, record);
	this.StepsGrid.plugins[0].startEdit(0, 0);
}

FGR_Form.prototype.DeleteStep = function()
{
	var record = this.StepsGrid.getSelectionModel().getLastSelected();
	
	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟", function(btn){
		if(btn == "no")
			return;
		
		me = FGR_FormObject;
		var mask = new Ext.LoadMask(me.StepsGrid, {msg:'در حال ذخيره سازي...'});
		mask.show();
		
		Ext.Ajax.request({
		  	url : me.address_prefix + "form.data.php",
		  	method : "POST",
		  	params : {
		  		task : "DeleteStep",
		  		StepID : record.data.StepID
		  	},
		  	success : function(response,o)
		  	{
				mask.hide();
		  		FGR_FormObject.StepsGrid.getStore().load();
		  	}	
		});
	});
	
}

FGR_Form.prototype.SaveStep = function(store,record){

	mask = new Ext.LoadMask(this.StepsGrid, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'SaveSteps',
			record : Ext.encode(record.data)
		},
		url: this.address_prefix + 'form.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				FGR_FormObject.StepsGrid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

FGR_Form.UPRender = function(v,p,r)
{
	if(r.data.ordering == 1)
		return "";
	return "<div align='center' title='حرکت مرحله به بالا' class='up' onclick='FGR_FormObject.changeLevel(\"up\");' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

FGR_Form.DOWNRender= function(v,p,r)
{
	store = FGR_FormObject.StepsGrid.getStore();
	if(store.getAt(store.getCount()-1).data.StepID == r.data.StepID)
		return "";
	return "<div align='center' title='حرکت مرحله به پایین' class='down' onclick='FGR_FormObject.changeLevel(\"down\");' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

FGR_Form.prototype.changeLevel = function(direction)
{
	var record = this.StepsGrid.getSelectionModel().getLastSelected();
	
	Ext.Ajax.request({
	  	url : this.address_prefix + "form.data.php",
	  	method : "POST",
	  	params : {
	  		task : "ChangeLevel",
	  		FormID : record.data.FormID,
	  		StepID : record.data.StepID,
			ordering : record.data.ordering,
	  		direction : direction
	  	},
	  	success : function()
	  	{
	  		FGR_FormObject.StepsGrid.getStore().load();
	  	}	
	});		
}

FGR_Form.prototype.StepsWin = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	this.StepsGrid.getStore().proxy.extraParams = {
		FormID : record.data.FormID
	};
	
	if(!this.stepsWin)
	{
		this.stepsWin = new Ext.window.Window({
			autoScroll : true,
			width : 780,
			modal : true,
			title : "اجزای فرم",
			closeAction : "hide",
			items : new Ext.form.Panel({
				plain: true,
				border: 0,
				bodyPadding: 5,
				items : [this.StepsGrid]
			})
		});
	}
	
	if(this.StepsGrid.rendered)
		this.StepsGrid.getStore().load();
	
	this.stepsWin.show();
}

</script>