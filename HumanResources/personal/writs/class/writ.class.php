<?php
//---------------------------
// programmer:	Mahdipour
// create Date: 94.11
//---------------------------

require_once  $address_prefix . "/HumanResources/personal/writs/class/writ_item.class.php";
require_once 'writ_subtype.class.php';
require_once $address_prefix . '/HumanResources/personal/staff/class/staff.class.php';
require_once $address_prefix . '/HumanResources/organization/positions/post.class.php';
require_once $address_prefix . '/HumanResources/personal/persons/class/dependent.class.php';
require_once $address_prefix . '/HumanResources/personal/persons/class/person.class.php';


class manage_writ extends PdoDataAccess
{
    public $writ_id;
    public $writ_ver;
    public $corrective;
    public $corrective_writ_id;
    public $corrective_writ_ver;
    public $corrective_date;
    public $staff_id;
    public $post_id;
    public $job_id;
    public $cost_center_id;
    public $ouid;
    public $sub_ouid;
    public $writ_type_id;
    public $writ_subtype_id;
    public $person_type;
    public $emp_state;
    public $emp_mode;
    public $worktime_type;
    public $ref_letter_no;
    public $ref_letter_date;
    //public $send_letter_no;
    public $send_letter_date;
    public $issue_date;
    public $execute_date;
    public $pay_date;
    public $contract_start_date;
    public $contract_end_date;
    public $education_level;
    public $science_level;
    public $cur_group;  
    public $description;
    public $children_count;
    public $included_children_count;
    public $marital_status;
    public $family_responsible;
    public $onduty_year;
    public $onduty_day;
    public $onduty_month;
    public $military_type;
    public $sbid;
    public $sfid;
    public $annual_effect;
    public $military_status;        
    public $history_only;
    public $warning_date;
    public $warning_message;   
    public $salary_pay_proc;
    //public $work_state_id;
    public $remembered;
   // public $notes;
    public $state;
    public $writ_signature_post_title;
    public $writ_signature_post_owner;
    public $dont_transfer;
    public $correct_completed;
    public $hortative_group;
    
    public $writ_transfer_date;   
    public $MissionPlace ; 
	public $CostCenterID ; 
	public $PayRet ; 

	private function onBeforeDelete()
	{
		if($this->writ_id == "")

            return false;
	
		//.................................

		$useInPayCalc = manage_writ::check_for_use_in_pay_calc($this->writ_id, $this->writ_ver, $this->staff_id);
		if($useInPayCalc != null || $this->state != WRIT_PERSONAL)
		{ 
			parent::PushException(WRIT_CAN_NOT_DELETE);
			return false;
		}

		if($this->check_corrective_state() == "NOT_CORRECTING" || $this->writ_has_new_version())
		{
			parent::PushException(ER_CANNT_DELETE_CORREDTED_WRIT);
			return false;
		}

		if(self::is_new_writ($this->execute_date) && !self::is_last($this->staff_id,$this->execute_date) &&
				$this->check_corrective_state() != "CORRECTING")
		{
			parent::PushException(ER_CANNT_DELETE_NEW_WRITS);
			return false;
		}

		$query = "select * from writs where corrective_writ_id=:wid AND corrective_writ_ver=:wver AND staff_id = :stid";
		$temp = PdoDataAccess::runquery($query, array(":wid" => $this->writ_id, ":wver" => $this->writ_ver, ":stid" => $this->staff_id));
		if(count($temp) > 0)
		{
			parent::PushException(ER_CONSTRAINT);
			return false;
		}

        return true;

    }

    private function onBeforeUpdate()
	{
		if(!$this->check_send_letter_no())
		{
			return false ;
		}

		if(HRSystem == 'PersonalSystem' && $this->person_type != HR_PROFESSOR )
		{
			if( $this->onduty_day < 0 || $this->onduty_day > 29 )
			{
				parent::PushException(ERR_ONDUTY_DAY_VALUE );
    			return false ;
			}
			if( $this->onduty_month < 0 || $this->onduty_month > 11 )
			{
				parent::PushException(ERR_ONDUTY_MONTH_VALUE );
    			return false ;
			}
		}
		//در صورتي كه واحد اصلي محل خدمت فرد تغيير كند مركز هزينه خالي خواهد شد تا واحد مالي
        //ملزم به تعيين مركز هزينه گردد
		if( HRSystem == 'PersonalSystem' )
		{
			$pre_writ_rec = $this->get_prior_writ();
			if($pre_writ_rec->ouid != $this->ouid )
			{
				$this->cost_center_id = null ;
			}
		}

		if($this->person_type == HR_PROFESSOR || $this->person_type == HR_EMPLOYEE )
		{
			if($this->post_id > 0)
			{
				$lastWritObj = self::GetLastWrit($this->staff_id);
				if($lastWritObj->writ_id == "" || $lastWritObj->execute_date <= $this->execute_date)
					if(!manage_posts::is_valid($this->post_id, $this->execute_date, $this->staff_id))
						return false;

                //---- چنانچه پست فرد را تغییر دهند ---------

               /* $prior_writ_Obj = $this->get_prior_writ();
                if($prior_writ_Obj->post_id != $this->post_id)
                {
                   if (manage_posts::change_user_post($prior_writ_Obj->staff_id, $prior_writ_Obj->post_id , NULL , $prior_writ_Obj->execute_date) === false )
                       return false ;
                }*/

			}
		}
	

		if( $this->pay_date < $this->issue_date )
		{
			if($this->state == WRIT_PERSONAL )
			   $this->pay_date = $this->issue_date ;
			else
			{
				parent::PushException(WRIT_PAY_DATE_MUST_BE_GREATHER_OR_EQUAL_ISSUE_DATE ) ;
				return false ;
			}
		}

		if(isset($_POST['contract_start_date']))
		   $this->contract_start_date = DateModules::Shamsi_to_Miladi($_POST['contract_start_date']);

		if(isset($_POST['contract_end_date']))
		   $this->contract_end_date = DateModules::Shamsi_to_Miladi($_POST['contract_end_date']);
		
                	//--------بررسی مجدد تیک مربوط به سرپرست خانواده ----------------------------


if($this->family_responsible == 0 ) 
		{ 

			$query = "select family_responsible from HRM_writs
						where staff_id=:stfid and writ_id =:WRID and writ_ver =:WRVER  ";
			
			$whereParam = array(":stfid" => $this->staff_id ,":WRID" => $this->writ_id,":WRVER" => $this->writ_ver);
			$resResponsible = PdoDataAccess::runquery($query, $whereParam); 
			$this->family_responsible = $resResponsible[0]['family_responsible'];
}
		
		return true ;
	}

	private function onBeforeInsert()
	{
		/*if($this->check_send_letter_no() === false )
			return false ; */ 

		$query = "
			select w.writ_id, w.writ_ver , w.emp_mode , w.execute_date
			from HRM_writs w
				inner join HRM_staff s on(w.writ_id=s.last_writ_id and w.writ_ver=s.last_writ_ver AND s.staff_id=w.staff_id)
                   where s.staff_id=".$this->staff_id ;
		$temp = parent::runquery($query);

		if(count($temp) != 0)
		{
			if( $temp[0]['emp_mode'] == EMP_MODE_RUSTICATION )
			{
				parent::PushException(NOT_ALLOW_ERR_RUSTICATION && $this->execute_date > $temp[0]['execute_date'] ) ;
				return false ;
			}

			if( $temp[0]['emp_mode'] == EMP_MODE_RE_BUY && $this->execute_date > $temp[0]['execute_date'])
			{
				parent::PushException(NOT_ALLOW_ERR_RE_BUY ) ;
				return false ;
			}
		}

		if(empty($this->emp_mode) || $this->emp_mode == 0)
		   $this->emp_mode = 1;

		if($this->writ_id == '')
	 	   $this->writ_id = parent::GetLastID('writs','writ_id');

		if(empty($this->issue_date))
		   $this->issue_date = $this->execute_date ;

		if(empty($this->pay_date))
		   $this->pay_date = $this->execute_date ;

		if(empty($this->onduty_year))
		  $this->onduty_year = 0 ;

		if(empty($this->onduty_month))
		  $this->onduty_month = 0 ;

		if(empty($this->onduty_day))
		  $this->onduty_day = 0 ;
                
                //----------------- new ------------
                if( $this->pay_date < $this->issue_date )
		{
			parent::PushException(WRIT_PAY_DATE_MUST_BE_GREATHER_OR_EQUAL_ISSUE_DATE ) ;
				return false ;
		}
                //------------------------
		if($this->person_type == HR_PROFESSOR || $this->person_type == HR_EMPLOYEE )
		{
			if($this->post_id > 0)
			{
				$lastWritObj = self::GetLastWrit($this->staff_id);
				if($lastWritObj->writ_id == "" || $lastWritObj->execute_date <= $this->execute_date)
					if(!manage_posts::is_valid($this->post_id, $this->execute_date, $this->staff_id))
						return false;
			}
		}
		
		 //  احکام ديگري که در اين روز صادر شده غيرفعال مي کند
		if( $this->history_only != 1 ) {
			if($this->writ_enter_state(WRIT_PERSONAL,$this->staff_id,$this->execute_date,$this->writ_id,$this->writ_ver) === false )
			    return false ;
		}
			
		return true ;

	}

	private function onAfterInsert()
	{
	   
        $empty_onduty_fields =  $this->onduty_year == 0 &&
        						$this->onduty_month == 0 &&
        						$this->onduty_day == 0;

        
        if ($empty_onduty_fields )
        {
        	if($empty_onduty_fields)
        	{
				$duty_duration = $this->duty_year_month_day($this->staff_id, "", $this->execute_date);

	            $this->onduty_year  = $duty_duration['year'];
	            $this->onduty_month = $duty_duration['month'];
	            $this->onduty_day   = $duty_duration['day'];
            }
       
			if( parent::update("HRM_writs", $this, "writ_id=:wid AND writ_ver=:wver AND staff_id=:stid",
				array(":wid" => $this->writ_id, ":wver" => $this->writ_ver, ":stid" => $this->staff_id)) === false )
				
				 return false ;
        }
  
		//__________________________________________________________ bahareeee  
		if( manage_staff::SetStaffLastWrit($this->staff_id) === false )
       	   return false ;
	 	//__________________________________________________________
        return true;
	}

    private function onAfterUpdate()
    {
	$lastWritObj = manage_writ::GetLastWrit($this->staff_id);
        $prior_writ_Obj = $this->get_prior_writ();

		if($lastWritObj->writ_id == $this->writ_id && $lastWritObj->writ_ver == $this->writ_ver)
		{
			//---- چنانچه پست فرد را تغییر دهند ---------

			if($prior_writ_Obj->post_id != $this->post_id)
			{
			   if (manage_posts::change_user_post($this->staff_id, $prior_writ_Obj->post_id , $this->post_id, $this->execute_date) === false)
				   return false ;
			}
			//__________________________________________________________
			if( manage_staff::SetStaffLastWrit($this->staff_id) === false )
			   return false ;
		}
        /*elseif($prior_writ_Obj->post_id != $this->post_id)
        {

            $query = "update position set staff_id=null where post_id=" . $prior_writ_Obj->post_id." and staff_id = ".$this->staff_id ;
	    		if( parent::runquery($query) === false )
	    		    return false ;

        }*/
		return true ;
    }

     /* _______________________________________________________________

					data members and functions
	_______________________________________________________________*/
    
    private function computeBaseValue()
    {
	
	if($this->execute_date > '2013-03-20'){
	    
	     $query =  " SELECT staff_id,
                               SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
                               SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver
                         FROM (
				SELECT  staff_id,
					max( CONCAT(execute_date,writ_id,'.',writ_ver) ) max_execute_date

				from writs
				
				where staff_id = ".$this->staff_id." and
				      execute_date < '2013-03-21'
				      
				) t1 
				
			  WHERE staff_id = ".$this->staff_id ; 
	     
	     $wd = parent::runquery($query);	     
	    	     
	     if(count($wd) > 0)
	     {		 
		 $qry = " select onduty_year 
				from writs 
				    where staff_id =".$wd[0]['staff_id']." and writ_id=".$wd[0]['writ_id']." and writ_ver=".$wd[0]['writ_ver'] ; 
		 
		 $bdt = parent::runquery($qry);
				
		 $dutyYears = ($bdt[0]['onduty_year']== 0 ) ? 1 : $bdt[0]['onduty_year'] ;	
				 
	     }	    
	    else 
	    {
		 $dutyYears =  1 ; 
	    }
	    
	    
	    
	    $query = " select sum(BaseValue) total			   
			from bases join staff using(PersonID)
			     where BaseStatus = 'NORMAL' AND ExecuteDate <= '".$this->execute_date."' AND 
				   BaseType in (3,4,5,6 ,7)  AND staff_id =" . $this->staff_id;
	
	    $dt = parent::runquery($query); 
	    
	    //...................
	    $qry = " select sum(BaseValue) total,
			    sum(if(BaseType=3 , BaseValue , 0 )) Azadegi , sum(if(BaseType=4 , BaseValue , 0 )) janbazi , sum(if(BaseType=5 , BaseValue , 0 )) jebhe  ,
			    sum(if(BaseType=1,ExtraInfo,0)) sarbaziMonths
		     from bases join staff using(PersonID)
		     where BaseStatus = 'NORMAL' AND ExecuteDate <= '".$this->execute_date."' AND staff_id=" . $this->staff_id;		
	    
	    $res = parent::runquery($qry);
	    
	   
	    $minScore = 0 ; 
	    if( count($res) > 0 && $res[0]["Azadegi"] > 0 && $res[0]["janbazi"] > 0 && $res[0]["jebhe"] > 0  )
	    {
		
		$minScore = min($res[0]["Azadegi"], $res[0]["janbazi"] , $res[0]["jebhe"]); 
		
	    }	    
	   
	    //...................
	    
	    if(count($dt) == 0)
	    { 
		$this->base = $dutyYears ;
		return;
	    }
	    else 
		$this->base = $dutyYears + $dt[0]["total"] - $minScore ; 
	    	   	    
	}
	else {
	    
	    $query = " select sum(BaseValue) total			   
			    from bases join staff using(PersonID)
				where BaseStatus = 'NORMAL' AND ExecuteDate <= '".$this->execute_date."' AND 
				    BaseType = 6  AND staff_id =" . $this->staff_id;

	    $dt = parent::runquery($query);

	    if(count($dt) == 0)
	    { 
		$this->base = ($this->onduty_year == 0 ) ? 1 : $this->onduty_year ;
		return;
	    }

	    $dutyYears = ($this->onduty_year == 0 ) ? 1 : $this->onduty_year  ;

	    $this->base = $dutyYears + $dt[0]["total"] ; 
	
	}		 
	
	return;		    

	  /*  if(count($dt) == 0)
	    {
		    $this->base = $this->onduty_year;
		    return;
	    }

	    $dutyYears = $this->onduty_year;

	    if($dt[0]["sarbaziMonths"] != "" && $dt[0]["sarbaziMonths"] != "0")
	    {
		    // مدت سربازی از سابقه کار کم شده و خدمت خالص بدست می آید.
		    $dutyDays = $this->onduty_day + 30.4375*$this->onduty_month + 365.25*$this->onduty_year;
		    $sarbaziDays = 30.4375*$dt[0]["sarbaziMonths"];
		    $dutyYears = round(($dutyDays - $sarbaziDays)/365.25,2);
		    $dutyYears = ($dutyYears < 0) ? 0 : floor($dutyYears);
	    }
	    // با توجه به اینکه فرد فقط می تواند از مجموع ترفیعات فقط دوستون از جدول پایه های مربوط به ایثارگری استفاده کند 
	    $minScore = 0 ; 
	    if($dt[0]["Azadegi"] > 0 && $dt[0]["janbazi"] > 0 && $dt[0]["jebhe"] > 0  )
	    {
		
		$minScore = min($dt[0]["Azadegi"], $dt[0]["janbazi"] , $dt[0]["jebhe"]); 
		
	    }	    
	    
	    $this->base = $dutyYears + $dt[0]["total"] - $minScore ;
	    	    	    	    
	    return; */
    }

    private function writ_enter_state($new_state,$staff_id,$execute_date,$writ_id=null,$writ_ver=null) {

			if(!$execute_date){
				$query = "  SELECT staff_id , execute_date
							FROM HRM_writs
							WHERE writ_id =$writ_id AND writ_ver = $writ_ver AND staff_id = $staff_id
							";
				$temp = PdoDataAccess::runquery($query);
				$execute_date = $temp[0]['execute_date'];
			}

			if($writ_id && $writ_ver)
				$add_where = " AND (writ_id<>$writ_id OR writ_ver<>$writ_ver)";

			// غير فعال کردن نسخه قبلي حکم

			$query = "  UPDATE HRM_writs
						SET history_only=1
						WHERE 	staff_id = ".$staff_id." AND
								(history_only=0 OR history_only IS NULL)
								AND execute_date='".$execute_date."'
								AND state=".$new_state.$add_where ;

		   return PdoDataAccess::runquery($query);

		}

