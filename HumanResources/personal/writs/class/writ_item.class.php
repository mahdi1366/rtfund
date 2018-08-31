<?

//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------
$address_prefix = getenv("DOCUMENT_ROOT");
require_once  $address_prefix . "/HumanResources/organization/positions/post.class.php" ; 
require_once $address_prefix .'/HumanResources/salary/salary_params/class/salary_params.class.php';
require_once $address_prefix .'/HumanResources/baseInfo/class/salary_item_types.class.php';
//require_once $address_prefix .'/HumanResources/personal/writs/writ.class.php';
require_once $address_prefix ."/HumanResources/personal/persons/class/devotion.class.php";
require_once $address_prefix ."/HumanResources/personal/persons/class/education.class.php";
require_once $address_prefix ."/HumanResources/baseInfo/class/city.class.php"; 
 
 
define("MONTH_DAY_COUNT", 30);

class manage_writ_item extends PdoDataAccess {

	public $writ_id;
	public $writ_ver;
	public $staff_id;
	public $salary_item_type_id;
	public $param1;
	public $param2;
	public $param3;
	public $param4;
	public $value;
	public $automatic;
	public $remember_date;
	public $remember_message;
	public $remembered;	
	public $must_pay;
	public $base_value;
	public $edit_reason;
	public $param5;
	public $param6;
	public $param7;

	private function OnBeforeInsert() {
						
		$query = "select    w.writ_id,
                            w.writ_ver,
                            w.execute_date,
                            w.staff_id,
                            s.personID,
                            w.person_type,                           
                            w.cur_group,
                            w.education_level,
                            w.onduty_year,
                            w.onduty_month,
                            w.onduty_day,                           
                            w.family_responsible,
                            w.included_children_count,
                            w.post_id,
							j.job_group,
                            w.emp_mode,
                            w.emp_state,       
							w.job_id,
							w.marital_status,
                            p.PersonID,
                            p.military_duration,
							p.military_duration_day,
                            p.birth_city_id,
                            p.locality_type,
							p.sex, 
                            s.last_retired_pay ,
                            sit.salary_item_type_id,
                            sit.salary_compute_type,
                            sit.multiplicand,
                            sit.function_name,
                            sit.user_data_entry,
                            sit.editable_value,
			    s.work_start_date ,
			    bf.param1 master_education_level,			    
			    sit.person_type as sp_person_type

				from HRM_writs w
					inner join HRM_staff s on s.staff_id = w.staff_id
					inner join HRM_persons p on p.personid = s.personid and p.person_type = s.person_type 
					join BaseInfo bf on(bf.TypeID=56 AND bf.InfoID=w.education_level)
					left join BSC_posts j on(j.PostID=w.post_id),HRM_salary_item_types sit

				where w.writ_id = " . $this->writ_id . " and w.writ_ver = " . $this->writ_ver . " and w.staff_id =" . $this->staff_id . " and
					  sit.salary_item_type_id = " . $this->salary_item_type_id;


		$baseInfo = parent::runquery($query);


			
		if (count($baseInfo) == 0)
			return true;

		// ----- در صورتی که حکم خودکار است روال مربوط به محاسبه قلم فراخوانی می شود ------------------

		//if (manage_writ::is_auto_writ($baseInfo[0]['execute_date'], $baseInfo[0]['person_type']) !== false) {

			$value = $this->calculate_writ_item_value($baseInfo[0]);

						
			if ($value === false) {
								
				return false;
			}

			if (($baseInfo[0]['user_data_entry'] == USER_DATA_ENTRY || $baseInfo[0]['editable_value'] ) &&
					isset($_POST['isset_by_user']) && $this->value > 0) {
				$this->base_value = $value;
			} else {
				$this->base_value = $value;
				$this->value = $value;
			}
		//}
		


		return true;
	}

	private function OnBeforeUpdate() {


		if (isset($_REQUEST['onlyUpdateRemembred'])) {
			unset($this->value);
			return true;
		}
		return $this->OnBeforeInsert();
	}

	private function OnAfterUpdate() {
		$return = self::compute_writ_items($this->writ_id, $this->writ_ver, $this->staff_id, true);

		if ($return === false) {
			return false;
		}

		return true;
	}

	function __construct($writ_id = "", $writ_ver = "", $staff_id = "", $salary_item_type_id = "", $newItem = "") {
		if (!empty($writ_id) && !empty($writ_ver) && !empty($staff_id) && !empty($salary_item_type_id)) {
			$query = "select * from HRM_writ_salary_items where writ_id=" . $writ_id .
					" AND writ_ver=" . $writ_ver . " AND staff_id = " . $staff_id . " AND salary_item_type_id=" . $salary_item_type_id;

			PdoDataAccess::FillObject($this, $query);
		}
		if ($newItem == 'new') {
			$this->must_pay = 1;
		}
	}

	static function GetAllWritItems($where = "", $whereParam = array()) {
		$query = " select wsi.writ_id,
       					  wsi.writ_ver,
						  wsi.staff_id,
                          wsi.salary_item_type_id,
                          wsi.param1,
                          wsi.param2,
                          wsi.param3,
                          wsi.value,
                          wsi.automatic,
                          wsi.remember_date,
                          wsi.remember_message,
                          s.person_type,
                          s.user_defined,
                          s.effect_type,
                          s.print_title,
                          s.compute_place,
                          s.user_data_entry,
                          s.salary_compute_type,
                          s.multiplicand,
                          s.function_name,
                          s.param1_title,
                          s.param1_input,
                          s.param2_title,
                          s.param2_input,
                          s.param3_title,
                          s.param3_input,
                          s.full_title ,
						  wsi.must_pay
                     from HRM_writ_salary_items wsi
                             INNER JOIN HRM_salary_item_types s ON (wsi.salary_item_type_id = s.salary_item_type_id )  
                                                              
                     where 1=1  
			          ";
		$query .= ($where != "") ? " AND " . $where : "";
		$query .= " order by s.print_order";

		$temp = parent::runquery($query, $whereParam);


		return $temp;
	}

	static function Count($where = "", $whereParam = array()) {
		$query = " select count(*) 
                   from writ_salary_items wsi
                            INNER JOIN salary_item_types s
                                 ON (wsi.salary_item_type_id = s.salary_item_type_id )  
			       
			     ";
		$query .= ($where != "") ? " where " . $where : "";

		$temp = parent::runquery($query, $whereParam);
		return $temp[0][0];
	}

///------------------------------------------------------------------------------------------	
	function AddWritItem() {	

		$return = $this->OnBeforeInsert();		

		if ($return === false){ echo $this->salary_item_type_id ; 
			return false; 
}

		if ($this->value != 0)
			$return = parent::insert("HRM_writ_salary_items", $this);

		if (!$return) {
			parent::PushException("ايجاد با شکست مواجه شد");
			return false;
		}
		$obj = new DataAudit();
		$obj->MainObjectID = $this->writ_id . '-' . $this->writ_ver;
		$obj->SubObjectID = $this->salary_item_type_id;
		$obj->TableName = "writ_salary_items";
		$obj->ActionType = DataAudit::Action_add;		
		$obj->execute();

		return true;
	}

	function replaceWritItem() {


		$return = $this->OnBeforeUpdate();

		if (!$return) {
			parent::PushException("ویرایش با شکست مواجه شد .");
			return false;
		}
		if(empty($this->remember_date))
			$this->remember_date = '0000-00-00' ; 
		
		parent::replace("HRM_writ_salary_items", $this);
					
		
		$obj = new DataAudit();
		$obj->MainObjectID = $this->writ_id . '-' . $this->writ_ver;
		$obj->SubObjectID = $this->salary_item_type_id;
		$obj->TableName = "HRM_writ_salary_items";
		$obj->ActionType = DataAudit::Action_replace;		
		$obj->execute();
	}

	function EditWritItem() {
		$return = $this->OnBeforeUpdate();
		if ($return === false)
			return false;

		$where = "writ_id=:wid AND writ_ver=:wver AND staff_id = :stid AND salary_item_type_id=:sid";

		$return = parent::update("HRM_writ_salary_items", $this, $where, array(":wid" => $this->writ_id,
					":wver" => $this->writ_ver,
					":stid" => $this->staff_id,
					":sid" => $this->salary_item_type_id));

		if (!$return)
			return false;

		/* $ret = $this->OnAfterUpdate();

		  if(!$ret)
		  return false ; */

		$obj = new DataAudit();
		$obj->MainObjectID = $this->writ_id . '-' . $this->writ_ver;
		$obj->SubObjectID = $this->salary_item_type_id;
		$obj->TableName = "writ_salary_items";
		$obj->ActionType = DataAudit::Action_update;
		$obj->RelatedPersonType = 3;
		$obj->RelatedPersonID = $this->staff_id;
		$obj->execute();

		return true;
	}

	function DontPayItems($WID, $WVER, $STID) {

		$query = " update writ_salary_items set  must_pay = 0
							where  writ_id = :WID and writ_ver = :WVER and staff_id = :SID ";

		parent::runquery($query, array(":WID" => $WID, ":WVER" => $WVER, ":SID" => $STID));

		$obj = new DataAudit();
		$obj->MainObjectID = $WID . '-' . $WVER;
		$obj->TableName = "writ_salary_items";
		$obj->ActionType = DataAudit::Action_update;
		$obj->RelatedPersonType = DataAudit::PersonType_staff;
		$obj->RelatedPersonID = $STID;
		$obj->execute();

		return true;
	}

	static function RemoveWritItem($where, $whereParam = array()) {
		$ret = PdoDataAccess::delete("HRM_writ_salary_items", $where, $whereParam);
		if ($ret) {
			$obj = new DataAudit();
			$obj->TableName = "HRM_writ_salary_items";
			$obj->ActionType = DataAudit::Action_delete;
			$obj->description = $where . "[" . implode(",", $whereParam) . "]";
			$obj->execute();
			return true;
		}
		return false;
	}

	//<editor-fold defaultstate="collapsed" desc="** توابع مربوط به محاسبه اقلام حقوقی **">

