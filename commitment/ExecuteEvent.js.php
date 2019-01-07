<script type="text/javascript">
//-----------------------------
//	Programmer	: Sh.Jafarkhani
//	Date		: 97.05
//-----------------------------

ExecuteEvent.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"] ?>',
	address_prefix : "<?= $js_prefix_address ?>",
	EventID : <?= $EventID?>,
	SourcesArr : <?= common_component::PHPArray_to_JSObject($SourcesArr) ?>,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ExecuteEvent(){

	this.RowDetails = {
		ptype: 'rowexpander',
		rowBodyTpl : [
			'<hr>','عنوان حساب: <span class=blueText>{CostDesc}</span><br>',
			'آیتم محاسباتی: <span class=blueText>',
				'{[values.ComputeItemDesc == null ? "---" : values.ComputeItemDesc]}</span><br>',			
			'گروه تفصیلی: <span class=blueText>{TafsiliTypeDesc} - ',
				'{[values.TafsiliDesc == null ? "" : values.TafsiliDesc]}</span><br>',
			'گروه تفصیلی2: <span class=blueText>{TafsiliType2Desc} - ',
				'{[values.Tafsili2Desc == null ? "" : values.Tafsili2Desc]}</span><br>',
			'گروه تفصیلی3: <span class=blueText>{TafsiliType3Desc} - ',
				'{[values.Tafsili3Desc == null ? "" : values.Tafsili3Desc]}</span><br>',
		]
	};

	this.grid = <?=  $grid?>;
	this.grid.getStore().on("load", function(){
		for(var i=0; i<this.totalCount; i++)
		{
			record = this.getAt(i);
			if(record.data.ComputeItemID*1 == 0)
			{
				new Ext.form.CurrencyField({
					renderTo : record.data.CostType == "DEBTOR" ?
						ExecuteEventObj.get("DebtorAmount_" + record.data.RowID) :	
						ExecuteEventObj.get("CreditorAmount_" + record.data.RowID),
					hideTrigger : true,
					name :  record.data.CostType == "DEBTOR" ? 
						"DebtorAmount_" + record.data.RowID : "CreditorAmount_" + record.data.RowID,
					width : 120
				});
			}
			if(record.data.Tafsili == "0" && record.data.TafsiliType*1 > 0)
			{
				new Ext.form.ComboBox({
					renderTo : ExecuteEventObj.get("TafsiliID1_" + record.data.RowID),
					width : 170,
					store: new Ext.data.Store({
						fields:["TafsiliID","TafsiliDesc"],
						proxy: {
							type: 'jsonp',
							url: this.address_prefix + '../baseinfo/baseinfo.data.php',
							params : {
								task : "GetAllTafsilis",
								TafsiliType : record.data.TafsiliType
							},
							reader: {root: 'rows',totalProperty: 'totalCount'}
						}
					}),
					emptyText:'انتخاب تفصیلی ...',
					allowBlank : false,
					name : "TafsiliID_" + record.data.RowID,
					valueField : "TafsiliID",
					displayField : "TafsiliDesc"
				});
			}
			if(record.data.Tafsili2 == "0" && record.data.TafsiliType2*1 > 0)
			{
				new Ext.form.ComboBox({
					renderTo : ExecuteEventObj.get("TafsiliID2_" + record.data.RowID),
					width : 170,
					store: new Ext.data.Store({
						fields:["TafsiliID","TafsiliDesc"],
						proxy: {
							type: 'jsonp',
							url: this.address_prefix + '../baseinfo/baseinfo.data.php',
							params : {
								task : "GetAllTafsilis",
								TafsiliType : record.data.TafsiliType
							},
							reader: {root: 'rows',totalProperty: 'totalCount'}
						}
					}),
					emptyText:'انتخاب تفصیلی ...',
					allowBlank : false,
					name : "TafsiliID2" + record.data.RowID,
					valueField : "TafsiliID",
					displayField : "TafsiliDesc"
				});
			}
			if(record.data.Tafsili3 == "0" && record.data.TafsiliType3*1 > 0)
			{
				new Ext.form.ComboBox({
					renderTo : ExecuteEventObj.get("TafsiliID3_" + record.data.RowID),
					width : 170,
					store: new Ext.data.Store({
						fields:["TafsiliID","TafsiliDesc"],
						proxy: {
							type: 'jsonp',
							url: this.address_prefix + '../baseinfo/baseinfo.data.php',
							params : {
								task : "GetAllTafsilis",
								TafsiliType : record.data.TafsiliType
							},
							reader: {root: 'rows',totalProperty: 'totalCount'}
						}
					}),
					emptyText:'انتخاب تفصیلی ...',
					allowBlank : false,
					name : "TafsiliID3" + record.data.RowID,
					valueField : "TafsiliID",
					displayField : "TafsiliDesc"
				});
			}
		}
	});
	this.grid.render(this.get("div_grid"));
	this.grid.getStore().proxy.form = this.get("MainForm");

}

ExecuteEvent.DebtorAmountRenderer = function(v,p,r){
	if(r.data.ComputeItemID*1 > 0)
		return Ext.util.Format.Money(v);
	else
		return "<div id=DebtorAmount_" + r.data.RowID + "></div>";
}

ExecuteEvent.CreditorAmountRenderer = function(v,p,r){
	if(r.data.ComputeItemID*1 > 0)
		return Ext.util.Format.Money(v);
	else
		return "<div id=CreditorAmount_" + r.data.RowID + "></div>";
}

ExecuteEvent.TafsiliRenderer1 = function(v,p,r){
	if(v == "0" && r.data.TafsiliType*1 > 0)
		return "<div id=TafsiliID1_" + r.data.RowID + "></div>";
	else
		return r.data.TafsiliValue1;
}
ExecuteEvent.TafsiliRenderer2 = function(v,p,r){
	if(v == "0" && r.data.TafsiliType2 != "0")
		return "<div id=TafsiliID2_" + r.data.RowID + "></div>";
	else
		return r.data.TafsiliValue2;
}
ExecuteEvent.TafsiliRenderer3 = function(v,p,r){
	if(v == "0" && r.data.TafsiliType3 != "0")
		return "<div id=TafsiliID3_" + r.data.RowID + "></div>";
	else
		return r.data.TafsiliValue3;
}

ExecuteEventObj = new ExecuteEvent();

ExecuteEvent.prototype.RegisterEventDoc = function(){

	Ext.MessageBox.confirm("", "آیا مایل به صدور سند می باشید؟", function(btn){
		if(btn == "no")
			return;
			
		me = ExecuteEventObj;
		mask = new Ext.LoadMask(Ext.getCmp(me.TabID), {msg:'در حال ذخیره سازی ...'});
		mask.show();

		Ext.Ajax.request({
			url: me.address_prefix + 'ExecuteEvent.data.php?task=RegisterEventDoc',
			params : {
				EventID : me.EventID,
				"SourcesArr[]" : me.SourcesArr
			},
			method: 'POST',
			form : me.get("MainForm"),

			success : function(response){
				mask.hide();
				result = Ext.decode(response.responseText);
				if(result.success)
				{
					Ext.MessageBox.alert("", result.data);
					framework.EventWindow.hide();
				}
				else
				{
					Ext.MessageBox.alert("خطا", result.data);
				}
			},
			failure : function(){mask.hide();}
		});
	});
}

</script>
