<?php

//-----------------------------
//	Programmer	: b.Mahdipour
//	Date		: 91.06
//-----------------------------

require_once '../../../header.inc.php';
require_once '../class/pay_get_lists.class.php';
require_once '../class/mission_list_items.class.php';
require_once '../class/pay_get_list_items.class.php';
require_once '../class/group_pay_get_log.class.php';
require_once '../../person_org_docs/subtracts.class.php';
require_once(inc_response);
require_once 'phpExcelReader.php';

ini_set("display_errors","on") ;

$task = isset($_POST ["task"]) ? $_POST ["task"] : (isset($_GET ["task"]) ? $_GET ["task"] : "");

switch ($task) {
	
    case "InsertData":
          InsertData();
	
}


function InsertData(){
	       
    $data = new Spreadsheet_Excel_Reader();
    $data->setOutputEncoding('utf-8');
    $data->setRowColOffset(0);
    $data->read($_FILES["attach"]["tmp_name"]);
         
    $FileType= $_POST["FileType"];
		
	$obj = new manage_pay_get_lists() ; 
	$MissionObj = new manage_mission_list_items(); 
	$PGIObj = new manage_pay_get_list_items(); 
	$log_obj = new manage_group_pay_get_log();		
	
	$success_count = 0;
	$unsuccess_count = 0;
	$costId = "" ; 
	        
        if($FileType == EXTRA_WORK_LIST )
        {
	    
	 	    
            for ($i = 1; $i < $data->sheets[0]['numRows']; $i++) {
                    //for ($j = 0; $j < $data->sheets[0]['numCols']; $j++)       
                    //$data->sheets[0]['cells'][$i][$j]; 
		 
		    if(!isset($data->sheets[0]['cells'][$i][0]) && !isset($data->sheets[0]['cells'][$i][1]))
			break ; 
		 
		     $query = " select p.pfname , p.plname , s.person_type from staff s inner join persons p on s.personid = p.personid
					where staff_id =" .$data->sheets[0]['cells'][$i][1] ;              
		     $result = PdoDataAccess::runquery($query) ;
		     
		    if( $costId != $data->sheets[0]['cells'][$i][0] ){
			$obj->list_id = null ; 
                        $obj->cost_center_id = $data->sheets[0]['cells'][$i][0] ; 
                        $obj->list_date = DateModules::Now() ;                    
                        $obj->doc_state = 1 ;
                        $obj->list_type = EXTRA_WORK_LIST ;
			
			if(  $obj->AddList()  === false )
			{
			    $log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][1], $result[0]["pfname"]." ".$result[0]["plname"] ," کد مرکز هزینه نامعتبر است.");
			    $unsuccess_count++; 

			    continue ; 
			}			
			$costId = $obj->cost_center_id ;                                                
                    }
		
			if(count($result) > 0 ){   
				$pt = $result[0]["person_type"];
				if($pt == 3)
				$salaryItemId = 152 ;
				elseif($pt == 2)
				$salaryItemId = 39 ;
				elseif($pt == 5)
				$salaryItemId = 639 ;

				$PGIObj->staff_id = $data->sheets[0]['cells'][$i][1] ;
				$PGIObj->salary_item_type_id = $salaryItemId ;                 
				$PGIObj->approved_amount = $data->sheets[0]['cells'][$i][2] ;
				$PGIObj->comments = $data->sheets[0]['cells'][$i][3] ;               

				$PGIObj->list_id = $obj->GetLastID("pay_get_lists", "list_id" , " list_type = 1 and cost_center_id =".$obj->cost_center_id ) ; 
				$PGIObj->list_row_no = $PGIObj->GetLastID("pay_get_list_items","list_row_no"," list_id =".$PGIObj->list_id ) + 1 ; 

				if($PGIObj->Add() == false ){                                                              
					$log_obj->make_unsuccess_rows($PGIObj->staff_id);
					$unsuccess_count++;                     
				}
		    }
		    else {
				$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][1] ," " , "شماره شناسایی نامعتبر است.");
				$unsuccess_count++;  
			 }
                    
             }
	     
	    $log_obj->finalize();
            $st = preg_replace('/\r\n/',"",$log_obj->make_result()) ; 
	    echo "{success:true,data:'" . $st . "'}";
	    die(); 
            
        }
        elseif($FileType == MISSION_LIST )
        {
                                   
           for ($i = 1; $i < $data->sheets[0]['numRows']; $i++) {
              
                //for ($j = 0; $j < $data->sheets[0]['numCols']; $j++)       
                //$data->sheets[0]['cells'][$i][$j]; 
	       
	       $query = " select p.pfname , p.plname , s.person_type from staff s inner join persons p on s.personid = p.personid
					where staff_id =" .$data->sheets[0]['cells'][$i][1] ;              
               $result = PdoDataAccess::runquery($query) ;
	       
               if( $costId != $data->sheets[0]['cells'][$i][0] ){
		    $obj->list_id = null ; 
                    $obj->cost_center_id = $data->sheets[0]['cells'][$i][0] ;                    
                    $obj->list_date = DateModules::Now() ;                    
                    $obj->doc_state = 1 ;
                    $obj->list_type = MISSION_LIST ;
		   		   
		    if( $obj->AddList() === false )
		    {
			$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][1], $result[0]["pfname"]." ".$result[0]["plname"] ," کد مرکز هزینه نامعتبر است.");
			$unsuccess_count++; 
			
			continue ; 
		    } 
		    
		    $costId = $obj->cost_center_id ;
                  
                    }             
               
	    if(count($result) > 0 ){
		
               $pt = $result[0]["person_type"];
               if($pt == 1)
                  $salaryItemId = 42 ;
               elseif($pt == 2)
                  $salaryItemId = 43 ;
	       elseif($pt == 3)
                  $salaryItemId = 10315 ;
               elseif($pt == 5)
                  $salaryItemId = 643 ;
                 
                $MissionObj->staff_id = $data->sheets[0]['cells'][$i][1] ;
                $MissionObj->doc_no = $data->sheets[0]['cells'][$i][2] ;
                $MissionObj->doc_date =  DateModules::shamsi_to_miladi($data->sheets[0]['cells'][$i][3]) ;                                 
                $MissionObj->from_date = DateModules::shamsi_to_miladi($data->sheets[0]['cells'][$i][4]) ;  
                $MissionObj->to_date = DateModules::shamsi_to_miladi($data->sheets[0]['cells'][$i][5]) ;  
                $MissionObj->duration = $data->sheets[0]['cells'][$i][6] ;  
                $MissionObj->travel_cost = (isset($data->sheets[0]['cells'][$i][7])) ? $data->sheets[0]['cells'][$i][7] : "" ;  
                $MissionObj->destination = $data->sheets[0]['cells'][$i][8] ;  
                $MissionObj->using_facilities = (isset($data->sheets[0]['cells'][$i][9])) ? $data->sheets[0]['cells'][$i][9] : "" ; 
           //     $MissionObj->report_summary =  (isset($data->sheets[0]['cells'][$i][11])) ? $data->sheets[0]['cells'][$i][11] : "" ;
                $MissionObj->comments = (isset($data->sheets[0]['cells'][$i][12])) ? $data->sheets[0]['cells'][$i][12] : "" ;
                $MissionObj->region_coef = (isset($data->sheets[0]['cells'][$i][10])) ? $data->sheets[0]['cells'][$i][10] : "" ;
                $MissionObj->salary_item_type_id = $salaryItemId ;
                              
                $MissionObj->list_id = $obj->GetLastID("pay_get_lists", "list_id", " list_type = 9 and cost_center_id =".$obj->cost_center_id) ; 
                $MissionObj->list_row_no = $MissionObj->GetLastID("mission_list_items","list_row_no"," list_id =".$MissionObj->list_id ) + 1 ; 
         
                if($MissionObj->Add() == false ){                                                              
                        $log_obj->make_unsuccess_rows($MissionObj->staff_id , $result[0]["pfname"]." ".$result[0]["plname"] , "اطلاعات مربوط  به این فرد معتبر نمی باشد.");
			$unsuccess_count++;                     

		    }
		
		}
		else {
		     $log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][1] ," " , "شماره شناسایی نامعتبر است.");
		     $unsuccess_count++;  
			 }
                   
			 }
                        
            $log_obj->finalize();
            $st = preg_replace('/\r\n/',"",$log_obj->make_result()) ; 
	    echo "{success:true,data:'" . $st . "'}";
	    die(); 
		
        } 
		// مربوط به وام و کسور
		elseif($FileType == 11 )
		{
						
			//.... محاسبه باقیمانده وامها ..........
			$Arr = preg_split('/[\/]/', DateModules::shNow()); 	
			$LYear = $Arr[0] ;
			$LMonth = $Arr[1]; 		
			
			manage_subtracts::GetRemainder("","","", true ,$LMonth, $LYear); 	
			//......................................
		    $pdo = PdoDataAccess::getPdoObject();
			$pdo->beginTransaction();
			
			$DB = "";	

			//echo  $data->sheets[0]['numRows'].'----'			  ; die() ; 
							
            for ($i = 1; $i < $data->sheets[0]['numRows']; $i++) {
	
				
				$subObj = new manage_subtracts(); 
				$subFlowObj = new manage_subtract_flows(); 
				
				if(!isset($data->sheets[0]['cells'][$i][1])) {
					
					$log_obj->make_unsuccess_rows('-','-'		, 'ردیف '.($i+1).' معتبر نمی باشد.');
						$unsuccess_count++; 
						continue ;
					
					
					}  
					
				// آیا این قلم وجود دارد ؟......................................
				$qry = " select salary_item_type_id , available_for
							from salary_item_types where salary_item_type_id=".$data->sheets[0]['cells'][$i][1] ; 
				$resItem = PdoDataAccess::runquery($qry); 
				//..............................................................
			
				if( isset($resItem[0]['salary_item_type_id']) && $resItem[0]['salary_item_type_id'] > 0 ){
					
					if(empty($data->sheets[0]['cells'][$i][0]))
					{
						$log_obj->make_unsuccess_rows('-', $data->sheets[0]['cells'][$i][1] ,'لطفا شماره شناسایی ردیف '
								.($i+1).'  را وارد نمایید.');
						$unsuccess_count++; 
						continue ;							
					}
					// قلم مربوط به وام نبوده است ...............................
					if($data->sheets[0]['cells'][$i][2] == 1 && $resItem[0]['available_for'] != 1 ) 
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], $data->sheets[0]['cells'][$i][1] ," قلم مربوط به وام نمی باشد. ");
						$unsuccess_count++; 

						continue ; 
						
					} 
					if( $data->sheets[0]['cells'][$i][2] == 2 && !( $resItem[0]['available_for'] == 4 || $resItem[0]['available_for'] == 5 )) 
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], $data->sheets[0]['cells'][$i][1] ," قلم مربوط به کسور نمی باشد. ");
						$unsuccess_count++; 

						continue ;					
					}
					if( $data->sheets[0]['cells'][$i][2] == 1 && !empty($data->sheets[0]['cells'][$i][6]) && $data->sheets[0]['cells'][$i][6] !=0 &&
							 !empty($data->sheets[0]['cells'][$i][5]) && $data->sheets[0]['cells'][$i][5] !=0 &&
						$data->sheets[0]['cells'][$i][6] > $data->sheets[0]['cells'][$i][5] )
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], $data->sheets[0]['cells'][$i][1] ,'قسط وام مورد نظر از مبلغ اوليه آن بزرگتر است.');
						$unsuccess_count++; 
						continue ;	
						
					}
				
					if(!empty($data->sheets[0]['cells'][$i][8]) && ( DateModules::GetDateFormat($data->sheets[0]['cells'][$i][7]) > DateModules::GetDateFormat($data->sheets[0]['cells'][$i][8]) ) )
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], $data->sheets[0]['cells'][$i][1] ,'تاريخ پايان بايد بزرگتر و يا مساوي تاريخ شروع باشد.');
						$unsuccess_count++; 
						continue ;							
					}
					
					if(empty($data->sheets[0]['cells'][$i][3]))
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], $data->sheets[0]['cells'][$i][1] ,'نوع وام/کسور مشخص نشده است.');
						$unsuccess_count++; 
						continue ;							
					}
					
					if(!empty($data->sheets[0]['cells'][$i][4])) 
					{
						$qry = "select bank_id from banks where branch_code=".$data->sheets[0]['cells'][$i][4] ; 
						$resbank = PdoDataAccess::runquery($qry) ; 
											
						if( count($resbank) == 0 ) 
						{
							$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], $data->sheets[0]['cells'][$i][1] ,'کد بانک معتبر نمی باشد.');
							$unsuccess_count++; 
							continue ;	
						}
							
					}
					
					
					
					$qry = " select staff_id,PersonID from staff where staff_id =".$data->sheets[0]['cells'][$i][0]; 
					$resStaff = PdoDataAccess::runquery($qry) ; 
					
					if( count($resStaff) == 0 )
					{
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], $data->sheets[0]['cells'][$i][1] ,' شماره شناسایی معتبر نمی باشد.');
						$unsuccess_count++; 
						continue ;	
						
					}
					
					//..............................................................................
					
					$Arr2 = preg_split('/[\/]/', DateModules::GetDateFormat($data->sheets[0]['cells'][$i][7])); 	
					$sfd = (int) $Arr2[0]."/". (int) $Arr2[1]	."/". (int) $Arr2[2]	; 
					
					if(!empty($data->sheets[0]['cells'][$i][8])) { 
						$Arr3 = preg_split('/[\/]/', DateModules::GetDateFormat($data->sheets[0]['cells'][$i][8])); 	
						$efd = (int) $Arr3[0]."/". (int) $Arr3[1]	."/". (int) $Arr3[2]	; 
					}
					//...........................................................................								
					$Arr = preg_split('/[\/]/', DateModules::GetDateFormat($data->sheets[0]['cells'][$i][7])); 		

					$sdate = $edate = '' ;
					$start_day = "" ; 				
					$start_month = (int) $Arr[1] ;   	
					
					if($start_month < 7 ) $day =  31 ; 
					
					elseif ($start_month < 12 ) $day =  30 ; 
					
					elseif ($start_month == 12 ) $day =  29 ; 
					
					$sd = (int) $Arr[0]."/". (int) $Arr[1]."/01" ; 
					$ed = (int) $Arr[0]."/". (int) $Arr[1]."/".$day ; 
					
					$sdate = DateModules::shamsi_to_miladi($sd);												
					$edate = DateModules::shamsi_to_miladi($ed);	

					$PID = $resStaff[0]['PersonID'] ; 	
				//ثبت وام یا کسور جدید..................................................
				if($data->sheets[0]['cells'][$i][3] == 1 ) 
				{			
						
						$qry = " select subtract_id, remainder, instalment  
									from person_subtracts 
										where salary_item_type_id = ".$data->sheets[0]['cells'][$i][1]." and
											  PersonID =".$PID." AND IsFinished = 0 and 
											  start_date <= '".str_replace("/","-",$sdate)."' and 
											  ( end_date >= '".str_replace("/","-",$edate)."' OR end_date IS NULL OR end_date = '0000-00-00' ) ";
											  
						
						$res = PdoDataAccess::runquery($qry); 
						
						if( count($res) > 0  && $res[0]['subtract_id'] > 0 ) 
						{
							
							$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ,"این /کسور قبلا در سیستم ثبت گردیده است.");
							$unsuccess_count++; 

							continue ;
						}						
						
						//.......................................................
											
						$subObj->PersonID = $PID ; 
						$subObj->subtract_type = ($data->sheets[0]['cells'][$i][2] == 1 ) ? LOAN : FIX_FRACTION ; 
						$subObj->bank_id = (!empty($data->sheets[0]['cells'][$i][4])) ?  $resbank[0]['bank_id'] : "" ;	
						$subObj->first_value = (!empty($data->sheets[0]['cells'][$i][5])) ? $data->sheets[0]['cells'][$i][5] : 0 ;
						$subObj->instalment = (!empty($data->sheets[0]['cells'][$i][6])) ? $data->sheets[0]['cells'][$i][6] : 0 ;
						$subObj->remainder = (!empty($data->sheets[0]['cells'][$i][5])) ? $data->sheets[0]['cells'][$i][5] : 0 ; 	
						$subObj->start_date = DateModules::shamsi_to_miladi($sfd); ; 
						$subObj->end_date = ( !empty($data->sheets[0]['cells'][$i][8]) ) ? DateModules::shamsi_to_miladi($efd) : "" ;
						$subObj->loan_no = (!empty($data->sheets[0]['cells'][$i][9])) ? $data->sheets[0]['cells'][$i][9] : "" ; 
						$subObj->contract_no = (!empty($data->sheets[0]['cells'][$i][10])) ? $data->sheets[0]['cells'][$i][10] : "" ;					
						$subObj->salary_item_type_id = $data->sheets[0]['cells'][$i][1] ;						
						$subObj->reg_date = DateModules::NowDateTime(); 
						//Isfinished = 0 ; 
					if(  $subObj->Add() === false )
					{ 
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," خطا ");
						$unsuccess_count++; 

						continue ; 
					}	

				}				
				// ثبت گردش ...................................................				
				if($data->sheets[0]['cells'][$i][3] == 2 ) 
				{
					
					$qry = " select psf.subtract_id 
									from person_subtracts  ps inner join person_subtract_flows psf 
																			on  ps.subtract_id = psf.subtract_id
																			
										where ps.salary_item_type_id = ".$data->sheets[0]['cells'][$i][1]." and
											  ps.PersonID =".$PID." and 
											  psf.flow_date >= '".str_replace("/","-",$sdate)."' and 
											  psf.flow_date <= '".str_replace("/","-",$edate)."'" ; 
						
					$res = PdoDataAccess::runquery($qry); 
	
					if( count($res) > 0 && $res[0]['subtract_id'] > 0 ) 
					{
						//echo PdoDataAccess::GetLatestQueryString() ; die();
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], "-" ," این گردش قبلا در سیستم ثبت گردیده است.");
						$unsuccess_count++; 

						continue ;
					}							
					//.................................................................
					 				 
					$qry = " select ps.subtract_id, ts.remainder, ps.instalment  
									from person_subtracts ps  left join tmp_SubtractRemainders ts 
									                               on ps.subtract_id = ts.subtract_id
																   
										where ps.salary_item_type_id = ".$data->sheets[0]['cells'][$i][1]." and
											  ps.PersonID =".$PID." AND ps.IsFinished = 0  AND ps.start_date <= '".str_replace("/","-",$sdate)."' AND  
											( ps.end_date >= '".str_replace("/","-",$edate)."' OR ps.end_date IS NULL OR ps.end_date = '0000-00-00' ) ";
					
					$res = PdoDataAccess::runquery($qry) ; 
					
		//	echo PdoDataAccess::GetLatestQueryString() ; 
					
					if( count($res) > 0  && $res[0]['subtract_id'] > 0 ) 
					{
						$new_remainder = $new_instalment = 0 ;
						if($data->sheets[0]['cells'][$i][2] == 1 ) {
							$subFlowObj->subtract_id = $res[0]['subtract_id']; 						
							$subFlowObj->flow_type = REGISTER_NEW_FLOW_TYPE ; 
							$subFlowObj->flow_date = DateModules::NowDateTime(); 
							$subFlowObj->flow_coaf = (!empty($data->sheets[0]['cells'][$i][5]) && ($data->sheets[0]['cells'][$i][5] * 1  > $res[0]['remainder'] * 1  ) ) ? -1 : 1 ; 	 
							$subFlowObj->amount = (!empty($data->sheets[0]['cells'][$i][5]) && ($data->sheets[0]['cells'][$i][5] * 1 > $res[0]['remainder'] * 1 ) ) ? ($data->sheets[0]['cells'][$i][5]*1 - $res[0]['remainder']*1 ) : ( $res[0]['remainder']*1 - $data->sheets[0]['cells'][$i][5]*1 ) ; 						
							$subFlowObj->newRemainder = (!empty($data->sheets[0]['cells'][$i][5]) ? $data->sheets[0]['cells'][$i][5] : 0 ) ; 
							$subFlowObj->comments = "گردش دستی" ;   				

							if($subFlowObj->Add() == false )
							{                                                              

								$log_obj->make_unsuccess_rows($subObj->staff_id);
								$unsuccess_count++;    
								continue ; 
							}
							else 
							{
								$new_remainder = (!empty($data->sheets[0]['cells'][$i][5])) ? $data->sheets[0]['cells'][$i][5] : 0 ;
								$new_instalment = (!empty($data->sheets[0]['cells'][$i][6])) ? $data->sheets[0]['cells'][$i][6] : 0 ;

								$qry = " update person_subtracts 
											set		remainder = ".$new_remainder.",
													instalment = ".$new_instalment.",
													reg_date = '".DateModules::NowDateTime()."'												
										where subtract_id =".$subFlowObj->subtract_id ; 

								PdoDataAccess::runquery($qry);
							} 
						}
						else 
						{
								//echo "*****"; die() ; 
							$new_instalment = (!empty($data->sheets[0]['cells'][$i][6])) ? $data->sheets[0]['cells'][$i][6] : 0 ;

							$qry = " update person_subtracts 
										set		
												instalment = ".$new_instalment.",
												reg_date = '".DateModules::NowDateTime()."'												
									where subtract_id =".$res[0]['subtract_id'] ; 

							PdoDataAccess::runquery($qry);
							
						}

					}
					else 
					{											 
						$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], $data->sheets[0]['cells'][$i][1] ,"این وام / کسور ثبت سیستم نشده است.");
						$unsuccess_count++;    
						continue ; 						
					}	
										
				}	
				
			}
			else 
			{
				$log_obj->make_unsuccess_rows($data->sheets[0]['cells'][$i][0], $data->sheets[0]['cells'][$i][1] ,"کد قلم معتبر نمی باشد.");
				$unsuccess_count++; 
				continue ;								
				
			}
                
			}
	     
			$log_obj->finalize();
			$st = preg_replace('/\r\n/',"",$log_obj->make_result()) ; 
			
			if($unsuccess_count > 0)
			{
				
				$pdo->rollBack();
			}
			else {
				$pdo->commit(); 			
			}
			
			echo "{success:true,data:'" . $st . "'}";
			die(); 
			
		} 
		
}
?>