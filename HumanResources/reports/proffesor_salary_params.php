<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	91.02
//---------------------------
require_once '../../baseInfo/class/salary_item_types.class.php';
require_once '../../salary/salary_params/class/salary_params.class.php';

if(!empty($_POST['FINT:78']) && $_POST['FINT:78'] > 0 && $_POST['FINT:78'] < 41 )
    $from_base = $_POST['FINT:78'] ; 
else 
    $from_base = 1 ; 

if(!empty($_POST['TINT:78']) && $_POST['TINT:78'] > 0 && $_POST['TINT:78'] < 41 )
    $to_base = $_POST['TINT:78'] ; 
else 
    $to_base = 40 ; 

if (empty($_POST['SELECT:77']) || $_POST['SELECT:77'] == -1  ) { 
        $from_science_level = 1;
        $to_science_level   = 5;
} else {
        $from_science_level = $_POST['SELECT:77'];
        $to_science_level   = $_POST['SELECT:77'];
}

$work_state_id = (!empty($_POST['SELECT:93']) && $_POST['SELECT:93'] != '-1' ) ? $_POST['SELECT:93'] : '19' ; 
$work_city_id = (!empty($_POST['SELECT:94']) && $_POST['SELECT:94'] != '-1' ) ? $_POST['SELECT:94'] : '11' ; ; 

$emp_state = 4 ; 

$execute_date = (!empty($_POST['FDATE:72'])) ? DateModules::Shamsi_to_Miladi($_POST['FDATE:72']) : DateModules::Now();

                        

         $recordSet = compute_professors_salary_params($from_base,
                                                $to_base,
                                                $from_science_level,
                                                $to_science_level,
                                                $work_state_id,
                                                $work_city_id,
                                                $execute_date,
                                                $emp_state);         

$content = "" ; 

for($i=0 ; $i<count($recordSet) ;$i++ )
{    
    $content .= "<tr><td>".($i+1)."</td>".
                "<td>".number_format(round($recordSet[$i]['total_sum']))."</td>"."<td>".number_format(round($recordSet[$i]['vijeh_extra']))."</td>".
                "<td>".number_format(round($recordSet[$i]['special_extra']))."</td>"."<td>".number_format(round($recordSet[$i]['absorb_extra'])).
                "</td>"."<td>".number_format(round($recordSet[$i]['weather_bad_extra']))."</td>"."<td>".number_format(round($recordSet[$i]['base_salary']))."</td>".
                "<td>".$recordSet[$i]['base']."</td>"."<td>".$recordSet[$i]['science_level']."</td></tr>";     
    
}

$tags =  array(
	'<!--data-->' => $content,
	'<!--now-->' => DateModules::shNow()
               );

$content = file_get_contents("../../reports/proffesor_salary_param.html");
$content = str_replace(array_keys($tags), array_values($tags), $content);
echo $content;

die() ; 

