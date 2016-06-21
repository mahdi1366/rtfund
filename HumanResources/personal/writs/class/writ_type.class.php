<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	90.01
//---------------------------

class manage_writ_Type  extends PdoDataAccess
{
	public $person_type;
	public $writ_type_id;
	public $title;
		
	function __construct($person_type, $writ_type_id )
	{
		if($person_type != "" && $writ_type_id != "")
		{
			$query = " select * from writ_types
                            where person_type = :ptype and
                                  writ_type_id = :wTypeId ";
			
			$whereParam = array(":ptype" => $person_type , 
			                    ":wTypeId" => $writ_type_id );
						
			PdoDataAccess::FillObject($this, $query, $whereParam);
		}
	}
	
}

?>