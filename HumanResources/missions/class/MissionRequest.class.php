<?php

/* -----------------------------
  //	Programmer	: Fatemipour
  //	Date		: 93.8
  ----------------------------- */

require_once '../../header.inc.php';

class MissionRequest {

    //private $MissionRequestsTableName = 'Miss_Requests';
    public $type; //نوع ماموریت : فردی - گروهی
    public $MissionLocation; //محل ماموریت
    public $FromDate; //از تاریخ    
    public $ToDate; //لغایت     
    public $subject; //موضوع ماموریت
    public $stuff;  // وسایل مورد نیاز در ماموریت               
    public $vehicle; // وسیله نقلیه رفت و برگشت            
    public $MissionReport; //گزارش مختصر ماموریت
    public $RequestTime; //زمان ذخیره پیش نویس یا زمان ارسال درخواست
    public $PersonID; //شخص درخواست دهنده
    public $status; //وضعیت درخواست
    public $RequestID;
    public $AreaCoef; //ضریب منطقه

    public function GetAllFinalized() {

        $q = "  select
                   mr.RequestID ,
                    CONCAT(hrmstotal.persons.pfname, ' ' , hrmstotal.persons.plname) as person,
                    mr.subject ,
                    CONCAT(g2j(DATE(mr.RequestTime)) , ' ' ,TIME(mr.RequestTime)) as RequestTime,
                    CONCAT(g2j(DATE(mr.ToDate)) , ' ' ,TIME(mr.ToDate)) as ToDate,
                    CONCAT(g2j(DATE(mr.FromDate)) , ' ' ,TIME(mr.FromDate)) as FromDate ,
                    ci.ptitle  MissionLocation

                from
                    hrmstotal.Miss_Requests  mr
                        inner join
                    hrmstotal.persons ON (hrmstotal.persons.PersonID = mr.PersonID )
                        left join  cities ci on ci.city_id = mr.city_id
                where
                    status = 6
            ";

        $temp = PdoDataAccess::runquery_fetchMode($q);

        return $temp;
    }

    public function GetAllAdmitted(){
        $q = " select
                    mr.RequestID,
                    CONCAT(hrmstotal.persons.pfname, ' ' , hrmstotal.persons.plname) as person,
                    mr.subject,
                    CONCAT(g2j(DATE(mr.RequestTime)) , ' ' ,TIME(mr.RequestTime)) as RequestTime,
                    CONCAT(g2j(DATE(mr.ToDate)) , ' ' ,TIME(mr.ToDate)) as ToDate,
                    CONCAT(g2j(DATE(mr.FromDate)) , ' ' ,TIME(mr.FromDate)) as FromDate ,
                    ci.ptitle MissionLocation

                from
                    hrmstotal.Miss_Requests mr
                        inner join
                    hrmstotal.persons ON (hrmstotal.persons.PersonID = mr.PersonID )
                        left join  cities ci on ci.city_id = mr.city_id
                where
                    status = 7
            ";

        $temp = PdoDataAccess::runquery_fetchMode($q);

        return $temp;
    }
    
