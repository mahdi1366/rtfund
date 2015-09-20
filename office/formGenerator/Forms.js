//---------------------------
// programmer:	Jafarkhani
// create Date:	89.02
//---------------------------

var dvtRenderFlag = false;
function devotionRenderCombo()
{	
	if(dvtRenderFlag)
		return;
	new Ext.form.ComboBox({
	    hiddenName:'devotionID',
		store: BasisData.DevotionStore,		
		emptyText:'جستجوي موقوفه بر اساس نام موقوفه ...',
		typeAhead: false,
		loadingText: 'در حال جستجو...',
		pageSize:10,
		width: 320,
		itemSelector: 'div.search-item',
		applyTo: 'DVT'
				
		,tpl: new Ext.XTemplate(
			'<table width="100%"><tr class="x-grid3-header"><td height="23px" width="40%">نام موقوفه</td>',
			'<td width="30%">سرفصل</td><td width="30%">کلاسه پرونده</td></tr><tr><td colspan="3">',
		    '<tpl for=".">',
		    '<div class="search-item">',
		    '<table width="100%" height="18px"><tr><td width="40%" style="padding-right:15">{dvt3}</td>',
		    '<td width="30%" style="padding-right:15">{dvt2}</td>',
		    '<td width="30%" style="padding-right:15">{dvt10}</td></tr></table></div>',
		    '</tpl>',
		    '</td></tr></table>')						        
		,onSelect: function(record){
			document.getElementById('DVT').value = record.data.dvt3;
			document.getElementById('devotionID').value = record.data.dvt01;
			this.collapse();
		}		
	});
	dvtRenderFlag = true;
}

var stateRenderFlag = false;
function stateRenderCombo()
{
	if(stateRenderFlag)
		return;
	new Ext.form.ComboBox({
	    hiddenName:'stateID',
		store: BasisData.StateStore,		
		emptyText:'جستجوي موقوفه بر اساس نام رقبه ...',
		typeAhead: false,
		loadingText: 'در حال جستجو...',
		pageSize:10,
		width: 320,
		itemSelector: 'div.search-item',
		applyTo: 'STA'
				
		,tpl: new Ext.XTemplate(
			'<table width="100%"><tr class="x-grid3-header"><td height="23px" width="40%">سرفصل رقبه</td>',
			'<td width="60%">کلاسه پرونده</td></tr><tr><td colspan="3">',
		    '<tpl for=".">',
		    '<div class="search-item">',
		    '<table width="100%" height="18px"><tr><td width="40%" style="padding-right:15">{sta2}</td>',
		    '<td width="60%" style="padding-right:15">{sta5}</td></tr></table></div>',
		    '</tpl>',
		    '</td></tr></table>')						        
		,onSelect: function(record){
			document.getElementById('STA').value = record.data.sta2;
			document.getElementById('stateID').value = record.data.sta02;
			this.collapse();
		}		
	});
	stateRenderFlag = true;
}

function changeForm(formDrp)
{
	document.getElementById("select_devotion").style.display = "none";
	document.getElementById("select_state").style.display = "none";
	switch(forms_EXTData[formDrp.selectedIndex - 1]["master1"])
	{
		case "devotions":
			document.getElementById("select_devotion").style.display = "block";
			devotionRenderCombo();
			break;
			
		case "states":
		case "rents":
			document.getElementById("select_state").style.display = "block";
			stateRenderCombo();
			break;
	}
}
var win;
function Adding()
{
	if(!win)
	{
		win = new Ext.Window({
			id: 'win',
			el:'win_selectForm',
			layout:'fit',
			modal: true,
			width:340,
			height:150,
			closeAction:'hide',
			//plain: true,
			items: [
				new Ext.Panel({
					contentEl : "pnl_selectForm",
					title: "انتخاب فرم"
				})
			],
			buttons: [{
			    text:'ایجاد',
			    iconCls:'save',
			    handler: AddForm				    	
			}
			,{
				text: 'انصراف',
				iconCls:'cross',
				
				handler: function(){win.hide();}
			}]
		});	  
	}
	win.show(); 
}

