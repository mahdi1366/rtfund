<?php

define("BRANCH_BASE", "3");
define("BRANCH_UM", "3");
define("BRANCH_PARK", "4");

define("Default_Agent_Loan", "9");
define("Default_BranchID", "3"); 

//................. TypeID = 9 .....................
define("DOCTYPE_STARTCYCLE", "1");
define("DOCTYPE_NORMAL", "2");
define("DOCTYPE_ENDCYCLE", "3");
define("DOCTYPE_CLOSECYCLE", "25");
define("DOCTYPE_LOAN_PAYMENT", "4");
define("DOCTYPE_INSTALLMENT_PAYMENT", "5");
define("DOCTYPE_END_REQUEST", "6");
define("DOCTYPE_DEPOSIT_PROFIT", "7");
define("DOCTYPE_DOCUMENT", "8");
define("DOCTYPE_INSTALLMENT_CHEQUE", "9");
define("DOCTYPE_SHARE_PROFIT", "10");
define("DOCTYPE_SAVING_IN", "11");
define("DOCTYPE_SAVING_OUT", "12");
define("DOCTYPE_WARRENTY", "13");
define("DOCTYPE_EQUALCHECKS", "14");
define("DOCTYPE_INCOMERCHEQUE", "15");
define("DOCTYPE_LOAN_DIFFERENCE", "16");
define("DOCTYPE_LOAN_COST", "17");
define("DOCTYPE_WARRENTY_END", "18");
define("DOCTYPE_WARRENTY_EXTEND", "19");
define("DOCTYPE_INSTALLMENT_CHANGE", "20");
define("DOCTYPE_WARRENTY_CANCEL", "21");
define("DOCTYPE_SALARY", "22");
define("DOCTYPE_SALARY_PAY", "24");
define("DOCTYPE_EDITINCOMECHEQUE", "23");
define("DOCTYPE_EXECUTE_EVENT", "26");

//............  TypeID=2 ....................
define("TAFTYPE_PERSONS", "1");
define("TAFTYPE_YEARS", "2");
define("TAFTYPE_ACCOUNTS", "3");
define("TAFTYPE_SUBAGENT", "4");
define("TAFTYPE_BANKS", "6");
define("TAFTYPE_ChequeStatus", "7");

define("TAFSILITYPE_PERSON", "107");
define("TAFSILITYPE_LOAN", "104");
define("TAFSILITYPE_PROCESS", "106");

//...............................

define("COSTID_ShortDeposite", "66"); // 210-01
define("COSTID_Todiee", "63");
define("COSTID_LongDeposite", "119");
define("COSTID_Fund", "1");
define("COSTID_Wage", "470"); // 550
define("COSTID_DepositeWage", "418"); // 750-09
define("COSTID_Bank", "253");
define("COSTID_share", "313"); 
define("COSTID_ShareProfit", "167");
define("COSTID_Commitment", "165");
define("COSTID_saving", "65");
define("COSTID_current", "202");
define("COSTID_BRANCH_PARK", "17"); // 499 park branch
define("COSTID_BRANCH_UM", "205"); // 900 um branch
define("COSTID_DepositeProfit", "416");
define("COSTID_GetDelay", "444");

define("ShareBaseAmount", "1100000");

define("ACCROLE_EXPERT", "1");
define("ACCROLE_MANAGER", "2");

//.............. TypeID=4 ................
define("INCOMECHEQUE_NOTVOSUL", "3001");
define("INCOMECHEQUE_FLOW_VOSUL", "3002");
define("INCOMECHEQUE_VOSUL", "3003");
define("INCOMECHEQUE_BARGASHTI", "3004");
define("INCOMECHEQUE_EBTAL", "3006");
define("INCOMECHEQUE_MOSTARAD", "3008");
define("INCOMECHEQUE_BARGHASHTI_MOSTARAD", "3009");
define("INCOMECHEQUE_MAKHDOOSH", "3010");
define("INCOMECHEQUE_CHANGE", "3011");
define("INCOMECHEQUE_EDIT", "3333");

//............  TypeID=6 ....................
define("BACKPAY_PAYTYPE_EPAY", "4");
define("BACKPAY_PAYTYPE_CHEQUE", "9");
define("BACKPAY_PAYTYPE_CORRECT", "100");
//.............. Loan Statuses TypeID=5 ..............
define("LON_REQ_STATUS_RAW", "1");
define("LON_REQ_STATUS_SEND", "10");
define("LON_REQ_STATUS_RETURN", "11");
define("LON_REQ_STATUS_REJECT", "20");
define("LON_REQ_STATUS_PRECONFIRM", "30");
define("LON_REQ_STATUS_RETURN_CUSTOMER", "35");
define("LON_REQ_STATUS_SEND_CUSTOMER", "40");
define("LON_REQ_STATUS_CUSTOMER_COMPLETE", "50");
define("LON_REQ_STATUS_REJECT_ATTACH", "60");
define("LON_REQ_STATUS_CONFIRM", "70");
define("LON_REQ_STATUS_DEFRAY", "95");
define("LON_REQ_STATUS_ENDED", "101");

//............  TypeID=11 ....................
define("SOURCETYPE_LOAN", "1");
define("SOURCETYPE_CONTRACT", "2");
define("SOURCETYPE_PLAN", "3");
define("SOURCETYPE_WARRENTY", "4");
define("SOURCETYPE_FORM", "5");

define("BLOCKID_LOAN","8");

//------------- flowID baseinfo typeID=11 ---------------
define("FLOWID_LOAN","1");
define("FLOWID_WFM_FORM","5");
define("FLOWID_WARRENTY", "4");
define("FLOWID_CONTRACT","2");
define("FLOWID_ACCDOC","8");

define("FLOWID_TRAFFIC_CORRECT","9");
define("FLOWID_TRAFFIC_DayOFF","10");
define("FLOWID_TRAFFIC_OFF","11");
define("FLOWID_TRAFFIC_DayMISSION","12");
define("FLOWID_TRAFFIC_MISSION","13");
define("FLOWID_TRAFFIC_EXTRA","14");
define("FLOWID_TRAFFIC_CHANGE_SHIFT","15");
//-------------------------------------------------------

define("WAR_STEPID_RAW", "100");
define("WAR_STEPID_CONFIRM", "110");
define("WAR_STEPID_END", "120");
define("WAR_STEPID_CANCEL", "130");

define("CNT_STEPID_RAW", "100");
define("CNT_STEPID_CONFIRM", "110");

define("DMS_DOCTYPE_LETTER", "63");

//-------------- office ---------------
define("OFC_ACCESSTYPE_NORMAL", "1");
define("OFC_ACCESSTYPE_SECRET", "2");

define("OFC_ROLE_SECRET", "1");
 
//------------ attendance ------------
define("ATN_STEPID_RAW", "100");
define("ATN_STEPID_CONFIRM", "200");

//------------ acc doc ------------
define("ACC_STEPID_RAW", "100");
define("ACC_STEPID_CONFIRM", "200");


define("DEFRAYLOAN_VOTEFORM", "9");
define("DEFRAYLOAN_WFMFORM", "16");

//------------ events ---------------
define("EVENT_LOAN_PAYMENT", 1010101);


define("FILE_FRAMEWORK_PICS",getenv("DOCUMENT_ROOT") . "/storage/framework/");

function BeginReport() {

        echo '<html>
			<head>
				<link rel="stylesheet" type="text/css" href="/generalUI/fonts/fonts.css" />
				<META http-equiv=Content-Type content="text/html; charset=UTF-8" >' .
			'</head>
			<body dir="rtl">';
    }

?>