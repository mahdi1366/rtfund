<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------

//require_once inc_manage_tree;
//require_once inc_manage_unit;

class manage_staff extends PdoDataAccess
{
	
	public $staff_id;
	public $PersonID;
	public $personel_no;
	public $person_type;
	public $bank_id;
	public $account_no;
	public $last_writ_id;
	public $last_writ_ver;
	public $last_cost_center_id;
	public $last_retired_pay;
	public $barcode;
	public $work_start_date;
	public $card_no;
	public $tafsili_id;
	public $UnitCode;
	public $FacCode;
	public $EduGrpCode;
	public $ProCode;
	public $DutyUnit;
	public $writ_execute_date;
	public $extra_work_coef;
	public $sum_paied_pension;
	public $retired_date;
	public $retired_state;
	public $job_id;
	public $post_id;
	public $ouid;
	public $ledger_number;
	public $die_date;
	public $parent_staff_id;
	public $last_person_type;
	public $ProfWorkStart ;
	public $Over25 ; 
	public $ResearchGrpCode ; 
	
	function __construct($PersonID = "",$person_type = "", $staff_id = "")
	{ 
		
		
		if($staff_id != "")
		{ 
			$whereParam = array(":staff_id" => $staff_id);
			$query = "select * from HRM_staff where staff_id=:staff_id";
			PdoDataAccess::FillObject($this, $query, $whereParam);
		}
		else if($PersonID != "" && $person_type != "" )
		{
					
			// if PersonID != "" and staff_id = "" then return the staff_id that it's person_type is minimum
			//----------------------------------------------------------------------------------------------
			$whereParam  = array(":PID" => $PersonID);
			$whereParam[":ptype"] = $person_type;
			
			$query = "select * from HRM_staff where PersonID=:PID AND person_type=:ptype order by person_type ASC";
			$temp = PdoDataAccess::runquery($query, $whereParam);
			PdoDataAccess::FillObjectByArray($this, $temp[0]);
		}
		else if ($PersonID != "" )
		{ 
			$whereParam  = array(":PID" => $PersonID);
			$query = " select s.*
								from HRM_persons p inner join HRM_staff s  on p.personid = s.personid and p.person_type = s.person_type
											where s.PersonID=:PID ";

			$temp = PdoDataAccess::runquery($query, $whereParam);
			PdoDataAccess::FillObjectByArray($this, $temp[0]);
						
		}
	 }
	
	function AddStaff($pdo = null)
	{             
                     
		//............................. آیا فرد قبلا با این نوع شخص در سیستم ثبت شده است؟.....................
		$qry = " select staff_id from HRM_staff where PersonID = ".$this->PersonID." and person_type = ".$this->person_type ; 
		$res = PdoDataAccess::runquery($qry) ;
                                                
		if(count($res) > 0 && $res[0]['staff_id'] > 0 )
		{
			return true; 
		}

		$this->staff_id = manage_staff::LastID($pdo) + 1;
								 
		if( PdoDataAccess::insert("HRM_staff", $this, $pdo) === false )                         
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;			
		$daObj->MainObjectID = $this->staff_id;
		$daObj->TableName = "HRM_staff";
		$daObj->execute($pdo);
                
                
	 	return true;	
		
	}
	
	function EditStaff()
	{
				
		$whereParams = array();	 	
	 	$whereParams[":sid"] = $this->staff_id;
		
		if($this->bank_id == -1 )
		{
			parent::PushException("لطفا اطلاعات مربوط به بانک را تکمیل نمایید .");
			return false ;
		}
	 	if(PdoDataAccess::update("HRM_staff",$this," staff_id =:sid ", $whereParams) === false){
			
			return false;
		}
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;			
		$daObj->MainObjectID = $this->staff_id;
		$daObj->TableName = "HRM_staff";
		$daObj->execute();

		//---------- Edit on hrms_total ----------------

		/*PdoDataAccess::runquery("update hrms_total.staff s1 join staff s on(s1.staff_id=s.staff_id)
			left join writs w on(s.staff_id=w.staff_id AND s.last_writ_id=w.writ_id AND s.last_writ_ver=w.writ_ver)
			left join staff_details sd on(sd.staff_id=s.staff_id) set
		s1.personel_no=s.personel_no,
		s1.person_type=s.person_type,
		s1.bank_id=s.bank_id,
		s1.account_no=s.account_no,
		s1.last_writ_id=s.last_writ_id,
		s1.last_writ_ver=s.last_writ_ver,
		s1.last_retired_pay=s.last_retired_pay,
		s1.barcode=s.barcode,
		s1.work_start_date=s.work_start_date,
		s1.card_no=s.card_no,
		s1.tafsili_id=s.tafsili_id,
		s1.UnitCode=s.UnitCode,
		s1.FacCode=s.FacCode,
		s1.EduGrpCode=s.EduGrpCode,
		s1.FUNdescription=sd.FUNdescription,
		s1.FUNsbid=if(w.sbid is not null,w.sbid,sd.FUNsbid),
		s1.FUNsfid=if(w.sfid is not null,w.sfid,sd.FUNsfid) ,
		s1.ProCode=s.ProCode,
		s1.FUNeducational_level=if(w.education_level is not null,w.education_level,sd.FUNeducational_level),
		s1.FUNscience_level=if(w.science_level is not null,w.science_level,sd.FUNscience_level),
		s1.DutyUnit=s.DutyUnit,
		s1.FUNemp_mode=if(w.emp_mode is not null,w.emp_mode,sd.FUNemp_mode),
		s1.writ_execute_date=w.execute_date,
		s1.extra_work_coef=s.extra_work_coef,
		s1.sum_paied_pension=s.sum_paied_pension,
		s1.FUNChildrenCount=if(w.children_count is not null,w.children_count,sd.FUNChildrenCount),
		s1.FUNWorkAge=sd.FUNWorkAge,
		s1.retired_date=s.retired_date,
		s1.retired_state=s.retired_state,
		s1.job_id=s.job_id,
		s1.post_id=s.post_id,
		s1.ouid=s.ouid

		where s1.staff_id=? AND s1.staff_id=s.staff_id" , array($this->staff_id));

		if(parent::GetExceptionCount() == 0)
		{
			$daObj->TableName = "hrms_total.staff";
			$daObj->execute();
		}
		else
		{
			print_r(PdoDataAccess::PopAllExceptions());
		}*/
	 	return true;
	}

