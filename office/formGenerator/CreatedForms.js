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














