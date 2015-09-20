<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.02
//---------------------------
require_once 'header.php';
require_once '../formGenerator/wfm.data.php';
?>
<script type="text/javascript" src="../organization/units.js?v1"></script>
<script type="text/javascript">
var TreeData = <?php echo GetHistoryTreeNodes(); ?>;

var tree = new Ext.tree.ColumnTree({
	id: 'myTree',
	el:'tree-div',
	width: 600,
	height: 400,
	autoScroll:true,
	rootVisible:false,
	tbar : [{
		id: 'return',
		text: 'بازگشت',
		iconCls: 'undo',
		handler: function(){HistoryWin.hide();}
	}],
	
	loader: new Ext.tree.TreeLoader({
		preloadChildren: true,
		clearOnLoad: false,
		uiProviders:{'col': Ext.tree.ColumnNodeUI}				
	}),
	
	columns:[{
		header:'فرد در سازمان',
		width:360,
		dataIndex:'user',
		renderer : function(v,n,a){
			
			var st = "<span";
			st += (a["ViewFlag"] == "0") ? " style='font-weight:bold' " : ""; 
			st += " >" + v + "</span>";
			return st;
		}
	},{
		header:'تاریخ دریافت',
		width:110,
		dataIndex:'SendDate',
		renderer : function(v,n,a){
			
			var st = "<span";
			st += (a["ViewFlag"] == "0") ? " style='font-weight:bold' " : ""; 
			st += " >" + v.substring(10) + ' ' + MiladiToShamsi(v.substring(0,10)) + "</span>";
			return st;
		}
	},{
		header : 'اقدامات',
		width: 40,
		dataIndex : 'SendComment',
		renderer : function(v){
			if(v == "") return "";
			var title = v.replace(/(\r\n|\n)/g, "");
			
			return "<div align='center' ext:qtip='" + title + "' class='comment'" +
			"style='background-repeat:no-repeat;background-position:center;" +
			"width:100%;height:16'></div>";
		}
	},{
		header : 'پاسخ',
		width: 35,
		dataIndex : 'SendComment',
		renderer : function(v,n,a){
			if(v == "") return "";
			var response = (a["Response"] == null) ? "---" : a["Response"];
			var ResponseDate = (a["ResponseDate"] == null) ? "---" : 
				a["ResponseDate"].substring(10) + '&nbsp;&nbsp;' + MiladiToShamsi(a["ResponseDate"].substring(0,10));
			
			return "<div align='center' class='comment2'" + 
				"ext:qtitle='پاسخ به نامه<br>زمان پاسخ : " + ResponseDate + "<hr>' ext:qtip='" + response + "'" +
				"style='background-repeat:no-repeat;background-position:center;" +
				"width:100%;height:16'></div>";
		}
	},{
		header : "وضعیت",
		width : 50,
		dataIndex : "ArchiveFlag",
		renderer : function(v,n,a){
			if(a["DeleteFlag"] == "1")
				return "<div align='center' ext:qtip='حذف شده است' class='remove' " +
				"style='background-repeat:no-repeat;background-position:center;width:100%;height:16'></div>";
				
			else if(a["ArchiveFlag"] == "1")
				return "<div align='center' ext:qtip='بایگانی شده است' class='archive' " +
				"style='background-repeat:no-repeat;background-position:center;width:100%;height:16'></div>";
			else
				return "";
				
		}
	}],
	
	root: new Ext.tree.AsyncTreeNode({
		text: 'سابقه گردش',
		id: 'source',
		children: [TreeData]
	})
});	
tree.render();
tree.expandAll();

function ShowResponse(v,a)
{
	var response = (a["Response"] == null) ? "---" : a["Response"];
	var ResponseDate = (a["ResponseDate"] == null) ? "---" : 
		a["ResponseDate"].substring(10) + '&nbsp;&nbsp;' + MiladiToShamsi(a["ResponseDate"].substring(0,10));
	
	var st = "<span";
	st += (a["ViewFlag"] == "0") ? " style='font-weight:bold' " : ""; 
	st += " ext:qtitle='پاسخ به نامه<br>زمان پاسخ : " + ResponseDate + 
		"<hr>' ext:qtip='" + response + "'>" + v + "</span>";
	return st;
}
</script>
<div id="tree-div" onmouseover="" style=";overflow:auto; width:250px;border:1px solid #c3daf9;"></div>