	static function SaveStaffTax($PID , $SID , $TaxVal )
	{
		$query = " update staff set sum_paied_pension = ". $TaxVal ." where  personid =".$PID." and staff_id = ".$SID ;
		
		if(parent::runquery($query, array()) === false )
		   return false ;
       
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;				
		$daObj->MainObjectID = $this->staff_id;
		$daObj->TableName = "staff";
		$daObj->execute();

		return true ; 

	}
			
	/**
	* ط³ظ†ظˆط§طھ ط®ط¯ظ…طھ ط´ط®طµ ط±ط§ ط¨ظ‡ ط³ط§ظ„ طŒ ظ…ط§ظ‡ ظˆ ط±ظˆط² ظ…ط´ط®طµ ظ…ظٹ ع©ظ†ط¯.
	* ظپط±ط¶ ط¨ط± ط§ظٹظ† ط§ط³طھ ع©ظ‡ ط³ظ†ظˆط§طھ ط®ط¯ظ…طھ ط­ع©ظ… ظ‚ط¨ظ„ظٹ ط¯ط±ط³طھ ظ…ط­ط§ط³ط¨ظ‡ ط´ط¯ظ‡ ط§ط³طھ
	* 
	* @param int $staff_id : ط§ع¯ط± ط§غŒظ† ظ¾ط§ط±ط§ظ…طھط± ظپط±ط³طھط§ط¯ظ‡ ظ†ط´ظˆط¯ ط¨ط§غŒط¯ ع©ط¯ ظ¾ط±ط³ظ†ظ„غŒ ط­طھظ…ط§ ظپط±ط³طھط§ط¯ظ‡ ط´ظˆط¯
	* @param int $personID : ط§ع¯ط± ط§غŒظ† ظ¾ط§ط±ط§ظ…طھط± ظپط±ط³طھط§ط¯ظ‡ ظ†ط´ظˆط¯ ط¨ط§غŒط¯ ع©ط¯ ط´ظ†ط§ط³ط§غŒغŒ ط­طھظ…ط§ ظپط±ط³طھط§ط¯ظ‡ ط´ظˆط¯
	* @param MiladiDate $toDate : ط³ظ†ظˆط§طھ ط®ط¯ظ…طھ ط±ط§ طھط§ ط§غŒظ† طھط§ط±غŒط® ظ…ط´ط®طµ ظ…غŒ ع©ظ†ط¯
	* 
	* @return array{"year"=>'',"month"=>'',"day"=>''}
	*/
	public function Duty_duration($staff_id, $toDate)
	{
		return manage_writ::duty_year_month_day($staff_id,"",$toDate);
	}

	/**
	 *ع†ع© ظ…غŒ ع©ظ†ط¯ ط§غŒط§ ط§غŒظ† ط´ظ…ط§ط±ظ‡ ط´ظ†ط§ط³ط§غŒغŒ ظˆط¬ظˆط¯ ط¯ط§ط±ط¯ غŒط§ ط®غŒط±طں 
	* */
	static function check_exist_staff_id($staff_id) {
        
		$query = " SELECT   staff_id
    			     FROM     staff
				       WHERE    staff_id = $staff_id ";
		$tmp = PdoDataAccess::runquery($query);
		
		if(count($tmp)!=0)
			return true;
		else {
			parent::PushException("ط´ط®طµ ط¨ط§ ط´ظ…ط§ط±ظ‡ ط´ظ†ط§ط³ط§غŒغŒ".$staff_id."ظˆط¬ظˆط¯ ظ†ط¯ط§ط±ط¯.");
			return false;
		}			
			
	}

	/**
	 * ط§ط·ظ„ط§ط¹ط§طھ ط¢ط®ط±غŒظ† ط­ع©ظ… ظپط±ط¯ ط±ط§ ط¨ط± ظ…غŒ ع¯ط±ط¯ط§ظ†ط¯
	 *
	 * @param string $staff_id
	 * 
	 * @return manage_writ object
	 */
	public static function GetLastWrit($staff_id)
	{
		$query = "select * from writs 
			where staff_id=:stfid and (history_only != ".HISTORY_ONLY." OR history_only IS NULL)
			order by execute_date DESC,writ_id DESC,writ_ver DESC";
		$whereParam = array(":stfid"=> $staff_id);
		
		require_once '../../writs/class/writ.class.php';
		$obj = new manage_writ("");
		PdoDataAccess::FillObject($obj, $query, $whereParam);
		
		return $obj;
	}
	
