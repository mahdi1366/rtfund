<script type="text/javascript">
//---------------------------
// programmer:	B.Mahdipour
// create Date: 93.08
//---------------------------

ArrearTransferWrit.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	mainPanel : "",
	advanceSearchObject : "",
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function ArrearTransferWrit()
{
	this.form = this.get("form_ATransferWrit");
	
	this.advanceSearchPanel = new Ext.Panel({
		applyTo: this.get("AdvanceASearchDIV"),
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
			handler: function(){ArrearTransferWritObject.advance_searching();}
		},{
			text : "پاک کردن فرم گزارش",
			iconCls : "clear",
			handler : function(){Ext.get(ArrearTransferWritObject.form).clear();}
		}]
	});
	this.advanceSearchPanel.loader.load({
		callback: function(){
			advanceSearchObject = new advanceSearch(ArrearTransferWritObject.form);
			ArrearTransferWritObject.advanceSearchPanel.doLayout();
		}
	});

	this.afterLoad();

}

ArrearTransferWrit.prototype.advance_searching = function()
{
	this.advanceSearchPanel.collapse();
	this.get("possibleAWrits").style.display = "block";
    if(this.grid.rendered == true )
       this.grid.getStore().load();
    else
       this.grid.render(this.get("result"));
	
}

ArrearTransferWrit.CheckRender = function(v,p,r)
{
	str = '<input type="checkbox" id="chk_' + r.data.writ_id + '_' + r.data.writ_ver + "_" + r.data.staff_id + '" ' +
			'name="chk_' + r.data.writ_id + '_' + r.data.writ_ver + "_" + r.data.staff_id + '_' + r.data.execute_date + '">';
	return str;
}

ArrearTransferWrit.opRender = function(value, p, record)
{
	var st = "";
	st += "<div  title='مشاهده اطلاعات' class='view' onclick='ArrearTransferWritObject.ShowWrit();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";	
	return st;
}

ArrearTransferWrit.prototype.ShowWrit = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();

	framework.OpenPage(this.address_prefix + "../../writs/ui/view_writ.php", "مشاهده حکم",
		 {
			 WID : record.data.writ_id,
             STID : record.data.staff_id,
             WVER : record.data.writ_ver ,
			 PID : record.data.PersonID,
             ExeDate : record.data.execute_date,
			 FacilID : <?= $_REQUEST["FacilID"]?>
		 });
}	

ArrearTransferWrit.prototype.selectAll = function(selectAllElem)
{
	var elems = this.form.getElementsByTagName("input");
	for(i=0; i < elems.length; i++)
		if(elems[i].id.indexOf("chk_") != -1)
			elems[i].checked = selectAllElem.checked;
}

ArrearTransferWrit.prototype.tranfering = function()
{
	var mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال انجام عملیات ...'});
	mask.show();
	
	Ext.Ajax.request({
		
		url : this.address_prefix + "../data/writ.data.php",
		method : "POST",
		params : {
			task : "ArrearTransferAction",
			new_state : this.new_state,
			mode: '<?= $returnFlag ? "return" : ""?>'
		},
		form : this.get("formSelectedWrits"),
		
		success : function(response,op){
			if(response.responseText == "true")
			{
				alert("<?= $action_title?> با موفقیت انجام شد.");
				mask.hide();
				ArrearTransferWritObject.grid.getStore().load();
				ArrearTransferWritObject.advanceSearchPanel.collapse();
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