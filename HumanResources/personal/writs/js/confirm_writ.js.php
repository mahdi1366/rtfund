<script type="text/javascript">
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.05
//---------------------------

ConfirmWrit.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	mainPanel : "",
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ConfirmWrit()
{
	this.form = this.get("form_ConfirmWrit");
	
	this.advanceSearchPanel = new Ext.Panel({
		applyTo: this.get("AdvanceSearchDIV"),
		title: "جستجوی پیشرفته",
		autoWidth:true,
		autoHeight: true,
		collapsible : true,
		animCollapse: false,
		frame: true,
		width : 750,
		bodyCfg: {style : "padding-right:10px;background-color:white;"},
		loader : {
			url : this.address_prefix + "advance_search_writ.php",
			scripts: true
		},

		buttons : [{
			text:'جستجو',
			iconCls: 'search',
			handler: function(){ConfirmWritObject.advance_searching();}
		},{
			text : "پاک کردن فرم گزارش",
			iconCls : "clear",
			handler : function(){Ext.get(ConfirmWritObject.form).clear();}
		}]
	});
	this.advanceSearchPanel.loader.load({
		callback: function(){
			advanceSearchObject = new advanceSearch(ConfirmWritObject.form);
			ConfirmWritObject.advanceSearchPanel.doLayout();
		}
	});

	this.afterLoad();
}

ConfirmWrit.prototype.advance_searching = function()
{ 
	this.advanceSearchPanel.collapse();
	this.get("possibleWrits").style.display = "block";
	this.grid.render(this.get("result"));
	this.grid.getStore().load();
}

ConfirmWrit.CheckRender = function(v,p,r)
{
	str = '<input type="checkbox" id="chk_' + r.data.writ_id + '_' + r.data.writ_ver + "_" + r.data.staff_id + '" ' +
			'name="chk_' + r.data.writ_id + '_' + r.data.writ_ver + "_" + r.data.staff_id + '" ' +
			(v == "1" ? "checked" : "") + ' onclick="ConfirmWritObject.changeConfirm(this);">'+
			'<input type="hidden" id="hdn_' + r.data.writ_id + '_' + r.data.writ_ver + "_" + r.data.staff_id + '"\n\
				name="hdn_' + r.data.writ_id + '_' + r.data.writ_ver + "_" + r.data.staff_id + '">';
	return str;
}

ConfirmWrit.prototype.changeConfirm = function(checkboxEl)
{
	this.get(checkboxEl.id.replace(/chk_/g, "hdn_")).value = checkboxEl.checked ? "1" : "0";
}

ConfirmWrit.prototype.selectAll = function(selectAllElem)
{
	var elems = this.form.getElementsByTagName("input");
	for(i=0; i < elems.length; i++)
		if(elems[i].id.indexOf("chk_") != -1)
		{
			elems[i].checked = selectAllElem.checked;
			this.changeConfirm(elems[i]);
		}
}

ConfirmWrit.prototype.confirm = function()
{
	var mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال تایید احکام ...'});
	mask.show();
	
	Ext.Ajax.request({
		
		url : this.address_prefix + "../data/writ.data.php",
		method : "POST",
		params : {
			task : "confirmAction"
		},
		form : this.get("form_selectedWrits"),
		
		success : function(response,op){
			if(response.responseText == "true")
			{
				alert("تایید احکام با موفقیت انجام شد.");
				mask.hide();
				ConfirmWritObject.grid.getStore().load();
				ConfirmWritObject.advanceSearchPanel.collapse();
			}
			else
			{
				alert("عملیات مورد نظر با شکست مواجه شد.");
				mask.hide();
			}
		}
	});
}

</script>