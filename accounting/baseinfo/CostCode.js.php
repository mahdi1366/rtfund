<script type="text/javascript">
//-------------------------
// programmer:	Jafarkhani
// Create Date:	94.06
//-------------------------

CostCode.prototype={
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : "<?= $js_prefix_address ?>",

	levelCount : 3,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function CostCode(){

	var levelCombos = new Array({
		xtype : "hiddenfield",
		name : "CostID"
	});
	this.levelTitles = new Array("حساب کل","معین","جزءمعین");
	for(var i=0; i < this.levelCount; i++)
	{
		levelCombos.push({
			xtype : "container",
			layout : "hbox",
			items : [
				new Ext.form.ComboBox({
					store: new Ext.data.Store({
						fields: ['BlockID','BlockCode','BlockDesc','LevelID'],
						proxy : {
							type : 'jsonp',    	    
							url : this.address_prefix + "baseinfo.data.php?task=SelectBlocks&level=" + (i+1),
							reader : {root: 'rows',totalProperty: 'totalCount'}
						}
					}),
					name : 'level' + (i+1),
					valueField : 'BlockID',
					displayField : 'BlockDesc',
					tpl: new Ext.XTemplate(
					'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct">',
					'<td height="23px">کد</td>',
					'<td>عنوان</td></tr>',
					'<tpl for=".">',
					'<tr class="x-boundlist-item" style="border-left:0;border-right:0;">',
					'<td style="border-left:0;border-right:0" class="search-item">{BlockCode}</td>',
					'<td style="border-left:0;border-right:0" class="search-item">{BlockDesc}</td></tr>',
					'</tpl>',
					'</table>'),
					typeAhead: false,
					fieldLabel : this.levelTitles[i],
					pageSize : 20,
					queryDelay:0,
					width: 500
				})
				,{
					xtype : "button",
					iconCls : "add",
					itemId : "btn_level_" + (i+1),
					hidden : i>0 ? false : true,
					handler : function(){CostCodeObj.AddNewBlock(this);}
				}]
		});
	}
	
	levelCombos.push({
		xtype : "combo",
		store: new Ext.data.Store({
			fields:["InfoID","InfoDesc"],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectTafsiliGroups',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			autoLoad : true
		}),
		name : "TafsiliType",
		fieldLabel : "گروه تفصیلی",
		typeAhead: false,
		style : "margin-left : 50px",
		queryMode : "local",
		valueField : "InfoID",
		displayField : "InfoDesc"
	},{
		xtype : "combo",
		store: new Ext.data.Store({
			fields:["InfoID","InfoDesc"],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + '../baseinfo/baseinfo.data.php?task=SelectTafsiliGroups',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			autoLoad : true
		}),
		typeAhead: false,
		name : "TafsiliType2",
		fieldLabel : "گروه تفصیلی2",
		queryMode : "local",
		style : "margin-left : 50px",
		valueField : "InfoID",
		displayField : "InfoDesc"
	},{
		xtype : "checkbox",
		inputValue : "YES",
		boxLabel : "این حساب قابل بلوکه شدن است",
		name : "IsBlockable"
	});

	this.formPanel = new Ext.form.Panel({
		applyTo: this.get("mainform"),
		collapsible: true,
		bodyPadding: '5 5 0',
		width: 810,
		frame : true,
		fieldDefaults: {
			msgTarget: 'side',
			labelWidth:100
		},
		defaults: {
			anchor: '100%'
		},
		items: [{
				xtype:'fieldset',
				itemId:'level',
				title: 'تعیین سطوح کد هزینه',
				width: 570,
				collapsible: true,
				defaultType: 'textfield',
				disabled : false	,
				layout: 'anchor',
				defaults: {
					anchor: '100%'
				},              
				items :	levelCombos
			},{
				xtype : 'fieldset',
				width: 570,
				html : "-اگر در هر جزء کد حساب آیتم مورد نظرتان"
				+" وجود نداشت می توانید با زدن دکمه ایجاد آنرا ایجاد کنید."
			},{
				buttons: [{
						text : "ذخیره",
						iconCls : "save",
						handler: function(){

							//.......... check completion of each pre level ..........
							mask = new Ext.LoadMask(CostCodeObj.formPanel, {msg:'در حال ذخيره سازي...'});
							mask.show();
							
							for(var i=0; i < CostCodeObj.levelCount; i++)
							{
								if(CostCodeObj.formPanel.down('[name=level' + (i+1) + "]").getValue() == null)
									if(i+2 <= CostCodeObj.levelCount && CostCodeObj.formPanel.down('[name=level' + (i+2) + "]").getValue() != null)
								{
									alert("تکمیل " + CostCodeObj.levelTitles[i] + " با توجه به انتخاب " + 
										CostCodeObj.levelTitles[i+1] + " الزامی است");
									return;
								}
							}

							CostCodeObj.formPanel.getForm().submit({
								url:  CostCodeObj.address_prefix + 'baseinfo.data.php?task=SaveCostCode',
								method : "POST",
								clientValidation : true,

								success : function(form,action){
									CostCodeObj.grid.getStore().load();
									CostCodeObj.formPanel.hide();
									mask.hide();
								}
								,
								failure : function(form,action){                                  
									alert(action.result.data);
									mask.hide();
								}
							});								
						}
					},{
						text : "انصراف",
						iconCls : "undo",
						handler : function(){
							CostCodeObj.formPanel.hide();
						}
					}]
			}]	

	});
	this.formPanel.hide();
	this.afterLoad();
}

CostCode.prototype.BeforeSaveCost = function(EditMode){

	this.formPanel.getForm().reset();
	this.formPanel.show();
	
	if(EditMode)
	{
		mask = new Ext.LoadMask(this.formPanel, {msg:'در حال ذخيره سازي...'});
		mask.show();
		var record = this.grid.getSelectionModel().getLastSelected();
		this.formPanel.down("[name=CostID]").setValue(record.data.CostID);
		this.formPanel.down("[name=TafsiliType]").setValue(record.data.TafsiliType);
		this.formPanel.down("[name=TafsiliType2]").setValue(record.data.TafsiliType2);
		this.formPanel.down("[name=IsBlockable]").setValue(record.data.IsBlockable =="YES" ? true : false);
		
		for(var i=0; i < CostCodeObj.levelCount; i++)
		{
			if(record.get("level" + (i+1)) == null)
				continue;
			eval("R"+(i+1)+" = this.formPanel.down('[name=level"+(i+1)+"]').getStore().load({ " +
				"params : {BlockID : record.data.level"+(i+1)+"}, " +
				"callback : function(records){"+
					"CostCodeObj.formPanel.down('[name=level"+(i+1)+"]').select(records[0]);}" +
			"});");			
		}
		LoadStr = "1==1";
		for(var i=0; i < CostCodeObj.levelCount; i++)
			LoadStr += " || !R"+(i+1)+".isLoading()";
		
		var t = setInterval(function(){
			eval("cond = " + LoadStr);
			if(cond)
			{
				clearInterval(t);
				mask.hide();
			}
		}, 1000);
	}	
}

CostCode.RemoveCost = function(value,p,record){
	
	if(record.data.IsActive == "YES")
		return  "<div  title='حذف اطلاعات' class='remove' onclick='CostCodeObj.RemoveCosts();' " +
		"style='float:left;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:50%;height:16'></div>";	
	else
		return  "<div  title='فعال سازی اطلاعات' class='undo' onclick='CostCodeObj.ActiveCost();' " +
		"style='float:left;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:50%;height:16'></div>";	
}

CostCode.EditCost = function(value,p,record){
	
	if(record.data.IsActive == "YES")
		return  "<div  title='ویرایش اطلاعات' class='edit' onclick='CostCodeObj.BeforeSaveCost(true);' " +
		"style='float:left;background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:50%;height:16'></div>";	
}

CostCode.prototype.RemoveCosts = function(){

	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟",function(btn){
		if(btn == "no")
			return;
		
		me = CostCodeObj;
		var record = me.grid.getSelectionModel().getLastSelected();

		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخيره سازي...'});
		mask.show();

		Ext.Ajax.request({
			params: {
				task: 'DeleteCostCode',
				CId: record.data.CostID
			},
			url:  me.address_prefix +'baseinfo.data.php',
			method: 'POST',
			success: function(response){
				mask.hide();
				var st = Ext.decode(response.responseText);
				if(st.data == "conflict")
					alert('این آیتم در جای دیگری استفاده شده و قابل حذف نمی باشد.');
				else
					CostCodeObj.grid.getStore().load();
			},
			failure: function(){}
		});
	});
}

