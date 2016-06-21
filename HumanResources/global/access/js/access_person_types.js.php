<script>
//-------------------------
// programmer:	Jafarkhani
// Date:		90.02
//-------------------------
PersonTypeAccess.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	UsersGrid : "",
	PersonTypesGrid : "",
	PersonTypesGridRender : false,

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function PersonTypeAccess()
{
	this.form = this.get("form_personTypeAccess");
	this.afterLoad();
}

PersonTypeAccess.accessRender = function(v,p,r)
{
	var st = "<input type='checkbox' id='chk_" + r.data.InfoID + "' name='chk_" + r.data.InfoID + "'";
	if(v == 1)
		st += " checked ";
	st += ">";
	return st;
}

PersonTypeAccess.PersonTypeRender = function(v,p,record)
{
	return "<div  title='بارگذاری' align=center class='refresh' onclick='PersonTypeAccessObject.LoadPersonTypes(\"" + record.data.UserID + "\");' " +
				"style='background-repeat:no-repeat;background-position:center;" +
				"cursor:pointer;width:100%;height:16'></div>";
	
}

PersonTypeAccess.prototype.LoadPersonTypes = function(UserID)
{
	this.form.UserID.value = UserID;
	if(!this.PersonTypesGridRender)
	{
		this.PersonTypesGridRender = true;
		this.PersonTypesGrid.render(this.get("div_grid2"));

	}
	else
		this.PersonTypesGrid.getStore().load();
}

PersonTypeAccess.prototype.SaveAccess = function()
{
	var mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال انجام عملیات...'});
	mask.show();
	
	Ext.Ajax.request({
		method: "POST",
		url: this.address_prefix + "../data/access.data.php",
		params: {
			task: "savePersonTypeAccess",
			UserID: this.form.UserID.value
		},
		form: this.form,

		success: function()
		{
			PersonTypeAccessObject.PersonTypesGrid.getStore().load();
			mask.hide();
		}
	});
}

PersonTypeAccess.prototype.checkAll = function(checkElem)
{
	var elems = this.form.getElementsByTagName("input");
	for(i=0; i<elems.length; i++)
		if(elems[i].id.indexOf("chk_") != -1)
			elems[i].checked = checkElem.checked;
}


</script>















