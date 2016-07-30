<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.12
//---------------------------

class manage_salary_item_type extends PdoDataAccess
{
	public $salary_item_type_id ;
	public $person_type;
	public $user_defined;
	public $effect_type;
	public $full_title;
	public $print_title;
	public $compute_place;
	public $insure_include;
	public $tax_include;
	public $retired_include;
	public $user_data_entry;
	public $salary_compute_type;
	public $work_time_unit;
	public $multiplicand;
	public $function_name;
	public $param1_title;
	public $param1_input;
	public $param2_title;
	public $param2_input;
	public $param3_title;
	public $param3_input;
	public $param4_title;
	public $param4_input;
	public $remember_distance;
	public $remember_message;
	public $print_order;
	public $validity_start_date;
	public $validity_end_date;
	
	public $available_for;
	public $backpay_include;
	public $month_length_effect;
	public $pension_include;
	public $credit_topic;
	public $editable_value;
	public $param5_title;
	public $param5_input;
	public $param6_title;
	public $param6_input;
	public $param7_title;
	public $param7_input;
	


	function __construct($salary_item_type_id = "")
	 {
		$this->DT_validity_start_date = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_validity_end_date = DataMember::CreateDMA(DataMember::DT_DATE);
		

		if($salary_item_type_id != "")
		{
		 	$query = "select * from HRM_salary_item_types where salary_item_type_id=:sit" ;
		 	PdoDataAccess::FillObject($this, $query, array(":sit"=> $salary_item_type_id));
           
		}
        else { 
            $this->insure_include = "1";
            $this->tax_include = "1";
            $this->retired_include = "1";
            $this->pension_include = "1";
            $this->user_data_entry = "1";
            $this->backpay_include = "0";

        }

	 	return;
	 }
         
         static function selectSubItem($where ,$whereParam)
         {
             $query = " select sit.salary_item_type_id , sit.full_title , bi.Title person_type_title , sit.person_type
                                    from salary_item_types sit inner join Basic_Info bi 
                                                                    on sit.person_type = bi.InfoID and bi.typeid = 16
                                                    where sit.effect_type = 2 ".$where;  
             
             return parent::runquery($query,$whereParam ) ;
         }

	 static function Select($where, $whereParam)
	 {
			 
			/*if($_GET['type'] == 1 ) {				
				
				$query = " select * from salary_item_types where available_for in (1) " ; 				
				$query .= ( $where != "" ) ? " AND ".$where : ""  ; 
			} 
			elseif($_GET['type'] == 2 ) {
				$query = " select * from salary_item_types where available_for in (4,5) " ; 
				$query .= ( $where != "" ) ? " AND ".$where : ""  ; 
			}
			else {*/
				$query = "select sit.salary_item_type_id,
								'کارمند' PTitle ,
								bi2.InfoDesc effectTitle ,
								full_title,
								print_title,
								bi3.InfoDesc compute_place_title ,
								insure_include,
								case insure_include
								when 1 then '*'
								when 0 then ''
								end insure_include_title ,
								tax_include,
								case tax_include
									when 0 then ''
									when 1 then '*'
								end tax_include_title ,
								user_data_entry,
								retired_include,
								case retired_include
									when 0 then ''
									when 1 then '*'
								end retired_include_title ,
								case pension_include
									when 0 then ''
									when 1 then '*'
								end pension_include_title ,
								case user_data_entry
									when 0 then ''
									when 1 then '*'
								end user_data_entry_title ,
								bi4.InfoDesc salary_compute_type_title ,
								bi5.InfoDesc  multiplicand_title,
								function_name,
								param1_title,
								param2_title,
								param3_title,
								remember_distance,
								remember_message,
								work_time_unit , 
if(validity_end_date is null or validity_end_date = '0000-00-00' or validity_end_date >= now() , 1 , 0 ) valid

						from HRM_salary_item_types sit
							
								left join BaseInfo bi2 on bi2.InfoID = sit.effect_type and  bi2.TypeID = 64
								left join BaseInfo bi3 on bi3.InfoID = sit.compute_place and  bi3.TypeID = 65
								left join BaseInfo bi4 on bi4.InfoID = sit.salary_compute_type and  bi4.TypeID = 66
								left join BaseInfo bi5 on bi5.InfoID = sit.multiplicand and  bi4.TypeID = 67
						";
							

				$query .= ($where != "") ? " where " . $where : "";
								
			//}
						
	 	return parent::runquery($query, $whereParam);
	 }

