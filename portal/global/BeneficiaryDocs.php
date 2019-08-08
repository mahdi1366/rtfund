<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 94.12
//-----------------------------
require_once '../header.inc.php';
require_once '../../framework/person/persons.class.php';
require_once inc_dataReader;

if(isset($_REQUEST["task"]))
{
	if($_REQUEST["task"] == "GetFiles")
		GetInfo();
}

function GetInfo(){
	$obj = new BSC_persons($_SESSION["USER"]["PersonID"]);

	$PT_Array = array();
	if($obj->IsStaff == "YES")
		$PT_Array[] = "'Staff'";
	if($obj->IsCustomer == "YES")
		$PT_Array[] = "'Customer'";
	if($obj->IsShareholder == "YES")
		$PT_Array[] = "'Shareholder'";
	if($obj->IsAgent == "YES")
		$PT_Array[] = "'Agent'";
	if($obj->IsSupporter == "YES")
		$PT_Array[] = "'Supporter'";
	if($obj->IsExpert == "YES")
		$PT_Array[] = "'Expert'";

	$files = PdoDataAccess::runquery("select DocumentID,ObjectID,DocDesc,b1.infoDesc DocTypeDesc,b2.infoDesc param1Title
				from DMS_documents d	
				join DMS_DocFiles df using(DocumentID)
				join BaseInfo b1 on(InfoID=d.DocType AND TypeID=8)
				left join BaseInfo b2 on(b1.param1=b2.InfoID AND b2.TypeID=7)
				where ObjectType='BeneficiaryDocs' AND ObjectID in(" . implode(",", $PT_Array) . ") 
					AND IsConfirm='YES'
				group by DocumentID
				order by ObjectID");
	
	echo dataReader::getJsonData($files, count($files), $_GET["callback"]);
	die();
}

?>
<script>

BeneficiaryDocs.prototype = {
	TabID : '<?= $_REQUEST["ExtTabID"]?>',
	address_prefix : "<?= $js_prefix_address?>",

	get : function(elementID){
		return findChild(this.TabID, elementID);
	}
};

function BeneficiaryDocs()
{
	this.mainPanel = new Ext.panel.Panel({
		frame : false,
		renderTo : this.get("AttachPanel"),
		border : false,
		autoHeight : true
	});
	
	this.docStore = new Ext.data.SimpleStore({
		proxy: {
			type: 'jsonp',
			url: this.address_prefix + 'BeneficiaryDocs.php?task=GetFiles',
			reader: {root: 'rows',totalProperty: 'totalCount'}
		},
		fields : ['ObjectID','RowID',"param1Title","DocTypeDesc","DocDesc", "DocumentID"],
		autoLoad : true,
		listeners :{
			load : function(){
				me = BeneficiaryDocsObject;
				currentGroup = "";
				
				if(this.totalCount == 0)
				{
					me.mainPanel.add({
						xtype : "fieldset",
						itemId : "fs_" + record.data.ObjectID,
						style : "text-align:right",
						html : "هیچ فایلی متناسب با نوع ارتباط شما با صندوق تعریف نشده است",
						width : 600
					});
					return;
				}
				
				for(i=0; i<this.totalCount; i++)
				{
					record = this.getAt(i);
					if(currentGroup != record.data.ObjectID)
					{
						title = "";
						switch(record.data.ObjectID){
							case "Staff" :			title = "همکاران صندوق";	break;
							case "Customer" :		title = "مشتری";			break;
							case "Shareholder" :	title = "سهامدار";			break;
							case "Agent" :			title = 'سرمایه گذار' ;		break;
							case "Supporter" :		title = "حامی";				break;
							case "Expert" :			title = 'کارشناس خارج از صندوق';break;
						}
						me.mainPanel.add({
							xtype : "fieldset",
							itemId : "fs_" + record.data.ObjectID,
							title : "فایل های مرتبط با " + title,
							style : "text-align:right",
							minHeight: 200,
							autoHeight : true,
							width : 600
						});
						parent = me.mainPanel.down("[itemId=fs_" + record.data.ObjectID + "]");
						currentGroup = record.data.ObjectID;
					}
					parent.add({
						xtype : "container",
						style : "margin-top : 5px",
						html : "<a target=blank href=/office/dms/ShowFile.php?ObjectID=" + record.data.ObjectID + "&DocumentID=" + record.data.DocumentID + ">" +
								" <img src=../../framework/icons/document.png style=vertical-align:middle />&nbsp;&nbsp;" + 
								/*record.data.param1Title + " - " + record.data.DocTypeDesc + */
								(record.data.DocDesc != "" ? record.data.DocDesc : "") +
								"</a>"
					});
					
					parent.doLayout();
				}
			}
		}
	});
	
	
	
}

var BeneficiaryDocsObject = new BeneficiaryDocs();

</script>
<center>
	<br>
	<div id="AttachPanel"></div>
</center>