	/**
	 * اين تابع قلمهاي مربوط به يک حکم را محاسبه مي نمايد
	 * 
	 */
	public static function compute_writ_items($writ_id, $writ_ver, $staff_id, $reComputeFlag = false) {
	
		//__________________________________________
		//کنترل معتبر بودن کد و نسخه حکم
		$curWrit = new manage_writ($writ_id, $writ_ver, $staff_id);
				
		if (empty($curWrit->writ_id)) {
			parent::PushException(WRIT_NOT_FOUND);
			return false;
		}

 
		if ($curWrit->salary_pay_proc != 3 ) {
	
			if (!$reComputeFlag) {
				
							//__________________________________________
				//first delete all current rows
				parent::runquery(" delete from HRM_writ_salary_items
													where writ_id=$writ_id AND writ_ver=$writ_ver AND staff_id= $staff_id");
	
				//__________________________________________
				//copy all of prior writ items to this writ
				$prior_writ_Obj = $curWrit->get_prior_writ("","");
				
					
				if (!empty($prior_writ_Obj->writ_id)) { 
					
										
					$query = "SELECT    wsi.salary_item_type_id,
													wsi.param1,
													wsi.param2,
													wsi.param3,
													wsi.param4,
													wsi.param5,
													wsi.param6,
													wsi.param7,
													wsi.value,
													wsi.base_value,
													wsi.automatic,
													wsi.remember_date,
													wsi.remember_message,
													wsi.remembered


											FROM    HRM_writ_salary_items  wsi
														LEFT OUTER JOIN HRM_salary_item_types  sit
															 ON (wsi.salary_item_type_id = sit.salary_item_type_id)

											WHERE   writ_id = " . $prior_writ_Obj->writ_id . " AND
													writ_ver = " . $prior_writ_Obj->writ_ver . "  AND
													staff_id = " . $prior_writ_Obj->staff_id . " AND
													validity_start_date <= '" . $curWrit->execute_date . "'AND
												   (validity_end_date >= '" . $curWrit->execute_date . "' OR
													validity_end_date IS NULL OR validity_end_date = '0000-00-00' )

											ORDER BY ComputeOrder ";

					$all_items = PdoDataAccess::runquery_fetchMode($query);
				
					// $all_items = parent::runquery($query );
					//______________________________________________________________
					//
					//
				
					//درج قلم هاي حکم قبلي براي اين حکم
							
					for ($i = 0; $i < $all_items->rowCount(); $i++) {

						$ItemRow = $all_items->fetch();
						$current_itemRecord = $ItemRow;
						$wsi_object = new manage_writ_item();
						parent::FillObjectByArray($wsi_object, $current_itemRecord);

						$wsi_object->writ_id = $curWrit->writ_id;
						$wsi_object->writ_ver = $curWrit->writ_ver;
						$wsi_object->staff_id = $curWrit->staff_id;
					  
						$return = $wsi_object->AddWritItem();

						if ($return === false) {
							
							return false;
						}
					}
					
					
				}
				
				
			}
 		
			

			$WsiObj = new manage_writ_item();
				 			 
			$return = $WsiObj->compute_automatic_writ_salary_items($curWrit);
		
			if ($return === false) {			

				return false;
			}

			$WsiObj->compute_semi_automatic_writ_salary_items($curWrit);
					
			
			//______________________________________________________________
			//بر اساس روال پرداخت حقوق نحوه پرداخت اقلام حقوقي کنترل مي شود

			require_once 'writ_subtype.class.php';
			$writTypes_obj = new manage_writ_subType($curWrit->person_type, $curWrit->writ_type_id, $curWrit->writ_subtype_id);
			if (!$curWrit->salary_pay_proc)
				$curWrit->salary_pay_proc = $writTypes_obj->salary_pay_proc;

			if ($curWrit->salary_pay_proc) {
				switch ($curWrit->salary_pay_proc) {
					case BENEFIT_CUT :
						if (!manage_writ_item::RemoveWritItem(" writ_id=" . $curWrit->writ_id . " AND 
																   writ_ver=" . $curWrit->writ_ver . " AND
																   staff_id=" . $curWrit->staff_id)) {
							parent::PushException(CAN_NOT_DELETE_SALARY_ITEMS);
							return false;
						}
						break;

					case ONLY_BASE_SALARY_PAY :
						switch ($curWrit->person_type) {
							case HR_PROFESSOR :
								$salary_item_type_id = SIT_PROFESSOR_BASE_SALARY;
								break;
							case HR_EMPLOYEE :
								$salary_item_type_id = SIT_STAFF_BASE_SALARY;
								break;
							case HR_WORKER :
								$salary_item_type_id = SIT_WORKER_BASE_SALARY;
								break;
						}
						if (!manage_writ_item::RemoveWritItem("writ_id=" . $curWrit->writ_id .
										" AND writ_ver=" . $curWrit->writ_ver .
										" AND salary_item_type_id != " . $salary_item_type_id . "
																   AND staff_id = " . $staff_id)) {
							parent::PushException(CAN_NOT_DELETE_SALARY_ITEMS);
							return false;
						}
						break;

					case ONLY_CONTINUES_SALARY_PAY :
						$dt = manage_writ_item::get_continouse_salary_items($curWrit->writ_id, $curWrit->writ_ver, $curWrit->staff_id);

						$items = "";
						for ($i = 0; $i < count($dt); $i++)
							$items .= $dt[$i]["salary_item_type_id"] . ",";

						$items = ($items != "") ? substr($items, 0, strlen($items) - 1) : "";
						//........................................
						if ($items != "") {
							if (!manage_writ_item::RemoveWritItem("	writ_id =" . $curWrit->writ_id . " AND 
																		writ_ver =" . $writ_ver . " AND 
																		staff_id =" . $curWrit->staff_id . " AND 
																		salary_item_type_id NOT IN (" . $items . ")")) {
								parent::PushException(CAN_NOT_DELETE_SALARY_ITEMS);
								return false;
							}
						}
						break;

					case CONTINUES_SALARY_RETIRED_HALF_BENEFIT_PAY :
					case CONTINUES_SALARY_HALF_BENEFIT_RETIRED_PAY :
					case CONTINUES_SALARY_HALF_BENEFIT_FRACTION_PAY :
						$dt = manage_writ_item::get_continouse_salary_items($curWrit->writ_id, $curWrit->writ_ver, $curWrit->staff_id); 
						$items = "";
						for ($i = 0; $i < count($dt); $i++)
							$items .= $dt[$i]["salary_item_type_id"] . ","; 

						$items = ($items != "") ? substr($items, 0, strlen($items) - 1) : "";
						//........................................
						if ($items != "") {

							$query = "update HRM_writ_salary_items set value = value * 0.5 
											where writ_id = " . $curWrit->writ_id . " AND 
												  writ_ver = " . $curWrit->writ_ver . " AND
												  staff_id = " . $curWrit->staff_id . " AND
												  salary_item_type_id in (" . $items . ")";

							parent::runquery($query);
						}
						break;

					case BENEFIT_PAY :
					case BENEFIT_WITHOUT_EXTRAWORK :
					case BENEFIT_EXIT_IN_WRIT_NOT_EXIST_IN_PAYMENT :
						break;
				}
			}

			if ($curWrit->worktime_type == HALF_TIME)
				$WsiObj->compute_half_time_salary_items($curWrit->corrective_writ_id, $curWrit->corrective_writ_ver, $curWrit->execute_date);
		
			if ($curWrit->worktime_type == QUARTER_TIME)
				$WsiObj->compute_quarter_time_salary_items($curWrit->corrective_writ_id, $curWrit->corrective_writ_ver, $curWrit->execute_date);
		}

		//__________________________________________
		//اعمال تغييرات با توجه به ايجاد و کامل شدن قلم هاي حکم جديد
		$is_auto_writ = $curWrit->is_auto_writ($curWrit->execute_date, $curWrit->person_type);
		if ($is_auto_writ)
			manage_writ::change_writ_state($curWrit->state, WRIT_PERSONAL, $curWrit->writ_id, $curWrit->writ_ver, $curWrit->staff_id, $curWrit->execute_date);
		else {
			if ($curWrit->history_only != 1)
				manage_writ::change_writ_state($curWrit->state, $curWrit->state, $curWrit->writ_id, $curWrit->writ_ver, $curWrit->staff_id, $curWrit->execute_date);
		}
				
		return true;
	}

	private function calculate_writ_item_value($writ_rec) {
		
		
	    	    
		switch ($writ_rec["salary_compute_type"]) {
			case SALARY_COMPUTE_TYPE_CONSTANT :
				$value = $this->value;
				break;

			case SALARY_COMPUTE_TYPE_MULTIPLY :
				$this->param2 = manage_writ_item::call_multiplicand_modules($writ_rec);
				$value = $this->param1 * $this->param2;
				break;

			case SALARY_COMPUTE_TYPE_FUNCTION :
				$functionName = $writ_rec["function_name"];

				$value = manage_writ_item::$functionName($writ_rec);

				if ($value === false)
					return false;
				break;

			default:
				$value = 0;
		}

		return $value;
	}

	static function Get_professor_base_number($sience_level) {
		switch ($sience_level) {
			case 1: return 90;
			case 2: return 100;
			case 3: return 125;
			case 4: return 145;
			case 5: return 170;
		}
		return "";
	}

	static function Get_employee_base_number($worker_emp_group) {
		switch ($worker_emp_group) {
			case 1 : return 400;
			case 2 : return 450;
			case 3 : return 500;
			case 4 : return 560;
			case 5 : return 620;
			case 6 : return 680;
			case 7 : return 740;
			case 8 : return 810;
			case 9 : return 880;
			case 10 : return 950;
			case 11 : return 1020;
			case 12 : return 1090;
			case 13 : return 1160;
			case 14 : return 1230;
			case 15 : return 1300;
			case 16 : return 1370;
			case 17 : return 1440;
			case 18 : return 1510;
			case 19 : return 1580;
			case 20 : return 1650;
		}
		return "";
	}

	static function get_writSalaryItem_value($writ_id, $writ_ver, $staff_id, $writ_salary_item_type_id, $baseValue = false) {
		$obj = new manage_writ_item($writ_id, $writ_ver, $staff_id, $writ_salary_item_type_id);

		if ($baseValue)
			return empty($obj->base_value) ? 0 : $obj->base_value;
		else
			return empty($obj->value) ? 0 : $obj->value;
	}

	/**
	 * @param manage_writ $cur_writ
	 */
	function compute_automatic_writ_salary_items($cur_writ,$t="") {
		
		$query = "  select * from HRM_salary_item_types
					WHERE person_type in ( 3 ) AND  				     

						  compute_place = " . SALARY_ITEM_COMPUTE_PLACE_WRIT . " AND 
					      salary_compute_type = " . SALARY_COMPUTE_TYPE_FUNCTION . " AND 
					      user_data_entry = " . AUTOMATIC . " AND
					      validity_start_date <= '" . $cur_writ->execute_date . "' AND 
					     (validity_end_date >= '" . $cur_writ->execute_date . "' OR 
						  validity_end_date IS NULL OR validity_end_date ='0000-00-00' )
					ORDER BY ComputeOrder "; 
	
		$salary_items_DT = parent::runquery_fetchMode($query);	


	  				
		for ($i = 0; $i < $salary_items_DT->rowCount(); $i++) {
		    
			$salary_items_row = $salary_items_DT->fetch();

			$query = "SELECT    w.writ_id,
								w.writ_ver,
								w.execute_date,
								w.staff_id,
								s.personID,
								w.person_type,
								w.cur_group,
								w.education_level,
								w.onduty_year,
								w.onduty_month,
								w.onduty_day,
								w.family_responsible,
								w.included_children_count,
								w.post_id,
								w.emp_mode,
								w.emp_state,
								w.job_id,
								j.job_group,
								w.marital_status,
								p.PersonID,p.sex , p.military_duration_day,
								p.military_duration,
								s.last_retired_pay,
								sit.salary_item_type_id,
								wsi.param1,
								wsi.param2,
								wsi.param3,
								wsi.param4,
								wsi.param5,
								wsi.param6,
								wsi.param7,
								wsi.value,
								wsi.automatic,
								wsi.remember_date,
								wsi.remember_message,
								wsi.remembered,
								sit.salary_compute_type,
								sit.multiplicand,
								sit.function_name,
								sit.user_data_entry,
								sit.editable_value,
								s.work_start_date,
								bf.param1 master_education_level,
								sit.person_type as sp_person_type

                 FROM    HRM_writs as w 
                			inner join HRM_staff as s on(w.staff_id = s.staff_id)
							left join BSC_posts j on(j.PostID=w.post_id)
                			inner join HRM_persons as p on(s.PersonID = p.PersonID)
							join BaseInfo bf on(bf.TypeID=56 AND bf.InfoID=w.education_level)
							join HRM_salary_item_types sit
				            left join HRM_writ_salary_items  wsi on(sit.salary_item_type_id=wsi.salary_item_type_id  AND
																	wsi.writ_id=w.writ_id AND wsi.writ_ver=w.writ_ver AND
																	wsi.staff_id=w.staff_id)
                        	
                WHERE   w.writ_id= " . $cur_writ->writ_id . " and 
                		w.writ_ver=" . $cur_writ->writ_ver . " and
                		w.staff_id =" . $cur_writ->staff_id . " and 
                		sit.salary_item_type_id = " . $salary_items_row["salary_item_type_id"];

			$dt = parent::runquery_fetchMode($query);		


						
			if ($dt->rowCount() > 0)
				$wsi_record = $dt->fetch();
		
			else
				continue;

			$this->writ_id = $cur_writ->writ_id;
			$this->writ_ver = $cur_writ->writ_ver;
			$this->staff_id = $cur_writ->staff_id;
			$this->salary_item_type_id = $salary_items_row["salary_item_type_id"];
			$this->automatic = 1;


			$this->remember_date = '0000-00-00';
			$this->remember_message = $salary_items_row['remember_message'];

			$function_name = $salary_items_row['function_name'];	
				
	
			$this->value = manage_writ_item::$function_name($wsi_record);
					
			if ($this->value === false){				
				
				return false;
				
				}

			$this->param1 = $wsi_record["param1"];
			$this->param2 = $wsi_record["param2"];
			$this->param3 = $wsi_record["param3"];
			$this->param4 = $wsi_record["param4"];
			$this->param5 = $wsi_record["param5"];
			$this->param6 = $wsi_record["param6"];
			$this->param7 = $wsi_record["param7"];


			if ($this->value > 0) { 
				$this->replaceWritItem();


			}
			else
				manage_writ_item::RemoveWritItem(" writ_id = " . $this->writ_id . " AND 
												   writ_ver = " . $this->writ_ver . " AND 
												   staff_id = " . $this->staff_id . " AND 
												   salary_item_type_id = " . $this->salary_item_type_id);
		}

		return true;
	}

	private function compute_semi_automatic_writ_salary_items($cur_writ) {
		$query = "SELECT    wsi.writ_id,
				            wsi.writ_ver,
				            wsi.staff_id,
				            wsi.salary_item_type_id,
				            wsi.param1,
				            wsi.param2,
				            wsi.param3,
				            wsi.param4,
				            wsi.param5,
				            wsi.param6,
				            wsi.param7,				            
				            wsi.value,
				            wsi.automatic,
				            wsi.remember_date,
				            wsi.remember_message,
				            sit.salary_compute_type,
				    		sit.multiplicand,
				            sit.function_name
			            
			    FROM 	HRM_writ_salary_items wsi
			         	LEFT OUTER JOIN HRM_salary_item_types sit ON (wsi.salary_item_type_id = sit.salary_item_type_id)
			         	
			    WHERE 	(sit.user_data_entry = 1) AND
			            wsi.writ_id = " . $cur_writ->writ_id . "  AND
			            wsi.writ_ver = " . $cur_writ->writ_ver . " AND 
			            wsi.staff_id = " . $cur_writ->staff_id . "";

		$dt = parent::runquery($query);
		for ($i = 0; $i < count($dt); $i++) {

			$this->writ_id = $dt[$i]["writ_id"];
			$this->writ_ver = $dt[$i]["writ_ver"];
			$this->salary_item_type_id = $dt[$i]["salary_item_type_id"];
			$this->staff_id = $dt[$i]["staff_id"];

			$this->param1 = $dt[$i]['param1'];
			$this->param2 = $dt[$i]['param2'];
			$this->param3 = $dt[$i]['param3'];
			$this->param4 = $dt[$i]['param4'];
			$this->param5 = $dt[$i]['param5'];
			$this->param6 = $dt[$i]['param6'];
			$this->param7 = $dt[$i]['param7'];
			$this->value = $dt[$i]['value'];
			$this->automatic = $dt[$i]['automatic'];
			$this->remember_date = $dt[$i]['remember_date'];
			$this->remember_message = $dt[$i]['remember_message'];

			if ($this->value > 0)
				$this->replaceWritItem();
		}

		return true;
	}

	/*	 * در صورت خدمت نيمه وقت افراد اقلام حقوقي مورد نظر
	 * بر اساس قوانين محاسبه مي شوند.
	 */

	private function compute_half_time_salary_items($corrective_writ_id = "", $corrective_writ_ver = "", $execute_date = "") {
		$sql = 'SELECT wsi.* , sit.salary_compute_type
	    		FROM   HRM_writ_salary_items wsi
	    			INNER JOIN HRM_salary_item_types sit
	    				ON wsi.salary_item_type_id = sit.salary_item_type_id
	            WHERE  writ_id = ' . $this->writ_id . ' AND 
	            	   writ_ver = ' . $this->writ_ver . ' AND 
	            	   staff_id = ' . $this->staff_id . '
	            	   
	            ORDER BY salary_item_type_id';

		$dt = PdoDataAccess::runquery($sql);

		for ($i = 0; $i < count($dt); $i++) {
			switch ($dt[$i]["salary_item_type_id"]) {
				//حق عائله مندي
				case SIT_STAFF_CHILD_RIGHT :
				case SIT_WORKER_CHILD_RIGHT :
				case SIT_PROFESSOR_CHILD_RIGHT :
					break;
				//حق اولاد
				case SIT_STAFF_CHILDREN_RIGHT :
				case SIT_PROFESSOR_CHILDREN_RIGHT :
					break;
				//فوق العاده محل خدمت
				case SIT_STAFF_DUTY_LOCATION_EXTRA :
					break;
				//فوق العاده سختي کار
				case SIT_STAFF_HARD_WORK_EXTRA :
					break;
				//فوق العاده بدي آب و هوا
				case SIT_STAFF_BAD_WEATHER_EXTRA :
				case SIT_PROFESSOR_BAD_WEATHER_EXTRA :
				case SIT_STAFF_BAD_WEATHER_EXTRA :
					break;

				//فوق العاده نوبت کاري
				case SIT_STAFF_SHIFT_EXTRA :
					break;

				default:
					if ($dt[$i]["salary_compute_type"] == SALARY_COMPUTE_TYPE_FUNCTION) {
					    
					    
						$query = "update HRM_writ_salary_items set value = value * 0.5
										where writ_id=" . $this->writ_id . " AND writ_ver = " . $this->writ_ver . " AND 
											  staff_id = " . $this->staff_id . " AND salary_item_type_id = " . $dt[$i]["salary_item_type_id"];

						PdoDataAccess::runquery($query);
					} else {
						$wrtArr['corrective_writ_id'] = $corrective_writ_id;
						$wrtArr['corrective_writ_ver'] = $corrective_writ_ver;
						$wrtArr['execute_date'] = $execute_date;
						$wrtArr['staff_id'] = $this->staff_id;
						$wrtArr['writ_id'] = $this->writ_id;
						$wrtArr['writ_ver'] = $this->writ_ver;



						$prior_obj = manage_writ::get_prior_writ($wrtArr ,"" ,"2013-02-19");
						
						if ($prior_obj->worktime_type != HALF_TIME  ) {
						     
						    if($prior_obj->worktime_type != QUARTER_TIME ){
							 $coef = '0.5' ; 
							 $newValue = $dt[$i]["value"]  ; 
							 }
						    else { 
							$coef = '2/3' ; 
							$newValue = $dt[$i]["value"] * (4/3)  ; 
							 }
						    
							$sql = 'UPDATE writ_salary_items
								    SET value = value * '.$coef.'
									    , base_value = ' . $newValue . '
								WHERE  writ_id = ' . $this->writ_id . ' AND
								    writ_ver = ' . $this->writ_ver . ' AND 
								    staff_id = ' . $this->staff_id . ' AND 
								    salary_item_type_id = ' . $dt[$i]['salary_item_type_id'] . ' AND 
								    base_value = 0';

							PdoDataAccess::runquery($sql);
						}
					}
			}
		}
	}
	
		/*	 * در صورت خدمت نيمه وقت افراد اقلام حقوقي مورد نظر
	 * بر اساس قوانين محاسبه مي شوند.
	 */

	private function compute_quarter_time_salary_items($corrective_writ_id = "", $corrective_writ_ver = "", $execute_date = "") {
		$sql = 'SELECT wsi.* , sit.salary_compute_type
	    		FROM   HRM_writ_salary_items wsi
	    			INNER JOIN HRM_salary_item_types sit
	    				ON wsi.salary_item_type_id = sit.salary_item_type_id
	            WHERE  writ_id = ' . $this->writ_id . ' AND 
	            	   writ_ver = ' . $this->writ_ver . ' AND 
	            	   staff_id = ' . $this->staff_id . '
	            	   
	            ORDER BY salary_item_type_id';

		$dt = PdoDataAccess::runquery($sql);

		for ($i = 0; $i < count($dt); $i++) {
			switch ($dt[$i]["salary_item_type_id"]) {
				//حق عائله مندي
				case SIT_STAFF_CHILD_RIGHT :
				case SIT_WORKER_CHILD_RIGHT :
				case SIT_PROFESSOR_CHILD_RIGHT :
					break;
				//حق اولاد
				case SIT_STAFF_CHILDREN_RIGHT :
				case SIT_PROFESSOR_CHILDREN_RIGHT :
					break;
				//فوق العاده محل خدمت
				case SIT_STAFF_DUTY_LOCATION_EXTRA :
					break;
				//فوق العاده سختي کار
				case SIT_STAFF_HARD_WORK_EXTRA :
					break;
				//فوق العاده بدي آب و هوا
				case SIT_STAFF_BAD_WEATHER_EXTRA :
				case SIT_PROFESSOR_BAD_WEATHER_EXTRA :
				case SIT_STAFF_BAD_WEATHER_EXTRA :
					break;

				//فوق العاده نوبت کاري
				case SIT_STAFF_SHIFT_EXTRA :
					break;

				default:
					if ($dt[$i]["salary_compute_type"] == SALARY_COMPUTE_TYPE_FUNCTION) {
						$query = "update writ_salary_items set value = value * 0.75
										where writ_id=" . $this->writ_id . " AND writ_ver = " . $this->writ_ver . " AND 
											  staff_id = " . $this->staff_id . " AND salary_item_type_id = " . $dt[$i]["salary_item_type_id"];

						PdoDataAccess::runquery($query);
					} else {
						$wrtArr['corrective_writ_id'] = $corrective_writ_id;
						$wrtArr['corrective_writ_ver'] = $corrective_writ_ver;
						$wrtArr['execute_date'] = $execute_date;
						$wrtArr['staff_id'] = $this->staff_id;
						$wrtArr['writ_id'] = $this->writ_id;
						$wrtArr['writ_ver'] = $this->writ_ver;



						$prior_obj = manage_writ::get_prior_writ($wrtArr,"","2013-02-19");
						
						if ($prior_obj->worktime_type != QUARTER_TIME ) {
						    
						    if($prior_obj->worktime_type != HALF_TIME){
							 $coef = '0.75' ; 
							 $newValue = $dt[$i]["value"] ;
							 }
						    else { 
							 $coef = '1.5' ; 
							 $newValue = $dt[$i]["value"] * 2   ; 
							 }
						    
						$sql = 'UPDATE writ_salary_items
							    SET value = value * '.$coef.'
								    , base_value = ' . $newValue . '
							WHERE  writ_id = ' . $this->writ_id . ' AND
								writ_ver = ' . $this->writ_ver . ' AND 
								staff_id = ' . $this->staff_id . ' AND 
								salary_item_type_id = ' . $dt[$i]['salary_item_type_id'] . ' AND 
								base_value = 0';

							PdoDataAccess::runquery($sql);
						}
					}
			}
		}
	}

	function call_multiplicand_modules($writ_rec) {
		switch ($writ_rec["multiplicand"]) {
			case "1" :
				return $this->get_base_salary($writ_rec["person_type"], $writ_rec["writ_id"], $writ_rec["writ_ver"]
								, $writ_rec["staff_id"]);

			case "2" :
				return $this->get_salary($writ_rec);

			case "3" :
				return $this->get_continouse_salary($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"]);

			case "4" :
				/** استخراج حقوق و مزاياي كامل مربوط به يك حكم خاص */
				return $this->compute_writ_items_sum($writ_rec);
		}
		return "";
	}

	/*	 * *
	 * استخراج حقوق مبنا مربوط به يك حكم خاص
	 *
	 * @param array $writ_rec
	 * @param boolean $final_value
	 * 
	 * @return manage_writ_item object
	 */

	//_____________________________________________________
	private function get_base_salary($person_type, $writ_id, $writ_ver, $staff_id, $final_value = true) {
		$salaryItem = "";
		if ($person_type == HR_PROFESSOR) {
			$salaryItem = SIT_PROFESSOR_BASE_SALARY;
		} elseif ($person_type == HR_EMPLOYEE) {
			$salaryItem = SIT_STAFF_BASE_SALARY;
		} elseif ($person_type == HR_WORKER) {
			$salaryItem = SIT3_BASE_SALARY;
		} elseif ($person_type == HR_CONTRACT) {
			$salaryItem = SIT5_STAFF_BASE_SALARY;
		}


		$si_obj = new manage_writ_item($writ_id, $writ_ver, $staff_id, $salaryItem);

		if ($final_value)
			$value = $si_obj->value;
		else
			$value = $si_obj->base_value;

		if (!($si_obj->value > 0)) {
			parent::PushException(BASE_SALARY_CALC_ERR);
			return false;
		}
		return $value;
	}

	/*	 * *
	 * محاسبه حداقل حقوق مبناي جدول در تاريخ اجراي يك حكم
	 *
	 * 
	 * @return value
	 */

	//_____________________________________________________
	private static function get_min_base_salary($person_type, $salary_date) {
		if ($person_type == HR_EMPLOYEE) {
			$rial_coef = manage_salary_params::get_salaryParam_value("", $person_type, SPT_RIAL_COEF, $salary_date);
			$value = $rial_coef * manage_writ_item::Get_employee_base_number(1);
		} else if ($person_type == HR_PROFESSOR) {
			$rial_coef = manage_salary_params::get_salaryParam_value("", $person_type, SPT_PROFESSOR_RIAL_COEF, $salary_date);
			$value = $rial_coef * manage_writ_item::Get_employee_base_number(1);
		} else if ($person_type == HR_CONTRACT) {
			$rial_coef = manage_salary_params::get_salaryParam_value("", $person_type, SPT_RIAL_COEF, $salary_date);
			$value = $rial_coef * manage_writ_item::Get_employee_base_number(1);
		}

		if (!($value > 0)) {
			parent::PushException(BASE_SALARY_CALC_ERR);
			return false;
		}
		return $value;
	}

	/*	 * *
	 * حقوق مبناي بدون گروه تشويقي
	 *
	 * 
	 * @return value
	 */

	//_____________________________________________________
	function get_none_hortative_base_salary($person_type, $writ_id, $writ_ver, $staff_id) {
		if ($person_type == HR_PROFESSOR) {
			$obj = new manage_writ_item($writ_id, $writ_ver, $staff_id, SIT_PROFESSOR_BASE_SALARY);
		} else if ($person_type == HR_EMPLOYEE) {
			$obj = new manage_writ_item($writ_id, $writ_ver, $staff_id, SIT_STAFF_BASE_SALARY);
		} else if ($person_type == HR_WORKER) {
			$obj = new manage_writ_item($writ_id, $writ_ver, $staff_id, SIT_PROFESSOR_BASE_SALARY);
		}

		if (!($obj->param4 > 0))
			$obj->param4 = $obj->value;

		return $obj->param4;
	}

	/**  استخراج حقوق مربوط به يك حكم خاص */
	//_____________________________________________________
	private function get_salary($writ_rec) {
		switch ($writ_rec['person_type']) {
			case HR_PROFESSOR :
				$value = $this->compute_writ_items_sum($writ_rec);
				break;
			case HR_EMPLOYEE :
				$value = $this->compute_writ_items_sum($writ_rec);
				break;
			case HR_WORKER :
				$value = $this->compute_writ_items_sum($writ_rec);
				break;
			default:
				return false;
		}
		return $value;
	}

	/** استخراج مقدار مجموع حقوق و مزاياي مستمر مربوط به يك حكم خاص */
	//_____________________________________________________
	private function get_continouse_salary($writ_id, $writ_ver, $staff_id) {
		//براي قبل از سال 84
		//اقلام حقوقي كه كسور بازنشستگي به آنها تعلق مي گيرد
		//از سال 84 به بعد مجموع اقلام حقوقي زير به عنوان حقوق و مزاياي مستمر است.
		//حقوق مبنا + افزايش سنواتي + فوق العاده شغل + فوق العاده شغل برجسته + حداقل دريافتي

		$sql = "SELECT  SUM(value) value
	
	        FROM    writ_salary_items wsi
	                LEFT OUTER JOIN salary_item_types sit
	                    ON (wsi.salary_item_type_id = sit.salary_item_type_id)
	
	        WHERE   ( wsi.writ_id = " . $writ_id . " ) AND
	                ( wsi.writ_ver = " . $writ_ver . " ) AND
	                ( wsi.staff_id = " . $staff_id . " ) AND 
	                  wsi.salary_item_type_id IN (" . SIT_STAFF_BASE_SALARY . ' , ' .
				SIT_STAFF_ANNUAL_INC . ' , ' .
				SIT_STAFF_JOB_EXTRA . ' , ' .
				SIT_STAFF_DOMINANT_JOB_EXTRA . ' , ' .
				SIT_STAFF_MIN_PAY . ')
	        GROUP BY wsi.writ_id, wsi.writ_ver';

		$temp = PdoDataAccess::runquery($sql);

		$value = $temp[0]['value'];

		if (!($value > 0))
			$value = 0;

		return $value;
	}

	/** محاسبه مجموع يك فيلد خاص در گروهي از اقلام يك حكم */
	//_____________________________________________________
	static function compute_writ_items_sum($writ_id, $writ_ver, $staff_id, $salary_item_type_id_set = "", $field = 'value') {
		
		if (!empty($salary_item_type_id_set))
			$condition = 'salary_item_type_id IN ' . $salary_item_type_id_set;
		else
			$condition = 'salary_item_type_id > 0 '; //dumy

		$sql = " SELECT  SUM($field) sum
	     	 FROM  	writ_salary_items
		     WHERE 	writ_id =" . $writ_id . " AND
	          	writ_ver =" . $writ_ver . " AND
	          	staff_id = " . $staff_id . " AND 
	            (" . $condition . ")
	
	    GROUP BY writ_id, writ_ver";

		$result = parent::runquery($sql);

		if (count($result) > 0)
			return $result[0]['sum'];

		return 0;
	}

	/**  استخراج اقلام حقوق و مزاياي مستمر مربوط به يك حكم خاص
	 *  اقلام حقوقي كه كسور بازنشستگي به آنها تعلق مي گيرد */
	//_____________________________________________________
	public static function get_continouse_salary_items($writ_id, $writ_ver, $staff_id) {
		$sql = "
		SELECT  wsi.salary_item_type_id,
	    		wsi.param1,
	            wsi.param2,
	            wsi.param3,
	    		wsi.param4,
	            wsi.param5,
	            wsi.param6,
	    		wsi.param7,
	            wsi.value,
	            wsi.automatic,
	            wsi.remember_date,
	            wsi.remember_message
	
	    FROM 	HRM_writ_salary_items wsi
	         	LEFT OUTER JOIN HRM_salary_item_types sit
	              	 ON (wsi.salary_item_type_id = sit.salary_item_type_id)
	
	    WHERE 	( wsi.writ_id = $writ_id ) AND
	            ( wsi.writ_ver = $writ_ver ) AND
	            ( wsi.staff_id = $staff_id ) AND 
	            ( sit.retired_include = 1)
		";

		return PdoDataAccess::runquery($sql);
	}

	/**
	 * استخراج ضريب افزايش سنواتي يك شخص براي يك سال خاص
	 *
	 * @param unknown_type $coef_year
	 * @param unknown_type $staff_id
	 * @return unknown
	 */
	//_____________________________________________________
	private function get_annual_coef($coef_year, $staff_id) {
		$year_last_day = DateModules::Shamsi_to_Miladi($coef_year . "-12-30");

		$query = "select scores_sum 
				  from evaluation_lists el
				  	LEFT OUTER JOIN evaluation_list_items eli ON(el.list_id = eli.list_id)
				  where (list_date <= '" . $year_last_day . "') AND
	    				(staff_id = $staff_id) AND
	                    (doc_state = " . CENTER_CONFIRM . ")
	              order by list_date DESC limit 1";

		$temp = PdoDataAccess::runquery($query);
		if (count($temp) == 0)
			return 0;
		$evaluation_score = $temp[0]['scores_sum'];

		if ($evaluation_score === NULL) {
			$annual_coef = 0;
		} else if ($evaluation_score < 60) {
			$annual_coef = 0.03;
		} else if ($evaluation_score < 80) {
			$annual_coef = 0.04;
		} else if ($evaluation_score <= 100) {
			$annual_coef = 0.05;
		} else {
			parent::PushException('WRONG_EVALUATION_SCORE');
			return false;
		}
		return $annual_coef;
	}

	/**
	 *  استخراج مجموع امتیاز ارزشیابی سنواتي يك شخص براي يك سال خاص
	 *
	 * @param unknown_type $coef_year
	 * @param unknown_type $staff_id
	 * @return unknown
	 */
	//_____________________________________________________
	private function get_evaluation_scores_sum($year, $staff_id) {
		$year_last_day = DateModules::Shamsi_to_Miladi($year . "/12/30");
		$year_first_day = DateModules::Shamsi_to_Miladi($year . "/01/01");

		$query = "select scores_sum 
				from evaluation_lists el
					LEFT OUTER JOIN evaluation_list_items eli ON(el.list_id = eli.list_id)
				where list_date <= '$year_last_day' AND list_date >= '$year_first_day' AND
    				 	staff_id = $staff_id AND doc_state = " . CENTER_CONFIRM . "
    			order by list_date DESC
    			limit 1";
		$dt = PdoDataAccess::runquery($query);

		if (count($dt) > 0) {
			$scores_sum = $dt[0]["scores_sum"];
			if (($scores_sum < 0) OR ($scores_sum > 100)) {
				parent::PushException(WRONG_EVALUATION_SCORE);
				return false;
			}
			return $scores_sum;
		}
		return false;
	}

	/*	 * *
	 *  استخراج ضريب بسيج يك شخص براي يك سال خاص
	 *
	 * @param unknown_type $coef_year
	 * @param unknown_type $staff_id
	 */

	//_____________________________________________________
	private function get_mobilization_coef($coef_year, $staff_id) {
		$year_last_day = DateModules::Shamsi_to_Miladi($coef_year . "/12/30");
		$year_first_day = DateModules::Shamsi_to_Miladi($coef_year . "/01/01");

		$query = "select mobilization_coef 
				  from mobilization_lists ml
				  	LEFT OUTER JOIN mobilization_list_items mli ON(ml.list_id = mli.list_id)
				  where (list_date <= '" . $year_last_day . "') AND
    					(list_date >= '" . $year_first_day . "') AND
    					(staff_id = " . $staff_id . ") AND
                        (doc_state = " . CENTER_CONFIRM . ")
				  order by list_date DESC
				  limit 1";
		$temp = PdoDataAccess::runquery($query);
		if (count($temp) == 0) {

			$mobilization_coef = 0;
		} else {
			$mobilization_coef = $temp[0]['mobilization_coef'];

			if (!($mobilization_coef > 0))
				$mobilization_coef = 0;
		}
		return $mobilization_coef;
	}

	/*	 * *
	 *    استخراج ضريب افزايش سنواتي يك شخص براي يك سال خاص
	 *
	 * @param unknown_type $coef_year
	 * @param unknown_type $staff_id
	 */

	//_____________________________________________________
	function get_dominant_job_extra_coef($coef_year, $staff_id) {
		$year_last_day = DateModules::Shamsi_to_Miladi($coef_year . "/12/30");
		$year_first_day = DateModules::Shamsi_to_Miladi($coef_year . "/01/01");

		$query = "select high_job_coef 
				
				from evaluation_lists el
					LEFT OUTER JOIN evaluation_list_items eli ON(el.list_id = eli.list_id)
				
				where list_date <= '$year_last_day' AND
	    			list_date >= '$year_first_day' AND
	    			staff_id = $staff_id AND
	                doc_state = " . CENTER_CONFIRM . "
				
				order by list_date DESC
				
				limit 1";

		$dt = PdoDataAccess::runquery($query);

		if (count($dt) > 0)
			return $dt[0]['high_job_coef'];
		return false;
	}

	/*	 * * تاريخ اعتبار يک قلم حقوقي را کنترل مي کند */

	function validate_salary_item($validity_start_date, $validity_end_date, $date) {
		if (DateModules::CompareDate($validity_start_date, $date) != 1 &&
				(DateModules::CompareDate($validity_end_date, $date) != -1 || $validity_end_date == null))
			return true;
		else
			return false;
	}

	/*	 * *****************************************************************************
	 * *
	 * * آيتمهاي حقوق هيات علمي
	 * *
	 * ***************************************************************************** */

	/*	 * * حقوق مبنا */

	private function compute_salary_item1_01($writ_rec) {
		//param1 : پايه
		//param2 : عدد مبنا
		//param3 : ضريب حقوق

		if (($writ_rec['emp_state'] == EMP_STATE_SOLDIER_CONTRACTUAL ||
				$writ_rec['emp_state'] == EMP_STATE_ONUS_SOLDIER_CONTRACTUAL ||
				$writ_rec['emp_state'] == EMP_STATE_CONTRACTUAL) &&
				$writ_rec['execute_date'] < str_replace("/", "-", DateModules::shamsi_to_miladi('1389-07-01')))
			$base = 1;
		else
			$base = $writ_rec['base'];


		$professor_base_number = manage_writ_item::Get_professor_base_number($writ_rec['science_level']);

		$salary_coef = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"], SPT_SALARY_COEF, $writ_rec['execute_date']);

		if (!$salary_coef) {
			parent::PushException(SALARY_COEF_NOT_FOUND);
			return false;
		}
		$this->param1 = $base;
		$this->param2 = $professor_base_number;
		$this->param3 = $salary_coef;

		$value = $salary_coef * ($professor_base_number + 5 * $base);
		//echo  $value ." value ----<br> ";
		if (!($value > 0)) {
			parent::PushException(BASE_SALARY_CALC_ERR);
			return false;
		}

		if (($writ_rec['emp_state'] == EMP_STATE_SOLDIER_CONTRACTUAL ||
				$writ_rec['emp_state'] == EMP_STATE_ONUS_SOLDIER_CONTRACTUAL ||
				$writ_rec['emp_state'] == EMP_STATE_CONTRACTUAL) &&
				$writ_rec['execute_date'] < '2009-09-23')
			$value *= 0.95;
		//echo  $value ." value ----<br> "; die();
		return $value;
	}

	/*	 * * حق عايله مندي */

	private function compute_salary_item1_02($writ_rec) {
		return $this->compute_salary_item2_04($writ_rec);
	}

	/*	 * * حق اولاد */

	private function compute_salary_item1_03($writ_rec) {
		return manage_writ_item::compute_salary_item2_05($writ_rec);
	}

	/*	 * * فوق العاده كار با اشعه */

	private function compute_salary_item1_04($writ_rec) {
		//param1 : ضريب فوق العاده كار با اشعه  :input
		//param2 : حقوق مبنا
		//param3 : فوق العاده مخصوص

		$this->param2 = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_PROFESSOR_BASE_SALARY);
		$this->param3 = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_PROFESSOR_SPECIAL_EXTRA);

		if (!($this->param2 > 0 && $this->param3 > 0)) {
			parent::PushException(WRIT_SALARY_ITEM_NOT_FOUND);
			return false;
		}
		$value = 0.5 * $this->param1 * ($this->param2 + $this->param3 );
		if (!($value > 0)) {
			parent::PushException(RAY_EXTRA_CALC_ERR);
			return false;
		}

		return $value;
	}

	/*	 * * فوق العاده جذب */

	private function compute_salary_item1_05($writ_rec) {
		//param1 : ضريب فوق العاده جذب
		//param2 : حقوق مبنا
		//param3 : not used
		//ماموريت تحصيلي خارج از كشور جذب تعلق نمي گيرد
		//دو سطرزير با نوع حكم مناسب فعال شود
		//if ($writ_rec['writ_type_id'] == x && $writ_rec['writ_subtype_id'] == y)
		//	return 0;

		$this->param1 = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"], SPT_PROFESSOR_ABSORB_COEF, $writ_rec['execute_date'], $writ_rec['science_level'], $writ_rec['work_city_id'], $writ_rec['work_state_id']);

		if (!$this->param1) {
			parent::PushException(PROFESSOR_ABSORB_COEF_NOT_FOUND);
			return false;
		}

		$this->param2 = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_PROFESSOR_BASE_SALARY);

		if (!$this->param2) {
			parent::PushException(WRIT_SALARY_ITEM_NOT_FOUND);
			return false;
		}

		$value = $this->param1 * $this->param2;
		if (!($value > 0)) {
			parent::PushException(PROFESSOR_ABSORB_EXTRA_CALC_ERR);
			return false;
		}

		return $value;
	}

	/*	 * * فوق العاده بدي آب و هوا */

	private function compute_salary_item1_06($writ_rec) {
		//param1 : ضريب بدي آب و هوا
		//param2 : حقوق مبنا
		//param3 : not used

		$this->param1 = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"], SPT_PROFESSOR_WHEATHER_COEF, $writ_rec['execute_date'], $writ_rec['work_city_id'], $writ_rec['work_state_id']);

		if (!$this->param1) {
			parent::PushException(PROFESSOR_WHEATHER_COEF_NOT_FOUND);
			return false;
		}
		$this->param2 = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_PROFESSOR_BASE_SALARY);
		if (!$this->param2) {
			parent::PushException(WRIT_SALARY_ITEM_NOT_FOUND);
			return false;
		}
		$value = $this->param1 * $this->param2;
		if (!($value > 0)) {
			parent::PushException(PROFESSOR_WHEATHER_EXTRA_CALC_ERR);
			return false;
		}

		return $value;
	}

	/*	 * * حق مسكن */

	private function compute_salary_item1_08($writ_rec) {
		//param1 : not used
		//param2 : not used
		//param3 : not used

		$value = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"], SPT_PROFESSOR_HOME_EXTRA, $writ_rec['execute_date']);
		if (!($value > 0)) {
			parent::PushException(PROFESSOR_HOME_EXTRA_CALC_ERR);
			return false;
		}
		return $value;
	}

	/*	 * * فوق العاده مخصوص	 */

	private function compute_salary_item1_09($writ_rec) {
		//param1 : مرتبه علمي
		//param2 : حقوق مبنا
		//param3 : ضريب فوق العاده مخصوص

		$this->param1 = $writ_rec['science_level'];
		$this->param2 = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_PROFESSOR_BASE_SALARY);

		$this->param3 = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"], SPT_SPECIAL_EXTRA_COEF, $writ_rec['execute_date'], $writ_rec['science_level']);

		if (!$this->param3) {
			parent::PushException(SPECIAL_EXTRA_COEF_NOT_FOUND);
			return false;
		}

		$value = $this->param3 * $this->param2;


		if (!($value > 0)) {
			parent::PushException(SPECIAL_EXTRA_CALC_ERR);
			return false;
		}

		return $value;
	}

	/*	 * * فوق العاده ايثارگري	 */

	private function compute_salary_item1_10($writ_rec) {
		//param1 : پايه ايثارگري
		//param2 : مرتبه علمي
		//param3 : ضريب حقوق
		//اين فوق العاده به مربي و مربي آموزشيار فقط تعلق مي گيرد.
		if ($writ_rec['science_level'] >= MASTERSTROKE)
			return 0;

		$person_devotions = manage_person_devotion::GetAllDevotions(" d.PersonID=" . $writ_rec["PersonID"] .
                                                                            " AND devotion_type in (" . DEVOTION_TYPE_WOUNDED . ")");


		if (count($person_devotions) <= 0)
			return 0;


		$this->param1 = 0;
		for ($i = 0; $i < count($person_devotions); $i++) {
			if ($person_devotions[$i]['devotion_type'] == DEVOTION_TYPE_WOUNDED) {
				if (10 <= $person_devotions[$i]['amount'] && $person_devotions[$i]['amount'] < 20) {
					$this->param1 += 1;
				} else if ($person_devotions[$i]['amount'] < 30) {
					$this->param1 += 2;
				} else if ($person_devotions[$i]['amount'] < 40) {
					$this->param1 += 3;
				} else if ($person_devotions[$i]['amount'] < 50) {
					$this->param1 += 5;
				} else if ($person_devotions[$i]['amount'] < 60) {
					$this->param1 += 7;
				} else if ($person_devotions[$i]['amount'] < 70) {
					$this->param1 += 9;
				} else {
					$this->param1 += 11;
				}
			}
		}

		$param1_base = $this->param1;


		$this->param2 = $writ_rec['science_level'];
		switch ($writ_rec['science_level']) {
			case INSTRUCTOR_EDUCATOR :
				$param2_science_level = 'مربي آموزشيار';
				break;
			case EDUCATOR :
				$param2_science_level = 'مربي';
				break;
			case MASTERSTROKE :
				$param2_science_level = 'استاديار';
				break;
			case LECTURESHIP :
				$param2_science_level = 'دانشيار';
				break;
			case MASTERSHIP :
				$param2_science_level = 'استاد';
				break;
		}

		$writ_rec['science_level']++;
		$higher_science_level_base_salary = manage_writ_item::compute_salary_item1_01($writ_rec);

		$higher_science_level_special_extra = $higher_science_level_base_salary *
				manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"], SPT_SPECIAL_EXTRA_COEF, $writ_rec['execute_date'], $writ_rec['science_level']);

		$higher_science_level_absorb_extra = $higher_science_level_base_salary *
				manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"], SPT_PROFESSOR_ABSORB_COEF, $writ_rec['execute_date'], $writ_rec['science_level'], $writ_rec['work_city_id'], $writ_rec['work_state_id']);

		$higher_science_level_wheather_extra = $higher_science_level_base_salary *
				manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"], SPT_PROFESSOR_WHEATHER_COEF, $writ_rec['execute_date'], $writ_rec['work_city_id'], $writ_rec['work_state_id']);

		$higher_science_level_particular_extra = $higher_science_level_base_salary *
				manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"], SPT_PARTICULAR_EXTRA, $writ_rec['execute_date'], $writ_rec['science_level']);
		//......................................................................

		if ($writ_rec['execute_date'] > '2010-03-20' && $writ_rec['execute_date'] < '2014-03-21') {

			$new_extra = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"], SPT_PARTICULAR_EXTRA_NEW, $writ_rec['execute_date'], $writ_rec['science_level']);

			$new_higher_science_level_particular_extra = $higher_science_level_base_salary * $new_extra;
			$maxVal = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"], MAX_SPT_PARTICULAR_EXTRA_NEW, $writ_rec['execute_date'], $writ_rec['science_level']);

			if (!($new_higher_science_level_particular_extra > 0)) {

				parent::PushException(PARTICULAR_EXTRA_CALC_ERR);
				return false;
			}

			if ($new_higher_science_level_particular_extra > $maxVal) {
				$new_higher_science_level_particular_extra = $maxVal;
			}

			$higher_science_level_particular_extra += $new_higher_science_level_particular_extra;
		}

		$sum_higher_science_level = $higher_science_level_base_salary + $higher_science_level_special_extra + $higher_science_level_absorb_extra +
				$higher_science_level_wheather_extra + $higher_science_level_particular_extra;

		$writ_rec['science_level']--;

		$base_salary = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_PROFESSOR_BASE_SALARY);

		$special_extra = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_PROFESSOR_SPECIAL_EXTRA);

		$absorb_extra = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_PROFESSOR_ABSOPPTION_EXTRA);

