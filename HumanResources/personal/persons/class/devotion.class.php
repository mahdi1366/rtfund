<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	88.06.17
//---------------------------

class manage_person_devotion extends PdoDataAccess
{
	public $PersonID;
	public $devotion_row;
	public $devotion_type;
	public $personel_relation;
	public $enlisted;
    public $amount;
    public $from_date;
    public $to_date;
	public $continous;
	public $war_place;
	public $letter_no;
	public $letter_date;
	public $comments;
	public $duration_include_paied_retired_fraction;

	public function  __construct() {
		$this->DT_enlisted = DataMember::CreateDMA(DataMember::DT_INT, 0);
		$this->DT_continous = DataMember::CreateDMA(DataMember::DT_INT, 0);
		$this->DT_letter_date = DataMember::CreateDMA(DataMember::DT_DATE);
	}

	function OnBeforeInsert()
	{

		 if( ( $this->devotion_type == FIGHTING_DEVOTION ||
			   $this->devotion_type == FREEDOM_DEVOTION ||
			   $this->devotion_type == WAR_REGION_WORK_DEVOTION ||
			   $this->devotion_type == WAR_REGION_TEACHING_DEVOTION ) &&
			 (empty($this->from_date) || $this->from_date == '0000-00-00' || empty($this->to_date) || $this->to_date == '0000-00-00'  )
			)
		 { 
			 parent::PushException(START_AND_END_DATE_MUST_NOT_BE_NULL);
             return false;
		 }
		 
		 if( $this->devotion_type == FIGHTING_DEVOTION ||
			 $this->devotion_type == FREEDOM_DEVOTION ||
			 $this->devotion_type == WAR_REGION_WORK_DEVOTION ||
			 $this->devotion_type == WAR_REGION_TEACHING_DEVOTION
			 )
		 {
			$this->amount = DateModules::getDateDiff($this->to_date , $this->from_date ); 

		 }

		 if($this->devotion_type == DEVOTION_TYPE_WOUNDED && (empty($this->from_date) || $this->from_date == '0000-00-00' ))
		 {

			 parent::PushException(START_DATE_MUST_NOT_BE_NULL);
             return false;
			 
		 }

			  //در صورتی که فرد رزمنده و جانباز با همدیگر باشد امکان ثبت فیلد
			// "مدت قابل قبول بازنشستگی که کسور آن  پرداخت شده "باید وجود داشته باشد.
			//در صورتی که فرد رزمنده و جانباز با همدیگر باشد امکان ثبت فیلد
			// "مدت قابل قبول بازنشستگی که کسور آن  پرداخت شده "باید وجود داشته باشد.
			if ($this->devotion_type == FIGHTING_DEVOTION) {
				
				$query = "	select *
									from person_devotions pd
											where pd.devotion_type = ".SACRIFICE_DEVOTION." and  pd.PersonID = ".$this->PersonID ;

				$temp = parent::runquery($query);

				if( count($temp) == 0 && $this->duration_include_paied_retired_fraction > 0 )
				{
					 parent::PushException(ER_WITHOUT_SACRIFIC_DEVOTION_HISTORY);
					 return false;
				}
				
				 // از زمان رزمندگی فرد نباید بیشتر باشد
				if($this->amount < $this->duration_include_paied_retired_fraction ){
					parent::PushException(ER_PAIED_DURATION_OVER_DEVOTION_DURATION);
					return false;
				}
				
				  //با خدمت در دستگاه نباید همپوشانی زمانی داشته باشد.

				$query ="select * from writs w
										INNER JOIN staff s
											  ON (w.staff_id = s.staff_id)
						 where w.execute_date >='".$this->from_date."' and w.execute_date <='".$this->to_date."' and s.PersonID = ".$this->PersonID ;

				$tmp = parent::runquery($query);

				if( count($tmp) > 0 )
				{
					 parent::PushException(ER_PERSON_DEVOTIONS_AND_WRITS_COINCIDENT);
					 return false;
				}
				
				 // با سربازی نباید تداخل داشته باشد.
                                //---------- این شرط بنابه درخواست آقای دلکلاله در تاریخ 28 خرداد 91 حذف گردید---------------------------

			/*	if($this->enlisted != 0 ) {
                                    
                                    $query =" select *
                                                                            from persons p
                                                                                            where ( p.military_from_date >='".$this->from_date."' and p.military_to_date <='".$this->to_date."' ) and
                                                                                                            p.PersonID =".$this->PersonID ;
    /*
    *  or
                                                                                                            p.military_from_date <= '".$this->from_date."' and  p.military_to_date >= '".$this->to_date."' or
                                                                                                            p.military_from_date >='".$this->from_date."' and p.military_to_date >= '".$this->to_date."' or
                                                                                                            p.military_from_date <= '".$this->from_date."' and p.military_to_date >= '".$this->to_date."' 
    *
    */
      /*                              $res = parent::runquery($query);

                                    if(count($res) >  0)
                                    {
                                            parent::PushException(ER_PERSON_DEVOTIONS_AND_MILITARY_SERVICE_COINCIDENT);
                                            return false;
                                    }
                                }*/
                              

			}	

		 return true ;

	 }
	 
