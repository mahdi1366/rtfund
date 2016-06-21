<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 91.05
//-----------------------------
require_once '../../../header.inc.php';

?>
<script>
importSt.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
}

function importSt()
{
	this.formPanel = new Ext.form.Panel({
		renderTo : this.get("main"),
		frame : true,
		bodyStyle : "text-align:right;padding:5px",
		title : "ایجاد فرد متفرقه برای یک دانشجو در سیستم کارگزینی",
		defaults : {
			labelWidth :150
		},
		width : 500,
		items :[{
			xtype : "combo",
			store : new Ext.data.Store({
				fields:["id","title"],
				data : [{"id" : 300, "title" : 'متفرقه'},{"id" : 200, "title" : "حق التدریس"}]
			}),
			displayField : "title",
			fieldLabel : "نوع فرد",
			queryMode: 'local',
			valueField : "id",
			hiddenName : "person_type",
			value : 300
		},{
			xtype : "combo",
			anchor : "100%",
			fieldLabel : "انتخاب دانشجو",
			store: new Ext.data.Store({
				fields:["StNo", "fullname"],
				proxy: {
					type: 'jsonp',
					url: this.address_prefix + '../data/person.data.php?task=selectStudents',
					reader: {root: 'rows',totalProperty: 'totalCount'}
				}
			}),
			emptyText:'انتخاب دانشجو ...',
			typeAhead: false,
			pageSize : 10,
			valueField : "StNo",
			hiddenName : "StNo",
			listConfig: {
				loadingText: 'در حال جستجو...',
				emptyText: 'فاقد اطلاعات'
			},
			tpl: new Ext.XTemplate(
				'<table cellspacing="0" width="100%"><tr class="x-grid3-header">'
			    	,'<td height="23px">کد دانشجویی</td>'
			    	,'<td>نام و نام خانوادگی</td></tr>',
			    '<tpl for=".">',
			    '<tr class="x-boundlist-item" style="border-left:0;border-right:0">'
			    	,'<td style="border-left:0;border-right:0" class="search-item">{StNo}</td>'
					,'<td style="border-left:0;border-right:0" class="search-item">{fullname}</td></tr>',
			    '</tpl>'
			    ,'</table>'),
				listeners :{
					select : function(combo, records){
						var record = records[0];
						this.setValue(record.data.fullname);
						importStObj.get("mainForm").StNo.value = record.data.StNo;
						this.collapse();
					}
				}
		}],
		buttons : [{
			text : "ایجاد فرد متفرقه",
			iconCls : "add",
			handler : function()
			{
				Ext.Ajax.request({
					url: importStObj.address_prefix + '../data/person.data.php?task=importStudent',
					method : "POST",
					form : importStObj.get("mainForm"),
					success : function(res){
						var sd = Ext.decode(res.responseText);
						
						if(sd.success)
							alert('دانشجو با موفقیت انتقال یافت');
						else if(sd.data == "Duplicate")
							alert('این دانشجو قبلا انتقال یافته است.');
						else
							alert("عملیات مورد نظر با شکست مواجه شد");
					}
				});
			}
		}]
	});
}

importStObj = new importSt();

</script>
<form id="mainForm">
	<center><br>
		<div id="main" ></div>
	</center>
</form>