	static function Count($where, $whereParam)
	 {
	 	$query = "select count(*)
	 				from HRM_salary_item_types sit	 			       
 			            left join BaseInfo bi2 on bi2.InfoID = sit.effect_type and  bi2.TypeID = 64
 			            left join BaseInfo bi3 on bi3.InfoID = sit.compute_place and  bi3.TypeID = 65
 			            left join BaseInfo bi4 on bi4.InfoID = sit.salary_compute_type and  bi4.TypeID = 66
						left join BaseInfo bi5 on bi5.InfoID = sit.multiplicand and  bi4.TypeID = 67 ";

	
		$query .= ($where != "") ? " where " . $where : "";
		
	 	$temp = parent::runquery($query, $whereParam);
		
	 	return $temp[0][0];
	 }

	function AddSalaryItem()
	{
				
	 	$this->salary_item_type_id = ($this->LastID()+1);
	 	$return = parent::insert("HRM_salary_item_types",$this);

	 	if($return === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->salary_item_type_id;
		$daObj->TableName = "HRM_salary_item_types";
		$daObj->execute();

		return true;
	}

	function EditSalaryItem()
	{ 
	 	$whereParams = array();
	 	$whereParams[":sid"] = $this->salary_item_type_id;

                   
	 	$result = parent::update("HRM_salary_item_types",$this," salary_item_type_id=:sid", $whereParams);

        if(!$result)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->salary_item_type_id;
		$daObj->TableName = "salary_item_types";
		$daObj->execute();

		return true;
	}

	function LastID()
	{
		return PdoDataAccess::GetLastID("HRM_salary_item_types", "salary_item_type_id");
	}

	static function Remove($salary_item_type_id)
	{
		$result = parent::delete("salary_item_types","salary_item_type_id=?", array($salary_item_type_id));
    
		if(!$result)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $salary_item_type_id;
		$daObj->TableName = "salary_item_types";
		$daObj->execute();

		return true;
	}
        
        
        function validate_salary_item_type_id($validity_start_date, $validity_end_date,$date) {
                
            if( DateModules::CompareDate($validity_start_date, $date) != 1 &&
                ( DateModules::CompareDate($validity_end_date, $date) != -1 ||
                    $validity_end_date == null || $validity_end_date == '0000-00-00' ) )
                    return true;
                    else
                    return false;
	}
	
	function GetAllCostCode($where = '', $whereParam = array()) {

        $query = " select CostCode , CostID ,concat_ws('-',b1.BlockDesc,b2.BlockDesc,b3.BlockDesc,b4.BlockDesc,b5.BlockDesc) as CostCodeName
						from
							accountancy.CostCodes cc
									left join accountancy.CostBlocks b1 on(b1.blockid=cc.Level1 and b1.LevelID=1 and (cc.level1 is not null))
									left join accountancy.CostBlocks b2 on(b2.blockid=cc.Level2 and b2.LevelID=2 and (cc.level2 is not null))
									left join accountancy.CostBlocks b3 on(b3.blockid=cc.Level3 and b3.LevelID=3 and (cc.level3 is not null))
									left join accountancy.CostBlocks b4 on(b4.blockid=cc.Level4 and b4.LevelID=4 and (cc.level4 is not null))
									left join accountancy.CostBlocks b5 on(b5.blockid=cc.Level5 and b5.LevelID=5 and (cc.level5 is not null))

				";
        $query .= $where;

        return parent::runquery($query, $whereParam);
    }

}

?>