function AddForm()
{
	var formDrp = document.getElementById("FormsList");
	if(formDrp.value == 0)
	{
		alert("ابتدا فرم مورد نظر خود را انتخاب کنید");
		return;
	}	
	var refID = (forms_EXTData[formDrp.selectedIndex - 1]["master1"] == "states") ? 
		document.getElementById("stateID").value : document.getElementById("devotionID").value;
		
	if(refID == "")
	{
		alert("تکمیل آیتم وابسته( موقوفه / رقبه) الزامی است.");
		return;
	} 
	win.hide();
	
	OpenPage("../formGenerator/NewForm.php?FormID=" + document.getElementById("FormsList").value + 
		"&referenceID=" + refID + "&returnTo=../formGenerator/CreatedForms");
	return;
	
}

function Deleting()
{
	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;
		
	var record = dg_grid.selModel.getSelected();

	Ext.Ajax.request({
	  	url : "../formGenerator/wfm.data.php",
	  	method : "POST",
	  	params : {
	  		task : "DeleteLetter",
	  		LetterID : record.data.LetterID
	  	},
	  	
	  	success : function(response,o)
	  	{
	  		dg_store.reload();
	  	}	
	});
}
//.......................................................
function showInfo(page)
{
	var record = dg_grid.selModel.getSelected();
	var RefID = (record.data.RefID) ? record.data.RefID : "0"; 
	var returnTo = "";
	
	if(page == "create")
		returnTo = "../formGenerator/CreatedForms";
	else
	 	returnTo = (!ArchiveFlag) ? "../formGenerator/ReceiveForms" : "archive";
	
	if(page == 'receive' && record.data.ViewFlag == "0")
	{
		Ext.Ajax.request({
		  	url : "../formGenerator/wfm.data.php",
		  	method : "POST",
		  	params : {
		  		task : "ChangeView",
		  		RefID : record.data.RefID,
		  	},
		  	
		  	success : function(response,o)
		  	{
		  		OpenPage("../formGenerator/NewForm.php?LetterID=" + record.data.LetterID + "&FormID=" + 
					record.data.FormID + "&referenceID=" + record.data.referenceID + "&RefID=" + RefID + 
					"&returnTo=" + returnTo);
		  	}	
		});
	}
	else
		OpenPage("../formGenerator/NewForm.php?LetterID=" + record.data.LetterID + "&FormID=" + 
			record.data.FormID + "&referenceID=" + record.data.referenceID + "&RefID=" + RefID + 
			"&returnTo=" + returnTo);
	return;
	
}

function searching()
{
	dg_store.baseParams = {
		task : "ReceivedForms",
		fromDate : document.getElementById("fromDate").value,
		toDate : document.getElementById("toDate").value,
		form : document.getElementById("FormsList").value,
		PersonID : document.getElementById("PersonID").value
	};
	dg_store.reload();
}

var sendWin;
function Sending(type,page)
{
	if(page == "receive")
	{
		var record = dg_grid.selModel.getSelected();
		if(!record)
		{
			alert("ابتدا فرم مورد نظر خود را انتخاب کنید");
			return;
		}
	}
	if(!sendWin)
	{
		sendWin = new Ext.Window({
			id: 'win',
			el:'win_sendForm',
			layout:'fit',
			title: "ارسال فرم",
			modal: true,
			width:575,
			height : 330,
			closeAction:'hide',
			items: [
				new Ext.Panel({
					contentEl : "pnl_sendForm"
				})
			],
			buttons: [{
			    text: (type == "send") ? 'ارسال' : 'بازگشت',
			    iconCls: (type == "send") ? 'send' : 'undo',
			    handler: SendForm.createDelegate(this,[type,page])		    	
			}
			,{
				text: 'انصراف',
				iconCls:'cross',
				handler: function(){sendWin.hide();}
			}],
			
			listeners : {show : function(){
				if(!Ext.getCmp("SendComment"))
				{
					new Ext.form.HtmlEditor({
						id:"SendComment",
						width: 550,
						height: 250,
						enableSourceEdit : false, 
						renderTo : "sendEditor"
					});
				}
				Ext.getCmp("SendComment").setValue();
			}}
		});	 
	}
	sendWin.show();
}

