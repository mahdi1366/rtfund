<script>
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 1394.12
//-----------------------------

PersonInfo.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	PersonID : <?= $PersonID ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function PersonInfo()
{
	this.FileCmp = new Ext.form.File({
		name : "FileType"
	});
	
	this.grid = <?= $grid ?>;
	this.grid.getView().getRowClass = function(record, index)
	{
		if(record.data.IsConfirm == "YES")
			return "greenRow";
		return "";
	}

	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
    mask.show();    
	
	this.store = new Ext.data.Store({
		proxy:{
			type: 'jsonp',
			url: this.address_prefix + "persons.data.php?task=selectPersons&full=true&PersonID=" + this.PersonID ,
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields : ["IsReal","fname","lname","CompanyName","UserName","NationalID","CityID",
			"EconomicID","PhoneNo","mobile","address","email","RegNo","RegDate","RegPlace",
			"CompanyType","AccountNo","DomainID","WebSite","IsGovermental","DomainDesc",
			"FatherName","ShNo","IsStaff","IsCustomer","IsSupporter","IsShareholder",
			"IsAgent","IsExpert", "SmsNo"],
		autoLoad : true,
		listeners :{
			load : function(){
				
				record = this.getAt(0);
				
				PersonInfoObject.MakeInfoPanel(record);
				PersonInfoObject.mainPanel.loadRecord(record);
				PersonInfoObject.tabPanel.down("[itemId=tab_info]").add(PersonInfoObject.mainPanel);
				
				if(record.data.IsReal == "YES")
					PersonInfoObject.tabPanel.down("[itemId=tab_signers]").destroy();
				else
				{
					PersonInfoObject.tabPanel.down("[name=RegDate]").setValue(
					MiladiToShamsi(record.data.RegDate) );
				}
				
				mask.hide();    
			}
		}
	});	
	
	this.tabPanel = new Ext.TabPanel({
		renderTo: this.get("mainForm"),
		activeTab: 0,
		plain:true,
		autoScroll : true,
		autoHeight: true, 
		width: 750,
		defaults:{
			autoHeight: true, 
			autoWidth : true            
		},
		items:[{
			title : "اطلاعات پایه",
			itemId : "tab_info"
		},{
			title : "مدارک",
			style : "padding:0 20px 0 20px",		
			itemId : "cmp_documents",						
			loader : {
				url : "../../office/dms/documents.php",
				scripts : true
			},
			listeners :{
				activate : function(){
					if(this.loader.isLoaded)
						return;
					this.loader.load({
						scripts : true,
						params : {
							ObjectID : PersonInfoObject.PersonID,
							ExtTabID : this.id,
							ObjectType : "person"
						}
					});
				}
			}
		},{
			title : "صاحبین امضاء",
			itemId : "tab_signers",
			style : "padding:5px",
			loader : {
				url : this.address_prefix + "OrgSigners.php",
				scripts : true
			},
			listeners :{
				activate : function(){
					if(this.loader.isLoaded)
						return;
					this.loader.load({
						scripts : true,
						params : {
							ExtTabID : this.id,
							PersonID : PersonInfoObject.PersonID
						}
					});
				}
			}
		},{
			title : "مجوز ها",
			itemId : "tab_licenses",
			style : "padding:5px",
			loader : {
				url : this.address_prefix + "licenses.php",
				scripts : true
			},
			listeners :{
				activate : function(){
					if(this.loader.isLoaded)
						return;
					this.loader.load({
						scripts : true,
						params : {
							ExtTabID : this.id,
							PersonID : PersonInfoObject.PersonID
						}
					});
				}
			}
		}]
	});	
	
	
}

PersonInfo.prototype.MakeInfoPanel = function(PersonRecord){
	
	var items;
	if(PersonRecord.data.IsReal == "YES")
		items = [{
			xtype : "container",
			colspan : 2,
			height : 160,
			width : 700,
			html : "<center><img class='PersonPicStyle' src='"+this.address_prefix+
				"showImage.php?PersonID="+this.PersonID+"' /></center>"
		},{
			xtype : "textfield",
			fieldLabel: 'نام',
			allowBlank : false,
			beforeLabelTextTpl: required,
			name: 'fname'
		},{
			xtype : "textfield",
			fieldLabel: 'نام خانوادگی',
			allowBlank : false,
			beforeLabelTextTpl: required,
			name: 'lname'
		},{
			xtype : "textfield",
			fieldLabel: 'نام پدر',
			allowBlank : false,
			beforeLabelTextTpl: required,
			name: 'FatherName'
		},{
			xtype : "textfield",
			fieldLabel: 'شماره شناسنامه',
			allowBlank : false,
			beforeLabelTextTpl: required,
			name: 'ShNo'
		},{
			xtype : "textfield",
			regex: /^\d{10}$/,
			maskRe: /[\d\-]/,
			fieldLabel: 'کد ملی',
			allowBlank : false,
			beforeLabelTextTpl: required,
			name: 'NationalID'
		},{
			xtype : "trigger",
			fieldLabel: 'حوزه فعالیت',
			name: 'DomainDesc',	
			triggerCls:'x-form-search-trigger',
			onTriggerClick : function(){
				PersonInfoObject.ActDomainLOV();
			}
		},{
			xtype : "textfield",
			maskRe: /[\d\-]/,
			fieldLabel: 'شماره تلفن',
			name: 'PhoneNo'
		},{
			xtype : "textfield",
			regex: /^\d{11}$/,
			maskRe: /[\d\-]/,
			fieldLabel: 'تلفن همراه',
			name: 'mobile'
		},{
			xtype : "textarea",
			fieldLabel: 'آدرس',
			name: 'address',
			rowspan : 4
		},{
			xtype : "numberfield",
			name : "PostalCode",
			fieldLabel : "کد پستی",
			hideTrigger : true
		},{
			xtype : "textfield",
			fieldLabel: 'وب سایت',
			name: 'WebSite',
			fieldStyle : "direction:ltr"
		},{
			xtype : "textfield",
			vtype : "email",
			fieldLabel: 'پست الکترونیک',
			name: 'email',
			fieldStyle : "direction:ltr"
		},{
			xtype : "textfield",
			regex: /^\d/,
			fieldLabel: 'شماره شبا',
			hideTrigger : true,
			fieldStyle : "direction:ltr",			
			name: 'AccountNo'
		},{
			xtype : "filefield",
			name : "PersonPic",
			fieldLabel : "تصویر"
		},{
			xtype : "hidden",
			name : "DomainID",
			colspan : 2
		}];
	else
		items = [{
			xtype : "container",
			colspan : 2,
			height : 160,
			width : 700,
			html : "<center><img class='PersonPicStyle' src='"+this.address_prefix+
				"showImage.php?PersonID="+this.PersonID+"&v="+this.TabID+"' /></center>"
		},{
			xtype : "textfield",
			beforeLabelTextTpl: required,
			fieldLabel: 'نام شرکت',
			width : 510,
			name: 'CompanyName',
			colspan : 2
		},{
			xtype : "textfield",
			//regex: /^\d{11}$/,
			maskRe: /[\d\-]/,
			allowBlank : false,
			fieldLabel: 'شناسه ملی',
			beforeLabelTextTpl: required,
			name: 'NationalID'
		},{
			xtype : "textfield",
			regex: /^\d{12}$/,
			maskRe: /[\d\-]/,
			fieldLabel: 'کد اقتصادی',
			name: 'EconomicID'
		},{
			xtype : "numberfield",
			fieldLabel: 'شماره ثبت',
			beforeLabelTextTpl: required,
			allowBlank : false,
			hideTrigger : true,
			name: 'RegNo'
		},{
			xtype : "shdatefield",
			allowBlank : false,
			beforeLabelTextTpl: required,
			fieldLabel: 'تاریخ ثبت',
			name: 'RegDate'
		},{
			xtype : "textfield",
			allowBlank : false,
			beforeLabelTextTpl: required,
			fieldLabel: 'محل ثبت',
			name: 'RegPlace'
		},{
			xtype : "combo",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + 'persons.data.php?task=selectCompanyTypes',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['InfoID','InfoDesc'],
				autoLoad : true					
			}),
			displayField : "InfoDesc",
			valueField : "InfoID",
			queryMode : "local",
			fieldLabel: 'نوع شرکت',
			name: 'CompanyType'
		},{
			xtype : "combo",
			store: new Ext.data.SimpleStore({
				fields : ['id','title'],
				data : [ 
					["YES" , "دولتی"],
					["NO" , "خصوصی"]
				]
			}),  
			displayField : "title",
			valueField : "id",
			fieldLabel: 'مالکیت شرکت',
			name: 'IsGovermental'
		},{
			xtype : "trigger",
			fieldLabel: 'حوزه فعالیت',
			name: 'DomainDesc',	
			triggerCls:'x-form-search-trigger',
			onTriggerClick : function(){
				PersonInfoObject.ActDomainLOV();
			}
		},{
			xtype : "textfield",
			maskRe: /[\d\-]/,
			fieldLabel: 'شماره تلفن',
			name: 'PhoneNo'
		},{
			xtype : "textfield",
			regex: /^\d{11}$/,
			maskRe: /[\d\-]/,
			fieldLabel: 'تلفن دورنگار',
			name: 'mobile'
		},{
			xtype : "combo",
			fieldLabel : "شهر",
			name : "CityID",
			store : new Ext.data.SimpleStore({
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + 'persons.data.php?task=selectCities',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields : ['InfoID','InfoDesc'],
				autoLoad : true					
			}),
			displayField : "InfoDesc",
			queryMode : 'local',
			valueField : "InfoID"
		},{
			xtype : "textfield",
			fieldLabel: 'وب سایت',
			name: 'WebSite',
			fieldStyle : "direction:ltr"
		},{
			xtype : "textarea",
			fieldLabel: 'آدرس',
			rows : 2,
			name: 'address',
			rowspan : 2
		},{
			xtype : "textfield",
			vtype : "email",
			fieldLabel: 'پست الکترونیک',
			name: 'email',
			fieldStyle : "direction:ltr"
		},{
			xtype : "textfield",
			regex: /^\d/,
			fieldLabel: 'شماره شبا',
			fieldStyle : "direction:ltr",
			name: 'AccountNo',
			hideTrigger : true
		},{
			xtype : "numberfield",
			name : "PostalCode",
			fieldLabel : "کد پستی",
			hideTrigger : true
		},{
			xtype : "numberfield",
			name : "SmsNo",
			hideTrigger : true,
			fieldLabel : "شماره دریافت پیامک"
		},{
			xtype : "filefield",
			name : "PersonPic",
			fieldLabel : "لوگوی شرکت"
		},{
			xtype : "hidden",
			name : "DomainID",
			colspan : 2
		}];
	
	this.mainPanel = new Ext.form.FormPanel({
		width: 750,
		frame : true,
		layout : {
			type : "table",
			columns : 2
		},
		defaults : {
			width : 350
		},
		items: items,

		buttons : [{
			text : "ذخیره",
			iconCls: 'save',
			handler: function(){ PersonInfoObject.SaveData(); }

		}]
	});
}

