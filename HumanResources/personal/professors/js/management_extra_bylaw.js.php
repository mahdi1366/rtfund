<script type="text/javascript">
//---------------------------
// programmer:	SH.Jafarkhani
// Date:		90.08
//---------------------------

ByLaw.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ByLaw()
{
	this.form = this.get("mainForm");

	this.grid = <?= $grid?>;
	this.grid.render(this.get("divGRID"));

	this.itemsgrid = <?= $itemsgrid?>;

	this.newItemWin = new Ext.Window({
		applyTo:this.get("newWin"),
		title: "ایجاد آیتم جدید",
		contentEl : this.get("newPnl"),
		layout:'fit',
		modal: true,
		width : 400,
		autoHeight : true,
		closeAction:'hide',
		buttons : [{
			text : "ذخیره",
			iconCls : "save",
			handler : function(){
				ByLawObject.SaveItem(null,
					{data:{
						bylaw_id : ByLawObject.get("bylaw_id").value,
						post_id : ByLawObject.get("post_id").value,
						value : ByLawObject.get("value").value
					}});
				}
			},
			{
				text : "انصراف",
				handler: function(){ByLawObject.newItemWin.hide();}
			}
		]
	});

	new Ext.form.TriggerField({
	    triggerCls:'x-form-search-trigger',
        inputId :'post_id',
	    onTriggerClick : function(){
			returnVal = LOV_Post('ALL');
          
			if(returnVal != "")
			{aa = returnVal;
			
				this.setValue(returnVal.post_id);
				ByLawObject.get("post_title").innerHTML = returnVal.post_no + "-" + returnVal.post_title;
			}

	    },
	    applyTo : this.get("post_id"),
	    width : 120
	});
}

var ByLawObject = new ByLaw();

ByLaw.prototype.Add = function()
{

    var modelClass = this.grid.getStore().model;
	var record = new modelClass({
		bylaw_id: "",
		from_date: null,
		to_date: null,
		description: null
	});
	this.grid.plugins[0].cancelEdit();
	this.grid.getStore().insert(0, record);
	this.grid.plugins[0].startEdit(0, 0);
	
}

ByLaw.prototype.Save = function(store,record)
{
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/management_extra_bylaw.data.php?task=save',
		method: 'POST',
		params: {
			record : Ext.encode(record.data)
		},

		success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				ByLawObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

ByLaw.prototype.Copy = function()
{ 
    var record = this.grid.getSelectionModel().getLastSelected();
    
    if(!record )
    {
        alert("لطفا ابتدا رکورد مورد نظر را انتخاب نمایید سپس بر روی دکمه کپی کلیک نمایید .");
        return

    }
    
    mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال انجام عملیات ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/management_extra_bylaw.data.php?task=copy',
		method: 'POST',
		params: {
			record : Ext.encode(record.data)
		},

		success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				ByLawObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

ByLaw.prototype.DeleteRender = function(v,p,r)
{
	return "<div title='حذف' class='remove' onclick='ByLawObject.Remove(" + r.data.bylaw_id + ",false);' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
}

ByLaw.prototype.itemsRender = function(v,p,r)
{
	return "<div title='اقلام' class='list' onclick='ByLawObject.LoadItems(" + r.data.bylaw_id + ");' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
}

ByLaw.prototype.Remove = function(bylaw_id, deleteItemsFlag)
{
	if(!deleteItemsFlag)
		if(!confirm("آیا مایل به حذف می باشید؟"))
			return;
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال حذف ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/management_extra_bylaw.data.php?task=delete',
		method: 'POST',
		params : {
			bylaw_id : bylaw_id,
			deleteItemsFlag : deleteItemsFlag
		},

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				ByLawObject.grid.getStore().load();
			}
			else if(st.data == "NOT-EMPTY")
			{
				if(confirm("این بخشنامه شامل اقلام می باشد. آیا مایل به حذف کلیه اقلام و سپس بخشنامه می باشید؟"))
					ByLawObject.Remove(bylaw_id, true);
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

ByLaw.prototype.LoadItems = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();
	this.grid.collapse();
	this.get("bylaw_id").value = record.data.bylaw_id;
    if(this.itemsgrid.rendered == true)
        this.itemsgrid.getStore().load();
    else
        this.itemsgrid.render("divItemsGrid");
	
}


//..............................................................................

ByLaw.prototype.AddItem = function()
{
	this.newItemWin.show();
}

ByLaw.prototype.SaveItem = function(store,record)
{   
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال ذخیره سازی ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/management_extra_bylaw.data.php?task=saveItem',
		method: 'POST',
		params: {
			record : Ext.encode(record.data)
		},

		success: function(response,option){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				ByLawObject.itemsgrid.getStore().load();
				ByLawObject.newItemWin.hide();
			}
			else
			{
				alert(st.data);
			}
		},
		failure: function(){}
	});
}

ByLaw.prototype.ItemDeleteRender = function(v,p,r)
{
	return "<div title='حذف' class='remove' onclick='ByLawObject.RemoveItem(" + r.data.bylaw_id + "," + r.data.post_id + ");' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
}

ByLaw.prototype.RemoveItem = function(bylaw_id, post_id)
{
	if(!confirm("آیا مایل به حذف می باشید؟"))
		return;
	mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال حذف ...'});
	mask.show();

	Ext.Ajax.request({
		url: this.address_prefix + '../data/management_extra_bylaw.data.php?task=deleteItem',
		method: 'POST',
		params : {
			bylaw_id : bylaw_id,
			post_id : post_id
		},

		success: function(response){
			mask.hide();
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				ByLawObject.itemsgrid.getStore().load();
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