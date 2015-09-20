<script type="text/javascript">
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 90.10
//-----------------------------

Tafsili.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function Tafsili()
{
	this.tafsiliCombo = new Ext.form.ComboBox({
		store: new Ext.data.Store({
			fields:["tafsiliID","tafsiliTitle"],
			proxy: {
				type: 'jsonp',
				url: this.address_prefix + '../data/tafsilis.data.php?task=selectTafsili',
				reader: {root: 'rows',totalProperty: 'totalCount'}
			}
		}),
		emptyText:'انتخاب تفصیلی ...',
		typeAhead: false,
		pageSize : 10,
		displayField : "tafsiliTitle",
		listConfig: {
			loadingText: 'در حال جستجو...',
			emptyText: 'فاقد اطلاعات'
		}		
	});
	
	new Ext.panel.Panel({
		renderTo: this.get("div_filter"),
		contentEl : this.get("tbl_filter"),
		title : "جستجوی تفصیلی",
		width : 500,
		frame:true,
		autoHeight : true,
		bodyBorder: false,
		buttons : [{
			text : "جستجو",
			iconCls : "search",
			handler : function()
			{
				TafsiliObject.grid.getStore().load();
			}
		}]
	});
	
	Ext.get(this.get("tbl_filter")).addKeyListener(13, function(){TafsiliObject.grid.getStore().load();});
	
	this.form = this.get("mainForm");
}

var TafsiliObject = new Tafsili();

Tafsili.deleteRender = function(v,p,r)
{
	if(r.data.IsActive == "0")
		return "";
	return  "<div title='حذف اطلاعات' class='remove' onclick='TafsiliObject.remove();' " +
			"style='background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;height:16'></div>";
}

Tafsili.prototype.Add = function()
{
	var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		TafsiliID: "",
		title: null
		
	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
}

Tafsili.prototype.Save = function(store,record)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/tafsilis.data.php?task=saveTafsili',
		method: 'POST',
		params: {
			record : Ext.encode(record.data),
			oldID : record.raw ? record.raw.tafsiliID : ""
		},
		form : this.get("TafsiliForm"),

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				TafsiliObject.grid.getStore().load();
			}
			else if(st.data == "duplicate")
			{
				alert("کد یا عنوان وارد شده تکراری می باشد.");
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

Tafsili.prototype.remove = function()
{
	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();


	Ext.Ajax.request({
		url: this.address_prefix + '../data/tafsilis.data.php?task=removeTafsili',
		params:{
			tafsiliID: record.data.tafsiliID
		},
		method: 'POST',

		success: function(response){
			mask.hide();
			if(response.responseText == "conflict")
				alert('این آیتم در جای دیگری استفاده شده و قابل حذف نمی باشد.');
			else
				TafsiliObject.grid.getStore().load();
		},
		failure: function(){}
	});
}

</script>