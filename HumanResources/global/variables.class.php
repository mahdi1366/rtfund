<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.10
//---------------------------
class manage_variables
{
	static function get_variable_info($varName, $param1="", $param2="")
	{
		$query = "select var_value
				  from variables
				  where var_name='" . $varName . "' ";

		$query .= $param1 != "" ? " AND param1='$param1'" : "";
		$query .= $param2 != "" ? " AND param2='$param2'" : "";

		$dt = PdoDataAccess::runquery($query);
		if(count($dt) == 0)
			return false;	
		
		return $dt[0][0];
	}
}
?>