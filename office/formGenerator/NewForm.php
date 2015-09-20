<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	89.02
//---------------------------
require_once 'header.php';
include_once '../global/public.php';
include_once '../formGenerator/form.class.php';
include_once '../devotions/dvt.class.php';
include_once '../states/states.class.php';
include_once '../rents/rnt.class.php';

$FormID = $_GET["FormID"];
$referenceID = $_GET["referenceID"];
$LetterID = isset($_GET["LetterID"]) ? $_GET["LetterID"] : "";
$fromPage = isset($_GET["from"]) ? $_GET["from"] : ""; 
$RefID = isset($_GET["RefID"]) ? $_GET["RefID"] : "0";
//__________________________________________________________
$formRecord = FormGenerator::select("FormID=" . $FormID);
$formFile = $formRecord[0]["FileType"] == "" ? false : true; 
if($formFile)
	$fileContent = "<tr><td>" . file_get_contents("../../" . FormImagePath . "form" . $FormID . "." . 
		$formRecord[0]["FileType"]) . "</td></tr>";
$output = "";
//__________________________________________________________
switch ($formRecord[0]["reference"])
{
	case "devotions" : 
		$mainDateRecord = be_devotion::select("dvt01=" . $referenceID);
		$mainDateRecord = $mainDateRecord[0];
		break;
	case "states" : 
		$mainDateRecord = be_state::select("sta02=" . $referenceID);
		$mainDateRecord = $mainDateRecord[0];
		break;
	case "rents" : 
		$mainDateRecord = be_rent::select("rnt02=" . $referenceID);
		$mainDateRecord = $mainDateRecord[0];
		break;
}
//__________________________________________________________
$element_dt = FormElements::selectWithWfmValues($FormID, $LetterID, $_SESSION["PersonID"], $RefID);
//__________________________________________________________
for($i=0; $i < count($element_dt); $i++)
{
	$output .= "<tr><td>". $element_dt[$i]["ElementTitle"] . "</td><td class='infoText' height='21px'>";
	switch ($element_dt[$i]["ElementType"])
	{
		case "bind":
			$cur_output = $mainDateRecord[$element_dt[$i]["referenceField"]];
			break;
		//..............................................................................
		case "text":
			if($element_dt[$i]["access"] == "1" && $element_dt[$i]["active"] == "1")
				$cur_output = "<input type='text' class='x-form-text x-form-field' style='width:" . $element_dt[$i]["width"] . "'
				id='elem_" . $element_dt[$i]["ElementID"] . "' name='elem_" . $element_dt[$i]["ElementID"] . 
				"'value='" . $element_dt[$i]["wfmElementValue"] . "'>";
			else
				$cur_output = $element_dt[$i]["wfmElementValue"] . "&nbsp;";
				
			break;
		//..............................................................................	
		case "date":
			$value = $element_dt[$i]["wfmElementValue"] == "" ? "" : 
				CommenModules::Miladi_to_Shamsi($element_dt[$i]["wfmElementValue"]);
				
			if($element_dt[$i]["access"] == "1" && $element_dt[$i]["active"] == "1")
				$cur_output = "<input type='text' class='x-form-text x-form-field' 
				id='elem_" . $element_dt[$i]["ElementID"] . "' name='elem_" . $element_dt[$i]["ElementID"] . "'
				value='" . $value . "' style='width:" . $element_dt[$i]["width"] . "'>
				<script>new Ext.form.SHDateField({applyTo: 'elem_" . $element_dt[$i]["ElementID"] . "',
					format: 'Y/m/d',width: 120});</script>";
			else 
			{
				if($element_dt[$i]["wfmElementValue"] == "")
					$cur_output = "&nbsp;";
				else 
					$cur_output = $value . "&nbsp;";
			}
			break;
		//..............................................................................	
		case "comment":
			$cur_output = hebrevc($element_dt[$i]["ElementValue"]);
			break;
		//..............................................................................
		case "textarea":
			if($element_dt[$i]["access"] == "1" && $element_dt[$i]["active"] == "1")
				$cur_output = "<textarea class='x-form-field' style='width:" . $element_dt[$i]["width"] . "' id='elem_" . 
					$element_dt[$i]["ElementID"] . "' name='elem_" . $element_dt[$i]["ElementID"] . "' rows='5'>" . 
					$element_dt[$i]["wfmElementValue"] . "</textarea>";
			else 
				$cur_output = hebrevc($element_dt[$i]["wfmElementValue"]) . "&nbsp;";
			break;
		//..............................................................................	
		case "combo":
			if($element_dt[$i]["access"] == "1" && $element_dt[$i]["active"] == "1")
			{
				if($element_dt[$i]["referenceInfoID"] == 0)
				{
					$cur_output = "<select class='x-form-field' id='elem_" . $element_dt[$i]["ElementID"] . "' 
						name='elem_" . $element_dt[$i]["ElementID"] . "' style='width:" . $element_dt[$i]["width"] . "'>";
					
					$options = split(':', $element_dt[$i]["ElementValue"]);
					for($j=0; $j<count($options); $j++)
					{
						$cur_output .= "<option value='" . $options[$j] . "' ";
						$cur_output .= ($element_dt[$i]["wfmElementValue"] == $options[$j]) ? "selected" : "";
						$cur_output .= ">" . $options[$j] . "</option>";
					}
					
					$cur_output .= "</select>";
				}
				else 
				{
					$cur_output = be_public::BindDropDown("elem_" . $element_dt[$i]["ElementID"], 
						$element_dt[$i]["wfmElementValue"],"--", $element_dt[$i]["referenceInfoID"], "width:" . $element_dt[$i]["width"]);
				}
			}
			else
			{
				if($element_dt[$i]["referenceInfoID"] == 0)
					$cur_output = $element_dt[$i]["wfmElementValue"] . "&nbsp;";
				else if($element_dt[$i]["wfmElementValue"] != "")
					$cur_output = be_public::GetInfoName($element_dt[$i]["wfmElementValue"]);
				else 
					$cur_output = "&nbsp;";
			}
			
			 
			break;
		//..............................................................................
		case "radio":
			$options = split(':', $element_dt[$i]["ElementValue"]);
			$cur_output = "";
			for($j=0; $j<count($options); $j++)
			{
				$cur_output .= "<input type='radio' name='elem_" . $element_dt[$i]["ElementID"] . "' 
					id='elem_" . $element_dt[$i]["ElementID"] . "' 
					value='" . $options[$j] . "' ";
				$cur_output .= ($element_dt[$i]["wfmElementValue"] == $options[$j]) ? "checked" : ""; 
				$cur_output .= ($element_dt[$i]["access"] == "1" && $element_dt[$i]["active"] == "1") ? "" : " disabled ";
				$cur_output .= "> " . $options[$j] . "<br>";
			}
			break;
		//..............................................................................
		case "check":
			$options = split(':', $element_dt[$i]["ElementValue"]);
			$selected = split(":", $element_dt[$i]["wfmElementValue"]);
			$cur_output = "";
			for($j=0; $j<count($options); $j++)
			{
				$cur_output .= "<input type='checkbox' name='elem_" . $element_dt[$i]["ElementID"] . "_" . $j . "' 
					id='elem_" . $element_dt[$i]["ElementID"] . "_" . $j . "' 
					value='" . $options[$j] . "' ";
				
				for($k=0; $k < count($selected); $k++)
					if($options[$j] == $selected[$k])
						$cur_output .= "checked";
						
				$cur_output .= ($element_dt[$i]["access"] == "1" && $element_dt[$i]["active"] == "1") ? "" : " disabled ";
				$cur_output .= " > " . $options[$j] . "<br>";
			}
			break;
		//..............................................................................
	}
	if($formFile)
		$fileContent = str_replace("#" . $element_dt[$i]["ElementID"] . "#", 
			$cur_output, $fileContent);
	else
		$output .= $cur_output . "</td></tr>"; 

}
?>
<script type="text/javascript">
new Ext.Panel({
	applyTo: "DIV_form",
	contentEl : "Tbl_form",
	width: "100%",
	autoHeight: true,
	autoScroll : true,
	
	tbar: [{
        id: 'return',
		text: 'بازگشت',
		iconCls: 'undo',
		handler: function(){
			var page = ('<?= $_REQUEST["returnTo"] ?>' != "archive") ? "<?= $_REQUEST["returnTo"] ?>.php" :
				 "../formGenerator/ReceiveForms.php?archive=true"
				
			OpenPage(page);
		}
	},'-',{
        id: 'print',
		text: 'چاپ فرم',
		iconCls: 'print',
		handler: function(){
			document.getElementById("printable").innerHTML = document.getElementById("Tbl_form").innerHTML;
			window.print();
			setTimeout(function(){document.getElementById("printable").innerHTML = "";},1000);
		}
	},'-'
	<? if($element_dt[0]["active"] == "1"){ ?>
	,{
		id: 'save',
		text: 'ذخیره',
		iconCls: 'save',
		handler: saveForm
	}
	<?} ?>
	]
});