	function OnAfterInsert()
	{
	

$query1 = "	select PersonID ,  devotion_type , sum(amount) amount
			from (
				select personid ,devotion_type , from_date ,g2j(to_date) ,  enlisted ,
					   sum(if(to_date > '1988-8-21' ,DATEDIFF('1988-8-21',from_date), DATEDIFF(to_date,from_date))) amount
				from hrmstotal.person_devotions

				where devotion_type = 1 AND  from_date < '1988-8-21' and personid = ".$this->PersonID."  AND enlisted = 1  

				) t1
			group by PersonID

			UNION ALL

			select  PersonID ,
					devotion_type,
					if((devotion_type=3 ) ,MAX(amount),SUM(amount)) amount

			from hrmstotal.person_devotions
			where  devotion_type in (3) and personid = ".$this->PersonID."

			group by personid , devotion_type

			UNION ALL

			select  PersonID ,
					devotion_type,
					if(( devotion_type=2 ) ,MAX(amount),SUM(amount)) amount

			from hrmstotal.person_devotions
			where if((devotion_type != 5 and devotion_type!=3),(from_date is not null AND from_date != '0000-00-00'),(1=1)) and devotion_type in (2)
				  and personid = ".$this->PersonID."

			group by personid , devotion_type
		"; 
		
		
		
		/*$query1 = "  select personid ,
                            devotion_type,
                            if((devotion_type=3 or devotion_type=2 ) ,MAX(amount),SUM(amount)) amount

                        from hrmstotal.person_devotions
					 where if((devotion_type != 5 and devotion_type!=3),(from_date is not null AND from_date != '0000-00-00'),(1=1)) and devotion_type in (2,3,1) 
								and personid = ".$this->PersonID."
									
					 group by personid , devotion_type " ;*/

	    $temp1 = PdoDataAccess::runquery($query1);
	
		for ($i=0 ; $i < count($temp1) ; $i++){
			$base=0 ;
			$type = 0;
						
			//.......................................آزادگی...........................
			if($temp1[$i]['devotion_type'] == 2 && (($temp1[$i]['amount'])/365 ) < 3  )  {
				$base = 1 ;
				$type = 3 ; 
			}			
			elseif($temp1[$i]['devotion_type'] == 2 && (($temp1[$i]['amount'])/365 ) >= 3  && (($temp1[$i]['amount'])/365 ) < 6 )  {
				$base = 2 ; 
				$type = 3 ;
			}	
			elseif($temp1[$i]['devotion_type'] == 2 && (($temp1[$i]['amount'])/365 ) >= 6  )  {
				$base = 3 ; 
				$type = 3 ;
			}		
			
			//.......................................رزمندگی...........................
			if( $temp1[$i]['devotion_type'] == 1 && (($temp1[$i]['amount'])/30 ) >= 6 && (($temp1[$i]['amount'])/30 ) < 9  ){
		
				$qry2 = " select personid ,devotion_type , from_date ,g2j(to_date) ,  enlisted ,
											sum(if(to_date > '1988-8-21' ,DATEDIFF('1988-8-21',from_date), DATEDIFF(to_date,from_date))) amount
											from hrmstotal.person_devotions

											where devotion_type = 1 AND enlisted = 1 AND  personid = ".$temp1[$i]['PersonID']." AND from_date < '1988-8-21' "; 

				$tmp2 = PdoDataAccess::runquery($qry2);

				/*for($j=0;$j< count($tmp2); $j++){*/

					if((($tmp2[0]['amount'])/30 ) >= 6 )
					{
						$base = 1 ; $type = 5 ; 					 
						//break ; 
					}			

				/*}			*/
		
			}					
		elseif($temp1[$i]['devotion_type'] == 1 && (($temp1[$i]['amount'])/365 ) < 3 && (($temp1[$i]['amount'])/30 ) >= 9  ){

				$base = 1 ; $type = 5 ; 

				}
        
        elseif($temp1[$i]['devotion_type'] == 1 && (($temp1[$i]['amount'])/365 ) >= 3  && (($temp1[$i]['amount'])/365 ) < 6  )  {
	        
            $base = 2 ; $type = 5 ; 
            
		}
        
        elseif($temp1[$i]['devotion_type'] == 1 && (($temp1[$i]['amount'])/365 ) >= 6  )   { 
	
            $base = 3 ; $type = 5 ; 
            
		} 
        //......................................جانبازی................................	
			
		if( $temp1[$i]['devotion_type'] == 3 && ($temp1[$i]['amount']) <= 34 )   {
			$base = 1 ; $type = 4 ;            
		}			
		elseif($temp1[$i]['devotion_type'] == 3 && ($temp1[$i]['amount']) >=35 && $temp1[$i]['amount'] <= 69 )   {
			
		$base = 2 ; $type = 4 ;            

		}
		elseif($temp1[$i]['devotion_type'] == 3 && ($temp1[$i]['amount'])>= 70 )   {
			
		$base = 3 ; $type = 4 ;            

		}							
		//......................................................................
		  			
		$qry = " select personid ,BaseType , BaseValue   
					from bases 
						where PersonID = ".$temp1[$i]['PersonID']." and BaseType = ".$type." and BaseStatus = 'NORMAL' "  ; 
		$tmp = PdoDataAccess::runquery($qry);
						
		if(count($tmp)> 0 && $tmp[0]['BaseValue'] == $base ) {
			continue;
		}
		elseif(count($tmp)> 0 && $tmp[0]['BaseValue'] != $base ) {
			// قبلی را DELETED و رکورد جدید را اضافه بکن..................
			$qry = " update bases 
								set BaseStatus = 'DELETED'
							where PersonID = ".$tmp[0]['personid']." and BaseType = ".$tmp[0]['BaseType']; 
			PdoDataAccess::runquery($qry);
			if($base > 0 ){
			$qry = " insert into hrmstotal.bases(PersonID ,BaseType ,BaseValue , RegDate , ExecuteDate ,BaseMode , ExtraInfo ,BaseStatus ) values
				( ".$temp1[$i]['PersonID'].", ".$type." , ".$base." , now() ,'2013-02-19' ,  'SYSTEM' ,0 , 'NORMAL'
						)"; 
			PdoDataAccess::runquery($qry);								
			}
		}			
		elseif(count($tmp)== 0 && $base!=0 && $type != 0){
			//.............رکورد جدید اضافه می شود ......................
			$qry = " insert into hrmstotal.bases(PersonID ,BaseType ,BaseValue , RegDate , ExecuteDate ,BaseMode , ExtraInfo ,BaseStatus ) values
				( ".$temp1[$i]['PersonID'].", ".$type." , ".$base." , now() ,'2013-02-19' ,  'SYSTEM' ,0 , 'NORMAL'
						)"; 
			PdoDataAccess::runquery($qry);				

		}			
		elseif($base==0 && $type == 0){
			if($temp1[$i]['devotion_type'] == 1)
				{		
					$qry = " select personid ,BaseType , BaseValue   
								from bases 
									where PersonID = ".$temp1[$i]['PersonID']." and BaseType = 5 and BaseStatus = 'NORMAL' "  ; 
					$tmp = PdoDataAccess::runquery($qry);

					if(count($tmp) > 0) 
						{
							$qry = " update bases 
										set BaseStatus = 'DELETED'
											where PersonID = ".$tmp[0]['personid']." and BaseType = 5 " ; 
							PdoDataAccess::runquery($qry);
						}						
				}
			continue;
		}
			//......................................................................
		}		
		return	true ;

//................
	}

