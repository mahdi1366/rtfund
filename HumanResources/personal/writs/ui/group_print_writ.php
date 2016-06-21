<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.08
//---------------------------
require_once '../../../header.inc.php';
require_once '../js/advance_search_writ.js.php';

?>
<script>
GroupPrintWrit.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	mainPanel : "",
	advanceSearchObject : "",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function GroupPrintWrit()
{
	this.form = this.get("form_search");
	
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
			url : this.address_prefix + "advance_search_writ.php?print_writs=true",
			scripts: true
		},

		buttons : [{
			text:'جستجو',
			iconCls: 'search',
			handler: function(){GroupPrintWritObject.advance_searching();}
		},{
			text : "پاک کردن فرم گزارش",
			iconCls : "clear",
			handler : function(){Ext.get(GroupPrintWritObject.form).clear();}
		}]
	});
	this.advanceSearchPanel.loader.load({
		callback: function(){
			advanceSearchObject = new advanceSearch(GroupPrintWritObject.form);
			GroupPrintWritObject.advanceSearchPanel.doLayout();
		}
	});

}

GroupPrintWritObject = new GroupPrintWrit();
var advanceSearchObject;

GroupPrintWrit.prototype.advance_searching = function()
{
	this.form.action = this.address_prefix + "print_writ.php"  ;
	this.form.target = "_blank";
	this.form.method = "POST";
	this.form.submit();
}
</script>
<center>
	<form id="form_search">
		<div id="AdvanceSearchDIV">
		<div id="AdvanceSearchPNL"></div>
	</div>
	</form>

</center>