PersonInfoObject = new PersonInfo();

PersonInfo.prototype.ActDomainLOV = function(){
		
	if(!this.DomainWin)
	{
		this.DomainWin = new Ext.window.Window({
			autoScroll : true,
			width : 420,
			height : 420,
			title : "حوزه فعالیت",
			closeAction : "hide",
			loader : {
				url : this.address_prefix + "../baseInfo/ActDomain.php?mode=adding",
				scripts : true
			}
		});
		
		Ext.getCmp(this.TabID).add(this.DomainWin);
	}
	
	this.DomainWin.show();
	
	this.DomainWin.loader.load({
		params : {
			ExtTabID : this.DomainWin.getEl().dom.id,
			parent : "PersonInfoObject.DomainWin",
			selectHandler : function(id, name){
				PersonInfoObject.mainPanel.down("[name=DomainDesc]").setValue(name);
				PersonInfoObject.mainPanel.down("[name=DomainID]").setValue(id);
			}
		}
	});
	

	/*showLOV(this.address_prefix + "DocCreditLOV.php?OrderID=" + record.data.OrderID 
					+ "&DocID=" + record.data.DocID + "&UnitID=" + record.data.UnitID, 900, 550);*/
}

PersonInfo.prototype.SaveData = function() {
				
	mask = new Ext.LoadMask(this.mainPanel, {msg:'در حال ذخيره سازي...'});
	mask.show();  
	this.mainPanel.getForm().submit({
		clientValidation: true,
		url: this.address_prefix + 'persons.data.php?task=SavePerson',
		IsUpload : true,
		
		params : {
			PersonID : this.PersonID
		},
		method: "POST",

		success : function(form,result){
			mask.hide();
			Ext.MessageBox.alert("","اطلاعات با موفقیت ذخیره شد");
		},
		failure : function(form,result){
			mask.hide();
			Ext.MessageBox.alert("",result.result.data == "" ? "عملیات مورد نظر با شکست مواجه شد" : result.result.data);
		}
	});
}

</script>