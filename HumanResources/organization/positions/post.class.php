<?php
//---------------------------
// programmer:	Mahdipour
// create Date:	94.11
//---------------------------
require_once $address_prefix . "/HumanResources/personal/staff/class/staff.class.php";

class manage_posts extends PdoDataAccess
{
	// محاسبه امتیاز مرتبط با مشاغل سرپرستی
	static function compute_manager_score_percent($staff_id)
	{
		$query = "SELECT
                      writ_id ,
                      w.onduty_year ,
                      w.onduty_month ,
                      w.onduty_day ,
                      p.post_type ,
                      p.post_id
			FROM writs w LEFT OUTER JOIN position p on(p.post_id = w.post_id)
			WHERE w.staff_id = $staff_id AND (w.history_only = 0 OR w.history_only IS NULL)
            ORDER BY w.execute_date
		";
		$dt = parent::runquery($query);
		
		$prior_onduty_year = -1 ;
		$prior_onduty_month = -1 ;
		$prior_onduty_day = -1 ;
		$prior_post_type = -1 ;
		$supervisor_percent = 0 ;
		$manager_percent = 0 ;

		        
		for($i=0; $i < count($dt); $i++)
		{   
			$cur_onduty_year = $dt[$i]['onduty_year'] ;
			$cur_onduty_month = $dt[$i]['onduty_month'] ;
			$cur_onduty_day = $dt[$i]['onduty_day'] ;
			$cur_post_type = $dt[$i]['post_type'];
			$cur_post_id = $dt[$i]['post_id'];
                        
			if($prior_post_type == POST_EXE_SUPERVICE || $prior_post_type == POST_EXE_MANAGER)
			{
				$diff_day = ($cur_onduty_year * 365.25 + $cur_onduty_month * 30.4375 + $cur_post_type) - 
							($prior_onduty_year * 365.25 + $prior_onduty_month * 30.4375 + $prior_post_type);
					if($diff_day > 0)
					{
						if($prior_post_type == POST_EXE_SUPERVICE)
						{ 	
							$supervisor_percent += ($diff_day/365.25) * 1 ;
                           
						}
						else if($prior_post_type == POST_EXE_MANAGER)
						{
							$manager_percent += ($diff_day/365.25) * 2 ;
						}
					}
			}
			$prior_onduty_year =  $cur_onduty_year ;
			$prior_onduty_month = $cur_onduty_month ;
			$prior_onduty_day = $cur_onduty_day ;
			$prior_post_type = $cur_post_type ;
			$prior_post_id = $cur_post_id ;
		}

		$supervisor_percent = min(array($supervisor_percent , 10));
		$manager_percent = min(array($manager_percent , 20));
		$percent = round(min(array($supervisor_percent + $manager_percent , 20)),3);
		
		return $percent ;
	}

	static function get_positions($post_id)
	{
		$query = "SELECT *
                    FROM   position p
                           LEFT OUTER JOIN job_fields jf ON (p.jfid = jf.jfid)
                    WHERE  p.post_id = $post_id";

        $dt = parent::runquery($query);
        if(count($dt) == 0)
        	return false;
        	
        return $dt[0];
	}
	
	static function get_PostType($staff_id , $post_id ="")
	{
	    if($post_id > 0 ) {
		
		$query = " select post_type , ManagementCoef
				from position where post_id = ".$post_id  ; 					    
		
		
	    }
	    else {
		$query = "  SELECT p.post_type , s.staff_id ,p.ManagementCoef
			    FROM position p
				    join staff s using(post_id)
			    WHERE  s.staff_id = " . $staff_id;				
		
	    }
	    
	    $dt = parent::runquery($query);
		if(count($dt) == 0)
			return "";

		return $dt[0]; 
		
	}
	
	static function get_SupervisionKind($staff_id ,$post_id )
	{
		$query = " SELECT SupervisionKind
			    FROM position
			    WHERE post_type=5 AND post_id =".$post_id;

        $dt = parent::runquery($query);
        if(count($dt) == 0)
        	return "";
		
        return $dt[0]["SupervisionKind"];
	}
	
