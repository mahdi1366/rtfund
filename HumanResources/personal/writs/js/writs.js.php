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

    WritList.attachRender = function(v,p,r){

    return "<div align='center' title='پیوست' class='attach' "+
    "onclick='WritListObject.RecordDocuments();' " +
    "style='background-repeat:no-repeat;background-position:center;" +
    "cursor:pointer;width:100%;height:16'></div>";
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

    WritList.prototype.RecordDocuments = function(){

    if(!this.documentWin)
{
    this.documentWin = new Ext.window.Window({
    width : 720,
    height : 440,
    modal : true,
    bodyStyle : "background-color:white;padding: 0 10px 0 10px",
    closeAction : "hide",
    loader : {
    url : "../../office/dms/documents.php",
    scripts : true
},
    buttons :[{
    text : "بازگشت",
    iconCls : "undo",
    handler : function(){this.up('window').hide();}
}]
});
    Ext.getCmp(this.TabID).add(this.documentWin);
}

    this.documentWin.show();
    this.documentWin.center();

    var record = this.grid.getSelectionModel().getLastSelected();
    this.documentWin.loader.load({
    scripts : true,
    params : {
    ExtTabID : this.documentWin.getEl().id,
    ObjectType : 'writs',
    ObjectID : record.data.writ_id
}
});
}

</script>