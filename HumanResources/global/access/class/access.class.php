<?php
//---------------------------
// programmer:	Sh.Jafarkhani
// Date:		90.02
//---------------------------

class manage_access extends PdoDataAccess
{
	static function getValidPersonTypes()
	{
		$DT = parent::runquery("select * from person_type_access where UserID=?", array($_SESSION["UserID"]));
		if(count($DT) == 0)
			return "-1";

		$str = "";
		for($i=0; $i<count($DT); $i++)
			$str .= $DT[$i]["person_type"] . ",";

		$str = substr($str, 0, strlen($str)-1);
		return $str;
	}

	static function getValidCostCenters()
	{
		$DT = parent::runquery("select * from cost_center_access where UserID=?", array($_SESSION["UserID"]));
		if(count($DT) == 0)
			return "-1";

		$str = "";
		for($i=0; $i<count($DT); $i++)
			$str .= $DT[$i]["cost_center_id"] . ",";

		$str = substr($str, 0, strlen($str)-1);
		return $str;
	}
	
	static function getValidPayments()
	{
		$DT = parent::runquery("select * from PaymentAccess where PersonID=?", array($_SESSION["PersonID"]));
		if(count($DT) == 0)
			return "-1";

		$str = "";
		for($i=0; $i<count($DT); $i++)
			$str .= $DT[$i]["PaymentType"] . ",";

		$str = substr($str, 0, strlen($str)-1);
		return $str;
	}

}
?>
