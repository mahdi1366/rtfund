<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	90.04
//---------------------------
require_once 'header.php';
require_once 'rpt.data.php';


$obj = new ReportGenerator($_GET["Q0"]);

$query = stripcslashes($obj->TotalQuery);
$query .= " limit 1";
$temp = dataAccess::RUNQUERY($query);
if(count($query) == 0)
{
	echo "گزارش مورد نظر فاقد اطلاعات می باشد. برای ایجاد فایل باید گزارش شامل حداقل یک رکورد باشد.";
	die();
}

$dataKeys = array_keys($temp[0]);

$output = "<tr>";
for($k=1,$i=1,$j=0; $i<count($dataKeys); $i=$i+2,$j++,$k++)
{
	$output .= "<td class='code' align='center' height='21px'>#" . $k . "#</td>";
	$output .= "<td style='padding: 4px'>" . $dataKeys[$i] . "</td>";
	if($j == 2)
	{
		$output .= "</tr><tr>";
		$j = -1;
	}
}
if($obj->FileType != "")
{
	$file = file_get_contents("../../" . ReportImagePath . "Report" . $obj->ReportID . "." . $obj->FileType);
}
?>
<script type="text/javascript">
function saveFile()
{
	if(document.getElementById("attach") == null)
	{
		alert("ابتدا فایل فرم گزارش خود را انتخاب کنید");
		return;
	}
	
	var mask = new Ext.LoadMask(document.body,{msg: 'در حال ذخیره اطلاعات ...'});
	mask.show();
	Ext.Ajax.request({
		url: '../ReportGenerator/rpt.data.php',
		params: {
			task: "formSave",
			Q0: '<?= $_GET["Q0"] ?>'
		},
		method: "POST",
		form: document.getElementById("MainForm"),
		isUpload: true,

		success:function(response,options)
		{
			mask.hide();		
			OpenPage("../ReportGenerator/formReport.php?Q0=<?= $_GET["Q0"]?>");	
		}
	});
}
</script>
<style>
.code {background-color: #b3e0d8;padding: 4px}
</style>
<table width='100%' style="border: 1px solid #b5b8c8;" cellspacing="0" cellpadding="4">
	<tr class='x-grid3-header' height='23px'>
		<td style="padding: 4px">کد آیتم</td>
		<td style="padding: 4px">عنوان آیتم</td>
		<td style="padding: 4px">کد آیتم</td>
		<td style="padding: 4px">عنوان آیتم</td>
		<td style="padding: 4px">کد آیتم</td>
		<td style="padding: 4px">عنوان آیتم</td>
	</tr>
	<?= $output ?>
</table>
<br>
<input type="file" id="attach" name="attach"/> 
<input class="button" type="button" onclick="saveFile();" value="ذخیره">
<br>
<?=$file ?>


