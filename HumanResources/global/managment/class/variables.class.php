<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.10
//---------------------------
class manage_variables
{
	
	static function get_variable_info($varName="" , $person_type = "")
	{ 
		
	echo "eeeeeee"; die();
		$query = "select * 
				  from rapid.variables2 
				  where var_name='" . $varName . "' AND 
						sysCode='" . $_SESSION["SystemCode"] . "' AND 
						uname is NULL AND
						mname is Null";
echo $query ; 
		$dt = PdoDataAccess::runquery($query);
		if(count($dt) == 0)
			return false;	
		
		return $dt[0];
	}
}
?>