	function onBeforeUpdate()
	{
		return $this->OnBeforeInsert() ;
	}
	
	function onAfterUpdate()
	{
		return $this->OnAfterInsert() ;
	}


	static function GetAllDevotions($where = "",$whereParam = array())
	{ 
		$query = " select d.PersonID,
                          d.devotion_row,
                          d.amount,
                          d.enlisted,
                          d.from_date,
                          d.to_date,
                          d.war_place,
                          bi.Title,
                          bi.TypeID ,
                          d.comments ,
                          d.letter_date , 
                          d.letter_no ,
                          d.continous,
						  bi2.Title Tenlisted ,
		                  d.devotion_type ,
		                  d.personel_relation                           
                               
                    from person_devotions d
						 LEFT OUTER JOIN persons p ON (d.PersonID = p.PersonID)
						 LEFT join Basic_Info bi ON (bi.InfoID =d.devotion_type and bi.TypeID = 2)
						 LEFT JOIN Basic_Info bi2 ON ( bi2.InfoID = d.enlisted  and bi2.TypeID = 7 )

                   where bi.TypeID = 2";
		
        
		$query .= ($where != "") ? " AND " . $where : "";

				
		$temp = parent::runquery($query, $whereParam);

		return $temp;
	}
	
	 function AddDevotion()
	 { 
	 	$this->devotion_row  = (manage_person_devotion::LastID($this->PersonID)+1);
		if($this->OnBeforeInsert() === false )
			return false;
	 	if( PdoDataAccess::insert("person_devotions", $this) === false )
			return false;
		
		$this->OnAfterInsert(); 
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->RelatedPersonID = $this->PersonID;
		$daObj->MainObjectID = $this->devotion_row;
		$daObj->TableName = "person_devotions";
		$daObj->execute();
		
		return true; 	
	 }
	 
