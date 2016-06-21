<script type="text/javascript">
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.05
//---------------------------

TransferWrit.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	mainPanel : "",
	advanceSearchObject : "",
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function TransferWrit()
{
	this.form = this.get("form_TransferWrit");
	
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
			handler: function(){TransferWritObject.advance_searching();}
		},{
			text : "پاک کردن فرم گزارش",
			iconCls : "clear",
			handler : function(){Ext.get(TransferWritObject.form).clear();}
		}]
	});
	this.advanceSearchPanel.loader.load({
		callback: function(){
			advanceSearchObject = new advanceSearch(TransferWritObject.form);
			TransferWritObject.advanceSearchPanel.doLayout();
		}
	});

	this.afterLoad();

}

TransferWrit.prototype.advance_searching = function()
{
	this.advanceSearchPanel.collapse();
	this.get("possibleWrits").style.display = "block";
    if(this.grid.rendered == true )
       this.grid.getStore().load();
    else
       this.grid.render(this.get("result"));
	
}

TransferWrit.CheckRender = function(v,p,r)
{
	str = '<input type="checkbox" id="chk_' + r.data.writ_id + '_' + r.data.writ_ver + "_" + r.data.staff_id + '" ' +
			'name="chk_' + r.data.writ_id + '_' + r.data.writ_ver + "_" + r.data.staff_id + '_' + r.data.execute_date + '">';
	return str;
}

TransferWrit.prototype.selectAll = function(selectAllElem)
{
	var elems = this.form.getElementsByTagName("input");
	for(i=0; i < elems.length; i++)
		if(elems[i].id.indexOf("chk_") != -1)
			elems[i].checked = selectAllElem.checked;
}

TransferWrit.prototype.tranfering = function()
{
	var mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال انجام عملیات ...'});
	mask.show();
	
	Ext.Ajax.request({
		
		url : this.address_prefix + "../data/writ.data.php",
		method : "POST",
		params : {
			task : "transferAction",
			new_state : this.new_state,
			mode: '<?= $returnFlag ? "return" : ""?>'
		},
		form : this.get("form_selectedWrits"),
		
		success : function(response,op){
			if(response.responseText == "true")
			{
				alert("<?= $action_title?> با موفقیت انجام شد.");
				mask.hide();
				TransferWritObject.grid.getStore().load();
				TransferWritObject.advanceSearchPanel.collapse();
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