   // چک کردن شماره دبيرخانه - شماره دبيرخانه نبايد در يک سال تکراري باشد
	public function check_send_letter_no() {
                           
		if ($this->send_letter_no == PDONULL ||  empty($this->send_letter_no)  )
			return true;

            $send_letter_year = substr(DateModules::Miladi_to_Shamsi($this->send_letter_date),0,4);
	    $jthis_year_first_day = '01/01/'.$send_letter_year;
	    $jthis_year_last_day  = '29/12/'.$send_letter_year;

	    $Gthis_year_first_day = DateModules::Shamsi_to_Miladi($jthis_year_first_day);
	    $Gone_year_ago_last_day_writ = DateModules::Shamsi_to_Miladi($jthis_year_last_day);

	    $query = " select writ_id , writ_ver ,send_letter_no
	               from HRM_writs
		       where  send_letter_no = ".$this->send_letter_no." AND
						issue_date >= '".$Gthis_year_first_day."' AND
						issue_date<='".$Gone_year_ago_last_day_writ."' AND 
						send_letter_date >= '".$Gthis_year_first_day."' AND
						send_letter_date <='".$Gone_year_ago_last_day_writ."' AND    
						(writ_id <> ".$this->writ_id." ) AND
						(if(corrective!=1,history_only != 1,(1=1)) OR history_only IS NULL)  "; 

            
	    $temp = parent::runquery($query);
	   	   	    
            if(count($temp) != 0)
            {
    		parent::PushException(strtr(ER_SEND_LETTER_NO_IS_REAPEATED,
                                      array("%0%" => $temp[0]["send_letter_no"], "%1%" => $temp[0]["writ_id"])));

    		return false ;
            }
	    else {
	    	return true ;
	    }

	}

	/**
	 * اگر تابع را بدون ورودي فراخواني کنيد يک شي خالي برمي گرداند
	 * اگر فقط شماره حکم را بفرستيد شي را با آخرين نسخه آن حکم پر مي کند
	 * اگر هم شماره حکم و هم نسخه حکم را به تابع بفرستيد شي مربوط به آن را برمي گرداند
	 *
	 * @param string $writ_id
	 * @param string $writ_ver
	 *
	 */
	function __construct($writ_id="", $writ_ver = "" , $staffID = "" )
	{

        if($writ_id != "")
	 	{
	 		if(empty($writ_ver))
	 		{
		 		 $query = " SELECT writ_id,writ_ver max_writ_ver
	                		FROM HRM_writs
	                		WHERE  writ_id = :wid and staff_id=:stid
	                		ORDER BY writ_ver desc ";

		 		 $whereParam = array(":wid" => $writ_id, ":stid" => $staffID);

		 		 $temp = parent::runquery($query, $whereParam);
		 		 $this->writ_ver = $temp[0]['max_writ_ver'];
	 		}

	 		$writ_ver = ($writ_ver == "") ? $this->writ_ver :  $writ_ver;

	 	    $query = "select * from HRM_writs where writ_id=:wid and writ_ver=:wver and staff_id =:sid " ;

	 		parent::FillObject($this, $query, array(":wid"=> $writ_id, ":wver"=> $writ_ver , ":sid"=> $staffID));



	 	}
//  این قسمت در زمان ثبت در دیتا بیس مورد استفاده قرار می گیرد چون تاریخ است بررسی میکند و ان را میلادی می کند و در دیتا بیس ذخیره می نماید.

        $this->DT_corrective_date = DataMember::CreateDMA(DataMember::DT_DATE);
        $this->DT_ref_letter_date = DataMember::CreateDMA(DataMember::DT_DATE);
        $this->DT_send_letter_date = DataMember::CreateDMA(DataMember::DT_DATE);
        $this->DT_issue_date = DataMember::CreateDMA(DataMember::DT_DATE);
        $this->DT_execute_date = DataMember::CreateDMA(DataMember::DT_DATE);
        $this->DT_pay_date = DataMember::CreateDMA(DataMember::DT_DATE);
        $this->DT_contract_start_date = DataMember::CreateDMA(DataMember::DT_DATE);
        $this->DT_contract_end_date = DataMember::CreateDMA(DataMember::DT_DATE);
        $this->DT_warning_date = DataMember::CreateDMA(DataMember::DT_DATE);

	 }

	function EditWrit()
	{
	 	if(!$this->onBeforeUpdate())
	 		return false ;


	 	$whereParams = array(":WID" => $this->writ_id , ":WVER" => $this->writ_ver, ":SID" => $this->staff_id);

	 	if(!parent::update("writs",$this," writ_id=:WID and writ_ver=:WVER and staff_id=:SID ", $whereParams))
                    return false ;
                

        if(!$this->onAfterUpdate())
	 		return false ;

        return true ;

	}

	/**
	 * مشخص مي کند که آیا آخرین حکم فرد قبل و یا در این تاریخ مي باشد يا خير
	 *
	 * @return boolean
	 */
    static function is_last($staff_id, $execute_date)
    {
    	$obj = manage_writ::GetLastWrit($staff_id);

		if(empty($obj->writ_id) || $obj->execute_date <= $execute_date)
			return true;

		return false;
    }
    /*
        * استخراج اطلاعات مربوط به حکم در سالهای مختلف
    */
    static function get_writs_info($staff_id , $from_j_year , $to_j_year){
            $query = "  SELECT
                             s.staff_id ,
                             w.cur_group ,
                             w.execute_date ,
                             SUM(CASE WHEN wsi.salary_item_type_id = ".SIT2_BASE_SALARY." THEN wsi.value ELSE 0 END) base_salary ,
                             SUM(CASE WHEN wsi.salary_item_type_id = ".SIT_STAFF_JOB_EXTRA." THEN wsi.param1 ELSE 0 END) job_coef ,
                             SUM(CASE WHEN wsi.salary_item_type_id = ".SIT_STAFF_DOMINANT_JOB_EXTRA." THEN wsi.param1 ELSE 0 END)  dominant_job_coef
                        FROM staff s
                             INNER JOIN persons p
                                ON p.personID = s.personID
                             INNER JOIN writs w
                                ON w.staff_id = s.staff_id
                             LEFT OUTER JOIN writ_salary_items wsi
                                ON wsi.writ_id = w.writ_id AND
                                   wsi.writ_ver = w.writ_ver AND
                                   wsi.staff_id = w.staff_id
                        WHERE s.staff_id = $staff_id AND w.history_only != 1
                        GROUP BY
                                 s.staff_id ,
                                 w.cur_group ,
                                 w.execute_date ,
                                 w.writ_id ,
                                 w.writ_ver
                        ORDER BY execute_date " ;
            $res = parent::runquery($query);

            $writs_info = array() ;

	if(count($res) > 0 ){

		for($j=0 ; $j < count($res); $j++){
			$cur_j_date = '01/01/'.$from_j_year;
			$next_j_date = '01/01/'.($from_j_year+1);
			$cur_g_date = str_replace("/","-",DateModules::Shamsi_to_Miladi($cur_j_date));
			$next_g_year = str_replace("/","-",DateModules::Shamsi_to_Miladi($next_j_date));

			$tmp_from_j_year = $from_j_year ;

			while(!($res[$j]['execute_date']>= $cur_g_date &&
                    $res[$j]['execute_date'] < $next_g_year) &&
                    $from_j_year <= $to_j_year)
                    {
                        $from_j_year ++ ;
                        $cur_j_date = '01/01/'.$from_j_year;
                        $next_j_date = '01/01/'.($from_j_year+1);
                        $cur_g_date = str_replace("/","-",DateModules::Shamsi_to_Miladi($cur_j_date));
                        $next_g_year = str_replace("/","-",DateModules::Shamsi_to_Miladi($next_j_date));
                    }

			while($from_j_year <= $to_j_year)
			{
				$writs_info[$from_j_year]['cur_group'] = $res[$j]['cur_group'];
				$writs_info[$from_j_year]['base_salary'] = $res[$j]['base_salary'];
				$writs_info[$from_j_year]['job_coef'] = $res[$j]['job_coef'];
				$writs_info[$from_j_year]['dominant_job_coef'] = $res[$j]['dominant_job_coef'];
				$from_j_year ++ ;
				$cur_j_date = '01/01/'.$from_j_year;
				$cur_g_date = str_replace("/","-",DateModules::Shamsi_to_Miladi($cur_j_date));
			}
			$from_j_year = $tmp_from_j_year ;

		}
	}
	return $writs_info ;
}


	/**
     *  اين تابع اطلاعات يک حکم قبل از اين حکم را برميگرداند
     *
     * @return manage_writ Object
	*/
	function get_prior_writ($writ_rec = "" ,$item = "" , $date = "" )
	{
		
		$wdate = "" ;
			    	    
		if(!empty($writ_rec))
		{
			if($writ_rec['person_type'] != 1 )
				if($date != "") $wdate = " AND execute_date < '".$date."' " ; 
			
			$corrective_writ_id = $writ_rec["corrective_writ_id"];
			$corrective_writ_ver = $writ_rec["corrective_writ_ver"];
			$writ_id = $writ_rec["writ_id"];
			$writ_ver = $writ_rec["writ_ver"];
			$staff_id = $writ_rec["staff_id"];
			$execute_date = $writ_rec["execute_date"];
		}
		else
		{
			if($this->person_type != 1 )
				if($date != "") $wdate = " AND execute_date < '".$date."' " ; 
				
			$corrective_writ_id = $this->corrective_writ_id;
			$corrective_writ_ver = $this->corrective_writ_ver;
			$writ_id = $this->writ_id;
			$writ_ver = $this->writ_ver;
			$staff_id = $this->staff_id;
			$execute_date = $this->execute_date;
		}

		if ($corrective_writ_id && $corrective_writ_ver)
		{
	        $dec_writ_ver = $writ_ver - 1;
	        //کنترل اينکه حکم با يک نگارش کمتر وجود دارد يا خير؟
	    	$this_writ_rec = manage_writ::get_writ_rec($writ_id, $dec_writ_ver , $staff_id );

	    	if ($this_writ_rec)
	    	{
	    		$prior_writ_ver = $writ_ver - 1;
	    		$query = "select *
	    		          from HRM_writs
	    		          where  staff_id = " . $staff_id . " AND
                                 writ_id = " . $writ_id . "   AND
                                 writ_ver = $prior_writ_ver     AND
                                 execute_date <= '" . $execute_date . "'
                          order by execute_date DESC,writ_id DESC,writ_ver DESC
                          limit 0,1 ";

	        }
	        else
	        {
	        	$query = " select *
	        	           from HRM_writs
	        	           where staff_id = " . $staff_id . "  AND
	                             execute_date <= '" . $execute_date . "'  AND
		                        (writ_id <>".$writ_id . " OR writ_ver <>" . $writ_ver . ") AND
	                            (writ_id <".$writ_id . " OR execute_date < '".$execute_date."') AND
	                            (history_only <> ".HISTORY_ONLY." OR history_only IS NULL  )
	                       order by execute_date DESC,writ_id DESC,writ_ver DESC
	                       limit 0,1 " ;
	        }
	    }
	    else
	    {
		    	$query = " select *
		    	           from HRM_writs
		    	           where  staff_id = ".$staff_id." AND
		                          execute_date <= '".$execute_date."'  AND
		                         (history_only <> ".HISTORY_ONLY." OR history_only IS NULL ) AND
		                         (writ_id <>".$writ_id." OR writ_ver <>".$writ_ver.") AND
		                         (writ_id <".$writ_id." OR execute_date < '".$execute_date."') 
		    	           order by execute_date DESC,writ_id DESC,writ_ver DESC ";

		}

        $obj = new manage_writ();
        PdoDataAccess::FillObject($obj, $query);
	//.........................جهت بررسی اینکه آیا حکم شامل اقلام حقوقی می باشد یا خیر ؟
	
	if($item==true) {	    
	    $qry = "select count(*) cnItem  
			    from HRM_writ_salary_items 
				where writ_id = ".$writ_id." and writ_ver = ".$writ_ver." and staff_id = ".$staff_id  ;
	    $resCnt = PdoDataAccess::runquery($qry) ; 
	    
	    if($resCnt[0]['cnItem'] > 0 )
		return $resCnt[0]['cnItem'] ; 
	    else		
		return 0 ;
	}
	//.......................
        return $obj;
	
	}

