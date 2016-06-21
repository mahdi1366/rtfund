<?php
//---------------------------
// programmer:	Jafarkhani
// create Date:	88.08
//---------------------------

class manage_writ_subType
{
	public $person_type;
	public $writ_type_id;
	public $writ_subtype_id;
	public $title;
	public $description;
	public $emp_state;
	public $emp_mode;
	public $worktime_type;
	public $edit_fields;
	public $time_limited;
	public $req_staff_signature;
	public $automatic;
	public $salary_pay_proc;
	public $items_effect;
	public $post_effect;
	public $annual_effect;
	public $remember_distance;
	public $remember_message;
	public $print_title;
	public $comments;
	public $show_in_summary_doc;
	
	function __construct($person_type, $writ_type_id, $writ_subtype_id)
	{
		if($person_type != "" && $writ_type_id != "" && $writ_subtype_id != "")
		{
			$query = "select * from writ_subtypes 
				where person_type = :ptype and 
					  writ_type_id = :wTypeId and 
					  writ_subtype_id = :wSubTypeId";
			
			$whereParam = array(":ptype" => $person_type , 
			                    ":wTypeId" => $writ_type_id ,
			                    ":wSubTypeId" => $writ_subtype_id );
						
			PdoDataAccess::FillObject($this, $query, $whereParam);
		}
	}
	
}

?>