	 function EditDevotion()
	 { 
	         
		if($this->onBeforeUpdate() === false )
			return false;
			
	 	$whereParams = array();
	 	$whereParams[":pid"] = $this->PersonID;
	 	$whereParams[":rowid"] = $this->devotion_row;
		if( PdoDataAccess::update("person_devotions",$this," PersonID=:pid and devotion_row=:rowid ", $whereParams) === false)
	 		return false;
		
		$this->onAfterUpdate(); 
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->RelatedPersonID = $this->PersonID;
		$daObj->MainObjectID = $this->devotion_row;
		$daObj->TableName = "person_devotions";
		$daObj->execute();

	 	return true;
	
	 }
	 
	static  function RemoveDevotion($PersonID,$row_no)
	 {
	 	
	 	$whereParams = array();
	 	$whereParams[":pid"] = $PersonID;
	 	$whereParams[":rowid"] = $row_no;
	 	
	 	if( PdoDataAccess::delete("person_devotions"," PersonID=:pid and devotion_row=:rowid", $whereParams) === false) {
			parent::PushException(ER_PERSON_DEP_DEL);
	 		return false;	 	
	 	}

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->RelatedPersonType = DataAudit::PersonType_staff;
		$daObj->RelatedPersonID = $PersonID;
		$daObj->MainObjectID = $row_no;
		$daObj->TableName = "person_devotions";
		$daObj->execute();
	 	
	 	return true;
	 			
	 }
	 
	static function CountDevotion($where = "",$whereParam = array())
	{
		$query = " select count(*)
                               
                   from person_devotions d
                             LEFT OUTER JOIN persons p ON (d.PersonID = p.PersonID)
                             INNER join Basic_Info bi ON (bi.InfoID =d.devotion_type and bi.TypeID = 2)

                   where bi.TypeID = 2";
		
		$query .= ($where != "") ? " AND " . $where : "";		
		
		$temp = parent::runquery($query, $whereParam);
			    
		return $temp[0][0];
	}
	
