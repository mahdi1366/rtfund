<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	95.10
//---------------------------

class manage_payments extends PdoDataAccess
{
	
	public $staff_id;
	public $pay_year;
	public $pay_month;
	public $writ_id;
	public $writ_ver;
	public $start_date ;
	public $end_date ;
	public $payment_type ;
	public $message ;
	public $bank_id ;
	public $account_no ;
	public $state ;
	public $_CostCenter ; 

	function __construct( $personid="" , $staff_id="" , $pay_year="" , $pay_month="" )
	{
		
	}

		
	function GetSalaryReceipt($personid="")
	{
	   
		$query = " select  p.staff_id ,p.pay_year , p.pay_month ,p.payment_type , concat(bi.InfoDesc,'  ',p.pay_year ) pay_year_month ,
						   SUM(pi.pay_value + pi.diff_pay_value) pay_sum,
						   SUM(pi.get_value + pi.diff_get_value) get_sum,
						   SUM(pi.pay_value + pi.diff_pay_value - pi.get_value - pi.diff_get_value) pure_pay , w.ouid , bu.UnitName full_unit_title 

												from HRM_payments p inner join HRM_staff s
																					     on p.staff_id = s.staff_id

																				  inner join HRM_persons per
																					     on per.personid = s.personid

																		inner join HRM_payment_items pi
																					  on p.staff_id = pi.staff_id and
																						 p.payment_type = pi.payment_type and
																						 p.pay_year = pi.pay_year and
																						 p.pay_month = pi.pay_month

																		inner join BaseInfo bi
																					  on bi.typeid = 78 and
																					  	 p.pay_month = bi.InfoID

																		left join HRM_writs w
																				on  p.writ_id = w.writ_id and
																					p.writ_ver = w.writ_ver and
																					p.staff_id = w.staff_id
																					
																		inner join BSC_units bu on bu.UnitID  = w.ouid

									where per.personid =:pid
																		
									group by p.pay_year ,
																	p.pay_month,
																	p.staff_id,
																	p.payment_type,
																	per.PersonID
									order by p.pay_year DESC,p.pay_month DESC  ";
		
			
		$whereParam[":pid"] = $personid;
			
		$tmp = PdoDataAccess::runquery($query ,$whereParam);
		

	//	for($i=0 ; $i < count($tmp) ; $i++ ){

			//	$tmp[$i]['full_unit_title'] = '' ;//manage_units::get_full_title($tmp[$i]['ouid']);

		//	}

			return $tmp ; 
	}
        
    function change_payment_state($pdo)
    {   
            
		$whereParams = array();
		$whereParams[":py"] = $this->pay_year;
		$whereParams[":pm"] = $this->pay_month;
		$whereParams[":payt"] = $this->payment_type ; 

		$query = " 
			update  HRM_payments p
				inner join HRM_payment_items pit on  p.staff_id = pit.staff_id and p.pay_year = pit.pay_year and 
					p.pay_month = pit.pay_month and  p.payment_type = pit.payment_type
		set p.state = ".$this->state."
		where p.pay_year = :py and p.pay_month = :pm and 
			  p.payment_type = :payt " ; 

		$result = parent::runquery($query, $whereParams, $pdo) ; 
       
	 	if($result === false )
		{
			 ExceptionHandler::PushException("خطا در تغییر وضعیت پرداخت حقوق");
			return false;
		}
		if(self::AffectedRows($pdo) == 0)
		{
			ExceptionHandler::PushException("محاسبه ایی جهت پرداخت در ماه فوق یافت نشد");
			return false;
		}
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->pay_year;
        $daObj->SubObjectID = $this->pay_month;
		$daObj->description = $this->state == "2" ? "پرداخت حقوق" : "برگشت از پرداخت";
		$daObj->TableName = "HRM_payments";
		$daObj->execute($pdo);

	 	return true;
            
        }
		
	function Add($pdo="",$DB="")
	{		
		
			/* $qry = "select person_type from staff where staff_id =".$this->staff_id ; 
			$res = parent::runquery($qry) ; 
			if($res[0]['person_type'] == 1 || $res[0]['person_type'] == 2 || $res[0]['person_type'] == 3 )
				$DB = "hrms." ; 
			else $DB = "hrms_sherkati.";  */ 
			
			$result = parent::insert($DB."payments", $this,$pdo);
		//echo PdoDataAccess::GetLatestQueryString() ; die();
			if($result === false)
			{ 
				return false;
			}

			$daObj = new DataAudit();
			$daObj->ActionType = DataAudit::Action_add;		
			$daObj->MainObjectID = $this->pay_month ;
			$daObj->SubObjectID = $this->staff_id ; 
			$daObj->TableName = "payments";
			$daObj->execute($pdo);   

			return true  ; 
			
		}		
	
}

?>