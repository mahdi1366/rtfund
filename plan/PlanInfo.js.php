<script>
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.11
//-----------------------------
	
PlanInfo.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	PlanID : <?= $PlanID ?>,
	RequestRecord : null,
	User : '<?= $User ?>',
	
	GroupForms : {},

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function PlanInfo(){
	
	this.mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال بارگذاري...'});
	//this.mask.show();
			
	this.BuildForms();
	return;
	
	if(this.PlanID > 0)
		this.LoadPlanInfo();
		
	if(this.PlanID == 0)
	{
		this.CustomizeForm(null);
		this.mask.hide();
	}	
}

PlanInfo.prototype.BuildForms = function(){

	this.tree = new Ext.tree.Panel({
		store: new Ext.data.TreeStore({
			proxy: {
				type: 'ajax',
				url: this.address_prefix + 'plan.data.php?task=selectGroups'
			}					
		}),
		root: {id: 'src'},
		rootVisible: false,
		autoScroll : true,
		width : 798,
		height : 120,
		listeners : {
			itemclick : function(v,record){
				if(!record.data.leaf) return; 
				PlanInfoObject.LoadElements(record);
			},
			itemcontextmenu : function(view, record, item, index, e){
				PlanInfoObject.ShowMenu(view, record, item, index, e);
			}
		}
	});
	
	this.itemsPanel = new Ext.panel.Panel({
		bodyStyle : 'padding:4px;',
		height : 478,
		width: 798,
		autoScroll : true
	});

   this.MainPanel =  new Ext.panel.Panel({
		applyTo : this.get("mainForm"),
		width: 800,
		height : 600,
		items : [this.tree,this.itemsPanel],
		tbar : [{
			text : "مشاهده ردیف های دارای اطلاعات",
			iconCls : "list",
			enableToggle : true,
			handler : function(){
				PlanInfoObject.itemsPanel.items.each(function(item){item.hide();});
				PlanInfoObject.tree.getStore().load({
					params : {
						filled : this.pressed ? "true" : "false"
					}
				});
			}
		}]
	});	
} 

PlanInfoObject = new PlanInfo();

PlanInfo.prototype.LoadElements = function(record, season){

	var parentEl = this.itemsPanel;
	parentEl.items.each(function(item){item.hide();});
	
	mask = new Ext.LoadMask(parentEl, {msg:'در حال بارگذاري...'});
	mask.show();
	
	var frm = null;
	eval("frm = this.GroupForms.elem_" + record.data.id);
	if(frm == null)
	{
		eval("this.GroupForms.elem_" + record.data.id + " = new Array();");
		this.store = new Ext.data.Store({
			proxy:{
				type: 'jsonp',
				extraParams : {
					GroupID : record.data.id,
					PlanID : this.PlanID
				},
				url: this.address_prefix + "plan.data.php?task=SelectElements",
				reader: {root: 'rows',totalProperty: 'totalCount'}
			},
			fields : ["ElementID", "ParentID", "GroupID", "ElementTitle", "ElementType", 
				"properties", "EditorProperties", "ElementValue", "values"],
			autoLoad : true,
			listeners :{
				load : function(){
					PlanInfoObject.MakeElemForms(this , season);
					mask.hide();
				}
			}
		});
	}
	else
	{
		for(i=0; i<frm.length; i++)
			parentEl.down("[itemId=" + frm[i] + "]").show();
		mask.hide();
	}
	
} 

function merge(obj1,obj2){
    var obj3 = {};
    for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
    for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
    return obj3;
}

