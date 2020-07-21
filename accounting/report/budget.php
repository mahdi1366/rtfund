<?php
//-----------------------------
//	Programmer	: S.M.Mokhtsri
//	Date		: 90.02
//-----------------------------

require_once '../../header.inc.php';
require_once "ReportGenerator.class.php";

if(isset($_REQUEST["show"]))
{
    $where = "";
    $whereParam = "";
    if(!empty($_POST["fromDate"]))
    {
        $where .= " AND d.DocDate >= :q1 ";
        $whereParam[":q1"] = DateModules::shamsi_to_miladi($_POST["fromDate"], "-");
    }
    if(!empty($_POST["toDate"]))
    {
        $where .= " AND d.DocDate <= :q2 ";
        $whereParam[":q2"] = DateModules::shamsi_to_miladi($_POST["toDate"], "-");
    }

    $query = "select 
		di.CostID,
		sum(di.DebtorAmount) DebtorAmount,
		sum(di.CreditorAmount) CreditorAmount,
		d.DocDate,cc.level1,b1.BlockDesc
		from ACC_DocItems di 
			join ACC_docs d using(DocID)
			join ACC_CostCodes cc using(CostID)
			join ACC_blocks b1 on(level1=b1.BlockID)
			where (cc.level1=1044 OR cc.level1=1045 OR cc.level1=1047) " . $where . "
			group by cc.level1";

    $dt = PdoDataAccess::runquery($query, $whereParam);

    $fromDate = DateModules::shamsi_to_miladi($_POST["fromDate"], "-");
    $toDate = DateModules::shamsi_to_miladi($_POST["toDate"], "-");

    $datetime1 = new DateTime($fromDate);
    $datetime2 = new DateTime($toDate);
    $diff=$datetime1->diff($datetime2);
    $diffDateSign = $diff->format('%R');
    $diffDate = $diff->format('%a');
    $diffDate = (isset($_POST["fromDate"]) && !empty($_POST["fromDate"]) && isset($_POST["fromDate"]) && !empty($_POST["fromDate"]))? $diffDate : 365; //new added

    // new calculate
    $ParentIDOpIn = [87]; //daramde amaliyati
    $ParentIDnOpIn = [88];  //daramde gheyre amaliyati
    $ParentIDCost = [26,40,50,54,69,72,81,84];  //hazine

    $queryLoc="select b.ParentID, ba.*
		from acc_budgetapproved ba 
		left JOIN acc_budgets b ON(b.BudgetID=ba.BudgetID) 
			where ba.CycleID=? AND b.ParentID =?";

    $totalOpInApp=0;
    $totalOpInPre=0;
    foreach ($ParentIDOpIn as $dtLoc){
        $OpIn = PdoDataAccess::runquery($queryLoc,array($CycleID,$dtLoc));
        $appAmount=0;
        $PreAmount=0;
        foreach ($OpIn as $item){
            $appAmount+= $item['approvedAmount'];
            $PreAmount+= $item['PrevisionAmount'];
        }
        $totalOpInApp+= $appAmount;
        $totalOpInPre+= $PreAmount;
    }

    $totalnOpInApp=0;
    $totalnOpInPre=0;
    foreach ($ParentIDnOpIn as $dtLoc){
        $nOpIn = PdoDataAccess::runquery($queryLoc,array($CycleID,$dtLoc));
        $appAmount=0;
        $PreAmount=0;
        foreach ($nOpIn as $item){
            $appAmount+= $item['approvedAmount'];
            $PreAmount+= $item['PrevisionAmount'];
        }
        $totalnOpInApp+= $appAmount;
        $totalnOpInPre+= $PreAmount;
    }

    $totalCostApp=0;
    $totalCostPre=0;
    foreach ($ParentIDCost as $dtLoc){
        $cost = PdoDataAccess::runquery($queryLoc,array($CycleID,$dtLoc));
        $appAmount=0;
        $PreAmount=0;
        foreach ($cost as $item){
            $appAmount+= $item['approvedAmount'];
            $PreAmount+= $item['PrevisionAmount'];
        }
        $totalCostApp+= $appAmount;
        $totalCostPre+= $PreAmount;

    }
    // end new calculate


    // daramade amaliyati
    $opInApproved = $totalOpInApp;  //mablaghe mosavab new Editted
    $monthOpInApproved = $opInApproved*$diffDate/365;
    $monthOpInApproved = number_format($monthOpInApproved, 0);
    $performanceOpInApp = number_format($dt[0]['CreditorAmount']-$dt[0]['DebtorAmount']);
    $opInAppRealPerc = $performanceOpInApp/$monthOpInApproved*100;
    $opInAppRealPerc = number_format($opInAppRealPerc,2);
    $opInPrevision = $totalOpInPre;  //mablaghe pishbini new Editted
    $monthOpInPrevision = $opInPrevision*$diffDate/365;
    $PreToAppPercent=$monthOpInPrevision/$performanceOpInApp*100;
    $PreToAppPercent = number_format($PreToAppPercent,2);

    // daramade gheyre amaliyati
    $nonOpInApproved = $totalnOpInApp;   //mablaghe mosavab new Editted
    $monthNonOpInApproved = ($nonOpInApproved*$diffDate)/365;
    $monthNonOpInApproved = number_format($monthNonOpInApproved, 0);
    $performanceNonOpInApp = number_format($dt[1]['CreditorAmount']-$dt[1]['DebtorAmount']);
    $nonOpInAppRealPerc = ($performanceNonOpInApp/$monthNonOpInApproved)*100;
    $nonOpInAppRealPerc = number_format($nonOpInAppRealPerc, 2);
    $nonOpInPrevision = $totalnOpInPre;   //mablaghe pishbini new Editted
    $monthNonOpInPrevision = ($nonOpInPrevision*$diffDate)/365;
    $NPreToAppPercent=($monthNonOpInPrevision/$performanceNonOpInApp)*100;
    $NPreToAppPercent = number_format($NPreToAppPercent, 2);

    // hazine amaliyati
    $CostApproved = $totalCostApp;   //mablaghe mosavab new Editted
    $monthCostApproved = $CostApproved*$diffDate/365;
    $monthCostApproved = number_format($monthCostApproved, 0);
    $performanceCostPre = number_format($dt[2]['DebtorAmount']-$dt[2]['CreditorAmount']);
    $CostRealPerc = $performanceCostPre/$monthCostApproved*100;
    $CostRealPerc = number_format($CostRealPerc,2);
    $CostPrevision = $totalCostPre;   //mablaghe pishbini new Editted
    $monthCostPrevision = $CostPrevision*$diffDate/365;
    $CostPreToAppPercent=$monthCostPrevision/$performanceCostPre*100;
    $CostPreToAppPercent=number_format($CostPreToAppPercent,2);

    //............................................................

    BeginReport();
    echo "<table style='border:2px groove #9BB1CD;border-collapse:collapse;width:100%'><tr>
			<td width=60px><img src='/framework/icons/logo.jpg' style='width:120px'></td>
			<td align='center' style='height:100px;vertical-align:middle;font-family:titr;font-size:15px'>
				گزارش بودجه
			</td>
			<td width='200px' align='center' style='font-family:tahoma;font-size:11px'>تاریخ تهیه گزارش : "
        . DateModules::shNow() . "<br>";
    if(!empty($_POST["fromDate"]))
    {
        echo "<br> گزارش از تاریخ : " . $_POST["fromDate"];
    }
    if(!empty($_POST["toDate"]))
    {
        echo "<br> گزارش تا تاریخ: " . $_POST["toDate"] ;
    }

    echo "</td></tr></table>";

    //..........................................................

    ?>
    <style>
        .newTable  td, th {
            border: 1px solid #dddddd;
            text-align: right;
            padding: 8px;
        }
        .newTable  th {
            color: rgb(21, 66, 139);
            background-color: rgb(189, 211, 239);
        }
        .newTable  td{
            text-align: center;
        }
        .newTable table{
            border:2px groove #9BB1CD;border-collapse:collapse;width:100%;font-family: nazanin;
            font-size: 16px;line-height: 20px;
        }
        .newTable  thead th{
            text-align: center;
        }
        .title{
            text-align: center;
            font-family: nazanin;
        }

    </style>

    <div class="newTable">
        <table>
            <h2 class="title">گزارش تراز بودجه در یک نگاه</h2>
            <thead>
            <tr>
                <th scope="col" colspan="2">عنوان</th>
                <th scope="col">بدهکار</th>
                <th scope="col">بستانکار</th>
                <th scope="col">مانده پایان دوره</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>درآمد</td>
                <td>عملیاتی</td>
                <td><?= number_format($dt[0]['DebtorAmount']) ?></td>
                <td><?= number_format($dt[0]['CreditorAmount']) ?></td>
                <td><?= number_format($dt[0]['CreditorAmount'] - $dt[0]['DebtorAmount']) ?></td>
            </tr>
            <tr>
                <td>درآمد</td>
                <td>غیرعملیاتی</td>
                <td><?= number_format($dt[1]['DebtorAmount']) ?></td>
                <td><?= number_format($dt[1]['CreditorAmount']) ?></td>
                <td><?= number_format($dt[1]['CreditorAmount'] - $dt[1]['DebtorAmount']) ?></td>
            </tr>
            <tr>
                <td>هزینه</td>
                <td>عملیاتی</td>
                <td><?= number_format($dt[2]['DebtorAmount']) ?></td>
                <td><?= number_format($dt[2]['CreditorAmount']) ?></td>
                <td><?= number_format($dt[2]['DebtorAmount'] - $dt[2]['CreditorAmount']) ?></td>
            </tr>
            </tbody>
        </table>
        <table>
            <h2 class="title">پیشبینی عملکرد در یک نگاه</h2>
            <thead>
            <tr>
                <th scope="col" colspan="2">عنوان</th>
                <th scope="col">مصوب سال</th>
                <th scope="col">پیش بینی </th>
                <th scope="col">عملکرد</th>
                <th scope="col">درصد تحقق</th>
                <th scope="col">پیش بینی سال آتی</th>
                <th scope="col">نسبت پیش بینی به عملکرد</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <strong class="book-title">درآمد</strong>
                </td>
                <td class="item-stock">عملیاتی</td>
                <td><?= $opInApproved  ?></td>
                <td><?= $monthOpInApproved  ?></td>
                <td><?= $performanceOpInApp  ?></td>
                <td><?= $opInAppRealPerc  ?>%</td>
                <td><?= $opInPrevision  ?></td>
                <td><?= $PreToAppPercent  ?>%</td>
            </tr>
            <tr>
                <td>
                    <strong class="book-title">درآمد</strong>
                </td>
                <td class="item-stock">غیرعملیاتی</td>
                <td><?= $nonOpInApproved  ?></td>
                <td><?= $monthNonOpInApproved  ?></td>
                <td><?= $performanceNonOpInApp  ?></td>
                <td><?= $nonOpInAppRealPerc  ?>%</td>
                <td><?= $nonOpInPrevision  ?></td>
                <td><?= $NPreToAppPercent  ?>%</td>
            </tr>
            <tr>
                <td>
                    <strong class="book-title">هزینه</strong>
                </td>
                <td class="item-stock">هزینه عملیاتی</td>
                <td><?= $CostApproved  ?></td>
                <td><?= $monthCostApproved  ?></td>
                <td><?= $performanceCostPre  ?></td>
                <td><?= $CostRealPerc  ?>%</td>
                <td><?= $CostPrevision  ?></td>
                <td><?= $CostPreToAppPercent  ?>%</td>
            </tr>
            </tbody>


        </table>
    </div>

    <?


}
?>
<script>
    BudgetReport.prototype = {
        TabID : '<?= $_REQUEST["ExtTabID"]?>',
        address_prefix : "<?= $js_prefix_address?>",

        get : function(elementID){
            return findChild(this.TabID, elementID);
        }
    }

    BudgetReport.prototype.showReport = function(btn, e)
    {
        this.form = this.get("mainForm")
        this.form.target = "_blank";
        this.form.method = "POST";
        this.form.action =  this.address_prefix + "budget.php?show=true";
        this.form.submit();
        this.get("excel").value = "";
        return;
    }

    function BudgetReport()
    {
        this.formPanel = new Ext.form.Panel({
            renderTo : this.get("main"),
            frame : true,
            layout :{
                type : "table",
                columns :2
            },
            bodyStyle : "text-align:right;padding:5px",
            title : "گزارش بودجه",
            defaults : {
                labelWidth :120,
                width : 400,
                style : "margin-left:15px"
            },
            width : 650,
            items :[{
                xtype : "combo",
                colspan : 2,
                width : 400,
                store : new Ext.data.SimpleStore({
                    proxy: {
                        type: 'jsonp',
                        url: "/accounting/global/domain.data.php?task=SelectCycles",
                        reader: {root: 'rows',totalProperty: 'totalCount'}
                    },
                    fields : ['CycleID','CycleDesc'],
                    autoLoad : true
                }),
                fieldLabel : "دوره",
                queryMode : 'local',
                value : "<?= !isset($_SESSION["accounting"]["CycleID"]) ? "" : $_SESSION["accounting"]["CycleID"] ?>",
                displayField : "CycleDesc",
                valueField : "CycleID",
                hiddenName : "CycleID"
            },{
                xtype : "shdatefield",
                name : "fromDate",
                width : 300,
                fieldLabel : "از تاریخ"
            },{
                xtype : "shdatefield",
                name : "toDate",
                width : 300,
                fieldLabel : "تا تاریخ"
            }/*,{
                xtype : "numberfield",
                hideTrigger : true,
                width : 300,
                name : "opInApproved",
                fieldLabel : "مبلغ مصوب درآمد عملیاتی سال"
            },{
                xtype : "numberfield",
                hideTrigger : true,
                width : 300,
                name : "opInPrevision",
                fieldLabel : "مبلغ پیش بینی درآمد عملیاتی سال آتی"
            },{
                xtype : "numberfield",
                hideTrigger : true,
                width : 300,
                name : "nonOpInApproved",
                fieldLabel : "مبلغ مصوب درآمد غیر عملیاتی سال"
            },{
                xtype : "numberfield",
                hideTrigger : true,
                width : 300,
                name : "nonOpInPrevision",
                fieldLabel : "مبلغ پیش بینی درآمد غیرعملیاتی سال آتی"
            },{
                xtype : "numberfield",
                hideTrigger : true,
                width : 300,
                name : "CostApproved",
                fieldLabel : "مبلغ مصوب هزینه سال"
            },{
                xtype : "numberfield",
                hideTrigger : true,
                width : 300,
                name : "CostPrevision",
                fieldLabel : "مبلغ پیش بینی هزینه سال آتی"
            }*/],
            buttons : [{
                text : "مشاهده گزارش",
                handler : Ext.bind(this.showReport,this),
                iconCls : "report"
            }]
        });
    }

    BudgetReportObj = new BudgetReport();
</script>
<form id="mainForm">
    <center><br>
        <div id="main" ></div>
    </center>
    <input type="hidden" name="excel" id="excel">
</form>