    public function Accept() {
        $q = "update hrmstotal.Miss_Requests 
                set status = 7 
                where RequestID= " . $this->RequestID;


        $temp = PdoDataAccess::runquery($q);
        if (is_array($temp))
            return true;
        else
            return false;
    }
  public function ReturnRequest() {
        $q = "update hrmstotal.Miss_Requests 
                set status = 6 
                where RequestID= " . $this->RequestID;


        $temp = PdoDataAccess::runquery($q);
        if (is_array($temp))
            return true;
        else
            return false;
    }
 function GetReport() {
        $q = "select MissionReport from hrmstotal.Miss_Requests where RequestID=:RequestID limit 1";
        $report = PdoDataAccess::runquery($q, array(':RequestID' => $this->RequestID));
        return $report[0]['MissionReport'];
    }
   function GetStatus() {
        $q = "select status from hrmstotal.Miss_Requests where RequestID=:RequestID limit 1";
        $report = PdoDataAccess::runquery($q, array(':RequestID' => $this->RequestID));
        return $report[0]['status'];
    }
     function GetSigner() {
        $Uname = $_SESSION['User']->UserID;

        //Is the request accepted ?
        $status = PdoDataAccess::runquery("SELECT status,PersonID FROM hrmstotal.Miss_Requests where RequestID  = ? ", array($this->RequestID));
        if ($status[0]['status'] == RAW)
            return null;

        $res = PdoDataAccess::runquery("SELECT IFNULL(cp2.ParentPersonID,cp1.ParentPersonID) as SpersonID , 
                                IFNULL(concat(cp2.ParentPFName ,' ', cp2.ParentPLName),concat(cp1.ParentPFName ,' ', cp1.ParentPLName)    ) as Sname
                        FROM baseinfo.ChartPersons cp1
                        left join baseinfo.ChartPersons cp2 on (cp1.ParentPersonID = cp2.PersonID and cp2.RelatedItemMasterID = 1 and cp2.ChartID = 24 )
                        where cp1.PersonID = " . $status[0]['PersonID'] . "
                        and cp1.ChartID = 24 
                        limit 1
                        ");
/*if($_SESSION['UserID'] == 'staghizadeh' ) 
{
echo PdoDataAccess::GetLatestQueryString()	; 
print_r(ExceptionHandler::PopAllExceptions()); die();
}*/
        $SpersonID = $res[0]['SpersonID'];
        $Sname = $res[0]['Sname'];

        /* $q = "SELECT PersonID , concat(persons.pfname,' ',persons.plname) as name
          FROM hrmstotal.Miss_RequestsControlHistory
          join hrmstotal.persons using (PersonID)
          where
          MissionRequestID = :RequestID
          and Action != 'REPORT'
          order by Date DESC
          limit 1";
          $pid = PdoDataAccess::runquery($q, array(':RequestID' => $this->RequestID));
          $SpersonID = $pid[0]['PersonID'];
          $Sname = $pid[0]['name']; */

        //----------------------------------------------------------------------
        $q = "SELECT person_type from persons where PersonID = $SpersonID";
        $pt = PdoDataAccess::runquery($q);
        $personType = $pt[0]['person_type'];

        if ($personType == 1) {
//اگر هیءت علمی است: حکم اجرایی ش 
            $q = "SELECT p.*,position.title , position.ouid 
                    from persons 
                    join staff on (persons.PersonID = staff.PersonID  and persons.person_type = staff.person_type )
                    join professor_exe_posts p on (p.staff_id = staff.staff_id)
                    join position on (position.post_id = p.post_id)
                    where persons.PersonID = $SpersonID and (to_date>Now() OR to_date='0000-00-00' OR to_date is null)  
                 ";

            $res = PdoDataAccess::runquery($q);
        } else {
//اگر هیءت علمی نیست:آخرین حکمش  
            $q = "SELECT position.title ,staff.ouid
                from persons 
                join staff on (persons.PersonID = staff.PersonID  and persons.person_type = staff.person_type )
                join position on (position.post_id = staff.post_id)
                where persons.PersonID = $SpersonID ";
            $res = PdoDataAccess::runquery($q);
        }
        /* if ($Uname=='jafarkhani'){
          echo "res";
          var_dump($res);
          } */

        if (count($res) > 1) {
//OUID array of signer
            $Souid = array();
            foreach ($res as $rec) {
                $Souid[] = $rec['ouid'];
            }

            /* if ($Uname=='jafarkhani'){
              echo "Souid";
              var_dump($Souid);
              } */

            /* شخص درخواست دهنده را پیدا می کنیم ببینیم OUID ش کجاست */
            $q = "SELECT  staff.ouid
                    FROM hrmstotal.Miss_Requests
                    join hrmstotal.persons using (PersonID)
                    join staff on (persons.PersonID = staff.PersonID  and persons.person_type = staff.person_type )                    
                    where 
                    RequestID = :RequestID limit 1";
            $ouid = PdoDataAccess::runquery($q, array(':RequestID' => $this->RequestID));
            $ROUID = $ouid[0]['ouid'];

            /* if ($Uname=='jafarkhani'){
              echo "</br>".$q."</br>";
              echo "ROUID";
              var_dump($ROUID);
              } */

            $common = array_search($ROUID, $Souid);
            if ($common !== false) {
                $title = $res[$common]['title'];
            } else {
                $SouidArr = array();
                /* تمام پرنت های تمام آی دی های شخص امضا کننده را در می آوریم */
                for ($i = 0; $i < count($Souid); $i++) {
                    $SouidArr[$i] = array();
                    $SouidArr[$i][] = $Souid[$i];
                    $CurSouid = $Souid[$i];
                    $p = true;
                    while ($p) {
                        $p = PdoDataAccess::runquery("SELECT parent_ouid FROM hrmstotal.org_new_units where ouid = $CurSouid and parent_ouid is not null limit 1 ");

                        if ($p & count($p) > 0) {
                            $SouidArr[$i][] = $p[0]['parent_ouid'];
                            $CurSouid = $p[0]['parent_ouid'];
                        }
                    }
                }

                //var_dump($SouidArr);
                /* شروع میکنیم از آی دی شخص به بالا ببینیم کدام یک در آی دی های شخص  امضا کننده موجو هست */

                $p = true;
                $CurrRouid = $ROUID;
                do {
                    //  echo "CUR".$CurrRouid."</br>";
                    $commons = array();
                    foreach ($SouidArr as $SouidArrEL) {
                        $common = array_search($CurrRouid, $SouidArrEL);
                        $commons[] = ($common == false) ? count($SouidArrEL) + 1 : $common;
                        if ($common) {
                            $p = false;
                        }
                    }
                    // echo "Commons:";
                    //var_dump($commons);
                    if ($p) {
                        $parent = PdoDataAccess::runquery("SELECT parent_ouid FROM hrmstotal.org_new_units where ouid = $CurrRouid and parent_ouid is not null limit 1 ");
                        if ($parent) {
                            $CurrRouid = $parent[0]['parent_ouid'];
                        } else {
                            $p = false;
                        }
                    }
                } while ($p);
                if (!$p) {
                    $ind = array_keys($commons, min($commons));
                    //echo "IND:";
                    //var_dump($ind);
                    /* در این جا اگر هم در هیچ کدام پیدا نشود، همان اولی را نمایش می دهد */
                    $title = $res[$ind[0]]['title'];
                } else {
                    $title = '';
                }
            }
        } else {
            $title = $res[0]['title'];
        }
        // echo "*****</br>";
        //var_dump($title);

        return array("personID" => $SpersonID, "title" => $title, "name" => $Sname);
    }
}
?>




