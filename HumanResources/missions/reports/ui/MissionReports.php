<?php
/* -----------------------------
  //	Programmer	: Fatemipour
  //	Date		: 94.03
  ----------------------------- */
ini_set('display_errors', 'On');
require_once '../../../header.inc.php';

function BindCheckList($checkboxPrefix, $basicTypeId) {
    $qry = " select * from hrmstotal.Basic_Info where  TypeID = " . TYPE_STATUS . " and InfoID != " . DRAFT ; 

    $temp = PdoDataAccess::runquery($qry);

    $obj = new CHECKBOXLIST();  
    $obj->datasource = $temp;

    $obj->idfield = $checkboxPrefix . "%InfoID%";
    $obj->valuefield = $checkboxPrefix . "%InfoID%";
    $obj->textfield = "%Title%";
    $obj->columnCount = 4;
    $obj->Allchecked = true;
    $obj->EnableCheckAllButton = true;
    return $obj->bind_checkboxlist();
}
?>

<center>    
    <form id="form_MissReport">
        <div id="MissReportDIV" style="width: 80%;">
            <div id="MissReportPNL" style="width:100%;">
                <table style="width: 100%;">   
                    <tr>
                        <td colspan='9' style='padding:5px' align=center>
                            <fieldset class='x-fieldset x-fieldset-default' style='border-width:1px 1px 1px 1px;width:98%'>
                                <legend class='x-fieldset-header x-fieldset-header-default'> 
                                    وضعیت
                                </legend>
                                <?= BindCheckList("status:", TYPE_STATUS); ?>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <td>

                            <div id='MissDate'></div> 

                        </td>
                    </tr>

                </table>
            </div>
        </div>   
    </form>
</center>
<?php
require_once '../js/MissionReports.js.php';
?>

<script type="text/javascript">
    //  Miss_reportObj.FilterList.render( Miss_reportObj.get("FilterList")); 
    // Miss_reportObj.form = document.getElementById("mainForm");
</script>

</body>
</html>