	/**
	 * ط§ط·ظ„ط§ط¹ط§طھ ظ…ط±ط¨ظˆط· ط¨ظ‡ ط¢ط®ط±غŒظ† ط­ع©ظ… ظپط±ط¯ ط±ط§ ط¯ط± ط§ط·ظ„ط§ط¹ط§طھ ظپط±ط¯
	 * (staff ط¬ط¯ظˆظ„) 
	 * ظ‚ط±ط§ط± ظ…غŒ ط¯ظ‡ط¯
	 *
	 * @param string $staff_id
	 * 
	 */
	public static function SetStaffLastWrit($staff_id)
	{
		$lastWritObj = manage_writ::GetLastWrit($staff_id);
		/*@var $lastWritObj manage_writ*/

		$staffObj = new manage_staff();
		
		if(!empty($lastWritObj->writ_id))
		{
			$staffObj->staff_id 	 = $staff_id;
			$staffObj->last_writ_id  = $lastWritObj->writ_id;
	        $staffObj->last_writ_ver = $lastWritObj->writ_ver;
			$staffObj->post_id       = $lastWritObj->post_id;
	        $staffObj->ouid          = $lastWritObj->ouid;
			
	        return $staffObj->EditStaff();
		}
		else
		{
			$staffObj->staff_id 	 = $staff_id;
			$staffObj->last_writ_id  = PDONULL;
	        $staffObj->last_writ_ver = PDONULL;
			$staffObj->post_id       = PDONULL;
	        $staffObj->ouid          = PDONULL;
			$lastWritObj->cost_center_id = PDONULL;

			return $staffObj->EditStaff();
		}
	}

	/*
	** ط±ظˆط²ظ‡ط§ظٹ ع©ط§ط±ع©ط±ط¯ ط´ط®طµ ط±ط§ ط¯ط± ط·ظˆظ„ ط³ط§ظ„ ط§ط³طھط®ط±ط§ط¬ ظ…ظٹ ع©ظ†ط¯.
	*/
	static function compute_year_work_days($staff_id, $start_date, $end_date)
	{
	    //ط§ط­ع©ط§ظ… ظ…ظˆط¬ظˆط¯ ط¯ط± ط§ظٹظ† ط¨ط§ط²ظ‡ طھط§ط±ظٹط®ظٹ ط±ط§ ط§ط³طھط®ط±ط§ط¬ ظ…ظٹ ع©ظ†ط¯.
	    $query = "select wst.annual_effect,w.execute_date
	    			from writs w
	    				INNER JOIN writ_subtypes  wst
									  ON (w.writ_type_id = wst.writ_type_id AND 
									  	  w.writ_subtype_id = wst.writ_subtype_id)
						where execute_date >= '$start_date' AND execute_date <= '$end_date' AND
	                            
	                             (history_only !=".HISTORY_ONLY." OR history_only is null) AND
	                             staff_id = $staff_id
						order by execute_date";
		$writDT = PdoDataAccess::runquery($query);
						
	    $writ_duration = 0;
	    $duration = 0;
	    $prev_end_date = null;
	    
	    for($i=0; $i<count($writDT); $i++)
	    {
	    	if($i+1 < count($writDT))
	    	{
	    		$writ_duration = DateModules::getDateDiff(
	    			DateModules::Miladi_to_Shamsi($writDT[$i+1]["execute_date"]), 
	    			DateModules::Miladi_to_Shamsi($writDT[$i]["execute_date"]));
	    			
	    		if ($prev_end_date == $writDT[$i]['execute_date']) 
	            	$writ_duration--;
	            $prev_end_date = $writDT[$i+1]['execute_date'];
	    	}
	    	else 
	    	{
	    		$writ_duration = DateModules::getDateDiff(
	    			DateModules::Miladi_to_Shamsi($end_date), 
	    			DateModules::Miladi_to_Shamsi($writDT[$i]["execute_date"]));

	    		if ($prev_end_date == $writDT[$i]['execute_date']) 
	            	$writ_duration--;
	            //$prev_end_date = $writ_recSet[$key+1]['execute_date'];
	    	}
	    	
	    	switch ($writDT[$i]['annual_effect'])
	    	{
	    		case HALF_COMPUTED :
	                $writ_duration *= 0.5;
	    			break;
	    		case DOUBLE_COMPUTED :
	                $writ_duration *= 2;
	    			break;
	    		case NOT_COMPUTED :
	                $writ_duration = 0;
	    			break;
	    	}
	    	$duration += $writ_duration;
    	
	    }
	    $duration++;
	
	    return $duration;
	}
	 
	 static function Select($where, $whereParam)
	 {
	 	$query = "select * from staff
				where (last_cost_center_id is null OR last_cost_center_id in(" . manage_access::getValidCostCenters() . "))
					AND person_type in(" . manage_access::getValidPersonTypes() . ")";
	 	
	 	$query .= ($where != "") ? " AND " . $where : "";
	 	
	 	return PdoDataAccess::runquery($query,$whereParam);
	 }

     static function SelectWarningMsg($where, $whereParam)
	 {
       ( $whereParam["PT"] = HR_CONTRACT ) ? $where.= " AND warning_date >= '2010-03-21' " : "" ;
          
	 	 $query = " select p.pfname,p.plname,p.staff_id,p.full_title,p.writ_id,p.writ_ver,p.warning_date,p.warning_message , p.ouid
                                 from temp_messages p where ".$where ;
          
	 	$tmp = PdoDataAccess::runquery($query);
	
			for($i=0 ; $i < count($tmp) ; $i++ ){

				$tmp[$i]['full_unit_title'] = manage_units::get_full_title($tmp[$i]['ouid']);

			}

			return $tmp ; 
	 }
     
	static function Count($where, $whereParam)
	 { 
         
	 	$query = "select count(*) from staff
			 where (last_cost_center_id is null OR last_cost_center_id in(" . manage_access::getValidCostCenters() . "))
				AND person_type in(" . manage_access::getValidPersonTypes() . ")";
	 	
	 	$query .= ($where != "") ? " AND " . $where : "";
	 	
	 	$temp = PdoDataAccess::runquery($query,$whereParam);
	 	
	 	return $temp[0][0];
	 }

     static function CountWarningMsg($where, $whereParam)
	 {  
         $obj = new manage_staff();
         $obj->staff_messages();

         ( $whereParam["PT"] = HR_CONTRACT ) ? $where.= " AND warning_date >= '2010-03-21' " : "" ;

         $query = " select count(*) from temp_messages p where ".$where ;

	 	 $temp = PdoDataAccess::runquery($query ,  $whereParam);

	 	return $temp[0][0];
	 }
	 