	public static function get_job_fields($post_id)
	{
		if(!$post_id)
			return false ;
			
		$query = "SELECT jf.*
					FROM job_fields jf
						INNER JOIN position p ON(jf.jfid = p.jfid)
					WHERE p.post_id = :pid";
		$whereParam = array();
		$whereParam[":pid"] = $post_id;
		
		$dt = parent::runquery($query, $whereParam);
		
		if(count($dt) > 0)
			return $dt[0];
		return false;
	}

	/**
	 * استخراج نام مسئول يک پست خاص
	 * شماره پست و تاريخ يک حکم را گرفته و براي اين پست خاص آخرين حکمي راکه تاريخ آن قبل از تاريخ فرستاده شده است رااستخراج کرده،
	 * سپس نام مسئول رااز روي اين حکم بدست مي آورد
	 *	
	 *
	 * @param  $post_id
	 * @param miladi Y/m/d date $writ_date
	 * @return concat(pfname,' ',plname)
	 */
	function get_post_owner($post_id, $writ_date)
	{
		if(empty($post_id) || empty($writ_date))
			return false;
			
		$query = "SELECT concat(p.pfname,' ',p.plname) as name
					FROM writs w
						LEFT OUTER JOIN staff s ON (w.staff_id = s.staff_id)
				        LEFT OUTER JOIN persons p ON (s.PersonID = p.PersonID)
				    WHERE w.post_id = $post_id AND w.execute_date <= '$writ_date'
				    ORDER BY w.execute_date DESC LIMIT 1";
			
	    $dt = parent::runquery($query);
	    
	    return $dt[0]["name"];
	}
	
	/**
	 * استخراج نام مسئول يک پست خاص
	 * شماره پست و تاريخ يک حکم را گرفته و براي اين پست خاص آخرين حکمي راکه تاريخ آن قبل از تاريخ فرستاده شده است رااستخراج کرده،
	 * سپس نام مسئول رااز روي اين حکم بدست مي آورد
	 *	
	 *
	 * @param  $post_id
	 * @param miladi Y/m/d date $writ_date
	 * @return array(name,staff_id)
	 */
	function get_post_owner2($post_id, $writ_date, $check_professor_exe_posts = false)
	{
		if(empty($post_id) || empty($writ_date))
			return false;
			
		if($check_professor_exe_posts == true)
		{
			$query = "select s.staff_id,concat(p.pfname,' ',p.plname) as name
						FROM professor_exe_posts pp
							INNER JOIN staff s ON s.staff_id = pp.staff_id
							INNER JOIN persons p ON p.PersonID = s.PersonID
						WHERE pp.post_id = $post_id AND pp.from_date <= '$writ_date'
						ORDER BY pp.from_date DESC
						";
			$dt = parent::runquery($query);
			
			if(count($dt) > 0 and !empty($dt[0]["name"]))
				return array("name" => $dt[0]["name"], "staff_id" => $dt[0]["staff_id"]);
		}
	
		$query = "select s.staff_id,concat(p.pfname,' ',p.plname) as name
					FROM writs w
						LEFT OUTER JOIN staff s ON (w.staff_id = s.staff_id)
					    LEFT OUTER JOIN persons p ON (s.PersonID = p.PersonID)
					WHERE w.post_id = $post_id AND w.execute_date <= '$writ_date'
					ORDER BY w.execute_date DESC LIMIT 1";
					
		$dt = parent::runquery($query);
		if(count($dt) == 0)
			return false;
		
		return array("name" => $dt[0]["name"], "staff_id" => $dt[0]["staff_id"]);
	}

	/**
	 * استخراج عنوان پست
	 *
	 * @param $post_id
	 */
	static function get_post_title($post_id)
	{
		$query = "select * from position where post_id = " . $post_id;
		$dt = parent::runquery($query);
		
		if(count($dt) == 0)
			return false;
			
		return $dt[0]["title"];
	}

	static function is_valid($post_id, $date, $staff_id)
	{
		$query = " select * from position where post_id =" . $post_id   ;
      
		$temp = PdoDataAccess::runquery($query);
		if(count($temp) == 0)
		{
			parent::PushException(POST_IS_NOT_VALID) ;
			return false ;
		}
		//پست در اختيار فرد ديگري است
		if($temp[0]["staff_id"] != $staff_id  && $temp[0]["staff_id"] != NULL )
		{
			parent::PushException(str_replace("%0%", $temp[0]['staff_id'], POST_HAS_DETERMINED_ERR)) ;
			return false;
		}

		// پست در تاريخ اجراي حكم معتبر است
		if(($temp[0]['validity_start'] <= $date || $temp[0]['validity_start'] == "" || $temp[0]['validity_start'] == "0000-00-00" ) &&
			($temp[0]['validity_end'] >= $date || $temp[0]['validity_end'] == "" || $temp[0]['validity_end'] == "0000-00-00" ))
			return true;
		else
		{
			parent::PushException(POST_EXPIRE);
			return false;
		}
	}

