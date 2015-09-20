<script type="text/javascript">
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 90.10
//-----------------------------

Moin.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function Moin()
{
	this.form = this.get("mainForm");
	
	this.kolCombo = new Ext.form.ComboBox({
		store : new Ext.data.Store({
			fields : ["kolID","kolTitle"],
			proxy : {
				type : "jsonp",
				url : this.address_prefix + "../data/kols.data.php?task=selectKol",
				reader : {
					root : "rows",
					totalProperty : "totalCount"
				}
			},
			autoLoad : true
		}),
		queryMode : 'local',
		displayField : "kolTitle",
		valueField : "kolID",
		hiddenName : "kolID"
	});
}

var MoinObject = new Moin();

Moin.deleteRender = function()
{
	return  "<div title='حذف اطلاعات' class='remove' onclick='MoinObject.remove();' " +
			"style='background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;height:16'></div>";
}

Moin.prototype.Add = function()
{
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		MoinID: "",
		title: null
		
	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

Moin.prototype.Save = function(store,record)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/moins.data.php?task=saveMoin',
		method: 'POST',
		params: {
			record : Ext.encode(record.data),
			oldID : record.raw ? record.raw.moinID : ""
		},
		form : this.get("MoinForm"),

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				MoinObject.grid.getStore().load();
			}
			else if(st.data == "duplicate")
			{
				alert("کد وارد شده تکراری می باشد.")
			}
			else if(st.data == "used")
			{
				alert("این حساب در جای دیگری استفاده شده است و قابل تغییر نیست")
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

Moin.prototype.remove = function()
{
	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();


	Ext.Ajax.request({
		url: this.address_prefix + '../data/moins.data.php?task=removeMoin',
		params:{
			kolID: record.data.kolID,
			moinID: record.data.moinID
		},
		method: 'POST',

		success: function(response){
			mask.hide();
			if(response.responseText == "conflict")
				alert('این آیتم در جای دیگری استفاده شده و قابل حذف نمی باشد.');
			else
				MoinObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

</script>