<?php

//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.10
//-----------------------------
require_once inc_UserRole;
class CNT_UserAccess extends PdoDataAccess {

    const DeputyID = 12;
    const CntUnitAgent = 102; //کارشناس واحد قراردادها
    

    private static function GetUserAccessInfo($UserID = '', $UnitID = 0, $RoleID = 0) {
        if ($UserID == '')
            $UserID = $_SESSION["UserID"];
        $roles = UserRole::GetUserRole(self::DeputyID, $UserID);
          
        $res1 = array();
        if ($UnitID > 0) {
            foreach ($roles as $role) {
                if ($role->AccUnitID == $UnitID || $role->AccUnitID == 0) {
                    $res1[] = $role;
                }
            }
        } else {
            $res1[] = $roles;
        }
        $res = array();
        if ($RoleID > 0) {
            foreach ($res1 as $role) {
                if ($role->UserRole == $RoleID) {
                    $res[] = $role;
                }
            }
        } else {
            $res[] = $res1;
        }        
        return $res;
    }

    public static function IsAllowedToConfirmDocs($ObjectID , $ObjectType) {
        switch ($ObjectType) {
            case 'CONTRACT':
                require_once __DIR__ . '/contract.class.php';
                $obj = new CNT_contracts($ObjectID);
                $UnitID = $obj->UnitID;
                break;
            default:
                $UnitID = -1;
                break;
        }
        $Roles = self::GetUserAccessInfo('',$UnitID,self::CntUnitAgent);
        if (count($Roles)>0)
            return true;
        return false;
    }

}