		$wheather_extra = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_PROFESSOR_BAD_WEATHER_EXTRA);

		$particular_extra = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_PROFESSOR_PARTICULAR_EXTRA);

		$sum = $base_salary + $special_extra + $absorb_extra + $wheather_extra + $particular_extra;
		//......................................................................
		
		
		
		$value = $sum_higher_science_level - $sum;

		$this->param1 = $param1_base;
		$this->param2 = $param2_science_level;

		return $value;
	}

	/*	 * * بابت ب 15/3015 */

	private function compute_salary_item1_11($writ_rec) {

		//param1 : پايه ايثارگري
		//param2 : مرتبه علمي
		//param3 : ضريب حقوق
		//براي استاديارو بالاتر اين قلم حقوقي برقرار نيست. به اين دليل کد پايين اضافه شد.
		//به گفته آقاي نداف در تاريخ 30/10/83 اين قسمت به صورت comment درآمد.
		/*    if ($writ_rec['science_level'] >= MASTERSTROKE) {
		  return 0;
		  } */
		$person_devotions = manage_person_devotion::GetAllDevotions("d.PersonID=" . $writ_rec["PersonID"] .
						" AND devotion_type in (" . DEVOTION_TYPE_FIGHTER . ' , ' . DEVOTION_TYPE_PRISONER . ' , ' . DEVOTION_TYPE_WAR_TEACH . ' , ' .
						DEVOTION_TYPE_WOUNDED . ")");

		$this->param1 = 0;
		for ($i = 0; $i < count($person_devotions); $i++) {
			$devotion_rec = $person_devotions[$i];

			switch ($devotion_rec['devotion_type']) {
				case DEVOTION_TYPE_FIGHTER://رزمندگي
					if (180 <= $devotion_rec['amount'] && $devotion_rec['amount'] < 365) {
						$this->param1 += 1;
					} else if ($devotion_rec['amount'] < 720) {
						$this->param1 += 2;
					} else if ($devotion_rec['amount'] < 1095) {
						$this->param1 += 3;
					} else if ($devotion_rec['amount'] < 1460) {
						$this->param1 += 5;
					} else if ($devotion_rec['amount'] < 1825) {
						$this->param1 += 7;
					} else if ($devotion_rec['amount'] < 2190) {
						$this->param1 += 9;
					} else {
						$this->param1 += 11;
					}
					break;

				case DEVOTION_TYPE_PRISONER://آزادگي
					if ($devotion_rec['amount'] < 365) {
						$this->param1 += 1;
					} else if ($devotion_rec['amount'] < 720) {
						$this->param1 += 2;
					} else if ($devotion_rec['amount'] < 1095) {
						$this->param1 += 3;
					} else if ($devotion_rec['amount'] < 1460) {
						$this->param1 += 5;
					} else if ($devotion_rec['amount'] < 1825) {
						$this->param1 += 7;
					} else if ($devotion_rec['amount'] < 2190) {
						$this->param1 += 9;
					} else {
						$this->param1 += 11;
					}
					break;

				case DEVOTION_TYPE_WOUNDED://جانبازي
					if ($writ_rec['science_level'] >= MASTERSTROKE) {
						if (10 <= $devotion_rec['amount'] && $devotion_rec['amount'] < 20) {
							$this->param1 += 1;
						} else if ($devotion_rec['amount'] < 30) {
							$this->param1 += 2;
						} else if ($devotion_rec['amount'] < 40) {
							$this->param1 += 3;
						} else if ($devotion_rec['amount'] < 50) {
							$this->param1 += 5;
						} else if ($devotion_rec['amount'] < 60) {
							$this->param1 += 7;
						} else if ($devotion_rec['amount'] < 70) {
							$this->param1 += 9;
						} else {
							$this->param1 += 11;
						}
					}
					break;
				case DEVOTION_TYPE_WAR_TEACH://تدريس مناطق جنگي
					$this->param1 += floor($devotion_rec['amount'] / 365);
					break;
			}
		}

		//چون مجموع پايه هاي که به شخص تعلق مي گيرد از يازده نبايد تجاوز کند شرط زير اضافه شد.
		if ($this->param1 > 11)
			$this->param1 = 11;

		//$writ_rec["param1"] = $param1;
		$this->param2 = $writ_rec['science_level'];
		$param1_base = $this->param1;

		switch ($writ_rec['science_level']) {
			case INSTRUCTOR_EDUCATOR :
				$param2_science_level = 'مربي آموزشيار';
				break;
			case EDUCATOR :
				$param2_science_level = 'مربي';
				break;
			case MASTERSTROKE :
				$param2_science_level = 'استاديار';
				break;
			case LECTURESHIP :
				$param2_science_level = 'دانشيار';
				break;
			case MASTERSHIP :
				$param2_science_level = 'استاد';
				break;
		}

		$writ_rec['base'] += $this->param1;
		$higher_base_base_salary = $this->compute_salary_item1_01($writ_rec);

		$higher_base_special_extra = $higher_base_base_salary * manage_salary_params::get_salaryParam_value
						("", $writ_rec["person_type"], SPT_SPECIAL_EXTRA_COEF, $writ_rec['execute_date'], $writ_rec['science_level']);

		$higher_base_absorb_extra = $higher_base_base_salary * manage_salary_params::get_salaryParam_value
						("", $writ_rec["person_type"], SPT_PROFESSOR_ABSORB_COEF, $writ_rec['execute_date'], $writ_rec['science_level'], $writ_rec['work_city_id'], $writ_rec['work_state_id']);

		$higher_base_wheather_extra = $higher_base_base_salary * manage_salary_params::get_salaryParam_value
						("", $writ_rec["person_type"], SPT_PROFESSOR_WHEATHER_COEF, $writ_rec['execute_date'], $writ_rec['work_city_id'], $writ_rec['work_state_id']);

		$SIT_PROFESSOR_PARTICULAR_EXTRA = manage_writ_item::get_writSalaryItem_value
						($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_PROFESSOR_PARTICULAR_EXTRA);

		$higher_particular_extra = $higher_base_base_salary * $SIT_PROFESSOR_PARTICULAR_EXTRA;

		$sum_higher_base = $higher_base_base_salary +
				$higher_base_special_extra +
				$higher_base_absorb_extra +
				$higher_base_wheather_extra +
				$higher_particular_extra;

		$writ_rec['base'] -= $this->param1;
		$base_salary = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_PROFESSOR_BASE_SALARY);
		$special_extra = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_PROFESSOR_SPECIAL_EXTRA);
		$absorb_extra = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_PROFESSOR_ABSOPPTION_EXTRA);
		$wheather_extra = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_PROFESSOR_BAD_WEATHER_EXTRA);
		$particular_extra = $SIT_PROFESSOR_PARTICULAR_EXTRA;
		$sum = $base_salary + $special_extra + $absorb_extra + $wheather_extra + $particular_extra;

		$value = $sum_higher_base - $sum;

		//به دليل اينکه هيات علمي پيماني پايه ندارند وعملا اختلاف انها
		//با يک پايه بالاتر صفر است کد زير اضافه شد.
		if ($value < 10)
			$value = 0;

		$this->param1 = $param1_base;
		$this->param2 = $param2_science_level;

		return $value;
	}

	/*	 * * افزايش فوق العاده جذب */

	private function compute_salary_item1_12($writ_rec) {

		//param1 : ضريب افزايش فوق العاده جذب : input
		//param2 : ضريب فوق العاده جذب بر مبناي تهران
		//param3 : حقوق مبنا + فوق العاده مخصوص + فوق العاده جذب برمبناي تهران

		$this->param2 = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"], SPT_PROFESSOR_ABSORB_COEF, $writ_rec['execute_date'], $writ_rec['science_level'], TEHRAN_CITY_ID, TEHRAN_STATE_ID);

		if (!$this->param2) {
			parent::PushException('TEHRAN_PROFESSOR_ABSORB_COEF_NOT_FOUND');
			return false;
		}

		$base_salary = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_PROFESSOR_BASE_SALARY);
		if (!$base_salary) {
			parent::PushException('WRIT_SALARY_ITEM_NOT_FOUND');
			return false;
		}

		$special_salary = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_PROFESSOR_SPECIAL_EXTRA);
		if (!$special_salary) {
			parent::PushException('SPECIAL_EXTRA_NOT_FOUND');
			return false;
		}

		$tehran_absorb_salary = $this->param2 * $base_salary;

		$this->param3 = $base_salary + $special_salary + $tehran_absorb_salary;

		$value = $this->param1 * $this->param3;

		return $value;
	}

	/*	 * * فوق العاده ويژه */

	private function compute_salary_item1_13($writ_rec) {
		//param1 : ضريب فوق العاده ويژه
		//param2 : حقوق مبنا
		//param3 : not used
		//param4 : ضریب فوق العاده ویژه جدید
		//param5 : سقف ضریب فوق العاده ویژه جدید
		//param6 : میلغ ضریب افزایش فوق العاده ویژه

		$this->param3 = NULL;
		$this->param1 = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"], SPT_PARTICULAR_EXTRA, $writ_rec['execute_date'], $writ_rec['science_level']);
		
		if (!$this->param1) {

			parent::PushException(PARTICULAR_EXTRA_COEF_NOT_FOUND);
			return false;
		}

		$this->param2 = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_PROFESSOR_BASE_SALARY);
		if (!$this->param2) {

			parent::PushException(WRIT_SALARY_ITEM_NOT_FOUND);
			return false;
		}

		$value_old = $this->param1 * $this->param2;


		if (!($value_old > 0)) {
			sisRaiseException(PARTICULAR_EXTRA_CALC_ERR);
			return false;
		}

		//// محاسبه ضریب فوق العاده جدید

		if ($writ_rec['execute_date'] > '2010-03-20' &&  $writ_rec['execute_date'] < '2014-03-21' ) {
			
			
					
			$this->param4 = manage_salary_params::get_salaryParam_value("", $writ_rec['person_type'], SPT_PARTICULAR_EXTRA_NEW, $writ_rec['execute_date'], $writ_rec['science_level']);

			if (!$this->param4) {
				parent::PushException(PARTICULAR_EXTRA_COEF_NOT_FOUND);
				return false;
			}

			$this->param3 = $this->param4;

			$this->param5 = manage_salary_params::get_salaryParam_value("", $writ_rec['person_type'], MAX_SPT_PARTICULAR_EXTRA_NEW, $writ_rec['execute_date'], $writ_rec['science_level']);

			if (!$this->param5) {
				parent::PushException(PARTICULAR_EXTRA_COEF_NOT_FOUND);
				return false;
			}

			$value_new = $this->param4 * $this->param2;

			if (!($value_new > 0)) {
				parent::PushException(PARTICULAR_EXTRA_CALC_ERR);
				return false;
			}

			$this->param6 = $value_new;

			if ($value_new > $this->param5) {
				$value_new = $this->param5;
			}

			$value = $value_new + $value_old;
		} else {

			$value = $value_old;
		}

		return $value;
	}

        //........................... فوق العاده ایثارگری........................
                
                private function  compute_salary_item1_14 ($writ_rec){
	           
                // param1  حداقل حقوق
                    
                  $qry = " SELECT count(*) cn
                                FROM person_devotions pd
                                                inner join persons p on pd.PersonID = p.PersonID
                                                
                            where pd.devotion_type = 3 and
                                  pd.amount > 24 and p.personID = ".$writ_rec['PersonID']; 
                  $resVal1 = PdoDataAccess::runquery($qry); 
     
                  $qry = " SELECT count(*) cn
                                FROM person_devotions pd
                                         inner join persons p on pd.PersonID = p.PersonID                                                

                            where pd.devotion_type = 2 and p.personID = ".$writ_rec['PersonID']; 
                  $resVal2 = PdoDataAccess::runquery($qry); 

                  $qry = " SELECT  count(*) cn
                                FROM person_devotions pd
                                             inner join persons p on pd.PersonID = p.PersonID                                                
                            where pd.devotion_type = 5 and personel_relation in (5,6) and p.personID = ".$writ_rec['PersonID']; 
                  $resVal3 = PdoDataAccess::runquery($qry); 
                   
                  if( $resVal1[0]['cn'] > 0  || $resVal2[0]['cn'] > 0 || $resVal3[0]['cn'] > 0 ) 
                  {
                      $this->param1 = manage_salary_params::get_salaryParam_value("", 1 , SPT_MIN_SALARY, $writ_rec['execute_date']);                           
 
                      $value = 0.25 * $this->param1 ;
                  }
                  else
                      return 0 ;               
                  
		
		  return $value ;
		
		}

	/*	 * *  محاسبه فوق العاده مديريت هيات علمي */

	static function get_professor_management_extra($writ_rec) {



		$execute_date = $writ_rec['execute_date'];

		//----------------------------------------------------------------------
		$query = "SELECT  *
				    FROM  professor_exe_posts pep
						LEFT OUTER JOIN position p ON (pep.post_id = p.post_id)

				WHERE  from_date <= '$execute_date' AND
					   ((to_date >= '$execute_date') OR (to_date IS NULL OR to_date = '0000-00-00' ))   AND
					   pep.staff_id = " . $writ_rec['staff_id'] . "  AND
					   p.included = 1";

		$temp = PdoDataAccess::runquery($query);

		if (count($temp) != 0) {
			$exe_post_set = "";

			foreach ($temp as $key => $exe_post_record) {
				if ($key == 0)
					$exe_post_set .= '( ' . $exe_post_record['post_id'];
				else
					$exe_post_set .= ' , ' . $exe_post_record['post_id'];
			}
			$exe_post_set .= ')';

			//------------------------------------------------------------------
			$query = "SELECT  MAX(mebi.value) value

				FROM  managmnt_extra_bylaw_items mebi
					  LEFT OUTER JOIN management_extra_bylaw meb ON (mebi.bylaw_id = meb.bylaw_id)

				WHERE mebi.post_id IN $exe_post_set    AND
					  meb.from_date <= '$execute_date' AND
					  meb.to_date >= '$execute_date'";

			$temp = PdoDataAccess::runquery($query);

			return $temp[0]['value'];
		}
		else
			return 0;
	}

	/*	 * * اقلام استاد را بر اساس آیتم هاي حکم قبلی و با پارامترهای سال قبل محاسبه مي کند */

	private static function compute_professors_salary_params($from_base, $to_base, $from_science_level, $to_science_level, $work_state_id, $work_city_id, $execute_date, $emp_state = EMP_STATE_APPROVED_CEREMONIOUS, $compute_heiat_omana_absorb_extra = true) {
		if (!$execute_date) {
			$execute_date = DateModules::shNow();
		}

		$sql = 'SELECT  validity_start_date,
	    				validity_end_date,
	    				salary_item_type_id
	    		FROM  salary_item_types
	    		WHERE  salary_item_type_id IN (' . SIT_PROFESSOR_BASE_SALARY . ',
	    									   ' . SIT_PROFESSOR_BAD_WEATHER_EXTRA . ',
	    									   ' . SIT_PROFESSOR_ABSOPPTION_EXTRA . ',
	    									   ' . SIT_PROFESSOR_SPECIAL_EXTRA . ',
	    									   ' . SIT_PROFESSOR_PARTICULAR_EXTRA . ',
	    									   ' . SIT_PROFESSOR_HEIAT_OMANA_SPECIAL_EXTRA . '
	    									   )';
		$temp = PdoDataAccess::runquery($sql);

		foreach ($temp as $result) {
			switch ($result['salary_item_type_id']) {
				case SIT_PROFESSOR_BASE_SALARY :
					$is_valid_base_salary = self::validate_salary_item($result['validity_start_date'], $result['validity_end_date'], $execute_date);
					break;
				case SIT_PROFESSOR_BAD_WEATHER_EXTRA :
					$is_valid_bad_weather_extra = self::validate_salary_item($result['validity_start_date'], $result['validity_end_date'], $execute_date);
					;
					break;
				case SIT_PROFESSOR_ABSOPPTION_EXTRA :
					$is_valid_absopption_extra = self::validate_salary_item($result['validity_start_date'], $result['validity_end_date'], $execute_date);
					;
					break;
				case SIT_PROFESSOR_SPECIAL_EXTRA :
					$is_valid_special_extra = self::validate_salary_item($result['validity_start_date'], $result['validity_end_date'], $execute_date);
					;
					break;
				case SIT_PROFESSOR_PARTICULAR_EXTRA :
					$is_valid_particular_extra = self::validate_salary_item($result['validity_start_date'], $result['validity_end_date'], $execute_date);
					;
					break;
				case SIT_PROFESSOR_HEIAT_OMANA_SPECIAL_EXTRA :
					$is_valid_heiat_omana_absorb_extera = self::validate_salary_item($result['validity_start_date'], $result['validity_end_date'], $execute_date);
					;
			}
		}

		$counter = 0;
		for ($science_level = $to_science_level; $science_level >= $from_science_level; $science_level--)
			for ($base = $from_base; $base <= $to_base; $base++) {
				$writ_rec['base'] = $base;
				$writ_rec['science_level'] = $science_level;
				$writ_rec['work_city_id'] = $work_city_id;
				$writ_rec['work_state_id'] = $work_state_id;
				$writ_rec['execute_date'] = $execute_date;
				$writ_rec['emp_state'] = $emp_state;

				if ($compute_heiat_omana_absorb_extra && $is_valid_heiat_omana_absorb_extera) {
					//آخرين حكم سال قبل
					$base_writ_rec['base'] = $base;
					$base_writ_rec['science_level'] = $science_level;
					$base_writ_rec['work_city_id'] = $work_city_id;
					$base_writ_rec['work_state_id'] = $work_state_id;
					$base_writ_rec['emp_state'] = $emp_state;

					$this_writ_year = substr(DateModules::Miladi_to_Shamsi($execute_date), 0, 4);
					$one_year_ago = $this_writ_year - 1;
					$one_year_ago_last_day_writ = $one_year_ago . "/12/29";
					$Gone_year_ago_last_day = DateModules::Shamsi_to_Miladi($one_year_ago_last_day_writ);
					$base_writ_rec['execute_date'] = $Gone_year_ago_last_day;

					if ($base_writ_rec)
						$rec = self::compute_professors_salary_params(
										$base_writ_rec['base'], $base_writ_rec['base'], $base_writ_rec['science_level'], $base_writ_rec['science_level'], $base_writ_rec['work_state_id'], $base_writ_rec['work_city_id'], $base_writ_rec['execute_date'], $base_writ_rec['emp_state'], false);
					$devotion_extra = 0;
					$param1 = $rec[0]['base_salary'] + $rec[0]['absorb_extra'] + $rec[0]['vijeh_extra'] +
							$rec[0]['special_extra'] + $devotion_extra;

					$heiat_omana_absorb_extra = $param1 * 0.02;
				}
				else
					$heiat_omana_absorb_extra = 0;
				//.............................
				if ($is_valid_base_salary == true)
					$base_salary = self::compute_salary_item1_01($writ_rec, $param1, $param2, $param3, $param4, $param5, $param6, $param7);
				else
					$base_salary = 0;
				//.............................	
				if ($is_valid_absopption_extra == true)
					$absorb_extra = round($base_salary * manage_salary_params::get_salaryParam_value("", "1", SPT_PROFESSOR_ABSORB_COEF, $writ_rec['execute_date'], $writ_rec['science_level'], $writ_rec['work_city_id'], $writ_rec['work_state_id']));
				else
					$absorb_extra = 0;
				//.............................
				if ($is_valid_bad_weather_extra == true)
					$weather_bad_extra = round($base_salary * manage_salary_params::get_salaryParam_value("", "1", SPT_PROFESSOR_WHEATHER_COEF, $writ_rec['execute_date'], $writ_rec['work_city_id'], $writ_rec['work_state_id']));
				else
					$weather_bad_extra = 0;
				//.............................
				if ($is_valid_special_extra == true)
					$special_extra = round($base_salary * manage_salary_params::get_salaryParam_value("", "1", SPT_SPECIAL_EXTRA_COEF, $writ_rec['execute_date'], $writ_rec['science_level']));
				else
					$special_extra = 0;
				//.............................
				if ($is_valid_particular_extra == true)
					$vijeh_extra = $base_salary * manage_salary_params::get_salaryParam_value("", "1", SPT_PARTICULAR_EXTRA, $writ_rec['execute_date'], $writ_rec['science_level']);
				else
					$vijeh_extra = 0;
				//.............................

				$salary_params[$counter]['base_salary'] = $base_salary;
				$salary_params[$counter]['absorb_extra'] = $absorb_extra;
				$salary_params[$counter]['weather_bad_extra'] = $weather_bad_extra;
				$salary_params[$counter]['special_extra'] = $special_extra;
				$salary_params[$counter]['heiat_omana_absorb_extra'] = $heiat_omana_absorb_extra;
				$salary_params[$counter]['vijeh_extra'] = $vijeh_extra;

				$salary_params[$counter]['total_sum'] = $base_salary + $absorb_extra + $weather_bad_extra +
						$special_extra + $vijeh_extra +
						$heiat_omana_absorb_extra;

				$salary_params[$counter]['base'] = $base;

				switch ($science_level) {
					case INSTRUCTOR_EDUCATOR : $salary_params[$counter]['science_level'] = 'مربي آموزشيار';
						break;
					case EDUCATOR : $salary_params[$counter]['science_level'] = 'مربي';
						break;
					case MASTERSTROKE : $salary_params[$counter]['science_level'] = 'استاديار';
						break;
					case LECTURESHIP : $salary_params[$counter]['science_level'] = 'دانشيار';
						break;
					case MASTERSHIP : $salary_params[$counter]['science_level'] = 'استاد';
						break;
				}
				$counter++;
			}
		return $salary_params;
	}

	/*	 * * محاسبه فوق العاده جذب اعضاي هيات علمي
	  برابر دو درصد حقوق و مزاياي سال قبل
	 */

	function compute_professor_heiat_omana_special_extra($writ_rec) {
		$value = 0;

		if ($writ_rec['execute_date'] > '2009-03-19') {
			$query = "select wsi.* from writs w 
						inner join writ_salary_items wsi on(w.writ_id = wsi.writ_id AND 
							w.writ_ver = wsi.writ_ver AND w.staff_id=wsi.staff_id)
						
						where  w.execute_date <= '2009-03-19'  and 
						       w.staff_id = " . $writ_rec['staff_id'] . " and 
						       wsi.salary_item_type_id = 44 and history_only != 1
						       
						order by w.execute_date DESC
						limit 1 ";

			$temp = PdoDataAccess::runquery($query);


			if (count($temp) != 0 && $temp[0]['value'] > 0) {
				$value = $temp[0]['value'];
				$this->param1 = $temp[0]['param1'];
			} else {
				$query = "select wsi.* from writs w 
							inner join writ_salary_items wsi on(w.writ_id = wsi.writ_id AND
								w.writ_ver = wsi.writ_ver AND w.staff_id=wsi.staff_id)
						
						where  w.execute_date > '2009-03-19'  and 
						       w.staff_id = " . $writ_rec['staff_id'] . " and 
						       wsi.salary_item_type_id = 44 and history_only != 1
						       
						order by w.execute_date DESC
						limit 1 ";

				$temp = PdoDataAccess::runquery($query);

				if ($temp[0]['value'] > 0) {
					$value = $temp[0]['value'];
					$this->param1 = $temp[0]['param1'];
				}
			}
		} else if ($writ_rec['execute_date'] <= '2009-03-19') {
			//آخرين حكم سال قبل
			$this_writ_year = substr(DateModules::Miladi_to_Shamsi($writ_rec['execute_date']), 0, 4);
			$one_year_ago = $this_writ_year - 1;
			$one_year_ago_last_day_writ = $one_year_ago . "/12/29";
			$Gone_year_ago_last_day = DateModules::Shamsi_to_Miladi($one_year_ago_last_day_writ);

			// اقلام فرد را بر اساس آیتم هاي حکم قبلی و با پارامترهای سال قبل محاسبه مي کند 
			$rec = self::compute_professors_salary_params(
							$writ_rec['base'], $writ_rec['base'], $writ_rec['science_level'], $writ_rec['science_level'], $writ_rec['work_state_id'], $writ_rec['work_city_id'], $Gone_year_ago_last_day, $writ_rec['emp_state'], false);

			// مبلغ فوق العاده ایثارگري را از سال قبل برمي دارد 
			$prev_year_writ_obj = manage_writ::get_last_writ_by_date($writ_rec['staff_id'], $Gone_year_ago_last_day);
			$devotion_extra = self::get_writSalaryItem_value($prev_year_writ_obj->writ_id, $prev_year_writ_obj->writ_ver, $prev_year_writ_obj->staff_id, SIT_PROFESSOR_DEVOTION_EXTRA);

			$param1 =
					$rec[0]['base_salary'] +
					$rec[0]['absorb_extra'] +
					$rec[0]['vijeh_extra'] +
					$rec[0]['special_extra'] +
					$devotion_extra;
			$value = $param1 * 0.02;
		}

		return $value;
	}

	/*******************************************************************************
	 * *
	 * * آيتمهاي حقوق كارمندان
	 * *
	 * ***************************************************************************** */
	
	private function GetGrade($writ_rec,$param6)
	{
		if($param6 == '1') // زیر دیپلم
		{
			return ($writ_rec["onduty_year"] <= 12) ? 1 : 2;			
		}
		if($param6 == '2') // دیپلم
		{
			return ($writ_rec["onduty_year"] <= 12) ? 1 : 2;			
		}
		
		/*if( $param6 == '1' || $param6 == '2' )
		{
		    return 1  ; 
		}*/

/*if($writ_rec["execute_date"] == '2015-02-20' && $writ_rec["person_type"] == 2  ) 
		{

			
			$qry = " select w1.staff_id , wsi.param3
						from (
								SELECT  w.staff_id,
										SUBSTRING_INDEX(SUBSTRING(max(CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver)),11),'.',1) writ_id,
													SUBSTRING_INDEX(max(CONCAT(w.execute_date,w.writ_id,'.',w.writ_ver)),'.',-1) writ_ver

								FROM writs w
										INNER JOIN staff ls ON(w.staff_id = ls.staff_id)

								WHERE w.issue_date < '2014-03-21' AND w.person_type = 2 AND 
								      w.corrective = 0  AND w.emp_mode not in (3,6) AND  
									  w.staff_id  = ".$writ_rec["staff_id"]."
						) w1 inner join writ_salary_items wsi
											on w1.staff_id = wsi.staff_id and
												w1.writ_id = wsi.writ_id and
												w1.writ_ver = wsi.writ_ver and wsi.salary_item_type_id = 34


						AND  w1.staff_id  = ".$writ_rec["staff_id"]; 
			
			$resG = PdoDataAccess::runquery($qry);

			$Ybase = $writ_rec["base"] - 1; 
if(isset($resG) && count($resG) > 0 ) {
			//..................مهارتی................
			if( $resG[0]['param3'] ==  2 ) 
			{
				if($param6 == 3 &&  $Ybase >= 8 ) 
				{
					$writ_rec["grade"] = 2 ; 
				}
				
				elseif($param6 == 4 &&  $Ybase >= 6 ) 
				{
					$writ_rec["grade"] = 2 ; 
				}
				
				elseif($param6 == 5 &&  $Ybase >= 5 ) 
				{
					$writ_rec["grade"] = 2 ; 
				}
				
				elseif($param6 == 6 &&  $Ybase >= 4 ) 
				{
					$writ_rec["grade"] = 2 ; 
				}	
else $writ_rec["grade"] = 1 ; 			
				
			}


			//.......................رتبه3.................			
			if( $resG[0]['param3'] ==  3 ) 
			{
				if($param6 == 3 &&  $Ybase >= 10 ) 
				{
					$writ_rec["grade"] = 3 ; 
				}
				
				elseif($param6 == 4 &&  $Ybase >= 6 ) 
				{
					$writ_rec["grade"] = 3 ; 
				}
				
				elseif($param6 == 5 &&  $Ybase >= 5 ) 
				{
					$writ_rec["grade"] = 3 ; 
				}
				
				elseif($param6 == 6 &&  $Ybase >= 4 ) 
				{
					$writ_rec["grade"] = 3 ; 
				}
else $writ_rec["grade"] = 2 ; 				
				
			}
			
			//....................رتبه 2.................... 
			
			if( $resG[0]['param3'] ==  4 ) 
			{
								
				if($param6 == 4 &&  $Ybase >= 6 ) 
				{
					$writ_rec["grade"] = 4 ; 
				}
				
				elseif($param6 == 5 &&  $Ybase >= 6 ) 
				{
					$writ_rec["grade"] = 4 ; 
				}
				
				elseif($param6 == 6 &&  $Ybase >= 6 ) 
				{
					$writ_rec["grade"] = 4 ; 
				}
                                                 else $writ_rec["grade"] = 3 ;
				
				
			}
			
			//..........................رتبه 1......................
			
			if( $resG[0]['param3'] ==  5 ) 
			{
								
				if($param6 == 5 &&  $Ybase >= 6 ) 
				{
					$writ_rec["grade"] = 5 ; 
				}
				
				elseif($param6 == 6 &&  $Ybase >= 6 ) 
				{
					$writ_rec["grade"] = 5 ; 
				}				
                           else $writ_rec["grade"] = 4 ; 
				
			}
			
			}
		}*/
if($writ_rec["staff_id"] == 401351 ){ $writ_rec["grade"] = 4 ;}

		if($writ_rec["master_education_level"] == '6') //دکتری
		{
			$query = "select * from bases 
						where BaseType in(3,4) AND 
						BaseStatus = 'NORMAL' AND
						PersonID=" . $writ_rec["PersonID"];
			$dt = PdoDataAccess::runquery($query);
			if(count($dt) != 0)
                
				return ( (int)$writ_rec["grade"] + 1 ) > 5   ? 5 :  (int)$writ_rec["grade"] + 1 ;
			
			$person_family_shohada = manage_person_devotion::get_person_devotions($writ_rec['PersonID'], 
					'(' . BEHOLDER_FAMILY_DEVOTION . ')', BOY . ',' . DAUGHTER);
			if(count($person_family_shohada) != 0)
				return ( (int)$writ_rec["grade"] + 1 ) > 5   ? 5 :  (int)$writ_rec["grade"] + 1 ;		
		}
		return (int)$writ_rec["grade"];
	}
	
	private function GetEducLevel($writ_rec)
	{
		
	   /* if($writ_rec["master_education_level"] == '1' || $writ_rec["master_education_level"] == '2' ) // زیر دیپلم
		{
			return 3 ;			
		}
	    if($writ_rec["master_education_level"] == '6') //دکتری
			return $writ_rec["master_education_level"] ;
		
		$query = "select * from bases 
					where BaseType in(3,4) AND 
					BaseStatus = 'NORMAL' AND					
					PersonID=" . $writ_rec["PersonID"];
		$dt = PdoDataAccess::runquery($query);
		if(count($dt) != 0)
			return (int)$writ_rec["master_education_level"] + 1;
			
		$person_family_shohada = manage_person_devotion::get_person_devotions($writ_rec['PersonID'], 
				'(' . BEHOLDER_FAMILY_DEVOTION . ')', BOY . ',' . DAUGHTER);
		if(count($person_family_shohada) != 0)
			return (int)$writ_rec["master_education_level"] + 1;				

		return (int)$writ_rec["master_education_level"];*/
	    
	    if($writ_rec["master_education_level"] == '6') //دکتری
			return $writ_rec["master_education_level"];
		
		$query = "select * from bases 
					where BaseType in(3,4) AND 
					BaseStatus = 'NORMAL' AND					
					PersonID=" . $writ_rec["PersonID"];
		$dt = PdoDataAccess::runquery($query);
		if(count($dt) != 0){
		    return (int)$writ_rec["master_education_level"] + 1;
		    			
			}
			
		$person_family_shohada = manage_person_devotion::get_person_devotions($writ_rec['PersonID'], 
				'(' . BEHOLDER_FAMILY_DEVOTION . ')', BOY . ',' . DAUGHTER);
		if(count($person_family_shohada) != 0) {  
			
		    return (int)$writ_rec["master_education_level"] + 1;				 
						
			}
						
		return (int)$writ_rec["master_education_level"];
	}
	
	/*
	 * حقوق رتبه و پایه
	 * تاریخ اجرا از 90/12/01
	 */
	private function compute_salary_item2_60($writ_rec) {

		//param1 : ضریب حقوقی
		//param2 : عدد مبنا
		//param3 : پایه
		//param4 : ضریب مدرک تحصیلی
		//param5 : رتبه
		//param6 : مدرک تحصیلی
		//param7 : ضریب مقدار برای مدارک دیپلم و زیر دیپلم
		//param8 : پایه استحقاقی

		// دارندگان مدرک دیپلم و زیر دیپلم
	    
	  
		$coafOfDiplom = 1;
			
				
		$this->param6 = self::GetEducLevel($writ_rec);
		$this->param5 = self::GetGrade($writ_rec,$this->param6);
		
		//...................................
		if($this->param6 == '1' || ($writ_rec["education_level"] < 122 && $writ_rec["education_level"] != 117 ) ) // زیر دیپلم
		{
			$coafOfDiplom = 0.8;
		}
		elseif($this->param6 == '2') // دیپلم
		{
			$coafOfDiplom = 0.9;
		}		
					
		if($this->param6 == 1 || $this->param6 == 2 ) {
		  
		    $this->param6 = 3 ; 
		    
		}		
	
		$this->param1 = manage_salary_params::get_salaryParam_value("", $writ_rec["sp_person_type"], 
					SPT_RIAL_COEF, $writ_rec['execute_date']);
				
		$this->param7 = $coafOfDiplom;
		$this->param2 = manage_salary_params::get_salaryParam_value("", $writ_rec["sp_person_type"], 
				SPT_BASE_GRADE, $writ_rec['execute_date'], $this->param5, $this->param6);
		
			
//.................
		$qry = " select  sum(if(ba.BaseType in (6) and ba.BaseStatus = 'NORMAL' ,ba.BaseValue,0))  TashvighiValue ,
						 sum(if(ba.BaseType in (3,4,5) and ba.BaseStatus = 'NORMAL' ,ba.BaseValue,0))  IsarValue , 
						 sum(if(ba.BaseType in (1,2,7,20,21,22,23,24,25,26,27 ) and ba.BaseStatus = 'NORMAL' ,ba.BaseValue,0)) OtherBase 
						 
				 from bases ba 
				 where ba.PersonID =".$writ_rec['PersonID']." AND ExecuteDate <= '".$writ_rec['execute_date']."'" ;
		$baseRes = parent::runquery($qry) ; 

 

//.................
		$otherPoint = (($baseRes[0]["TashvighiValue"] + $baseRes[0]["OtherBase"]) > 7) ? 7 : ($baseRes[0]["TashvighiValue"] + $baseRes[0]["OtherBase"])  ; 
		
						
		if($writ_rec["sex"] == 1 &&  $writ_rec["person_type"] == 2 && ($writ_rec["military_duration_day"] > 0 || $writ_rec["military_duration"] > 0 ) )
			{
							
			$totalDayWrt = DateModules::ymd_to_days($writ_rec["onduty_year"], $writ_rec["onduty_month"], $writ_rec["onduty_day"]) ; 			
			$totalDaySar = DateModules::ymd_to_days(0, $writ_rec["military_duration"], $writ_rec["military_duration_day"]) ; 					
				
$resDay = $totalDayWrt -  $totalDaySar  ; 
	
			$Vyear = 0 ; 
			$Vmonth = $Vday = 0 ; 
			DateModules::day_to_ymd($resDay, $Vyear, $Vmonth, $Vday) ; 
			$Vyear =  $Vyear ; 
	
			
			//echo $Vyear." ---- ".$baseRes[0]["IsarValue"]."--isa---".$otherPoint  ;  die() ; 
			}						
		 else  		
			$Vyear =  $writ_rec["onduty_year"] ;  
		
	//if($_SESSION['UserID'] == 'jafarkhani') { echo $Vyear .'---' ;  die();          }		
		// به اضافه یک می شود با توجه به تبصره 8 آیین نامه	
		$this->param8 =  /*($writ_rec['execute_date'] > '2014-03-21' && $writ_rec['base'] > 0 ) ? $writ_rec['base'] :*/ ($Vyear + 1 );		
		$this->param3 = /*$writ_rec["base"] $Vyear + 1 */ $this->param8 +  $baseRes[0]["IsarValue"] + $otherPoint  ;		
				
		$this->param4 = manage_salary_params::get_salaryParam_value("", $writ_rec["sp_person_type"], 
				SPT_EDULEVEL_COAF, $writ_rec['execute_date'], $this->param6);



		$value = $coafOfDiplom * ($this->param1 * ($this->param2 + ($this->param3 * $this->param4)));	
						
		return $value;
	}
	
	/*
	 * فوق العاده شغل
	 * تاریخ اجرا 90/12/01
	 */
	private function compute_salary_item2_61($writ_rec) {

		//param1 : حقوق رتبه و پایه
		//param2 : ضریب فوق العاده
		//param3 : ضریب سرپرستی
		//param4 : رتبه
		//param5 : درصد برای دارندگاه مدرک زیر دیپلم و دیپلم
		
		// دارندگان مدرک دیپلم و زیر دیپلم
		$coafOfDiplom = 1;
		$supervision ="" ; 
		//$this->param4 = self::GetGrade($writ_rec);
		
				
		$this->param6 = self::GetEducLevel($writ_rec);
		$this->param4 = self::GetGrade($writ_rec,$this->param6);
		/*
		if($this->param6 == '1') // زیر دیپلم
		{
			$coafOfDiplom = 0.8;
		}
		if($this->param6 == '2') // دیپلم
		{
			$coafOfDiplom = 0.9;
		}
		*/
		if($this->param6 == 1 || $this->param6 == 2 ) 
		    $this->param6 = 3 ; 
				
		
		$obj = new manage_writ_item();
		
		$this->param1 = $obj->compute_salary_item2_60($writ_rec);
		
		$this->param3 = 0 ; 
		$this->param5 = $coafOfDiplom;
		$this->param2 = manage_salary_params::get_salaryParam_value("", $writ_rec["sp_person_type"], 
				SPT_EXTRA_SHOGHL, $writ_rec['execute_date'], $this->param4);	
		
		$management = manage_posts::get_PostType($writ_rec["staff_id"], $writ_rec["post_id"]) ; 
		if($writ_rec["post_id"] > 0 && $writ_rec["post_id"]  != NULL ) {
		    if($management['post_type'] == 2 ) 
			$supervision = 1 ; 
		    else 
			$supervision = manage_posts::get_SupervisionKind($writ_rec["staff_id"], $writ_rec["post_id"]);
		}
		$this->param3 = ($supervision == "") ? 0 :
			manage_salary_params::get_salaryParam_value("", $writ_rec["sp_person_type"], 
				SPT_EXTRA_SUPERVISION_SHOGHL, $writ_rec['execute_date'], $supervision);
		
		$value = $coafOfDiplom *  $this->param2  * $this->param1 ; 		
				
		return $value;
	}
	
	/*
	 * فوق العاده جذب
	 * تاریخ اجرا 90/12/01
	 */
	private function compute_salary_item2_62($writ_rec) {

		//param1 : حقوق رتبه و پایه
		//param2 : ضریب فوق العاده
		//param3 : ضریب سرپرستی
		
		// دارندگان مدرک دیپلم و زیر دیپلم
		$coafOfDiplom = 1;
		$supervision = 0 ;
		$this->param6 = self::GetEducLevel($writ_rec);
		$this->param4 = self::GetGrade($writ_rec,$this->param6 );
		/*
		if($this->param6 == '1') // زیر دیپلم
		{
			$coafOfDiplom = 0.8;
		}
		if($this->param6 == '2') // دیپلم
		{
			$coafOfDiplom = 0.9;
		}
		*/
		if($this->param6 == '1' || $this->param6 == '2' ) 
		    $this->param6 = '3' ; 
		
		
		$obj = new manage_writ_item();
		
		$this->param1 = $obj->compute_salary_item2_60($writ_rec);
		
		$this->param5 = $coafOfDiplom;
		$this->param2 = manage_salary_params::get_salaryParam_value("", $writ_rec["sp_person_type"], 
				SPT_EXTRA_JAZB, $writ_rec['execute_date'], $this->param4);
		
		if($writ_rec["post_id"] > 0 && $writ_rec["post_id"]  != NULL ) {
		    $management = manage_posts::get_PostType($writ_rec["staff_id"], $writ_rec["post_id"]) ; 

		    if($management['post_type'] == 2 ) 
		    $supervision = 1 ; 
		    else
		    $supervision = manage_posts::get_SupervisionKind($writ_rec["staff_id"], $writ_rec["post_id"]);
		}
		$this->param3 = ($supervision == "") ? 0 :
			manage_salary_params::get_salaryParam_value("", $writ_rec["sp_person_type"], 
				SPT_EXTRA_SUPERVISION_JAZB, $writ_rec['execute_date'], $supervision);
		
		$value = $coafOfDiplom *  $this->param2 * $this->param1 ; 	
		
		return $value;
	}                             

	/*
	 * فوق العاده ویژه
	 * تاریخ اجرا 90/12/01
	 */
	private function compute_salary_item2_63($writ_rec) {

		//param1 : حقوق رتبه و پایه
		//param2 : ضریب فوق العاده
		
		// دارندگان مدرک دیپلم و زیر دیپلم
		$coafOfDiplom = 1;
		$this->param6 = self::GetEducLevel($writ_rec);
		$this->param3 = self::GetGrade($writ_rec,$this->param6);
		/*if($this->param6 == '1') // زیر دیپلم
		{
			$coafOfDiplom = 0.8;
		}
		if($this->param6 == '2') // دیپلم
		{
			$coafOfDiplom = 0.9;
		}*/
		
		if($this->param6 == '1' || $this->param6 == '2' ) 
		    $this->param6 = '3' ; 
		
		$obj = new manage_writ_item();
		
		$this->param1 = $obj->compute_salary_item2_60($writ_rec);		
				
		$this->param2 = manage_salary_params::get_salaryParam_value("", $writ_rec["sp_person_type"], 
				SPT_EXTRA_VIJHE, $writ_rec['execute_date'], $this->param3);
				
		$value = $coafOfDiplom * ($this->param1 * $this->param2);
		return $value;
	}
	
	/*
	 تفاوت تطبیق ماده 25
	 * تاریخ اجرا 90/12/01
	 */
	private function  compute_salary_item2_64 ($writ_rec){
	    
	    //.............. آخرین حکم فرد تا قبل از 1/12/91  ملاک می باشد................
				
		$query = "
			select writ_id,writ_ver,staff_id 
			from writs
			where (history_only != 1 OR history_only is null) AND
				writ_id <> ? AND staff_id=?  AND execute_date < '2013-02-19'
			order by execute_date desc,writ_ver desc";
		
		$priorWrit = parent::runquery($query, array($writ_rec["writ_id"], $writ_rec["staff_id"]));
		

		if(count($priorWrit) == 0)
			return 0;
		
		
		
		$priorWrit = $priorWrit[0];
		//..............................................
		$query = "  SELECT SUM(wsi.value) prior_value_sum
				FROM   
					writ_salary_items wsi
					JOIN salary_item_types sit ON (wsi.salary_item_type_id = sit.salary_item_type_id)
				WHERE
					wsi.writ_id = " . $priorWrit["writ_id"] . " AND
					wsi.writ_ver = " . $priorWrit["writ_ver"] . " AND
					wsi.staff_id = " . $priorWrit["staff_id"] . " AND
					sit.retired_include = 1 AND 
					sit.salary_item_type_id!=49 AND 
					sit.salary_item_type_id not in (".SIT_STAFF_SHIFT_EXTRA.",628,27) " ;
		
				
		$tmp = PdoDataAccess::runquery($query);
		
		$priorWritValue = $tmp[0]["prior_value_sum"];		
		
		//..............................................
		
		$query = "SELECT SUM(wsi.value) value_sum
				FROM   
					writ_salary_items wsi
					JOIN salary_item_types sit ON (wsi.salary_item_type_id = sit.salary_item_type_id)
				WHERE
					wsi.writ_id = " . $writ_rec["writ_id"] . " AND
					wsi.writ_ver = " . $writ_rec["writ_ver"] . " AND
					wsi.staff_id = " . $writ_rec["staff_id"] . " AND
					sit.retired_include = 1 AND 
					sit.salary_item_type_id not in ( 10327 , 10328 , 10331 ) " ;

		$tmp2 = PdoDataAccess::runquery($query);

		$currentWritValue = $tmp2[0]["value_sum"];			
		
		if($currentWritValue < $priorWritValue){
		  
		    $value = ($priorWritValue - $currentWritValue);
			
		}
		else 
			$value = 0;
		
		    return $value;
	}
	
	/*
	فوق العاده سختی شرایط محیط کار
	 * تاریخ اجرا 90/12/01
	 */
	private function  compute_salary_item2_65 ($writ_rec){

//param1 : مبلغ حقوق رتبه و پایه
		//param2 : ضریب سختی کار
		
		if($writ_rec['execute_date'] > '2015-02-19'){
			
		   $obj = new manage_writ_item();		
		   $this->param1 = $obj->compute_salary_item2_60($writ_rec);
		   
		   $qry = " select CoefDifficulty from staff where staff_id = ".$writ_rec['staff_id']; 
		   $DT = parent::runquery($qry);
		   
		   if (!count($DT) > 0)
				return 0;
		   if (!($DT[0]["CoefDifficulty"] > 0)) { 


				parent::PushException("ضریب سختی کار برای فرد تعیین نشده است.");
				return ;
			}
		   $this->param2 = $DT[0]["CoefDifficulty"];
		   
		   $value = ($this->param2 / 200) * $this->param1 ; 
		   
		   return $value ; 
			
		}
		else {
	    
	     	 $writObj = manage_writ::get_last_writ_With_salry_before_date($writ_rec['staff_id'], '2014-03-20');
			if ($writObj->writ_id) {
				$DT = parent::runquery("
                                    SELECT param1 darsad , value
                                        FROM writ_salary_items
                                        WHERE
                                            writ_id = " . $writObj->writ_id . "
                                            AND writ_ver = " . $writObj->writ_ver . "
                                            AND staff_id = " . $writObj->staff_id . "
                                            AND salary_item_type_id in (49)");

				if (!count($DT) > 0)
					return 0;
			}
			else
				return 0;
	
			if($DT[0]["darsad"] == NULL )
			{ 
			    $coef = manage_salary_params::get_salaryParam_value("", $writ_rec["sp_person_type"], 
						    SPT_RIAL_COEF, $writ_rec['execute_date']);
			    $this->param1 = $DT[0]["value"] / $coef ; 
			}
			else 
			    $this->param1 = $DT[0]["darsad"];
					
		$obj = new manage_writ_item();
		return 0.005 * ($this->param1/12) * $obj->compute_salary_item2_60($writ_rec); 
}
	}
	
	//............ کمک هزینه اولاد..............
	
	private function  compute_salary_item2_66 ($writ_rec){
	    
	    $this->param2 = $writ_rec['included_children_count'];	
	    
		if ($writ_rec['family_responsible'] != 1) {
			
			return 0;
		}

		if ($writ_rec['included_children_count'] == 0) {
			
			return 0 ;
		}
		
		$this->param2 = $writ_rec['included_children_count'];

		//به دليل اينکه ممکن است کسي حق اولاد بگيرد ولي مشمول عائله مندي نشود
		//کد زير به اينصورت تغيير کرد.
		//مثال آن مانند خانمي است که طلاق گرفته است ولي حضانت فرزندان بر عهده اوست.
		//مجددا بنا به درخواست دانشگاه به حالت اول برگشت .
		if ($writ_rec['family_responsible'] != 1) {
			parent::PushException(NOT_FAMILY_RESPONSIBLE);
			return false;
		}

		if ($writ_rec['included_children_count'] == 0) {
			parent::PushException(ZERO_INCLUDED_CHILDREN);
			return false;
		}

		$OladParam = manage_salary_params::get_salaryParam_value("", 3, 203, $writ_rec["execute_date"]);
		
		$value = $this->param2 * $OladParam;

		if (!($value > 0))
			return false;

		return $value;
	}
	
	//...........کمک هزینه عائله مندی ................
	
	private function  compute_salary_item2_67 ($writ_rec){
		
		if ($writ_rec['family_responsible'] != 1) {
			
			return 0;
		}


		
		$score = 810;
		$rial_coef = manage_salary_params::get_salaryParam_value("", $writ_rec["sp_person_type"], SPT_RIAL_COEF, $writ_rec["execute_date"]);

		$value = 0;
		if ($writ_rec['family_responsible'] == 1)
			$value = $score * $rial_coef;
		else {
			parent::PushException(NOT_FAMILY_RESPONSIBLE);
			return 0;
		}

		if (!($value > 0))
			return false;

		$this->param1 = $score;

		return $value;
		
	}
	
	//.... فوق العاده مدیریت .................
	
	private function  compute_salary_item2_68 ($writ_rec){
	
	    
	   $supervision = 0 ; 
	    if($writ_rec["post_id"] > 0 && $writ_rec["post_id"]  != NULL ) {
	    
		$management = manage_posts::get_PostType($writ_rec["staff_id"], $writ_rec["post_id"]) ; 
 
		if($management['post_type'] == 2 ) 
		    $supervision = 1 ; 
		else if($management['post_type'] == 5 )
		    $supervision = manage_posts::get_SupervisionKind($writ_rec["staff_id"], $writ_rec["post_id"]);
	    }
		 
		if($supervision == 1 )  
		{
			$coefSup = 2.8 ; 
		}
		elseif($supervision == 2 )
		{
			$coefSup = 1.5 ; 
		}
		else return 0 ;  
		//.......
		$this->param1 = $coefSup ; 
		$this->param2 = 0.783 ;  ; // ضریب A		

                if($writ_rec["execute_date"] > '2015-03-20')			
		   $this->param2 = 0.9 ; 
		
		$obj = new manage_writ_item();
		$value = $obj->compute_salary_item2_60($writ_rec) * $coefSup * $this->param2 ; 
		
		return $value ;	    
	    
	}
	
	private function  compute_salary_item2_74 ($writ_rec){
	
	    
	    $supervision = 0 ; 
	    if($writ_rec["post_id"] > 0 && $writ_rec["post_id"]  != NULL ) {
	    
		$management = manage_posts::get_PostType($writ_rec["staff_id"], $writ_rec["post_id"]) ; 
 
		if($management['post_type'] == 5 )
		    $supervision = manage_posts::get_SupervisionKind($writ_rec["staff_id"], $writ_rec["post_id"]);
	    }
		
		if($supervision == 3) 
		{			
			$coefSup = 0.85 ;
		}
		elseif($supervision == 5 )
		{
			$coefSup = 0.40 ; 
		}
		else return 0 ;  
		
		$this->param1 = $coefSup ; 
		$this->param2 =  0.783 ; 		
		if($writ_rec["execute_date"] > '2015-03-20')			
			$this->param2 = 0.9 ; 

		$obj = new manage_writ_item();
		
		$value = $obj->compute_salary_item2_60($writ_rec) * $coefSup * $this->param2 ; 
		
		return $value ;
		
		}


           //............. حق سنوات....................
		
		private function  compute_salary_item2_75 ($writ_rec){
	          
	          $qry = " select sum(value) sv
					from HRM_writ_salary_items
								where staff_id = ".$writ_rec['staff_id']." and writ_id = ".$writ_rec['writ_id']." and 
									  writ_ver = ".$writ_rec['writ_ver']." and salary_item_type_id in (1,17,2,4,3,9) " ; 
		  
		  $resVal = PdoDataAccess::runquery($qry); 
		  
                
		  $this->param1 = $resVal[0]['sv']  ;
          $this->param2 = 365   ;
		  $value =($resVal[0]['sv'] / $this->param2 ) * 30 ;
		  
		
		  return $value ;
		
		}

//........................... فوق العاده ایثارگری........................
                
                private function  compute_salary_item2_76 ($writ_rec){
	           
                // param1  حداقل حقوق
                    
                  $qry = " SELECT count(*) cn
                                FROM person_devotions pd
                                                inner join persons p on pd.PersonID = p.PersonID
                                                
                            where pd.devotion_type = 3 and
                                  pd.amount > 24 and p.personID = ".$writ_rec['PersonID']; 
                  $resVal1 = PdoDataAccess::runquery($qry); 
     
                  $qry = " SELECT count(*) cn
                                FROM person_devotions pd
                                         inner join persons p on pd.PersonID = p.PersonID                                                

                            where pd.devotion_type = 2 and p.personID = ".$writ_rec['PersonID']; 
                  $resVal2 = PdoDataAccess::runquery($qry); 

                  $qry = " SELECT  count(*) cn
                                FROM person_devotions pd
                                             inner join persons p on pd.PersonID = p.PersonID                                                
                            where pd.devotion_type = 5 and personel_relation in (5,6) and p.personID = ".$writ_rec['PersonID']; 
                  $resVal3 = PdoDataAccess::runquery($qry); 
                   
                  if( $resVal1[0]['cn'] > 0  || $resVal2[0]['cn'] > 0 || $resVal3[0]['cn'] > 0 ) 
                  {
                      $this->param1 = manage_salary_params::get_salaryParam_value("",  2 , SPT_MIN_SALARY, $writ_rec['execute_date']);                           
 
                      $value = 0.25 * $this->param1 ;
                  }
                  else
                      return 0 ;               
                  
		
		  return $value ;
		
		}			
					
	//......... بدی آب و هوا ...................
	
	private function  compute_salary_item2_69 ($writ_rec){

                if($writ_rec["execute_date"] < '2014-03-21') {
			$writObj = manage_writ::get_last_writ_With_salry_before_date($writ_rec['staff_id'], '2014-03-20');

			$this->param2 = self::compute_writ_items_sum($writObj->writ_id , $writObj->writ_ver , $writ_rec["staff_id"], '(56,35,36,34)');

			if (!($this->param2 > 0))
				return 0 ;

		//بنا به قانون جدید
			$this->param3 = manage_person_education::GetEducationalGroupLevel($writ_rec['education_level'], 'MasterID');

			$this->param1 = manage_salary_params::get_salaryParam_value("", $writ_rec["sp_person_type"], SPT_NEW_WHEATHER_COEF, $writ_rec['execute_date'], $writ_rec['work_city_id'], $writ_rec['work_state_id'], $this->param3);

			if (!$this->param1) {

				parent::PushException(WHEATHER_COEF_NOT_FOUND);
				return false;
			}



			$value = $this->param1 * $this->param2;

			if (!($value > 0)) {
				parent::PushException(WHEATHER_ITEM_CALC_ERR);
				return false;
			}
		}

               if($writ_rec['person_type'] != HR_CONTRACT ) {
               //..........رسمی ها کد قلم 46 و .................................
		$writObj = manage_writ::get_last_writ_With_salry_before_date($writ_rec['staff_id'], '2015-03-21');
	    	     
	    $value = self::compute_writ_items_sum($writObj->writ_id , $writObj->writ_ver , $writ_rec["staff_id"], '(46)');
	    	    
	    if (!($value > 0))
		    return 0 ;
		}
else if($writ_rec['person_type'] == HR_CONTRACT  ) {
	    //........................ قراردادی ها 5 درصد حقوق رتبه و پایه انها ........	    
                $obj = new manage_writ_item();
		$value = $obj->compute_salary_item2_60($writ_rec) * 0.05 ; 
		    	}    
	  
		
	    return $value;
	  
	}
	//............ فوق العاده کار با اشعه .................
	
	private function  compute_salary_item2_70 ($writ_rec){
	    
	       $writObj = manage_writ::get_last_writ_With_salry_before_date($writ_rec['staff_id'], '2009-03-21');
	       $value = self::compute_writ_items_sum($writObj->writ_id , $writObj->writ_ver , $writ_rec["staff_id"], '(19 , 619)');
	       
	        if (!($value > 0)) {
		    parent::PushException("فرد در سال 87 دارای قلم حقوقی فوق العاده کار با اشعه نمی باشد .");
		    return false;
		}
		
	    return $value;	      
	    
	}
	
	//..................... تفاوت تطبیق ماده 28 ...................
	
	private function  compute_salary_item2_71 ($writ_rec){	
	    
	     $value = 0 ;
	     
	     $NowPostTyp = manage_posts::get_PostType($writ_rec['staff_id']) ;
	    	     
	     if($NowPostTyp['post_type'] != POST_EXE_MANAGER && $NowPostTyp['post_type'] != POST_EXE_SUPERVICE ){
	    
		$writObj = manage_writ::get_last_writ_With_salry_before_date($writ_rec['staff_id'], $writ_rec['execute_date']);
		$ptype = manage_posts::get_PostType($writObj->staff_id , $writObj->post_id) ;

		if($writObj->execute_date > '2013-02-18' && ($ptype['post_type'] == POST_EXE_MANAGER || $ptype['post_type'] == POST_EXE_SUPERVICE )  ) {
		    $SumItems  = self::compute_writ_items_sum($writObj->writ_id , $writObj->writ_ver , $writ_rec["staff_id"], '(10266,10267)');
		    $value =  0.8 * $SumItems ; 		    
		}
		
		if (!($value > 0)) {
		   
		    return 0;
		}
		
		return $value;
			     
	     }     
	     
	}
	
	//....... فوق العاده نوبت کاری ............................
	
	private function compute_salary_item2_72($writ_rec) {
            
            if(  $writ_rec['execute_date'] < '2015-12-08' )  {

                $writObj = manage_writ::get_last_writ_With_salry_before_date($writ_rec['staff_id'], '2014-03-21');
                $value  = self::compute_writ_items_sum($writObj->writ_id , $writObj->writ_ver , $writ_rec["staff_id"], '(27,628)');

                if (($value > 0)) {

                    return $value;
                }

                else {
                    $value  = self::compute_writ_items_sum($writObj->writ_id , $writObj->writ_ver , $writ_rec["staff_id"], '(45)');
                    if (($value > 0)) {

                        return $value;
                    }
                }
                       
            }
            else if( $writ_rec['execute_date'] > '2015-12-07' )
            {                
                $this->param2 = $obj->compute_salary_item2_60($writ_rec);                
                
                 $value  = $this->param1 * $this->param2 ;
                 if (($value > 0)) {
                        return $value;
                 }
            }
            
            return 0 ;          
                       
	}
	
	//..........................................................................................

	/*	 * * حقوق مبنا */

	private function compute_salary_item2_01($writ_rec) {

		//param1 : گروه
		//param2 : عدد مبنا
		//param3 : ضريب ريالي

		$rial_coef = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_RIAL_COEF, $writ_rec['execute_date']);
		if (!$rial_coef) {
			parent::PushException('RIAL_COEF_NOT_FOUND');
			return false;
		}
		$value = $rial_coef * manage_writ_item::Get_employee_base_number($writ_rec['cur_group']);
		if (!($value > 0)) {
			parent::PushException(BASE_SALARY_CALC_ERR);
			return false;
		}
		$this->param1 = $writ_rec['cur_group'];
		$this->param2 = manage_writ_item::Get_employee_base_number($writ_rec['cur_group']);
		$this->param3 = $rial_coef;
		if (in_array($writ_rec['person_type'], array(1, 2, 3))) {
			$this->param4 = $rial_coef * manage_writ_item::Get_employee_base_number($writ_rec['cur_group'] - $writ_rec['hortative_group']);
		}

		return $value;
	}

	/*	 * * حداقل دريافتي */

	private function compute_salary_item2_03($writ_rec) {
		//param1 : حداقل دريافتي طبق قانون
		//param2 : مجموع اقلام دريافتي

		$this->param1 = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_MIN_SALARY, $writ_rec['execute_date']);
		if (!$this->param1) {
			parent::PushException(MIN_SALARY_NOT_FOUND);
			return false;
		}

		if (in_array($writ_rec['person_type'], array(1, 2, 3))) {
			//حقوق مبنا + افزايش سنواتي + فوق العاده شغل + تفاوت تطبيق + فوق العاده شغل برجسته
			//( 2 , 4 , 12 , 17 , 18 )
			// حقوق پايه سال قبل
			$this_writ_year = substr(DateModules::Miladi_to_Shamsi($writ_rec['execute_date']), 0, 4);
			$one_year_ago = $this_writ_year - 1;
			$one_year_ago_last_day_writ = $one_year_ago . "/12/29";
			$Gone_year_ago_last_day = DateModules::Shamsi_to_Miladi($one_year_ago_last_day_writ);
			$prior_writ_obj = manage_writ::get_last_writ_by_date($writ_rec['staff_id'], $Gone_year_ago_last_day);
			$base_salary = manage_writ_item::get_base_salary($prior_writ_obj->person_type, $prior_writ_obj->writ_id, $prior_writ_obj->writ_ver, $prior_writ_obj->staff_id);

			//ضريب بسيج
			$prior_writ_year = substr(DateModules::Miladi_to_Shamsi($prior_writ_obj->execute_date), 0, 4);
			$mob_coef = manage_writ_item::get_mobilization_coef($prior_writ_year, $writ_rec['staff_id']); //درصد بسيج سال قبل
			//افزايش بسيج
			$mob_anual_inc = $base_salary * $mob_coef;
			$annual_inc = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_STAFF_ANNUAL_INC);

			//افزايش سنواتي بدون افزايش بسيج
			// عدم محاسبه بسيج در حد اقل حقوق مربوط به سال 86 به بعد مي باشد
			if ($this_writ_year > 1385)
				$annual_inc = max($annual_inc - $mob_anual_inc, 0);

			if (!$writ_rec['hortative_group']) {
				$this->param2 = manage_writ_item::compute_writ_items_sum($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], '( ' . SIT_STAFF_BASE_SALARY . ' , ' . SIT_STAFF_ADAPTION_DIFFERENCE . ' , ' .
								SIT_STAFF_JOB_EXTRA . ' , ' . SIT_STAFF_DOMINANT_JOB_EXTRA . ' )') + $annual_inc;
			} else {
				$this->param2 = manage_writ_item::compute_writ_items_sum($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], '( ' . SIT_STAFF_BASE_SALARY . ' , ' . SIT_STAFF_ADAPTION_DIFFERENCE . ' , ' .
								SIT_STAFF_JOB_EXTRA . ' , ' . SIT_STAFF_DOMINANT_JOB_EXTRA . ' , ' . SIT_STAFF_ANNUAL_INC . ')', 'param4');
			}
		} else if (in_array($writ_rec['person_type'], array(5, 6))) {
			$this->param2 = manage_writ_item::compute_writ_items_sum($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], '( ' . SIT5_STAFF_BASE_SALARY . ' , ' . SIT5_STAFF_ANNUAL_INC . ' , ' .
							SIT5_STAFF_ADAPTION_DIFFERENCE . ' , ' . SIT5_STAFF_JOB_EXTRA . ' , ' . SIT5_STAFF_DOMINANT_JOB_EXTRA . ')');
		}
		if ($this->param2 < $this->param1)
			$value = $this->param1 - $this->param2;
		else
			$value = 0;

		return $value;
	}

	/*	 * *فوق العاده تعديل */

	private function compute_salary_item2_12($writ_rec) {
		//param1 : مجموع اقلام دريافتي
		//param2 : ميزان تعديل
		//حقوق مبنا + افزايش سنواتي + فوق العاده شغل + تفاوت تطبيق + فوق العاده شغل برجسته
		//( 2 , 4 , 12 , 17 , 18 )

		$rial_coef = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_RIAL_COEF, $writ_rec['execute_date']);
		$sum_sal_items = manage_writ_item::compute_writ_items_sum($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], '( ' .
						SIT_STAFF_BASE_SALARY . ' , ' .
						SIT_STAFF_ANNUAL_INC . ' , ' .
						SIT_STAFF_ADAPTION_DIFFERENCE . ' , ' .
						SIT_STAFF_JOB_EXTRA . ' , ' .
						SIT_STAFF_DOMINANT_JOB_EXTRA . ' , ' .
						SIT_STAFF_ABSOPPTION_EXTRA . ' , ' .
						SIT_STAFF_MIN_PAY . ' , ' .
						SIT_EMPLOYEE_SPECIAL_EXTRA . ' , ' .
						SIT_STAFF_ADAPTION_DIFFERENCE . ' )');

		$max_value = manage_writ_item::Get_employee_base_number(1) * $rial_coef * 1.5;

		if ($sum_sal_items <= 3000000)
			$value = $max_value;
		elseif ($sum_sal_items >= 5700000)
			$value = 0;
		else
			$value = (1 - (intval(($sum_sal_items - 3000000) / 300000) + 1) * 0.1) * $max_value;

		$this->param1 = $sum_sal_items;
		$this->param2 = $max_value;

		return $value;
	}

	/*	 * * حق عائله مندي */

	private function compute_salary_item2_04($writ_rec) {
		//param1 : حقوق مبناي حداقل حقوق
		//param2 : ضريب
		if ($writ_rec['family_responsible'] == 1) {
                    
			$this->param1 = ($writ_rec['person_type'] == 1 && $writ_rec['execute_date'] > '2013-09-22' )  ? manage_salary_params::get_salaryParam_value("", $writ_rec['person_type'], SPT_SALARY_COEF, $writ_rec['execute_date']) : manage_writ_item::get_min_base_salary($writ_rec['person_type'], $writ_rec['execute_date']);
			
		//	$this->param1 = manage_writ_item::get_min_base_salary($writ_rec['person_type'], $writ_rec['execute_date']);
		
			$this->param2 = ($writ_rec['person_type'] == 1 && $writ_rec['execute_date'] > '2013-09-22' ) ? 57 : 0.70 ;
			$value = $this->param1 * $this->param2;
		}
		else
			$value = 0;

		return $value;
	}

	/*	 * * حق اولاد */

	private function compute_salary_item2_05($writ_rec) {
		//param1 : تعداد فرزند مشمول
		//param2 : حقوق مبناي حداقل حقوق جدول
		//param3 : ضريب

		$this->param1 = $writ_rec['included_children_count'];

		if ($writ_rec['included_children_count'] > 0) {
			$this->param2 = ($writ_rec['person_type'] == 1 && $writ_rec['execute_date'] > '2013-09-22' )  ? manage_salary_params::get_salaryParam_value("", $writ_rec['person_type'], SPT_SALARY_COEF, $writ_rec['execute_date']) : manage_writ_item::get_min_base_salary($writ_rec['person_type'], $writ_rec['execute_date']);
			//$this->param2 = manage_writ_item::get_min_base_salary($writ_rec['person_type'], $writ_rec['execute_date']);
			$this->param3 =  ($writ_rec['person_type'] == 1 &&  $writ_rec['execute_date'] > '2013-09-22' ) ?	 15 : 0.14;
			$value = $this->param1 * $this->param2 * $this->param3;
		} else
			$value = 0;

		return $value;
	}

	/*	 * * تفاوت تطبيق */

	private function compute_salary_item2_06($writ_rec) {
		//param1 : پايه (فقط براي كارشناسان ارشد با شغل آموزشي پژوهشي)،  (input)
		//param2 : حقوق مورد تطبيق
		//param3 : حقوق و مزاياي مستمر
		//param4 : مبلغ تفاوت تطبيق جهت استفاده در محاسبه حداقل دريافتي

		if ($this->param1 > 0) { //تفاوت تطبيق از نوع طرح همترازي
			//first check : education_level == MS
			$education_level_rec = manage_person_education::GetEducationLevelByDate($writ_rec["PersonID"]);
			$education_level = $education_level_rec['max_education_level'];

			if (!($education_level == EDUCATION_LEVEL_MS ||
					$education_level == EDUCATION_LEVEL_EQUAL_MS ||
					$education_level == EDUCATION_LEVEL_DOCTORATE ||
					$education_level == EDUCATION_LEVEL_EQUAL_PHD ||
					$education_level == EDUCATION_LEVEL_PHD)) {
				parent::PushException(EDUCATION_LEVEL_MUST_BE_MS);
				return false;
			}

			//then check : job field must be educ/research
			$job_field_educ_research = manage_posts::get_job_fields($writ_rec['post_id']);
			if (!$job_field_educ_research) {
				parent::PushException(JOB_FIELD_MUST_BE_EDUC_RESEARCH);
				return false;
			}

			//آيا شخص جانباز يا آزاده است
			$is_free_sacrifice_man = manage_person_devotion::get_person_devotions($writ_rec["PersonID"], '(' . SACRIFICE_DEVOTION . ',' . FREEDOM_DEVOTION . ')');

			//then compute : salary of equvalent professor
			$writ_rec['base'] = $this->param1; //virtual professor!

			if ($education_level == EDUCATION_LEVEL_DOCTORATE ||
					$education_level == EDUCATION_LEVEL_EQUAL_PHD ||
					$education_level == EDUCATION_LEVEL_PHD ||
					count($is_free_sacrifice_man) > 0) {
				$writ_rec['science_level'] = SCIENCE_LEVEL_PHD;	//virtual professor!
			} else if ($education_level != EDUCATION_LEVEL_MS ||
					$education_level != EDUCATION_LEVEL_EQUAL_MS) {
				$writ_rec['science_level'] = SCIENCE_LEVEL_MS;	//virtual professor!
			}

			$base = $writ_rec['base'];

			$professor_base_number = manage_writ_item::Get_professor_base_number($writ_rec['science_level']);
			$salary_coef = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_SALARY_COEF, $writ_rec['execute_date']);
			if (!$salary_coef) {
				parent::PushException(SALARY_COEF_NOT_FOUND);
				return false;
			}
			$this->param1 = $base;
			$this->param2 = $professor_base_number;
			$this->param3 = $salary_coef;

			$equal_salary_base_salary = $salary_coef * ($professor_base_number + 5 * $base);
			if (!($equal_salary_base_salary > 0)) {
				parent::PushException(BASE_SALARY_CALC_ERR);
				return false;
			}

			$special_extra_coef = manage_salary_params::get_salaryParam_value("", "1", SPT_SPECIAL_EXTRA_COEF, $writ_rec['execute_date'],  $writ_rec['science_level']);

			if (!$special_extra_coef) {
				parent::PushException(SPECIAL_EXTRA_COEF_NOT_FOUND);
				return false;
			}
			$equal_salary_special_extra = $special_extra_coef * $equal_salary_base_salary;
			if (!($equal_salary_special_extra > 0)) {
				parent::PushException(SPECIAL_EXTRA_CALC_ERR);
				return false;
			}

			///////////////////////////////////////////////////////////////////////
			$this->param2 = $equal_salary_base_salary + $equal_salary_special_extra;
			if ($this->param2 <= 0) {
				parent::PushException(EQUALENT_PROFESSOR_SALARY_CALC_ERR);
				return false;
			}
			$this->param3 = manage_writ_item::compute_writ_items_sum($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], '( ' . SIT_STAFF_BASE_SALARY . ' , ' .
							SIT_STAFF_ANNUAL_INC . ' , ' .
							SIT_STAFF_MIN_PAY . ' , ' .
							SIT_STAFF_JOB_EXTRA . ' , ' .
							SIT_STAFF_DOMINANT_JOB_EXTRA . ' )');
			//چون به کساني که تفاوت تطبيق مي گيرند حداقل دريافتي تعلق نمي گيرد
			//بنابراين جمع چهار قلم
			//حقوق مبنا + افزايش سنواتي + فوق العاده شغل + فوق العاده شغل برجسته
			//ممکن است از حداقل حقوق کمترشود که در اين موارد بايد از حداقل حقوق استفاده شود.
			//کد نوشته شده زير اشتباه است و به همين دليل به صورت comment شده درآمد.
			/*
			  $min_salary = get_salary_param(SPT_MIN_SALARY, $writ_rec['execute_date']);
			  if ($param3 < $min_salary)
			  $param3 = $min_salary;
			 */

			$value = (0.8 * $this->param2) - $this->param3;
		} else { //تفاوت تطبيق از نوع تنزل گروه

			/*
			  ابتدا حكم قبلي استخراج مي گردد، اگر گروه آن پايين تر بود
			  حقوق و مزاياي مستمر آن استخراج مي گردد
			  اگر گروه آن برابر بود
			  تفاوت تطبيق از اقلام آن استخراج مي گردد
			  و با حقوق مورد تطبيق آن مقايسه مي گردد
			 */
			$this->param3 = manage_writ_item::get_continouse_salary($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"]);
			$prior_writ_obj = manage_writ::get_prior_writ($writ_rec);
			if ($prior_writ_obj->cur_group < $writ_rec['cur_group']) {
				$this->param2 = manage_writ_item::get_continouse_salary($prior_writ_obj->writ_id, $prior_writ_obj->writ_ver, $prior_writ_obj->staff_id);
				$value = $this->param3 - $this->param2;
			} else {
				//get prior param2:
				$value = manage_writ_item::get_writSalaryItem_value($prior_writ_obj->writ_id, $prior_writ_obj->writ_ver, $prior_writ_obj->staff_id, SIT_STAFF_ADAPTION_DIFFERENCE);
				$value = $this->param3 - $this->param2;
			}
		}

		if ($value > 0) {
			$min_pay = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], SIT_STAFF_MIN_PAY);
			if ($min_pay) {
				$sql = 'UPDATE  writ_salary_items
		    			SET value = 0
		    			WHERE  writ_id = ' . $writ_rec['writ_id'] . ' AND
		    				   writ_ver = ' . $writ_rec['writ_ver'] . ' AND
		    				   salary_item_type_id = ' . SIT_STAFF_MIN_PAY . ' AND 
		    				   staff_id = ' . $writ_rec['staff_id'];
				PdoDataAccess::runquery($sql);
			}
		}

		if ($value <= 0) {
			parent::PushException(MATCH_DIFFERENCE_IS_ZERO);
			return false;
		}
		$this->param4 = $value;
		return $value;
	}

	/*	 * * فوق العاده محروميت تسهيلات */

	private function compute_salary_item2_07($writ_rec) {

		//param1 : نام شهر محل خدمت
		//param2 : ضريب فوق العاده محروميت تسهيلات
		//param3 : حقوق مبنا
		//چون به حالت اشتغال ها اين قلم تعلق نمي گيرد کد زير اضافه شد.
		if ($writ_rec['emp_mode'] == EMP_MODE_ENGAGEMENT) {
			return 0;
		}

		$this->param2 = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_FACILITY_PRIVATION_COEF, $writ_rec['execute_date'], $writ_rec['work_city_id'], $writ_rec['work_state_id']);
		if ($this->param2 > 0) {
			$this->param1 = manage_cities::getCityName($writ_rec['work_city_id']);
			$this->param3 = manage_writ_item::get_base_salary($writ_rec["person_type"], $writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"]);

			if (!($this->param3 > 0))
				return false;
			$value = $this->param2 * $this > param3;
		}
		else
			$value = 0;

		return $value;
	}

	/*	 * * فوق العاده جذب مناطق محروم */

	private function compute_salary_item2_08($writ_rec) {
		//param1 : محل تولد/شغل/محل خدمت
		//param2 : ضريب فوق العاده جذب مناطق محروم
		//param3 : حقوق مبنا

		if ($writ_rec['work_city_id'] == 14 && $writ_rec['work_state_id'] == 19) {

			$birth_city_name = manage_cities::getCityName($writ_rec['birth_city_id']);

			$work_city_name = manage_cities::getCityName($writ_rec['work_city_id']);

			$job_rec = manage_posts::get_job_fields($writ_rec["post_id"]);
			$job_field_group = $job_rec["start_group"];

			$this->param2 = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_PRIVATED_ZONE_ABSORB_COEF, $writ_rec['execute_date'], "", "", "", "dim1_id<=" . $writ_rec['cur_group'] . " and dim2_id>=" . $writ_rec['cur_group']);

			//در صورتي که شخص بومي نباشد ضريب او 5% اضافه تر محاسبه مي گردد.
			if ($writ_rec['locality_type'] == ADVENTIVE)
				$this->param2 += 0.05;

			if ($this->param2 > 0) {
				$this->param1 = $birth_city_name . ' / ' . $job_rec["title"] . ' / ' . $work_city_name;
				$this->param3 = manage_writ_item::get_base_salary($writ_rec["person_type"], $writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"]);
				if (!($this->param3 > 0))
					return false;
				$value = $this->param2 * $this->param3;
			}
			else
				$value = 0;
		}
		else
			return 0;

		return $value;
	}

	/*	 * * فوق العاده محل خدمت */

	private function compute_salary_item2_09($writ_rec) {
		//param1 : مبدا انتقال   (input)
		//param2 : ضريب فوق العاده محل خدمت
		//param3 : حقوق مبنا 

		$this->param2 = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_WORK_PLACE_COEF, $writ_rec['execute_date']);
		if (!$this->param2) {
			parent::PushException(WORK_PLACE_COEF_NOT_FOUND);
			return false;
		}
		if ($this->param2 > 0) {
			$this->param3 = manage_writ_item::get_base_salary($writ_rec["person_type"], $writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"]);
			if (!($this->param3 > 0))
				return false;
			$value = $this->param2 * $this->param3;
		}
		else
			$value = 0;

		return $value;
	}

	/*	 * * فوق العاده شغل	 */

	private function compute_salary_item2_10($writ_rec) {
		//param1 : ضريب فوق العاده شغل : input
		//param2 : حقوق مبنا
		//param3 : not used
		//param4 : مبلغ فوق العاده شغل بدون در نظر گرفتن گروه تشويقي

		$this->param2 = manage_writ_item::get_base_salary($writ_rec["person_type"], $writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"]);


		if (!($this->param2 > 0))
			return false;

		if (in_array($writ_rec['person_type'], array(1, 2, 3))) {
			$nh_base_salary = manage_writ_item::get_none_hortative_base_salary($writ_rec["person_type"], $writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"]);
			$this->param4 = $this->param1 * $nh_base_salary;
		}
		$value = $this->param1 * $this->param2;



		if (!($value > 0)) {
			parent::PushException(JOB_ITEM_CALC_ERR);
			/* if( $curWrit->staff_id == '381731' )
			  {
			  echo $wsi_object->salary_item_type_id."aaaa---<br>";
			  } */
			return 0;
		}
		return $value;
	}

	/*	 * * فوق العاده شغل برجسته */

	private function compute_salary_item2_11($writ_rec) {
		//param1 : ضريب فوق العاده شغل برجسته
		//param2 : حقوق مبنا
		//param4 : مبلغ فوق العاده شغل برجسته بدون در نظر گرفتن گروه تشويقي

		$this_writ_year = substr(DateModules::Miladi_to_Shamsi($writ_rec['execute_date']), 0, 4);
		$prior_writ_year = $this_writ_year - 1;

		if ($writ_rec['emp_mode'] != EMP_MODE_ENGAGEMENT)
			$this->param1 = manage_writ_item::get_dominant_job_extra_coef($prior_writ_year, $writ_rec['staff_id']);
		else
			$this->param1 = 0.20;

		if (!$this->param1) {
			parent::PushException(HIGH_JOB_COEF_NOT_FOUND);
			return false;
		}
		$this->param2 = manage_writ_item::get_base_salary($writ_rec["person_type"], $writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"]);
		$nh_base_salary = manage_writ_item::get_none_hortative_base_salary($writ_rec["person_type"], $writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"]);

		if (!($this->param2 > 0))
			return false;
		$value = $this->param1 * $this->param2;
		$this->param4 = $this->param1 * $nh_base_salary;

		return $value;
	}

	/*	 * * فوق العاده جذب */

	private function compute_salary_item2_13($writ_rec) {

		//param1 : ضريب فوق العاده جذب : input
		//param2 : مضروب فيه (حقوق مبنا + افزايش سنواتي + فوق العاده شغل +  + حداقل دريافتي + فوق العاده شغل برجسته )

		if (in_array($writ_rec['person_type'], array(1, 2, 3))) {
			$this->param2 = manage_writ_item::compute_writ_items_sum($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], '( ' . SIT_STAFF_BASE_SALARY . ' , ' . SIT_STAFF_ANNUAL_INC . ' , ' .
							SIT_STAFF_JOB_EXTRA . ' , ' . SIT_STAFF_DOMINANT_JOB_EXTRA . ' , ' .
							SIT_STAFF_MIN_PAY . ' , ' . SIT_STAFF_ADAPTION_DIFFERENCE . ',' .
							SIT_STAFF_DEVOTION_ANNUAL_INC . ')');
		}
		// //از تاريخ 1/1/85 به بعد روش محاسبه اين قلم تغيير كرده است
		elseif (in_array($writ_rec['person_type'], array(5, 6)) &&
				DateModules::CompareDate($writ_rec['execute_date'], '2006-03-21') == -1) {
			//حقوق مبنا + افزايش سنواتي + حداقل دريافتي +
			//فوق العاده شغل + فوق العاده شغل برجسته
			$this->param2 = manage_writ_item::compute_writ_items_sum(
							$writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], '( ' . SIT5_STAFF_BASE_SALARY . ' , ' . SIT5_STAFF_ANNUAL_INC . ' , ' .
							SIT5_STAFF_JOB_EXTRA . ' , ' . SIT5_STAFF_DOMINANT_JOB_EXTRA . ' , ' .
							SIT5_STAFF_MIN_PAY . ' )');
		} elseif (in_array($writ_rec['person_type'], array(5, 6)) &&
				DateModules::CompareDate($writ_rec['execute_date'], '2006-03-21') != -1) {
			//(حقوق مبنا + افزايش سنواتي + فوق العاده شغل +  حداقل دريافتي
			//+ فوق العاده شغل برجسته + تفاوت تطبيق + تفاوت همترازي)
			$this->param2 = manage_writ_item::compute_writ_items_sum($writ_rec['writ_id'], $writ_rec['writ_ver'], $writ_rec['staff_id'], '( ' . SIT5_STAFF_BASE_SALARY . ' , ' . SIT5_STAFF_ANNUAL_INC . ' , ' .
							SIT5_STAFF_JOB_EXTRA . ' , ' . SIT5_STAFF_DOMINANT_JOB_EXTRA . ' , ' .
							SIT5_STAFF_MIN_PAY . ' , ' . SIT5_STAFF_ADAPTION_DIFFERENCE . ')');
		}
		//چون به کساني که تفاوت تطبيق مي گيرند حداقل دريافتي تعلق نمي گيرد
		//بنابراين جمع چهار قلم
		//حقوق مبنا + افزايش سنواتي + فوق العاده شغل + فوق العاده شغل برجسته
		//ممکن است از حداقل حقوق کمترشود که در اين موارد بايد از حداقل حقوق استفاده شود.

		$min_salary = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_MIN_SALARY, $writ_rec['execute_date']);

		if ($this->param2 < $min_salary)
			$this->param2 = $min_salary;

		$value = $this->param1 * $this->param2;
		if (!($value > 0)) {
			parent::PushException(ABSORB_ITEM_CALC_ERR);
			return 0;
		}
		return $value;
	}

	/*	 * * Not Used */

	private function compute_salary_item2_14($writ_rec) {
		return 0;
	}

	/*	 * * فوق العاده بدي آب و هوا */

	private function compute_salary_item2_15($writ_rec) {
		//param1 : ضريب بدي أب و هوا
		//param2 : حقوق مبنا
		//چون به حالت اشتغال ها اين قلم تعلق نمي گيرد کد زير اضافه شد.
		if ($writ_rec['emp_mode'] == EMP_MODE_ENGAGEMENT || $writ_rec['emp_mode'] == EMP_MODE_EDUCATIONAL_MISSION) {
			return 0;
		}

		$this->param1 = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_WHEATHER_COEF, $writ_rec['execute_date']);


		if (!$this->param1) {
			parent::PushException(WHEATHER_COEF_NOT_FOUND);
			return false;
		}
		$this->param2 = manage_writ_item::get_base_salary($writ_rec["person_type"], $writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"]);
		if (!($this->param2 > 0))
			return false;

		$value = $this->param1 * $this->param2;
		if (!($value > 0)) {
			parent::PushException(WHEATHER_ITEM_CALC_ERR);
			return false;
		}
		return $value;
	}

	/*	 * * فوق العاده ويژه */

	private function compute_salary_item2_16($writ_rec) {
		//param1 : امتياز پژوهشي : input
		//param2 : حقوق مبنا + افزايش سنواتي + فوق العاده شغل + فوق العاده شغل برجسته +
		//فوق     العاده جذب + فوق العاده جذب 7%
		//حداقل دريافتي به جمع اقلام فوق بايد اضافه گردد
		//param3 : ضريب فوق العاده ويژه
		//چون از ابتداي سال 1385(01/01/1385) جذب 7% از فوق العاده ويژه حذف شده
		//کد زير اضافه گرديد.
		$this_writ_year = substr(DateModules::Miladi_to_Shamsi($writ_rec['execute_date']), 0, 4);

		if ($this_writ_year < 1385 && in_array($writ_rec['person_type'], array(1, 2, 3))) {
			$items_set = '(' . SIT_STAFF_BASE_SALARY . ',' . SIT_STAFF_ANNUAL_INC . ',' . SIT_STAFF_JOB_EXTRA . ',' .
					SIT_STAFF_DOMINANT_JOB_EXTRA . ',' . SIT_STAFF_ABSOPPTION_EXTRA . ',' .
					SIT_EMPLOYEE_SEVEN_PERCENT_ABSORB_EXTRA . ',' . SIT_STAFF_MIN_PAY . ')';
		} elseif ($this_writ_year >= 1385 && in_array($writ_rec['person_type'], array(1, 2, 3))) {
			$items_set = '(' . SIT_STAFF_BASE_SALARY . ',' . SIT_STAFF_ANNUAL_INC . ',' . SIT_STAFF_JOB_EXTRA . ',' .
					SIT_STAFF_DOMINANT_JOB_EXTRA . ',' . SIT_STAFF_ABSOPPTION_EXTRA . ',' . SIT_STAFF_MIN_PAY . ')';
		} elseif ($this_writ_year < 1385 && in_array($writ_rec['person_type'], array(5, 6))) {
			$items_set = '(' . SIT5_STAFF_BASE_SALARY . ',' . SIT5_STAFF_ANNUAL_INC . ',' . SIT5_STAFF_JOB_EXTRA . ',' . SIT5_STAFF_DOMINANT_JOB_EXTRA . ',' . SIT5_STAFF_ABSOPPTION_EXTRA . ',' . SIT5_EMPLOYEE_SEVEN_PERCENT_ABSORB_EXTRA . ',' . SIT5_STAFF_MIN_PAY . ')';
		} elseif ($this_writ_year >= 1385 && in_array($writ_rec['person_type'], array(5, 6))) {
			$items_set = '(' . SIT5_STAFF_BASE_SALARY . ',' . SIT5_STAFF_ANNUAL_INC . ',' . SIT5_STAFF_JOB_EXTRA . ',' . SIT5_STAFF_DOMINANT_JOB_EXTRA . ',' . SIT5_STAFF_ABSOPPTION_EXTRA . ',' . SIT5_STAFF_MIN_PAY . ')';
		}

		$this->param2 = manage_writ_item::compute_writ_items_sum($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], $items_set);

		if (($this->param1 >= 600) && ($this->param1 < 1100))
			$this->param3 = 0.35;
		else if (($this->param1 >= 1100) && ($this->param1 < 1600))
			$this->param3 = 0.55;
		else if ($this->param1 >= 1600)
			$this->param3 = 0.75;
		else {

			parent::PushException(NOT_ENOUGH_SCORE_ERR);
			return false;
		}

		$value = $this->param3 * $this->param2;

		return $value;
	}

	/*	 * * فوق العاده جذب 7% */

	private function compute_salary_item2_17($writ_rec) {
		//param1 : ضريب (7%)
		//param2 : فوق العاده جذب
		//param3 : not used
		//در صورتي که ضريب به صورت دستي تنظيم نشده است از حکم قبلي بردارد
		if (!$this->param1) {
			$prior_writ_obj = manage_writ::get_prior_writ($writ_rec);
			if (!empty($prior_writ_obj->writ_id)) {
				$prior_obj = new manage_writ_item($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"],
								(in_array($writ_rec['person_type'], array(1, 2, 3))) ?
										SIT_EMPLOYEE_SEVEN_PERCENT_ABSORB_EXTRA :
										SIT5_EMPLOYEE_SEVEN_PERCENT_ABSORB_EXTRA);
			}
			if (empty($prior_writ_obj->writ_id) || $prior_obj->value <= 0)
				$this->param1 = 0;
			else
				$this->param1 = $prior_obj->param1;
		}
		//    $param1 = 0.07;
		//    $param2 = get_salary_param( SIT_STAFF_ABSOPPTION_EXTRA, $writ_rec['execute_date']);
		//حقوق مبنا + افزايش سنواتي + حداقل دريافتي +
		//فوق العاده شغل + فوق العاده شغل برجسته
		if (in_array($writ_rec['person_type'], array(1, 2, 3))) {
			$this->param2 = manage_writ_item::compute_writ_items_sum($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], '( ' . SIT_STAFF_BASE_SALARY . ' , ' . SIT_STAFF_ANNUAL_INC . ' , ' . SIT_STAFF_JOB_EXTRA . ' , ' .
							SIT_STAFF_DOMINANT_JOB_EXTRA . ' , ' . SIT_STAFF_MIN_PAY . ' )');
		}
		if (in_array($writ_rec['person_type'], array(5, 6))) {
			$this->param2 = manage_writ_item::compute_writ_items_sum($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], '( ' . SIT5_STAFF_BASE_SALARY . ' , ' . SIT5_STAFF_ANNUAL_INC . ' , ' . SIT5_STAFF_JOB_EXTRA . ' , ' .
							SIT5_STAFF_DOMINANT_JOB_EXTRA . ' , ' . SIT5_STAFF_MIN_PAY . ' )');
		}
		//چون به کساني که تفاوت تطبيق مي گيرند حداقل دريافتي تعلق نمي گيرد
		//بنابراين جمع چهار قلم
		//حقوق مبنا + افزايش سنواتي + فوق العاده شغل + فوق العاده شغل برجسته
		//ممکن است از حداقل حقوق کمترشود که در اين موارد بايد از حداقل حقوق استفاده شود.
		$min_salary = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_MIN_SALARY, $writ_rec['execute_date']);
		if ($this->param2 < $min_salary)
			$this->param2 = $min_salary;

		$value = $this->param1 * $this->param2;
		if (!($value > 0)) {
			parent::PushException(SEVEN_PERCENT_ABSORB_ITEM_CALC_ERR);
			return false;
		}

		return $value;
	}

	/*	 * * فوق العاده نوبت کاري */

	private function compute_salary_item2_18($writ_rec) {
		//param1 : ضريب 35%
		//param2 : فوق العاده جذب
		//چون به حالت اشتغال ها اين قلم تعلق نمي گيرد کد زير اضافه شد.
		if ($writ_rec['emp_mode'] == EMP_MODE_ENGAGEMENT) {
			return 0;
		}

		$rial_coef = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_RIAL_COEF, $writ_rec['execute_date']);
		if (!$rial_coef) {
			parent::PushException(RIAL_COEF_NOT_FOUND);
			return false;
		}
		$max_work_shift = $rial_coef * manage_writ_item::Get_employee_base_number(1) * 0.35;

		$this->param2 = manage_writ_item::get_base_salary($writ_rec["person_type"], $writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"]);

		$value = $this->param1 * $this->param2;

		//فوق العاده نوبت کاري نبايد از 35% حداقل حقوق جدول (گروه 1) تجاوز کند.
		if ($value > $max_work_shift) {
			$value = $max_work_shift;
		}

		if (!($value > 0)) {
			parent::PushException(WORK_SHIFT_ITEM_CALC_ERR);
			return false;
		}

		return $value;
	}

	/*	 * * فوق العاده جذب هيات امنا */

	private function compute_salary_item2_19($writ_rec) {
		//param1 : حقوق مبنا + افزايش سنواتي + فوق العاده شغل + حداقل دريافتي + فوق العاده شغل برجسته
		//param2 : ضريب فوق العاده جذب ويژه
		//param3 : نمره ارزشيابي
		//در صورتي که شخص جانباز باشد از ضريب يک مقطع بالاتر استفاده مي کند.
		// بنا به درخواست آقای دلکلاله برای همه افراد دیده می شود .
		// if($writ_rec['person_type'] != HR_CONTRACT &&  $writ_rec['person_type'] != HR_WORKER ) {


			
			

		

		$qry = " SELECT pd.personid
                             FROM   person_devotions pd
                             WHERE  (pd.devotion_type IN (" . DEVOTION_TYPE_WOUNDED . "," . DEVOTION_TYPE_PRISONER . ") OR
                                    (pd.devotion_type = " . BEHOLDER_FAMILY_DEVOTION . " AND
                                    (pd.personel_relation = " . BOY . " OR pd.personel_relation = " . DAUGHTER . "))) AND
                                     pd.PersonID = " . $writ_rec['personID'];
		$resDev = parent::runquery($qry);


		if (count($resDev) > 0)
			$personDev = true;
		else
			$personDev = false;

		// }
		// else
		// {
		// $personDev = false ;
		//}
		//..........................................................
		//چون فرمول براي قبل از سال 85 و بعد از آن متفاوت است کد به صورت زير اصلاح شد.
		$jexecute_date = DateModules::Miladi_to_Shamsi($writ_rec['execute_date']);
		list($year, $month, $day) = explode('/', $jexecute_date);
		$education_level = $writ_rec['education_level'];
		if ($year <= 1384) {
			//در صورتي که شخص جانباز باشد از ضريب يک مقطع بالاتر استفاده مي کند.
			//$person_devotions = manage_person_devotion::get_person_devotions($writ_rec["PersonID"],
			//	'('.DEVOTION_TYPE_WOUNDED.','.DEVOTION_TYPE_PRISONER.')');
			//حقوق مبنا + افزايش سنواتي + حداقل دريافتي + فوق العاده شغل + فوق العاده شغل برجسته
			$this->param1 = manage_writ_item::compute_writ_items_sum($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], '( ' . SIT_STAFF_BASE_SALARY . ' , ' . SIT_STAFF_ANNUAL_INC . ' , ' . SIT_STAFF_JOB_EXTRA . ' , ' .
							SIT_STAFF_DOMINANT_JOB_EXTRA . ' , ' . SIT_STAFF_MIN_PAY . ' )');

			//در صورتي که مجموع اقلام فوق کمتر از حداقل دريافتي شود
			//حداقل دريافتي براي محاسبات ملاک خواهد بود.
			$min_salary = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_MIN_SALARY, $writ_rec['execute_date']);
			if ($this->param1 < $min_salary)
				$this->param1 = $min_salary;


			if (($education_level <= EDUCATION_LEVEL_SEVENTH_GRADE &&
					$education_level != EDUCATION_LEVEL_NEW_END_HIGH_SCHOOL) &&
					$personDev == true) {
				$this->param2 = 0.40;
			} else if (($education_level <= EDUCATION_LEVEL_SEVENTH_GRADE &&
					$education_level != EDUCATION_LEVEL_NEW_END_HIGH_SCHOOL)) {
				$this->param2 = 0.35;
			} else if (($education_level <= EDUCATION_LEVEL_THIRTH_HONARESTAN &&
					$education_level != EDUCATION_LEVEL_NEW_END_HIGH_SCHOOL) &&
					$personDev == true) {
				$this->param2 = 0.50;
			} else if (($education_level <= EDUCATION_LEVEL_THIRTH_HONARESTAN &&
					$education_level != EDUCATION_LEVEL_NEW_END_HIGH_SCHOOL)) {
				$this->param2 = 0.40;
			} else if (($education_level <= EDUCATION_LEVEL_ALL_DIPLOMA ||
					$education_level == EDUCATION_LEVEL_NEW_END_HIGH_SCHOOL) &&
					$personDev == true) {
				$this->param2 = 0.65;
			} else if ($education_level <= EDUCATION_LEVEL_ALL_DIPLOMA ||
					$education_level == EDUCATION_LEVEL_NEW_END_HIGH_SCHOOL) {
				$this->param2 = 0.50;
			} else if (($education_level == EDUCATION_LEVEL_HIGH_DIPLOMA ||
					$education_level == EDUCATION_LEVEL_EQUAL_HIGH_DIPLOMA) &&
					$personDev == true) {
				$this->param2 = 0.85;
			} else if ($education_level == EDUCATION_LEVEL_HIGH_DIPLOMA ||
					$education_level == EDUCATION_LEVEL_EQUAL_HIGH_DIPLOMA) {
				$this->param2 = 0.65;
			} else if (($education_level == EDUCATION_LEVEL_BS ||
					$education_level == EDUCATION_LEVEL_EQUAL_BS) &&
					$personDev == true) {
				$this->param2 = 1;
			} else if ($education_level == EDUCATION_LEVEL_BS ||
					$education_level == EDUCATION_LEVEL_EQUAL_BS) {
				$this->param2 = 0.85;
			} else if ($education_level == EDUCATION_LEVEL_MS ||
					$education_level == EDUCATION_LEVEL_EQUAL_MS) {
				$this->param2 = 1;
			}

			$writ_year = substr(DateModules::Miladi_to_Shamsi($writ_rec['execute_date']), 0, 4);
			$writ_year--;
			$this->param3 = manage_writ_item::get_evaluation_scores_sum($writ_year, $writ_rec['staff_id']);

			$multiplicant = $this->param2 * ($this->param3 / 100) + 0.0000001;
			$multiplicant = round($multiplicant, 2);
			$value = $this->param1 * $multiplicant;

			if (!($value > 0)) {
				parent::PushException(SPECIAL_ABSORB_EXTRA_ITEM_CALC_ERR);
				return false;
			}
		} else if ($year >= 1385 && $year < 1388) {
			$date = DateModules::Shamsi_to_Miladi('1384/12/29');
			$last_writ_obj = manage_writ::get_last_writ_by_date($writ_rec['staff_id'], $date);
			if (!empty($last_writ_obj->writ_id)) {
				$value = manage_writ_item::get_writSalaryItem_value($last_writ_obj->writ_id, $last_writ_obj->writ_ver, $last_writ_obj->staff_id, SIT_STAFF_HEIAT_OMANA_SPECIAL_EXTRA);
			}

			if (!($value > 0)) {
				parent::PushException(SPECIAL_ABSORB_EXTRA_ITEM_CALC_ERR);
				return false;
			}
		} else if ($year >= 1390) {

			if ($education_level < EDUCATION_LEVEL_DIPLOMA &&
					$education_level != EDUCATION_LEVEL_NEW_END_HIGH_SCHOOL &&
					$personDev == true) {
				$education_level = DIPLOMA_LEVEL;
			} else if ($education_level < EDUCATION_LEVEL_DIPLOMA &&
					$education_level != EDUCATION_LEVEL_NEW_END_HIGH_SCHOOL) {
				$education_level = UNDER_DIPLOMA_LEVEL;
			} else if (($education_level <= EDUCATION_LEVEL_ALL_DIPLOMA ||
					$education_level == EDUCATION_LEVEL_THREE_YEAR_HIGH_SCHOOL ||
					$education_level == EDUCATION_LEVEL_THREE_YEAR_HIGH_SCHOOL_WITHOUT_SCORE ||
					$education_level == EDUCATION_LEVEL_NEW_END_HIGH_SCHOOL) &&
					$personDev == true) {
				$education_level = HIGH_DIPLOMA_LEVEL;
			} else if ($education_level <= EDUCATION_LEVEL_ALL_DIPLOMA ||
					$education_level == EDUCATION_LEVEL_THREE_YEAR_HIGH_SCHOOL ||
					$education_level == EDUCATION_LEVEL_THREE_YEAR_HIGH_SCHOOL_WITHOUT_SCORE ||
					$education_level == EDUCATION_LEVEL_NEW_END_HIGH_SCHOOL) {
				$education_level = DIPLOMA_LEVEL;
			} else if (($education_level == EDUCATION_LEVEL_HIGH_DIPLOMA ||
					$education_level == EDUCATION_LEVEL_EQUAL_HIGH_DIPLOMA) &&
					$personDev == true) {
				$education_level = BS_LEVEL;
			} else if ($education_level == EDUCATION_LEVEL_HIGH_DIPLOMA ||
					$education_level == EDUCATION_LEVEL_EQUAL_HIGH_DIPLOMA) {
				$education_level = HIGH_DIPLOMA_LEVEL;
			} else if (($education_level == EDUCATION_LEVEL_BS ||
					$education_level == EDUCATION_LEVEL_EQUAL_BS) &&
					$personDev == true) {
				$education_level = MS_LEVEL;
			} else if ($education_level == EDUCATION_LEVEL_BS ||
					$education_level == EDUCATION_LEVEL_EQUAL_BS) {
				$education_level = BS_LEVEL;
			} else if (($education_level == EDUCATION_LEVEL_MS ||
					$education_level == EDUCATION_LEVEL_HOZE_LEVEL3_EQUAL_MS ||
					$education_level == EDUCATION_LEVEL_EQUAL_MS) &&
					$personDev == true) {
				$education_level = PHD_LEVEL;
			} else if ($education_level == EDUCATION_LEVEL_MS ||
					$education_level == EDUCATION_LEVEL_HOZE_LEVEL3_EQUAL_MS ||
					$education_level == EDUCATION_LEVEL_EQUAL_MS) {
				$education_level = MS_LEVEL;
			} else if ($education_level >= EDUCATION_LEVEL_DOCTORATE) {
				$education_level = PHD_LEVEL;
			}

			switch ($education_level) {
				case PHD_LEVEL :
					$this->param1 = 'دکتری';
					break;
				case MS_LEVEL :
					$this->param1 = 'کارشناسی ارشد';
					break;
				case BS_LEVEL :
					$this->param1 = 'کارشناسی';
					break;
				case HIGH_DIPLOMA_LEVEL :
					$this->param1 = 'فوق دیپلم';
					break;
				case DIPLOMA_LEVEL :
					$this->param1 = 'دیپلم';
					break;
				case UNDER_DIPLOMA_LEVEL :
					$this->param1 = 'زیر دیپلم';
					break;
			}

			$this->param4 = $writ_rec['onduty_year'] . ' سال و ' . $writ_rec['onduty_month'] . ' ماه و ' . $writ_rec['onduty_day'] . ' روز';

			//بررسی سنوات خدمت و اختلاف آن با سنوات خدمت در حکم.
			unset($duty_duration);
			$duty_duration_year = 0;
			$duty_duration_month = 0;
			$duty_duration_day = 0;

			$duty_qry = " SELECT s.work_start_date,
	    						 p.military_duration,
	    						 p.military_duration_day
	    				 FROM    staff s
	    				 		INNER JOIN persons p
	    				 			  ON (s.PersonID = p.PersonID)
	    				 WHERE s.staff_id = " . $writ_rec['staff_id'];

			$duty_res = parent::runquery($duty_qry);

			if ($duty_res[0]['work_start_date']) {

				$duty_duration = DateModules::GDateMinusGDate($writ_rec['execute_date'], $duty_res[0]['work_start_date']) +
						(floor($duty_res[0]['military_duration'] * 30.4375 + $duty_res[0]['military_duration_day'])) + 1;
				$duty_duration_year = floor($duty_duration / 366.25);
				$duty_duration_month = floor(($duty_duration - ($duty_duration_year * 366.25)) / 30);
				$duty_duration_day = floor($duty_duration - ($duty_duration_year * 366.25) - floor($duty_duration_month * 30));
			}

			$emp_qry = " SELECT SUM(pe.retired_duration_year) retired_duration_year,
	    						SUM(pe.retired_duration_month) retired_duration_month,
	    						SUM(pe.retired_duration_day) retired_duration_day
	    				  FROM   person_employments pe
                                 WHERE pe.PersonID = " . $writ_rec['PersonID'];

			$emp_res = parent::runquery($emp_qry);

			$duty_duration_year += $emp_res[0]['retired_duration_year'];
			$duty_duration_month += $emp_res[0]['retired_duration_month'];
			$duty_duration_day += $emp_res[0]['retired_duration_day'];

			//...........................................................................
			if ($duty_duration_day >= 30) {
				$duty_duration_month += floor($duty_duration_day / 30);
				$duty_duration_day -= floor(floor($duty_duration_day / 30) * 30);
			}

			if ($duty_duration_month >= 12) {
				$duty_duration_year += floor($duty_duration_month / 12);
				$duty_duration_month -= floor($duty_duration_month / 12) * 12;
			}

			$this->param2 = $duty_duration_year . ' سال و ' . $duty_duration_month . ' ماه و ' . $duty_duration_day . ' روز';

			$pqry = " SELECT value
        				 FROM  salary_params sp
        				 WHERE sp.param_type = " . SPT_HEIAT_OMANA_ABSORBTION_VALUE . " AND
					       sp.person_type = ".$writ_rec['person_type']." AND 	
        				       sp.from_date <= '" . $writ_rec['execute_date'] . "' AND
        				       sp.to_date >= '" . $writ_rec['execute_date'] . "' AND 
        				       sp.dim1_id = " . $education_level . " AND
        				       sp.dim2_id > " . $writ_rec['onduty_year'] . "
        				 ORDER BY sp.from_date DESC,sp.to_date DESC, dim2_id
        				 LIMIT 1 ";

			$pres = parent::runquery($pqry);
			if (count($pres) < 1) {
				parent::PushException(NOT_DEFINE_HEIAT_OMANA_PARAM);
				return false;
			}
			$value = $pres[0]['value'];
			$this->param3 = $value;
		}
		
		return $value;
	}

	/*	 * * فوق العاده جذب 2 */

	private function compute_salary_item2_32($writ_rec) {
		//param1 : فوق العاده تعديل سال قبل
		//param2 :
		//param3 :

		$adjast_extra_value = 0;

		//به دست آوردن فوق العاده تعديل سال قبل
		$date = DateModules::Shamsi_to_Miladi("1386/12/29");
		$last_writ_obj = manage_writ::get_last_writ_by_date($writ_rec['staff_id'], $date, SIT_STAFF_ADJUST_EXTRA);
		if (!empty($last_writ_obj->writ_id)) {
			$adjast_extra_value = manage_writ_item::get_writSalaryItem_value($last_writ_obj->writ_id, $last_writ_obj->writ_ver, $last_writ_obj->staff_id, SIT_STAFF_ADJUST_EXTRA);
		}
		$this->param1 = $adjast_extra_value;
		$value = $adjast_extra_value * 1.5;
		if (!($value > 0)) {
			return false;
		}
		return $value;
	}

	/** افزايش سنواتي */
	private function compute_salary_item2_02($writ_rec) {
		//param1 : افزايش سنوات سال قبل
		//param2 : ضريب افزايش سنواتي
		//param3 : درصد بسيج
		//param4 : مبلغ افزايش سنواتي بدون در نظر گرفتن گروه تشويقي و درصد بسيج
		//_____________________________________
		// چک کن که حکم قبلي وجود دارد يا خير

		$this_writ_year = substr(DateModules::Miladi_to_Shamsi($writ_rec['execute_date']), 0, 4);
		$one_year_ago = $this_writ_year - 1;
		$one_year_ago_first_day = $one_year_ago . "/01/01";
		$one_year_ago_last_day = $one_year_ago . "/12/30";
		$one_year_ago_last_day_writ = $one_year_ago . "/12/29";
		$Gone_year_ago_first_day = DateModules::Shamsi_to_Miladi($one_year_ago_first_day);
		$Gone_year_ago_last_day = DateModules::Shamsi_to_Miladi($one_year_ago_last_day_writ);
		$prior_writ = manage_writ::get_last_writ_by_date($writ_rec['staff_id'], DateModules::AddToGDate($writ_rec['execute_date'], -1, 0, 0));

		// در صورتی که حکم قبلی وجود داشته باشد که در همان سال باشد افزایش سنواتی آن تغییر نمی کند .
		if (!empty($prior_writ->writ_id)) {
			if (!DateModules::similar_year($writ_rec['execute_date'], $prior_writ->execute_date)) {
				$obj = new manage_writ_item($prior_writ->writ_id, $prior_writ->writ_ver, $prior_writ->staff_id,
								($prior_writ->person_type == 5 ) ? SIT5_STAFF_ANNUAL_INC : SIT_STAFF_ANNUAL_INC);
				if (!empty($obj->writ_id)) {
					$this->param1 = $obj->param1;
					$this->param2 = $obj->param2;
					$this->param3 = $obj->param3;

					if (!$obj->param4)
						$this->param4 = $obj->value;

					return $obj->value;
				}
			}
		}

		//آخرين حکم قبل از سال شخص را استخراج مي کند.
		$prior_writ = manage_writ::get_last_writ_by_date($writ_rec['staff_id'], $Gone_year_ago_last_day);

		$prior_writ_year = substr(DateModules::Miladi_to_Shamsi($prior_writ->execute_date), 0, 4);

		//____________________________________________________
		//اولين حكم- محاسبه افزايش سنواتي در بدو استخدام
		if (empty($prior_writ->writ_id)) {
			//در بدو استخدام: جانبازي + آزادگي + سربازي + جبهه
			$this->param1 = 0;
			$this->param2 = 0;
			$this->param3 = 0;
			$this->value = 0;

			if ($writ_rec['military_duration'] > 30)
				$writ_rec['military_duration'] = 30;

			//سربازي : هر ماه 0/25% سالي 3% ، حداكثر 2/5 سال
			$this->param2 += $writ_rec['military_duration'] * 0.0025;

			if ($writ_rec['execute_date'] < DateModules::Shamsi_to_Miladi('1384-11-12')) {
				$temp = manage_person_devotion::GetAllDevotions("d.PersonID=" . $writ_rec["personID"]);

				for ($i = 0; $i < count($temp); $i++) {
					switch ($temp[$i]['devotion_type']) {
						case DEVOTION_TYPE_FIGHTER://رزمندگي
							//جبهه : ماهي 5/0% سالي 6% (سوابق تا قبل از 29/5/69
							$this->param2 += ($temp[$i]['amount'] / 30) * 0.005;
							break;
						case DEVOTION_TYPE_PRISONER://آزادگي
							//- آزادگي : ماهي 0.5% سالي 6%
							$this->param2 += ($temp[$i]['amount'] / 30) * 0.005;
							break;
						case DEVOTION_TYPE_WOUNDED://جانبازي
							//- جانبازي : هر 1% جانبازي 6/0%
							$this->param2 += $temp[$i]['amount'] * 0.006;
							break;
					}
				}
			}
			//____________________________________________
			// حقوق مبنا
			$base_salary = manage_writ::get_base_salary($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], $writ_rec["person_type"]);

			if (in_array($writ_rec['person_type'], array(1, 2, 3))) {
				// حقوق مبنا بدون گروه تشویقی
				$nh_base_salary = manage_writ::get_base_salary($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], $writ_rec["person_type"], false);
			}
			$value = $base_salary * $this->param2;
			if (in_array($writ_rec['person_type'], array(1, 2, 3))) {
				$this->param4 = $nh_base_salary * $this->param2;
			}

			return $value;
		}
		//احكام طول دوره خدمت
		else {
			//افزايش سنواتي سال قبل
			$obj = new manage_writ_item($prior_writ->writ_id, $prior_writ->writ_ver, $prior_writ->staff_id,
							($prior_writ->person_type == 5 ) ? SIT5_STAFF_ANNUAL_INC : SIT_STAFF_ANNUAL_INC);
			$this->param1 = $obj->value;
			$this->param2 = $obj->param2;
			$this->param3 = $obj->param3;
			$this->param4 = $obj->param4;
			$this->param5 = $obj->param5;
			$this->param6 = $obj->param6;
			$this->param7 = $obj->param7;

			if ($writ_rec['emp_mode'] != EMP_MODE_ENGAGEMENT && in_array($writ_rec['person_type'], array(1, 2, 3))) {
				//ضريب افزايش سنواتي سال قبل
				$this->param2 = manage_writ_item::get_annual_coef($prior_writ_year, $writ_rec['staff_id']);
			} else {
				$this->param2 = 0.05;
			}

			if ($writ_rec['execute_date'] < DateModules::Shamsi_to_Miladi('1384/11/12')) {
				//درصد بسيج سال قبل
				$this->param3 = manage_writ_item::get_mobilization_coef($prior_writ_year, $writ_rec['staff_id']);
			} else if (manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], 287) > 0)
				$this->param3 = 0;
			else
				$this->param3 = manage_writ_item::get_mobilization_coef($prior_writ_year, $writ_rec['staff_id']);

			$base_salary = manage_writ::get_base_salary($prior_writ->writ_id, $prior_writ->writ_ver, $prior_writ->staff_id, $prior_writ->person_type);

			if (in_array($writ_rec['person_type'], array(1, 2, 3))) {
				$nh_base_salary = manage_writ::get_base_salary($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], $writ_rec["person_type"], false);
			}

			if (!($base_salary > 0))
				return false;


			$duplicate_duration = 0;
			if (!empty($writ_rec['last_retired_pay'])) {
				if ($writ_rec['last_retired_pay'] >= $Gone_year_ago_first_day &&
						$writ_rec['last_retired_pay'] < $Gone_year_ago_last_day) {
					$jlast_retired_pay = DateModules::Miladi_to_Shamsi($writ_rec['last_retired_pay']);
					$duplicate_duration = ceil(DateModules::getDateDiff($one_year_ago_last_day, $jlast_retired_pay));
				} else if ($writ_rec['last_retired_pay'] <= $Gone_year_ago_first_day) {
					$duplicate_duration = 360;
				}
			}

			$year_work_days = manage_staff::compute_year_work_days($writ_rec['staff_id'], $Gone_year_ago_first_day, $Gone_year_ago_last_day);
			if ($year_work_days > 360) {
				$year_work_days = 360;
			}
			//نسبت کارکرد در سال قبل.
			$annual_inc_duration = ($year_work_days + $duplicate_duration) / 360;

			//فرمول محاسبه براي سال دوم به بعد.
			$value = (($base_salary + $this->param1) * $this->param2 * $annual_inc_duration) +
					($this->param3 * $base_salary ) + $this->param1;

			if (in_array($writ_rec['person_type'], array(1, 2, 3))) {
				$this->param4 = (($nh_base_salary + $this->param1) * $this->param2 * $annual_inc_duration) +
						$this->param1;
			}
		}

		return $value;
	}

	/** حق شغل */
	private function compute_salary_item2_34($writ_rec) {
		//param1 : امتیاز
		//param2 : 
		//param3 :
		//param4 :
		//param5 :
		//param6 :
		//param7 :


                if($writ_rec['execute_date'] >='2013-02-19' && $writ_rec['execute_date'] < '2013-03-20' ) 
		{

			return ;
		}

		$job_level = "";

		// در وضعیت ثبت حکم پارامتر 4 را از حکم قبلي می خواند
		//در صورتي که کاربر آن را به روز رساني کند مقدار ثبت شده توسط کاربر مد نظر قرار می گيرد
		if ($this->param4 === null) {
			$obj = new manage_writ();
			parent::FillObjectByArray($obj, $writ_rec);
			$prior_writ = $obj->get_prior_writ("","",$writ_rec['execute_date']);

			if (!empty($prior_writ->writ_id)) {
				$item34 = new manage_writ_item($prior_writ->writ_id, $prior_writ->writ_ver, $prior_writ->staff_id, 34);
				if ($item34 && $item34->param4)
					$this->param4 = $item34->param4;
			}
		}

		$jdate = '1387/12/30';
		$date = DateModules::Shamsi_to_Miladi($jdate);
		$last_writ = manage_writ::get_last_writ_by_date($writ_rec['staff_id'], $date);
		$max_education_level = $writ_rec['education_level'];

		$Jexecute_date = DateModules::Miladi_to_Shamsi($writ_rec["execute_date"]);
		if ($Jexecute_date >= '1388/01/01')
			$new_emmployee = true;
		else
			$new_emmployee = false;

		$salary_item_type_166 = null;
		$salary_item_type_12 = null;

		if (!empty($last_writ->writ_id)) {
                  
			$salary_item_type_166 = new manage_writ_item($last_writ->writ_id, $last_writ->writ_ver, $last_writ->staff_id, 166);
			if (empty($salary_item_type_166->salary_item_type_id)) {
				$salary_item_type_12 = new manage_writ_item($last_writ->writ_id, $last_writ->writ_ver, $last_writ->staff_id, 12);
			}
		}
                   
           
		// level , job_level
		$scores = array(
			1 => array(1 => 2400, 2 => 2650, 3 => 2650, 4 => 2650, 5 => 2650),
			2 => array(1 => 2600, 2 => 2850, 3 => 2850, 4 => 2850, 4 => 2850),
			3 => array(1 => 2800, 2 => 3050, 3 => 3050, 4 => 3050, 5 => 3050),
			4 => array(1 => 3000, 2 => 3250, 3 => 3600, 4 => 4050, 5 => 4600),
			5 => array(1 => 3200, 2 => 3450, 3 => 3800, 4 => 4250, 5 => 4800),
			6 => array(1 => 3400, 2 => 3650, 3 => 4000, 4 => 4450, 5 => 5000),
			7 => array(1 => 3600, 2 => 3850, 3 => 4200, 4 => 4650, 5 => 5200),
			8 => array(1 => 3800, 2 => 4050, 3 => 4400, 4 => 4850, 5 => 5400),
			9 => array(1 => 4000, 2 => 4250, 3 => 4600, 4 => 5050, 5 => 5600),
			10 => array(1 => 4200, 2 => 4450, 3 => 4800, 4 => 5250, 5 => 5800),
			11 => array(1 => 4400, 2 => 4650, 3 => 5000, 4 => 5450, 5 => 6000),
			12 => array(1 => 4600, 2 => 4850, 3 => 5200, 4 => 5650, 5 => 6200),
			13 => array(1 => 4800, 2 => 5050, 3 => 5400, 4 => 5850, 5 => 6400),
			14 => array(1 => 5000, 2 => 5250, 3 => 5600, 4 => 6050, 5 => 6600),
			15 => array(1 => 5200, 2 => 5450, 3 => 5800, 4 => 6250, 5 => 6800),
			16 => array(1 => 5400, 2 => 5650, 3 => 6000, 4 => 6450, 5 => 7000)
				);

		if (!empty($last_writ->writ_id)) {
			if ($writ_rec['cur_group'] < 6)
				$level = 1;
			else
				$level = $writ_rec['cur_group'] - 4;
		}
		else {
			if ($writ_rec['cur_group'] < 6)
				$level = 1;
			else
				$level = $writ_rec['cur_group'] - 4;
		}

		//در صورتی که به فرد نه ویژه و نه هعمترازی تعلق نگرفته باشد در سطح یک قرار می گیرد.

		if (!$salary_item_type_12 && !$salary_item_type_166) {

			$job_level = 1;
		}
 
		//به فرد در آخرین حکم سال 87 ویژه تعلق می گرفته است.
		$vije_include = false;
		if ($salary_item_type_166) {
			if ($salary_item_type_166->param1 >= 600 && $salary_item_type_166->param1 < 1100) {
				$job_level = 3;
				$vije_include = true;
			} else if ($salary_item_type_166->param1 >= 1100 && $salary_item_type_166->param1 < 1600) {
				$job_level = 4;
				$vije_include = true;
			} else if ($salary_item_type_166->param1 >= 1600) {
				$job_level = 5;
				$vije_include = true;
			}
		}
		//به فرد در آخرین حکم سال 87 تفاوت همترازی تعلق گرفته است.
		if ($salary_item_type_12 && !empty($salary_item_type_12->writ_id)) {
                    
			$person_devotions = manage_person_devotion::get_person_devotions($writ_rec["PersonID"], "(" . SACRIFICE_DEVOTION . ")");
			$person_family_shohada =  manage_person_devotion::get_person_devotions($writ_rec['PersonID'], '(' . BEHOLDER_FAMILY_DEVOTION . ')', BOY . ',' . DAUGHTER);
                       
			if (($max_education_level == 500 || $max_education_level == 501)) {
				$coef = 3;
				$vije_include = true;
			} else if ($max_education_level > 501) {
				$coef = 4;
				$vije_include = true;
			}
			if (count($person_devotions) != 0 || count($person_family_shohada) != 0) {
				$coef++;
			}
			if ($coef) {
				$job_level = $coef;
			}
		}

		if ($writ_rec['person_type'] != 1 && $job_level > 4) {
			$job_level = 4;
		}

		if ($writ_rec['onduty_year'] >= 30) {
			$writ_rec['onduty_year'] = 30;
			$writ_rec['onduty_month'] = 0;
			$writ_rec['onduty_day'] = 0;
		}

		if (!$vije_include) {                    
                     
			$related_onduty_year = ($writ_rec['related_onduty_year'] +
					($writ_rec['related_onduty_month'] / 12) +
					($writ_rec['related_onduty_day'] / 365.25)) / 6;

			if ($new_emmployee)
				$coef = 1 / 12;
			else
				$coef = 0.1;
			$onduty_year = (($writ_rec['onduty_year'] - $writ_rec['related_onduty_year']) +
					(($writ_rec['onduty_month'] - $writ_rec['related_onduty_month']) / 12) +
					(($writ_rec['onduty_day'] - $writ_rec['related_onduty_month']) / 365.25)) * $coef;

			// احتساب مضاعف سنوات آزادگان
			$dev_add_coef = 0;
			$person_devotions = manage_person_devotion::get_person_devotions($writ_rec["PersonID"], "(" . FREEDOM_DEVOTION . ")");
                        $person_family_shohada =  manage_person_devotion::get_person_devotions($writ_rec['PersonID'], '(' . BEHOLDER_FAMILY_DEVOTION . ')', BOY . ',' . DAUGHTER);
                        
			if ( ( count($person_devotions) > 0  && $person_devotions[0]['amount'] > 0 ) || 
                               count($person_family_shohada) > 0 )
				$dev_add_coef = 1;

			$job_level += floor(($related_onduty_year + $onduty_year) * (1 + $dev_add_coef));

			if ($writ_rec['person_type'] != 1 && $job_level > 4) {
				$job_level = 4;
			}

			$result = manage_posts::get_job_fields($writ_rec['post_id']);
			if ($result)
				$jl = $result["job_level"];
			else
				$jl = JOB_LEVEL_COMMON;

			if ($max_education_level < 400 && $jl < JOB_LEVEL_BS)
				$job_level = min(array($job_level, 2));
			else
				$job_level = min(array($job_level, 3));
		}


                   
		if (!empty($this->param4)) {

			$job_level = $this->param4;

		} else {
			//...... چنانچه دستی وارد نشده باشد از اولین حکم در سال 88 رتبه مربوطه برداشته می شود ........

			$MyQry = "select wsi.param3
                                         from writs w inner join writ_salary_items wsi
                                                                 on w.writ_id = wsi.writ_id and
                                                                    w.writ_ver = wsi.writ_ver

                                               where w.staff_id = " . $writ_rec['staff_id'] . " and w.execute_date >= '2009-03-21' and
                                                    ( w.history_only <> 1 or  w.history_only is null ) and  wsi.salary_item_type_id = 34
                                            order by w.execute_date ASC limit 1  ";
			$MyRes = parent::runquery($MyQry);


			if ($MyRes[0]['param3'] > 0)
				$job_level = round($MyRes[0]['param3']);
		}

		//..........................................

		$score = !empty($job_level) ? $scores[$level][$job_level] : 0;
 
		$rial_coef = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_RIAL_COEF, $writ_rec["execute_date"]);
		// echo $rial_coef ."idkjf"; die();

		$value = $score * $rial_coef;

		if (!($value > 0)) {
			return false;
		}
		$this->param1 = $score;
		$this->param2 = $level;
		$this->param3 = $job_level;

		return $value;
	}

	/** فوق العاده مدیریت */
	private function compute_salary_item2_35($writ_rec) {
		//param1 : امتیاز
		//param2 : ورودی : سحح مدیریت
		//param3 : 
		//param4 : حوزه جغرافیایی
		//param5 : ورودی : شماره عنوان مدیرتی
		//param6 :
		//param7 :
		//level , manage_level
		//level 1
		// در صورتي که فرد پست ندارد و يا پست وي مديريتي يا سرپریتي نمی باشد
		// فوق العاده مديريت به وی تعلق نمي گيرد


		if (empty($this->param4) || empty($this->param2) || empty($this->param7)) {
			unset($this->param1);
			return 0;
		}


		$post_rec = manage_posts::get_positions($writ_rec['post_id']);

		if (!$post_rec || ($post_rec['post_type'] != POST_EXE_MANAGER && $post_rec['post_type'] != POST_EXE_SUPERVICE)) {

			parent::PushException("این فرد دارای پست مدیریتی نمی باشد.");
			return false;
		}


		$geo_pos_level = $this->param4;

		// input , input , input
		//[$param4  geo_pos_level][$param2 management_level][$param7 manager_title_no]
		//geo_pos_level1  level1
		$arr = array();
		$arr[1][1][1] = 2300;
		$arr[1][1][2] = 1850;
		$arr[1][1][3] = 1400;
		$arr[1][1][4] = 950;
		$arr[1][1][5] = 510;

		//geo_pos_level1 level 2
		$arr[1][2][1] = 2350;
		$arr[1][2][2] = 1900;
		$arr[1][2][3] = 1450;
		$arr[1][2][4] = 1000;
		$arr[1][2][5] = 550;

		//geo_pos_level1 level 3
		$arr[1][3][1] = 2400;
		$arr[1][3][2] = 1950;
		$arr[1][3][3] = 1500;
		$arr[1][3][4] = 1050;
		$arr[1][3][5] = 600;

		//geo_pos_level1  level1
		$arr[2][1][1] = 2450;
		$arr[2][1][2] = 2000;
		$arr[2][1][3] = 1550;
		$arr[2][1][4] = 1100;
		$arr[2][1][5] = 650;

		//geo_pos_level2 level 2
		$arr[2][2][1] = 2500;
		$arr[2][2][2] = 2050;
		$arr[2][2][3] = 1600;
		$arr[2][2][4] = 1150;
		$arr[2][2][5] = 700;

		//geo_pos_level2 level 3
		$arr[2][3][1] = 2550;
		$arr[2][3][2] = 2100;
		$arr[2][3][3] = 1650;
		$arr[2][3][4] = 1200;
		$arr[2][3][5] = 750;

		//geo_pos_level3  level1
		$arr[3][1][1] = 2600;
		$arr[3][1][2] = 2150;
		$arr[3][1][3] = 1700;
		$arr[3][1][4] = 1250;
		$arr[3][1][5] = 800;

		//geo_pos_level3 level 2
		$arr[3][2][1] = 2650;
		$arr[3][2][2] = 2200;
		$arr[3][2][3] = 1750;
		$arr[3][2][4] = 1300;
		$arr[3][2][5] = 850;

		//geo_pos_level3 level 3
		$arr[3][3][1] = 2700;
		$arr[3][3][2] = 2250;
		$arr[3][3][3] = 1800;
		$arr[3][3][4] = 1350;
		$arr[3][3][5] = 900;

		$this->param1 = $arr[$geo_pos_level][$this->param2][$this->param7];

		$rial_coef = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_RIAL_COEF, $writ_rec["execute_date"]);

		$value = $this->param1 * $rial_coef;


		if (!($value > 0)) {
			return false;
		}
		return $value;
	}

	/** حق شاغل	 */
	private function compute_salary_item2_36($writ_rec) {
		//param1 : امتیاز
		//param2 : سنوات خدمت مربوط و مشابه
		//param3 : دوره های آموزشی
		//param4 : ضريب امتياز سرپرستي و مديريت
		//param5 : امتياز کامل
		//param6 :  سنوات خدمت
		//param7 : تفاوت مبلغ
 
		$educQry = " select education_level , doc_date
                                from  person_educations
                                             where personid =" . $writ_rec['PersonID'] . " and doc_date <= '" . $writ_rec['execute_date'] . "'  ";

		$resEduc = parent::runquery($educQry);

		$score = 0;

		$prev_onduty_year = 0;
		$prev_onduty_month = 0;
		$prev_onduty_day = 0;

		$prev_related_onduty_year = 0;
		$prev_related_onduty_month = 0;
		$prev_related_onduty_day = 0;

		$Sum_duty_year = 0;
		$Sum_related_duty_year = 0;

		$qry = " SELECT param1 FROM Basic_Info
 					   where typeid = 6	and InfoID = " . $writ_rec['education_level'];

		$res = parent::runquery($qry);

		$education_level['max_education_level'] = $res[0]['param1'];
                
                
		$person_devotions = manage_person_devotion::get_person_devotions($writ_rec['PersonID'], '(' . DEVOTION_TYPE_WOUNDED . ')');
		$devFlag = 0;
		if ($writ_rec['execute_date'] >= '2010-03-21') {
			$person_family_shohada = manage_person_devotion::get_person_devotions($writ_rec['PersonID'], '(' . BEHOLDER_FAMILY_DEVOTION . ')', BOY . ',' . DAUGHTER);

			if (count($person_family_shohada) > 0) {
				if ($person_family_shohada[0]['devotion_type'] > 0)
					$devFlag = 1;
			}
		}

		if (count($person_devotions) > 0) {
			if ($person_devotions[0]['amount'] > 0)
				$devFlag = 1;
		}

		if ($devFlag == 1) {

			if ($education_level['max_education_level'] < 122)
				$education_level['max_education_level'] = 122;

			else if ($education_level['max_education_level'] < 200)
				$education_level['max_education_level'] = 200;

			else if ($education_level['max_education_level'] < 300)
				$education_level['max_education_level'] = 300;
			else
				$education_level['max_education_level'] += 100;
		}

		//کسانی که دارای مدرک تحصیلی زیر دیپلم هستند.
		if ($education_level['max_education_level'] < 200) {
			$score = 1100;
			//امتیاز مهارت وتوانایی
			$score += 200;
		}

		//کسانی که دارای مدرک تحصیلی دیپلم هستند
		else if ($education_level['max_education_level'] < 300) {
			$score = 1200;
			//امتیاز مهارت وتوانایی
			$score += 250;
		} //کسانی که دارای مدرک تحصیلی کاردانی هستند.
		else if ($education_level['max_education_level'] == 300 || $education_level['max_education_level'] == 301) {
			$score = 1400;
			//امتیاز مهارت وتوانایی
			$score += 300;
		} //کسانی که دارای مدرک تحصیلی کارشناسی هستند.
		else if ($education_level['max_education_level'] == 400 || $education_level['max_education_level'] == 401) {
			$score = 1700;
			//امتیاز مهارت وتوانایی
			$score += 400;
		} //کسانی که دارای مدرک تحصیلی کارشناسی ارشد می باشند.
		else if ($education_level['max_education_level'] == 500 || $education_level['max_education_level'] == 501) {
			 $score = 2000;
			//امتیاز مهارت وتوانایی
			$score += 600;
		} //کسانی که دارای مدرک تحصیلی دکتری می باشند.
		else if ($education_level['max_education_level'] > 501) {
			$score = 2300;
			//امتیاز مهارت وتوانایی
			$score += 800;
		}
                
            

		for ($i = 0; $i < count($resEduc); $i++) {

			if (isset($resEduc[$i + 1])) {
				$date = $resEduc[$i + 1]['doc_date'];
			}
			else
				$date = $writ_rec['execute_date'];

			$last_writ_rec = manage_writ::get_last_writ_by_date($writ_rec['staff_id'], $date);
                       
			$education_level['max_education_level'] = $resEduc[$i]['education_level'];

			$myqry = " SELECT param1 FROM Basic_Info
 					  where typeid = 6	and InfoID = " . $resEduc[$i]['education_level'];
			$myres = parent::runquery($myqry);

			$education_level['max_education_level'] = $myres[0]['param1'];
			$person_devotions = manage_person_devotion::get_person_devotions($writ_rec['PersonID'], '(' . DEVOTION_TYPE_WOUNDED . ')');

			$devFlag = 0;

			if ($writ_rec['execute_date'] >= '2010-03-21') {
				$person_family_shohada = manage_person_devotion::get_person_devotions($writ_rec['PersonID'], '(' . BEHOLDER_FAMILY_DEVOTION . ')', BOY . ',' . DAUGHTER);
				if (count($person_family_shohada) > 0) {
					if ($person_family_shohada[0]['devotion_type'] > 0)
						$devFlag = 1;
				}
			}
			/* if($person_devotions[0]['amount']>0 || $person_family_shohada ){

			  if($education_level['max_education_level']< 122)
			  $education_level['max_education_level'] = 122 ;

			  else if($education_level['max_education_level']< 200)
			  $education_level['max_education_level'] = 200 ;

			  else if($education_level['max_education_level']< 300)
			  $education_level['max_education_level'] = 300 ;
			  else
			  $education_level['max_education_level'] += 100 ;
			  } */


			if (count($person_devotions) > 0) {
				if ($person_devotions[0]['amount'] > 0)
					$devFlag = 1;
			}

 
			if ($devFlag == 1) {

				if ($education_level['max_education_level'] < 122)
					$education_level['max_education_level'] = 122;

				else if ($education_level['max_education_level'] < 200)
					$education_level['max_education_level'] = 200;

				else if ($education_level['max_education_level'] < 300)
					$education_level['max_education_level'] = 300;
				else
					$education_level['max_education_level'] += 100;
			}

  
                    
			$first_duty_day = $prev_onduty_year * 365.25 + $prev_onduty_month * 30.4375 + $prev_onduty_day;
			$last_duty_day = $last_writ_rec->onduty_year * 365.25 + $last_writ_rec->onduty_month * 30.4375 + $last_writ_rec->onduty_day;
			$onduty_year = ($last_duty_day - $first_duty_day ) / 365.25;

			if ($onduty_year >= 30) {
				$onduty_year = 30;
			}
			if (($Sum_duty_year + $onduty_year ) >= 30) {
				$onduty_year = 30 - $Sum_duty_year;
			}


			$Sum_duty_year += $onduty_year;
                     
                    
			$first_related_duty_day = $prev_related_onduty_year * 365.25 + $prev_related_onduty_month * 30.4375 + $prev_related_onduty_day;
			$last_related_duty_day = $last_writ_rec->related_onduty_year * 365.25 + $last_writ_rec->related_onduty_month * 30.4375 + $last_writ_rec->related_onduty_day;
			$related_onduty_year = ($last_related_duty_day - $first_related_duty_day ) / 365.25;

                                           
			if ($related_onduty_year >= 30) {
				$related_onduty_year = 30;
			}

			if (($Sum_related_duty_year + $related_onduty_year ) >= 30) {
				$related_onduty_year = 30 - $Sum_related_duty_year;
			}
                        
        
			$Sum_related_duty_year += $related_onduty_year;

			$dev_coef = 1;
			$related_onduty_year *= $dev_coef;
			$onduty_year *= $dev_coef;
                        
              
			$prev_onduty_year = $last_writ_rec->onduty_year;
			$prev_onduty_month = $last_writ_rec->onduty_month;
			$prev_onduty_day = $last_writ_rec->onduty_day;

			$prev_related_onduty_year = $last_writ_rec->related_onduty_year;
			$prev_related_onduty_month = $last_writ_rec->related_onduty_month;
			$prev_related_onduty_day = $last_writ_rec->related_onduty_day;

                      
			//کسانی که دارای مدرک تحصیلی زیر دیپلم هستند.
			if ($education_level['max_education_level'] < 200) {

				//امتیاز سنوات خدمت در هر سال
				$score += $onduty_year * 10;
				//امتیاز تجربه مربوط و مشابه در هر سال
				$score += $related_onduty_year * 8;
			}

			//کسانی که دارای مدرک تحصیلی دیپلم هستند
			else if ($education_level['max_education_level'] < 300) {

				//امتیاز سنوات خدمت در هر سال
				$score += $onduty_year * 15;
				//امتیاز تجربه مربوط و مشابه در هر سال
				$score += $related_onduty_year * 10;
			} //کسانی که دارای مدرک تحصیلی کاردانی هستند.
			else if ($education_level['max_education_level'] == 300 || $education_level['max_education_level'] == 301) {

				//امتیاز سنوات خدمت در هر سال
				$score += $onduty_year * 20;
				//امتیاز تجربه مربوط و مشابه در هر سال
				$score += $related_onduty_year * 12;
			} //کسانی که دارای مدرک تحصیلی کارشناسی هستند.
			else if ($education_level['max_education_level'] == 400 || $education_level['max_education_level'] == 401) {

				//امتیاز سنوات خدمت در هر سال
				$score += $onduty_year * 25;
				//امتیاز تجربه مربوط و مشابه در هر سال
				$score += $related_onduty_year * 14;
			} //کسانی که دارای مدرک تحصیلی کارشناسی ارشد می باشند.
			else if ($education_level['max_education_level'] == 500 || $education_level['max_education_level'] == 501) {
                          
				//امتیاز سنوات خدمت در هر سال
				$score += $onduty_year * 30;
				//امتیاز تجربه مربوط و مشابه در هر سال
				$score += $related_onduty_year * 16;
                            
			} //کسانی که دارای مدرک تحصیلی دکتری می باشند.
			else if ($education_level['max_education_level'] > 501) {

				//امتیاز سنوات خدمت در هر سال
				$score += $onduty_year * 35;
				//امتیاز تجربه مربوط و مشابه در هر سال
				$score += $related_onduty_year * 18;
			}
                        
                          
		}



		if ($writ_rec['onduty_year'] >= 30) {
			$writ_rec['onduty_year'] = 30;
			$writ_rec['onduty_month'] = 0;
			$writ_rec['onduty_day'] = 0;
		}

		$onduty_year = ($writ_rec['onduty_year'] +
				($writ_rec['onduty_month'] / 12) +
				($writ_rec['onduty_day'] / 365.25));

		if ($writ_rec['related_onduty_year'] >= 30) {
			$writ_rec['related_onduty_year'] = 30;
			$writ_rec['related_onduty_month'] = 0;
			$writ_rec['related_onduty_day'] = 0;
		}

		$related_onduty_year = ($writ_rec['related_onduty_year'] +
				($writ_rec['related_onduty_month'] / 12) +
				($writ_rec['related_onduty_day'] / 365.25));


		$this->param2 = round($related_onduty_year, 2);
		$this->param6 = round($onduty_year, 2);

		$cqry = " SELECT SUM(total_hours) c_hours
			    	 FROM staff s
			    		  INNER JOIN person_courses pc
			    			    ON (s.PersonID = pc.PersonID)
			    	 WHERE s.staff_id = " . $writ_rec['staff_id'] . " AND pc.to_date<= '" . $writ_rec['execute_date'] . "'";
		$cres = parent::runquery($cqry);
		$hours = $cres[0]['c_hours'];
		if ($hours > 1000) {
			$hours = 1000;
		}
		$this->param3 = $hours;

		$score += ($this->param3 * 0.5);
                                    
		$mng_coef = manage_posts::compute_manager_score_percent($writ_rec["staff_id"],$writ_rec["execute_date"]) / 100;

               
                    
		$score = $score + $score * $mng_coef;
		$this->param4 = $mng_coef;
		$this->param5 = $score;

		$rial_coef = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_RIAL_COEF, $writ_rec['execute_date']);

		if (!$rial_coef) {
			parent::PushException(RIAL_COEF_NOT_FOUND);
			return false;
		}             
               

		$value = $score * $rial_coef;

		if (!($value > 0)) {
			return false;
		}
		$this->param1 = round($score, 2);
		//حق شاغل باید حداکثر 75 درصد حق شغل باشد.
		$hagh_shoghl_value = manage_writ_item::get_writSalaryItem_value($writ_rec['writ_id'], $writ_rec['writ_ver'], $writ_rec["staff_id"], 34);

		if (($hagh_shoghl_value * 0.75) < $value) {
			$this->param7 = $value - $hagh_shoghl_value;
			$value = $hagh_shoghl_value * 0.75;
		}

		return $value;
	}

	/** تفاوت تطبیق	 */
	private function compute_salary_item2_56($writ_rec) {
		//param1 : 
		//param2 : 
		//param3 :
		//param4 :
		//param5 :
		//param6 :
		//param7 :
		
		$writDt = parent::runquery("
				Select execute_date
				from writs
				where staff_id = ? and history_only != 1
				order by execute_date ASC limit 1 ", array($writ_rec['staff_id']));
		
		$writDt2 = parent::runquery("
				select writ_id , writ_ver , execute_date , education_level , post_id
				from writs
				where staff_id = ? and execute_date = ? and history_only <> 1
				order by writ_id DESC , writ_ver DESC limit 1 ", array($writ_rec['staff_id'], $writDt[0]["execute_date"]));
		

		$value = 0 ; 
		if ($writDt2[0]['execute_date'] >= '2009-03-21') {
								
				
					
			$job_field_Dt = parent::runquery("
				select jf.job_level
				from position p inner join job_fields jf on(p.jfid = jf.jfid)
				where p.post_id = ?", array($writDt2[0]['post_id']));
			// سطح شغل
			if ($job_field_Dt[0]['job_level'] > 2)
				$coef = 65;
			else
				$coef = 43;

			// مدرک تحصیلی
			$educLevel_Dt = parent::runquery("
				SELECT param1 FROM Basic_Info
				where typeid = 6 and InfoID = ?", array($writDt2[0]['education_level']));

			$education_level = $educLevel_Dt[0]["param1"];

			//--------------------کسانی که دارای مدرک تحصیلی زیر سیکل می باشند .

			if ($education_level < 122) {
				$educcoef = 35;
			}
			//-------------------
			//کسانی که داراي مدرک تحصیلی پايين تر از ديپلم دارند
			elseif ($education_level < 200) {
				$educcoef = 45;
			}
			//
			else if ($education_level < 300) {
				$educcoef = 55;
			}
			//کسانی که دارای مدرک تحصیلی کاردانی هستند.
			else if ($education_level == 300 || $education_level == 301) {
				$educcoef = 65;
			} //کسانی که دارای مدرک تحصیلی کارشناسی هستند.
			else if ($education_level == 400 || $education_level == 401) {
				$educcoef = 85;
			} //کسانی که دارای مدرک تحصیلی کارشناسی ارشد می باشند.
			else if ($education_level == 500 || $education_level == 501) {
				$educcoef = 100;
			} //کسانی که دارای مدرک تحصیلی دکتری می باشند.
			else if ($education_level > 501) {
				$educcoef = 100;
			}

			$min_sal_param = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_MIN_SALARY, '2009-03-21');

			$prior_year_val = $min_sal_param + ( $coef * $min_sal_param ) / 100 + ( 85 * $educcoef * 1170000 ) / 10000 + 408600;

			$shoghl_score = self::compute_writ_items_sum($writDt2[0]["writ_id"], $writDt2[0]["writ_ver"], $writ_rec["staff_id"], "(34)", "param1");

			$shaghel_score = self::compute_writ_items_sum($writDt2[0]["writ_id"], $writDt2[0]["writ_ver"], $writ_rec["staff_id"], "(36)", "param1");

			if (substr(DateModules::miladi_to_shamsi($writ_rec['execute_date']), 0, 4) >= '1389')
				$other_score = self::compute_writ_items_sum($writDt2[0]["writ_id"], $writDt2[0]["writ_ver"], $writ_rec["staff_id"], "(47,48,55)", "param1");
			else
				$other_score = self::compute_writ_items_sum($writDt2[0]["writ_id"], $writDt2[0]["writ_ver"], $writ_rec["staff_id"], "(35,47,48,55)", "param1");

			if ($shaghel_score > $shoghl_score * 0.75)
				$shaghel_score = $shoghl_score * 0.75;
			$diff = $prior_year_val - ($shaghel_score + $shoghl_score + $other_score) * 500;
			if ($diff > 0)
				$value = $diff;
			if (!($value > 0)){
				
				return 0; 
				
				}
		}
		else if ($writDt2[0]['execute_date'] < '2009-03-21') {
			
			
			$this_writ_year = substr(DateModules::Miladi_to_Shamsi('2009-03-21'), 0, 4);
			$one_year_ago = $this_writ_year - 1;
			$one_year_ago_last_day = $one_year_ago . "/12/29";
			$Gone_year_ago_last_day = DateModules::Shamsi_to_Miladi($one_year_ago_last_day);

			$query = "SELECT * FROM writs w
						inner join writ_salary_items wsi on(w.writ_id = wsi.writ_id and w.writ_ver = wsi.writ_ver and w.staff_id=wsi.staff_id)
					WHERE w.staff_id = " . $writ_rec['staff_id'] . " AND w.execute_date <= '" . $Gone_year_ago_last_day . "'
						AND (w.history_only != 1 OR w.history_only is null )
					ORDER BY w.execute_date DESC,w.writ_id DESC,w.writ_ver DESC";

			$prior_writ = PdoDataAccess::runquery($query);
						
			if (count($prior_writ) == 0)
				return null;
											
			//___________________________________________
			$query = " SELECT SUM(wsi.value) value_sum
				FROM   writ_salary_items wsi
					   LEFT OUTER JOIN salary_item_types sit ON (wsi.salary_item_type_id = sit.salary_item_type_id)
				WHERE  wsi.writ_id = " .  $prior_writ[0]["writ_id"] . " AND
					   wsi.writ_ver = " . $prior_writ[0]["writ_ver"] . " AND
					   wsi.staff_id = " . $prior_writ[0]["staff_id"] . " AND
					   sit.retired_include = 1 AND sit.salary_item_type_id!=" . SIT_STAFF_HARD_WORK_EXTRA .
					" AND sit.salary_item_type_id!=" . SIT_STAFF_SHIFT_EXTRA;

			$temp = PdoDataAccess::runquery($query);
			
			$prior_year_val = $temp[0]["value_sum"];
			//___________________________________________

			$query = "select * from writs w
						inner join writ_salary_items wsi on(w.writ_id =  wsi.writ_id and w.writ_ver = wsi.writ_ver and w.staff_id=wsi.staff_id)
					where
						w.staff_id = " . $writ_rec['staff_id'] . "
						AND w.execute_date = '2009-03-21'
						AND (w.history_only != " . HISTORY_ONLY . " OR w.history_only is null )
						order by w.execute_date ASC ,w.writ_id DESC,w.writ_ver DESC";
			$temp = PdoDataAccess::runquery($query);
			
			if (count($temp) == 0)
				return null;

			$shoghl_score = manage_writ_item::compute_writ_items_sum($temp[0]["writ_id"], $temp[0]["writ_ver"], $temp[0]["staff_id"], "(34)", "param1");
			$shaghel_score = manage_writ_item::compute_writ_items_sum($temp[0]["writ_id"], $temp[0]["writ_ver"], $temp[0]["staff_id"], "(36)", "param1");

			$this_writ_year = substr(DateModules::Miladi_to_Shamsi($writ_rec['execute_date']), 0, 4);
                        
                        /***بنابه قانون دانشگاه برای احکام بیشتر از سال 89 امتیاز فوقالعاده مدیریت در سایر امتیاز ها لحاظ نمی گردد.***/
		
			if ($this_writ_year >= 1389) {				
				
				$other_score = manage_writ_item::compute_writ_items_sum($temp[0]["writ_id"], $temp[0]["writ_ver"], $temp[0]["staff_id"], "(47,48,55)", "param1"); 
								
				}
			else {
				
				$other_score = manage_writ_item::compute_writ_items_sum($temp[0]["writ_id"], $temp[0]["writ_ver"], $temp[0]["staff_id"], "(35,47,48,55)", "param1");
				
			}
			if ($shaghel_score > $shoghl_score * 0.75)
				$shaghel_score = $shoghl_score * 0.75;
			
			
					
			$diff = $prior_year_val - ($shaghel_score + $shoghl_score + $other_score) * 500;
			
		
			if ($diff > 0)
				$value = $diff;

			if (!($value > 0)){
				
				return 0 ; 
				
				}

			return $value;
		}
		
		
		return $value;
		
	}

	/** مبالغ بند 6 دستورالعمل - سایر */
	private function compute_salary_item2_45($writ_rec) {
		//param1 : امتیاز
		//param2 :
		//param3 :
		//param4 :
		//param5 :
		//param6 :
		//param7 :

		$prior_writ_object = manage_writ::get_last_writ_With_salry_before_date($writ_rec['staff_id'], '2009-03-21');

		if (empty($prior_writ_object->writ_id)) {
			//$writ_rec['execute_date']
			$prior_writ_object = manage_writ::get_last_writ_With_salry_before_date($writ_rec['staff_id'],'2013-02-19');			
			$value = $this->get_writSalaryItem_value($prior_writ_object->writ_id, $prior_writ_object->writ_ver, $writ_rec['staff_id'], SIT_STAFF_BAND6);
		} else {
			$value = $this->compute_writ_items_sum($prior_writ_object->writ_id, $prior_writ_object->writ_ver, $prior_writ_object->staff_id, "(" . SIT_STAFF_SHIFT_EXTRA . "," . SIT_STAFF_DUTY_LOCATION_EXTRA . ")");
		}

		if (!($value > 0))
			return 0;

		return $value;
	}

	/** فوق العاده بدی آب و هوا	 */
	private function compute_salary_item2_46($writ_rec) {
		//param1 : امتیاز
		//param2 :
		//param3 :
		//param4 :
		//param5 :
		//param6 :
		//param7 :

		if ($writ_rec['execute_date'] >= '2013-02-19' && $writ_rec['execute_date'] < '2013-03-21')
		{
			return 0 ; 
		}
		
		if ($writ_rec['execute_date'] >= '2010-12-22') {
			//بنا به قانون جدید
			$this->param3 = manage_person_education::GetEducationalGroupLevel($writ_rec['education_level'], 'MasterID');
			$this->param1 = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_WHEATHER_COEF, $writ_rec['execute_date'], $writ_rec['work_city_id'], $writ_rec['work_state_id'], $this->param3);

			if (!$this->param1) {
				parent::PushException(WHEATHER_COEF_NOT_FOUND);
				return false;
			}

			$this->param2 = self::compute_writ_items_sum($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], '(56,35,36,34)');

			if (!($this->param2 > 0))
				return false;
			$value = $this->param1 * $this->param2;
		}
		else if ($writ_rec['execute_date'] < '2010-12-22') {
		    
		    

			if ($writ_rec['execute_date'] >= '2009-03-21') { 
				
			       
				
				$writObj = manage_writ::get_last_writ_With_salry_before_date($writ_rec['staff_id'], '2009-03-21');
				
			} else {
				$writObj = manage_writ::get_last_writ_With_salry_before_date($writ_rec['staff_id'], $writ_rec['execute_date']);
			}

			if ($writObj->writ_id == "") {
				parent::PushException(WHEATHER_ITEM_CALC_ERR);
				return 0 ;
			}

			$value = self::compute_writ_items_sum($writObj->writ_id, $writObj->writ_ver, $writObj->staff_id, "(" . SIT_STAFF_BAD_WEATHER_EXTRA . ")");
		}
		

		if (!($value > 0)) {
			parent::PushException(WHEATHER_ITEM_CALC_ERR);
			return false;
		}
		return $value;
	}

	/** فوق العاده ایثارگری	 */
	private function compute_salary_item2_47($writ_rec) {
		//param1 : امتیاز
		//param2 :
		//param3 :
		//param4 :
		//param5 :
		//param6 :
		//param7 :
	   
		$score = manage_person_devotion::get_devotion_score($writ_rec["staff_id"], $writ_rec["execute_date"]);
		
		if (!($score > 0))
			return false;

		$this->param1 = $score;
		$rial_coef = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_RIAL_COEF, $writ_rec["execute_date"]);

		return $score * $rial_coef;
	}

	/** فوق العاده نشانهای دولتی	 */
	private function compute_salary_item2_48($writ_rec) {
		//param1 : امتیاز
		//param2 :
		//param3 :
		//param4 :
		//param5 :
		//param6 :
		//param7 :

		return;
	}

	/** فوق العاده سختی شرایط کا ر و کار در محیطهای غیر متعارف */
	private function compute_salary_item2_49($writ_rec) {
		//param1 : امتیاز
		//param2 :
		//param3 :
		//param4 :
		//param5 :
		//param6 :
		//param7 :

		if ($writ_rec['execute_date'] >= '2010-12-26') {
			$writObj = manage_writ::get_last_writ_With_salry_before_date($writ_rec['staff_id'], '2009-03-21');
			if ($writObj->writ_id) {
				$DT = parent::runquery("
                                    SELECT (param1*100) darsad , value
                                        FROM writ_salary_items
                                        WHERE
                                            writ_id = " . $writObj->writ_id . "
                                            AND writ_ver = " . $writObj->writ_ver . "
                                            AND staff_id = " . $writObj->staff_id . "
                                            AND salary_item_type_id in (" . SIT_STAFF_HARD_WORK_EXTRA . ")");

				if (!count($DT) > 0)
					return 0;
			}
			else
				return 0;

			$darsad = $DT[0]["darsad"];
			$param2 = ($darsad) / 100;
			$score = $darsad * 12;
			$param1 = $score;
			$score = ($score > 1000) ? 1000 : $score;

			$base_salary = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_RIAL_COEF, $writ_rec['execute_date']);

			$value = $base_salary * $score;

			if ($value < $DT[0]["value"])
				$value = $DT[0]["value"];

			if (!($value > 0))
				return false;
		}
		else if ($writ_rec['execute_date'] < '2010-12-26') {
			if ($writ_rec['execute_date'] >= '2009-03-21')
				$writ_obj = manage_writ::get_last_writ_With_salry_before_date($writ_rec['staff_id'], '2009-03-21');
			else
				$writ_obj = manage_writ::get_last_writ_With_salry_before_date($writ_rec['staff_id'], $writ_rec['execute_date']);

			$value = self::compute_writ_items_sum($writ_obj->writ_id, $writ_obj->writ_ver, $writ_obj->staff_id, "(" . SIT_STAFF_HARD_WORK_EXTRA . ")");

			if (!($value > 0))
				return false;
		}
		return $value;
	}

	/*	 * کمک هزینه عاپله مندی */

	private function compute_salary_item2_50($writ_rec) {
		//param1 : امتیاز
		//param2 :
		//param3 :
		//param4 :
		//param5 :
		//param6 :
		//param7 :
		$score = 810;
		$rial_coef = manage_salary_params::get_salaryParam_value("", $writ_rec["sp_person_type"], SPT_RIAL_COEF, $writ_rec["execute_date"]);

		$value = 0;
		if ($writ_rec['family_responsible'] == 1)
			$value = $score * $rial_coef;
		else {
			parent::PushException(NOT_FAMILY_RESPONSIBLE);
			return 0;
		}

		if (!($value > 0))
			return false;

		$this->param1 = $score;

		return $value;
	}

	/*	 * کمک هزینه اولاد */

	private function compute_salary_item2_51($writ_rec) {
		//param1 : امتیاز
		//param2 :
		//param3 :
		//param4 :
		//param5 :
		//param6 :
		//param7 :

		$this->param2 = $writ_rec['included_children_count'];

		//به دليل اينکه ممکن است کسي حق اولاد بگيرد ولي مشمول عائله مندي نشود
		//کد زير به اينصورت تغيير کرد.
		//مثال آن مانند خانمي است که طلاق گرفته است ولي حضانت فرزندان بر عهده اوست.
		//مجددا بنا به درخواست دانشگاه به حالت اول برگشت .
		if ($writ_rec['family_responsible'] != 1) {
			parent::PushException(NOT_FAMILY_RESPONSIBLE);
			return false;
		}

		if ($writ_rec['included_children_count'] == 0) {
			parent::PushException(ZERO_INCLUDED_CHILDREN);
			return false;
		}

		$rial_coef = manage_salary_params::get_salaryParam_value("", $writ_rec["sp_person_type"], SPT_RIAL_COEF, $writ_rec["execute_date"]);
		$score = $this->param2 * 210;
		$value = $score * $rial_coef;

		if (!($value > 0))
			return false;

		$this->param1 = $score;

		return $value;
	}

	/*	 * فوق العاده شغل مشاغل تخصصی */

	private function compute_salary_item2_52($writ_rec) {
		//param1 : امتیاز
		//param2 :
		//param3 :
		//param4 :
		//param5 :
		//param6 :
		//param7 :
		return;
	}

	/*	 * افزایش جز ب بند 11 قانون بودجه سال 1388 کشور */

	private function compute_salary_item2_57($writ_rec) {
		//param1 : 
		//param2 :
		//param3 :
		//param4 :
		//param5 :
		//param6 :
		//param7 :

		$writDT = parent::runquery("
			Select writ_id , writ_ver , execute_date , education_level , post_id ,
				family_responsible , staff_id , included_children_count , marital_status
			from writs
			where staff_id = " . $writ_rec['staff_id'] . " and history_only != 1
			order by execute_date ASC limit 1 ");

		$DT = parent::runquery("
			select writ_id , writ_ver , execute_date , education_level , post_id ,
				family_responsible , staff_id , included_children_count , marital_status
			from writs
			where staff_id = " . $writ_rec['staff_id'] . " and execute_date ='" . $writDT[0]['execute_date'] . "' and history_only <> 1
			order by writ_id DESC , writ_ver DESC limit 1 ");

		if ($DT[0]['execute_date'] >= '2009-03-21') {
			$diff1 = 0;
			$jobFieldDT = parent::runquery("
				select jf.job_level
                from position p inner join job_fields jf on p.jfid = jf.jfid
				where p.post_id = " . $DT[0]['post_id']);

			// سطح شغل
			if ($jobFieldDT[0]['job_level'] > 2)
				$coef = 65;
			else
				$coef = 43;

			// مدرک تحصیلی
			$education_level = manage_person_education::GetEducationalGroupLevel($DT[0]['education_level'], 'param1');

			//--------------------کسانی که دارای مدرک تحصیلی زیر سیکل می باشند .

			if ($education_level < 122) {
				$educcoef = 35;
			}
			//-------------------
			//کسانی که داراي مدرک تحصیلی پايين تر از ديپلم دارند
			elseif ($education_level < 200) {
				$educcoef = 45;
			}
			//
			else if ($education_level < 300) {
				$educcoef = 55;
			}
			//کسانی که دارای مدرک تحصیلی کاردانی هستند.
			else if ($education_level == 300 || $education_level == 301) {
				$educcoef = 65;
			} //کسانی که دارای مدرک تحصیلی کارشناسی هستند.
			else if ($education_level == 400 || $education_level == 401) {
				$educcoef = 85;
			}  //کسانی که دارای مدرک تحصیلی کارشناسی ارشد می باشند.
			else if ($education_level == 500 || $education_level == 501) {
				$educcoef = 100;
			}  //کسانی که دارای مدرک تحصیلی دکتری می باشند.
			else if ($education_level > 501) {
				$educcoef = 100;
			}



			$min_sal_param = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_MIN_SALARY, '2009-03-21');




			$prior_year_val = $min_sal_param + ( $coef * $min_sal_param ) / 100 + ( 85 * $educcoef * 1170000 ) / 10000 + 408600;



			$inc_15_percent = $prior_year_val * 0.15;


			$s = $prior_year_val + $inc_15_percent;

			$this_year_val = self::compute_writ_items_sum($DT[0]["writ_id"], $DT[0]["writ_ver"], $DT[0]["staff_id"], '(34,35,36,47,48,55,56)');



			if ($this_year_val < $s) {
				$diff1 = $s - $this_year_val;
				$this->param2 = $diff1;
			}

			//حق عائله مندی
			if ($DT[0]['family_responsible'] == 1) {
				$p1 = self::get_min_base_salary($writ_rec["person_type"], '2008-03-20');
				$p2 = 0.70;
				$haghe_Aelehmandi = $p1 * $p2;
			}
			else
				$haghe_Aelehmandi = 0;

			// حق اولاد

			$p3 = $DT[0]['included_children_count'];

			if ($DT[0]['included_children_count'] > 0) {
				$p4 = self::get_min_base_salary($writ_rec["person_type"], '2008-03-20');
				$p5 = 0.14;
				$haghe_olad = $p3 * $p4 * $p5;
			}
			else
				$haghe_olad = 0;



			if ($DT[0]['marital_status'] == 1)
				$marita_status = 600000;
			else if ($DT[0]['marital_status'] != 1 && $DT[0]['family_responsible'] == 1)
				$marita_status = 1000000;
			else if ($DT[0]['marital_status'] != 1 && $DT[0]['family_responsible'] != 1)
				$marita_status = 600000;


			$diff2 = ( $prior_year_val + $haghe_Aelehmandi + $haghe_olad + $marita_status ) - $this_year_val;
			$this->param3 = $diff2;

			$value = $diff1 + $diff2;
		}
		//-----------------------------------------
		else if ($DT[0]['execute_date'] < '2009-03-21') {
			if ($writ_rec['execute_date'] < '2010-03-20')
				$exedate = $writ_rec['execute_date'];
			else
				$exedate = '2010-03-20';

			$query = "select *
						from writs w inner join writ_salary_items wsi on(w.writ_id=wsi.writ_id and w.writ_ver=wsi.writ_ver and w.staff_id=wsi.staff_id)
						where w.staff_id = " . $writ_rec['staff_id'] . " AND
							w.execute_date = '2009-03-21' and
							(w.history_only != " . HISTORY_ONLY . " OR w.history_only is null )
						order by w.execute_date DESC,w.writ_id DESC,w.writ_ver DESC
						limit 1";


			$result = parent::runquery($query);
			$result = $result[0];

			$this_year_val = manage_writ_item::compute_writ_items_sum($result["writ_id"], $result["writ_ver"], $result["staff_id"], '(34,35,36,47,48,55,56)');



			$this_year_other_val = manage_writ_item::compute_writ_items_sum($result["writ_id"], $result["writ_ver"], $result["staff_id"], '(50,51,45,49,52,54)');

			$this_writ_year = substr(DateModules::Miladi_to_Shamsi('2009-03-21'), 0, 4);
			$one_year_ago = $this_writ_year - 1;
			$one_year_ago_last_day = $one_year_ago . "/12/29";
			$Gone_year_ago_last_day = DateModules::Shamsi_to_Miladi($one_year_ago_last_day);

			$query = "SELECT * FROM writs w
						inner join writ_salary_items wsi on(w.writ_id = wsi.writ_id and w.writ_ver = wsi.writ_ver and w.staff_id=wsi.staff_id)
					WHERE w.staff_id = " . $writ_rec['staff_id'] . " AND w.execute_date <= '" . $Gone_year_ago_last_day . "'
						AND (w.history_only != 1 OR w.history_only is null )
					ORDER BY w.execute_date DESC,w.writ_id DESC,w.writ_ver DESC";

			$prior_writ = parent::runquery($query);
			if (count($prior_writ) == 0)
				return null;
			//_______________________________________
			// compute_retired_include_salary_exp

			$sql = "SELECT SUM(wsi.value) value_sum
				FROM   writ_salary_items wsi
					   LEFT OUTER JOIN salary_item_types sit
							ON (wsi.salary_item_type_id = sit.salary_item_type_id)
				WHERE  wsi.writ_id = " . $prior_writ[0]["writ_id"] . " AND
					   wsi.writ_ver = " . $prior_writ[0]["writ_ver"] . " AND
					   wsi.staff_id = " . $prior_writ[0]["staff_id"] . " AND
					   sit.retired_include = 1 AND
					   sit.salary_item_type_id != " . SIT_STAFF_HARD_WORK_EXTRA . " AND
					   sit.salary_item_type_id != " . SIT_STAFF_SHIFT_EXTRA;


			$dt = parent::runquery($sql);
			$prior_year_val = $dt[0]["value_sum"];
			//__________________________________________

			$inc = $this_year_val - $prior_year_val;
			$inc_15_percent = round($prior_year_val * 0.15);
			$max_inc = max(array($inc, $inc_15_percent));
			$rel_inc = $max_inc - $inc;
			$this->param2 = $rel_inc;

			//محاسبه حق اولاد و عایله مندی آخرین حکم سال 1387
			$prior_year_family_val = self::compute_writ_items_sum($prior_writ[0]["writ_id"], $prior_writ[0]["writ_ver"], $prior_writ[0]["staff_id"], "(" . SIT_STAFF_CHILDREN_RIGHT . "," . SIT_STAFF_CHILD_RIGHT . ")");

			//باید برای مشمول بازنشستگی و ... بیشتر بررسی گردد.
			$prior_year_hardwork_shiftwork = manage_writ_item::compute_writ_items_sum($prior_writ[0]["writ_id"], $prior_writ[0]["writ_ver"], $prior_writ[0]["staff_id"], '(' . SIT_STAFF_HARD_WORK_EXTRA . ',' . SIT_STAFF_SHIFT_EXTRA . ')');

			$prior_year_full_val = $prior_year_val + $prior_year_family_val + $prior_year_hardwork_shiftwork;
			$this_year_full_val = $this_year_val + $this_year_other_val + $rel_inc;
			$inc_tmp = $this_year_full_val - $prior_year_full_val;

			if ($writ_rec['family_responsible'] == 1)
				$fix_inc = max(array($inc_tmp, 1000000)) - $inc_tmp;
			else
				$fix_inc = max(array($inc_tmp, 600000)) - $inc_tmp;

			$this->param3 = $fix_inc;

			$value = $rel_inc + $fix_inc;
		}

		if (!($value > 0))
			return false;

		return $value;
	}

	/** فوق العاده مناطق کمتر توسعه یافته */
	private function compute_salary_item2_54($writ_rec) {
		//param1 : امتیاز
		//param2 :
		//param3 :
		//param4 :
		//param5 :
		//param6 :
		//param7 :

		if ($writ_rec['execute_date'] >= '2010-12-22') {
			// بنا به قانون جدید
			$edu_main_level = manage_person_education::GetEducationalGroupLevel($writ_rec['education_level'], 'MasterID');

			$this->param3 = $edu_main_level;
			$this->param1 = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_LACK_COEF, $writ_rec['execute_date'], $writ_rec['work_city_id'], $writ_rec['work_state_id'], $edu_main_level);

			if (!$this->param1) {
				parent::PushException(LACK_COEF_NOT_FOUND);
				return false;
			}

			$this->param2 = self::compute_writ_items_sum($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], '(56,35,36,34)');

			if (!($this->param2 > 0))
				return false;
			$value = $this->param1 * $this->param2;
		}
		else if ($writ_rec['execute_date'] < '2010-12-22') {
			if ($writ_rec['execute_date'] >= '2009-03-21')
				$prior_writ_object = manage_writ::get_last_writ_With_salry_before_date($writ_rec['staff_id'], '2009-03-21');
			else
				$prior_writ_object = manage_writ::get_last_writ_With_salry_before_date($writ_rec['staff_id'], $writ_rec['execute_date']);

			$value = self::compute_writ_items_sum($prior_writ_object->writ_id, $prior_writ_object->writ_ver, $prior_writ_object->staff_id, "(" . SIT_STAFF_FACILITIES_VITIOSITY_EXTRA . ',' . SIT_STAFF_DEPRIVED_REGIONS_ABSOPPTION_EXTRA . ")");
		}
		if (!($value > 0)) {
			parent::PushException(LACK_ITEM_CALC_ERR);
			return false;
		}
		return $value;
	}

	/** خدمات اداری در مناطق جنگ زده */
	private function compute_salary_item2_55($writ_rec) {
		//param1 : score
		//param2 :
		//param3 :
		//param4 :
		//param5 :
		//param6 :
		//param7 :

		$query = "SELECT SUM(amount) amount
					FROM staff s
						INNER JOIN persons p ON(s.PersonID = p.PersonID)
						INNER JOIN person_devotions pd ON(pd.PersonID = p.PersonID)
					WHERE s.staff_id = " . $writ_rec["staff_id"] . " AND 
						  devotion_type = " . WAR_REGION_WORK_DEVOTION;
		$dt = PdoDataAccess::runquery($query);

		if ($dt[0]['amount'] == NULL) {
			$dt[0]['amount'] = 0;
			$value = 0;
		}
		if ($dt[0]['amount'] > 0) {
			$this->param1 = round((($dt[0]['amount'] / 365) * 130), 2);
			$rial_coef = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_RIAL_COEF, $writ_rec["execute_date"]);
			$value = $this->param1 * $rial_coef;
		}

		if (!($value > 0))
			return 0 ;

		return $value;
	}
	
	/*
	 * مبلغ قلم تفاوت با مصوبه 3/12/91 هیئت امنا 
	 */
	
	private function compute_salary_item2_73($writ_rec) {
		
		
		
		$query = " select diff_value extra_value 
							
						from Table1_tmp 
						
							where staff_id = ".$writ_rec['staff_id']; 
		
		$dt = PdoDataAccess::runquery($query) ; 
		
		
		$value = $dt[0]['extra_value'] ; 
		
		if (!($value > 0))
			return 0 ;

		return $value;				
	}
	

	/*
	 * محاسبه افزایش سنواتی تشویقی بدون در نظر گرفتن حداقل حقوق
	 */

	private function compute_persuasive_annual_inc($writ_rec) {

		$staff_id = $writ_rec['staff_id'];
		$cur_group = $writ_rec['cur_group'];
		$persuasive_group = $this->param1;
		$execute_date = $writ_rec['execute_date'];

		$query = " SELECT SUBSTRING(CONCAT(execute_date,' ',cur_group),12) cur_group
                            FROM writs
                                WHERE staff_id =" . $writ_rec['staff_id'];

		$dt = PdoDataAccess::runquery($query);

		$input_group = $dt[0]['cur_group'];

		$query = "  SELECT work_start_date
                            FROM staff
                                WHERE staff_id = $staff_id
                     ";
		$res = PdoDataAccess::runquery($query);
		$work_start_date = $res[0]['work_start_date'];
		$work_start_jdate = DateModules::Miladi_to_Shamsi($work_start_date);
		$start_year = substr($work_start_jdate, 0, 4);

		if ($start_year < 1371)
			$start_year = 1371;

		$execute_jdate = DateModules::Miladi_to_Shamsi($execute_date);
		$end_year = substr($execute_jdate, 0, 4);

		// استخراج اطلاعات جانبازي
		$devotions_info = manage_person_devotion::get_devotions_coefs($staff_id, $start_year, $end_year);

		// استخراج اطلاعات احكام
		$writs_info = manage_writ::get_writs_info($staff_id, $start_year - 1, $end_year);

		// استخراج اطلاعات بسيج
		$mob_info = manage_person_devotion::get_mobilizations_coefs($staff_id, $start_year, $end_year);

		$rial_coefs = manage_salary_params::get_rial_coefs($start_year, $end_year, $writ_rec['person_type']);

		$devotion_inc = 0;
		$mobilization_inc = 0;
		$year_coef = array();

		for ($year = $start_year; $year <= $end_year; $year++) {
			// گروه ورودي و عوامل ايثارگري
			$prev_year_coef = $year_coef;
			$year_coef =
					$devotions_info[$year][DEVOTION_TYPE_FIGHTER] +
					$devotions_info[$year][DEVOTION_TYPE_PRISONER] +
					$devotions_info[$year][DEVOTION_TYPE_WOUNDED];

			if ($devotion_inc == 0)
				$devotion_inc += $year_coef * ((array_key_exists(($year - 1), $writs_info)) ? $writs_info[$year - 1]['base_salary'] : 0);
			else if ($year_coef != $prev_year_coef)
				$devotion_inc += ($year_coef - $prev_year_coef) * ((array_key_exists(($year - 1), $writs_info)) ? $writs_info[$year - 1]['base_salary'] : 0 );

			// گروه ورودي و بسيج
			$x3 = ((array_key_exists(($year - 1), $mob_info)) ? $mob_info[$year - 1] : 0 ) * ((array_key_exists(($year - 1), $writs_info)) ? $writs_info[$year - 1]['base_salary'] : 0);
			$mobilization_inc += $x3;
		}

		// اختلاف حقوق پایه با احتساب و بدون احتساب گروه تشویقی
		$base_salary = $rial_coefs[$end_year] * manage_writ_item::Get_employee_base_number($cur_group);

		$base_salary_wp = $rial_coefs[$end_year] * manage_writ_item::Get_employee_base_number($cur_group - $persuasive_group);

		$base_salary_inc = $base_salary - $base_salary_wp;

		$job_extra_inc = $writs_info[$end_year]['job_coef'] * $base_salary - $writs_info[$end_year]['job_coef'] * $base_salary_wp;

		$dominant_job_extra_inc = $writs_info[$end_year]['dominant_job_coef'] * $base_salary - $writs_info[$end_year]['dominant_job_coef'] * $base_salary_wp;

		// جمع مبالغ مربوط به �?وق العاده ايثارگري تشويقي
		$add_value = $devotion_inc + $mobilization_inc + $base_salary_inc + $job_extra_inc + $dominant_job_extra_inc;

		$min_sal_items_sum = manage_writ_item::compute_writ_items_sum($writ_rec['writ_id'], $writ_rec['writ_ver'], $writ_rec['staff_id'], '( ' . SIT_STAFF_BASE_SALARY . ' , ' . SIT_STAFF_ADAPTION_DIFFERENCE . ' , ' . SIT_STAFF_JOB_EXTRA . ' , ' .
						SIT_STAFF_DOMINANT_JOB_EXTRA . ' , ' . SIT_STAFF_ANNUAL_INC . ')');

		$min_sal_param = manage_salary_params::get_salaryParam_value("", $writ_rec['person_type'].",101", SPT_MIN_SALARY, $writ_rec['execute_date']);

		// ت�?اوت حداقل حقوق در صورتي كه تاثير من�?ي دارد اعمال مي گردد
		$min_salary_diff = ($min_sal_param - $min_sal_items_sum);
		if ($min_salary_diff > 0)
			$min_salary_diff = 0;

		$value = $min_salary_diff + $add_value;
		if ($value < 0)
			return false;

		$this->param2 = $input_group;
		$this->param3 = round($devotion_inc);
		$this->param4 = round($base_salary_inc);
		$this->param5 = round($mobilization_inc);
		$this->param6 = round($job_extra_inc);
		$this->param7 = 'مبلغ فوق العاده شغل برجسته :' . round($dominant_job_extra_inc) . '<br>' .
				'جمع همه عوامل :' . round($add_value) . '<br>' .
				'پارامتر حداقل حقوق :' . round($min_sal_param) . '<br>' .
				'اقلام مشمول حداقل حقوق :' . round($min_sal_items_sum) . '<br>';
		return round($value);
	}

	/*	 * *****************************************************************************
	 * *
	 * آیتمهای حقوق روز مزد بیمه ای
	 * *
	 * ***************************************************************************** */

	/*
	  مزد شغل
	 */

	private function compute_salary_item3_01($writ_rec) {


		//param1 : گروه
		//param2 : مزد شغل
		//param3 : ورودی : مجموع روزهای کارکرد سال قبل
		// should be deleted at the first of 91 ------------------------------------------
             

		$MONTH_DAY_COUNT = 30;


		if (!(1 <= $writ_rec['job_group'] && $writ_rec['job_group'] <= 20)) {
			parent::PushException(UNKNOWN_JOB_GROUP);
			return false;
		}

		$job_group = $writ_rec['job_group'];

 
		$group1_annual_rate = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"], SPT_GROUP1_ANNUAL_RATE, $writ_rec['execute_date']);
				
		if ($group1_annual_rate < 0) {
			parent::PushException(UNKNOWN_GROUP1_ANNUAL_RATE);
			return false;
		}

		$job_salary = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"], SPT_JOB_SALARY, $writ_rec['execute_date'], $writ_rec['job_group']);

		if (!(0 < $job_salary)) {

			parent::PushException(UNKNOWN_JOB_SALARY);
			return false;
		}
		
		$this->param3 = 365 ;
		 
		$annual_salary = ($this->param3 / 365) * $group1_annual_rate;
		$this->param1 = $job_group;
		$this->param2 = $job_salary;
						
		if ($writ_rec['execute_date'] >= '2012-03-20') {
			$month_duration = 30;
		}
		//مبناي محاسبه تعداد روز در ماه براي محاسبه 31 روز است نه 30 روز.
		//$month_duration = 30 ; //$MONTH_DAY_COUNT;
		$value = ($job_salary + $annual_salary) * $month_duration;

		return $value;
	}

	/*
	 * * حق عائله مندي
	 */

	private function compute_salary_item3_02($writ_rec) {
		return $this->compute_salary_item5_35($writ_rec);
	}

	/*
	 * فوق العاده نوبت کاری
	 */

	private function compute_salary_item3_03($writ_rec) {
		return $this->compute_salary_item5_36($writ_rec);
	}

	/*
	 * * حق مسكن
	 */

	private function compute_salary_item3_04($writ_rec) {
		return $this->compute_salary_item5_37($writ_rec);
	}

	/*
	 * * حق خواروبار
	 */

	private function compute_salary_item3_05($writ_rec) {
		return $this->compute_salary_item5_38($writ_rec);
	}

