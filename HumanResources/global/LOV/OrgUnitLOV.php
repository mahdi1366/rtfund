<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.03
//---------------------------
require_once "../../header.inc.php";
require_once inc_dataGrid;
?>
<html>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
	<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-all.css" />
    <link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/ext-rtl.css" />
	<link rel="stylesheet" type="text/css" href="/generalUI/ext4/resources/css/icons.css" />
	<script type="text/javascript" src="/generalUI/ext4/resources/ext-all.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/resources/ext-extend.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/component.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/message.js"></script>
	<script type="text/javascript" src="/generalUI/ext4/ux/TreeSearch.js"></script>
	<script type="text/javascript">
	Ext.onReady(function(){
		new Ext.tree.Panel({
			renderTo: 'tree-div',
			frame: true,
			width: 480,
			height: 560,
			style : "margin-top:10px",
			title: "واحد های سازمانی",
			store : new Ext.data.TreeStore({
				root : {
					id : "source",
					text: 'واحد های اصلی',
					expanded: true
				},
				proxy: {
					type: 'ajax',
					url: "../../organization/org_units/unit.data.php?task=GetTreeNodes"
				}
			}),
			plugins: [new Ext.tree.Search()],
			listeners : {
				itemdblclick : function(view,record)
				{
					window.returnValue = record.data.id;
					window.close();
				}
			}
		});
	});

	</script>
<body dir="rtl">
<center>
<div id="tree-div"></div>
</center>
</body>
</html>