	private static function LastID($PersonID)
	 {
	 	$whereParam = array();
	 	$whereParam[":PD"] = $PersonID;
	 	
	 	return parent::GetLastID("person_devotions","devotion_row","PersonID=:PD",$whereParam);
	 }
	 
    public static function get_person_devotions($personID, $devotion_type_set = NULL, $personel_relation = OWN)
	{
		$where = "";
		
		if ($devotion_type_set)
			$where = ' AND devotion_type IN ' . $devotion_type_set;
			
		if($personel_relation != 'ALL')
			$where .= ' AND personel_relation IN ('. $personel_relation.')' ;
	
		$sql = "SELECT  devotion_type,b.InfoDesc as devotionTypeName,SUM(amount) amount
				FROM    HRM_person_devotions
					join BaseInfo b on(TypeID= 70 AND InfoID=devotion_type)
		        WHERE   PersonID = ".$personID . $where . "
		        GROUP BY devotion_type";
	
		return parent::runquery($sql);
	}

// جمع آوری سابقه ایثارگری یک فرد 
    static function get_devotions_last_coefs($staff_id , $gdate="" , $pt="")
    {  
		if( $gdate !="" ) $w = "and pd.from_date <= '$gdate' ";
		else $w = "" ;
                
                if( $pt != HR_WORKER ) 
                    $est = " and enlisted = 1 ";
                else
                    $est = " " ; 
                
        	//_________________________________________________
		// جمع اوري سوابق ايثارگري يك شخص خاص
		$query = "SELECT s.staff_id,
					SUM(CASE WHEN pd.devotion_type=".DEVOTION_TYPE_FIGHTER." $est
	                     THEN if((pd.from_date <= '1988-08-20' and to_date <= '1988-08-20'),amount ,
								if(( pd.from_date <= '1988-08-20' and to_date >= '1988-08-20'),
									datediff('1988-08-20',pd.from_date ),0 )) END) fighter,
					SUM(CASE WHEN pd.devotion_type=".DEVOTION_TYPE_PRISONER." THEN amount ELSE 0 END) prisoner,
					MAX(CASE WHEN pd.devotion_type=".DEVOTION_TYPE_WOUNDED." THEN amount ELSE 0 END) wounded
				 FROM staff s
				 	INNER JOIN persons p ON (p.personID = s.personID)
				 	INNER JOIN person_devotions pd ON (pd.personID = p.personID)
				 WHERE s.staff_id = $staff_id $w
				 GROUP BY pd.PersonID";
		
		$dt = parent::runquery($query);

		if(count($dt) == 0)
		{
			parent::PushException(WRIT_SALARY_ITEM_NOT_FOUND);
			return false;
		}

		$devotion_coefs['fighter']	= $dt[0]['fighter'];
		$devotion_coefs['prisoner']	= $dt[0]['prisoner'];
		$devotion_coefs['wounded']	= $dt[0]['wounded'];
 
       return $devotion_coefs ; 
    }