	/**
	 * تنها با استفاده از اين تابع مي توان حکم جديد صادر کرد
	 * @return boolean : قرار مي دهد  ExceptionHandler اگر صدور حکم با موفقيت انجام نشود توضيح خطا را در
	 */
	private function IssueWritAction($indiv=NULL)
	{
		$temp = parent::runquery("select last_writ_id,last_writ_ver,staff_id,PersonID,person_type from HRM_staff
   											where staff_id=" . $this->staff_id." and person_type =". $this->person_type );
		
			

		if (count($temp) == 0)
		{
	    	parent::PushException(ERROR_STAFF_ID_NOT_FOUND);
	    	return false;
		}
   		$staff_rec = $temp[0];
   		$PersonID = $staff_rec['PersonID'];
		
		
       //___________________________________________________________
		//در صورتي که روال ناتمام اصلاح وجود دارد
		if($this->correct_is_not_completed($this->staff_id)){


			parent::PushException(ERROR_CORRECT_IS_NOT_COMPLETED);
	    	return false;
	    }
		

	    //___________________________________________________________
	    //در صورتي که حکم خودکار است کنترلهاي زير اعمال شود
		$is_auto = $this->is_auto_writ($this->execute_date, $this->person_type);

   		if($is_auto)
   		{
   		 			//___________________________________________________________
   
	$lastWritObj = manage_writ::GetLastWrit($this->staff_id); 			
			
			$writ_subtype_obj = new manage_writ_subType($this->person_type, $this->writ_type_id, $this->writ_subtype_id);
					
             //.........................................................................................
			if( ($this->person_type == 5 || $writ_subtype_obj->emp_state == 2  ) && $this->contract_start_date!=NULL && 
				(substr(DateModules::miladi_to_shamsi($this->execute_date),0,4) != substr(DateModules::miladi_to_shamsi($this->contract_start_date),0,4) ))
			{   
					parent::PushException(ER_CONTRACT_DATE);
					return false;                    
			}
        	   
             //.........................................................................................
			//..... در صورتی که فرد بازنشسته باشد ولی آزاده باشد امکان صدور حکم برای فرد میسر می باشد ................
				//.... جانباز نیز بهمین صورت...................................................................
			$Azadegi = manage_person_devotion::get_person_devotions($PersonID,"(2,3)") ;

			//...................................................................................................
   			 //کنترل مي کند که در صورتي که شخص بازنشسته شده باشد براي او حکمي صادر نشود.
   			if(!empty($lastWritObj->writ_id) && $lastWritObj->execute_date < $this->execute_date && !$this->history_only)
   			{
   				if (( ($lastWritObj->emp_mode == EMP_MODE_RETIRE && $Azadegi[0]['amount'] <= 0 )
		    		|| $lastWritObj->emp_mode == EMP_MODE_RE_BUY
		    		|| $lastWritObj->emp_mode == EMP_MODE_RUSTICATION
		    		|| $lastWritObj->emp_mode == EMP_MODE_PERMANENT_BREAK
		    		|| $lastWritObj->emp_mode == EMP_MODE_BREAKAWAY
		    		|| ($lastWritObj->emp_mode == EMP_MODE_CONVEYANCE && $writ_subtype_obj->emp_mode != 16 ) ) && $this->corrective_writ_id == PDONULL   )
		    	{
		        	parent::PushException(ERROR_FOR_RETIRED_PERSONS_CAN_NOT_ISSUE_WRIT);
		            return false;
		        }
   			}
			
		    //___________________________________________________________
   		}
		if (!empty($lastWritObj->writ_id))
		{   
			
			parent::FillObjectByObject($lastWritObj,$this);
			
			//--------بررسی مجدد تیک مربوط به سرپرست خانواده ----------------------------
			$query = "select family_responsible from HRM_writs
						where staff_id=:stfid and (history_only != ".HISTORY_ONLY." OR history_only IS NULL)
							order by execute_date DESC,writ_id DESC,writ_ver DESC ";
			
			$whereParam = array(":stfid" => $this->staff_id);
			$resResponsible = parent::runquery($query, $whereParam); 
			$this->family_responsible = $resResponsible[0]['family_responsible'];
			//--------------------------------------------------------------------------
			//اگر حكم قبلي در محاسبه حقوق استفاده شده باشد و يا در وضعيت مياني باشد
	        //اين حكم كه اطلاعات حكم قبلي را كپي مي كند نبايد مقدار وضعيت آن را نيز كپي كند
	        $this->state = 1;


	        if( (!$this->corrective_writ_id && !$this->corrective_writ_ver) ||
                ( $this->corrective_writ_id == PDONULL && $this->corrective_writ_ver  == PDONULL ) ){
	        	  $this->correct_completed = 0;
	        }
	        //___________________________________________________________
			//در صورتي که حکم يکي از شرايط زير را داشته باشد مي تواند بدون
	    	// رعايت توالي تاريخي صادر شود :
	   		// خودکار نباشد - حکم اصلاحي و يا در حال اصلاح باشد - حکم فقط ثبت سابقه باشد
			if($this->is_new_writ($this->execute_date, $this->person_type) &&
	   			!$this->corrective && $this->corrective_writ_id == PDONULL && !$this->history_only)
	        {
				
               if ($this->execute_date < $lastWritObj->execute_date && $indiv == NULL )
	            {
                    parent::PushException(EXECUTE_DATE_OF_NORMAL_WRIT_CANT_BEFORE_LAST_ONE_ERR);
	                return false;
	            }
	        }
			//__________________________________________________________
	        // محاسبه سنوات خدمت فرد
	        $duty_duration = $this->duty_year_month_day($this->staff_id,"", $this->execute_date);

	        $this->onduty_year   = !empty($duty_duration['year']) ? 	$duty_duration['year'] 	: 0;
	        $this->onduty_month  = !empty($duty_duration['month']) ? $duty_duration['month'] : 0;
	        $this->onduty_day    = !empty($duty_duration['day']) ? 	$duty_duration['day'] 	: 0;

	      /*  if( $this->person_type != HR_CONTRACT ){
		        $related_duty_duration = $this->related_duty_years($this->staff_id, $this->execute_date, $this->post_id, "INSERT");
		        $this->related_onduty_year   = !empty($related_duty_duration['year']) ? $related_duty_duration['year'] : 0;
		        $this->related_onduty_month  = !empty($related_duty_duration['month']) ? $related_duty_duration['month'] : 0;
		        $this->related_onduty_day    = !empty($related_duty_duration['day']) ? $related_duty_duration['day'] : 0;
	        } */	
			
			if ($this->person_type == HR_EMPLOYEE || $this->person_type == HR_CONTRACT )
			{
				/*if($lastWritObj->execute_date > '2014-03-20')
				   $this->base = $lastWritObj->base ; 
				else {*/
				   
					$Pqry = " select sex , military_duration_day ,military_duration  
									from HRM_persons p inner join HRM_staff s on p.personid = s.personid 
											where s.staff_id=".$this->staff_id ; 
					$Pres = parent::runquery($Pqry) ; 
					if($Pres[0]["sex"] == 1 &&  $this->person_type == 2 && ($Pres[0]["military_duration_day"] > 0 || $Pres[0]["military_duration"] > 0 ) )
					{
						$totalDayWrt = DateModules::ymd_to_days($this->onduty_year, $this->onduty_month , $this->onduty_day ) ; 			
						$totalDaySar = DateModules::ymd_to_days(0, $Pres[0]["military_duration"], $Pres[0]["military_duration_day"]) ; 					
						$resDay = $totalDayWrt -  $totalDaySar  ; 

						$Vyear = 0 ; 
						$Vmonth = $Vday = 0 ; 
						DateModules::day_to_ymd($resDay, $Vyear, $Vmonth, $Vday) ; 
						$Vyear =  $Vyear ; 

					}						
					else { 		
													
							/*$totalDayWrt = DateModules::ymd_to_days($this->onduty_year, $this->onduty_month , $this->onduty_day ) ; 							

							$diffYear = DateModules::getDateDiff(DateModules::Now(),'2014-03-21');

							$remainDay = $totalDayWrt - $diffYear ; 

							DateModules::day_to_ymd($remainDay, $Ryear, $Rmonth, $Rday) ; 
							$Vyear = $Ryear  ;*/
						
							$Vyear =  $this->onduty_year ;   
						
						
						}
					
					$this->base =  $Vyear + 1 ; 
					
				   /*}*/
			}

        if( $this->person_type == HR_WORKER )
        {
		   $qry = " select job_id
					from HRM_writs
					where execute_date < '".$this->execute_date."' and staff_id = ".$this->staff_id."
					order by execute_date  Desc
					limit 1 " ; 
		   $resJob = PdoDataAccess::runquery($qry); 
		   		   
        	   $this->job_id = $resJob[0]['job_id'] ;
        }
					
			//__________________________________________________________
		}
		else
		{
			
	       //___________________________________________________________
	        $this->onduty_year  = 0;
	        $this->onduty_month = 0;
	        $this->onduty_day   = 0;

	       /* $this->related_onduty_year   = 0;
	        $this->related_onduty_month  = 0;
	        $this->related_onduty_day    = 0; */ 
			//$this->grade = 1 ; 
			
	        $this->family_responsible = 0;
	      
        	$this->job_id   = PDONULL;
			
	    			
	    }
	    //___________________________________________________________
        // محاسبه اطلاعات مربوط به آخرين مدرک تحصيلي فرد

        $education_level_rec = manage_person_education::GetEducationLevelByDate($PersonID, $this->execute_date,$is_auto);
        if ($education_level_rec === false)
        {
        	//در صورتي که حکم دستي است بدون مدرک مي توان حکم را ثبت نمود
        	if($is_auto)
        	{
        		return false;
        	}
        	else
        	{
        		// در صورتي که حکم دستي و براي فرد مدرک تحصيلي مشخص نشده ، تحصيلات بيسواد ثبت مي شود
        		$education_level_rec['max_education_level'] = '101';
			}
        }


        $this->education_level	= ($education_level_rec != 101 ) ? $education_level_rec['max_education_level'] : '101' ;

        $this->sfid				= isset($education_level_rec['sfid']) ? $education_level_rec['sfid'] : "";
        $this->sbid				= isset($education_level_rec['sbid']) ? $education_level_rec['sbid'] : "";

        if( $this->person_type == HR_CONTRACT)
        {
        	//$this->job_id = 1111;
        }
	    //__________________________________________________________
		// محاسبه تعداد فرزندان

        $where = "PersonID=" . $PersonID . "
				  AND (dependency = 5 or dependency = 6)
				  AND birth_date <='" . $this->execute_date . "'";
		$no = manage_person_dependency::CountDependency($where);
        $this->children_count = $no;
        //__________________________________________________________
        // محاسبه افراد تحت تکفل فرد
        $this->included_children_count = manage_person_dependency::bail_count($PersonID,$this->person_type,$this->execute_date, $this->execute_date);
        //__________________________________________________________
        // تعيين وضعيت تاهل و ايثارگري
        $person_obj = new manage_person($PersonID);

        $this->marital_status  = $person_obj->marital_status;

        $this->military_status  = $person_obj->military_status;
        $this->military_type  = $person_obj->military_type;
	    //__________________________________________________________

	    $this->writ_ver = 1;
		if ($this->corrective == true)
		{
	    	$this->corrective = 1;
	    	$this->description = "";
	    }
	    else if ($this->corrective_writ_id == PDONULL && $this->corrective_writ_ver == PDONULL )
	    {
	    	$this->corrective   = 0;
            $this->corrective_writ_id   = PDONULL;
            $this->corrective_writ_ver  = PDONULL;
	    	$this->corrective_date      = '0000-00-00';//PDONULL;
	    }


	    $this->send_letter_date = $this->issue_date ;
	    $this->pay_date			= ($this->execute_date > $this->issue_date) ? $this->execute_date : $this->issue_date;
		$this->ref_letter_no = (!empty($lastWritObj->ref_letter_no) ) ? $lastWritObj->ref_letter_no : PDONULL ;
	    //$this->ref_letter_date = (!empty($lastWritObj->ref_letter_date) ) ?  $lastWritObj->ref_letter_date : PDONULL ;
		
		 //__________________________________________________________
	    //جايگزيني مقادير مربوط به نوع اصلي و فرعي حکم

	    if ($writ_subtype_obj->time_limited != 1)
	    {
	    	$this->contract_start_date  = '0000-00-00';PDONULL;
	    	$this->contract_end_date    = '0000-00-00';//PDONULL;
	    }

	    if ($writ_subtype_obj->salary_pay_proc > 0)
	    	$this->salary_pay_proc    = $writ_subtype_obj->salary_pay_proc;

	    if ($writ_subtype_obj->annual_effect > 0)
	    	$this->annual_effect  = $writ_subtype_obj->annual_effect;

	    if ($writ_subtype_obj->emp_state > 0)
	    	$this->emp_state       = $writ_subtype_obj->emp_state;

	    if ($writ_subtype_obj->emp_mode > 0)
	    	$this->emp_mode        = $writ_subtype_obj->emp_mode;

	    if ($writ_subtype_obj->worktime_type > 0)
	    	$this->worktime_type   = $writ_subtype_obj->worktime_type;

	    if ($writ_subtype_obj->post_effect == FREE_POST_EFFECT)
	        $this->post_id = PDONULL;

	    if (!$writ_subtype_obj->remember_distance)
	    	$writ_subtype_obj->remember_distance = 0;

	    if($writ_subtype_obj->remember_distance > 0)
	    	$this->warning_date    = DateModules::AddToGDate($this->execute_date, 0, $writ_subtype_obj->remember_distance);
	    else
	    	$this->warning_date = '0000-00-00' ;//PDONULL;

	    $this->warning_message = $writ_subtype_obj->remember_message;
	    $this->description     = $writ_subtype_obj->comments;
	   

	    if ($this->corrective == true || $this->corrective == 1 )

            $this->description =  PDONULL;
			$this->writ_signature_post_owner = ($this->issue_date > '2014-02-01') ? ' مهدی پور' : WRIT_SIGNATURE_POST_OWNER ;
			
			
		
		$this->writ_signature_post_title = WRIT_SIGNATURE_POST_TITLE ;
		
		
	    // براي افراد پيماني هر حکم جديد قرارداد جديد مي باشد .
	    // بنابراين تاريخ شروع قرارداد تاريخ اجراي حکم خواهد بود .
	    // تاريخ خاتمه قرارداد پرسنل قراردادي پايان سال خواهد بود .
	    if($writ_subtype_obj->time_limited == 1 && (
	    			$this->emp_state == EMP_STATE_CONTRACTUAL ||
	    			$this->emp_state == EMP_STATE_SOLDIER_CONTRACTUAL ||
	    			$this->emp_state == EMP_STATE_ONUS_SOLDIER_CONTRACTUAL))

	    {
	    	$this->contract_start_date = $this->execute_date;
			$arr = preg_split('/\//',DateModules::Miladi_to_Shamsi($this->execute_date));
			$Jdate = $arr[0] . "/12/29";
	    	$this->contract_end_date = DateModules::Shamsi_to_Miladi($Jdate);
            }
		//__________________________________________________________

		$this->remembered    = PDONULL;
		$this->dont_transfer = PDONULL ;
	    if ($this->history_only  && ($this->state == WRIT_PERSONAL || $this->state == PDONULL))
	    	$this->history_only  = 1;
	    else
	    	$this->history_only = 0;

	//__________________________________________________________
		$this->job_id = (empty($this->job_id)) ? PDONULL : $this->job_id;

        //---------------------
	$pObj = new manage_person("", $this->staff_id ) ; 
	
	if($pObj->sex == 2 && $pObj->marital_status == 1 )  
	   $this->family_responsible = 0 ;  	
	
	if($pObj->sex == 1 && ( $this->person_type == 3 || $this->person_type == 5 ) && $this->marital_status == 2 ) 
	    $this->family_responsible = 1 ; 
	
	if($pObj->sex == 2 && (  $this->person_type == 5 ) && $this->execute_date > '2014-03-20' ) 
	    $this->family_responsible = 0 ;
	        
	    //.............................................
	    $pdo = parent::getPdoObject();
	    /*@var $pdo PDO*/

	    $pdo->beginTransaction();

	    $this->writ_id  = manage_writ::LastID() + 1;

	    if(empty($this->writ_id))
	    {
	    	parent::PushException("خطاي کد آخرين رکورد");
	    	$pdo->rollBack();
	    	return false;
	    }
	    
	    if(!$this->onBeforeInsert()){	
		
	        $pdo->rollBack();
	    	return false ;
	    } 
	
	    $return = parent::insert("HRM_writs", $this);
	
	    if(!$return)
	    {
	    	parent::PushException("ايجاد با شکست مواجه شد");
	    	$pdo->rollBack();
	    	return false;
	    }

	    $this->onAfterInsert();
	
	    $pdo->commit();
		
	    return true;
	}

	/**
	 * مشخص کردن امضا کننده حکم
	 *
	 */
	private function set_writ_sign_info()
	{
		$post = manage_variables::get_variable_info("post_id1", $this->person_type);
		$owner_record = manage_posts::get_post_owner2($post, date('Y-m-d'), true);
       //print_r(ExceptionHandler::PopAllExceptions());
		//echo $post ."----post"; die();
		//در صورتي که پست در اختيار خود فرد است
		if($owner_record["staff_id"] == $this->staff_id)
		{
			// پست دوم را بخوان
	    	$post_record = manage_variables::get_variable_info("post_id2", $this->person_type);
		    $owner_record = manage_posts::get_post_owner2($post, date('Y-m-d'), true);
		}

		$this->writ_signature_post_owner = $owner_record["name"];
		$this->writ_signature_post_title = manage_posts::get_post_title($post);
	}

	/**
	 * از تاريخ شروع اصلاح ، صدور حکم اصلاحي را شروع مي کند.
	 *
	 * @param unknown_type $staff_id
	 * @param unknown_type $corrective_date
	 * @param unknown_type $writ_rec
	 * @param unknown_type $writ_type_id
	 * @param unknown_type $writ_subtype_id
	 * @param unknown_type $base
	 * @param unknown_type $send_letter_no
	 * @param unknown_type $issue_date
	 * @param unknown_type $base_writ_issue
	 * @return unknown
	 */
	public function CorrectiveIssueAction($base_writ_issue)
	{
		$stobj = new manage_staff("", "", $this->staff_id);

		if ($stobj->staff_id == null || $stobj->staff_id == "" )
		{
	    	parent::PushException(ERROR_STAFF_ID_NOT_FOUND);
	    	return false;
	    }
   		$staff_rec = $stobj;
   		$PersonID = $staff_rec->PersonID ;
   		$this->person_type = $staff_rec->person_type;

		//ابتدا چک مي شود که در تاريخ شروع اصلاح حکمي وجود دارد يا خير ؟
		$exist_writ_rec = manage_writ::Is_Writ_For_Correct($this->staff_id,$this->corrective_date);

		// چنانچه حکمي نباشد و صدور حکم پايه نيز تيک نخورده باشد خطا مي دهد.
		if($exist_writ_rec == NULL && !$base_writ_issue)
		{
			parent::PushException("در تاريخ شروع اصلاح حکم وجود ندارد .");
			return false;
		}
		//صدور حکم پايه
		else if ($base_writ_issue)
		{
			$exist_writ_rec = manage_writ::IssueWrit(   $this->staff_id,
												   	    $this->writ_type_id,
												   		$this->writ_subtype_id,
												   		$this->corrective_date,
                                                        $this->person_type,
												   		$this->issue_date,
												   		false,
												  		false ,
												   		$this->send_letter_no,
												  		$this->writ_id,
												   		$this->writ_ver,
												   		$this->base
												   		);
			if($exist_writ_rec != false )
			{
				manage_writ_item::compute_writ_items($exist_writ_rec->writ_id , $exist_writ_rec->writ_ver,
					$exist_writ_rec->staff_id);

                return $exist_writ_rec ;
				/*header("location: ../ui/view_writ.php?WID=" . $exist_writ_rec->writ_id .
					"&WVER=" . $exist_writ_rec->writ_ver . "&STID=" . $exist_writ_rec->staff_id);
				die();*/
			}

            return $exist_writ_rec ;

		}
		//صدور اولين حکم اصلاحي
		elseif ($exist_writ_rec != NULL)
		{	
			//در اين قسمت حکمي را که روي ان ورژن مي خورد فقط ثبت سابقه مي کند.
			$exist_writ_rec->history_only = "1";
			
			PdoDataAccess::update("writs", $exist_writ_rec, "writ_id=:wid AND writ_ver=:wver AND staff_id=:stid
				AND  state=".WRIT_PERSONAL, array(":wid" => $exist_writ_rec->writ_id,
				":wver" => $exist_writ_rec->writ_ver, ":stid" => $exist_writ_rec->staff_id));

			//$exist_writ_rec->send_letter_no = null ;
                        $exist_writ_rec->history_only = 0;

			//وضعيت حكم به نسخه جديد حكم منتقل نمي شود
			$exist_writ_rec->state = null ;
			$exist_writ_rec->correct_completed = null ;
			$exist_writ_rec->writ_ver = $exist_writ_rec->writ_ver + 1  ;

			$exist_writ_rec->corrective_date = DateModules::Shamsi_to_Miladi($this->corrective_date) ;
			$exist_writ_rec->corrective_writ_id = $this->writ_id ;
			$exist_writ_rec->corrective_writ_ver = $this->writ_ver ;
			$exist_writ_rec->correct_completed = WRIT_CORRECTING ;

			//آخرين مدرک تحصيلي فرد را بر مي دارد.
			$education_level_rec = manage_person_education::GetEducationLevelByDate($PersonID, $exist_writ_rec->execute_date);
			$exist_writ_rec->education_level = $education_level_rec['max_education_level'] ;
			$exist_writ_rec->sfid = $education_level_rec['sfid'] ;
			$exist_writ_rec->sbid = $education_level_rec['sbid'] ;

			 //تعداد فرزندان محاسبه مي گردد.

			$where = "PersonID=" . $PersonID . "
					  AND (dependency = 5 or dependency = 6)
					  AND birth_date <='" .$exist_writ_rec->execute_date . "'";
			$no = manage_person_dependency::CountDependency($where);
			$exist_writ_rec->children_count = $no;

			//تعداد افراد تحت کفالت
			$exist_writ_rec->included_children_count = manage_person_dependency::bail_count($PersonID, $exist_writ_rec->person_type ,$exist_writ_rec->execute_date , $exist_writ_rec->execute_date);
if ($exist_writ_rec->person_type == HR_EMPLOYEE || $exist_writ_rec->person_type == HR_CONTRACT )
{
	/*if($lastWritObj->execute_date > '2014-03-20')
		$this->base = $lastWritObj->base ; 
	else {*/

		$Pqry = " select sex , military_duration_day ,military_duration  
						from persons p inner join staff s on p.personid = s.personid 
								where s.staff_id=".$exist_writ_rec->staff_id ; 
		$Pres = parent::runquery($Pqry) ; 
		if($Pres[0]["sex"] == 1 &&  $exist_writ_rec->person_type == 2 && ($Pres[0]["military_duration_day"] > 0 || $Pres[0]["military_duration"] > 0 ) )
		{
			$totalDayWrt = DateModules::ymd_to_days($exist_writ_rec->onduty_year, $exist_writ_rec->onduty_month , $exist_writ_rec->onduty_day ) ; 			
			$totalDaySar = DateModules::ymd_to_days(0, $Pres[0]["military_duration"], $Pres[0]["military_duration_day"]) ; 					
			$resDay = $totalDayWrt -  $totalDaySar  ; 

			$Vyear = 0 ; 
			$Vmonth = $Vday = 0 ; 
			DateModules::day_to_ymd($resDay, $Vyear, $Vmonth, $Vday) ; 
			$Vyear =  $Vyear ; 

		}						
		else { 		

				/*$totalDayWrt = DateModules::ymd_to_days($this->onduty_year, $this->onduty_month , $this->onduty_day ) ; 							

				$diffYear = DateModules::getDateDiff(DateModules::Now(),'2014-03-21');

				$remainDay = $totalDayWrt - $diffYear ; 

				DateModules::day_to_ymd($remainDay, $Ryear, $Rmonth, $Rday) ; 
				$Vyear = $Ryear  ;*/

				$Vyear =  $exist_writ_rec->onduty_year ;   


			}

		$exist_writ_rec->base =  $Vyear + 1 ; 

		/*}*/
}
			//$this->set_writ_sign_info();

           /* if($exist_writ_rec->person_type == HR_PROFESSOR )
            {
                $this->writ_signature_post_owner = 'محمد کافی' ; 
                $this->writ_signature_post_title = ' رئیس دانشگاه ';
            }
			else {
				$exist_writ_rec->writ_signature_post_owner = ($this->issue_date > '2014-02-01') ? 'ابوالقاسم ساقی' : WRIT_SIGNATURE_POST_OWNER ;
			}*/
			
			//..........
			if($exist_writ_rec->person_type == HR_PROFESSOR && $exist_writ_rec->staff_id != '111551' )
        {
            $exist_writ_rec->writ_signature_post_owner = 'محمد کافی' ; 
			$exist_writ_rec->writ_signature_post_title = ' رئیس دانشگاه' ; 
            //$this->writ_signature_post_title = ' رئیس دانشگاه ';
        }
		else if($exist_writ_rec->person_type == HR_PROFESSOR && $exist_writ_rec->staff_id == '111551' )
        {
            //$this->writ_signature_post_owner = 'محمدجواد وریدی' ;
			$exist_writ_rec->writ_signature_post_owner = 'ابوالفضل باباخانی' ;
            $exist_writ_rec->writ_signature_post_title = 'معاون اداری ومالی دانشگاه';
        }
		else {
				$exist_writ_rec->writ_signature_post_owner = ($exist_writ_rec->issue_date > '2014-02-01') ? 'ابوالقاسم ساقی' : WRIT_SIGNATURE_POST_OWNER ;
			}
			
			//.............
            $return = parent::insert("writs", $exist_writ_rec);

		    if($return == 0)
		    {
		    	parent::PushException("ايجاد با شکست مواجه شد");
		    	return false;
		    }

		    manage_writ_item::compute_writ_items($exist_writ_rec->writ_id , $exist_writ_rec->writ_ver, $exist_writ_rec->staff_id);

		    return $exist_writ_rec ;
           /*  header("location: ../ui/view_writ.php?WID=" . $exist_writ_rec->writ_id .
		    	"&WVER=" . $exist_writ_rec->writ_ver . "&STID=" . $exist_writ_rec->staff_id);
			die();*/
		}

	}

