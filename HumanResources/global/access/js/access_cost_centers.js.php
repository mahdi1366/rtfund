<script>
//-------------------------
// programmer:	Jafarkhani
// Date:		90.02
//-------------------------

CostCenterAccess.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	form : "",
	UsersGrid : "",
	CostCentersGrid : "",
	CostCentersGridRender : false,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function CostCenterAccess()
{
	this.form = this.get("form_costCenterAccess");
	this.afterLoad();
}

CostCenterAccess.accessRender = function(v,p,r)
{
	var st = "<input type='checkbox' id='chk_" + r.data.cost_center_id + "' name='chk_" + r.data.cost_center_id + "'";
	if(v == 1)
		st += " checked ";
	st += ">";
	return st;
}

CostCenterAccess.LoadCostCentersRender = function(v,p,record)
{
	return "<div  title='بارگذاری' align=center class='refresh' onclick='CostCenterAccessObject.LoadCostCenters(\"" + record.data.UserID + "\");' " +
				"style='background-repeat:no-repeat;background-position:center;" +
				"cursor:pointer;width:100%;height:16'></div>";
	
}

CostCenterAccess.prototype.LoadCostCenters = function(UserID)
{
	this.form.UserID.value = UserID;
	if(!this.CostCentersGridRender)
	{
		this.CostCentersGridRender = true;
		this.CostCentersGrid.render(this.get("div_grid2"));
		
	}
	else
		this.CostCentersGrid.getStore().load();
}

CostCenterAccess.prototype.SaveAccess = function()
{
	var mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال انجام عملیات...'});
	mask.show();
	
	Ext.Ajax.request({
		method: "POST",
		url: this.address_prefix + "../data/access.data.php",
		params: {
			task: "saveCostAccess",
			UserID: this.form.UserID.value
		},
		form: this.form,

		success: function()
		{
			CostCenterAccessObject.CostCentersGrid.getStore().load();
			mask.hide();
		}
	});
}

CostCenterAccess.prototype.checkAll = function(checkElem)
{
	var elems = this.form.getElementsByTagName("input");
	for(i=0; i<elems.length; i++)
		if(elems[i].id.indexOf("chk_") != -1)
			elems[i].checked = checkElem.checked;
}

</script>