PlanInfo.prototype.MakeElemForms = function(store, season){

	var parentEl = this.itemsPanel;
	
	for(var i=0; i < store.getCount(); i++)
	{
		record = store.getAt(i);
		switch(record.data.ElementType)
		{
			case "panel" : 
				NewElement = {
					xtype : "form",
					frame : true,
					itemId : "element_" + record.data.ElementID,
					style : "margin-bottom:4px",
					buttons : [{
						text : "ذخیره",
						iconCls : "save",
						handler : function(){
							mask = new Ext.LoadMask(parentEl, {msg:'در حال ذخيره سازي...'});
							mask.show();    
							this.up('form').getForm().submit({
								url:  PlanInfoObject.address_prefix + 'plan.data.php?task=SavePlanItems',
								method: 'POST',
								params : {
									PlanID : PlanInfoObject.PlanID,
									ElementID : this.up('form').itemId.split("_")[1]
								},
								success: function(form,result){
									record = PlanInfoObject.tree.getSelectionModel().getSelection()[0];
									record.set("cls","filled");
									mask.hide();
								},
								failure: function(){}
							});
						}
					}]
				};
				break;
			//..................................................................
			case "grid" :
				
				var fields = new Array();
				var columns = [ {dataIndex : "RowID",hidden : true},
								{dataIndex : "PlanID",hidden : true},
								{dataIndex : "ElementID",hidden : true}];
				while(true)
				{
					i++;
					var sub_record = store.getAt(i);
					if(sub_record == null || sub_record.data.ParentID != record.data.ElementID)
					{
						i--;
						break;
					}
					var editor = {xtype : sub_record.data.ElementType};
					if(sub_record.data.ElementType == "combo")
					{
						arr = sub_record.data.values.split("#");
						data = [];
						for(j=0;j<arr.length;j++)
							data.push([ arr[j] ]);
						editor.store = new Ext.data.SimpleStore({
							fields : ['value'],
							data : data
						});
						editor.displayField = "value";
						editor.valueField = "value";
					}
					eval("editor = merge(editor,{" + sub_record.data.EditorProperties + "});");
					
					NewColumn = {
						menuDisabled : true,
						sortable : false,
						text : sub_record.data.ElementTitle,
						dataIndex : "element_" + sub_record.data.ElementID,
						editor : editor						
					};
					if(sub_record.data.ElementType == "currencyfield")
						NewColumn.renderer = Ext.util.Format.Money;
					if(sub_record.data.ElementType == "currencyfield" || 
						sub_record.data.ElementType == "numberfield")
						NewColumn.editor.hideTrigger = "true";
					
					
					eval("NewColumn = merge(NewColumn,{" + sub_record.data.properties + "});");
					columns.push(NewColumn);
					fields.push("element_" + sub_record.data.ElementID);
				}
				NewElement = {
					xtype : "grid",
					viewConfig: {
						stripeRows: true,
						enableTextSelection: true
					},
					tbar : [{
						text : "ایجاد ردیف",
						iconCls : "add",
						handler : function(){
							var grid = this.up('grid');
							var modelClass = grid.getStore().model;
							var record = new modelClass({
								RowID : null,
								PlanID : PlanInfoObject.PlanID,
								ElementID : grid.getStore().proxy.extraParams.ElementID
							});
							grid.plugins[0].cancelEdit();
							grid.getStore().insert(0, record);
							grid.plugins[0].startEdit(0, 0);
						}
					},'-',{
						text : "ویرایش ردیف",
						iconCls : "edit",
						handler : function(){
							var grid = this.up('grid');
							var record = grid.getSelectionModel().getLastSelected();
							if(record == null)
							{
								Ext.MessageBox.alert("","ابتدا ردیف مورد نظر را انتخاب کنید");
								return;
							}
							grid.plugins[0].startEdit(grid.getStore().indexOf(record),0);
						}
					},'-',{
						text : "حذف ردیف",
						iconCls : "remove",
						handler : function(){
							var grid = this.up('grid');
							var record = grid.getSelectionModel().getLastSelected();
							if(record == null)
							{
								Ext.MessageBox.alert("","ابتدا ردیف مورد نظر را انتخاب کنید");
								return;
							}
							
							Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟",function(btn){
								if(btn == "no")
									return;
								var mask = new Ext.LoadMask(parentEl, {msg:'در حال ذخيره سازي...'});
								mask.show();    
								Ext.Ajax.request({
									url:  PlanInfoObject.address_prefix + 'plan.data.php?task=DeletePlanItem',
									params:{
										RowID : record.data.RowID
									},
									method: 'POST',
									success: function(response,option){
										mask.hide();
										grid.getStore().load();
									},
									failure: function(){}
								});
							});
						}
					}],
					selType : 'rowmodel',
					plugins : [new Ext.grid.plugin.RowEditing()],
					scroll: 'vertical', 
					itemId : "element_" + record.data.ElementID,
					store : new Ext.data.Store({
						proxy:{
							type: 'jsonp',
							url: this.address_prefix + "plan.data.php?task=SelectPlanItems",
							reader: {root: 'rows',totalProperty: 'totalCount'},
							extraParams : {
								PlanID : this.PlanID,
								ElementID : record.data.ElementID
							}							
						},
						fields : ["RowID", "PlanID", "ElementID"].concat(fields),
						autoLoad : true,
						listeners : {
							update : function(store,record){
								mask = new Ext.LoadMask(parentEl, {msg:'در حال ذخيره سازي...'});
								mask.show();    
								Ext.Ajax.request({
									url:  PlanInfoObject.address_prefix + 'plan.data.php?task=SavePlanItems',
									params:{
										record : Ext.encode(record.data)
									},
									method: 'POST',
									success: function(response,option){
										mask.hide();
										store.load();
									},
									failure: function(){}
								});
								return true;
							}
						}
					}),
					columns: columns
				};
				break;
			//..................................................................
			case "radio" :
				var items = new Array();
				values = record.data.values.split('#');
				for(j=0;j<values.length;j++)
					items.push({
						boxLabel : values[j],
						name : "element_" + record.data.ElementID,
						inputValue : j,
						checked : record.data.ElementValue == j ? true : false
					});
				NewElement = {
					xtype : "radiogroup",
					fieldLabel : record.data.ElementTitle,
					itemId : "element_" + record.data.ElementID,
					items : items,					
					columns: values.length
				};
				break;
			case "displayfield":
				NewElement = {
					xtype : record.data.ElementType,
					fieldLabel : record.data.ElementTitle,
					value : record.data.values.replace(/\n/g,"<br>"),
					fieldCls : "desc"
				};
				break;				
			default : 
				NewElement = {
					xtype : record.data.ElementType,
					fieldLabel : record.data.ElementTitle,
					itemId : "element_" + record.data.ElementID,
					name : "element_" + record.data.ElementID,
					value : record.data.ElementValue
				};
		}
		
		eval("NewElement = merge(NewElement,{" + record.data.properties + "});");
		
		if(record.data.ParentID == 0)
		{
			eval("PlanInfoObject.GroupForms.elem_" + record.data.GroupID + ".push('element_" + record.data.ElementID + "');");
			parentEl.add(NewElement);
		}
		else
		{
			var parent = this.itemsPanel.down("[itemId=element_" + record.data.ParentID + "]");
			parent.add(NewElement);
		}
	}
}

PlanInfo.prototype.ShowMenu = function(view, record, item, index, e)
{
	e.stopEvent();
	e.preventDefault();
	view.select(index);

	this.Menu = new Ext.menu.Menu();

	if(record.data.id == "src" || !record.isLeaf())
		return;
	
	this.Menu.add({
		text: 'تایید اطلاعات',
		iconCls: 'tick',
		handler : function(){PlanInfoObject.BeforeSurveyGroup('CONFIRM');}
	},{
		text: 'رد اطلاعات',
		iconCls: 'cross',
		handler : function(){PlanInfoObject.BeforeSurveyGroup('REJECT');}
	});
	
	var coords = e.getXY();
	this.Menu.showAt([coords[0]-120, coords[1]]);
}

