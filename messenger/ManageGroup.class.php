<?php
//---------------------------
// programmer:	bMahdipour
// create Date:	90.12.16
//---------------------------

class manage_msg_group extends PdoDataAccess
{
	 
	public $GID;
	public $GroupTitle;
    public $FileType;

	
	function __construct($GID="")
    {
       $this->DT_GID = DataMember::CreateDMA(DataMember::Pattern_Num);
       $this->DT_GroupTitle = DataMember::CreateDMA(DataMember::Pattern_FaEnAlphaNum);
       $this->DT_FileType = DataMember::CreateDMA(DataMember::Pattern_FaEnAlphaNum);                        
      
       if (!empty($GID)) {           
			$params = array($GID);
			$query = "select * from msg_group where GID =?";
			parent::FillObject($this, $query, $params);
		}
        
		return;
    }
	static function GetAll($where = "",$whereParam = array())
	{
       
		$query = "select * from msg_group ";
		$query .= ($where != "") ? " where " . $where : "";
        
		return parent::runquery($query, $whereParam);
	}
	 
	function Add()
	{
	 	if( parent::insert("msg_group", $this) === false )
			return false;

		$this->sfid = parent::InsertID();
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = PdoDataAccess::InsertID();
		$daObj->TableName = "msg_group";
		$daObj->execute();

		return true;
	}
	function Edit()
	{
	 	$whereParams = array();
	 	$whereParams[":GID"] = $this->GID;

	 	if( parent::update("msg_group",$this," GID=:GID", $whereParams) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->GID;
		$daObj->TableName = "msg_group";
		$daObj->execute();

	 	return true;
    }	
	 	 

	static function Remove($GID)
	{

        $res = parent::runquery(" select count(*) cn from msg_members where GID = ? " , array($GID) );

        if($res[0]['cn'] > 0 )
        {
            parent::PushException("این گروه دارای عضو می باشد و حذف آن امکان پذیر نمی باشد .");
            return false ;
        }

        $result = parent::delete("msg_group", "GID=:GID ", array(":GID" => $GID));
		
		if($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $GID;
		$daObj->TableName = "msg_group";
		$daObj->execute();

		return true;
	}
}

class manage_msg_members extends PdoDataAccess
{
	 
	public $MID;
	public $GID;
    public $PersonID;
    
	function __construct()
    {
        $this->DT_MID = DataMember::CreateDMA(DataMember::Pattern_Num);
        $this->DT_GID = DataMember::CreateDMA(DataMember::Pattern_Num);
        $this->DT_PersonID = DataMember::CreateDMA(DataMember::Pattern_Num);        
    }
	static function GetAll($where = "",$whereParam = array())
	{
		$query = "select m.*,p.fname,p.lname    
                  from msg_members m 
                    inner join bsc_persons p on m.PersonID = p.PersonID   ";
		$query .= ($where != "") ? " where " . $where : "";
		return parent::runquery($query, $whereParam);
	}
	 

	function Add()
	{
        
        if( parent::insert("msg_members", $this) === false )
			return false;
		
		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_add;
		$daObj->MainObjectID = PdoDataAccess::InsertID();
		$daObj->TableName = "msg_members";
		$daObj->execute();

		return true;
	}
	function Edit()
	{
	 	$whereParams = array();
	 	$whereParams[":GID"] = $this->GID;
        $whereParams[":MID"] = $this->MID;

	 	if( parent::update("msg_members",$this," GID=:GID and MID=:MID ", $whereParams) === false )
	 		return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_update;
		$daObj->MainObjectID = $this->GID;
        $daObj->SubObjectID = $this->MID;
		$daObj->TableName = "msg_members";
		$daObj->execute();

	 	return true;
    }
	
	static function Remove($GID,$MID)
	{
        
        $result = parent::delete("msg_members", "GID=:GID and MID=:MID", array(
                                                ":GID" => $GID , ":MID" => $MID ));
		
		if($result === false)
			return false;

		$daObj = new DataAudit();
		$daObj->ActionType = DataAudit::Action_delete;
		$daObj->MainObjectID = $GID;
        $daObj->SubObjectID = $MID;
		$daObj->TableName = "msg_members";
		$daObj->execute();

		return true;
	}
    
    static function GetAllMyMessage($where = "",$whereParam = array())
	{
		$query = "  select m.* ,concat(GroupTitle,'-',message,'-',MsgNo) TM , message LastMsg , t2.MsgNo
                    from msg_members m

                    inner join msg_group g on m.GID = g.GID

                    inner join msg_messages ms on ms.GID = m.GID

                    inner join (
                        select max(MSGID) maxID
                        from msg_messages
                        group by GID 
                    ) t on t.maxID = ms.MSGID
                    inner join (
                        select me.GID , count(*) MsgNo
                        from msg_messages me
                        inner join msg_members mb on me.GID = mb.GID
                                    left join msg_messagestatus st
                                         on me.MSGID = st.MSGID and me.MID = st.MID
                        where mb.PersonID = :PID and st.MSID is null
                        group by me.GID
                    ) t2 on t2.GID = ms.GID
                    
                    where m.PersonID = :PID
                    group by ms.GID
                ";
		$query .= ($where != "") ? " where " . $where : "";
		return parent::runquery($query, $whereParam);
	}
    
    static function GetAllGroupMessage($where = "",$whereParam = array())
	{
		$query = "  SELECT MSGID , GID , MID , concat(MSGID,'-',message) message
                    FROM msg_messages
                    order by MSGID ASC LIMIT 5 
                 ";
		$query .= ($where != "") ? " where " . $where : "";
		return parent::runquery($query, $whereParam);
	}
    
}
	




?>