	 static function SelectRetMsg($where, $whereParam)
	 {
         $retDate = DateModules::AddToGDate(DateModules::Now(),0,1) ; 
		 
         $query = " select p.personid , s.staff_id , p.pfname , p.plname , s.retired_date , bi.title retiredTitle

					from staff s inner join writs w
									on s.staff_id = w.staff_id and
										s.last_writ_id = w.writ_id and s.last_writ_ver = w.writ_ver and
										w.emp_mode != 13
										
								 inner join persons p
											on p.personid = s.personid

								 inner join Basic_Info bi
											on bi.typeid = 28 and bi.infoID = s.retired_state " ; 
		 
		$where .= " and s.retired_date < '".$retDate."' and
				  ( s.retired_date != '0000-00-00' or s.retired_date is null ) " ;

		$query .= $where ; 
		
	 	$temp = PdoDataAccess::runquery($query ,  $whereParam);
		
		return $temp ; 
	 }
	 
static function SelectTarfiMsg($where, $whereParam,$PT)
	 {


            list($eyear,$emonth,$eday) = preg_split('/[\/]/',DateModules::shNow());		 
              	$prevSdate = DateModules::shamsi_to_miladi(($eyear-1)."/01/01"); 
                $prevEdate = DateModules::shamsi_to_miladi(($eyear-1)."/12/29"); 
            $SDate = $eyear."/01/01" ;

            if( $emonth < 7 )
                $EDate = $eyear."/".$emonth."/31" ;
            if( $emonth > 6 && $emonth < 12 )
                $EDate = $eyear."/".$emonth."/30" ;
            if( $emonth == 12 )
                $EDate = $eyear."/".$emonth."/29" ;

    if( $emonth < 7 )
                $UpToDate = ( $eyear - 1 )."/".$emonth."/31" ;
            if( $emonth > 6 && $emonth < 12 )
                $UpToDate = ($eyear - 1 )."/".$emonth."/30" ;
            if( $emonth == 12 )
                $UpToDate = ( $eyear - 1) ."/".$emonth."/29" ;


            if($PT == 2 ) {
                $query = "  select s.staff_id , p.pfname , p.plname , p.military_duration , w.emp_state , s.work_start_date , t2.execute_date  UpgradeDate

                            from persons p inner join staff s
                                                on p.personid = s.personid and p.person_type = s.person_type
                                        inner join writs w on s.staff_id = w.staff_id and  s.last_writ_id = w.writ_id and s.last_writ_ver = w.writ_ver
                                        left join (
                                            select distinct staff_id
                                            from writs
                                            where execute_date >= '".DateModules::shamsi_to_miladi($SDate)."'  and  
                                                if( emp_state = 4 , (writ_type_id = 5 and writ_subtype_id = 145) ,
                                                if( emp_state = 3 , (writ_type_id = 4 and writ_subtype_id = 29) ,
                                                if(emp_state = 2 , (writ_type_id = 3 and writ_subtype_id = 91) , (0=1))))
                                                    ) t1 on s.staff_id = t1.staff_id

                                       left join (

 select  distinct staff_id , execute_date
                                                from writs
                                                where
                                                    execute_date >= '".$prevSdate."'  and
                                                    execute_date <= '".$prevEdate."'  and
                                                    if( emp_state = 4 , (writ_type_id = 5 and writ_subtype_id
 = 145) ,
                                                    if( emp_state = 3 , (writ_type_id = 4 and writ_subtype_id
 = 29) ,
                                                    if(emp_state = 2 , (writ_type_id = 3 and writ_subtype_id
 = 91) , (0=1))))

                               ) t2

                            on s.staff_id = t2.staff_id



                            where   CAST( substr(g2j(s.work_start_date),6,2) AS UNSIGNED )  <= ".$emonth." and 
                                    ( t2.execute_date <='".DateModules::shamsi_to_miladi($UpToDate)."' OR t2.execute_date IS NULL ) and p.person_type in (2) and 
                                    emp_mode in (1,2,3,4,5,6,10,15,16,17,18,19,26) and w.cost_center_id <> 46 and 
                                    t1.staff_id is null
                            " ; 

            }
            elseif ($PT == 5 ) {

                    $query = "  select s.staff_id , p.pfname , p.plname , p.military_duration , w.emp_state , s.work_start_date , t2.execute_date  UpgradeDate

                                from persons p inner join staff s
                                                    on p.personid = s.personid and p.person_type = s.person_type
                                            inner join writs w on s.staff_id = w.staff_id and  s.last_writ_id = w.writ_id and s.last_writ_ver = w.writ_ver
                                            left join (
                                                select distinct staff_id
                                                from writs
                                                where  
                                                    execute_date >= '".DateModules::shamsi_to_miladi($SDate)."'  and  
                                                    if( emp_state = 5 , (writ_type_id = 3 and writ_subtype_id = 60 ) , (0=1))
                                                     ) t1 on s.staff_id = t1.staff_id

 left join (

 select  distinct staff_id , execute_date
                                                from writs
                                                where
                                                    execute_date >= '".$prevSdate."'  and
                                                    execute_date <= '".$prevEdate."'  and
                                                    if( emp_state = 5 , (writ_type_id = 3 and writ_subtype_id = 60 ) , (0=1))

                               ) t2

                            on s.staff_id = t2.staff_id


                                where    CAST( substr(g2j(s.work_start_date),6,2) AS UNSIGNED )  <= ".$emonth." and
                        ( t2.execute_date <='".DateModules::shamsi_to_miladi($UpToDate)."' OR t2.execute_date IS NULL ) and p.person_type in (5) and 
                                        emp_mode in (1,2,3,4,5,6,10,15,16,17,18,19,26) and cost_center_id <> 46 and 
                                        t1.staff_id is null
                                " ; 

            } 
         

            $temp = PdoDataAccess::runquery($query ,  $whereParam);

           /* for($i=0;$i<count($temp);$i++) 
            {
                $shTDate = DateModules::miladi_to_shamsi($temp[$i]['work_start_date']) ; 
                list($ey,$em,$ed) = preg_split('/[\/]/',$shTDate);
                $temp[$i]['UpDate'] = $eyear."/".$em."/".$ed; 

            }*/

            return $temp ; 
	 }
	 static function CountRetMsg($where, $whereParam)
	 {  		  
		 $retDate = DateModules::AddToGDate(DateModules::Now(),0,1) ; 
		 
         $query = " select count(*)

					from staff s inner join writs w
									on s.staff_id = w.staff_id and
										s.last_writ_id = w.writ_id and s.last_writ_ver = w.writ_ver and
										w.emp_mode != 13
										
								 inner join persons p
											on p.personid = s.personid

								 inner join Basic_Info bi
											on bi.typeid = 28 and bi.infoID = s.retired_state " ; 
		 
		$where .= " and s.retired_date < '".$retDate."' and
				  ( s.retired_date != '0000-00-00' or s.retired_date is null ) " ;

	 	$temp = PdoDataAccess::runquery($query ,  $whereParam);
				
	 	return $temp[0][0];
	 }

static function CountTarfiMsg($where, $whereParam, $PT )
	 {  		

  
                list($eyear,$emonth,$eday) = preg_split('/[\/]/',DateModules::shNow());		 
             	$prevSdate = DateModules::shamsi_to_miladi(($eyear-1)."/01/01"); 
                $prevEdate = DateModules::shamsi_to_miladi(($eyear-1)."/12/29");  
                $SDate = $eyear."/01/01" ;
                
                if( $emonth < 7 )
                    $EDate = $eyear."/".$emonth."/31" ;
                if( $emonth > 6 && $emonth < 12 )
                    $EDate = $eyear."/".$emonth."/30" ;
                if( $emonth == 12 )
                    $EDate = $eyear."/".$emonth."/29" ;
if( $emonth < 7 )
                $UpToDate = ( $eyear - 1 )."/".$emonth."/31" ;
            if( $emonth > 6 && $emonth < 12 )
                $UpToDate = ($eyear - 1 )."/".$emonth."/30" ;
            if( $emonth == 12 )
                $UpToDate = ( $eyear - 1) ."/".$emonth."/29" ;

                
                if($PT == 2 ) {
                    $query = "  select count(*)

                                from persons p inner join staff s
                                                    on p.personid = s.personid and p.person_type = s.person_type
                                            inner join writs w on s.staff_id = w.staff_id and  s.last_writ_id = w.writ_id and s.last_writ_ver = w.writ_ver
                                            left join (
                                                select distinct staff_id
                                                from writs
                                                where  
                                                    execute_date >= '".DateModules::shamsi_to_miladi($SDate)."'  and  
                                                    if( emp_state = 4 , (writ_type_id = 5 and writ_subtype_id = 145) ,
                                                    if( emp_state = 3 , (writ_type_id = 4 and writ_subtype_id = 29) ,
                                                    if(emp_state = 2 , (writ_type_id = 3 and writ_subtype_id = 91) , (0=1))))
                                                     ) t1 on s.staff_id = t1.staff_id

 left join (

 select  distinct staff_id , execute_date
                                                from writs
                                                where
                                                    execute_date >= '".$prevSdate."'  and
                                                    execute_date <= '".$prevEdate."'  and
                                                    if( emp_state = 4 , (writ_type_id = 5 and writ_subtype_id
 = 145) ,
                                                    if( emp_state = 3 , (writ_type_id = 4 and writ_subtype_id
 = 29) ,
                                                    if(emp_state = 2 , (writ_type_id = 3 and writ_subtype_id
 = 91) , (0=1))))

                               ) t2

                            on s.staff_id = t2.staff_id


                                where    CAST( substr(g2j(s.work_start_date),6,2) AS UNSIGNED )  <= ".$emonth." and
                        ( t2.execute_date <='".DateModules::shamsi_to_miladi($UpToDate)."' OR t2.execute_date IS NULL ) and p.person_type in (2) and 
                                        emp_mode in (1,2,3,4,5,6,10,15,16,17,18,19,26) and cost_center_id <> 46 and 
                                        t1.staff_id is null
                                " ; 


                }
		elseif ($PT == 5 ) {
                    
                      $query = "  select count(*)

                                from persons p inner join staff s
                                                    on p.personid = s.personid and p.person_type = s.person_type
                                            inner join writs w on s.staff_id = w.staff_id and  s.last_writ_id = w.writ_id and s.last_writ_ver = w.writ_ver
                                            left join (
                                                select distinct staff_id
                                                from writs
                                                where  
                                                    execute_date >= '".DateModules::shamsi_to_miladi($SDate)."'  and  
                                                    if( emp_state = 5 , (writ_type_id = 3 and writ_subtype_id = 60 ) , (0=1))
                                                     ) t1 on s.staff_id = t1.staff_id

 left join (

 select  distinct staff_id , execute_date
                                                from writs
                                                where
                                                    execute_date >= '".$prevSdate."'  and
                                                    execute_date <= '".$prevEdate."'  and
                                                    if( emp_state = 5 , (writ_type_id = 3 and writ_subtype_id = 60 ) , (0=1))

                               ) t2

                            on s.staff_id = t2.staff_id


                                where    CAST( substr(g2j(s.work_start_date),6,2) AS UNSIGNED )  <= ".$emonth." and
                        ( t2.execute_date <='".DateModules::shamsi_to_miladi($UpToDate)."' OR t2.execute_date IS NULL ) and p.person_type in (5) and 
                                        emp_mode in (1,2,3,4,5,6,10,15,16,17,18,19,26) and cost_center_id <> 46 and 
                                        t1.staff_id is null
                                " ; 
                
                } 
		
	 	$temp = PdoDataAccess::runquery($query ,  $whereParam);
				
	 	return $temp[0][0];
	 }
         