/*سرپرستی*/
	private function compute_salary_item3_06($writ_rec) {
		
		if ($writ_rec['execute_date'] > '2015-03-20') {
			$qry = " select  wsi.value bv
						from writs w inner join writ_salary_items wsi
													on  w.staff_id = wsi.staff_id and
														w.writ_id = wsi.writ_id and  w.writ_ver = wsi.writ_ver

						where w.execute_date = '2015-01-31' and salary_item_type_id = 10392 and w.staff_id = ".$writ_rec['staff_id'] ; 
			$res = PdoDataAccess::runquery($qry) ; 
			$value = ($res[0]['bv'] * 1.17) ;
			
			}
			else $value = 0 ; 
			
		return  $value ;
	}

	/*
	 * افزایش سنواتی کارکنان روز مزد دانشگاه
	 */

	private function compute_salary_item3_07($writ_rec) {

		//param1 : افزایش سنواتی سال قبل
		//param2 : نرخ سنوات امسال
		//param3 : تعداد روزهاي ماه
		// should be deleted at the first of 91 ------------------------------------------

if ($writ_rec['execute_date'] == '2015-01-31') {
			$qry = " select  (wsi.value ) bv , param1,param2,param3
						from writs w inner join writ_salary_items wsi
													on  w.staff_id = wsi.staff_id and
														w.writ_id = wsi.writ_id and  w.writ_ver = wsi.writ_ver

						where w.execute_date = '2015-01-31' and salary_item_type_id = 283 and w.staff_id = ".$writ_rec['staff_id'] ; 
			$res = PdoDataAccess::runquery($qry) ; 
			
			$this->param1 =$res[0]['param1']; 
			$this->param2 =$res[0]['param2']; 
			$this->param3 =$res[0]['bv']; 
			
			 if( $res[0]['bv'] > 0 ) 
			     return $res[0]['bv'] ; 			 
						
		}

if ($writ_rec['execute_date'] > '2015-03-20') {
			$qry = " select  (wsi.value/30 ) bv
						from writs w inner join writ_salary_items wsi
													on  w.staff_id = wsi.staff_id and
														w.writ_id = wsi.writ_id and  w.writ_ver = wsi.writ_ver

						where w.execute_date = '2015-01-31' and salary_item_type_id = 283 and w.staff_id = ".$writ_rec['staff_id'] ; 
			$res = PdoDataAccess::runquery($qry) ; 
			$job_group = $writ_rec['job_group'];
			$annual_rate = manage_salary_params::get_salaryParam_value("", $writ_rec['person_type'].",101", SPT_GROUP1_ANNUAL_RATE, $writ_rec['execute_date'], $job_group);

			if (!(0 < $annual_rate)) {
				parent::PushException(UNKNOWN_GROUP1_ANNUAL_RATE);
				return false;
			}
                        $this->param1 = $res[0]['bv'] ;
			$this->param2 = $annual_rate ; 
                        $this->param3 = 1.17 ;
			$value = (($res[0]['bv'] * 1.17) + $annual_rate) * 30 ; 
			
		}
		else {
		$MONTH_DAY_COUNT = ($writ_rec["person_type"] == HR_WORKER) ? 31 : MONTH_DAY_COUNT;

		if ($writ_rec['execute_date'] >= '2012-03-20') {
			$MONTH_DAY_COUNT = 30;
		}

		if (!(1 <= $writ_rec['job_group'] && $writ_rec['job_group'] <= 20)) {
			parent::PushException(UNKNOWN_JOB_GROUP);
			return false;
		}

		$job_group = $writ_rec['job_group'];

		$Jexecute_date = DateModules::miladi_to_shamsi($writ_rec['execute_date']);
		$year = substr($Jexecute_date, 0, 4);
		$prior_year_last_day = ($year - 1) . "/12/29";

		$prior_Gexecute_date = DateModules::shamsi_to_miladi($prior_year_last_day);
		$annual_rate = manage_salary_params::get_salaryParam_value("", $writ_rec['person_type'].",101", SPT_GROUP1_ANNUAL_RATE, $writ_rec['execute_date'], $job_group);

		if (!(0 < $annual_rate)) {
			parent::PushException(UNKNOWN_GROUP1_ANNUAL_RATE);
			return false;
		}

		$last_writ_year = manage_writ::get_last_writ_With_salry_before_date($writ_rec['staff_id'], $prior_Gexecute_date);

		if ($last_writ_year) {
			$prior_annual_inc = manage_writ_item::get_writSalaryItem_value($last_writ_year->writ_id, $last_writ_year->writ_ver, $last_writ_year->staff_id, SIT_WORKER_ANNUAL_INC);
		} else {
			$prior_annual_inc = 0;
		}
		//مبناي محاسبه تعداد روز در ماه براي محاسبه 31 روز است نه 30 روز.
		$month_duration = $MONTH_DAY_COUNT;
		
		$value = $prior_annual_inc + ($annual_rate * $month_duration);
}
		return $value;
	}

	/*
	 * فوق العاده ایثارگری
	 */

	private function compute_salary_item3_47($writ_rec) {


		// should be deleted at the first of 91 ------------------------------------------
		$MONTH_DAY_COUNT = ($writ_rec["person_type"] == HR_WORKER) ? 31 : MONTH_DAY_COUNT;

		if ($writ_rec['execute_date'] >= '2012-03-20') {
			$MONTH_DAY_COUNT = 30;
		}

		$k = 0;
		$devotion_coefs = manage_person_devotion::get_devotions_last_coefs($writ_rec['staff_id'] ,"", $writ_rec["person_type"] );
		$prisoner = $devotion_coefs['prisoner'] / 30;
		$fighter = $devotion_coefs['fighter'] / 30;
                                    
		if ( ($writ_rec["person_type"] == HR_WORKER) && ($prisoner >= 9 || $fighter >= 9))
			$k = 1;
		else if ($writ_rec["person_type"] == HR_WORKER)  {

			$query = "SELECT s.staff_id,
						CASE WHEN pd.devotion_type=" . DEVOTION_TYPE_FIGHTER . "
									 THEN if(( pd.from_date <= '1988-08-20' and to_date <= '1988-08-20' ),amount ,
										  if(( pd.from_date <= '1988-08-20' and to_date >= '1988-08-20' ) ,
											   datediff('1988-08-20',pd.from_date ) ,0 )) END fighter ,
						CASE WHEN pd.devotion_type=" . DEVOTION_TYPE_PRISONER . " THEN amount ELSE 0 END prisoner

				 FROM staff s
					 INNER JOIN persons p ON (p.personID = s.personID)
					 INNER JOIN person_devotions pd ON (pd.personID = p.personID)
				 WHERE s.staff_id =" . $writ_rec['staff_id'];

			$res = parent::runquery($query);

			for ($i = 0; $i < count($res); $i++) {
				if (($res[$i]['fighter'] / 30 ) >= 6 || ($res[$i]['prisoner'] / 30 ) >= 6) {
					$k = 1;
					break;
				}
			}
		}
                else if ($devotion_coefs['prisoner'] >= 1 || $devotion_coefs['fighter'] >= 1 ) 
                    $k = 1;
          
		if ($devotion_coefs['wounded'] > 0 || $k == 1) { 
			if (!(1 <= $writ_rec['job_group'] && $writ_rec['job_group'] <= 20)) {
				parent::PushException(UNKNOWN_JOB_GROUP);
				return false;
			}

			$job_group = $writ_rec['job_group'];
			$this->param1 = $job_group;
			$cur_value = manage_salary_params::get_salaryParam_value("", $writ_rec['person_type'].",101", SPT_JOB_SALARY, $writ_rec['execute_date'], $job_group);

			if (!(0 < $cur_value)) {
				parent::PushException(UNKNOWN_JOB_SALARY);
				return false;
			}
			$this->param2 = $cur_value;

			$next_value = manage_salary_params::get_salaryParam_value("", $writ_rec['person_type'].",101", SPT_JOB_SALARY, $writ_rec['execute_date'], $job_group + 1);

			if (!(0 < $next_value)) { 
				parent::PushException(UNKNOWN_JOB_SALARY);
				return false;
			}
			$this->param3 = $next_value;
			$value = ($next_value - $cur_value) * $MONTH_DAY_COUNT;

			if ($value <= 0)
				return 0;
			else
				return $value;
		} 


		return 0;
	}

	/*	 * *ساير مزاياي روزمزد بيمه اي */

	private function compute_salary_other_premium($writ_rec) {

		//$param1 گروه معادل استخدام كشوري افراد
		//$param2 افزايش سنواتي سال جاري
		//$param3 افزايش سنواتي سال قبل
		//$param4 ضريب فوق العاده شغل .
		//$param5 ضريب فوق العاده جذب

		$worker_salary = $this->compute_writ_items_sum($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"]
				, '( ' . SIT_WORKER_BASE_SALARY . ' , ' . SIT_WORKER_ANNUAL_INC . ')');

		// ________________________________________
		//compute_other_premium_base_salary
		$rial_coef = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_RIAL_COEF, $writ_rec['execute_date']);
		if (!$rial_coef) {
			parent::PushException(RIAL_COEF_NOT_FOUND);
			return false;
		}

		$worker_emp_base_salary = $rial_coef * manage_writ_item::Get_employee_base_number($this->param1);
		//_________________________________________

		$worker_emp_annual_inc = $this->param2;
		$worker_emp_job_extra = $this->param4 * $worker_emp_base_salary;

		//_________________________________________
		//compute_other_premium_min_salary

		$min_sal_value = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_MIN_SALARY, $writ_rec['execute_date']);
		$worker_emp_min_salary = max($min_sal_value - ($worker_emp_base_salary + $worker_emp_annual_inc + $worker_emp_job_extra), 0);
		//_________________________________________
		//compute_other_premium_absorb_extra
		$worker_emp_absorb_extra = $this->param5 *
				($worker_emp_base_salary + $worker_emp_annual_inc + $worker_emp_job_extra + $worker_emp_min_salary);
		//_________________________________________
		//compute_other_premium_bad_weather_extra
		$worker_emp_bad_wheader = 0;

		if ($writ_rec['emp_mode'] == EMP_MODE_ENGAGEMENT || $writ_rec['emp_mode'] == EMP_MODE_EDUCATIONAL_MISSION)
			$worker_emp_bad_wheader = 0;

		$this->param1 = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_WHEATHER_COEF, $writ_rec['execute_date']);
		if (!$this->param1) {
			parent::PushException('WHEATHER_COEF_NOT_FOUND');
			$worker_emp_bad_wheader = 0;
		}
		$value = $this->param1 * $worker_emp_base_salary;
		if (!($value > 0)) {
			parent::PushException('WHEATHER_ITEM_CALC_ERR');
			$worker_emp_bad_wheader = 0;
		}
		//_________________________________________
		//compute_other_premium_8_9_absorb
		//// محاسبه فوق العاده جذب بندهاي 8 و 9
		// به دست آوردن اولين روز سال قبل
		$this_writ_year = substr(DateModules::Miladi_to_Shamsi($writ_rec['execute_date']), 0, 4);
		$one_year_ago = $this_writ_year - 1;
		$one_year_ago_first_day = $one_year_ago . "/01/01";
		$Gone_year_ago_first_day = DateModules::Shamsi_to_Miladi($one_year_ago_first_day);

		// ضريب ريالي سال قبل
		$rial_coef = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_RIAL_COEF, $Gone_year_ago_first_day);
		if (!$rial_coef) {
			parent::PushException('RIAL_COEF_NOT_FOUND');
			return false;
		}

		// حقوق پايه سال قبل
		$prior_base_salary = $rial_coef * manage_writ_item::Get_employee_base_number($this->param1);

		// افزايش سنواتي سال قبل
		$prior_annual_inc = $this->param3;

		//فوق العاده شغل سال قبل
		$prior_job_extra = $prior_base_salary * $this->param4;

		// حداقل دريافتي سال قبل
		$min_sal_value = manage_salary_params::get_salaryParam_value("", $writ_rec["person_type"].",101", SPT_MIN_SALARY, $Gone_year_ago_first_day);
		$prior_min_salary = max($min_sal_value - ($prior_base_salary + $prior_annual_inc + $prior_job_extra), 0);

		// جمع اقلام مرتبط با فوق العاده تعديل
		$adjust_sal_items_sum = $prior_base_salary + $prior_annual_inc + $prior_job_extra + $prior_min_salary;

		// محاسبه فوق العاده تعديل سال قبل
		$max_value = manage_writ_item::Get_employee_base_number(1) * $rial_coef * 1.5;
		if ($adjust_sal_items_sum <= 3000000)
			$value = $max_value;
		elseif ($adjust_sal_items_sum >= 5700000)
			$value = 0;
		else
			$value = (1 - (intval(($adjust_sal_items_sum - 3000000) / 300000) + 1) * 0.1) * $max_value;

		$worker_emp_8_9_absorb = $value * 1.5;
		//_________________________________________

		$worker_emp_salary =
				$worker_emp_base_salary +
				$worker_emp_annual_inc +
				$worker_emp_job_extra +
				$worker_emp_min_salary +
				$worker_emp_absorb_extra +
				$worker_emp_bad_wheader +
				$worker_emp_8_9_absorb;

		$this->param6 = $worker_salary;

		$this->param7 = 'حقوق پايه:' . intval($worker_emp_base_salary) . '<br>' .
				'سنوات:' . intval($worker_emp_annual_inc) . '<br>' .
				'ف شغل:' . intval($worker_emp_job_extra) . '<br>' .
				'حداقل:' . intval($worker_emp_min_salary) . '<br>' .
				'ف جذب:' . intval($worker_emp_absorb_extra) . '<br>' .
				'ف بدي آب و هوا:' . intval($worker_emp_bad_wheader) . '<br>' .
				'جذب 8 و 9 ر:' . intval($worker_emp_8_9_absorb) . '<br>' .
				'جمع:' . intval($worker_emp_salary);

		$this->param2 = CurrencyModulesclass::toCurrency($this->param2);
		$value = max((11 / 12) * $worker_emp_salary - $worker_salary, 0);

		if (!($value > 0))
			return false;
		return $value;
	}

	/*	 * *ساير مزاياي روزمزد بيمه اي */

	private function compute_salary_other_premium3($writ_rec) {

		$values = array(
			UNDER_DIPLOMA_LEVEL => 200000,
			DIPLOMA_LEVEL => 300000,
			HIGH_DIPLOMA_LEVEL => 600000,
			BS_LEVEL => 900000,
			MS_LEVEL => 900000,
			PHD_LEVEL => 900000);
		$edu_main_level = manage_person_education::GetEducationalGroupLevel($writ_rec['education_level'], 'MasterID');
		$value = $values[$edu_main_level];
		if (!($value > 0))
			return false;
		return $value;
	}