	/**
	 * وضعیت اصلاحی فرم را مشخص می کند
	 *
	 * @return "CORRECTING" or "NOT_CORRECTING" or ""
	 */
	public function check_corrective_state()
	{
		
		
		if($this->corrective)
			return 'CORRECTING';

		if($this->correct_completed == WRIT_CORRECT_COMPLETED)
			return 'NOT_CORRECTING';

		if(!empty($this->corrective_writ_id) && !empty($this->corrective_writ_ver))
		{
			$query = "SELECT w.writ_id,
			               w.writ_ver,
			               w.staff_id,
			               w.execute_date,
			               s.person_type ,
			               w.corrective ,
			               w.corrective_writ_id ,
			               w.corrective_writ_ver

					FROM HRM_staff s
						LEFT OUTER JOIN HRM_writs w ON (w.staff_id = s.staff_id)

			        WHERE s.staff_id = " . $this->staff_id . " AND
							(writ_id<>" . $this->writ_id . " OR writ_ver<>" . $this->writ_ver . ")AND
			               	(execute_date > '" . $this->execute_date . "' OR
			               		(execute_date = '" . $this->execute_date . "' AND writ_id>" . $this->writ_id . ")) AND
			               (
			               		(writ_id=" . $this->corrective_writ_id . " AND writ_ver=" . $this->corrective_writ_ver . ")OR
			               		(corrective_writ_id=" . $this->corrective_writ_id . " AND
			               			corrective_writ_ver = " . $this->corrective_writ_ver . ")
			               )
			        ORDER BY s.staff_id,w.execute_date,w.writ_id , w.writ_ver DESC";

			$temp = PdoDataAccess::runquery($query);
			if(count($temp) == 0)
				return "";

			if($temp[0]['corrective'] || !$temp[0]['corrective_writ_id'])
				return 'CORRECTING';
			else
				return 'NOT_CORRECTING';
		}

		return "";
	}
	/*_______________________________________________________________

						static functions
	_______________________________________________________________*/

	public static function RemoveWrit($writ_id, $writ_ver, $staff_id)
	{
		$obj = new manage_writ($writ_id, $writ_ver, $staff_id);

		if(!$obj->onBeforeDelete()){

            return false;

            }


    	$DB = PdoDataAccess::getPdoObject();
    	/*@var $DB PDO*/
    	$DB->beginTransaction();

        $return = PdoDataAccess::delete("writ_salary_items", "writ_id=:wid AND writ_ver=:wver AND staff_id=:stid",
                        array(":wid"=> $obj->writ_id, ":wver"=> $obj->writ_ver, ":stid" => $obj->staff_id));

        if($return === false)
        {
        	$DB->rollBack();
        	return false;
        }


        $return = PdoDataAccess::delete("writs", "writ_id=:wid AND writ_ver=:wver AND staff_id=:stid",
        	array(":wid"=> $obj->writ_id, ":wver"=> $obj->writ_ver, ":stid" => $obj->staff_id));

		if($return === false)
        {
        	$DB->rollBack();
        	return false;
        }
	
	$daObj = new DataAudit();
	$daObj->ActionType = DataAudit::Action_delete;
	$daObj->MainObjectID = $obj->staff_id;
	$daObj->TableName = "writs";	
	$daObj->execute();

        $last_writ_obj = manage_staff::GetLastWrit($obj->staff_id);

        if($last_writ_obj) {

        //__________________________________________________
        // دادن پست حكم قبلي به فرد در صورت خالي بودن اين پست
    	if($obj->is_last($obj->staff_id, $obj->execute_date) && $last_writ_obj->post_id != $obj->post_id)
    	{
    		if(!manage_posts::change_user_post($obj->staff_id, $obj->post_id, $last_writ_obj->post_id, $obj->execute_date))
    		{
    			$DB->rollBack();
        		return false;
    		}
    	}

        }
    	if($obj->history_only != HISTORY_ONLY)
    		if(!manage_writ::change_writ_state(WRIT_PERSONAL, WRIT_PERSONAL, $obj->writ_id, $obj->writ_ver,
					$obj->staff_id, $obj->execute_date, $DB))
    		{
    			$DB->rollBack();
        		return false;
    		}
    	//__________________________________________________
    	//در صورت حذف يک حکم نسخه قبلي آن را در صورتي که به حقوق منتقل نشده است فعال مي کند
    	if(!manage_staff::SetStaffLastWrit($obj->staff_id))
    	{
    		$DB->rollBack();
        	return false;
    	}

        $DB->commit();
	
	
        return true;
	}

	/**
	 * تنها با استفاده از اين تابع مي توان حکم جديد صادر کرد
	 *
	 * @param int $staff_id
	 * @param int $writ_type_id
	 * @param int $writ_subtype_id
	 * @param ShamsiDate $execute_date
	 * @param boolean $history_only
	 * @param boolean $corrective
	 * @param string $send_letter_no
	 * @param ShamsiDate $issue_date
	 * @param int $corrective_writ_id
	 * @param int $corrective_writ_ver
	 *
	 * @return boolean : قرار مي دهد ExceptionHandler اگر صدور حکم با موفقيت انجام نشود توضيح خطا را در
	 */
	public static function IssueWrit($staff_id,
									 $writ_type_id,
									 $writ_subtype_id,
									 $execute_date,
									 $person_type,
									 $issue_date = Null,
									 $history_only=false,
									 $corrective=false,
									 $send_letter_no = NULL,
									 $corrective_writ_id=NULL ,
									 $corrective_writ_ver=NULL,									
									 $contract_start_date=NULL,
									 $contract_end_date=NULL,
                                                                         $indiv=NULL)
	{		
						                
        //-------------------------------------------new--------------------------
                       
		if($issue_date != NULL )
		{
					
                    if(DateModules::CompareDate(DateModules::Shamsi_to_Miladi($issue_date) , date('Y-m-d')) == 1 ) {
                            parent::PushException(ER_ISSUE_DATE_IS_NOT_VALID);
                            return false;
                    }

		}
		$obj = new manage_writ();
  
        if( $corrective !== true )
            $corrective = false ;

        if( $history_only !== true )
            $history_only = false ;

		if(!is_bool($corrective) || !is_bool($history_only))
		{
			parent::PushException("ورودي هاي تابع نا معتبر مي باشد");
			return false;
		}

		require_once '../../persons/class/person.class.php';
		require_once '../../persons/class/education.class.php';
		require_once '../../persons/class/dependent.class.php';
		//---------------------------------------
 
		$obj->staff_id = $staff_id;
		$obj->person_type = $person_type;
		$obj->writ_type_id = $writ_type_id;
		$obj->writ_subtype_id = $writ_subtype_id;

		$issue_date = (!empty($issue_date)) ? str_replace("/","-",DateModules::Shamsi_to_Miladi($issue_date)) : "0000-00-00" ;

		$obj->issue_date = ($issue_date == "0000-00-00") ? date("Y-m-d") : $issue_date;

		$obj->execute_date = str_replace("/","-",DateModules::Shamsi_to_Miladi($execute_date));
		$obj->history_only = ( $history_only == true ) ? "1" : "0";
		$obj->corrective = ( $corrective == true ) ? "1" : "0";



		if($corrective_writ_id != null)
			$obj->corrective_writ_id = $corrective_writ_id;
		else $obj->corrective_writ_id = PDONULL ;

		if($corrective_writ_ver != null)
			$obj->corrective_writ_ver = $corrective_writ_ver;
		else 	$obj->corrective_writ_ver = PDONULL;


		/*if($send_letter_no != null)
			$obj->send_letter_no = $send_letter_no;
		else 	$obj->send_letter_no = PDONULL ; */


		if(!empty($corrective_writ_id) && !empty($corrective_writ_ver))
	    {
	    	$obj->corrective_writ_id = $corrective_writ_id;
	    	$obj->corrective_writ_ver = $corrective_writ_ver;
	    	$obj->correct_completed = WRIT_CORRECTING ;

	    }
		else
			$obj->correct_completed = WRIT_NOT_CORRECTED;
	   
	    //.......................................
	    	    
	    if(!empty($contract_start_date)){
		
		if( $obj->execute_date <  DateModules::Shamsi_to_Miladi($contract_start_date) ){
		    list($year,$month,$day) = explode('/',DateModules::miladi_to_shamsi($obj->execute_date));		   		  
		    $Sdate = $year . "/01/01"; 
		    $Edate = $year . "/12/29"; 
		    $obj->contract_start_date = DateModules::Shamsi_to_Miladi($Sdate) ;
		    
		    }
		    else {
			 $obj->contract_start_date = DateModules::Shamsi_to_Miladi($contract_start_date) ;			
			 }
		     
		}
		else {			
			list($year,$month,$day) = explode('/',DateModules::miladi_to_shamsi($obj->execute_date));		   		  
		    $Sdate = $year . "/01/01"; 
		    $Edate = $year . "/12/29"; 
		    $obj->contract_start_date = DateModules::Shamsi_to_Miladi($Sdate) ;
			$obj->contract_end_date = DateModules::Shamsi_to_Miladi($Edate);			
		}
		
	    if(!empty($contract_end_date)){
			 if( $obj->execute_date <  DateModules::Shamsi_to_Miladi($contract_start_date) )
			{			     
			     $obj->contract_end_date = DateModules::Shamsi_to_Miladi($Edate); 			     
			}
			else {     
			    
			     $obj->contract_end_date = DateModules::Shamsi_to_Miladi($contract_end_date);  			
			}
			
			}

		//___________________________________________________________________________________________________
		//عدم امکان صدور یک حکم زمانی که حکمی با آن تاریخ اجرا وجود دارد که هنوز باز است

		$query = "select *
							from HRM_writs where execute_date=? AND staff_id=? AND history_only <> 1 AND
								 writ_type_id = ".$obj->writ_type_id." AND  writ_subtype_id = ".$obj->writ_subtype_id." AND state=" . WRIT_PERSONAL;
		$DT = parent::runquery($query, array($obj->execute_date, $obj->staff_id));
		if(count($DT) != 0 && $obj->execute_date >= '2005-03-20' )
		{

			parent::PushException(EXIST_OPEN_WRIT);
			return false;
		}

		//.......................................

		$return = $obj->IssueWritAction($indiv);
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $obj->staff_id;
		$daObj->TableName = "HRM_writs";	
		$daObj->execute();
	
		if(!$return)
			return false;
		return $obj;
	}