    static function SelectEsMsg($where = "", $whereParam = array())
	 {  
                        
            $dateArr = preg_split("/\//",DateModules::shNow()); 

            $month = (($dateArr[1] - 1) == 0 ) ? "12" : ($dateArr[1] - 1) ; 
            $startDate = DateModules::shamsi_to_miladi($dateArr[0]."/".$month."/01") ; 
            
            if($dateArr[1]<7)
                $endDate = DateModules::shamsi_to_miladi($dateArr[0]."/".$dateArr[1]."/31") ; 
            else if($dateArr[1]<12)
                $endDate = DateModules::shamsi_to_miladi($dateArr[0]."/".$dateArr[1]."/30") ; 
            else if($dateArr[1] == 12)
                $endDate = DateModules::shamsi_to_miladi($dateArr[0]."/".$dateArr[1]."/29") ; 
            
            $query = " select s.staff_id , p.pfname , p.plname , w.ouid ,w.writ_id , w.writ_ver , w.issue_date ,bi.Title emp_state_title , w.execute_date ,w.state
                                from persons p  inner join staff s 
                                                        on p.personid = s.personid
                                                inner join writs w
                                                        on s.staff_id = w.staff_id
                                                inner join org_new_units o 
                                                        on w.ouid = o.ouid 
                                                inner join Basic_Info bi 
                                                       on bi.InfoID = w.emp_state and TypeID = 3
                                                
                            where  w.issue_date >='".$startDate."'  and  w.issue_date <= '".$endDate."' and 
                                   w.emp_mode = 21 and w.emp_state not in (3,4) and w.state <> 1 and w.execute_date <='".$endDate."'" ;

            
            $temp = PdoDataAccess::runquery($query ,  $whereParam);

            for($i=0 ; $i < count($temp) ; $i++ ){

                        $temp[$i]['full_unit_title'] = manage_units::get_full_title($temp[$i]['ouid']);

                }

            return $temp;
	 }
	 
