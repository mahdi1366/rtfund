<script type="text/javascript">
//---------------------------
// programmer:	Jafarkhani
// create Date:	93.11
//---------------------------

WritList.prototype = {
	parent : PersonObject,
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.form, elementID);
	}
};

function WritList()
{
	this.form = this.parent.get("form_WritList");
	
	this.grid = <?= $grid?>;
	this.grid.render(this.get("WrtGRID"));
}

WritListObject = new WritList();

WritList.opRender = function(value, p, record)
{
	var st = "";
	
	st += "<div  title='ویرایش اطلاعات' class='edit' onclick='WritListObject.editInfo();' " +
			"style='float:right;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
	st +=	"<div  title='حذف اطلاعات' class='remove' onclick='WritListObject.deletePersonWrt();' " +
			"style='float:left;background-repeat:no-repeat;background-position:center;" +
			"cursor:pointer;width:50%;height:16'></div>";
	
	return st;
}

WritList.prototype.editInfo = function(record)
{
	var record = this.grid.getSelectionModel().getLastSelected();

	framework.OpenPage(this.address_prefix + "../ui/view_writ.php", "مشاهده حکم",
		{   WID : record.data.writ_id,
            STID : record.data.staff_id,
            WVER : record.data.writ_ver,
            PID : record.data.PersonID,
            ExeDate : record.data.execute_date});
}

WritList.prototype.deletePersonWrt = function()
{
	var record = this.grid.getSelectionModel().getLastSelected();

	if(!confirm("آيا از حذف اطمينان داريد؟"))
		return;

	mask = new Ext.LoadMask(Ext.getCmp(this.parent.TabID), {msg:'در حال ذخيره سازي...'});
	mask.show();

	Ext.Ajax.request({
		url : this.address_prefix + "../data/writ.data.php",
		method : "POST",
		params : {
			task : "DeleteWrit",
			writ_id : record.data.writ_id,
			writ_ver: record.data.writ_ver,
			staff_id : record.data.staff_id
		},
		success : function(response)
		{
			var st = Ext.decode(response.responseText);
			if(st.success)
			{
				alert("حکم مورد نظر با موفقیت حذف شد");
					WritListObject.grid.getStore().load();
			}
			else
			{
				alert(st.data);
			}
			mask.hide();
		}
	});
}


</script>