CostCode.prototype.PrintCost = function()
{        
	window.open(this.address_prefix + "PrintCostCode.php");
}

CostCode.prototype.AddNewBlock = function(btn){

	var levelID = btn.itemId.replace("btn_level_", "");

	this.BlockWindow = new Ext.window.Window({
		width : this.levelCount00,
		modal : true,
		title : "ایجاد جزء حساب",
		closeAction : "hide",
		items : new Ext.form.Panel({
			plain: true,
			border: 0,
			bodyPadding: 5,
			items : [{
					xtype : "numberfield",
					fieldLabel : "کد",
					beforeLabelTextTpl: required,
					allowBlank : false,
					name : "BlockCode",
					hideTrigger : true
				},{
					xtype : "textfield",
					fieldLabel : "عنوان",
					beforeLabelTextTpl: required,
					allowBlank : false,
					name : "BlockDesc",
					hideTrigger : true
				}],
			buttons : [{
					text : "ایجاد",
					iconCls : "add",
					handler : function(){

						mask = new Ext.LoadMask(Ext.getCmp(CostCodeObj.TabID), {msg:'در حال ذخيره سازي...'});
						mask.show();

						var modelClass = CostCodeObj.formPanel.down('[name=level' + levelID + "]").getStore().model;
						var record = new modelClass({
							BlockID: null,
							LevelID: levelID,
							BlockDesc: this.up('form').down("[name=BlockDesc]").getValue(),
							BlockCode: this.up('form').down("[name=BlockCode]").getValue(),
							essence: null
						});
						this.up('form').getForm().submit({
							clientValidation: true,
							url: CostCodeObj.address_prefix + 'baseinfo.data.php?task=SaveBlockData',
							method : "POST",
							params : {
								record : Ext.encode(record.data)
							},
							success : function(form,action){

								CostCodeObj.formPanel.down('[name=level' + levelID + "]").getStore().load({
									params : {
										BlockID : action.result.data
									},
									callback : function(){
										CostCodeObj.formPanel.down('[name=level' + levelID + "]").
											select(action.result.data);
										CostCodeObj.BlockWindow.hide();
										mask.hide();
									}
								});
							},
							failure : function(form,action){
								alert(action.result.data);
							}
						});
					}
				},{
					text : "بازگشت",
					iconCls : "undo",
					handler : function(){
						this.up('window').hide();
					}
				}]
		})
	});
	Ext.Ajax.request({
		url : this.address_prefix + "baseinfo.data.php?task=getLastID",
		methos : "post",
		params : {
			levelID : levelID
		},
		success : function(response)
		{
			var sd = Ext.decode(response.responseText);
			CostCodeObj.BlockWindow.down("[name=BlockCode]").setValue(sd.data);
		}
	});

	Ext.getCmp(this.TabID).add(this.BlockWindow);
	this.BlockWindow.show();
}

CostCode.prototype.ActiveCost = function(){

	Ext.MessageBox.confirm("","آیا مایل به فعال شدن مجدد کد حساب می باشید؟",function(btn){
		if(btn == "no")
			return;
		
		me = CostCodeObj;
		var record = me.grid.getSelectionModel().getLastSelected();

		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخيره سازي...'});
		mask.show();

		Ext.Ajax.request({
			params: {
				task: 'ActiveCostCode',
				CostID: record.data.CostID
			},
			url:  me.address_prefix +'baseinfo.data.php',
			method: 'POST',
			success: function(response){
				mask.hide();
				var result = Ext.decode(response.responseText);
				if(result.success)
					CostCodeObj.grid.getStore().load();
				else if(result.data != "")
					Ext.MessageBox.alert("",result.data);
				else
					Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه گردید");
					
			},
			failure: function(){}
		});
	});
}


</script>
