<script type="text/javascript">
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.06
//-----------------------------

Access.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function Access()
{
	this.filterPanel = new Ext.form.Panel({
		title: "انتخاب سیستم",
		width: 500,
		applyTo : this.get("div_form"),
		collapsible : true,
		collapsed : false,
		frame: true,
		items : [{
			xtype : "combo",
			store: new Ext.data.Store({
				autoLoad : true,
				proxy:{
					type: 'jsonp',
					url: this.address_prefix + 'framework.data.php?task=selectSystems',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ['SystemID','SysName']
			}),
			fieldLabel : "سیستم",
			queryMode : 'local',
			hiddenName : "SystemID",
			displayField: 'SysName',
			valueField : "SystemID",
			width : 400,
			itemId : "SystemID"
		},{
			xtype : "combo",
			store: new Ext.data.Store({
				proxy:{
					type: 'jsonp',
					url: this.address_prefix + 'framework.data.php?task=selectPersons',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				},
				fields :  ['PersonID','fullname']
			}),
			fieldLabel : "کاربر",
			displayField: 'fullname',
			valueField : "PersonID",
			hiddenName : "PersonID",
			width : 400,
			itemId : "PersonID"
		}], 
		buttons : [{
			text : "دسترسی های کاربر",
			iconCls : "refresh",
			handler : function(){
				
				AccessObject.grid.getStore().proxy.extraParams = {
					SystemID : this.up('form').getComponent("SystemID").getValue(),
					PersonID : this.up('form').getComponent("PersonID").getValue()
				};
				if(!AccessObject.grid.rendered)
					AccessObject.grid.render(AccessObject.get("div_dg"));
				else
					AccessObject.grid.getStore().load();
			}
		}]
	});
	
	//this.afterLoad();
}

var AccessObject = new Access();

Access.fullRender = function(v,p,r)
{
	str = '<input type="checkbox" id="fullChk_' + r.data.MenuID +
		'" onclick="AccessObject.fullClick(this,' + r.data.MenuID + ');" ';
	str += (r.data.ViewFlag == "1" && r.data.AddFlag == "1" &&
		r.data.EditFlag == "1" && r.data.RemoveFlag == "1") ? " checked " : "";
	str += ">";
	return str;
}

Access.viewRender = function(v,p,r)
{
	str = '<input type="checkbox" id="viewChk_' + r.data.MenuID +
		'" onclick="AccessObject.ViewFlagClick(this,' + r.data.MenuID +
		');" name="viewChk_' + r.data.MenuID + '"';
	str += (v == "YES") ? " checked " : "";
	str += ">";
	return str;
}

Access.addRender = function(v,p,r)
{
	str = '<input type="checkbox" id="addChk_' + r.data.MenuID +
		'" onclick="AccessObject.checkViewFlag(this,' + r.data.MenuID +
		');" name="addChk_' + r.data.MenuID + '"';
	str += (v == "YES") ? " checked " : "";
	str += ">";
	return str;
}

Access.editRender = function(v,p,r)
{
	str = '<input type="checkbox" id="editChk_' + r.data.MenuID +
		'" onclick="AccessObject.checkViewFlag(this,' + r.data.MenuID +
		');" name="editChk_' + r.data.MenuID + '"';
	str += (v == "YES") ? " checked " : "";
	str += ">";
	return str;
}

Access.removeRender = function(v,p,r)
{
	str = '<input type="checkbox" id="removeChk_' + r.data.MenuID +
		'" onclick="AccessObject.checkViewFlag(this,' + r.data.MenuID +
		');" name="removeChk_' + r.data.MenuID + '"';
	str += (v == "YES") ? " checked " : "";
	str += ">";
	return str;
}

Access.prototype.checkViewFlag = function(elem,MenuID)
{
	if(elem.checked)
		this.get("viewChk_" + MenuID).checked = true;
	else
		this.get("fullChk_" + MenuID).checked = false;
}

Access.prototype.fullClick = function(elem,MenuID)
{
	if(elem.checked)
	{
		this.get("viewChk_" + MenuID).checked = true;
		this.get("addChk_" + MenuID).checked = true;
		this.get("editChk_" + MenuID).checked = true;
		this.get("removeChk_" + MenuID).checked = true;
	}
	else
	{
		this.get("viewChk_" + MenuID).checked = false;
		this.get("addChk_" + MenuID).checked = false;
		this.get("editChk_" + MenuID).checked = false;
		this.get("removeChk_" + MenuID).checked = false;
	}
}

Access.prototype.ViewFlagClick = function(elem,MenuID)
{
	if(!elem.checked)
	{
		this.get("fullChk_" + MenuID).checked = false;
		this.get("addChk_" + MenuID).checked = false;
		this.get("editChk_" + MenuID).checked = false;
		this.get("removeChk_" + MenuID).checked = false;
	}
}

Access.prototype.saveAction = function()
{
	var mask = new Ext.LoadMask(Ext.getCmp(this.TabID),{msg: 'در حال ذخيره اطلاعات ...'});
	mask.show();
	Ext.Ajax.request({
		url: this.address_prefix + 'framework.data.php?task=SaveUserAccess',
		form: this.get("MainForm"),
		method: "POST",

		success: function(response)
		{
			AccessObject.grid.getStore().load();
			mask.hide();
		}
	});
}

</script>