    /**
     * تغيير پست سازماني فرد
     *
     * @param unknown_type $staff_id
     * @param unknown_type $prior_post_id
     * @param unknown_type $new_post_id
     */
	public static function change_user_post($staff_id , $prior_post_id , $new_post_id, $execute_date)
	{
		$staffObj = new manage_staff("", "", $staff_id);
		
    	if($prior_post_id != $new_post_id)
    	{
    		if($prior_post_id)
    		{
    			$query = "update position set staff_id=null where post_id=" . $prior_post_id;
	    		if( parent::runquery($query) === false )
	    		    return false ;
				$daObj = new DataAudit();
				$daObj->ActionType = DataAudit::Action_update;
				$daObj->MainObjectID = $prior_post_id;
				$daObj->TableName = "position";
				$daObj->description = "آزاد کردن پست";
				$daObj->execute();

				//------------------ baseinfo update ---------------------------
				self::baseinfoRelease($staffObj->PersonID, $prior_post_id, "آزاد کردن پست");

                $query = "update staff set post_id= null where staff_id = " . $staff_id;
                if( parent::runquery($query) === false )
                    return false ;    
    		}
    		if($new_post_id)
    		{
    			$query = "update position set staff_id=$staff_id where post_id = " . $new_post_id; 
                if( parent::runquery($query) === false )
	    		    return false ;

				//------------------ baseinfo update ---------------------------
				self::baseinfoAssign($staffObj->PersonID, $new_post_id, $execute_date, "انتساب پست");

				$query = "update staff set post_id= $new_post_id where staff_id = " . $staff_id;
                if( parent::runquery($query) === false )
                    return false ;
    		}
	    	    
	    	return true ; 
    	}
    }