    // جمع اوري سوابق ايثارگري يك شخص خاص
        static function get_devotions_coefs($staff_id , $from_j_year , $to_j_year){
     
            $query = "SELECT pd.*
                             FROM staff s
                             INNER JOIN persons p
                                ON p.personID = s.personID
                             INNER JOIN person_devotions pd
                                ON pd.personID = p.personID
                             WHERE s.staff_id = $staff_id
                             ORDER BY letter_date " ;
            $res = parent::runquery($query);

            $dv_types =   array(
                                DEVOTION_TYPE_FIGHTER ,
                                DEVOTION_TYPE_PRISONER ,
                                DEVOTION_TYPE_WOUNDED
                                );
            $devotions = array();
            for($j=$from_j_year ; $j <= $to_j_year ; $j++)
            {
                $devotions[$j][DEVOTION_TYPE_FIGHTER] = 0 ;
                $devotions[$j][DEVOTION_TYPE_PRISONER] = 0 ;
                $devotions[$j][DEVOTION_TYPE_WOUNDED] = 0 ;
            }

            foreach ($dv_types as $dv_type){

                $is_first = true ;
                for ($i= 0 ; $i < count($res); $i++) {
                    if($res[$i]['devotion_type'] == $dv_type){
                        $cur_j_date = '01/01/'.$from_j_year;
                        $cur_g_date = DateModules::Shamsi_to_Miladi($cur_j_date);

                        //اولين سابقه از پارامتر ورودي تاريخ شروع محاسبه مي شود نه از تاريخ نامه
                        if($is_first || $dv_type != DEVOTION_TYPE_WOUNDED) {
                            $res[$i]['letter_date'] = $cur_g_date ;
                            $is_first = false ;
                        }
                        $tmp_from_j_year = $from_j_year ;

                        while(str_replace("-","/",$res[$i]['letter_date'])> $cur_g_date &&
                              $from_j_year <= $to_j_year)
                        {
                            $from_j_year ++ ;
                            $cur_j_date = '01/01/'.$from_j_year;
                            $cur_g_date = DateModules::Shamsi_to_Miladi($cur_j_date);
                        }

                        while($from_j_year <= $to_j_year)
                        {
                            // محاسبه ضرايب ايثارگري
                            if($dv_type == DEVOTION_TYPE_FIGHTER)
                            {
                                if($res[$i]['enlisted'])
                                $coef = 0.06/356 ;
                                else
                                $coef = 0.03/365 ;                             
                                $devotions[$from_j_year][$dv_type] += $coef *$res[$i]['amount'];
                            }
                            else if ($dv_type == DEVOTION_TYPE_PRISONER)
                                $devotions[$from_j_year][$dv_type] = $res[$i]['amount']*0.06/365;
                            else if($dv_type == DEVOTION_TYPE_WOUNDED)
                                $devotions[$from_j_year][$dv_type] = $res[$i]['amount']*0.1*0.06;

                            $from_j_year ++ ;
                            $cur_j_date = '01/01/'.$from_j_year;
                            $cur_g_date = DateModules::Shamsi_to_Miladi($cur_j_date);
                        }
                        $from_j_year = $tmp_from_j_year ;
                    }

                }
            }
            return $devotions ;
        }

