<?php
//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 93.08
//-----------------------------
require_once '../../header.inc.php';
require_once '../class/MissionRequest.class.php';
require_once '../data/definitions.php';

$Uname = $_SESSION['User']->UserID;

/*
  ini_set('display_errors', 'On');
  error_reporting(E_ALL);
  $_SESSION['User'] = new StaffUser("jafarkhani");
  $_SESSION["PersonID"] = $_SESSION['User']->PersonID;
  $_SESSION["UserID"] = "jafarkhani";
  }
 */

$RequestID = $_REQUEST['RequestID'];
/* بررسی دسترسی */
if ($Uname != 'jafarkhani' && $Uname != 'fatemipour') {
    $res1 = PdoDataAccess::runquery("SELECT PersonID 
                                 FROM hrmstotal.Miss_Requests
                                 WHERE RequestID = ? ", array($RequestID));

    /*if ($res1[0]['PersonID'] != $_SESSION['User']->PersonID) {
        die();
    }*/
    if ($res1[0]['PersonID'] != $_SESSION['User']->PersonID) {
        /* UPDATED: Allows controllers to see the print */
        $res2 = PdoDataAccess::runquery("SELECT PersonID 
                                 FROM hrmstotal.Miss_RequestsControlHistory
                                 WHERE RequestID = ? and PersonID = ? limti 1", array($RequestID , $_SESSION['User']->PersonID));
        if (count($res2) == 0)
            die();
    }
}

//------------------------------------------------------------------------------
function data_uri($content, $mime) {
    $base64 = base64_encode($content);
    return ('data:' . $mime . ';base64,' . $base64);
}

$q = "SELECT 
        Miss_Requests.RequestID ,
        CONCAT(persons.pfname,' ',persons.plname) as name,
        org_new_units.ptitle as Sender,
        bi2.title as type ,
        bi1.title as vehicle,
	concat ( hrmstotal.states.ptitle , ' ' , hrmstotal.cities.ptitle) as MissionLocation,
	Miss_Requests.subject,
	Miss_Requests.stuff,         
	/*CONCAT(g2j(DATE(Miss_Requests.RequestTime)) , ' ' ,TIME(Miss_Requests.RequestTime)) as RequestTime,*/
        g2j(DATE(Miss_Requests.RequestTime)) as RequestTime,
        /*CONCAT(g2j(DATE(Miss_Requests.FromDate)) , ' ' ,TIME(Miss_Requests.FromDate)) as FromDate,*/
        g2j(DATE(Miss_Requests.FromDate)) as FromDate,
        /*CONCAT(g2j(DATE(Miss_Requests.ToDate)) , ' ' ,TIME(Miss_Requests.ToDate)) as ToDate,*/
        g2j(DATE(Miss_Requests.ToDate)) as ToDate,
        Miss_Requests.MissionReport as report,
        position.title as OrganizationPost
      FROM hrmstotal.Miss_Requests
        join persons on (persons.PersonID = Miss_Requests.PersonID)
        join hrmstotal.cities using (city_id)
        join hrmstotal.states on (states.state_id = Miss_Requests.state_id )
        join staff on (persons.PersonID = staff.PersonID  and persons.person_type = staff.person_type )
        join org_new_units on (org_new_units.ouid = staff.ouid)
        join hrmstotal.writs on (writs.staff_id = staff.staff_id   
					and writs.writ_id =  staff.last_writ_id
					and writs.writ_ver = staff.last_writ_ver )
        left join hrmstotal.position on (position.post_id = writs.post_id)
        join hrmstotal.Miss_BaseInfo bi1 on (bi1.InfoID = Miss_Requests.vehicle and bi1.TypeID = 2)
        join hrmstotal.Miss_BaseInfo bi2 on (bi2.InfoID = Miss_Requests.type and bi2.TypeID = 3)
        join hrmstotal.Miss_BaseInfo bi3 on (bi3.InfoID = Miss_Requests.status and bi3.TypeID = 1)
      WHERE RequestID = ? 
      LIMIT 1";


$res = PdoDataAccess::runquery($q, array($RequestID));
$rec = $res[0];

$missObj = new MissionRequest();
$missObj->RequestID = $_REQUEST['RequestID'];
$status = $missObj->GetStatus();

if ($status == ACCEPTED_ || $status == FINALIZED || $status == REPORTED || $status == ADMITTED || $status == PAYED) {    
    $signer = $missObj->GetSigner();
    $q = "select sign from hrmstotal.PersonSigns where PersonID = " . $signer['personID'] . " limit 1";

    $SignPic_res = PdoDataAccess::runquery($q);
  
    if (count($SignPic_res) > 0) {
        $SignPic = $SignPic_res[0]['sign'];
    } else {
        $SignPic = null;
    }
}

?>
<style>
    .break { page-break-before: always; }
</style>
<html>
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>	
        <style media="print">
            .noPrint {display:none;}
        </style>
        <style type="text/css">
            body {font-family: tahoma;font-size: 8pt;margin-top: 0px}
            td	 {padding: 4px;font-size: 8pt; line-height:2;}
            span {font-size: 7pt; font-weight : bold;}
            fieldset{border-color: #AAAAAA;border-style: dotted;}
        </style>
    </head>
    <body dir="rtl" style="margin-top: 0px; margin-right: 0px; margin-left: 0px;">
        <div style="width:100%;height:100%;background-position: center 10%; background-repeat: no-repeat;">
            <center>
                <input class="noPrint" onclick="javascript:window.print();" type="button" style="font-family: tahoma" value="  چاپ  ">
                <div style="width:100%;">
                    <table style="width:95%;border-collapse: collapse;border:0">

                        <tr>
                            <td width="100px" style="text-align:'right' ; height:'10px'">
                                <img src="fum.jpg" width="80px">
                            </td>  

                            <td align="center">
                                <font style="font-family: b titr; font-size: 11pt; font-weight: bold;">                                        
                                برگ درخواست ماموریت 

                                </font>
                                <!--<font style="font-family: b titr; font-size: 10pt; font-weight: bold;">  
                                
                                موضوع ماده ۲۸ آیین نامه استخدامی غیر هیات علمی دانشگاهها و موسسات آموزش عالی، پژوهش و فناوری                                
                                
                                </font>
                                -->
                            </td>                            

                            <td width="150px"  align="right" style="line-height:1;">
                                شماره پیگیری :
                                <?php echo $rec['RequestID']; ?>
                                </br></br>
                                تاریخ درخواست : 
                                <?php echo $rec['RequestTime']; ?>
                            </td>

                        </tr>
                    </table>

                    <table border="1" style="width:95%;border-collapse: collapse;">
                        <tr>
                            <td align="right" width="100%" colspan="2" > 
                                موسسه :
                                <span>   دانشگاه فردوسی مشهد  </span>
                            </td>
                        </tr>
                        <tr>
                            <td align="right" width="100%" colspan="2"> 
                                واحد اعزام کننده:
                                <span> <?php echo $rec['Sender']; ?> </span>                               
                            </td>
                        </tr>  

                        <tr>

                            <td align="right" width="100%" colspan="2">   نام و نام خانوادگی مامور :

                                <span> <?php echo $rec['name'] ?> </span>

                            </td>                                
                        </tr>
                        <tr>
                            <td align="right" width="100%" colspan="2" > 
                                عنوان پست سازمانی:
                                <span>  <?php echo $rec['OrganizationPost']; ?>     </span>
                            </td>

                        </tr>  

                    <!--   <tr>
                            <td align="right" width="50%"> 
                                موضوع درخواست :
                                <b>  صدور حکم ماموریت  </b>                               
                            </td>
                            <td align="right"  width="50%"> 
                                تاریخ درخواست:
                        <?php echo $rec['RequestTime']; ?> 
                            </td>
                        </tr> -->

                        <tr>
                            <td align="right" width="40%" >                                
                                نوع ماموریت:
                                <span>  <?php echo $rec['type']; ?>  </span>
                            </td>
                            <td align="right" width="60%">  
                                تاریخ ماموریت: 

                                از 
                                <span> <?php echo $rec['FromDate']; ?> </span>

                                لغایت 
                                <span> <?php echo $rec['ToDate']; ?> <span>

                                        </td>
                                        </tr>

                                        <tr>
                                            <td align="right" width="40%" >
                                                محل ماموریت :
                                                <span> <?php echo $rec['MissionLocation']; ?> </span>
                                            </td>

                                            <td align="right" width="60%" >
                                                نوع وسیله رفت و برگشت : 
                                                <span> <?php echo $rec['vehicle']; ?> </span>
                                            </td>                            
                                        </tr>  


                                        <tr>
                                            <td align="right" width="100%" colspan="2" >
                                                موضوع ماموریت : 
                                                <span> <?php echo $rec['subject']; ?> </span>
                                            </td>

                                        </tr>

                                        <tr>
                                            <td align="right" width="100%"  colspan="2" >
                                                وسایل و تجهیزات مورد نیاز در ماموریت :
                                                <span> <?php echo $rec['stuff']; ?> </span>
                                            </td>

                                        </tr>

                                        <?php if ($status == ACCEPTED_ || $status == FINALIZED || $status == REPORTED || $status == ADMITTED || $status == PAYED) { ?>
                                            <tr>
                                                <td align="right" width="100%"  colspan="2" >
                                                    نام و نام خانوادگی مقام موافقت کننده:
                                                    <span> <?php echo $signer['name']; ?> </span>


                                                    </br>                                    
                                                    عنوان پست سازمانی:

                                                    <span> <?php echo $signer['title']; ?> </span>
                                                    </br>
                                                    <?php
                                                    echo "<img id='MissSignPic' src=" . data_uri($SignPic, 'image/jpeg') . " />";
                                                    //echo "<img id='MissSignPic' style='width:100px;' src=" . data_uri($SignPic, 'image/jpeg') . " />"; 
                                                    ?> 

                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="right" width="100%"  colspan="2" >
                                                    گزارش  مختصر ماموریت:
                                                    </br>
                                                    <?php echo $rec['report'] . '</br>'; ?>
                                                </td>

                                            </tr>
                                        <? } ?>
                                        </table>
                                        </div>
                                        </div>
                                        </body> 

                                        <script>
                                            var w = document.getElementById('MissSignPic').width;
                                            var h = document.getElementById('MissSignPic').height;
                                            if (w<h){
                                                document.getElementById('MissSignPic').style.width = 100;
                                            }else{
                                                document.getElementById('MissSignPic').style.height = 100;
                                            }
                                        </script>

                                        </html>

