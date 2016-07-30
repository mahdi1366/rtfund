<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.10
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
		$query = " select  p.staff_id ,p.pay_year , p.pay_month ,p.payment_type , concat(bi.title,'  ',p.pay_year ) pay_year_month ,
						   SUM(pi.pay_value + pi.diff_pay_value) pay_sum,
						   SUM(pi.get_value + pi.diff_get_value) get_sum,
						   SUM(pi.pay_value + pi.diff_pay_value - pi.get_value - pi.diff_get_value) pure_pay , w.ouid

											from hrmstotal.payments p inner join staff s
																					on p.staff_id = s.staff_id
																				inner join persons per
																					on per.personid = s.personid
																		inner join hrmstotal.payment_items pi
																					  on p.staff_id = pi.staff_id and
																						 p.payment_type = pi.payment_type and
																						 p.pay_year = pi.pay_year and
																						 p.pay_month = pi.pay_month
																		inner join hrmstotal.Basic_Info bi
																					  on bi.typeid = 41 and
																						 p.pay_month = bi.infoid
																		left join writs w
																				on  p.writ_id = w.writ_id and
																					p.writ_ver = w.writ_ver and
																					p.staff_id = w.staff_id

									where per.personid =:pid
																		
									group by p.pay_year ,
																	p.pay_month,
																	p.staff_id,
																	p.payment_type,
																	per.PersonID
									order by p.pay_year DESC,p.pay_month DESC  ";
		
			
		$whereParam[":pid"] = $personid;
			
		$tmp = PdoDataAccess::runquery($query ,$whereParam);
		for($i=0 ; $i < count($tmp) ; $i++ ){

				$tmp[$i]['full_unit_title'] = manage_units::get_full_title($tmp[$i]['ouid']);

			}

			return $tmp ; 
	}
        
        function change_payment_state($pt)
        {   
            
            $whereParams = array();
            $whereParams[":py"] = $this->pay_year;
            $whereParams[":pm"] = $this->pay_month;
            $whereParams[":payt"] = $this->payment_type ; 
            
            //....................... انتقال حقوق.........................
            
            /*$query = " delete from hrmstotal.payment_items 
                            where pay_year = ".$this->pay_year." and pay_month =".$this->pay_month." and payment_type = ".$this->payment_type  ;
            
            $result = parent::runquery($query, array()) ; 
            
	 	if($result === false )
	 		return false;
            
            $query = " delete from hrmstotal.payments 
                            where pay_year = ".$this->pay_year." and pay_month =".$this->pay_month." and payment_type = ".$this->payment_type ;
            
            $result = parent::runquery($query, array()) ; 
            
	 	if($result === false )
	 		return false;
                
            $query = "insert into payment_items (row_id , pay_year , pay_month , staff_id , salary_item_type_id ,  pay_value ,  get_value , param1 ,  param2 , 
                                                 param3 ,  param4 , param5 , param6 ,cost_center_id , payment_type , debt_total_id ,
                                                 debt_ledger_id , debt_tafsili_id , debt_tafsili2_id , cred_total_id , cred_ledger_id , cred_tafsili_id , cred_tafsili2_id , 
                                                 diff_get_value , diff_pay_value , diff_param1 , diff_param2 , diff_param3 , diff_param4 , diff_param5 , diff_param6 , diff_value_coef , 
                                                 param7 , diff_param7 , diff_param1_coef ,diff_param2_coef , diff_param3_coef , diff_param4_coef , diff_param5_coef , diff_param6_coef , 
                                                 diff_param7_coef , param8 ,param9, diff_param8 ,diff_param9 ,diff_param8_coef, diff_param9_coef)( select row_id , pay_year , pay_month , staff_id , salary_item_type_id ,  pay_value ,  get_value , param1 ,  param2 , 
                                                 param3 ,  param4 , param5 , param6 ,cost_center_id , payment_type , debt_total_id ,
                                                 debt_ledger_id , debt_tafsili_id , debt_tafsili2_id , cred_total_id , cred_ledger_id , cred_tafsili_id , cred_tafsili2_id , 
                                                 diff_get_value , diff_pay_value , diff_param1 , diff_param2 , diff_param3 , diff_param4 , diff_param5 , diff_param6 , diff_value_coef , 
                                                 param7 , diff_param7 , diff_param1_coef ,diff_param2_coef , diff_param3_coef , diff_param4_coef , diff_param5_coef , diff_param6_coef , 
                                                 diff_param7_coef , param8 ,param9, diff_param8 ,diff_param9 ,diff_param8_coef, diff_param9_coef from hrms.payment_items 
                                where pay_year = ".$this->pay_year." and pay_month = ".$this->pay_month." and payment_type =".$this->payment_type." )" ;     
                 
            $result = parent::runquery($query, array()) ; 
            
	 	if($result === false )
	 		return false;
                
                $query = "insert into payment_items (row_id , pay_year , pay_month , staff_id , salary_item_type_id ,  pay_value ,  get_value , param1 ,  param2 , 
                                                 param3 ,  param4 , param5 , param6 ,cost_center_id , payment_type , debt_total_id ,
                                                 debt_ledger_id , debt_tafsili_id , debt_tafsili2_id , cred_total_id , cred_ledger_id , cred_tafsili_id , cred_tafsili2_id , 
                                                 diff_get_value , diff_pay_value , diff_param1 , diff_param2 , diff_param3 , diff_param4 , diff_param5 , diff_param6 , diff_value_coef , 
                                                 param7 , diff_param7 , diff_param1_coef ,diff_param2_coef , diff_param3_coef , diff_param4_coef , diff_param5_coef , diff_param6_coef , 
                                                 diff_param7_coef , param8 ,param9, diff_param8 ,diff_param9 ,diff_param8_coef, diff_param9_coef)( select row_id , pay_year , pay_month , staff_id , salary_item_type_id ,  pay_value ,  get_value , param1 ,  param2 , 
                                                 param3 ,  param4 , param5 , param6 ,cost_center_id , payment_type , debt_total_id ,
                                                 debt_ledger_id , debt_tafsili_id , debt_tafsili2_id , cred_total_id , cred_ledger_id , cred_tafsili_id , cred_tafsili2_id , 
                                                 diff_get_value , diff_pay_value , diff_param1 , diff_param2 , diff_param3 , diff_param4 , diff_param5 , diff_param6 , diff_value_coef , 
                                                 param7 , diff_param7 , diff_param1_coef ,diff_param2_coef , diff_param3_coef , diff_param4_coef , diff_param5_coef , diff_param6_coef , 
                                                 diff_param7_coef , param8 ,param9, diff_param8 ,diff_param9 ,diff_param8_coef, diff_param9_coef from hrms_sherkati.payment_items 
                                where pay_year = ".$this->pay_year." and pay_month = ".$this->pay_month." and payment_type =".$this->payment_type." )" ;     
                
            $result = parent::runquery($query, array()) ; 
            
	 	if($result === false )
	 		return false;
		
		 $query = "insert into payment_items (row_id , pay_year , pay_month , staff_id , salary_item_type_id ,  pay_value ,  get_value , param1 ,  param2 , 
                                                 param3 ,  param4 , param5 , param6 ,cost_center_id , payment_type , debt_total_id ,
                                                 debt_ledger_id , debt_tafsili_id , debt_tafsili2_id , cred_total_id , cred_ledger_id , cred_tafsili_id , cred_tafsili2_id , 
                                                 diff_get_value , diff_pay_value , diff_param1 , diff_param2 , diff_param3 , diff_param4 , diff_param5 , diff_param6 , diff_value_coef , 
                                                 param7 , diff_param7 , diff_param1_coef ,diff_param2_coef , diff_param3_coef , diff_param4_coef , diff_param5_coef , diff_param6_coef , 
                                                 diff_param7_coef , param8 ,param9, diff_param8 ,diff_param9 ,diff_param8_coef, diff_param9_coef)( select @i := @i + 1 , pay_year , pay_month , staff_id , salary_item_type_id ,  pay_value ,  get_value , param1 ,  param2 , 
                                                 param3 ,  param4 , param5 , param6 ,cost_center_id , payment_type , debt_total_id ,
                                                 debt_ledger_id , debt_tafsili_id , debt_tafsili2_id , cred_total_id , cred_ledger_id , cred_tafsili_id , cred_tafsili2_id , 
                                                 diff_get_value , diff_pay_value , diff_param1 , diff_param2 , diff_param3 , diff_param4 , diff_param5 , diff_param6 , diff_value_coef , 
                                                 param7 , diff_param7 , diff_param1_coef ,diff_param2_coef , diff_param3_coef , diff_param4_coef , diff_param5_coef , diff_param6_coef , 
                                                 diff_param7_coef , param8 ,param9, diff_param8 ,diff_param9 ,diff_param8_coef, diff_param9_coef from hrmr.payment_items ,(select @i:=0) t 
                                where pay_year = ".$this->pay_year." and pay_month = ".$this->pay_month." and payment_type =".$this->payment_type." )" ;     
                
		$result = parent::runquery($query, array()) ; 
            
	 	if($result === false )
	 		return false;
                
            $query = "insert into 
                                    payments (staff_id , pay_year ,pay_month ,writ_id ,writ_ver ,start_date, end_date ,payment_type, message, bank_id, account_no, state )
                                             ( select staff_id , pay_year ,pay_month ,writ_id ,writ_ver ,start_date, end_date ,payment_type, message, bank_id, account_no, state 
                                                    from hrms.payments 
                                                            where pay_year = ".$this->pay_year." and pay_month = ".$this->pay_month." and payment_type =".$this->payment_type." ) " ;
            $result = parent::runquery($query, array()) ; 

            if($result === false )
                return false;
            
             $query = "insert into 
                                    payments (staff_id , pay_year ,pay_month ,writ_id ,writ_ver ,start_date, end_date ,payment_type, message, bank_id, account_no, state )
                                             ( select staff_id , pay_year ,pay_month ,writ_id ,writ_ver ,start_date, end_date ,payment_type, message, bank_id, account_no, state 
                                                    from hrms_sherkati.payments 
                                                            where pay_year = ".$this->pay_year." and pay_month = ".$this->pay_month." and payment_type =".$this->payment_type." ) " ;
            $result = parent::runquery($query, array()) ; 

            if($result === false )
                return false; 
			
			$query = "insert into 
                                    payments (staff_id , pay_year ,pay_month ,writ_id ,writ_ver ,start_date, end_date ,payment_type, message, bank_id, account_no, state )
                                             ( select staff_id , pay_year ,pay_month ,writ_id ,writ_ver ,start_date, end_date ,payment_type, message, bank_id, account_no, state 
                                                    from hrmr.payments 
                                                            where pay_year = ".$this->pay_year." and pay_month = ".$this->pay_month." and payment_type =".$this->payment_type." ) " ;
            $result = parent::runquery($query, array()) ; 

            if($result === false )
                return false;
				*/
//......................................................
            if($pt == 102 )
                $wherePT= " 1,2,3 " ; 
           else if($pt == 100)
               $wherePT = " 1,2,3,5 " ; 
            else  
                $wherePT = $pt ; 
					
            
            $query = " update  payments p inner join staff s on p.staff_id = s.staff_id
										  inner join payment_items pit on  p.staff_id = pit.staff_id and p.pay_year = pit.pay_year and 
																		   p.pay_month = pit.pay_month and  p.payment_type = pit.payment_type
                                  set p.state = ".$this->state."
                                                 where p.pay_year = :py and p.pay_month = :pm and 
													   p.payment_type = :payt and s.person_type in ( $wherePT ) and pit.cost_center_id in (".$this->_CostCenter.") " ; 
         
            $result = parent::runquery($query, $whereParams) ; 
       
	 	if($result === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->pay_month;
                $daObj->SubObjectID = $this->state;
		$daObj->TableName = "payments";
		$daObj->execute();

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