    // امتیاز ایثارگری را محاسبه می کند
	static function get_devotion_score($staff_id ,$gdate="" )
	{
        $devotion_coefs = manage_person_devotion::get_devotions_last_coefs($staff_id ,$gdate);

       if($gdate >= '2010-03-21') {

           $query = " SELECT s.staff_id,s.person_type , s.personid
						  	 FROM staff s
								 WHERE s.staff_id = ".$staff_id ;
            
           $res = parent::runquery($query);

           $person_family_shohada =  manage_person_devotion::get_person_devotions($res[0]["personid"], '('.BEHOLDER_FAMILY_DEVOTION.')',  BOY.','.DAUGHTER );

		if($person_family_shohada && $devotion_coefs['wounded'] < 50  && $res[0]["person_type"] == 2 ){
			 $devotion_coefs['wounded'] = 50 ;
		}

	}

        $d1_value = $devotion_coefs['wounded'];
		$d2_value = $devotion_coefs['prisoner']/30;
		$d3_value = $devotion_coefs['fighter']/30;
		
		//جانبازی 
		$d_array1 = array(
		array("start"=> 4.999  , "end"=> 5   , "score"=> 400),
		array("start"=> 5  , "end"=> 10  , "score"=> 500),
		array("start"=> 10 , "end"=> 15  , "score"=> 600),
		array("start"=> 15 , "end"=> 20  , "score"=> 700),
		array("start"=> 20 , "end"=> 25  , "score"=> 800),
		array("start"=> 25 , "end"=> 30  , "score"=> 900),
		array("start"=> 30 , "end"=> 35  , "score"=> 1000),
		array("start"=> 35 , "end"=> 40  , "score"=> 1100),
		array("start"=> 40 , "end"=> 45  , "score"=> 1200),
		array("start"=> 45 , "end"=> 50  , "score"=> 1300),
		array("start"=> 50 , "end"=> 60  , "score"=> 1500),
		array("start"=> 60 , "end"=> 200 , "score"=> 1550)
		);
	
		$score1 = 0 ;
		foreach ($d_array1 as $arr) {
			if($arr["start"]<$d1_value && $arr["end"]>=$d1_value){
				$score1 = $arr["score"];
				break;
			}
		}
	    
		// اسارت 
		$d_array2 = array(
		array("start"=> 2.999  , "end"=> 6   , "score"=> 400),
		array("start"=> 6  , "end"=> 12  , "score"=> 500),
		array("start"=> 12 , "end"=> 18  , "score"=> 600),
		array("start"=> 18 , "end"=> 24  , "score"=> 700),
		array("start"=> 24 , "end"=> 30  , "score"=> 800),
		array("start"=> 30 , "end"=> 36  , "score"=> 900),
		array("start"=> 36 , "end"=> 42  , "score"=> 1000),
		array("start"=> 42 , "end"=> 48  , "score"=> 1100),
		array("start"=> 48 , "end"=> 54  , "score"=> 1200),
		array("start"=> 54 , "end"=> 60  , "score"=> 1300),
		array("start"=> 60 , "end"=> 70  , "score"=> 1500),
		array("start"=> 70 , "end"=> 300 , "score"=> 1550)
		);
	
		$score2 = 0 ;
		foreach ($d_array2 as $arr) {
			if($arr["start"]<$d2_value && $arr["end"]>=$d2_value){
				$score2 = $arr["score"];
				break;
			}
		}
		
		// رزمندگی 
		$d_array3 = array(
		array("start"=> 2.999  , "end"=>6   , "score"=> 400),
		array("start"=> 6  , "end"=>12  , "score"=> 500),
		array("start"=> 12 , "end"=>18  , "score"=> 600),
		array("start"=> 18 , "end"=>24  , "score"=> 700),
		array("start"=> 24 , "end"=>30  , "score"=> 800),
		array("start"=> 30 , "end"=>36  , "score"=> 900),
		array("start"=> 36 , "end"=>42  , "score"=> 1000),
		array("start"=> 42 , "end"=>48  , "score"=> 1100),
		array("start"=> 48 , "end"=>54  , "score"=> 1200),
		array("start"=> 54 , "end"=>60  , "score"=> 1300),
		array("start"=> 60 , "end"=>70  , "score"=> 1500),
		array("start"=> 70 , "end"=>300 , "score"=> 1550)
		);
		$score3 = 0 ;
		foreach ($d_array3 as $arr) {
			if($arr["start"]<$d3_value && $arr["end"]>=$d3_value){
				$score3 = $arr["score"];
				break;
			}
		}

       		
		// ایثارگری که بالاترین امتیاز را دارد به اضافه ی 25 درصد سایر ایثارگری ها تا سقف 1550 امتیاز
		$max = max(array($score1 , $score2 , $score3));
		$sum = $score1 + $score2 + $score3 ;
		
		//return min(array($max + 0.25 * ($sum - $max),1550)) ;
		return min(array($sum,1550)) ;
	}

    /*
     * این قسمت موقتا در این صفحه اضافه شده است چون تابع ان  مربوط به کلاس خودش می باشد
     * ضرایب بسیج سالهای مختلف 
     */

 
    static function get_mobilizations_coefs($staff_id , $from_j_year , $to_j_year){
    
        $query = "   SELECT *
                       FROM
                            mobilization_lists ml
                                INNER JOIN mobilization_list_items mli
                                    ON ml.list_id = mli.list_id
                     WHERE staff_id = $staff_id
                         ORDER BY list_date
                ";
         $res = parent::runquery($query);

         $mobilizations = array();
         
         for($i=0 ; $i < count($res) ; $i++){

            $list_date = $res[$i]['list_date'];
            $list_j_date =  DateModules::Miladi_to_Shamsi($list_date);
            $year = substr($list_j_date,0,4);
            if($year>=$from_j_year && $year<=$to_j_year)
            $mobilizations[$year] = $res[$i]['mobilization_coef'];

         }
        return $mobilizations ;
    }
		 
	 
}


?>