function cancel()
{
	OpenPage("../formGenerator/CreatedForms.php");
}

function saveForm()
{
	var mask = new Ext.LoadMask(document.body,{msg: 'در حال ذخیره ...'});
	mask.show();
	
	Ext.Ajax.request({
		url: '../formGenerator/wfm.data.php',
		params: {
			task: "LetterSave"
		},
		form: document.getElementById("MainForm"),
		method: "POST",

		success:function(response,options)
		{
			mask.hide();
			if(response.responseText == "true")
				alert("ذخیره فرم با موفقیت انجام شد");
			dg_store.reload();
		}
	});
}
</script>
<style>
.infotd td{border-bottom: solid 1px #E0E0E0;padding-right:4px}
.infoText{font-weight: bold; color: #0D6EB2;}

@media print
{
	#non-printable { display: none; }
	#printable { display: block; }
}

</style>

	<input type="hidden" name="FormID" value="<?= $FormID ?>">
	<input type="hidden" name="referenceID" value="<?= $referenceID ?>">
	<input type="hidden" name="LetterID" value="<?= $LetterID ?>">
	<div id="DIV_form">
		<div id="Tbl_form">
			<table width="100%" class="x-form-text infotd" >
			<?= $formFile ? $fileContent : $output ?>
			<tr>
				<td></td>
				<td></td>
			</tr>
			</table>
		</div>
	</div>
