//  سایر پرداختی ها 
	private function compute_salary_item3_10($writ_rec) {
                if ($writ_rec['execute_date'] >= '2015-03-21') {
			$qry = " select  wsi.value bv
						from writs w inner join writ_salary_items wsi
													on  w.staff_id = wsi.staff_id and
														w.writ_id = wsi.writ_id and  w.writ_ver = wsi.writ_ver

						where w.execute_date = '2015-01-31' and salary_item_type_id = 10231 and w.staff_id = ".$writ_rec['staff_id'] ; 
			$res = PdoDataAccess::runquery($qry) ; 
			$value = ($res[0]['bv'] * 1.17) ;
			return $value;
			}
			else
		return $this->compute_salary_item2_19($writ_rec);
	}

	/*	 * *****************************************************************************
	 * *
	 * * آیتمهای حقوقی قراردادی ها 
	 * *
	 * ***************************************************************************** */

	/*	 * * حقوق مبنا */

	private function compute_salary_item5_01($writ_rec) {

		return $this->compute_salary_item2_01($writ_rec);
	}

	/** افزایش سنواتی  */
	private function compute_salary_item5_02($writ_rec) {

		return $this->compute_salary_item2_02($writ_rec);
	}

	/** حق عائله مندی   */
	private function compute_salary_item5_04($writ_rec) {

		return $this->compute_salary_item2_04($writ_rec);
	}

	/** حق اولاد */
	private function compute_salary_item5_05($writ_rec) {

		return $this->compute_salary_item2_05($writ_rec);
	}

	/*	 * * فوق العاده محروميت تسهيلات */

	private function compute_salary_item5_07($writ_rec) {

		return $this->compute_salary_item2_07($writ_rec);
	}

	/** فوق العاده شغل  */
	private function compute_salary_item5_10($writ_rec) {

		return $this->compute_salary_item2_10($writ_rec);
	}

	/*	 * * فوق العاده جذب   */

	private function compute_salary_item5_13($writ_rec) {

		return $this->compute_salary_item2_13($writ_rec);
	}

	/*	 * * فوق العاده بدي آب و هوا   */

	private function compute_salary_item5_15($writ_rec) {

		return $this->compute_salary_item2_15($writ_rec);
	}

	/*	 * * فوق العاده نوبت کاري  */

	private function compute_salary_item5_18($writ_rec) {

		return $this->compute_salary_item2_18($writ_rec);
	}

	

	/** حق عائله مندی  */
	private function compute_salary_item5_35($writ_rec) {

		//param1 : وضعيت تاهل
		//param2 : تعداد فرزند مشمول
		//حق عايله مندي به افراد مجرد تعلق نمي گيرد.
		if ($writ_rec['marital_status'] == 1) {
			parent::PushException(SINGLES_CANT_GET_CHILD_LAW);
			return false;
		}

		$group1_job_salary = manage_salary_params::get_salaryParam_value("", $writ_rec['person_type'].",101", SPT_JOB_SALARY, $writ_rec['execute_date'], 1);

		if (!(0 < $group1_job_salary)) { 
			parent::PushException(UNKNOWN_JOB_SALARY);
			return false;
		}

		if (($writ_rec['marital_status'] > MARITAL_STATUS_SINGLE) && ($writ_rec['included_children_count'] > 0 ) /*&&
				($writ_rec['family_responsible'] > 0 )*/) {
			$query = " SELECT title FROM Basic_Info where typeid = 15 and infoid = " . $writ_rec['marital_status'];
			$res = parent::runquery($query);
			$this->param1 = $res[0]['title'];
			$this->param2 = $writ_rec['included_children_count'];

			if ($this->param2 > 2 && $writ_rec['execute_date'] < '2014-03-21' )
				$this->param2 = 2;

			$value = 3 * $this->param2 * $group1_job_salary;
		}
		else
			$value = 0;

		return $value;
	}

	/*	 * * فوق العاده نوبت كاري   */

	private function compute_salary_item5_36($writ_rec) {
		// param1 :  گروه
		// param2 :  مزد مبنا
		// param3 : ضریب فوق العاده نوبت کاری input
		// should be deleted at the first of 91 ------------------------------------------
		$MONTH_DAY_COUNT = ($writ_rec["person_type"] == HR_WORKER) ? 31 : MONTH_DAY_COUNT;

		if ($writ_rec['execute_date'] >= '2012-03-20') {
			$MONTH_DAY_COUNT = 30;
		}

		if (!(1 <= $writ_rec['job_group'] && $writ_rec['job_group'] <= 20)) {
			parent::PushException(UNKNOWN_JOB_GROUP);
			return false;
		}

		$job_salary = manage_salary_params::get_salaryParam_value("", $writ_rec['person_type'], SPT_JOB_SALARY, $writ_rec['execute_date'], $writ_rec['job_group']);
		if (!(0 < $job_salary)) {
			parent::PushException(UNKNOWN_JOB_SALARY);
			return false;
		}

		$annual_inc = manage_writ_item::get_writSalaryItem_value($writ_rec["writ_id"], $writ_rec["writ_ver"], $writ_rec["staff_id"], ($writ_rec['person_type'] == 3 ) ? SIT_WORKER_ANNUAL_INC : SIT_STAFF_DEFINED_ANNUAL_INC);

		$this->param1 = $writ_rec['job_group'];
		$this->param2 = $job_salary + $annual_inc;

		$value = (($job_salary * $MONTH_DAY_COUNT) + $annual_inc) * $this->param3;

		return $value;
	}

	/*	 * * حق مسكن   */

	private function compute_salary_item5_37($writ_rec) {

		//param1 : وضعيت تاهل

		$query = " SELECT title FROM Basic_Info where typeid = 15 and infoid = " . $writ_rec['marital_status'];
		$res = parent::runquery($query);
		$this->param1 = $res[0]['title'];
		$value = manage_salary_params::get_salaryParam_value("", $writ_rec['person_type'].",101", SPT_HOME_EXTRA, $writ_rec['execute_date'], $writ_rec['marital_status']);

		if (!(0 < $value)) { echo PdoDataAccess::GetLatestQueryString(); die();
			parent::PushException(UNKNOWN_HOME_EXTRA);
			return false;
		}

		return $value;
	}

	/*	 * * حق خواروبار  */

	private function compute_salary_item5_38($writ_rec) {
		//param1 : وضعيت تاهل

		$query = " SELECT title FROM Basic_Info where typeid = 15 and infoid = " . $writ_rec['marital_status'];
		$res = parent::runquery($query);
		$this->param1 = $res[0]['title'];
		$value = manage_salary_params::get_salaryParam_value("", $writ_rec['person_type'].",101", SPT_FOOD_EXTRA, $writ_rec['execute_date'], $writ_rec['marital_status']);

		if (!(0 < $value)) {
			parent::PushException(UNKNOWN_HOME_EXTRA);
			return false;
			sisRaiseException('UNKNOWN_FOOD_EXTRA');
			return false;
		}

		return $value;
	}

	/*	 * فوق العاده ویژه* */

	private function compute_salary_item5_16($writ_rec) {

		return $this->compute_salary_item2_16($writ_rec);
	}

	/*	 * * فوق العاده جذب 7% */

	private function compute_salary_item5_17($writ_rec) {

		return $this->compute_salary_item2_17($writ_rec);
	}

	/* حداقل دریافتی* */

	private function compute_salary_item5_03($writ_rec) {

		return $this->compute_salary_item2_03($writ_rec);
	}

	/**
	  مزد سنوات
	 */
	private function compute_salary_item5_39($writ_rec) {

		// should be deleted at the first of 91 ------------------------------------------
		$MONTH_DAY_COUNT = ($writ_rec["person_type"] == HR_WORKER) ? 31 : MONTH_DAY_COUNT;

		if ($writ_rec['execute_date'] >= '2012-03-20') {
			$MONTH_DAY_COUNT = 30;
		}

		//param1 : افزايش سنواتي سال قبل
		//param2 : نرخ سنوات امسال
		//param3 : تعداد روزهاي ماه

		$this_writ_year = substr(DateModules::miladi_to_shamsi($writ_rec['execute_date']), 0, 4);
		$one_year_ago = $this_writ_year - 1;
		$one_year_ago_first_day = $one_year_ago . "-01-01";
		$one_year_ago_last_day_writ = $one_year_ago . "-12-29";
		$Gone_year_ago_first_day = DateModules::shamsi_to_miladi($one_year_ago_first_day);
		$Gone_year_ago_last_day = DateModules::shamsi_to_miladi($one_year_ago_last_day_writ);

		if ($this_writ_year >= '1389') {
			//آخرين حکم قبل از سال شخص را استخراج مي کند.
			$prior_writ_rec = manage_writ::get_last_writ_by_date($writ_rec['staff_id'],$Gone_year_ago_last_day); //'2013-02-18'
			
			
			
			//در بدو استخدام سنوات به شخص تعلق نمي گيرد.
			if ($prior_writ_rec->writ_id == "")
				return 0;
			//end
		}
		
		$annual_rate = manage_salary_params::get_salaryParam_value("", $writ_rec['person_type'].",101", SPT_GROUP1_ANNUAL_RATE, $writ_rec['execute_date'], $writ_rec['job_group']);
		if (!(0 < $annual_rate)) {
			parent::PushException(UNKNOWN_GROUP1_ANNUAL_RATE);
			return false;
		}

		if ($this_writ_year >= '1389') {
			if ($prior_writ_rec->writ_id != "")
				$prior_annual_inc = manage_writ_item::get_writSalaryItem_value($prior_writ_rec->writ_id, $prior_writ_rec->writ_ver, $prior_writ_rec->staff_id, SIT_STAFF_DEFINED_ANNUAL_INC);

			else
				$prior_annual_inc = 0;
		}
		else {
			$prior_annual_inc = 0;
		}
		
	  

		$work_start_date = $writ_rec["work_start_date"];
		if ($this_writ_year >= '1389')
			$year_work_days = manage_writ::compute_year_work_days($Gone_year_ago_first_day, $Gone_year_ago_last_day, $writ_rec['staff_id']);
		else
			$year_work_days = manage_writ::compute_year_work_days($work_start_date, $Gone_year_ago_last_day, $writ_rec['staff_id']);

		if ($year_work_days > 365 &&
				$writ_rec['person_type'] == HR_CONTRACT) {

			$year_work_days = 360;
		}

		$Day_Year = ( $writ_rec['person_type'] == HR_CONTRACT ) ? 360 : 365;

		if ($writ_rec['person_type'] == HR_CONTRACT)
			$month_duration = 30;
		else if ($writ_rec['person_type'] == HR_WORKER)
			$month_duration = $MONTH_DAY_COUNT;

		if ($writ_rec['execute_date'] >= '2012-03-20') {
			$month_duration = 30;
		}


		$value = $prior_annual_inc + (($annual_rate * $month_duration) * $year_work_days / $Day_Year );

		return $value;
	}

	/*
	 * * فوق العاده تعديل
	 */

	private function compute_salary_item5_32($writ_rec) {

		//param1 : مجموع اقلام حقوقي مورد نظر
		//مجموع اقلام حقوقي
		//حقوق  مبنا + افزايش سنواتي + فوق العاده شغل + فوق العاده برجسته + حداقل دريافتي +
		//تفاوت تطبيق + فوق العاده جذب + فوق العاده ويژه
		$this->param1 = $this->compute_writ_items_sum($writ_rec['writ_id'], $writ_rec['writ_ver'], $writ_rec['staff_id'], '( ' . SIT5_STAFF_BASE_SALARY . ' , ' .
				SIT5_STAFF_ANNUAL_INC . ' , ' .
				SIT5_STAFF_JOB_EXTRA . ' , ' .
				SIT5_STAFF_DOMINANT_JOB_EXTRA . ' , ' .
				SIT5_STAFF_MIN_PAY . ' , ' .
				SIT_STAFF_EQUALITY_DIFFERENCE . ' , ' .
				SIT5_STAFF_ABSOPPTION_EXTRA . ' , ' .
				SIT5_EMPLOYEE_SPECIAL_EXTRA . ' )');

		if ($this->param1 <= 3000000)
			$value = 272400;
		else if ($this->param1 > 3000000)
			$value = (1 - ceil((($this->param1 - 3000000) / 300000)) / 10) * 272400;

		return $value;
	}

	/*
	 * * فوق العاده جذب بندهای 8 و 9
	 */

	private function compute_salary_item5_33($writ_rec) {

		//param1 : مبلغ فوق العاده تعديل سال قبل
		//param2 : ضريب

		$this_writ_year = substr(DateModules::miladi_to_shamsi($writ_rec['execute_date']), 0, 4);

		$one_year_ago = $this_writ_year - 1;
		$one_year_ago_last_day = $one_year_ago . "/12/29";
		$Gone_year_ago_last_day = DateModules::shamsi_to_miladi($one_year_ago_last_day);

		//آخرين حکم قبل از سال شخص را استخراج مي کند.
		$prior_writObj = manage_writ::get_last_writ_by_date($writ_rec['staff_id'], $Gone_year_ago_last_day);

		//مقدار قلم فوق العاده تعديل آخرين حکم سال قبل را استخراج مي کند.
		//اين قلم بر اساس بخشنامه افزايش حقوق سال 87 اضافه شد.
		$this->param1 = manage_writ_item::get_writSalaryItem_value($prior_writObj->writ_id, $prior_writObj->writ_ver, $prior_writObj->staff_id, SIT5_STAFF_EXTRA_ADJUST);
		$this->param2 = 1.5;

		$value = $this->param1 * $this->param2;

		if (!($value > 0)) {
			return false;
		}

		return $value;
	}

	/*
	 * *سایر
	 */

	private function compute_salary_item5_40($writ_rec) {
		//param1 : یک دوازدهم مزد شغل گروه و مزد سنوات
		//param2 : تفاوت تا حداقل پیمانی


		$sum_include_salary = manage_writ_item::compute_writ_items_sum($writ_rec['writ_id'], $writ_rec['writ_ver'], $writ_rec['staff_id'], '(605,885)');
		$this->param1 = $sum_include_salary / 12;

		//min salary with children right
		// 1 = single 2 = married with no children 3 = married with 1 children 4 = married with 2 children
		// 1 = diploma 2 = BA 3 = BSc 4 = MSc 5 = PhD
		$values = array(1 => array(1 => 2000000, 2 => 2000000, 3 => 3060000, 4 => 3480000, 5 => 3900000),
			2 => array(1 => 2916000, 2 => 3186000, 3 => 3546000, 4 => 3966000, 5 => 4386000),
			3 => array(1 => 3042000, 2 => 3312000, 3 => 3672000, 4 => 4092000, 5 => 4512000),
			4 => array(1 => 3168000, 2 => 3438000, 3 => 3798000, 4 => 4218000, 5 => 4638000));
		//وضعیت تاهل
		if ($writ_rec['marital_status'] == 1 || $writ_rec['marital_status'] == 3 || $writ_rec['marital_status'] == 4) {
			$row_index = 1;
		} else if ($writ_rec['marital_status'] == 2) {
			$row_index = 2;
			if ($writ_rec['included_children_count'] > 0 && $writ_rec['included_children_count'] <= 2) {
				$row_index += $writ_rec['included_children_count'];
			}
		}

		//مدرک تحصیلی
		if ($writ_rec['education_level'] <= 201) {
			$col_index = 1;
		} else if ($writ_rec['education_level'] == 300 || $writ_rec['education_level'] == 301) {
			$col_index = 2;
		} else if ($writ_rec['education_level'] == 202 ||
				$writ_rec['education_level'] == 400 ||
				$writ_rec['education_level'] == 401) {
			$col_index = 3;
		} else if ($writ_rec['education_level'] == 500 ||
				$writ_rec['education_level'] == 501 ||
				$writ_rec['education_level'] == 604) {
			$col_index = 4;
		} else if ($writ_rec['education_level'] > 501 && $writ_rec['education_level'] != 604) {
			$col_index = 5;
		}

		//مجموع کلیه اقلام حقوقی شخص
		$sum_total_salary = manage_writ_item::compute_writ_items_sum($writ_rec['writ_id'], $writ_rec['writ_ver'], $writ_rec['staff_id'], '(605,609,632,633,885)');

		$contractive_equal_value = $values[$row_index][$col_index];

		if (($sum_total_salary + $this->param1) < $contractive_equal_value) {
			$this->param2 = $contractive_equal_value - ($sum_total_salary + $this->param1);
		} else {
			$this->param2 = 0;
		}

		$this->param1 = (int) $this->param1;
		$this->param2 = (int) $this->param2;

		$value = $this->param1 + $this->param2;

		return $value;
	}

	//سایر پرداختی ها 
	private function compute_salary_item5_41($writ_rec) {
		return $this->compute_salary_item2_19($writ_rec);
	}

	/*
	 * * سایر مزایای 3
	 */

	private function compute_salary_other_premium5($writ_rec) {

		//$param1 سایر مزایا1
		//$param2 سایر مزایا2
		//$param3
		//$param4
		//$param5


		$edu_main_level = manage_person_education::GetEducationalGroupLevel($writ_rec['education_level'], 'MasterID');


		$Minsalary = manage_salary_params::get_salaryParam_value("", $writ_rec['person_type'].",101", SPT_SAYER_MAZAYA1, $writ_rec['execute_date'], $edu_main_level);

		$Maskan = self::compute_salary_item5_37($writ_rec);
		$Mozdgroup = self::compute_salary_item5_34($writ_rec);

		$this->param1 = $Minsalary - ($Maskan + $Mozdgroup );

		if ($this->param1 < 0)
			$this->param1 = 0;

		$this->param2 = manage_salary_params::get_salaryParam_value("", $writ_rec['person_type'].",101", SPT_SAYER_MAZAYA2, $writ_rec['execute_date'], $edu_main_level);


		$value = $this->param1 + $this->param2;

		if (!($value > 0))
			return 0;
		return $value;
	}

}