	 static function change_Retired_Pay()
	 {              
                       
		$query = "  select s.staff_id , p.pfname , p.plname , w.ouid , w.state

					from staff s inner join writs w
									on  s.staff_id = w.staff_id and
										s.last_writ_id = w.writ_id and
										s.last_writ_ver = w.writ_ver

								left join staff_include_history sih
									on sih.staff_id = s.staff_id and sih.service_include = 1 and
										( sih.end_date is null or sih.end_date = '0000-00-00')

								inner join persons p
									on s.personid = p.personid

					where s.person_type = 1 and PayRet = 1 and sih.staff_id is null
				" ;


		$temp = PdoDataAccess::runquery($query);

		for($i=0 ; $i < count($temp) ; $i++ ){

					$temp[$i]['full_unit_title'] = manage_units::get_full_title($temp[$i]['ouid']);

			}

		return $temp;
	 }

     function ISEmptyPTypeInFrameWork()
     {
            $query = " select PersonType from AccountSpecs where personid = :PID ";
            
            $whereParam = array(":PID"=> $this->PersonID);
            $temp = parent::runquery($query,$whereParam);
 
            return   $temp[0]['PersonType'] ;
     }

     function UpdatePtyInFrameWork()
     { 
            if($this->last_person_type == HR_EMPLOYEE )
                $pt = 'STAFF';
            if($this->last_person_type == HR_PROFESSOR)
                $pt = 'PROF';

            $query = " update AccountSpecs set PersonType = '".$pt."'  where personid = ".$this->PersonID ;
          
            return   parent::runquery($query); 
     }
     
