<script type="text/javascript">
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 90.10
//-----------------------------

Person.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function Person()
{
	this.mainPanel = new Ext.form.FormPanel({
		frame: true,
		hidden : true,
		title: 'اطلاعات شخصی',
		width: 500,
		defaults: {
			xtype : "displayfield",
			fieldCls : "blueText",
			style : "margin-bottom:10px",
			anchor : "98%"
		},
		items: [{
			fieldLabel: 'نام',
			name: 'fname'
		},{
			fieldLabel: 'نام خانوادگی',
			name: 'lname'
		},{
			fieldLabel: 'نام شرکت',
			name: 'CompanyName'
		},{
			fieldLabel: 'کد ملی',
			name: 'NationalID'
		},{
			fieldLabel: 'کد اقتصادی',
			name: 'EconomicID'
		},{
			fieldLabel: 'شماره تلفن',
			name: 'PhoneNo'
		},{
			fieldLabel: 'تلفن همراه',
			name: 'mobile'
		},{
			vtype : "email",
			fieldLabel: 'پست الکترونیک',
			name: 'email',
			fieldStyle : "direction:ltr"
		},{
			fieldLabel: 'آدرس',
			name: 'address'
		}]
	});

	this.tabPanel = new Ext.TabPanel({
		renderTo: this.get("info"),
		activeTab: 0,
		disabled : true,
		plain:true,
		autoHeight : true,
		width: 750,
		height : 300,
		defaults:{
			autoHeight: true, 
			autoWidth : true            
		},
		items:[{
			title : "اطلاعات شخصی",
			items : this.mainPanel
		},{
			title : "مدارک",
			style : "padding:10px",
			itemId : "documents",
			items : [{
				xtype : "container",
				cls : "blueText",
				html : "<br>" + "ردیف های سبز رنگ ردیف های تایید شده و برابر اصل شده توسط صندوق بوده و قابل تغییر نمی باشند"
			}]
		}]
	});	
	
	this.PersonStore = new Ext.data.Store({
		proxy:{
			type: 'jsonp',
			url: this.address_prefix + "persons.data.php?task=selectPersons",
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields : ["IsReal","fname","lname","CompanyName","UserName","NationalID","EconomicID","PhoneNo","mobile","address","email"],
		listeners :{
			load : function(){
				me = PersonObject;
				me.mainPanel.loadRecord(this.getAt(0));
				
				if(this.getAt(0).data.IsReal == "YES")
				{
					me.mainPanel.down("[name=CompanyName]").hide();
					me.mainPanel.down("[name=EconomicID]").hide();
					
				}
				else
				{
					me.mainPanel.down("[name=fname]").hide();
					me.mainPanel.down("[name=lname]").hide();
					me.mainPanel.down("[name=NationalID]").hide();
				}
				
				me.mainPanel.show();
				me.mainPanel.center();
			}
		}
	});	
}

Person.deleteRender = function(v,p,r)
{
	if(r.data.IsActive == "NO")
		return "";
	return "<div align='center' title='حذف کاربر' class='remove' onclick='PersonObject.Deleting();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

Person.infoRender = function(v,p,r)
{
	return "<div align='center' title='مشاهده اطلاعات' class='info' onclick='PersonObject.ShowInfo();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

Person.resetPassRender = function(v,p,r)
{
	if(r.data.IsActive == "NO")
		return "";
	return "<div align='center' title='حذف رمز عبور' class='undo' onclick='PersonObject.ResetPass();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16'></div>";
}

Person.prototype.Adding = function()
{
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		personID : ""
	});

	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

Person.prototype.Deleting = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	if(record && confirm("آيا مايل به حذف مي باشيد؟"))
	{
		Ext.Ajax.request({
		  	url : this.address_prefix + "framework.data.php",
		  	method : "POST",
		  	params : {
		  		task : "DeletePerson",
		  		PersonID : record.data.PersonID
		  	},
		  	success : function(response,o)
		  	{
		  		PersonObject.grid.getStore().load();
		  	}
		});
	}
}

Person.prototype.saveData = function(store,record)
{
    mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'SavePerson',
			record : Ext.encode(record.data)
		},
		url: this.address_prefix +'framework.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				PersonObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

Person.prototype.ResetPass = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
		
    mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		params: {
			task: 'ResetPass',
			PersonID : record.data.PersonID
		},
		url: this.address_prefix +'framework.data.php',
		method: 'POST',

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				Ext.MessageBox.alert("Warning","رمز عبور با موفقیت حذف گردید. بعد از اولین بار ورود به  سیستم رمز عبور تنظیم خواهد شد.");
				PersonObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

//..............................................................................

Person.FileRender = function(v,p,r){
	
	return "<div align='center' title='مشاهده فایل' class='attach' "+
		"onclick='Person.ShowFile(" + r.data.DocumentID + ");' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:100%;height:16;float:right'></div>";
}

Person.ShowFile = function(DocumentID){
	
	window.open("../../dms/ShowFile.php?DocumentID=" + DocumentID);
}

Person.ConfirmRender = function(v,p,r){
	
	if(r.data.IsConfirm == "YES")
		return "";
	
	return "<div align='center' title='تایید' class='tick' "+
		"onclick='PersonObject.ConfirmDocument();' " +
		"style='background-repeat:no-repeat;background-position:center;" +
		"cursor:pointer;width:18px;height:16;float:right'></div>";
}

Person.prototype.ConfirmDocument = function(){
	
	Ext.MessageBox.confirm("","فایل با اصل مدرک مطابق می باشد",function(btn){
		if(btn == "no")
			return;
		
		me = PersonObject;
		
		var record = me.docgrid.getSelectionModel().getLastSelected();
		mask = new Ext.LoadMask(me.docgrid,{msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix +'../../dms/dms.data.php',
			method: "POST",
			params: {
				task: "ConfirmDocument",
				DocumentID : record.data.DocumentID
			},
			success: function(response){
				mask.hide();
				var st = Ext.decode(response.responseText);

				if(st.success)
				{   
					PersonObject.docgrid.getStore().load();
				}
				else
				{
					if(st.data == "")
						alert("خطا در اجرای عملیات");
					else
						alert(st.data);
				}
			},
			failure: function(){}
		});
	});
}

Person.prototype.ShowInfo = function(){
	
		var record = this.grid.getSelectionModel().getLastSelected();

		this.tabPanel.setDisabled(false);
		this.PersonStore.proxy.extraParams = {PersonID : record.data.PersonID};

		this.docgrid.getStore().proxy.extraParams = {
			ObjectID : record.data.PersonID
		};

		this.PersonStore.load();		
	
}

</script>
