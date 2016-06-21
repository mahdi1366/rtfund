<?php

//---------------------------
// programmer:	Jafarkhani
// create Date:	91.04
//---------------------------

class manage_bases extends PdoDataAccess {

	public $RowID;
	public $PersonID;
	public $BaseType;
	public $BaseValue;
	public $RegDate;
	public $ExecuteDate;
	public $BaseMode;
	public $ExtraInfo;
	public $BaseStatus;

	function __construct() {
		$this->DT_ExecuteDate = DataMember::CreateDMA(DataMember::DT_DATE);
	}
	static function GetAll($where = "", $whereParam = array()) {
		
		$query = "select b.*,i.Title as typeName , concat(pfname,' ',plname) as fullName
			from bases b
			join persons p using(PersonID)
			join Basic_Info i on(i.TypeID=43 AND i.InfoID=BaseType)";
		$query .= ($where != "") ? " where " . $where : "";
		
		return parent::runquery($query, $whereParam);
	}

	function Add() {
		if (parent::insert("bases", $this) === false)
			return false;

		$this->RowID = parent::InsertID();

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->RowID;
		$daObj->TableName = "bases";
		$daObj->execute();

		return true;
	}

	function Edit() {
		$whereParams = array();
		$whereParams[":r"] = $this->RowID;

		if (parent::update("bases", $this, " RowID=:r", $whereParams) === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->RowID;
		$daObj->TableName = "bases";
		$daObj->execute();

		return true;
	}

	static function Remove($RowID) {

		$result = parent::runquery("update bases set BaseStatus='DELETED' where RowID=:r ", array(":r" => $RowID));

		if ($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $RowID;
		$daObj->TableName = "bases";
		$daObj->execute();

		return true;
	}
	
	function AddMilitaryBase($PID)
	{
	    
	    $qry = " select * 
			    from bases 
				where baseType = 1 and baseValue = 1 and   personid =".$PID ; 
	    
	    $resRec = parent::runquery($qry) ; 
	    
	    $query = "	select  personid ,
				if ((military_duration > (DATEDIFF( military_to_date ,military_from_date))/30.4375 ) ,military_duration , (DATEDIFF( military_to_date ,military_from_date))/30.4375 ) duration

			    from persons
				where sex = 1 and person_type in (2,3,5) and personid = ".$PID ; 
	    
	    $res = parent::runquery($query) ; 
	    
	    if($res[0]['duration']/12 > 1 && count($resRec) == 0 )
		{
		
		$this->PersonID = $PID ; 
		$this->BaseType = 1 ; 
		$this->BaseValue = 1 ; 
		$this->RegDate = DateModules::Now() ; 
		$this->ExecuteDate = DateModules::Now() ; 
		$this->BaseMode = 'SYSTEM' ; 
		$this->ExtraInfo = $res[0]['duration'] ; 
		$this->BaseStatus = 'NORMAL' ; 
				
		$result = parent::insert($query, $this) ;
		
		if ($result === false)
			return false;		
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = $this->PersonID ;
		$daObj->SubObjectID = $this->BaseType ; 
		$daObj->TableName = "bases";
		$daObj->execute();

		return true;
		
		}
		
		else if ($res[0]['duration']/12 < 1 && count($resRec) == 1 ) 
		{
		    $this->BaseStatus = 'DELETED' ; 
		    $result = parent::update("bases", $this , "BaseType = 1 and  BaseValue =1 and personid =".$PID ) ; 
		    
		    if ($result === false)
			return false;		
		
		    $daObj = new DataAudit();
		    $daObj->ActionType = DataAudit::Action_update;
		    $daObj->MainObjectID = $this->PersonID ;
		    $daObj->SubObjectID = $this->BaseStatus ; 
		    $daObj->TableName = "bases";
		    $daObj->execute();

		    return true;
			
		}
		
		else if ($res[0]['duration']/12 > 1 && count($resRec) == 1)
		{
		    if($resRec[0]['BaseStatus'] == 'DELETED' )
		    {	
			$this->BaseStatus = 'NORMAL' ; 
			$result = parent::update("bases", $this , "BaseType = 1 and  BaseValue =1 and personid =".$PID ) ; 

			if ($result === false)
			    return false;		

			$daObj = new DataAudit();
			$daObj->ActionType = DataAudit::Action_update;
			$daObj->MainObjectID = $this->PersonID ;
			$daObj->SubObjectID = $this->BaseStatus ; 
			$daObj->TableName = "bases";
			$daObj->execute();

			return true; 
		    
		    }
		    else 
			return true ;

		}
	    
	}
}

?>