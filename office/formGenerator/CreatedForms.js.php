<script>
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.02
//---------------------------

var sendWin;
function Sending()
{
	if(!sendWin)
	{
		sendWin = new Ext.Window({
			id: 'win',
			el:'win_sendForm',
			layout:'fit',
			title: "ارسال فرم",
			modal: true,
			width:450,
			autoHeight : true,
			closeAction:'hide',
			items: [
				new Ext.Panel({
					contentEl : "pnl_sendForm"
				})
			],
			buttons: [{
			    text: (type == "send") ? 'ارسال' : 'بازگشت',
			    iconCls:'send',
			    handler: SendForm.createDelegate(this,[type])		    	
			}
			,{
				text: 'انصراف',
				iconCls:'undo',
				handler: function(){sendWin.hide();}
			}]
		});	 
	}
	sendWin.show();
}

function SendForm()
{
	sendWin.hide();
	var record = dg_grid.selModel.getSelected();

	Ext.Ajax.request({
	  	url : "../formGenerator/wfm.data.php",
	  	method : "POST",
	  	form : document.getElementById("MainForm"),
	  	params : {
	  		task : "SendLetter",
	  		LetterID : record.data.LetterID,
	  		FormID : record.data.FormID,
	  		SendType : "ref",
	  		SendComment : document.getElementById("SendComment").value
	  	},
	  	
	  	success : function(response,o)
	  	{
	  		dg_store.reload();
	  	}	
	});
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

function AddingAction(){Adding();};

function referenceRender(v,p,r)
{
	var referenceName;
	switch(r.data.reference)
	{
		case "devotions": referenceName = "موقوفه";break;
		case "states": referenceName = "رقبه";break;
		case "rents": referenceName = "اجاره";break;
	}
	
	return referenceName + " کد " + v;
}
function operationRender(v,p,r)
{
	return "<div align='center' title='مشاهده فرم' onclick='operationMenu(event);' " +
		"style='background-repeat:no-repeat;background-position:center;"+
		"background-image:url(images/setting.gif);" +
		"cursor:pointer;width:100%;height:16'></div>";
}
function operationMenu(e)
{
	var record = dg_grid.selModel.getSelected();
	var op_menu = new Ext.menu.Menu();
	
	op_menu.add({text: 'مشاهده فرم',iconCls: 'info',handler : function(){showInfo('create');} });
	
	var readonly = (record.data.StepID == '1') ? "false" : "true";
	op_menu.add({text: 'پیوست',iconCls: 'attach',handler : function(){Attaching(readonly);} });
	
	if(record.data.StepID == '1')
	{
		op_menu.add({text: 'ارسال فرم',iconCls: 'send',handler : function(){Sending('send','create');} });
		op_menu.add({text: 'حذف',iconCls: 'remove',handler : function(){Deleting();} });
	}
	op_menu.add({text: 'سابقه',iconCls: 'history',handler : function(){showHistoryForm();} });
	
	op_menu.showAt([e.clientX-100, e.clientY+5]);
}

function unloadFn()
{
	if(win)
	{
		win.destroy();
		win = null;
	}
	if(sendWin)
	{
		sendWin.destroy();
		sendWin = null;
	}
	if(AttachWin)
	{
		AttachWin.destroy();
		AttachWin = null;
	}
	if(HistoryWin)
	{
		HistoryWin.destroy();
		HistoryWin = null;
	}
}
</script>