function SendForm(type,page)
{
	sendWin.hide();
	var record = dg_grid.selModel.getSelected();

	Ext.Ajax.request({
	  	url : "../formGenerator/wfm.data.php",
	  	method : "POST",
	  	form : document.getElementById("MainForm"),
	  	params : {
	  		task : (type == "send") ? "SendLetter" : "ReturnLetter",
	  		RefID : record.data.RefID,
	  		LetterID : record.data.LetterID,
	  		FormID : record.data.FormID,
	  		SendType : "ref",
	  		SendComment : document.getElementById("SendComment").value,
	  		StepID : (page == "receive") ? record.data.StepID : "1"
	  	},
	  	
	  	success : function(response,o)
	  	{
	  		dg_store.reload();
	  		if(response.responseText == "true")
	  		{
	  			if(type == "send")
	  				alert("فرم با موفقیت ارسال شد.");
	  			else
	  				alert("فرم با موفقیت برگشت داده شد.");
	  		}
	  		else
	  			alert("عملیات مورد نظر با شکست مواجه شد.مجددا سعی کنید.");
	  	}	
	});
}

function ArchiveForm(archive)
{
	var record = dg_grid.selModel.getSelected();
	if(!record)
	{
		alert("ابتدا فرم مورد نظر خود را انتخاب کنید");
		return;
	}
	
	var message = ArchiveFlag ? "آیا مایل به خروج از بایگانی فرم می باشید؟" : 
		"آیا مایل به بایگانی فرم می باشید؟";
	if(!confirm(message))
		return;
	
	Ext.Ajax.request({
	  	url : "../formGenerator/wfm.data.php",
	  	method : "POST",
	  	params : {
	  		task : "ArchiveLetter",
	  		LetterID : record.data.LetterID,
	  		RefID : record.data.RefID,
	  		ArchiveFlag : archive
	  	},
	  	
	  	success : function(response,o)
	  	{
	  		dg_store.reload();
	  	}	
	});		
}

function DeleteForm()
{
	var record = dg_grid.selModel.getSelected();
	if(!record)
	{
		alert("ابتدا فرم مورد نظر خود را انتخاب کنید");
		return;
	}
	
	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;
	
	Ext.Ajax.request({
	  	url : "../formGenerator/wfm.data.php",
	  	method : "POST",
	  	params : {
	  		task : "DeleteRef",
	  		LetterID : record.data.LetterID,
	  		RefID : record.data.RefID
	  	},
	  	
	  	success : function(response,o)
	  	{
	  		dg_store.reload();
	  	}	
	});	
}

var responseWin;
function Responsing()
{
	var record = dg_grid.selModel.getSelected();
	if(!record)
	{
		alert("ابتدا فرم مورد نظر خود را انتخاب کنید");
		return;
	}
	if(!responseWin)
	{
		responseWin = new Ext.Window({
			id: 'win',
			el:'win_response',
			layout:'fit',
			title: "پاسخ به فرم دریافتی",
			modal: true,
			width:575,
			height : 330,
			closeAction:'hide',
			items: [
				new Ext.Panel({
					contentEl : "pnl_response"
				})
			],
			buttons: [{
			    text:'ذخیره',
			    iconCls:'save',
			    handler: saveResponse			    	
			}
			,{
				text: 'انصراف',
				iconCls:'cross',
				handler: function(){responseWin.hide();}
			}],
			listeners : {show : function(){
				if(!Ext.getCmp("Response"))
				{
					new Ext.form.HtmlEditor({
						id:"Response",
						width: 550,
						height: 250,
						enableSourceEdit : false, 
						renderTo : "responseEditor"
					});
				}
				var record = dg_grid.selModel.getSelected();
				Ext.getCmp('Response').setValue(record.data.Response); 
			}}
		});	 
	}
	responseWin.show();
	
}

function saveResponse()
{
	var record = dg_grid.selModel.getSelected();
	
	Ext.Ajax.request({
	  	url : "../formGenerator/wfm.data.php",
	  	method : "POST",
	  	params : {
	  		task : "ResponseLetter",
	  		LetterID : record.data.LetterID,
	  		RefID : record.data.RefID,
	  		Response : Ext.getCmp("Response").getValue()
	  	},
	  	
	  	success : function(response,o)
	  	{
	  		responseWin.hide();
	  	}	
	});		
}

