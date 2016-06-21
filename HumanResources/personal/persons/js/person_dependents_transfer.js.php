<script type="text/javascript">
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.07
//---------------------------

DepTransfer.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	personCombo : "",
	advanceSearchPanel : "",
	
	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function DepTransfer()
{
	this.form = this.get("form_SearchPersonDep");
	
	this.advanceSearchPanel = new Ext.Panel({
		applyTo: this.get("advanceSearchDIV"),
		contentEl: this.get("advanceSearchPNL"),
		title: "جستجو",
        frame:true,
		width: 600,
		autoHeight: true,
		collapsible : true,
		collapsed : false,
        buttons :[{
                text: "جستجو",
				iconCls: 'search',
				handler: function(){SearchDepPersonObject.searching()}
        }]
	});
	Ext.get(this.get("advanceSearchPNL")).addKeyListener(13, function(){SearchDepPersonObject.searching();});
    this.form.from_StaffID.focus();
	
	this.afterLoad();
	
}

DepTransfer.prototype.searching = function()
{
	this.get("possibleDeps").style.display = "block";
	this.grid.render(this.get("personResultDIV"));
	this.grid.getStore().load();
}


DepTransfer.prototype.selectAll = function(selectAllElem)
{
	var elems = this.form.getElementsByTagName("input");
	for(i=0; i < elems.length; i++)
		if(elems[i].id.indexOf("chk_") != -1)
			elems[i].checked = selectAllElem.checked;
}

DepTransfer.CheckRender = function(v,p,r)
{
	str = '<input type="checkbox" id="chk_' + r.data.PersonID + "_" + r.data.staff_id + '" ' +
		  'name="chk_' + r.data.PersonID + "_" + r.data.staff_id + '">';
	return str;
}

DepTransfer.prototype.tranfering = function()
{
	var mask = new Ext.LoadMask(Ext.getCmp(this.TabID), {msg:'در حال انجام عملیات ...'});
	mask.show();

	Ext.Ajax.request({

		url : this.address_prefix + "../data/dependent.data.php",
		method : "POST",
		params : {
			task : "transferAction"
		},
		form : this.get("form_SearchPersonDep"),

		success : function(response,op){
			if(response.responseText == "true")
			{
				alert("عملیات با موفقیت انجام گردید.");
				mask.hide();
				SearchDepPersonObject.grid.getStore().load();				
			}
			else
			{
				alert("عملیات مورد نظر با شکست مواجه شد.");
				mask.hide();
			}
		}
	});
}
//------------------------------------------------------
</script>