  /**
	 * از تاريخ شروع اصلاح ، صدور حکم اصلاحي را شروع مي کند.
	 *
	 * @param int $staff_id
	 * @param ShamsiDate $corrective_date
	 * @param manage_writ $writ_rec
	 * @param int $writ_type_id
	 * @param int $writ_subtype_id
	 * @param int $base
	 * @param string $send_letter_no
	 * @param ShamsiDate $issue_date
	 * @param int $base_writ_issue

	 */
	public static function start_corrective_writ_issue($staff_id,
			                        				   $corrective_date,
													   $writ_obj,
													   $writ_type_id,
													   $writ_subtype_id,
													   $base,
													   $send_letter_no,
			                                           $issue_date,
			                                           $base_writ_issue )
	{

		$obj = new manage_writ();

		$obj->staff_id = $staff_id;
		$obj->corrective_date = $corrective_date;
		$obj->writ_id = $writ_obj->writ_id ;
		$obj->writ_ver = $writ_obj->writ_ver ;
		$obj->writ_type_id = $writ_type_id;
		$obj->writ_subtype_id = $writ_subtype_id;
		$obj->base = $base;
		$obj->send_letter_no = $send_letter_no;
		$obj->issue_date = $issue_date;

		return $obj->CorrectiveIssueAction($base_writ_issue);
	}

	/**
	 * اطلاعات آخرين حکم فرد را بر مي گرداند
	 *
	 * @param string $staff_id
	 *
	 * @return manage_writ object
	 */
	public static function GetLastWrit($staff_id)
	{
		$query = "select * from HRM_writs
			   		where staff_id=:stfid and (history_only != ".HISTORY_ONLY." OR history_only IS NULL)
						order by execute_date DESC,writ_id DESC,writ_ver DESC ";
			$whereParam = array(":stfid" => $staff_id);

			$obj = new manage_writ("");
			parent::FillObject($obj, $query, $whereParam);

			return $obj;
	}

	/**
	 * اين تابع بررسي مي کند که در زمان شروع اصلاح حکمي و جود دارد يا خير ؟
	 *
	 * @param  $staff_id
	 * @param  $corrective_date
	 * @return اگر حکمی وجود داشته باشد رکورد آن را برمی گرداند در غیر اینصورت null برمی گرداند
	 */
	public static function Is_Writ_For_Correct($staff_id, $corrective_date)
	{
	    $corrective_date = DateModules::Shamsi_to_Miladi($corrective_date);
		$query = " SELECT *
		   		   FROM   writs
		    	   WHERE  staff_id = ".$staff_id." AND
		    	  		  execute_date = '".$corrective_date."' AND
		    	  		  history_only <> 1
		    	   ORDER BY writ_id , writ_ver DESC ";

		$temp = parent::runquery($query);
        if(count($temp)!= 0){
        	$ObjWrt = new manage_writ();
            parent::FillObjectByArray($ObjWrt,$temp[0]);
            return $ObjWrt;
        }
        else
        {
        	return NULL ;
        }
	}