function compute_professors_salary_params($from_base,
            $to_base,
            $from_science_level,
            $to_science_level,
            $work_state_id,
            $work_city_id,
            $execute_date,
            $emp_state = EMP_STATE_APPROVED_CEREMONIOUS ,
            $compute_heiat_omana_absorb_extra = true) {
    

    $query = 'SELECT  validity_start_date,
    				validity_end_date,
    				salary_item_type_id
    		FROM  salary_item_types
    		WHERE  salary_item_type_id IN ( '.SIT_PROFESSOR_BASE_SALARY.',
                                                '.SIT_PROFESSOR_BAD_WEATHER_EXTRA.',
                                                '.SIT_PROFESSOR_ABSOPPTION_EXTRA.',
                                                '.SIT_PROFESSOR_SPECIAL_EXTRA.',
                                                '.SIT_PROFESSOR_PARTICULAR_EXTRA.',
                                                '.SIT_PROFESSOR_HEIAT_OMANA_SPECIAL_EXTRA.'
                                                )';
    
    $resultSet = PdoDataAccess::runquery($query);
    
    foreach ($resultSet as $result) {
    	switch ($result['salary_item_type_id']) {
    		case SIT_PROFESSOR_BASE_SALARY :
    			$is_valid_base_salary = manage_salary_item_type::validate_salary_item_type_id($result['validity_start_date'],
                                                                                                      $result['validity_end_date'],
                                                                                                      $execute_date);
    			break;
    		case SIT_PROFESSOR_BAD_WEATHER_EXTRA :
  				$is_valid_bad_weather_extra = manage_salary_item_type::validate_salary_item_type_id($result['validity_start_date'],
                                                                                                                    $result['validity_end_date'],
                                                                                                                    $execute_date);
    			break;
    		case SIT_PROFESSOR_ABSOPPTION_EXTRA :
   				$is_valid_absopption_extra = manage_salary_item_type::validate_salary_item_type_id($result['validity_start_date'],
                                                                                                                   $result['validity_end_date'],
                                                                                                                   $execute_date);
    			break;
    		case SIT_PROFESSOR_SPECIAL_EXTRA :
   				$is_valid_special_extra = manage_salary_item_type::validate_salary_item_type_id($result['validity_start_date'],
                                                                                                                $result['validity_end_date'],
                                                                                                                $execute_date);
    			break;
    		case SIT_PROFESSOR_PARTICULAR_EXTRA :
   				$is_valid_particular_extra = manage_salary_item_type::validate_salary_item_type_id($result['validity_start_date'],
                                                                                                                   $result['validity_end_date'],
                                                                                                                   $execute_date);;
    			break;
    		case SIT_PROFESSOR_HEIAT_OMANA_SPECIAL_EXTRA :
   				$is_valid_heiat_omana_absorb_extera = manage_salary_item_type::validate_salary_item_type_id($result['validity_start_date'],
                                                                                                                            $result['validity_end_date'],
                                                                                                                            $execute_date);
    	}
    }
          
    $counter = 0;
	for ($science_level = $to_science_level; $science_level >= $from_science_level; $science_level--)
    	for ($base = $from_base; $base <= $to_base; $base++){
        	$writ_rec['base']          = $base;
        	$writ_rec['science_level'] = $science_level;
        	$writ_rec['work_city_id']  = $work_city_id;
                $writ_rec['work_state_id'] = $work_state_id;
                $writ_rec['execute_date'] = $execute_date;
                $writ_rec['emp_state']	  = $emp_state ;

		/*	if($compute_heiat_omana_absorb_extra && $is_valid_heiat_omana_absorb_extera){
				//آخرين حكم سال قبل
                            $base_writ_rec['base']          = $base;
                            $base_writ_rec['science_level'] = $science_level;
                            $base_writ_rec['work_city_id']  = $work_city_id;
                            $base_writ_rec['work_state_id'] = $work_state_id;
                            $base_writ_rec['emp_state']	  	= $emp_state ;
 
                                                             
                            $this_writ_year = substr(DateModules::miladi_to_shamsi($execute_date),0,4);
			    $one_year_ago = $this_writ_year - 1;
			    $one_year_ago_last_day_writ = '29/12/'.$one_year_ago;
			    $Gone_year_ago_last_day = DateModules::shamsi_to_miladi($one_year_ago_last_day_writ);
			    $base_writ_rec['execute_date'] = $Gone_year_ago_last_day ;
 
			    if($base_writ_rec)
				    $rec = compute_professors_salary_params(
				    				$base_writ_rec['base'],
				    				$base_writ_rec['base'],
				    				$base_writ_rec['science_level'],
				    				$base_writ_rec['science_level'],
				    				$base_writ_rec['work_state_id'],
				    				$base_writ_rec['work_city_id'],
				    				$base_writ_rec['execute_date'] ,
				    				$base_writ_rec['emp_state'],
				    				false);
			    $devotion_extra = 0 ;
			   	$param1 =
					$rec[0]['base_salary'] +
					$rec[0]['absorb_extra'] +
					$rec[0]['vijeh_extra'] +
					$rec[0]['special_extra'] +
					$devotion_extra ;
				$heiat_omana_absorb_extra = $param1 * 0.02 ;
			}
			else
				$heiat_omana_absorb_extra = 0 ;*/

			if ($is_valid_base_salary == true) { 
                            $base_salary = compute_salary_item1_01($writ_rec);
                            } else {
                                $base_salary = 0;
                            }

            if ($is_valid_absopption_extra == true) {
            	$absorb_extra       = round($base_salary * 
                            manage_salary_params::get_salaryParam_value("", HR_PROFESSOR ,SPT_PROFESSOR_ABSORB_COEF , $writ_rec['execute_date'],$writ_rec['science_level'],$writ_rec['work_city_id'],$writ_rec['work_state_id']));

            } else {
            	$absorb_extra = 0;
            }

            if ($is_valid_bad_weather_extra == true) {
				$weather_bad_extra  = round($base_salary * 
                                        manage_salary_params::get_salaryParam_value( "" , HR_PROFESSOR , SPT_PROFESSOR_WHEATHER_COEF, $writ_rec['execute_date'],$writ_rec['work_city_id'],$writ_rec['work_state_id']));
            } else {
            	$weather_bad_extra = 0;
            }

            if ($is_valid_special_extra == true) {
				$special_extra      = round($base_salary * 
                                                        manage_salary_params::get_salaryParam_value( "" , HR_PROFESSOR , SPT_SPECIAL_EXTRA_COEF, $writ_rec['execute_date'], $writ_rec['science_level']));

            } else {
            	$special_extra = 0;
            }

            if ($is_valid_particular_extra == true) {
				$vijeh_extra_old        = $base_salary * manage_salary_params::get_salaryParam_value( "" , HR_PROFESSOR , SPT_PARTICULAR_EXTRA, $writ_rec['execute_date'],$writ_rec['science_level']);

				if($writ_rec['execute_date'] > '2010-03-20'){

							$vijeh_extra_new = $base_salary * manage_salary_params::get_salaryParam_value( "" , HR_PROFESSOR , SPT_PARTICULAR_EXTRA_NEW, $writ_rec['execute_date'],$writ_rec['science_level']);
							$Mvalue =  manage_salary_params::get_salaryParam_value( "" , HR_PROFESSOR ,MAX_SPT_PARTICULAR_EXTRA_NEW, $writ_rec['execute_date'],$writ_rec['science_level']);
							if( $vijeh_extra_new >  $Mvalue )
							{
								$vijeh_extra_new = $Mvalue ;
							}

								$vijeh_extra = $vijeh_extra_new + $vijeh_extra_old ;
				}
				else {

					$vijeh_extra = $vijeh_extra_old ;
				}

            } else {
            	$vijeh_extra = 0;
            }
          
            $salary_params[$counter]['base_salary']       		= $base_salary;
            $salary_params[$counter]['absorb_extra']      		= $absorb_extra;
            $salary_params[$counter]['weather_bad_extra'] 		= $weather_bad_extra;
            $salary_params[$counter]['special_extra']     		= $special_extra;
           // $salary_params[$counter]['heiat_omana_absorb_extra']= $heiat_omana_absorb_extra ;
            $salary_params[$counter]['vijeh_extra']       		= $vijeh_extra;
            $salary_params[$counter]['total_sum']         		= $base_salary + $absorb_extra+ $weather_bad_extra + $special_extra + $vijeh_extra ; // + $heiat_omana_absorb_extra;

            $salary_params[$counter]['base']     		  = $base;
            switch ($science_level) {
            	case INSTRUCTOR_EDUCATOR : $salary_params[$counter]['science_level'] = 'مربي آموزشيار';
                	break;
            	case EDUCATOR            : $salary_params[$counter]['science_level'] = 'مربي';
                	break;
            	case MASTERSTROKE        : $salary_params[$counter]['science_level'] = 'استاديار';
                	break;
            	case LECTURESHIP         : $salary_params[$counter]['science_level'] = 'دانشيار';
                	break;
            	case MASTERSHIP          : $salary_params[$counter]['science_level'] = 'استاد';
                	break;
            }            
                           
            $counter++;
        }
        
        return $salary_params ; 

}

