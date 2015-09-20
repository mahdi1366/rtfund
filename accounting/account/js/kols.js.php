<script type="text/javascript">
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 90.10
//-----------------------------

Kol.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function Kol()
{
	this.form = this.get("mainForm");
}

var KolObject = new Kol();

Kol.deleteRender = function()
{
	return  "<div title='حذف اطلاعات' class='remove' onclick='KolObject.remove();' " +
			"style='background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;height:16'></div>";
}

Kol.prototype.Add = function()
{
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		KolID: "",
		title: null
		
	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

Kol.prototype.Save = function(store,record)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/kols.data.php?task=saveKol',
		method: 'POST',
		params: {
			record : Ext.encode(record.data),
			oldKolID : record.raw ? record.raw.kolID : ""
		},
		form : this.get("KolForm"),

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				KolObject.grid.getStore().load();
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

Kol.prototype.remove = function()
{
	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();


	Ext.Ajax.request({
		url: this.address_prefix + '../data/kols.data.php?task=removeKol',
		params:{
			kolID: record.data.kolID
		},
		method: 'POST',

		success: function(response){
			mask.hide();
			if(response.responseText == "conflict")
				alert('این آیتم در جای دیگری استفاده شده و قابل حذف نمی باشد.');
			else
				KolObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

</script>