     static function CountICMsg($where, $whereParam)
	 {
         
         $query = ' select count(*)
                     from person_dependents pd
       						 INNER JOIN persons p
                             	ON (pd.PersonID = p.PersonID)
       						 INNER JOIN staff s
                             	ON (p.PersonID = s.PersonID) AND p.person_type = s.person_type   
                             INNER JOIN writs w
                                        ON (w.writ_id = s.last_writ_id AND w.writ_ver = s.last_writ_ver)
                             INNER JOIN person_dependent_supports pds
                                       ON pds.PersonID = pd.PersonID AND pds.master_row_no = pd.row_no
                             INNER JOIN Basic_Info BI
                                       ON BI.InfoID = pd.dependency and BI.TypeID = 1
                             INNER JOIN Basic_Info BI2
                                       ON BI2.InfoID = pds.insure_type and BI2.TypeID = 30

                    where to_date BETWEEN DATE(SUBDATE(NOW(),7)) AND DATE(ADDDATE(NOW(),7)) AND pds.insure_type <> 3 AND
                          w.emp_mode NOT IN ('.EMP_MODE_PERMANENT_BREAK.','.EMP_MODE_BREAKAWAY.','.
					       	   EMP_MODE_RUSTICATION.','.EMP_MODE_RETIRE.','.
					       	   EMP_MODE_RE_BUY.') AND '.$where  ;
	 
	 	 $temp = PdoDataAccess::runquery($query ,  $whereParam);

	 	return $temp[0][0];
	 }
     
     static function SelectICMsg($where, $whereParam)
	 {                 
	 	$query = " select  pd.PersonID,
                           pd.row_no,
                           concat(pd.fname ,' ', pd.lname ) dname ,
                           concat(p.pfname ,' ', p.plname) pname ,
                           BI.Title dependency_title ,
                           pd.idcard_no,
                                               pd.birth_date,
                                                   pds.from_date ,
                                                   pds.to_date ,
                                                   pd.father_name,
                                                   s.staff_id ,
                           BI2.Title  insure_type

                    from person_dependents pd
       						 INNER JOIN persons p
                             	ON (pd.PersonID = p.PersonID)
       						 INNER JOIN staff s
                             	ON (p.PersonID = s.PersonID) AND p.person_type = s.person_type 
                            INNER JOIN writs w
                                        ON (w.writ_id = s.last_writ_id AND w.writ_ver = s.last_writ_ver)
                            INNER JOIN person_dependent_supports pds
                                       ON pds.PersonID = pd.PersonID AND pds.master_row_no = pd.row_no
                            INNER JOIN Basic_Info BI
                                       ON BI.InfoID = pd.dependency and BI.TypeID = 1
                            INNER JOIN Basic_Info BI2
                               ON BI2.InfoID = pds.insure_type and BI2.TypeID = 30

                    where to_date BETWEEN SUBDATE(NOW(),7) AND ADDDATE(NOW(),7) AND pds.insure_type <> 3 AND
                          w.emp_mode NOT IN (".EMP_MODE_PERMANENT_BREAK.",".EMP_MODE_BREAKAWAY.",".
                                               EMP_MODE_RUSTICATION.",".EMP_MODE_RETIRE.",".
					       	                   EMP_MODE_RE_BUY.") AND ".$where ;
              
	 	return PdoDataAccess::runquery($query);
	 }


     static function CounteglMsg($where, $whereParam)
	 {

         $query = ' select count(*)
                        FROM persons p INNER JOIN staff s
                                            ON (p.PersonID = s.PersonID )
                                       LEFT OUTER JOIN writs w
                                            ON ((s.last_writ_id = w.writ_id) AND
                                                (s.last_writ_ver = w.writ_ver))
                                       LEFT OUTER JOIN org_new_units o
                                            ON (w.ouid = o.ouid)
                        WHERE ( s.person_type = '.HR_EMPLOYEE.' and w.emp_mode IN ('.EMP_MODE_PRACTITIONER.') AND
                                                CASE WHEN (20 + w.onduty_year * 365.25 + w.`onduty_month` * 30.4375 + w.`onduty_day` +
                                                          (CASE w.annual_effect WHEN 1 THEN 1 WHEN 2 THEN 0.5
                                                     WHEN 3 THEN 0 WHEN 4 THEN 2 ELSE 0 END) * DATEDIFF(CURDATE(),w.execute_date)) MOD
                                                                                                        (CASE WHEN w.education_level<400 THEN 5 ELSE 4 END * 365.25)
                                                                                                             BETWEEN 0 AND 30 THEN 1 ELSE 0 END = 1) AND
                                                                                (( cost_center_id IN (9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,46,47,90,99,100,101 ) OR
                                            cost_center_id IS NULL) and '.$where.')' ;
         
	 	 $temp = PdoDataAccess::runquery($query ,  $whereParam);

	 	return $temp[0][0];
	 }

      static function SelecteglMsg($where, $whereParam)
	 {
	 	$query = " select      p.PersonID,
                               p.pfname,
                               p.plname,
                               p.efname,
                               p.elname,
                               p.father_name,
                               p.idcard_no,
                               p.birth_date,
                               p.national_code ,
                               s.staff_id ,
                               w.ouid,
                               concat(if(o4.ptitle is null , ' ' ,o4.ptitle ),' ',
                                      if(o3.ptitle is null , ' ' ,o3.ptitle ) ,' ',
                                      if(o2.ptitle is null , ' ' ,o2.ptitle ) ,' ',
                                      if(o.ptitle is null , ' ' ,o.ptitle ) ) total_org_unit_title
                            
                     FROM persons p INNER JOIN staff s
                                            ON (p.PersonID = s.PersonID )
                                       LEFT OUTER JOIN writs w
                                            ON ((s.last_writ_id = w.writ_id) AND
                                                (s.last_writ_ver = w.writ_ver))
                                       LEFT OUTER JOIN org_new_units o
                                            ON (w.ouid = o.ouid)
                                       LEFT JOIN org_new_units o2
                                            ON o.parent_ouid = o2.ouid
                                       LEFT JOIN org_new_units o3
                                            ON o2.parent_ouid = o3.ouid
                                       LEFT JOIN org_new_units o4
                                            ON o3.parent_ouid = o4.ouid

                        WHERE ( s.person_type = ".HR_EMPLOYEE." and w.emp_mode IN (".EMP_MODE_PRACTITIONER.") AND
                                                CASE WHEN (20 + w.onduty_year * 365.25 + w.`onduty_month` * 30.4375 + w.`onduty_day` +
                                                          (CASE w.annual_effect WHEN 1 THEN 1 WHEN 2 THEN 0.5
                                                     WHEN 3 THEN 0 WHEN 4 THEN 2 ELSE 0 END) * DATEDIFF(CURDATE(),w.execute_date)) MOD
                                                                                                        (CASE WHEN w.education_level<400 THEN 5 ELSE 4 END * 365.25)
                                                                                                             BETWEEN 0 AND 30 THEN 1 ELSE 0 END = 1) AND
                                                                                (( cost_center_id IN (9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,46,47,90,99,100,101 ) OR
                                            cost_center_id IS NULL) and ".$where.")" ;


	 	return PdoDataAccess::runquery($query);
	 }
     
     function staff_messages ()
     {       
           $current_date = date('Y-m-d');
           $next_wheek_date = DateModules::AddToGDate($current_date,7) ;

           $query = ' DROP TABLE IF EXISTS temp_cw; ' ;
           PdoDataAccess::runquery($query);
           
          

           $query = " DROP TABLE IF EXISTS temp_cwsi; " ;
           PdoDataAccess::runquery($query);

           $query = " DROP TABLE IF EXISTS temp_messages; " ;
           PdoDataAccess::runquery($query);

           $query = " CREATE TABLE temp_cw TYPE=MyISAM as
                        SELECT writ_id,
                               writ_ver,
                               w.warning_date,
                               w.warning_message,
                               staff_id , w.ouid
                        FROM writs w
                        WHERE ( w.remembered != ".REMEMBERED." OR w.remembered is null ) AND
                                w.warning_date < '".$next_wheek_date."'  AND  w.warning_date <> '0000-00-00' " ;
		 
           PdoDataAccess::runquery($query);

           $query = ' CREATE TABLE temp_cwsi TYPE=MyISAM as
                        SELECT wsi.writ_id,
                               wsi.writ_ver,
                               wsi.salary_item_type_id,
                               wsi.remember_date,
                               wsi.remember_message,
                               w.staff_id , w.ouid
                        FROM writ_salary_items wsi
                            INNER JOIN  writs w
                                ON(wsi.writ_id = w.writ_id AND wsi.writ_ver = w.writ_ver AND wsi.staff_id = w.staff_id )
                        WHERE    ( wsi.remembered != '.REMEMBERED.' OR w.remembered is null ) AND
                                 wsi.remember_date < \''.$next_wheek_date.'\' AND wsi.salary_item_type_id NOT IN (10232 , 10233 ) ';
		  
         PdoDataAccess::runquery($query);
         
         $query = " CREATE TABLE temp_messages TYPE=MyISAM as
        			   (SELECT   p.pfname,
						         p.plname,
                                 p.person_type,
						         s.staff_id,
						         NULL AS full_title,
						         w.writ_id,
						         w.writ_ver,
						         w.warning_date,
						         w.warning_message,w.ouid

						FROM     temp_cw w
						         INNER  JOIN staff s
						  	          ON (w.staff_id = s.staff_id)
						         INNER JOIN persons p
						  	          ON (s.PersonID = p.PersonID))
                               
						UNION ALL
						(SELECT  p.pfname,
						         p.plname,
                                 p.person_type,
						         s.staff_id,
						         sit.full_title,
						         wsi.writ_id,
						         wsi.writ_ver,
						         wsi.remember_date warning_date,
						         wsi.remember_message warning_message , wsi.ouid

						FROM     temp_cwsi wsi
						         INNER JOIN salary_item_types sit
						  	          ON (wsi.salary_item_type_id = sit.salary_item_type_id)
						         INNER JOIN staff s
						  	          ON (wsi.staff_id = s.staff_id)
						         INNER JOIN persons p
						  	          ON (s.PersonID = p.PersonID))
                                
						ORDER BY   	plname,pfname,writ_id
                        ";
		                 
         PdoDataAccess::runquery($query);  
       return true ;
     }
     
	static function remove($personID = "", $staff_id = "", $pdo = null)
	{
		if($personID != "")
		{
			$whereParam = array(":pid"=> $personID);
			$return = PdoDataAccess::delete("HRM_staff", "PersonID=:pid", $whereParam, $pdo);
			
			if(!$return)
				return false;

			$daObj = new DataAudit();
			$daObj->ActionType = DataAudit::Action_delete;
			$daObj->MainObjectID = $personID;
			$daObj->TableName = "HRM_staff";
			$daObj->execute(); 				
						
		}
		else if($staff_id != "")
		{
			$whereParam = array(":staff_id"=> $staff_id);
			$return = PdoDataAccess::delete("staff_details","staff_id=:staff_id", $whereParam, $pdo);
			if(!$return)
				return false;
			$return = PdoDataAccess::delete("staff","staff_id=:staff_id", $whereParam, $pdo);
			if(!$return)
				return false;

			$daObj = new DataAudit();
			$daObj->ActionType = DataAudit::Action_delete;
			$daObj->MainObjectID = $staff_id;
			$daObj->TableName = "staff";
			$daObj->execute();

			return true;
		}
		else
			return false;

		return true;
	 }
	 
	 public static function LastID($pdo = "")
	 {
	 	return PdoDataAccess::GetLastID("HRM_staff", "staff_id", "", array());
	 }

     static function Create_New_Staff($personid , $persontype )
     {
         $obj = new manage_staff($personid, $persontype);

         if($persontype == 3 || $persontype == 5 )
            $obj->person_type = 2 ;
         else if ( $persontype == 2 )
            $obj->person_type = 1 ;

         $old_staff_id = $obj->staff_id ; 
         
         $ret = $obj->AddStaff();
		 
         if ($ret == true) {
						
             //--------------------------------------------------
             $qry = " select * from staff_tax_history where staff_id=".$old_staff_id." AND payed_tax_value IS NOT NULL "; 
             $tmp = PdoDataAccess::runquery($qry);
            
             if(count($tmp) > 0 )
                {
                 $Newobj = new manage_staff_tax($old_staff_id);
                 parent::FillObjectByArray($Newobj, $tmp[0]) ; 
                 
                 $Newobj->staff_id = $obj->staff_id ; 
                 $Newobj->tax_history_id = (parent::GetLastID('staff_tax_history','tax_history_id') + 1) ; 
                 
                 if( PdoDataAccess::insert("staff_tax_history", $Newobj) === false )
					 return false;                      
                }
             //................. آزاد کردن پست سازمانی شماره شناسایی قبلی ...................
			 
			 $qry = " update position set staff_id= null where staff_id = ".$old_staff_id ; 
			 PdoDataAccess::runquery($qry);
			 
			//..................................................
                         
             $query = " update persons set person_type = ". $obj->person_type ." where  personid =".$obj->PersonID ;
              PdoDataAccess::runquery($query);
             if(ExceptionHandler::GetExceptionCount() == 0 ){
                 return $obj->staff_id ;
             }
			
			 
         }

         return false ; 
                     
     }
	 

}

?>