	/**
	 * اين تابع وضعيت حکم را تغيير مي دهد
	 * اگر در تاريخ اجراي حکم، احکام ديگري با وضعيت جديد وجود داشته باشند ثبت سابقه می شوند
	 * اگر در تاريخ اجراي حکم، احکام ديگري با وضعيت قبلي وجود داشته باشند که ثبت سابقه هستند فعال می شوند
	 */
		static function change_writ_state($old_state, $new_state, $writ_id, $writ_ver, $staff_id, $execute_date, $DB = "")
	{
		if($DB == "")
		{
			$pdo = PdoDataAccess::getPdoObject();
			/*@var $pdo PDO*/
			$pdo->beginTransaction();
		}
		else
			$pdo = $DB;

		if($old_state > $new_state)
		{
			//_______________________________________________
			// تغيير وضعيت حکم
			$query = "UPDATE HRM_writs
						SET state = " . $new_state . "
						WHERE writ_id=" . $writ_id . " AND
							  writ_ver=" . $writ_ver . " AND
							  staff_id=" . $staff_id ;
			PdoDataAccess::runquery($query, array());
            //..................................................
            if($new_state == 2 ) {
               $qry = " UPDATE HRM_writs
                                SET writ_recieve_date = NULL
                        WHERE writ_id=" . $writ_id . " AND
							  writ_ver=" . $writ_ver . " AND
							  staff_id=" . $staff_id  ;
                PdoDataAccess::runquery($qry, array());

            }
            if($new_state == 1 ) {
               $qry = " UPDATE HRM_writs
                                SET writ_transfer_date = NULL
                        WHERE writ_id=" . $writ_id . " AND
							  writ_ver=" . $writ_ver . " AND
							  staff_id=" . $staff_id  ;
                PdoDataAccess::runquery($qry, array());

            }

            //..................................................

			if(ExceptionHandler::GetExceptionCount() != 0)
			{
				if($DB == "")
					$pdo->rollBack();
				return false;
			}
			//_______________________________________________
			// فعال کردن نسخه قبلي حکم
			$query = "UPDATE HRM_writs
						SET history_only=0
						WHERE staff_id=" . $staff_id . "
							  AND execute_date='" . $execute_date . "'
							  AND state=" . $old_state . "
							  AND (writ_id=" . $writ_id . " OR writ_ver<" . $writ_ver . ")";
			PdoDataAccess::runquery($query, array());
			if(ExceptionHandler::GetExceptionCount() != 0)
			{
				if($DB == "")
					$pdo->rollBack();
				return false;
			}
			if(PdoDataAccess::AffectedRows() != 0)
			{
				if($DB == "")
					$pdo->commit();
				return true;
			}
			//_______________________________________________
			//پيدا کردن حکم قبلی و فعال کردن آن
			$query = "SELECT writ_id , writ_ver , staff_id
						FROM writs
						WHERE 	staff_id = " . $staff_id . "
								AND execute_date = '" . $execute_date . "'
								AND corrective = 0
								AND state = " . $old_state . "
								AND (writ_id <> " . $writ_id . ")
						ORDER BY writ_id DESC , writ_ver DESC";

			$temp = PdoDataAccess::runquery($query, array());
			if(count($temp) != 0)
			{
				$query = "UPDATE writs
							SET history_only = 0
							WHERE writ_id = :wid AND writ_ver = :wver AND staff_id = :stid";

				PdoDataAccess::runquery($query, array(":wid" => $temp[0]["writ_id"],
														  ":wver" => $temp[0]["writ_ver"],
														  ":stid" => $temp[0]["staff_id"]));
				if(ExceptionHandler::GetExceptionCount() != 0)
				{
					if($DB == "")
						$pdo->rollBack();
					return false;
				}
			}
			if($DB == "")
				$pdo->commit();
		}
		else
		{
		    
		   
			$temp = PdoDataAccess::runquery("SELECT staff_id , writ_id , writ_ver , execute_date
				FROM writs
				WHERE (history_only=0 OR history_only IS NULL)
 			    	AND (dont_transfer = 0 OR dont_transfer IS NULL)
  			    	AND (correct_completed !=" . WRIT_CORRECTING . ")
  			     	AND writ_id=" . $writ_id . " AND writ_ver=" . $writ_ver . " AND staff_id=" . $staff_id);

			if(count($temp) == 0)
			{
				if($DB == "")
					$pdo->commit();
				return true;
			}

			$return = PdoDataAccess::runquery("update writs set state=" . $new_state .
				" where writ_id=" . $writ_id . " AND writ_ver=" . $writ_ver . " AND staff_id=" . $staff_id);

          //..................................................
 
            if($new_state == 2 ) {
               $qry = " UPDATE writs
                                SET writ_transfer_date = now()
                        WHERE writ_id=" . $writ_id . " AND
							  writ_ver=" . $writ_ver . " AND
							  staff_id=" . $staff_id  ;
                PdoDataAccess::runquery($qry, array());

            }
            if($new_state == 3 ) {
               $qry = " UPDATE writs
                                SET writ_recieve_date =  now()
                        WHERE writ_id=" . $writ_id . " AND
							  writ_ver=" . $writ_ver . " AND
							  staff_id=" . $staff_id  ;
                PdoDataAccess::runquery($qry, array());
	
		//......................بررسی جهت خالی کردن مرکز هزینه ..............
		
		 $obj = new manage_writ($writ_id ,$writ_ver ,$staff_id ) ;
		 $PrevItm = $obj->get_prior_writ("",true) ; 
		 	
		    
		 if($PrevItm == 0 && manage_writ_item::compute_writ_items_sum($writ_id, $writ_ver, $staff_id) > 0 )
		    {
			$qry = " UPDATE writs
                                SET cost_center_id = null
                        WHERE writ_id=" . $writ_id . " AND
							  writ_ver=" . $writ_ver . " AND
							  staff_id=" . $staff_id  ;
			PdoDataAccess::runquery($qry, array());			
		    }
		      
		//......................
 
            }

            //..................................................

			if(ExceptionHandler::GetExceptionCount() != 0)
			{ 
				if($DB == "")
					$pdo->rollBack();
				return false;
			}

			$return = PdoDataAccess::runquery("UPDATE writs
				SET history_only=1
				WHERE staff_id = " . $temp[0]["staff_id"] . " AND
						execute_date = '" . $temp[0]["execute_date"] . "' AND
						((writ_id = " . $temp[0]["writ_id"] . " AND " . $temp[0]["writ_ver"] . ">writ_ver) OR
							" . $temp[0]["writ_id"] . ">writ_id) AND
						(history_only=0 OR history_only IS NULL) AND state =" . $new_state);

			if(ExceptionHandler::GetExceptionCount() != 0)
			{
				if($DB == "")
					$pdo->rollBack();
				return false;
			}
   
			if($DB == "")
				$pdo->commit();
			return true;

		}
		return true;
	}
	/**
	 * اين تابع مشخص مي کند که آيا حکم مربوط به قبل از سال 1374 مي باشد يا بعد از آن.
	 * اگر حکم مربوط به قبل از سال 1374 باشد در هر حالتي قابل ويرايش است.
	 *
	 * اگر زمان اجرا به تابع فرستاده شود ارسال نوع فرد نيز الزامي است در غير اينصورت مي توان
	 * فقط شماره و نسخه حکم را ارسال کرد. اگر نسخه فرم ارسال نشود آخرين نسخه آن حکم در نظر گرفته مي شود
	 */
	static function is_auto_writ($Gexecute_date="", $person_type="", $writ_id="", $writ_ver="", $staff_id="")
	{
		$state = manage_writ::get_writ_edit_state($Gexecute_date,$person_type,$writ_id,$writ_ver,$staff_id);

	    if($state ==2 || $state == 3 )
	    	return true ;

	    return false ;
	}

	static function is_first_writ($writ_id, $writ_ver, $staff_id)
	{
		$query = " SELECT COUNT(*) rcount
		           FROM HRM_writs w1
						 INNER JOIN HRM_writs w2 ON(w1.staff_id = w2.staff_id)
				   WHERE w1.writ_id=:wid AND
				         w1.writ_ver=:wver AND
				         w1.staff_id=:stid AND
				       ((w2.execute_date< w1.execute_date) OR
				        (w2.execute_date = w1.execute_date AND
				         w2.writ_id<w1.writ_id))  AND
				         w2.history_only IS NOT NULL ";
		$temp = PdoDataAccess::runquery($query, array(":wid" => $writ_id, ":wver" => $writ_ver, ":stid" => $staff_id));

		if($temp[0][0] == 0)
		{
			return true ;
		}

		return false ;
     }

	/**
	 * اين تابع مشخص مي کند که آيا حکم جزء احکامي است که مربوط به بعد از زمان استقرار سيستم مي باشد.
	 *
	 * اگر زمان اجرا به تابع فرستاده شود ارسال نوع فرد نيز الزامي است در غير اينصورت مي توان
	 * فقط شماره و نسخه حکم را ارسال کرد. اگر نسخه فرم ارسال نشود آخرين نسخه آن حکم در نظر گرفته مي شود
	 */
	static function is_new_writ($Gexecute_date="", $person_type="", $writ_id="", $writ_ver="", $staff_id="")
	{
		$state = manage_writ::get_writ_edit_state($Gexecute_date, $person_type, $writ_id, $writ_ver, $staff_id);

		if($state ==1 || $state == 3 ) {
			return true ;
		}

		return false ;

    }

    /**
     * این تابع بررسی می کند که آیا حکم مورد استفاده قرار گرفته است یا نه
     *
     * @param int $writ_id
     * @param int $writ_ver
     * @return boolean
     */
    static function IsUsed($writ_id, $writ_ver, $staff_id)
    {
	    $query = "SELECT *
					FROM writs w
	               		JOIN payments p ON (w.writ_id = p.writ_id AND w.writ_ver = p.writ_ver
	               			AND w.staff_id = p.staff_id)
					WHERE  p.writ_id = :wid AND p.writ_ver = :wver AND p.staff_id = :stid  AND
	               		   w.history_only != ".HISTORY_ONLY;

	    $temp = PdoDataAccess::runquery($query, array(":wid" => $writ_id, ":wver" => $writ_ver, ":stid" => $staff_id));

	    if(Count($temp) == 0)
	    	return false;
	    return true;
    }

	/** اين تابع بررسي مي کند حکمي با اين
	 * id , version
	 * وجود دارد يا خير و رکورد را برمي گرداند.
	*/
	static function get_writ_rec($writ_id,$writ_ver , $staff_id )
	{
		$query = " SELECT *
		   		   FROM   HRM_writs
		    	   WHERE  writ_id = :wid AND
		    	  		  writ_ver = :wver AND
		    	  		  staff_id = :stid";
		$temp = PdoDataAccess::runquery($query, array(":wid" => $writ_id, ":wver" => $writ_ver, ":stid" => $staff_id));
        if(count($temp)!= 0 )
        	return $temp[0];
        else {

        	$temp = NULL ;
        	return $temp ;
        }

	}

	/**
	 * مشخص مي کند که آيا احکام اصلاحي منتقل شده اند يا خير
	 *
	 * @param unknown_type $writ_id
	 * @param unknown_type $writ_ver
	 * @return boolean
	 */
	static function corrective_writs_is_used($writ_id, $writ_ver, $staff_id)
	{
		$query = "  SELECT COUNT(*) rcount
					FROM writs
					WHERE corrective_writ_id = :wid AND corrective_writ_ver = :wver AND staff_id = :stid
					AND state<> ".WRIT_PERSONAL  ;

		$temp = PdoDataAccess::runquery($query, array(":wid" => $writ_id, ":wver" => $writ_ver, ":stid" => $staff_id));

		if($temp[0]['rcount'] > 0 )
			return true ;

		return false ;
	 }

	static function Count($where = "",$whereParam = array())
	{
		$query = " SELECT count(*)
		           FROM persons p
                        LEFT JOIN staff s ON p.personid = s.personid
                        LEFT JOIN writs w ON s.staff_id = w.staff_id
			     ";
		$query .= ($where != "") ? " where " . $where : "";

		$temp = PdoDataAccess::runquery($query, $whereParam);
		return $temp[0][0];
	}

	static function GetWritInfo($where, $whereParam = array())
	{
		$staff_group_join = "";
		if(!empty($_REQUEST['staff_group_id']))
		{
			$staff_group_join = " LEFT JOIN staff_group_members sgm
	       					          ON sgm.staff_id = s.staff_id AND sgm.staff_group_id = ".$_REQUEST['staff_group_id'];
		}

		if(isset($_REQUEST['last_writ_view'])) {

			$query = " SELECT w.*,
                          bi1.Title corrective_title ,
		                  bi2.Title history_only_title ,
                          bi3.Title science_level_title ,
                          p.pfname ,
                          p.plname ,
                          p.PersonID ,
                          concat(p.pfname ,' ',p.plname) fullname ,
                          wt.title MainWtitle,
                          wst.title wst_title,
                          wst.time_limited,
                          concat(wt.title ,' ',wst.title) wt_title ,
                          bi4.Title emp_state_title ,
                          bi5.Title educTitle ,
                          bi6.Title SPTitle ,
                          bi7.Title AETitle ,
                          o.ptitle o_ptitle,
                          c.title c_title,
                          c.cost_center_id ,
                          sf.sfid,
                          sf.ptitle sf_ptitle,
                          sb.sbid,
                          sb.ptitle sb_ptitle ,
                          parentu.ouid ,
                          parentu.ptitle parentTitle , 
			  sum(if(ba.BaseType in (6) and ba.BaseStatus = 'NORMAL' ,ba.BaseValue,0))  TashvighiValue ,
			  sum(if(ba.BaseType in (3,4,5) and ba.BaseStatus = 'NORMAL' ,ba.BaseValue,0))  IsarValue

		           FROM persons p
                        JOIN staff s ON(p.personid = s.personid)
                        ".$staff_group_join."
                        INNER JOIN writs w ON(s.staff_id = w.staff_id AND s.last_writ_id = w.writ_id AND s.last_writ_ver = w.writ_ver)
                        LEFT JOIN Basic_Info bi1 ON ( bi1.InfoID = w.corrective  AND bi1.TypeID = 5)
                        LEFT JOIN Basic_Info bi2 ON ( bi2.InfoID = w.history_only  AND bi2.TypeID = 5)
                        LEFT JOIN Basic_Info bi3 ON (bi3.InfoID = w.science_level AND bi3.TypeID = 8)
                        LEFT JOIN Basic_Info bi4 ON (bi4.InfoID = w.emp_state AND bi4.TypeID = 3)
                        LEFT JOIN Basic_Info bi5 ON (bi5.InfoID = w.education_level AND bi5.TypeID = 6 )
                        LEFT JOIN Basic_Info bi6 ON (bi6.InfoID = w.salary_pay_proc AND bi6.TypeID = 12 )
                        LEFT JOIN Basic_Info bi7 ON (bi7.InfoID = w.annual_effect AND bi7.TypeID = 13 )
                        LEFT OUTER JOIN position po ON (w.post_id = po.post_id)
                        LEFT OUTER JOIN writ_types wt ON (w.writ_type_id = wt.writ_type_id AND w.person_type = wt.person_type)
                        LEFT OUTER JOIN writ_subtypes wst ON (w.writ_subtype_id = wst.writ_subtype_id AND
                                               w.writ_type_id = wst.writ_type_id AND w.person_type = wst.person_type)
                        LEFT OUTER JOIN study_branchs sb ON ((w.sbid = sb.sbid) AND (w.sfid = sb.sfid))
                        LEFT OUTER JOIN study_fields sf ON (w.sfid = sf.sfid)
                        LEFT OUTER JOIN org_new_units o ON (o.ouid = w.ouid)
                        LEFT OUTER JOIN org_new_units parentu ON (parentu.ouid = o.parent_ouid)
                        LEFT OUTER JOIN cost_centers c ON (w.cost_center_id = c.cost_center_id)
			LEFT OUTER JOIN bases ba ON p.personid = ba.personid 

                        where (s.last_cost_center_id is null OR s.last_cost_center_id in(" . manage_access::getValidCostCenters() . "))
						AND s.person_type in(" . manage_access::getValidPersonTypes() . ")";
		}
		else {
			
			
			$query = " SELECT w.*, w.ouid sub_ouid ,
	                          bi1.InfoDesc corrective_title ,
			                  bi2.InfoDesc history_only_title ,	                         
	                          p.pfname ,
	                          p.plname ,
	                          p.PersonID ,
	                          concat(p.pfname ,' ',p.plname) fullname ,
	                          wt.title MainWtitle,
	                          wst.title wst_title,
	                          wst.time_limited,
	                          concat(wt.title ,' ',wst.title) wt_title ,
	                          bi4.InfoDesc emp_state_title ,
	                          bi5.InfoDesc educTitle ,
	                          bi6.InfoDesc SPTitle ,
	                          bi7.InfoDesc AETitle ,
	                          bi8.InfoDesc ModeTitle ,
	                          o.ptitle o_ptitle,	                         
	                          sf.sfid,
	                          sf.ptitle sf_ptitle,
	                          sb.sbid,
	                          sb.ptitle sb_ptitle ,
	                          parentu.ouid ,
	                          parentu.ptitle parentTitle ,
	                          po.title post_title ,
	                          po.post_no ,
	                          j.title job_title ,
	                          j.job_group ,
	                          
				 (w.cur_group - jf.start_group) + 1 job_category 

				FROM HRM_persons p
	                        JOIN HRM_staff s ON(p.personid = s.personid)
	                        ".$staff_group_join ."
	                        INNER JOIN HRM_writs w ON (s.staff_id = w.staff_id)
							
	                        LEFT JOIN BaseInfo bi1 ON ( bi1.InfoID = w.corrective  AND bi1.TypeID = 57)
	                        LEFT JOIN BaseInfo bi2 ON ( bi2.InfoID = w.history_only  AND bi2.TypeID = 57)	                        
	                        LEFT JOIN BaseInfo bi4 ON (bi4.InfoID = w.emp_state AND bi4.TypeID = 58)
	                        LEFT JOIN BaseInfo bi5 ON (bi5.InfoID = w.education_level AND bi5.TypeID = 56 )
	                        LEFT JOIN BaseInfo bi6 ON (bi6.InfoID = w.salary_pay_proc AND bi6.TypeID = 59 )
	                        LEFT JOIN BaseInfo bi7 ON (bi7.InfoID = w.annual_effect AND bi7.TypeID = 60 )
	                        LEFT OUTER JOIN BaseInfo bi8 ON ( bi8.InfoID = w.emp_mode AND bi8.TypeID = 61 )
	                        LEFT OUTER JOIN HRM_position po ON (w.post_id = po.post_id)
							LEFT OUTER JOIN HRM_job_fields jf ON (po.jfid = jf.jfid)
							
	                        LEFT OUTER JOIN HRM_writ_types wt ON ((w.writ_type_id = wt.writ_type_id) AND (w.person_type = wt.person_type))
	                        LEFT OUTER JOIN HRM_writ_subtypes wst ON ((w.writ_subtype_id = wst.writ_subtype_id) AND (w.writ_type_id = wst.writ_type_id) AND (w.person_type = wst.person_type))
	                        LEFT OUTER JOIN HRM_study_branchs sb ON ((w.sbid = sb.sbid) AND (w.sfid = sb.sfid))
	                        LEFT OUTER JOIN HRM_study_fields sf ON (w.sfid = sf.sfid)
	                        LEFT OUTER JOIN HRM_org_new_units o ON (o.ouid = w.ouid)
	                        LEFT OUTER JOIN HRM_org_new_units parentu ON (parentu.ouid = o.parent_ouid)
	                       						
	                        LEFT OUTER JOIN HRM_jobs j ON ( w.job_id = j.job_id )
							
					where (s.last_cost_center_id is null )	";
		}

		$query .= ($where != "") ? " AND " . $where : "";

		$temp = parent::runquery($query, $whereParam);
 		
		return $temp;
	}

	static function GetAllWrits($where = "",$whereParam = array())
	{  
			
       /* $staff_group_join = "";
		if(!empty($_REQUEST['staff_group_id']))
		{
			$staff_group_join = " LEFT JOIN staff_group_members sgm
	       					          ON sgm.staff_id = s.staff_id AND sgm.staff_group_id = ".$_REQUEST['staff_group_id'];
		} */ 

	//	PdoDataAccess::runquery("insert into temp_sum_item_writs select * from sum_items_writs i on duplicate key update sumValue=i.sumValue");
		$whr = "" ; 
		/*if($_SESSION['UserID'] != 'jafarkhani' && $_SESSION['UserID'] != 'delkalaleh' && $_SESSION['UserID'] != 'nadaf' && 
		   $_SESSION['UserID'] != 'm-hakimi' && $_SESSION['UserID'] != 'shokri'  ) {
			
			 $whr = " AND w.execute_date < '2014-02-20' " ; 
		} */
		if(isset($_REQUEST['last_writ_view']) && (empty($_REQUEST['to_execute_date']) || 
                         $_REQUEST['to_execute_date']=='0000-00-00' )) {
			
			
		   
			$query = " SELECT w.*,
                          bi1.Title corrective_title ,
		                  bi2.Title history_only_title ,
                          bi3.Title science_level_title ,
                          p.pfname ,
                          p.plname ,
                          p.PersonID ,
                          concat(p.pfname ,' ',p.plname) fullname ,
                          wt.title MainWtitle,
                          wst.title wst_title,
                          wst.time_limited,
                          concat(wt.title ,' ',wst.title) wt_title ,
                          bi4.Title emp_state_title ,
                          bi5.Title educTitle ,
                          bi6.Title SPTitle ,
                          bi7.Title AETitle ,
                          o.ptitle o_ptitle,
                          c.title c_title,
                          c.cost_center_id ,
                          sf.sfid,
                          sf.ptitle sf_ptitle,
                          sb.sbid,
                          sb.ptitle sb_ptitle ,
                          parentu.ouid ,
                          parentu.ptitle parentTitle ,
                          temp.sumValue

		           FROM persons p
                        JOIN staff s ON(p.personid = s.personid)
                        ".$staff_group_join."
                        INNER JOIN writs w ON(s.staff_id = w.staff_id AND s.last_writ_id = w.writ_id AND s.last_writ_ver = w.writ_ver)
                        LEFT JOIN sum_items_writs temp ON temp.writ_id = w.writ_id and
                                     temp.writ_ver = w.writ_ver and
                                     temp.staff_id = w.staff_id
                        LEFT JOIN Basic_Info bi1 ON ( bi1.InfoID = w.corrective  AND bi1.TypeID = 5)
                        LEFT JOIN Basic_Info bi2 ON ( bi2.InfoID = w.history_only  AND bi2.TypeID = 5)
                        LEFT JOIN Basic_Info bi3 ON (bi3.InfoID = w.science_level AND bi3.TypeID = 8)
                        LEFT JOIN Basic_Info bi4 ON (bi4.InfoID = w.emp_state AND bi4.TypeID = 3)
                        LEFT JOIN Basic_Info bi5 ON (bi5.InfoID = w.education_level AND bi5.TypeID = 6 )
                        LEFT JOIN Basic_Info bi6 ON (bi6.InfoID = w.salary_pay_proc AND bi6.TypeID = 12 )
                        LEFT JOIN Basic_Info bi7 ON (bi7.InfoID = w.annual_effect AND bi7.TypeID = 13 )
                        LEFT OUTER JOIN position po ON (w.post_id = po.post_id)
                        LEFT OUTER JOIN writ_types wt ON (w.writ_type_id = wt.writ_type_id AND w.person_type = wt.person_type)
                        LEFT OUTER JOIN writ_subtypes wst ON (w.writ_subtype_id = wst.writ_subtype_id AND
                                               w.writ_type_id = wst.writ_type_id AND w.person_type = wst.person_type)
                        LEFT OUTER JOIN study_branchs sb ON ((w.sbid = sb.sbid) AND (w.sfid = sb.sfid))
                        LEFT OUTER JOIN study_fields sf ON (w.sfid = sf.sfid)
        LEFT OUTER JOIN org_new_units o ON (o.ouid = w.ouid)
        LEFT OUTER JOIN org_new_units parentu ON (parentu.ouid = o.parent_ouid)
                        LEFT OUTER JOIN cost_centers c ON (w.cost_center_id = c.cost_center_id)

                        where (s.last_cost_center_id is null OR s.last_cost_center_id in(" . manage_access::getValidCostCenters() . "))
						AND s.person_type in(" . manage_access::getValidPersonTypes() . ") $whr ";
		}
                else if(isset($_REQUEST['last_writ_view']) && (!empty($_REQUEST['to_execute_date']) && $_REQUEST['to_execute_date']!='0000-00-00')) {
                   
						   
                    $whereW = " AND w.execute_date >= '".DateModules::shamsi_to_miladi($_REQUEST['from_execute_date'])."'" ; 
                    $whereW .= " AND w.execute_date <= '".DateModules::shamsi_to_miladi($_REQUEST['to_execute_date'])."'" ;                     
                    
                    $query = " SELECT w.*,
                          bi1.Title corrective_title ,
		                  bi2.Title history_only_title ,
                          bi3.Title science_level_title ,
                          p.pfname ,
                          p.plname ,
                          p.PersonID ,
                          concat(p.pfname ,' ',p.plname) fullname ,
                          wt.title MainWtitle,
                          wst.title wst_title,
                          wst.time_limited,
                          concat(wt.title ,' ',wst.title) wt_title ,
                          bi4.Title emp_state_title ,
                          bi5.Title educTitle ,
                          bi6.Title SPTitle ,
                          bi7.Title AETitle ,
                          o.ptitle o_ptitle,
                          c.title c_title,
                          c.cost_center_id ,
                          sf.sfid,
                          sf.ptitle sf_ptitle,
                          sb.sbid,
                          sb.ptitle sb_ptitle ,
                          parentu.ouid ,
                          parentu.ptitle parentTitle ,
                          temp.sumValue

		           FROM persons p
                        JOIN staff s ON(p.personid = s.personid)
                        ".$staff_group_join."
                        INNER JOIN (SELECT    staff_id,
                                                    SUBSTRING_INDEX(SUBSTRING(max_execute_date,11),'.',1) writ_id,
                                                    SUBSTRING_INDEX(max_execute_date,'.',-1) writ_ver
                                                FROM (SELECT w.staff_id,
                                                            max( CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver) ) max_execute_date
                                                        FROM writs w
                                                                INNER JOIN staff ls
                                                                        ON(w.staff_id = ls.staff_id)
                                                        WHERE w.history_only = 0 ".$whereW."
                                                        GROUP BY w.staff_id)tbl2) tbl1
                                             on s.staff_id = tbl1.staff_id 
                        INNER JOIN writs w
                                on  tbl1.writ_id = w.writ_id and
                                    tbl1.writ_ver = w.writ_ver and
                                    tbl1.staff_id = w.staff_id                           
                        LEFT JOIN sum_items_writs temp ON temp.writ_id = w.writ_id and
                                     temp.writ_ver = w.writ_ver and
                                     temp.staff_id = w.staff_id
                        LEFT JOIN Basic_Info bi1 ON ( bi1.InfoID = w.corrective  AND bi1.TypeID = 5)
                        LEFT JOIN Basic_Info bi2 ON ( bi2.InfoID = w.history_only  AND bi2.TypeID = 5)
                        LEFT JOIN Basic_Info bi3 ON (bi3.InfoID = w.science_level AND bi3.TypeID = 8)
                        LEFT JOIN Basic_Info bi4 ON (bi4.InfoID = w.emp_state AND bi4.TypeID = 3)
                        LEFT JOIN Basic_Info bi5 ON (bi5.InfoID = w.education_level AND bi5.TypeID = 6 )
                        LEFT JOIN Basic_Info bi6 ON (bi6.InfoID = w.salary_pay_proc AND bi6.TypeID = 12 )
                        LEFT JOIN Basic_Info bi7 ON (bi7.InfoID = w.annual_effect AND bi7.TypeID = 13 )
                        LEFT OUTER JOIN position po ON (w.post_id = po.post_id)
                        LEFT OUTER JOIN writ_types wt ON (w.writ_type_id = wt.writ_type_id AND w.person_type = wt.person_type)
                        LEFT OUTER JOIN writ_subtypes wst ON (w.writ_subtype_id = wst.writ_subtype_id AND
                                               w.writ_type_id = wst.writ_type_id AND w.person_type = wst.person_type)
                        LEFT OUTER JOIN study_branchs sb ON ((w.sbid = sb.sbid) AND (w.sfid = sb.sfid))
                        LEFT OUTER JOIN study_fields sf ON (w.sfid = sf.sfid)
                        LEFT OUTER JOIN org_new_units o ON (o.ouid = w.ouid)
                        LEFT OUTER JOIN org_new_units parentu ON (parentu.ouid = o.parent_ouid)
                        LEFT OUTER JOIN cost_centers c ON (w.cost_center_id = c.cost_center_id)

                        where (s.last_cost_center_id is null OR s.last_cost_center_id in(" . manage_access::getValidCostCenters() . "))
						AND s.person_type in(" . manage_access::getValidPersonTypes() . ") $whr ";
                } 
		else {  
						   
		    $query = " SELECT w.*, w.ouid sub_ouid ,
	                          bi1.InfoDesc corrective_title ,
			                  bi2.InfoDesc history_only_title ,	                          
	                          p.pfname ,
	                          p.plname ,
	                          p.PersonID ,
	                          concat(p.pfname ,' ',p.plname) fullname ,
	                          wt.title MainWtitle,
	                          wst.title wst_title,
	                          wst.time_limited,
	                          concat(wt.title ,' ',wst.title) wt_title ,
	                          bi4.InfoDesc emp_state_title ,
	                          bi5.InfoDesc educTitle ,
	                          bi6.InfoDesc SPTitle ,
	                          bi7.InfoDesc AETitle ,
	                          bi8.InfoDesc ModeTitle ,
	                          o.ptitle o_ptitle,	                         
	                          sf.sfid,
	                          sf.ptitle sf_ptitle,
	                          sb.sbid,
	                          sb.ptitle sb_ptitle ,
	                          parentu.ouid ,
	                          parentu.ptitle parentTitle ,
	                          po.title post_title ,
	                          po.post_no ,
	                          j.title job_title ,
	                          j.job_group ,
	                         
							  (w.cur_group - jf.start_group) + 1 job_category 

			           FROM HRM_persons p
	                        JOIN HRM_staff s ON(p.personid = s.personid)	                        
	                        INNER JOIN HRM_writs w ON (s.staff_id = w.staff_id )
                         
	                        LEFT JOIN BaseInfo bi1 ON ( bi1.InfoID = w.corrective  AND bi1.TypeID = 57)
	                        LEFT JOIN BaseInfo bi2 ON ( bi2.InfoID = w.history_only  AND bi2.TypeID = 57)	                       
	                        LEFT JOIN BaseInfo bi4 ON (bi4.InfoID = w.emp_state AND bi4.TypeID = 58)
	                        LEFT JOIN BaseInfo bi5 ON (bi5.InfoID = w.education_level AND bi5.TypeID = 56 )
	                        LEFT JOIN BaseInfo bi6 ON (bi6.InfoID = w.salary_pay_proc AND bi6.TypeID = 59 )
	                        LEFT JOIN BaseInfo bi7 ON (bi7.InfoID = w.annual_effect AND bi7.TypeID = 60 )
	                        LEFT OUTER JOIN BaseInfo bi8 ON ( bi8.InfoID = w.emp_mode AND bi8.TypeID = 61 )
	                        LEFT OUTER JOIN HRM_position po ON (w.post_id = po.post_id)
							LEFT OUTER JOIN HRM_job_fields jf ON (po.jfid = jf.jfid)
	                        LEFT OUTER JOIN HRM_writ_types wt ON ((w.writ_type_id = wt.writ_type_id) AND (w.person_type = wt.person_type))
	                        LEFT OUTER JOIN HRM_writ_subtypes wst ON ((w.writ_subtype_id = wst.writ_subtype_id) AND (w.writ_type_id = wst.writ_type_id) AND (w.person_type = wst.person_type))
	                        LEFT OUTER JOIN HRM_study_branchs sb ON ((w.sbid = sb.sbid) AND (w.sfid = sb.sfid))
	                        LEFT OUTER JOIN HRM_study_fields sf ON (w.sfid = sf.sfid)
	                        LEFT OUTER JOIN HRM_org_new_units o ON (o.ouid = w.ouid)
	                        LEFT OUTER JOIN HRM_org_new_units parentu ON (parentu.ouid = o.parent_ouid)	                        
	                        LEFT OUTER JOIN HRM_jobs j ON ( w.job_id = j.job_id )                       


					where (1=1) $whr
                    ";
		}

		$query .= ($where != "") ? " AND " . $where : "";
				           
		$temp = parent::runquery_fetchMode($query, $whereParam);
	
		return $temp;

	}

	static function LastID($pdo = "")
	{
		return PdoDataAccess::GetLastID("HRM_writs","writ_id","",array(), $pdo);
	}

	/**
	 * اين تابع براي يک کد شناسايي خواص چک مي کند که آيا حکم اصلاحي ناتمام وجود دارد يا خير
	 */
	private static function correct_is_not_completed($staff_id)
	{
				
		$query = "
			SELECT COUNT(*) rcount
			FROM HRM_writs
			WHERE correct_completed = " . WRIT_CORRECTING . " AND staff_id = " . $staff_id;



		$dt = PdoDataAccess::runquery($query);

		if($dt[0]["rcount"] > 0)
			return true;

		return false;
	}

	/**
	* سنوات خدمت شخص را به سال ، ماه و روز مشخص مي کند.
	* فرض بر اين است که سنوات خدمت حکم قبلي درست محاسبه شده است
	*
	* @param int $staff_id : اگر اين پارامتر فرستاده نشود بايد کد پرسنلي حتما فرستاده شود
	* @param int $personID : اگر اين پارامتر فرستاده نشود بايد کد شناسايي حتما فرستاده شود
	* @param ShamsiDate $toDate : سنوات خدمت را تا اين تاريخ مشخص مي کند
	*
	* @return array{"year"=>'',"month"=>'',"day"=>''}
	*/
	public function duty_year_month_day($staff_id = "", $personID = "", $toDate)
	{
		if($staff_id == "" && $personID = "")
		{
			parent::PushException("يکي از دو پارامتر staff_id و PersonID بايد فرستاده شود");
			return false;
		}
	   	$query = "select w.execute_date,
						 w.contract_start_date ,
						 w.contract_end_date ,
						 w.person_type ,
						 w.onduty_year ,
						 w.onduty_month ,
						 w.onduty_day ,
						 w.annual_effect
				from HRM_writs as w";

	   	if($personID != "")
	   		$query .= " join HRM_staff as s using(staff_id) where s.PersonID=" . $personID;

		else if($staff_id != "")
			$query .= " where w.staff_id = $staff_id";

		$query .= " AND (w.history_only != " . HISTORY_ONLY . " OR w.history_only is null) AND w.execute_date <= '$toDate'
						 order by w.execute_date DESC,w.writ_id DESC,w.writ_ver DESC
						 limit 1";

		$temp = PdoDataAccess::runquery($query);

		if(count($temp) == 0)
			return array("year" => 0,"month" => 0,"day" => 0);

		$writ_rec = $temp[0];

		$temp_duration = 0;

		if(DateModules::CompareDate($toDate, $writ_rec['execute_date'])>=0)
			$temp_duration = DateModules::GDateMinusGDate($toDate, $writ_rec['execute_date']);

		if ($writ_rec['annual_effect'] == HALF_COMPUTED)
			$temp_duration *= 0.5;
		else if ($writ_rec['annual_effect'] == DOUBLE_COMPUTED)
			$temp_duration *= 2;
		else if ($writ_rec['annual_effect'] == NOT_COMPUTED)
			$temp_duration = 0;

		$prev_writ_duration = DateModules::ymd_to_days($writ_rec['onduty_year'],$writ_rec['onduty_month'], $writ_rec['onduty_day']);

		$duration =  $prev_writ_duration + $temp_duration ;

		$return = array();
		DateModules::day_to_ymd($duration , $return['year'], $return['month'], $return['day']);

		return $return;
	}

	/**
	 *این تابع سنوات خدمت مربوط به رسته اصلی که در این حکم وجود دارد را محاسبه می کند .
	 */
	/* public function related_duty_years($staff_id , $toDate , $post_id , $action="INSERT")
	{
		$days = 0 ;
		if($post_id)
		{
			$query = "
				SELECT  jcid
				FROM position p
		        	LEFT OUTER JOIN job_fields jf
		            	ON p.jfid = jf.jfid
		        WHERE p.post_id = $post_id
			";
		    $temp = PdoDataAccess::runquery($query);
			$jcid = $temp[0]['jcid'];

			if($jcid)
			{
				$query = "
					SELECT
							w.writ_id ,
							w.writ_ver ,
							w.staff_id ,
							w.annual_effect ,
							js.jcid ,
							w.execute_date ,
							w.onduty_year ,
							w.onduty_month ,
							w.onduty_day
					FROM writs w
					LEFT OUTER JOIN position p ON w.post_id = p.post_id
			        LEFT OUTER JOIN job_fields jf ON p.jfid = jf.jfid
			        LEFT OUTER JOIN job_subcategory js ON jf.jsid = js.jsid
					WHERE
						(w.history_only=0 OR w.history_only IS NULL)
						AND w.execute_date <= '$toDate'
						AND w.person_type = 2 AND w.staff_id = $staff_id
					ORDER BY w.staff_id , w.execute_date , w.writ_id , w.writ_ver";

				$prior_writ_rec = null ;
				$writ_recSet = null ;
				$writ_recSet = PdoDataAccess::runquery($query);
				$coef = 0 ;
				for($i=0 ; $i<count($writ_recSet) ; $i++ ){
					$writ_rec = $writ_recSet[$i];
					if($action == "UPDATE" && $i == count($writ_recSet) - 1)
					{
						$writ_rec['annaual_effect'] = $annaual_effect ;
						$writ_rec['jcid'] = $jcid ;
					}
					if($prior_writ_rec && $jcid && $prior_writ_rec['jcid'] == $jcid)
					{
					    if ($prior_writ_rec['annual_effect'] == HALF_COMPUTED){
					        $coef = 0.5;
					    }
					    else if ($prior_writ_rec['annual_effect'] == DOUBLE_COMPUTED){
					        $coef = 2;
					    }
					    else if ($prior_writ_rec['annual_effect'] == NOT_COMPUTED){
					        $coef = 0;
					    }
					    else if ($prior_writ_rec['annual_effect'] == COMPUTED){
					    	$coef = 1 ;
					    }
					    $add_day = DateModules::GDateMinusGDate($writ_rec['execute_date'], $prior_writ_rec['execute_date']) * $coef;
					    $days += $add_day;
					}
					$prior_writ_rec = $writ_rec ;
				}
			    if($action == "INSERT"){
					if ($prior_writ_rec['annual_effect'] == HALF_COMPUTED){
				        $coef = 0.5;
					}
				    else if ($prior_writ_rec['annual_effect'] == DOUBLE_COMPUTED){
				        $coef= 2;
				    }
				    else if ($prior_writ_rec['annual_effect'] == NOT_COMPUTED){
				        $coef = 0;
				    }
				    else if ($prior_writ_rec['annual_effect'] == COMPUTED){
				    	$coef = 1 ;
				    }
				    if($jcid && $prior_writ_rec  && $prior_writ_rec['jcid'] == $jcid)
				    {
					    $add_day = DateModules::GDateMinusGDate($toDate, $prior_writ_rec['execute_date']) * $coef;
					    $days += $add_day;
				    }
			    }

				//افزودن سنوات خدمت اولين حکم
				if(count($writ_recSet)>0)
				{
					$days += DateModules::ymd_to_days($writ_recSet[0]['onduty_year'],
														$writ_recSet[0]['onduty_month'],
														$writ_recSet[0]['onduty_day']);
				}

				$days = round($days);
			}
		}
		$return = array();
		DateModules::day_to_ymd($days , $return['year'],$return['month'], $return['day']);
		return $return;
	} */

	private static function get_writ_edit_state($Gexecute_date = "", $person_type = "", $writ_id="", $writ_ver="", $staff_id="")
	{
		$stage1_end_date = OPEN_WRIT_WITHOUT_CALC_END_DATE ;
	    $stage2_end_date = OPEN_WRIT_WITH_CALC_END_DATE ;

		if($Gexecute_date == "")
	    {
		   	if($writ_ver == "")
		   	{
		   		$query = "SELECT MAX(writ_ver) writ_ver
					  FROM HRM_writs
					  WHERE writ_id = :wid AND staff_id = :stid";

		   		$temp = PdoDataAccess::runquery($query, array(":wid" => $writ_id, ":stid" => $staff_id));

				$writ_ver = $temp[0][0];

		  	}
		  	$query = "SELECT execute_date,person_type
					FROM HRM_writs
					WHERE writ_id = :wid AND writ_ver = :wver AND staff_id = :stid";

		  	$temp = PdoDataAccess::runquery($query, array(":wid" => $writ_id, ":wver" => $writ_ver , ":stid" => $staff_id));

echo PdoDataAccess::GetLatestQueryString(); die();

		    $Gexecute_date = $temp[0]["execute_date"];
			$person_type = $temp[0]["person_type"];
		}

		if($person_type == "")
		{
			//echo " نوع فرد مشخص نشده است .";
			return null;
		}

	 	if(($person_type == HR_PROFESSOR && DateModules::CompareDate($Gexecute_date,$stage1_end_date)>=0)||
		   ($person_type == HR_WORKER && DateModules::CompareDate($Gexecute_date,$stage1_end_date)>=0)||
		   ($person_type == HR_EMPLOYEE && DateModules::CompareDate($Gexecute_date,$stage1_end_date)>=0)||
		   ($person_type == HR_CONTRACT && DateModules::CompareDate($Gexecute_date,$stage1_end_date)>=0)||
		    $person_type == HR_RETIRED)
			$is_auto_writ = true ;
		else
			$is_auto_writ = false ;

		if(($person_type == HR_PROFESSOR && DateModules::CompareDate($Gexecute_date,$stage2_end_date)>=0)||
		   ($person_type == HR_WORKER && DateModules::CompareDate($Gexecute_date,$stage2_end_date)>=0) ||
		   ($person_type == HR_EMPLOYEE && DateModules::CompareDate($Gexecute_date,$stage2_end_date)>=0) ||
		   ($person_type == HR_CONTRACT && DateModules::CompareDate($Gexecute_date,$stage2_end_date)>=0) )
			$is_new_writ = true ;
		else
			$is_new_writ = false ;

		if(!$is_auto_writ && !$is_new_writ)
			return 0 ;
		if(!$is_auto_writ && $is_new_writ)
			return 1 ;
		if($is_auto_writ && !$is_new_writ)
			return 2 ;
		if($is_auto_writ && $is_new_writ)
			return 3 ;
	}

	static function check_for_use_in_pay_calc($writ_id , $writ_ver, $staff_id)
    {
    	/*$query = "SELECT GROUP_CONCAT(DISTINCT '&nbsp;',pay_year,'-',pay_month,'&nbsp;') use_list
                  FROM payment_writs
                  WHERE  writ_id = :wid AND writ_ver = :wver AND staff_id = :stid";*/
		$query = "SELECT distinct concat(pay_year,'-',pay_month) lst
                  FROM payment_writs
                  WHERE  writ_id = :wid AND writ_ver = :wver AND staff_id = :stid";
    	$temp = PdoDataAccess::runquery($query , array(":wid" => $writ_id, ":wver" => $writ_ver, ":stid" => $staff_id));

		$result = "";
		for($i=0; $i < count($temp); $i++)
			$result .= '&nbsp;' . $temp[$i]['lst'] . '&nbsp;,';
		
		if(count($temp) > 0)
			return substr ($result, 0, strlen($result)-1);
		else
    	    return null ;

    }

    /**
     * اين تابع شماره حکم و ورژن ان را مي گيرد و وضعيت ان حکم را برمي گرداند
	*/
	static function get_writ_state($writ_id, $writ_ver, $staff_id)
    {
     $query ="Select state from HRM_writs where writ_id=:wid AND writ_ver=:wver AND staff_id=:stid";

     $whereParam = array(":wid" => $writ_id, ":wver" => $writ_ver, ":stid" => $staff_id);
     $temp = PdoDataAccess::runquery($query, $whereParam);
     if(count($temp) > 0)
     	return $temp[0][0];

     return false;
    }

	/**
	 * آخرين حکم يک شخص تا يک تاريخ خاص را برمي گرداند
	 *
	 * @param  $staff_id
	 * @param miladiDate $execute_date
	 * @param  $salary_item_type_id : اگر اين پارامتر فرستاده شود حکم مذکور حتما شامل اين قلم مي باشد
	 * @param boolean $check_pay_date : اگر (true) باشد چک مي کند که تاريخ پرداخت قبل از تاريخ اجرا باشد
	 *
	 * @return manage_writ object
	 */
	static function get_last_writ_by_date($staff_id, $execute_date, $salary_item_type_id = null, $check_pay_date = false)
	{

		$where = ($check_pay_date) ? " pay_date <= '" . $execute_date . "' AND " : "";

	    $query = "	select *
			
	    		from writs w
	    		where staff_id=:staffid AND execute_date <= :exedate AND  w.emp_mode not in (  ".EMP_MODE_LEAVE_WITHOUT_SALARY.",".EMP_MODE_CONVEYANCE.",
                                                                                                       ".EMP_MODE_TEMPORARY_BREAK.",".EMP_MODE_PERMANENT_BREAK.",
                                                                                                       ".EMP_MODE_BREAKAWAY.','.EMP_MODE_RUSTICATION.",
                                                                                                       ".EMP_MODE_RETIRE.','.EMP_MODE_RE_BUY.",
                                                                                                       ".EMP_MODE_WITHOUT_SALARY." ) AND 
					(NOT( w.execute_date > '2013-02-18' and  w.execute_date < '2013-03-20' ))  AND  " .
					$where . "(history_only != " . HISTORY_ONLY . " OR history_only is null)";
		
		$query .= ($salary_item_type_id != null) ? "
				AND EXISTS(
				  SELECT DISTINCT wsi.writ_id , wsi.writ_ver
          		  FROM writ_salary_items wsi
          		  WHERE wsi.writ_id= w.writ_id AND
          		  		wsi.writ_ver = w.writ_ver AND
          		  		wsi.staff_id = w.staff_id AND
          		  		salary_item_type_id = " . $salary_item_type_id . ")" : "";

		$query .= " order by execute_date DESC,writ_id DESC,writ_ver DESC
					limit 1";

		$obj = new manage_writ();
		PdoDataAccess::FillObject($obj, $query, array(":staffid" => $staff_id, ":exedate" => $execute_date));
		 
	    return $obj;
	}

	static function get_last_writ_With_salry_before_date($staff_id, $execute_date)
	{

	    $query = "
			select *
			from writs
			where staff_id = ? and execute_date < ? and
				history_only != 1 and emp_mode not in (3,9,15)
			order by execute_date DESC , writ_id DESC , writ_ver DESC
			limit 1 ";

		$obj = new manage_writ();
						
		PdoDataAccess::FillObject($obj, $query, array($staff_id, $execute_date));

	    return $obj;
	}
	
	

	/**
	 *  آخرين حکم سال قبل فرد را نسبت به تاريخ فرستاده شده برمي گرداند
	 *
	 *  @return manage_writ object
	 */
	static function get_last_writ_of_prior_year($staff_id, $current_execute_date)
	{
		$this_writ_year = substr(DateModules::Miladi_to_Shamsi($current_execute_date), 0, 4);
		$one_year_ago = $this_writ_year - 1;
		$prior_writ = manage_writ::get_last_writ_by_date($staff_id, DateModules::Shamsi_to_Miladi($one_year_ago . "-12-30"));

		return $prior_writ;
	}

	/**
	 * استخراج حقوق مبنا مربوط به يك حكم خاص
	 *
	 * @param boolean $hortative : اگر false باشد حقوق مبنا را بدون گروه تشويقي محاسبه مي کند
	 *
	 * @return value
	 */
	static function get_base_salary($writ_id, $writ_ver, $staff_id, $person_type, $hortative = true)
	{
		if ($person_type == HR_PROFESSOR)
		{
			$obj = new manage_writ_item($writ_id, $writ_ver, $staff_id, SIT_PROFESSOR_BASE_SALARY);
		}
		else if ($person_type == HR_EMPLOYEE)
		{
			$obj = new manage_writ_item($writ_id, $writ_ver, $staff_id, SIT_STAFF_BASE_SALARY);
		}
		else if ($person_type == HR_WORKER)
		{
			$obj = new manage_writ_item($writ_id, $writ_ver, $staff_id, SIT3_BASE_SALARY);
		}
		else if ($person_type == HR_CONTRACT)
		{
			$obj = new manage_writ_item($writ_id, $writ_ver, $staff_id, SIT5_STAFF_BASE_SALARY);
		}
		if(!$hortative)
			return (!($obj->param4 > 0)) ? $obj->value : $obj->param4;

		if (!($obj->value > 0))
		{
			parent::PushException('BASE_SALARY_CALC_ERR');
			return false;
		}
		return $obj->value;
	}

	/*
	** روزهاي کارکرد شخص را در طول سال استخراج مي کند.
	*/
    public static function compute_year_work_days($start_date, $end_date, $staff_id){

        //احکام موجود در اين بازه تاريخي را استخراج مي کند.
		$query = " select wst.annual_effect,w.execute_date
                   from  writs  w
						INNER JOIN writ_subtypes  wst
                                      ON (w.writ_type_id = wst.writ_type_id AND
                                          w.writ_subtype_id = wst.writ_subtype_id AND
                                          w.person_type = wst.person_type)
                   where  execute_date >= '$start_date' AND
                          execute_date <= '$end_date'    AND
                    
                         (history_only !=".HISTORY_ONLY." OR history_only is null ) AND
                          staff_id = $staff_id
                   order by  execute_date  ";
        $writ_recSet = PdoDataAccess::runquery($query);
 
        $writ_duration = 0;
        $duration = 0;
        $prev_end_date = null;
		for($i=0; $i<count($writ_recSet); $i++)
		{
			if($i+1 < count($writ_recSet))
			{
				$writ_duration = DateModules::getDateDiff(
					DateModules::miladi_to_shamsi($writ_recSet[$i+1]['execute_date']),
					DateModules::miladi_to_shamsi($writ_recSet[$i]['execute_date']));
				if ($prev_end_date == $writ_recSet[$i]['execute_date'])
					$writ_duration--;
				$prev_end_date = $writ_recSet[$i+1]['execute_date'];
			}
			else
			{
				$writ_duration = DateModules::getDateDiff(
					DateModules::miladi_to_shamsi($end_date),
					DateModules::miladi_to_shamsi($writ_recSet[$i]['execute_date']));
				if ($prev_end_date == $writ_recSet[$i]['execute_date'])
					$writ_duration--;
			}
			switch ($writ_recSet[$i]['annual_effect'])
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

	/**
	 * چک مي کند که آيا نسخه جديدتري از حکم وجود دارد يا خير
	 *
	 * @param int $writ_id
	 * @param int $writ_ver
	 * @return boolean
	 */
	function writ_has_new_version()
	{
		$DT = parent::runquery("
				SELECT writ_id FROM HRM_writs
				WHERE writ_id=? AND writ_ver>? AND staff_id=?",array($this->writ_id, $this->writ_ver, $this->staff_id));
		return count($DT) == 0 ? false : true;
	}

    function prior_corrective_writ()
    {
     // echo $this->corrective."---"; die();
      if(!$this->corrective )
      { 
          $query = " UPDATE writs
                        SET history_only=0
                             WHERE(writ_id=".$this->writ_id." AND  staff_id = ".$this->staff_id." AND  writ_ver=".(($this->writ_ver)-1)." AND history_only = 1 ) ";
          parent::runquery($query);

          if(!manage_writ::RemoveWrit($this->writ_id, $this->writ_ver, $this->staff_id)){

             return false ; 
			 
			 }
      }
      else {

        $this->corrective_writ_id = $this->writ_id ;
    	$this->corrective_writ_ver = $this->writ_ver ;

            parent::runquery(" UPDATE writs
                                    SET correct_completed=".WRIT_CORRECTING."
                                        WHERE((corrective_writ_id=".$this->corrective_writ_id." AND corrective_writ_ver= ".$this->corrective_writ_ver." ) OR
                                              (writ_id= ".$this->corrective_writ_id." AND writ_ver=".$this->corrective_writ_ver." ) AND staff_id = ".$this->staff_id." )");

      }

      $query = " SELECT  writ_id ,
                         writ_ver ,
                         corrective ,
                         staff_id
                  FROM writs
                  WHERE
                        staff_id = $this->staff_id AND
                        ((corrective_writ_id = $this->corrective_writ_id AND
                          corrective_writ_ver = $this->corrective_writ_ver ) OR
                        ( writ_id = $this->corrective_writ_id AND
                          writ_ver = $this->corrective_writ_ver ) )
                  ORDER BY corrective , execute_date DESC , writ_id DESC , writ_ver DESC ";
      $temp = parent::runquery($query);

      if(count($temp) > 0 )
      {

          if($temp[0]['corrective'] == 1 )
          {
              parent::runquery("delete from writs where staff_id = ".$temp[0]['staff_id']." and writ_id =".$temp[0]['writ_id']." and writ_ver = ".$temp[0]['writ_ver'] );
              return 'Stop';
          }
          else
          {
               return $temp[0];
          }

      }

     }

     function Next_Corrective_Writ()
     {
        $query = "  SELECT  w.writ_id,
                            w.writ_ver,
                            w.staff_id,
                            w.execute_date,
                            w.corrective ,
                            s.person_type ,
                            w2.writ_ver upper_ver

                    FROM   staff s
                           LEFT OUTER JOIN writs w
                                ON (w.staff_id = s.staff_id )
                           LEFT OUTER JOIN writs w2
                                ON (w2.staff_id = s.staff_id AND w2.writ_id = w.writ_id AND w.writ_ver + 1 = w2.writ_ver )
                    WHERE
                           s.staff_id = $this->staff_id AND
                            (w.execute_date > '$this->execute_date' OR (w.execute_date = '$this->execute_date' AND w.writ_id > $this->writ_id) ) AND
                           ((w.history_only != ".HISTORY_ONLY." OR w.history_only IS NULL)OR
                            (w.writ_id=$this->corrective_writ_id AND w.writ_ver=$this->corrective_writ_ver))
                            AND (w2.writ_ver IS NULL OR w.history_only != ".HISTORY_ONLY." OR w.history_only IS NULL)
                     ORDER BY s.staff_id , w.execute_date,w.writ_id , w.writ_ver ";

         $temp = parent::runquery($query);


         if(count($temp) > 0 )
         {
            $state = 1 ;
            $next_writ_rec = $temp[0];

            for($i=0 ; $i < count($temp) ; $i++){

                if( $state == 2 )
                {
                    if( $temp[$i]['execute_date'] == $min_execute_date &&  $temp[$i]['corrective'] != 1 )
                        $next_writ_rec = $temp[$i];
                    else
                        break ;
                }

                if( $state == 1 )
                {
                    $min_execute_date = $temp[$i]['execute_date'] ;
                    $next_writ_rec = $temp[$i] ;
                    $state = 2 ;
                }

            }

         }


         if(!$next_writ_rec) {

            return false ;
         }
         $obj = new manage_writ($next_writ_rec['writ_id'],$next_writ_rec['writ_ver'],$next_writ_rec['staff_id']);


         if( $obj->writ_id > 0 )
         {
		 

             

             if(( $this->corrective_writ_id  != $obj->writ_id  ||
                  $this->corrective_writ_ver != $obj->writ_ver )|| $obj->corrective ==0){

                  $obj->history_only = 1;


            $qry3 = " select writ_id , writ_ver
                                from writs
                                       where writ_id = ".$obj->writ_id." and writ_ver = ".$obj->writ_ver." and
                                             staff_id =".$obj->staff_id." and state=".WRIT_PERSONAL;

            $tmp3 = parent::runquery($qry3);

            if(count($tmp3)> 0 )
                 $obj->EditWrit();
//............
$qry1 = " SELECT issue_date
    					        FROM writs
    					            WHERE writ_id = ".$this->corrective_writ_id." AND writ_ver= ".$this->corrective_writ_ver." AND staff_id = ".$this->staff_id ;

             $tmp1 = parent::runquery($qry1);

             $obj->issue_date = DateModules::shNow(); //$tmp1[0]['issue_date'];


             if ($obj->execute_date > $obj->issue_date ) {
                    $obj->pay_date   = $obj->execute_date ;
                } else {
                    $obj->pay_date   =  $obj->issue_date ;
                }

             $qry2 = " select last_writ_id , last_writ_ver
                                from staff where staff_id =".$obj->staff_id ;
             $tmp2 = parent::runquery($qry2);
//.............
	        $obj->history_only = 0;
	        //end
	        if(!$next_writ_rec['upper_ver'])
	            $obj->writ_ver ++;
	        else
	        {
	        	$qry4 = " SELECT MAX(writ_ver) writ_ver
                                FROM writs
                                        WHERE writ_id = ".$obj->writ_id ;

                $tmp4 = parent::runquery($qry4);

	        	$obj->writ_ver = $tmp4[0]['writ_ver'] + 1;
			}

                $obj->state = 1 ;
	        $obj->corrective = 0 ;
	        $obj->corrective_date    = $this->corrective_date;
	        $obj->corrective_writ_id  = $this->corrective_writ_id;
	        $obj->corrective_writ_ver = $this->corrective_writ_ver;
	        $obj->correct_completed = WRIT_CORRECTING ;
	            $qry5 = " select personid,staff_id from staff where staff_id =".$obj->staff_id ;
                $tmp5 = parent::runquery($qry5);
 
			$education_level_rec = manage_person_education::GetEducationLevelByDate($tmp5[0]['personid'], $obj->execute_date);
	        $obj->education_level   = $education_level_rec['max_education_level'];
        	$obj->sfid              = $education_level_rec['sfid'];
        	$obj->sbid              = $education_level_rec['sbid'];

            $where = " PersonID=" . $tmp5[0]['personid'] . "  AND
                      (dependency = 5 or dependency = 6) AND
				       birth_date <='" . $obj->execute_date . "'";

            $obj->children_count    = manage_person_dependency::CountDependency($where);
            $obj->included_children_count = manage_person_dependency::bail_count($tmp5[0]['personid'],$obj->person_type,$obj->execute_date,$obj->execute_date);

            $person_obj = new manage_person($tmp5[0]['personid']);
            $obj->marital_status  = $person_obj->marital_status;
			//......
			
			if($obj->person_type == HR_PROFESSOR && $obj->staff_id != '111551' )
        {
            $obj->writ_signature_post_owner = 'محمد کافی' ; 
			$obj->writ_signature_post_title = ' رئیس دانشگاه' ; 
            //$this->writ_signature_post_title = ' رئیس دانشگاه ';
        }
		else if($obj->person_type == HR_PROFESSOR && $obj->staff_id == '111551' )
        {
            //$this->writ_signature_post_owner = 'محمدجواد وریدی' ;
			$obj->writ_signature_post_owner = 'ابوالفضل باباخانی' ;
            $obj->writ_signature_post_title = 'معاون اداری ومالی دانشگاه';
        }
		else {
				$obj->writ_signature_post_owner = ($obj->issue_date > '2014-02-01') ? 'ابوالقاسم ساقی' : WRIT_SIGNATURE_POST_OWNER ;
			}
			
			//.......
			//$obj->writ_signature_post_owner = ($obj->issue_date > '2014-02-01') ? 'ابوالقاسم ساقی' : WRIT_SIGNATURE_POST_OWNER ;
			
            $pdo = parent::getPdoObject();
            $pdo->beginTransaction();

            if(!$obj->onBeforeInsert()){
                $pdo->rollBack();
                return false ;
            }

            $return = parent::insert("writs", $obj);

            if(!$return)
            {
                parent::PushException("ايجاد با شکست مواجه شد");
                $pdo->rollBack();
                return false;
            }

            if(!$obj->onAfterInsert()){

                 parent::PushException("ايجاد با شکست مواجه شد");
                $pdo->rollBack();
                return false;
            }

            $pdo->commit();

            if(!manage_writ_item::compute_writ_items($obj->writ_id, $obj->writ_ver , $obj->staff_id)){
					
                 return false ;
            }


        }
        else
        {   
		
			$description = "";
            $this->writ_id = $obj->writ_id;
            $this->writ_ver = $obj->writ_ver;

            $qry3 = " SELECT w.writ_id , w.writ_ver , w.execute_date , w.issue_date ,  ws.title
                            FROM writs w
                                INNER JOIN writ_subtypes ws ON ws.writ_type_id = w.writ_type_id
                                    AND ws.writ_subtype_id = w.writ_subtype_id AND w.person_type = ws.person_type
                                    AND w.staff_id = ".$obj->staff_id."
                                INNER JOIN
                                (
                                SELECT
                                    writ_id , writ_ver - 1 writ_ver2
                                FROM writs
                                WHERE
                                    corrective_writ_id = $this->corrective_writ_id AND corrective_writ_ver = $this->corrective_writ_ver AND  staff_id = ".$obj->staff_id."
                                )
                                w2
                                    ON w.writ_id = w2.writ_id AND w.writ_ver = w2.writ_ver2";

             $tmp3 = parent::runquery($qry3);

             $qry4 = " SELECT w.writ_id , w.writ_ver , w.execute_date , w.issue_date ,  ws.title
                            FROM writs w
                                INNER JOIN writ_subtypes ws
                                        ON ws.writ_type_id = w.writ_type_id
                                                AND ws.writ_subtype_id = w.writ_subtype_id AND w.person_type = ws.person_type
                                                AND w.staff_id = $obj->staff_id
                                WHERE
                                    corrective_writ_id = $this->corrective_writ_id AND corrective_writ_ver = $this->corrective_writ_ver AND
                                    w.writ_ver = 1 AND staff_id =".$obj->staff_id ;

             $tmp4 = parent::runquery($qry4);
             $i= 0;
       		 $j= 0;

       		if(!empty($tmp4[0]['send_letter_no'])){
				 $description.='بر اساس حکم شماره '.$tmp4[0]['send_letter_no'].' مورخه '.
                                DateModules::miladi_to_shamsi($tmp4[0]['issue_date']).' احکام ذيل اصلاح مي گردد : '.chr(13);
			}
			else
			{
   				$description.= 'ليست احکام اصلاح شده : '.chr(13);
			}
            for($i=0 ; $i < count($tmp3) ; $i++)
            {
                $description.= ($i+1).'- حکم '.$tmp3[$i]['title'].' شماره '.$tmp3[$i]['send_letter_no'].' مورخه '.DateModules::miladi_to_shamsi($tmp3[$i]['issue_date']).chr(13);
            }

                 parent::runquery(" UPDATE writs
                                         SET correct_completed=".WRIT_CORRECT_COMPLETED."
                                    WHERE(
                                        (corrective_writ_id=$this->corrective_writ_id AND corrective_writ_ver=$this->corrective_writ_ver)
                                            OR(writ_id=$this->corrective_writ_id AND writ_ver=$this->corrective_writ_ver AND staff_id = $this->staff_id )
                                    )");

                 parent::runquery(" UPDATE writs
                                        SET description = '".$description."'
                                        WHERE  writ_id = $this->writ_id AND writ_ver=$this->writ_ver AND description IS NULL AND staff_id =".$this->staff_id );


        }

             return $obj ;
         }

     }

}

?>