PlanInfo.prototype.BeforeSurveyGroup = function(mode){
	
	if(mode == "CONFIRM")
	{
		Ext.MessageBox.confirm("","آیا مایل به تایید می باشید؟", function(btn){
			if(btn == "no")
				return;
			
			PlanInfoObject.SurveyGroup(mode, "");
		});
		return;
	}
	if(!this.commentWin)
	{
		this.commentWin = new Ext.window.Window({
			width : 412,
			height : 198,
			modal : true,
			title : "توضیحات رد اطلاعات",
			bodyStyle : "background-color:white",
			items : [{
				xtype : "textarea",
				width : 400,
				rows : 8,
				name : "ActDesc"
			}],
			closeAction : "hide",
			buttons : [{
				text : "رد اطلاعات",				
				iconCls : "cross",
				itemId : "btn_reject"
			},{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.commentWin);
	}
	this.commentWin.down("[itemId=btn_reject]").setHandler(function(){
		PlanInfoObject.SurveyGroup('REJECT', 
			this.up('window').down("[name=ActDesc]").getValue());});
	this.commentWin.show();
	this.commentWin.center();
}

PlanInfo.prototype.SurveyGroup = function(mode, ActDesc){
	
	mask = new Ext.LoadMask(this.itemPanel, {msg:'در حال بارگذاري...'});
	mask.show();
	
	Ext.Ajax.request({
		methos : "post",
		url : this.address_prefix + "request.data.php",
		params : {
			task : "ChangeRequestStatus",
			PlanID : this.PlanID,
			mode : mode,
			ActDesc : ActDesc
		},
		
		success : function(){
			
			PlanInfoObject.LoadPlanInfo();
			if(PlanInfoObject.commentWin)
				PlanInfoObject.commentWin.hide();
		}
	});
}







PlanInfo.prototype.LoadPlanInfo = function(){
	
	this.store = new Ext.data.Store({
		proxy:{
			type: 'jsonp',
			url: this.address_prefix + "request.data.php?task=SelectAllRequests&PlanID=" + this.PlanID,
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields : ["PlanID","BranchID","BranchName","ReqPersonID","ReqPersonRole","ReqFullname","LoanPersonID",
					"LoanFullname","ReqDate","ReqAmount","ReqDetails","BorrowerDesc","BorrowerID",
					"guarantees","AgentGuarantee","StatusID","DocumentDesc","SupportPersonID"],
		autoLoad : true,
		listeners :{
			load : function(){
				
				me = PlanInfoObject;
				
				if(me.RequestRecord != null)
				{
					me.RequestRecord = this.getAt(0);
					me.CustomizeForm(me.RequestRecord);
					me.grid.getStore().load();
					me.mask.hide();
					return;
				}				
				//..........................................................
				record = this.getAt(0);
				me.RequestRecord = record;
				me.companyPanel.loadRecord(record);
				//..........................................................
				if(record.data.AgentGuarantee == "YES")
					me.companyPanel.down("[name=AgentGuarantee]").setValue(true);
				if(record.data.guarantees != null)
				{
					arr = record.data.guarantees.split(",");
					for(i=0; i<arr.length; i++)
						if(arr[i] != "")
							me.companyPanel.down("[name=guarantee_" + arr[i] + "]").setValue(true);
				}
				//..........................................................
				var R1 = false;
				if(record.data.LoanPersonID > 0)
				{
					R1 = me.companyPanel.down("[name=LoanPersonID]").getStore().load({
						params : {
							PersonID : record.data.LoanPersonID
						},
						callback : function(){
							me.companyPanel.down("[name=LoanPersonID]").setValue(this.getAt(0).data.PersonID);
						}
					});
				}			
				//..........................................................
				var t = setInterval(function(){
					if(!R1 || !R1.isLoading())
					{
						clearInterval(t);
						me.CustomizeForm(record);
						me.mask.hide();
					}
				}, 1000);
			}
		}
	});
}

PlanInfo.OperationRender = function(v,p,record){
	
	if(PlanInfoObject.User == "Staff")
		return "<div  title='عملیات' class='setting' onclick='PlanInfoObject.OperationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";

	if(record.data.IsStarted == "NO")
	{
		if(PlanInfoObject.User == "Agent" && record.data.StatusID == "1")
		{
			return "<div  title='ویرایش' class='edit' onclick='PlanInfoObject.PartInfo(true);' " +
			"style='background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:16px;float:right;height:16'></div>" + 
			
			"<div  title='حذف' class='remove' onclick='PlanInfoObject.DeletePart();' " +
			"style='background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:16px;float:right;height:16'></div>";
		}
	}		
}

PlanInfo.prototype.OperationMenu = function(e){

	record = this.grid.getSelectionModel().getLastSelected();
	ReqRecord = this.store.getAt(0);
	
	var op_menu = new Ext.menu.Menu();

	if(record.data.imp_VamCode*1 > 0)
	{
		op_menu.add({text: 'اقساط',iconCls: 'list',
		handler : function(){ return PlanInfoObject.LoadInstallments(); }});

		op_menu.add({text: 'ویرایش',iconCls: 'edit', 
			handler : function(){ return PlanInfoObject.PartInfo(true); }});
		
		op_menu.showAt(e.pageX-120, e.pageY);
		return;
	}
	if(record.data.IsStarted == "NO")
	{
		if(record.data.StatusID == "70")
			op_menu.add({text: 'شروع گردش فرم',iconCls: 'refresh',
			handler : function(){ return PlanInfoObject.StartFlow(); }});
		
		op_menu.add({text: 'ویرایش',iconCls: 'edit', 
			handler : function(){ return PlanInfoObject.PartInfo(true); }});

		op_menu.add({text: 'حذف',iconCls: 'remove', 
			handler : function(){ return PlanInfoObject.DeletePart(); }});
				
	}	
	if(record.data.IsEnded == "YES")
	{
		op_menu.add({text: 'اقساط',iconCls: 'list',
		handler : function(){ return PlanInfoObject.LoadInstallments(); }});

		op_menu.add({text: 'پرداخت',iconCls: 'epay',
		handler : function(){ return PlanInfoObject.PayInfo(); }});
	
		if(record.data.IsPaid == "YES")
			op_menu.add({text: 'اتمام مرحله و ایجاد مرحله جدید',iconCls: "app",
			handler : function(){ return PlanInfoObject.EndPart(); }});
	}		
	
	if(record.data.StatusID == "70")
		op_menu.add({text: 'سابقه درخواست',iconCls: 'history', 
		handler : function(){ return PlanInfoObject.ShowPartHistory(); }});
	
	op_menu.showAt(e.pageX-120, e.pageY);
}

PlanInfo.prototype.CustomizeForm = function(record){
	
	if(this.User == "Staff")
	{
		this.companyPanel.down("[itemId=cmp_saveAndSend]").hide();
		
		if(record == null)
		{
			this.companyPanel.down("[itemId=cmp_Supporter]").show();
			this.companyPanel.down("[name=ReqFullname]").hide();
			this.companyPanel.down("[name=BorrowerDesc]").hide();
			this.companyPanel.down("[name=BorrowerID]").hide();
			this.companyPanel.down("[name=AgentGuarantee]").hide();
			this.PartsPanel.hide();
		}
	}
	if(this.User == "Agent")
	{
		if(this.PlanID == 0)
			this.PartsPanel.hide();
		else
		{
			this.companyPanel.down("[itemId=cmp_save]").hide();
			this.companyPanel.down("[itemId=cmp_saveAndSend]").show();
		}		
		
		this.companyPanel.down("[name=ReqFullname]").hide();		
		this.companyPanel.down("[name=BranchID]").setValue(1);
		this.companyPanel.down("[name=BranchID]").hide();	
		this.companyPanel.down("[name=LoanPersonID]").hide();
		
		this.companyPanel.doLayout();
	}
	
	if(record != null)
	{
		if(this.User == "Agent" && record.data.StatusID != "1" && record.data.StatusID != "20")
		{
			this.companyPanel.getEl().readonly();
			this.companyPanel.down("[itemId=cmp_save]").hide();
			this.companyPanel.down("[itemId=cmp_saveAndSend]").hide();
			this.grid.down("[itemId=addPart]").hide();
			this.grid.down("[dataIndex=PartID]").hide();
		}	
		if(this.User == "Staff")
		{
			if(record.data.ReqPersonRole == "Agent")
			{
				if(record.data.StatusID == "10")
				{
					this.companyPanel.getEl().readonly(new Array("LoanPersonID","DocumentDesc"));
				}
				else
				{
					this.companyPanel.getEl().readonly();
					this.companyPanel.down("[itemId=cmp_save]").hide();
				}
				this.companyPanel.doLayout();
				this.grid.down("[itemId=addPart]").hide();				
			}
			else
			{
				if(record.data.StatusID == "70")
				{
					this.companyPanel.getEl().readonly();
					this.companyPanel.down("[itemId=cmp_save]").hide();
				}
			}
			
			if(record.data.ReqPersonRole == "Staff")
			{
				this.companyPanel.down("[itemId=cmp_Supporter]").show();
				this.companyPanel.down("[name=ReqFullname]").hide();
				this.companyPanel.down("[name=BorrowerDesc]").hide();
				this.companyPanel.down("[name=BorrowerID]").hide();
			}
		}	
		if(this.User == "Customer")
		{
			this.companyPanel.down("[itemId=cmp_Supporter]").show();
			this.companyPanel.down("[name=LoanPersonID]").hide();
			this.companyPanel.down("[name=BorrowerDesc]").hide();
			this.companyPanel.down("[name=BorrowerID]").hide();
			this.companyPanel.down("[name=ReqDetails]").hide();
			this.companyPanel.down("[itemId=cmp_save]").hide();
			if(record.data.ReqPersonRole == "Staff")
				this.companyPanel.down("[name=AgentGuarantee]").hide();
			
			this.companyPanel.getEl().readonly();
			
			this.grid.down("[itemId=addPart]").hide();
			this.grid.down("[dataIndex=PartID]").hide();	
			this.companyPanel.down("[itemId=cmp_saveAndSend]").hide();
			this.PartsPanel.down("[name=FundWage]").getEl().dom.style.display = "none";
			this.get("TR_FundWage").style.display = "none";
			this.get("TR_AgentWage").style.display = "none";
		}
		this.companyPanel.doLayout();
	}
	
	//..........................................................................
	this.companyPanel.down("[itemId=cmp_confirm30]").hide();
	this.companyPanel.down("[itemId=cmp_reject20]").hide();
	this.companyPanel.down("[itemId=cmp_SendToCustomer]").hide();
	this.companyPanel.down("[itemId=cmp_returnFromCustomer]").hide();
	this.companyPanel.down("[itemId=cmp_confirm70]").hide();
	this.companyPanel.down("[itemId=cmp_reject60]").hide();
	
	if(record != null && this.User == "Staff")
	{
		this.companyPanel.down("[itemId=cmp_LoanDocuments]").show();
		this.companyPanel.down("[itemId=cmp_PersonDocuments]").show();
		this.companyPanel.down("[itemId=cmp_history]").show();
		
		if('<?= $_SESSION["USER"]["UserName"] ?>' == 'admin')
		{
			this.companyPanel.down("[itemId=cmp_changeStatus]").show();
		}
		if(record.data.StatusID == "1" && record.data.ReqPersonRole == "Staff")
		{
			this.companyPanel.down("[itemId=cmp_confirm30]").show();
		}
		if(record.data.StatusID == "10")
		{
			this.companyPanel.down("[itemId=cmp_confirm30]").show();
			this.companyPanel.down("[itemId=cmp_reject20]").show();
		}
		if(record.data.StatusID == "30" || record.data.StatusID == "35")
		{
			this.companyPanel.down("[itemId=cmp_SendToCustomer]").show();
		}
		if(record.data.StatusID == "40")
		{
			this.companyPanel.down("[itemId=cmp_returnFromCustomer]").show();
		}
		if(record.data.StatusID == "50")
		{
			this.companyPanel.down("[itemId=cmp_confirm70]").show();
			this.companyPanel.down("[itemId=cmp_reject60]").show();
		}
	}
}

PlanInfo.prototype.SaveRequest = function(mode){

	mask = new Ext.LoadMask(this.companyPanel, {msg:'در حال ذخيره سازي...'});
	mask.show();  
	
	this.companyPanel.getForm().submit({
		clientValidation: true,
		url: this.address_prefix + '../../loan/request/request.data.php?task=SaveLoanRequest' , 
		method: "POST",
		params : {
			PlanID : this.PlanID,
			sending : mode == "send" ? "true" : "false"
		},
		
		success : function(form,action){
			mask.hide();
			me = PlanInfoObject;
			
			me.PlanID = action.result.data;
			me.grid.getStore().proxy.extraParams = {PlanID: me.PlanID};
			me.grid.getStore().load();
			
			if(me.User == "Agent")
				me.companyPanel.down("[itemId=cmp_save]").hide();
			
			me.PartsPanel.show();
			
			me.LoadPlanInfo();
			
			if( mode == "send")
			{
				me.companyPanel.hide();
				me.PartsPanel.hide();
				me.SendedPanel.show();
				me.SendedPanel.getComponent("PlanID").
					update('شماره پیگیری درخواست : ' + me.PlanID);
			}
		},
		failure : function(){
			mask.hide();
			//Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
		}
	});
}

PlanInfo.prototype.PartInfo = function(EditMode){
	
	if(!this.PartWin)
	{
		this.PartWin = new Ext.window.Window({
			width : 500,
			height : 300,
			modal : true,
			closeAction : 'hide',
			title : "ایجاد مرحله جدید",
			items : new Ext.form.Panel({
				layout : {
					type : "table",
					columns : 2
				},
				defaults : {
					xtype : "numberfield",
					labelWidth : 80,
					hideTrigger : true,
					width : 150,
					labelWidth : 90,
					allowBlank : false
				},				
				items :[{
					xtype : "textfield",
					name : "PartDesc",
					fieldLabel : "عنوان مرحله",
					colspan : 2,
					width : 450
				},{
					xtype : "currencyfield",
					name : "PartAmount",
					fieldLabel : "مبلغ پرداخت",
					maxValue : this.RequestRecord.data.RequestAmount,
					width : 220
				},{
					xtype : "shdatefield",
					name : "PartDate",
					allowBlank : true,
					hideTrigger : false,
					fieldLabel : "تاریخ پرداخت",
					width : 200
				},{
					xtype : "container",
					layout : "hbox",
					width : 250,
					items : [{
						xtype:'numberfield',
						fieldLabel: 'فاصله اقساط',
						hideTrigger : true,
						allowBlank : false,
						name: 'PayInterval',
						labelWidth: 90,
						width : 150
					},{
						xtype : "radio",
						boxLabel : "ماه",
						inputValue : "MONTH",
						itemId : "monthInterval",
						checked : true,
						name : "IntervalType"
					},{
						xtype : "radio",
						boxLabel : "روز",
						inputValue : "DAY",
						itemId : "dayInterval",
						name : "IntervalType"
					}]
				},{
					fieldLabel: 'مدت تنفس',
					name: 'DelayMonths',
					afterSubTpl : "ماه"
				},{
					fieldLabel: 'تعداد اقساط',
					name: 'InstallmentCount'
				},{
					fieldLabel: 'درصد دیرکرد',
					name: 'ForfeitPercent'
				},{
					fieldLabel: 'کارمزد مشتری',
					name: 'CustomerWage'	
				},{
					fieldLabel: 'کارمزد صندوق',
					name: 'FundWage'
				},{
					xtype : "fieldset",
					colspan :2,
					width : 450,
					style : "margin-right:10px",
					items : [{
						xtype : "radio",
						boxLabel : "پرداخت کارمزد طی اقساط",
						name : "WageReturn",
						inputValue : "INSTALLMENT",
						checked : true
					},{
						xtype : "radio",
						boxLabel : "پرداخت کارمزد از سپرده سرمایه گذار",
						name : "WageReturn",
						inputValue : "AGENT"
					},{
						xtype : "radio",						
						boxLabel : "پرداخت کارمزد هنگام پرداخت وام",
						name : "WageReturn",
						inputValue : "CUSTOMER"
					}]
				},{
					xtype : "hidden",
					name : "PartID"
				}]				
			}),
			buttons : [{
				text : "ذخیره",
				iconCls : "save",
				handler : function(){
					PlanInfoObject.SavePart();
				}
			},{
				text : "انصراف",
				iconCls : "undo",
				handler : function(){
					this.up('window').hide();
				}
			}]
		});
		
		if(this.User == "Agent")
		{
			this.PartWin.down("[name=PartDate]").hide();
			this.PartWin.down("[name=PartAmount]").colspan = 2;
			
		}
	}
	
	this.PartWin.show();
	if(EditMode)
	{
		record = this.grid.getSelectionModel().getLastSelected();
		this.PartWin.down('form').loadRecord(record);
		this.PartWin.down("[name=PartDate]").setValue(MiladiToShamsi(record.data.PartDate));
		this.PartWin.down("[name=PayInterval]").setValue(record.data.PayInterval*1);
		this.PartWin.down("[itemId=monthInterval]").setValue(record.data.IntervalType == "MONTH" ? true : false);
		this.PartWin.down("[itemId=dayInterval]").setValue(record.data.IntervalType == "DAY" ? true : false);
	}
	else
		this.PartWin.down('form').getForm().reset();
}

PlanInfo.prototype.SavePart = function(){

	mask = new Ext.LoadMask(this.PartWin, {msg:'در حال ذخیره سازی ...'});
	mask.show();

	this.PartWin.down('form').getForm().submit({
		clientValidation: true,
		url: this.address_prefix +'../../loan/request/request.data.php',
		method: "POST",
		params: {
			task: "SavePart",
			PlanID : this.PlanID
		},
		success: function(form,action){
			mask.hide();
			PlanInfoObject.grid.getStore().load();
			PlanInfoObject.PartWin.hide();
		},
		failure: function(){
			mask.hide();
		}
	});
}

PlanInfo.prototype.DeletePart = function(){

	Ext.MessageBox.confirm("","آیا مایل به حذف می باشید؟",function(btn){
		if(btn == "no")
			return;
		
		me = PlanInfoObject;
		record = me.grid.getSelectionModel().getLastSelected();
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'request.data.php',
			method: "POST",
			params: {
				task: "DeletePart",
				PartID : record.data.PartID
			},
			success: function(response){
				result = Ext.decode(response.responseText);
				mask.hide();
				if(result.success)
					PlanInfoObject.grid.getStore().load();
				else
					Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد;")
			}
		});
	});
}

PlanInfo.prototype.LoadSummary = function(record){

	function PMT(F8, F9, F7, YearMonths) {  
		
		if(F8 == 0)
			return F7/F9;
				
		F8 = F8/(YearMonths*100);
		F7 = -F7;
		return F8 * F7 * Math.pow((1 + F8), F9) / (1 - Math.pow((1 + F8), F9)); 
	} 
	function ComputeWage(F7, F8, F9, YearMonths){
		
		return (((F7*F8/YearMonths*( Math.pow((1+(F8/YearMonths)),F9)))/
			((Math.pow((1+(F8/YearMonths)),F9))-1))*F9)-F7;
	}
	function roundUp(number, digits)
	{
		var factor = Math.pow(10,digits);
		return Math.ceil(number*factor) / factor;
	}
	function YearWageCompute(record,TotalWage,yearNo, YearMonths){
		
		PayMonth = MiladiToShamsi(record.data.PartDate).split('/')[1]*1;
		PayMonth = PayMonth*YearMonths/12;
		
		FirstYearInstallmentCount = YearMonths - PayMonth;
		MidYearInstallmentCount = Math.floor((record.data.InstallmentCount-FirstYearInstallmentCount) / YearMonths);
		LastYeatInstallmentCount = (record.data.InstallmentCount-FirstYearInstallmentCount) % YearMonths;
		
		if(yearNo > MidYearInstallmentCount+2)
			return 0;
		
		F9 = record.data.InstallmentCount*1;
		var BeforeMonths = 0
		if(yearNo == 2)
			BeforeMonths = FirstYearInstallmentCount;
		else if(yearNo > 2)
			BeforeMonths = FirstYearInstallmentCount + (yearNo-2)*YearMonths;
		
		var curMonths = FirstYearInstallmentCount;
		if(yearNo > 1 && yearNo <= MidYearInstallmentCount+1)
			curMonths = YearMonths;
		else if(yearNo > MidYearInstallmentCount+1)
			curMonths = LastYeatInstallmentCount;
		
		var val = ((((F9-BeforeMonths)*(F9-BeforeMonths+1))-
			(F9-BeforeMonths-curMonths)*(F9-BeforeMonths-curMonths+1)))/(F9*(F9+1))*TotalWage;
		return Ext.util.Format.Money(Math.round(val));
	}

	YearMonths = 12;
	if(record.data.IntervalType == "DAY")
		YearMonths = Math.floor(365/record.data.PayInterval);

	FirstPay = roundUp(PMT(record.data.CustomerWage,record.data.InstallmentCount, 
		record.data.PartAmount, YearMonths),-3);
	TotalWage = Math.round(ComputeWage(record.data.PartAmount, record.data.CustomerWage/100, 
		record.data.InstallmentCount, YearMonths));
	TotalWage = !isInt(TotalWage) ? 0 : TotalWage;	
	FundWage = Math.round((record.data.FundWage/record.data.CustomerWage)*TotalWage);
	FundWage = !isInt(FundWage) ? 0 : FundWage;
	AgentWage = TotalWage - FundWage;
	
	TotalDelay = Math.round(record.data.PartAmount*record.data.CustomerWage*record.data.DelayMonths/1200);
	LastPay = record.data.PartAmount*1 + TotalWage - FirstPay*(record.data.InstallmentCount-1);
	
	if(record.data.InstallmentCount == 1)
		LastPay = 0;
	
	this.get("SUM_InstallmentAmount").innerHTML = Ext.util.Format.Money(FirstPay);
	this.get("SUM_LastInstallmentAmount").innerHTML = Ext.util.Format.Money(LastPay);
	this.get("SUM_Delay").innerHTML = Ext.util.Format.Money(TotalDelay);
	this.get("SUM_NetAmount").innerHTML = Ext.util.Format.Money(record.data.PartAmount - TotalDelay);	
	
	this.get("SUM_TotalWage").innerHTML = Ext.util.Format.Money(TotalWage);	
	this.get("SUM_FundWage").innerHTML = Ext.util.Format.Money(FundWage);	
	this.get("SUM_AgentWage").innerHTML = Ext.util.Format.Money(AgentWage);	
	
	this.get("SUM_Wage_1Year").innerHTML = YearWageCompute(record, TotalWage, 1, YearMonths);
	this.get("SUM_Wage_2Year").innerHTML = YearWageCompute(record, TotalWage, 2, YearMonths);
	this.get("SUM_Wage_3Year").innerHTML = YearWageCompute(record, TotalWage, 3, YearMonths);
	this.get("SUM_Wage_4Year").innerHTML = YearWageCompute(record, TotalWage, 4, YearMonths);
}

PlanInfo.prototype.ShowHistory = function(){

	if(!this.HistoryWin)
	{
		this.HistoryWin = new Ext.window.Window({
			title: 'سابقه گردش درخواست',
			modal : true,
			autoScroll : true,
			width: 700,
			height : 500,
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "history.php",
				scripts : true
			},
			buttons : [{
					text : "بازگشت",
					iconCls : "undo",
					handler : function(){
						this.up('window').hide();
					}
				}]
		});
		Ext.getCmp(this.TabID).add(this.HistoryWin);
	}
	this.HistoryWin.show();
	this.HistoryWin.center();
	this.HistoryWin.loader.load({
		params : {
			PlanID : this.PlanID
		}
	});
}

PlanInfo.prototype.LoanDocuments = function(ObjectType){

	if(!this.documentWin)
	{
		this.documentWin = new Ext.window.Window({
			width : 720,
			height : 440,
			modal : true,
			bodyStyle : "background-color:white;padding: 0 10px 0 10px",
			closeAction : "hide",
			loader : {
				url : "../../dms/documents.php",
				scripts : true
			},
			buttons :[{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.documentWin);
	}

	this.documentWin.show();
	this.documentWin.center();
	
	this.documentWin.loader.load({
		scripts : true,
		params : {
			ExtTabID : this.documentWin.getEl().id,
			ObjectType : ObjectType,
			ObjectID : ObjectType == "loan" ? this.PlanID : this.RequestRecord.data.LoanPersonID
		}
	});
}

PlanInfo.prototype.beforeChangeStatus = function(StatusID){
	
	if(new Array(20,60).indexOf(StatusID) == -1)
	{
		Ext.MessageBox.confirm("","آیا مایل به تایید می باشید؟", function(btn){
			if(btn == "no")
				return;
			
			PlanInfoObject.ChangeStatus (StatusID, "");
		});
		return;
	}
	if(!this.commentWin)
	{
		this.commentWin = new Ext.window.Window({
			width : 412,
			height : 198,
			modal : true,
			title : "دلیل رد مدرک برای درخواست کننده",
			bodyStyle : "background-color:white",
			items : [{
				xtype : "textarea",
				width : 400,
				rows : 8,
				name : "StepComment"
			}],
			closeAction : "hide",
			buttons : [{
				text : "اعمال",				
				iconCls : "save",
				itemId : "btn_save"
			},{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.commentWin);
	}
	this.commentWin.down("[itemId=btn_save]").setHandler(function(){
		PlanInfoObject.ChangeStatus(StatusID, 
			this.up('window').down("[name=StepComment]").getValue());});
	this.commentWin.show();
	this.commentWin.center();
}

PlanInfo.prototype.ChangeStatus = function(StatusID, StepComment){
	
	this.mask.show();
	
	Ext.Ajax.request({
		methos : "post",
		url : this.address_prefix + "request.data.php",
		params : {
			task : "ChangeRequestStatus",
			PlanID : this.PlanID,
			StatusID : StatusID,
			StepComment : StepComment
		},
		
		success : function(){
			
			PlanInfoObject.LoadPlanInfo();
			if(PlanInfoObject.commentWin)
				PlanInfoObject.commentWin.hide();
		}
	});
}

PlanInfo.prototype.SetStatus = function(){
	
	if(!this.setStatusWin)
	{
		this.setStatusWin = new Ext.window.Window({
			width : 412,
			height : 198,
			modal : true,
			title : "تغییر وضعیت",
			defaults : {width : 380},
			bodyStyle : "background-color:white",
			items : [{
				xtype : "combo",
				store : new Ext.data.SimpleStore({
					proxy: {
						type: 'jsonp',
						url: this.address_prefix + "request.data.php?task=selectRequestStatuses",
						reader: {root: 'rows',totalProperty: 'totalCount'}
					},
					fields : ['InfoID','InfoDesc'],
					autoLoad : true					
				}),
				fieldLabel : "وضعیت جدید",
				queryMode : 'local',
				allowBlank : false,
				displayField : "InfoDesc",
				valueField : "InfoID",
				itemId : "StatusID"
			},{
				xtype : "textarea",
				itemId : "comment",
				fieldLabel : "توضیحات"
			}],
			closeAction : "hide",
			buttons : [{
				text : "تغییر وضعیت",				
				iconCls : "save",
				itemId : "btn_save",
				handler : function(){
					status = this.up('window').getComponent("StatusID").getValue();
					comment = this.up('window').getComponent("comment").getValue();
					PlanInfoObject.ChangeStatus(status, "[تغییر وضعیت]" + comment);
					this.up('window').hide();
				}
			},{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.setStatusWin);
	}
	this.setStatusWin.show();
	this.setStatusWin.center();
}

//.........................................................

PlanInfo.prototype.LoadInstallments = function(){
	
	var record = this.grid.getSelectionModel().getLastSelected();
	if(!record)
	{
		Ext.MessageBox.alert("","ابتدا مرحله مورد نظر خود را انتخاب کنید");
		return;
	}
	
	if(!this.InstallmentsWin)
	{
		this.InstallmentsWin = new Ext.window.Window({
			width : 770,
			title : "لیست اقساط",
			height : 410,
			modal : true,
			loader : {
				url : this.address_prefix + "installments.php",
				method : "post",
				scripts : true
			},
			closeAction : "hide"
		});
		
		Ext.getCmp(this.TabID).add(this.InstallmentsWin);
	}
	this.InstallmentsWin.show();
	this.InstallmentsWin.center();
	
	this.InstallmentsWin.loader.load({
		params : {
			ExtTabID : this.InstallmentsWin.getEl().id,
			PartID : record.data.PartID
		}
	});
	
	this.InstallmentsWin.show();
	this.InstallmentsWin.center();
}
//.........................................................

PlanInfo.prototype.PayInfo = function(){
	
	if(!this.PayInfoWin)
	{
		this.PayInfoWin = new Ext.window.Window({
			width : 400,
			height : 600,
			autoScroll : true,
			modal : true,
			title : "مبلغ پرداخت",
			bodyStyle : "background-color:white",
			loader : {
				url : this.address_prefix + "PartPayInfo.php"
			},
			closeAction : "hide",
			buttons : [{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		Ext.getCmp(this.TabID).add(this.PayInfoWin);
	}
	
	this.PayInfoWin.show();
	this.PayInfoWin.center();
	
	this.PayInfoWin.loader.load({
		params : {
			PartID : this.grid.getSelectionModel().getLastSelected().data.PartID
		}
	});
}

PlanInfo.prototype.PayPart = function(MaxAvailablePayAmount){
	
	if(!this.PayWin)
	{
		this.PayWin = new Ext.window.Window({
			width : 202,
			modal : true,
			title : "مبلغ پرداخت",
			bodyStyle : "padding-top:4px;background-color:white",
			items : [{
				xtype : "currencyfield",
				hideTrigger : true,
				width : 190,
				name : "PayAmount"
			}],
			closeAction : "hide",
			buttons : [{
				text : "پرداخت",				
				iconCls : "epay",
				handler : function(){
					me = PlanInfoObject;
					var record = me.grid.getSelectionModel().getLastSelected();

					mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخیره سازی ...'});
					mask.show();

					Ext.Ajax.request({
						url: me.address_prefix +'request.data.php',
						method: "POST",
						params: {
							task: "PayPart",
							PartID : record.data.PartID,
							PayAmount : me.PayWin.down("[name=PayAmount]").getValue()						
						},
						success: function(response){

							result = Ext.decode(response.responseText);
							if(!result.success)
								Ext.MessageBox.alert("", result.data);
							
							PlanInfoObject.PayWin.hide();
							PlanInfoObject.PayInfoWin.loader.load({
								params : {
									PartID : PlanInfoObject.grid.getSelectionModel().getLastSelected().data.PartID
								},
								callback : function(){mask.hide();}
							});
						}
					});
				}
			},{
				text : "بازگشت",
				iconCls : "undo",
				handler : function(){this.up('window').hide();}
			}]
		});
		
		Ext.getCmp(this.TabID).add(this.PayWin);
	}
	
	this.PayWin.show();
	this.PayWin.center();
	this.PayWin.down("[name=PayAmount]").setValue(MaxAvailablePayAmount);
	this.PayWin.down("[name=PayAmount]").setMaxValue(MaxAvailablePayAmount);
}

PlanInfo.prototype.ReturnPayPart = function(DocID){
	
	Ext.MessageBox.confirm("","آیا مایل به برگشت پرداخت این مرحله از وام می باشید؟",function(btn){
		
		if(btn == "no")
			return;
		
		me = PlanInfoObject;
		var record = me.grid.getSelectionModel().getLastSelected();
	
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'request.data.php',
			method: "POST",
			params: {
				task: "ReturnPayPart",
				PartID : record.data.PartID,
				DocID : DocID
			},
			success: function(response){
				
				result = Ext.decode(response.responseText);
				if(!result.success)
				{
					if(result.data == "")
						Ext.MessageBox.alert("","عملیات مورد نظر با شکست مواجه شد");
					else
						Ext.MessageBox.alert("", result.data);
					mask.hide();
					return;
				}				
				PlanInfoObject.PayInfoWin.loader.load({
					params : {
						PartID : PlanInfoObject.grid.getSelectionModel().getLastSelected().data.PartID
					},
					callback : function(){mask.hide();}
				});
			}
		});
	});
}

PlanInfo.prototype.EndPart = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به اتمام این مرحله از وام می باشید؟" 
		+ "<br>توجه : تغییرات اعمال شده قابل برگشت نمی باشد." 
		+ "<br>فقط زمانی این کار را انجام دهید که از انجام آن مطمئن هستید" , function(btn){
				
		if(btn == "no")
			return;
		
		me = PlanInfoObject;
		var record = me.grid.getSelectionModel().getLastSelected();
	
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'request.data.php',
			method: "POST",
			params: {
				task: "EndPart",
				PartID : record.data.PartID
			},
			success: function(response){
				
				result = Ext.decode(response.responseText);
				if(!result.success)
					Ext.MessageBox.alert("error", result.data);
				else
					Ext.MessageBox.alert("", "عملیات مورد نظر با موفقیت انجام گردید");
				
				mask.hide();
				PlanInfoObject.grid.getStore().load();
			}
		});
	});
}

PlanInfo.prototype.StartFlow = function(){
	
	Ext.MessageBox.confirm("","آیا مایل به شروع گردش تایید پرداخت مرحله می باشید؟",function(btn){
		
		if(btn == "no")
			return;
		
		me = PlanInfoObject;
		var record = me.grid.getSelectionModel().getLastSelected();
	
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'request.data.php',
			method: "POST",
			params: {
				task: "StartFlow",
				PartID : record.data.PartID
			},
			success: function(response){
				mask.hide();
				PlanInfoObject.grid.getStore().load();
			}
		});
	});
}

PlanInfo.prototype.ShowPartHistory = function(){

	if(!this.HistoryWin)
	{
		this.HistoryWin = new Ext.window.Window({
			title: 'سابقه گردش',
			modal : true,
			autoScroll : true,
			width: 700,
			height : 500,
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "../../office/workflow/history.php",
				scripts : true
			},
			buttons : [{
					text : "بازگشت",
					iconCls : "undo",
					handler : function(){
						this.up('window').hide();
					}
				}]
		});
		Ext.getCmp(this.TabID).add(this.HistoryWin);
	}
	this.HistoryWin.show();
	this.HistoryWin.center();
	this.HistoryWin.loader.load({
		params : {
			FlowID : 1,
			ObjectID : this.grid.getSelectionModel().getLastSelected().data.PartID
		}
	});
}

</script>
