<?php

//---------------------------
// programmer:	Mahdipour
// create Date:	97.06
//---------------------------

class manage_StaffPaidCostCode extends PdoDataAccess {

    public $SPID;
    public $StaffID;
    public $StartDate;
    public $EndDate;
    public $CostID;

    public function __construct($staff_id = "") {

        if ($staff_id != "") {
            $whereParam = array(":staff_id" => $staff_id);
            $query = "select * from StaffPaidCostCode where StaffID=:staff_id  ";
            PdoDataAccess::FillObject($this, $query, $whereParam);
        }

        $this->DT_StartDate = DataMember::CreateDMA(DataMember::DT_DATE);
        $this->DT_EndDate = DataMember::CreateDMA(DataMember::DT_DATE);
    }

    private function date_overlap($PID) {
        $whereParam = array();
        $whereParam[":pid"] = $PID;

        $query = "  select  count(*) cn
                        from staff_tax_history sth inner join staff s
                                                        on sth.staff_id = s.staff_id
                                                   inner join persons p
                                                        on s.personid = p.personid
                    where
                    p.personid =:pid  and payed_tax_value is null  and ( sth.end_date is null or sth.end_date = '0000-00-00' )
                                  ";

        $temp = PdoDataAccess::runquery($query, $whereParam);

        if ($temp[0][0] > 0 && $this->tax_history_id == null) {
            parent::PushException("لطفا ابتدا تاریخ انتهای رکورد قبل را پر نمایید.");
            return false;
        }


        $query = "	select count(*)
							from staff_tax_history sth inner join staff s
																on sth.staff_id = s.staff_id
														   inner join persons p
																on s.personid = p.personid
									where
											((sth.start_date <=:fdate AND sth.end_date>=:fdate) OR (sth.start_date>=:fdate AND sth.start_date <=:tdate)) AND
											  p.personid =:pid  ";



        $whereParam[":fdate"] = str_replace('/', '-', DateModules::shamsi_to_miladi($this->start_date));
        $whereParam[":tdate"] = str_replace('/', '-', DateModules::shamsi_to_miladi($this->end_date));

        if ($this->tax_history_id) {
            $query .= " and tax_history_id  != :ihid ";
            $whereParam[":ihid"] = $this->tax_history_id;
        }

        $temp = PdoDataAccess::runquery($query, $whereParam);

        if ($temp[0][0] != 0) {
            parent::PushException(ER_DATE_RANGE_OVERLAP);
            return false;
        }

        return true;
    }

    function SaveStaffCostCode($PID) {

        /*if (!$this->date_overlap($PID))
            return false;*/

        if (PdoDataAccess::insert("HRM_StaffPaidCostCode", $this) === false)
            return false;

        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_add;
        $daObj->RelatedPersonType = 3;
        $daObj->RelatedPersonID = $this->StaffID;
        $daObj->TableName = "StaffPaidCostCode";
        $daObj->execute();

        return true;
    }

    function EditStaffCostCode($PID) {

        /*if (!$this->date_overlap($PID))
            return false;*/

        $whereParams = array();
        $whereParams[":sid"] = $this->StaffID;
        $whereParams[":SPID"] = $this->SPID;
        
        
        if (PdoDataAccess::update("HRM_StaffPaidCostCode", $this, " StaffID =:sid  and SPID = :SPID ", $whereParams) === false)
            return false;

        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_update;
        $daObj->RelatedPersonType = 3;
        $daObj->RelatedPersonID = $this->StaffID;
        $daObj->TableName = "HRM_StaffPaidCostCode";
        $daObj->execute();

        return true;
    }


    static function GetAllStaffCostCode($personid) {

        $query = "  select sth.*  , 
						cc.CostCode,
						concat_ws(' - ',b1.BlockDesc,b2.BlockDesc,b3.BlockDesc) CostDesc
                    from HRM_StaffPaidCostCode sth 
                        inner join HRM_staff s on sth.StaffID = s.staff_id 
                        inner join HRM_persons p on p.personid = s.personid
						left join ACC_CostCodes cc using(CostID)
						left join ACC_blocks b1 on(level1=b1.BlockID)
						left join ACC_blocks b2 on(level2=b2.BlockID)
						left join ACC_blocks b3 on(level3=b3.BlockID)
                    where p.personid  = ?  " ;

        $temp = PdoDataAccess::runquery($query , array($personid));

        return $temp;
    }

    function Remove() {
        $result = parent::delete("HRM_StaffPaidCostCode", " StaffID = :SID and SPID = :ID ", array(":SID" => $this->StaffID,
                    ":ID" => $this->SPID));
        if ($result === false)
            return false;

        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_delete;
        $daObj->RelatedPersonID = $this->StaffID;
        $daObj->RelatedPersonType = 3;
        $daObj->MainObjectID = $this->SPID;
        $daObj->TableName = "HRM_StaffPaidCostCode";
        $daObj->execute();

        return true;
    }

}

?>