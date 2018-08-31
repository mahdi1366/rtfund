<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	92.9
//---------------------------

class manage_payment_items extends PdoDataAccess
{
	
	public $pay_year;	
	public $pay_month;
	public $staff_id;
	public $salary_item_type_id;
	public $pay_value ;
	public $get_value ;
	public $param1 ;
	public $param2 ;
	public $param3 ;
	public $param4 ;
	public $param5 ;
	public $param6 ;
	public $param7 ;
	public $param8 ;
	public $param9 ;
	public $cost_center_id ;
	public $payment_type ;
	public $diff_get_value ;
	public $diff_pay_value ;
	public $diff_param1 ;
	public $diff_param2 ;
	public $diff_param3 ;
	public $diff_param4 ;
	public $diff_param5 ;
	public $diff_param6 ; 
	public $diff_param7 ;
	public $diff_param8 ;
	public $diff_param9 ;
	public $diff_value_coef ;
		

	function __construct( $personid="" , $staff_id="" , $pay_year="" , $pay_month="" )
	{
		
	}

	function Add($pdo="",$DB="")
	{
		
		$result = parent::insert("HRM_payment_items", $this,$pdo);

		if($result === false)
		{ 
			return false;
		}

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;		
		$daObj->MainObjectID = $this->staff_id;
		$daObj->TableName = "HRM_payment_items";
		$daObj->execute($pdo);   

		return true  ; 

	}
	
	function Edit($pdo="",$DB="") {
	
		$result = parent::update("HRM_payment_items", $this,
		      " pay_year=".$this->pay_year." and pay_month=".$this->pay_month." and 														   staff_id=".$this->staff_id." and 
		           payment_type =".$this->payment_type ,array(),$pdo );
		if ($result === false)
		return false;	

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->staff_id; 
		$daObj->TableName = "payment_items";
		$daObj->execute($pdo);

		return true;
	}	
	
}

?>