	static function baseinfoAssign($personID, $post_id, $date, $desc)
	{
		//------------------ baseinfo update ---------------------------
		$temp = PdoDataAccess::runquery("select * from baseinfo.posts where PersonID=" . $personID);
		
		if(count($temp) != 0)
		{
			$dt = PdoDataAccess::runquery("select * from position where post_id=" . $post_id);
			PdoDataAccess::runquery("update baseinfo.posts
				set PostStatus='APPROVED', PostNumber=" . $post_id . " , title='" . $dt[0]["title"] . "'
				where PersonID=" . $personID);
			PdoDataAccess::runquery("insert into baseinfo.PostAssignHistory (PersonID, PostID, AssignDate)
			values(" . $personID . "," . $temp[0]["PostID"] . ",'" . $date . "')");
		}
		else
		{
			PdoDataAccess::runquery("insert into baseinfo.posts(title,ouid,sub_ouid,PostStatus,PersonID,PostNumber)
				select pp.title,pp.ouid,pp.ouid,'APPROVED'," . $personID . ",pp.post_id
				from position pp
				where pp.post_id=" . $post_id);

			$PostID = PdoDataAccess::InsertID();
			PdoDataAccess::runquery("insert into baseinfo.PostAssignHistory (PersonID, PostID, AssignDate)
			values(" . $personID . "," . $PostID . "," . $date . ")");
		}
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->RelatedPersonID = $personID;
		if(count($temp) != 0)
			$daObj->SubObjectID = $temp[0]["PostID"];
		$daObj->MainObjectID = $post_id;
		$daObj->TableName = " baseinfo.posts";
		$daObj->description = $desc;
		$daObj->execute();
		//--------------------------------------------------------------
	}

	static function baseinfoRelease($personID, $post_id, $desc)
	{
		//------------------ baseinfo update ---------------------------
		PdoDataAccess::runquery("update baseinfo.posts set PostNumber=0,PostStatus='NOT_APPROVED' where PostNumber=" . $post_id);
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->RelatedPersonID = $personID;
		$daObj->MainObjectID = $post_id;
		$daObj->TableName = " baseinfo.posts";
		$daObj->description = $desc;
		$daObj->execute();

		//--------------------------------------------------------------
	}

	static function dropdown_post_type($dropName, $selectedId = "", $extraRow = "", $width = "90%",$event)
	{
		require_once inc_component;
		
		$obj = new DROPDOWN();
		
		$obj->datasource = parent::runquery("select * from Basic_Info where TypeID=27");
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="x-form-text x-form-field" style="width:' . $width . '"';
                $obj->Event = $event;
		$obj->id = $dropName;
	
		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("InfoID" => "-1", "Title" => $extraRow))	,$obj->datasource);
			
		return $obj->bind_dropdown($selectedId);
	}
        
        static function dropdown_sup_type($dropName, $selectedId = "", $extraRow = "", $width = "30%")
	{
		require_once inc_component;
		
		$obj = new DROPDOWN();
		
		$obj->datasource = parent::runquery("select * from Basic_Info where TypeID=42");
		$obj->valuefield = "%InfoID%";
		$obj->textfield = "%Title%";
		$obj->Style = 'class="x-form-text x-form-field" style="width:' . $width . '"';
                
		$obj->id = $dropName;
	
		if(!empty($extraRow))
			$obj->datasource = array_merge(array(array("InfoID" => "-1", "Title" => $extraRow))	,$obj->datasource);
			
		return $obj->bind_dropdown($selectedId);
	}

	public $post_id;
	public $ouid;
	public $post_rowno;
	public $post_no;
	public $post_type;
	public $title;
	public $included;
	public $jfid;
	public $validity_start;
	public $validity_end;
	public $description;
	public $parent_post_id;
	public $parent_path;
	public $is_dummy_post;
	public $staff_id;
        public $person_type;
	public $RegDate;
	public $_fullName;
	public $_jcid;
        public $SupervisionKind ; 
	public $ManagementCoef ; 
                

        static function GetAllPosts($where= "", $whereParam = array())
	{
	
	
		$query = "select p.*,concat(pfname,' ',plname) as fullname,o.ptitle as unitTitle,bi.Title as post_type_title
					,jc.title as jobCategory
            from position as p
				left join staff as s using(staff_id)
				left join persons as ps using(PersonID)
				left join org_new_units o on(o.ouid=p.ouid)
				left join Basic_Info as bi on(bi.InfoID=p.post_type and bi.TypeID=" . BINFTYPE_post_type . ")
				left join job_fields j on(j.jfid=p.jfid)
				left join job_category jc on(jc.jcid=j.jcid)
			";
		$query .= ($where != "") ? " where " . $where : "";
		
		$temp = parent::runquery($query, $whereParam);
		
		
		return $temp;
	}

	function  __construct($post_id = "")
	{
		$this->DT_validity_start = DataMember::CreateDMA(DataMember::DT_DATE);
		$this->DT_validity_end   = DataMember::CreateDMA(DataMember::DT_DATE);
		
		if($post_id == "")
			return;
		
		parent::FillObject($this, "select p.*,concat(pfname,' ',plname) as _fullName, jf.jcid as _jcid
			from `position` p
				left join staff as s using(staff_id)
                left join persons as ps using(PersonID)
				left join job_fields jf using(jfid)
				
			where p.post_id=?",	array($post_id));

	}

	function OnAfterUpdate()
	{
		//------------------ baseinfo update ---------------------------
		PdoDataAccess::runquery("update baseinfo.posts
				set title='" . $this->title . "' where PostNumber=" . $this->post_id);

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->post_id;
		$daObj->TableName = "baseinfo.posts";
		$daObj->execute();
		//--------------------------------------------------------------

	}

	function OnAfterInsert()
	{
		//------------------ baseinfo update ---------------------------
		/*PdoDataAccess::runquery("insert into baseinfo.posts(title,ouid,PostStatus,PostNumber)
				values('" . $this->title . "','" . $this->ouid . "','APPROVED'," . $this->post_id . ")");

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->post_id;
		$daObj->TableName = "baseinfo.posts";
		$daObj->execute();*/
		//--------------------------------------------------------------

	}


	static function OnBeforeDelete($post_id)
	{
		//------------------ baseinfo usage check ---------------------------
		$temp = PdoDataAccess::runquery("select * from baseinfo.ChartNodes join baseinfo.posts on(RelatedItemID=PostID AND NodeType='POST')
			where PostNumber=" . $post_id);
		if(count($temp) != 0)
			return false;
		return true;
		//--------------------------------------------------------------
	}

	static function OnAfterDelete($post_id)
	{
		//------------------ baseinfo delete ---------------------------
		$temp = PdoDataAccess::runquery("select * from baseinfo.ChartNodes join baseinfo.posts on(NodeType='POST' AND RelatedItemID=PostID)
			where PostNumber=" . $post_id);
		if(count($temp) > 0)
		{
			PdoDataAccess::runquery("update baseinfo.posts set PostNumber=0,PostStatus='NOT_APPROVED' where PostNumber=" . $post_id);

			$daObj = new DataAudit();
			$daObj->ActionType = DataAudit::Action_delete;
			$daObj->MainObjectID = $post_id;
			$daObj->TableName = "baseinfo.posts";
			$daObj->execute();
		}
		else
		{
			PdoDataAccess::runquery("delete from baseinfo.posts where PostNumber=" . $post_id);

			$daObj = new DataAudit();
			$daObj->ActionType = DataAudit::Action_delete;
			$daObj->MainObjectID = $post_id;
			$daObj->TableName = "baseinfo.posts";
			$daObj->execute();
		}
		//--------------------------------------------------------------
	}

	function AddPost()
	{
		$db = PdoDataAccess::getPdoObject();
		/*@var $db PDO*/
		$db->beginTransaction();
		
		$this->post_id = parent::GetLastID("`position`", "post_id") + 1;
		$this->RegDate = PDONOW;
	 	$return = parent::insert("`position`", $this);
	 	
	 	if($return === false)
	 	{
	 		$db->rollBack();
			return false;
	 	}
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->post_id;
		$daObj->TableName = "position";
		$daObj->execute();
		
		$db->commit();

		$this->OnAfterInsert();

		return true;	 	
	}
	 
	function EditPost()
	{
		$whereParams = array();
	 	$whereParams[":post_id"] = $this->post_id;
	 	
	 	$db = PdoDataAccess::getPdoObject();
	 	/*@var $db PDO*/
	 	$db->beginTransaction();
	 	
	 	parent::runquery("insert into position_history select *,'EDIT',now() from position where post_id=:post_id", $whereParams);
                                
	 	$return =  parent::update("position", $this, " post_id=:post_id", $whereParams);
	 	
	 	if($return === false)
	 	{
	 		$db->rollBack();
			return false;
	 	}

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->post_id;
		$daObj->TableName = "position";
		$daObj->execute();
			
	 	$db->commit();

		$this->OnAfterUpdate();

	 	return true;
	}
	 
	static function CountPosts($where = "", $whereParam = array())
	{
		$query = " select count(*) from position p";
		$query .= ($where != "") ? " where " . $where : "";		
		
		$temp = parent::runquery($query, $whereParam);
		return $temp[0][0];
	}
	
	static function RemovePost($post_id)
	{
		if(!self::OnBeforeDelete($post_id))
			return false;
			
	 	$whereParams = array();
	 	$whereParams[":post_id"] = $post_id;
	 	
	 	$db = parent::getPdoObject();
	 	/*@var $db PDO*/
	 	$db->beginTransaction();
	 	
	 	parent::runquery("insert into position_history select *,'DELETE',now() from position where post_id=:post_id", $whereParams);
	 	$return =  parent::delete("position", " post_id=:post_id", $whereParams);
	 	
	 	if($return == "0")
	 	{
	 		$db->rollBack();
			return false;
	 	}

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $post_id;
		$daObj->TableName = "position";
		$daObj->execute();
		
	 	$db->commit();

		self::OnAfterDelete();
		
	 	return true;
	}
}

define('POST_EXE_NORMAL',	1);
define('POST_EXE_MANAGER',	2);
define('POST_PROFESSOR_EDU',3);
define('POST_PROFESSOR_RSC',4);
define('POST_EXE_SUPERVICE',5);

define('JOB_LEVEL_COMMON',		1);
define('JOB_LEVEL_HIGH_DIPLOMA',2);
define('JOB_LEVEL_BS',			3);
define('JOB_LEVEL_HIGHT_BS',	4);

?>