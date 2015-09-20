<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.03
//-----------------------------

require_once '../../header.inc.php';

?>
<script>
shareBack.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function shareBack()
{
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		bodyStyle : "text-align:right;padding:5px",
		title : "استرداد سهام",
		defaults : {
			labelWidth :150
		},
		width : 500,
		items :[{
			xtype : "shdatefield",
			itemId : "cmp_DayDate",
			fieldLabel : "تاریخ روز انجام عملیات"
		},{
			xtype : "combo",
			itemId : "cmp_tafsiliID",
			anchor : "100%",
			fieldLabel : "انتخاب تفصیلی سهامدار",
			store: new Ext.data.Store({
				fields:["TafsiliID","tafsiliTitle","shareNo"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../../sale/data/shareholders.data.php?task=select',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			}),
			tpl: new Ext.XTemplate(
					'<table cellspacing="0" width="100%"><tr class="x-grid-header-ct">'
						,'<td height="23px">نام و نام خانوادگی</td>'
						,'<td>شماره اشتراک</td>'
					,'<tpl for=".">'
					,'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
						,'<td style="border-left:0;border-right:0" class="search-item">{tafsiliTitle}</td>'
						,'<td style="border-left:0;border-right:0" class="search-item">{shareNo}</td></tr>'
					,'</tpl>'
					,'</table>'),
			emptyText:'انتخاب تفصیلی ...',
			typeAhead: false,
			pageSize : 10,
			displayField : "tafsiliTitle",
			valueField : "TafsiliID",
			listConfig: {
				loadingText: 'در حال جستجو...',
				emptyText: 'فاقد اطلاعات'
			}
		},{
			xtype : "displayfield",
			itemId : "cmp_shareAmount",
			fieldLabel : "مبلغ کل سهام"
		}],
		buttons : [{
			text : "مشاهده مانده سهام",
			handler : function(){shareBackObj.showRemainder();},
			iconCls : "view"
		},{
			text : "استرداد سهام",
			handler : function(){shareBackObj.shareBack();},
			iconCls : "undo",
			itemId : "btn_shareBack",
			disabled : true
		}]
	});
}

shareBackObj = new shareBack();

shareBack.prototype.showRemainder = function()
{
	Ext.Ajax.request({
		method : "POST",
		url : this.address_prefix + "../data/tafsilis.data.php?task=getTafsiliRemainder",
		params : {
			tafsiliID : this.formPanel.down("[itemId=cmp_tafsiliID]").getValue()
		},
		success : function(response)
		{
			if(response.responseText == "DONE_BEFOR")
			{
				alert("سهام این فرد قبلا مسترد شده است");
				shareBackObj.formPanel.down("[itemId=cmp_shareAmount]").setValue("");
				return;
			}
			shareBackObj.formPanel.down("[itemId=cmp_shareAmount]").setValue(Ext.util.Format.Money(response.responseText) + " ریال");
			shareBackObj.formPanel.down("[itemId=btn_shareBack]").enable();
			shareBackObj.remainder = response.responseText;
		}
	});
}

shareBack.prototype.shareBack = function(btn, e)
{
	Ext.Ajax.request({
		method : "POST",
		url : this.address_prefix + "../data/acc_docs.data.php?task=shareBack",
		params : {
			TafsiliID : this.formPanel.down("[itemId=cmp_tafsiliID]").getValue(),
			remainder : this.remainder,
			DayDate : this.formPanel.down("[itemId=cmp_DayDate]").getRawValue()
		},
		success : function(response)
		{
			var dt = Ext.decode(response.responseText);
			if(dt.success)
			{
				alert("سند مربوط به استرداد سهام با موفقیت صادر گردید");
				shareBackObj.formPanel.down("[itemId=btn_shareBack]").disable();
				shareBackObj.formPanel.getForm().reset();
			}
			else
				alert("عملیات مورد نظر با شکست مواجه شد");
		}
	});
}

</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
</form>