var AttachWin;
function Attaching(readonly)
{
	var record = dg_grid.selModel.getSelected();
	if(!record)
	{
		alert("ابتدا فرم مورد نظر خود را انتخاب کنید");
		return;
	}
	
	if(!AttachWin)
	{
		AttachWin = new Ext.Window({
			id: 'win',
			el:'div_attach',
			layout:'fit',
			modal: true,
			width:540,
			height:460,			
			closeAction:'hide',
			style : "padding : 5px",
			autoScroll : true		
		});	
	}
	AttachWin.show();
	
	AttachWin.load({url:"../formGenerator/attach.php?LetterID=" + record.data.LetterID + 
		"&readonly=" + readonly, scripts:true});
}

var HistoryWin;
function showHistoryForm()
{
	var record = dg_grid.selModel.getSelected();
	if(!record)
	{
		alert("ابتدا فرم مورد نظر خود را انتخاب کنید");
		return;
	}
	
	if(!HistoryWin)
	{
		HistoryWin = new Ext.Window({
			id: 'win',
			el:'div_history',
			layout:'fit',
			modal: true,
			width:615,
			height:420,			
			closeAction:'hide',
			autoScroll : true		
		});	
	}
	HistoryWin.show();
	HistoryWin.load({url:"../formGenerator/history.php?LetterID=" + record.data.LetterID,scripts:true});
}

var ArchiveWin;
function CoArchiving()
{
	var record = dg_grid.selModel.getSelected();
	if(!record)
	{
		alert("ابتدا فرم مورد نظر خود را انتخاب کنید");
		return;
	}
	
	Ext.Ajax.request({
	  	url : "../formGenerator/wfm.data.php",
	  	method : "POST",
	  	params : {
	  		task : "GetArchiveLevelID",
	  		LetterID : record.data.LetterID
	  	},
	  	
	  	success : function(response,o)
	  	{
	  		if(response.responseText != "")
	  		{
	  			if(!confirm("در حال حاضر فرم در سطح بایگانی [" + response.responseText + "] ذخیره شده است.\n" + 
	  				"آیا مایل به تغییر آن می باشید؟"))
	  				return;
	  		}
  			if(!ArchiveWin)
			{
				ArchiveWin = new Ext.Window({
					id: 'win',
					el:'div_CoArchive',
					layout:'fit',
					modal: true,
					width:420,
					height:445,			
					closeAction:'hide',
					autoScroll : true,
					
					tbar : [{
						id: 'save',
						text: 'ذخیره فرم در سطح بایگانی انتخابی',
						iconCls: 'save',
						handler: SaveArchiveLevel
					}]
				});	
			}
			ArchiveWin.show();
			ArchiveWin.load({url:"../archive/SelectArchive.php",scripts:true});
	  	}	
	});	
}

function SaveArchiveLevel()
{
	if(!tree.getSelectionModel().getSelectedNode())
	{
		alert("ابتدا سطح بایگانی مورد نظر را انتخاب کنید");
		return;
	}
	var record = dg_grid.selModel.getSelected();
	var node = tree.getSelectionModel().getSelectedNode();
	
	Ext.Ajax.request({
	  	url : "../formGenerator/wfm.data.php",
	  	method : "POST",
	  	params : {
	  		task : "SaveArchiveLevelID",
	  		LetterID : record.data.LetterID,
	  		ArchiveLevelID : node.id
	  	},
	  	
	  	success : function(response,o)
	  	{
	  		ArchiveWin.hide();
	  		alert("فرم در سطح بایگانی [" + node.text + "] ذخیره شد.");
	  	}	
	});	
}

//............................................................

function ApplyChanges()
{
	var record = dg_grid.selModel.getSelected();
	
	if(!confirm("آیا مایل به اعمال تغییرات در آیتم مربوطه می باشید؟"))
		return;
		
	var mask = new Ext.LoadMask(document.body,{msg: 'در حال اعمال تغییرات ...'});
	mask.show();
		
	Ext.Ajax.request({
	  	url : "../formGenerator/wfm.data.php",
	  	method : "POST",
	  	params : {
	  		task : "ApplyChanges",
	  		LetterID : record.data.LetterID
	  	},
	  	
	  	success : function(response,o)
	  	{
	  		mask.hide();
	  		if(response.responseText == "true")
	  		{
	  			alert("اعمال تغییرات با موفقیت انجام شد.");
	  		}
	  		else
	  		{
	  			alert("اعمال تغییرات با شکست مواجه شد. لطفا مجددا سعی کنید");
	  		}
	  	}	
	});	
}

