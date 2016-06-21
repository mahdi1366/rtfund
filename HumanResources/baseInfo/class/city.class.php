<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.12
//---------------------------

class manage_cities extends PdoDataAccess
{
	static function getCityName($city_id)
	{
		$query = "select * from cities where city_id=" . $city_id;
		$dt = parent::runquery($query);
		
		if(count($dt) <> 0)
			return $dt[0]["ptitle"];
			
		return "";
	}
}
?>