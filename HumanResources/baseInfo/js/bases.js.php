<script type="text/javascript">
//---------------------------
// programmer:	Jafarkhani
// create Date:	91.04
//---------------------------

Base.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function Base()
{
	this.baseTypeCombo = new Ext.form.ComboBox({
		store : Ext.data.Store({
			fields : ['InfoID','Title','param1'],
			data : <?= json_encode($BaseTypesDT) ?>
		}),
		typeAhead: true,
		displayField : 'Title',
		valueField : 'InfoID',
		triggerAction: 'all',
		lazyRender: true,
		listClass: 'x-combo-list-small',
		listeners : {
			Select: function(combo, records){
				if(records[0].data.param1 > 0 ) 
                                    {
                                        combo.ownerCt.down("[itemId=cmp_baseValue]").setValue(records[0].data.param1 == null ? 0 : records[0].data.param1); 
                                        combo.ownerCt.down("[itemId=cmp_baseValue]").setMaxValue(records[0].data.param1 == null ? 5 : records[0].data.param1);
                                    }
				
				combo.ownerCt.down("[itemId=cmp_baseValue]").setMinValue(1);
				BaseObject.grid.getStore().getAt(0).data.BaseType = records[0].data.InfoID;
			}
		}
	});
	this.personCombo = new Ext.form.ComboBox({
		store: personStore,
		emptyText:'جستجوي استاد/كارمند بر اساس نام و نام خانوادگي ...',
		typeAhead: false,
		listConfig :{
			loadingText: 'در حال جستجو...'
		},
		pageSize:10,
		width: 200,

		tpl: new Ext.XTemplate(
			'<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
				,'<td>نام</td>'
				,'<td>نام خانوادگی</td>'
				,'<td>نوع شخص</td>'
				,'<td>واحد محل خدمت</td></tr>',
			'<tpl for=".">',
			'<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
				,'<td style="border-left:0;border-right:0" class="search-item">{pfname}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{plname}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{personTypeName}</td>'
				,'<td style="border-left:0;border-right:0" class="search-item">{unit_name}&nbsp;</td></tr>',
			'</tpl>'
			,'</table>')

		,listeners : {
			Select: function(combo, records){
				this.setValue(records[0].data.pfname + " " + records[0].data.plname);
				BaseObject.grid.getStore().getAt(0).data.PersonID = records[0].data.PersonID;
				this.collapse();
			}
		}
	});
	
}

var BaseObject = new Base();

Base.DeleteRender = function(value, p, record)
{
    if(record.data.BaseMode == "SYSTEM" || record.data.BaseStatus == "DELETED")
		return "";
    return   "<div  title='حذف اطلاعات' class='remove' onclick='BaseObject.deleteBase();' " +
			 "style='float:left;background-repeat:no-repeat;background-position:center;" +
			 "cursor:pointer;width:50%;height:16'></div>";
}

Base.prototype.AddBase = function()
{
    var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		RowID: "",
		title: null
	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
    
}

Base.prototype.SaveBase = function(store,record)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/bases.data.php?task=SaveBase',
		method: 'POST',
		params: {
			record : Ext.encode(record.data)
		},

		success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				BaseObject.grid.getStore().load();
			}
			else
			{
				if(st.data == "OverMaxCGroup")
					alert("مجموع گروه های تشویقی یک فرد نمی تواند از 5 بیشتر باشد");
				else
					alert(st.data);
			}
		},
		failure: function(){}
	});
}

Base.prototype.deleteBase = function()
{
	if(!confirm("آیا از حذف اطمینان دارید؟"))
		return;
	
	var record = this.grid.getSelectionModel().getLastSelected();
	
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();


	Ext.Ajax.request({
		url: this.address_prefix + '../data/bases.data.php?task=removeBase',
		params:{
			RowID: record.data.RowID
		},
		method: 'POST',
        success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حذف با موفقیت انجام شد.");
				BaseObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}		
	});
}

</script>