function compute_salary_item1_01($writ_rec){
		//param1 : پايه
		//param2 : عدد مبنا
		//param3 : ضريب حقوق
	
		if (($writ_rec['emp_state'] == EMP_STATE_SOLDIER_CONTRACTUAL ||
			$writ_rec['emp_state'] == EMP_STATE_ONUS_SOLDIER_CONTRACTUAL || 
			$writ_rec['emp_state'] == EMP_STATE_CONTRACTUAL) && 
			$writ_rec['execute_date'] < str_replace("/","-",DateModules::shamsi_to_miladi('1389-07-01')))		
				$base = 1;
		else
			$base = $writ_rec['base'];


     $professor_base_number = Get_professor_base_number($writ_rec['science_level']);

	 $salary_coef = manage_salary_params::get_salaryParam_value("", HR_PROFESSOR, SPT_SALARY_COEF, $writ_rec['execute_date']);

		if (!$salary_coef) 
		{
                    PdoDataAccess::PushException(SALARY_COEF_NOT_FOUND);
                    return false;
		}
		//$this->param1 = $base;
		//$this->param2 = $professor_base_number;
		//$this->param3 = $salary_coef;
		
		$value = $salary_coef * ($professor_base_number + 5 * $base);
		//echo  $value ." value ----<br> ";
		if (!($value > 0)) 
		{
			parent::PushException(BASE_SALARY_CALC_ERR);
			return false;
		}
	
		if(($writ_rec['emp_state'] == EMP_STATE_SOLDIER_CONTRACTUAL ||
			$writ_rec['emp_state'] == EMP_STATE_ONUS_SOLDIER_CONTRACTUAL || 
			$writ_rec['emp_state'] == EMP_STATE_CONTRACTUAL)&&
			$writ_rec['execute_date'] < '2009-09-23')

			$value *= 0.95;
	//echo  $value ." value ----<br> "; die();
		return $value;
	}
        
        function Get_professor_base_number($sience_level)
	{
		switch($sience_level)
		{
			case 1: return 90;
			case 2: return 100;
			case 3: return 125;
			case 4: return 145;
			case 5: return 170;
		}
		return "";
	}



?>