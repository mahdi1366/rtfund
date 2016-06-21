<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------

require_once '../../../header.inc.php';
require_once inc_dataReader;
require_once inc_dataGrid;
require_once inc_PDODataAccess;
//require_once '../../../organization/org_units/unit.class.php';
//____________  GET ACCESS  _____________
$accessObj = FRW_access::GetAccess($_POST["MenuID"]);
//--------------------------------------

require_once '../js/search_person.js.php';

$dg = new sadaf_datagrid("searchPrsonGrid",$js_prefix_address . "../data/person.data.php?task=gridSelect",
                         "personResultDIV", "form_SearchPerson");

$col = $dg->addColumn("شماره پرسنلی","PersonID","int","true");

$col = $dg->addColumn("شماره شناسایی","staff_id","int");
$col->width = 120;
$col = $dg->addColumn("نام", "pfname","string");
$col->width = 100;
$col = $dg->addColumn("نام خانوادگی", "plname","string");
$col->width = 120;
$col = $dg->addColumn("نام پدر", "father_name","string");
$col->width = 100;
$col = $dg->addColumn("ش ش", "idcard_no","int");
$col->width = 100;
$dg->addColumn("عنوان کامل واحد محل خدمت", "org_unit_title","string");
$col = $dg->addColumn("عملیات", "" , "string");
$col->renderer = "SearchPerson.opRender";
$col->width = 60;

$dg->EnableSearch = false;
$dg->notRender = true;
$dg->autoExpandColumn = "org_unit_title";
$dg->DefaultSortField = "PersonID";
$dg->width = 800;
$personGrid = $dg->makeGrid_returnObjects();

?>
<script>	
	SearchPerson.prototype.afterLoad = function()
	{
		this.PersonGrid = <?= $personGrid?>;
		
	}
	var SearchPersonObject = new SearchPerson();
</script>
<form id="form_SearchPerson" method="POST">
<br><br>
<center>
<div>
    <div id="selectPersonDIV">	
    </div>	
    <div id="advanceSearchDIV">
                    <div id="advanceSearchPNL" style="padding: 5px" >
                            <table id="searchTBL" style="width:100%">
                                    <tr>
                                            <td>شماره شناسایی از :</td>
                                            <td><input type="text" class="x-form-text x-form-field" style="width: 50%" id="from_SID" name="from_SID"></td>
                                            <td>تا :</td>
                                            <td><input type="text" class="x-form-text x-form-field" style="width: 50%" id="to_SID" name="to_SID"></td>
                                    </tr>
                                    <tr>
                                            <td>نام :</td>
                                            <td><input type="text" class="x-form-text x-form-field" style="width: 90%" id="pfname" name="pfname"></td>
                                            <td>نام خانوادگی :</td>
                                            <td><input type="text" class="x-form-text x-form-field" style="width: 90%" id="plname" name="plname"></td>
                                    </tr>
                                    <tr>
                                            <td>واحد محل خدمت :</td>
                                            <td colspan="3">
                                            <input type="text" id="ouid" name="ouid">
                                            </td>
                                    </tr>
                                    <tr>
                                            <td>کد ملی :</td>
                                            <td><input type="text" class="x-form-text x-form-field" style="width: 90%" id="national_code" name="national_code"></td>
                                            <td>گروه پرسنلی :</td>
                                            <td></td>
                                    </tr>
                                    <tr>
                                            <td>حالت استخدامی :</td>
                                            <td colspan="3"></td>
                                    </tr>	

                            </table>
                    </div>
            </div>
            </div>
            <br>
            <div>
            <div id="personResultDIV"></div>
            </div>
    </center>
    </form>
