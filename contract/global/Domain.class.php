<?php

class Domain extends PdoDataAccess {

    static function FillSessions() {
       /* $dt = PdoDataAccess::runquery("select STO_UserState.* , periods.*,  
            STO_units.UnitID, 
            STO_units.UnitName, 
            STO_units.FacCode
            from STO_UserState 
            join STO_units using (UnitID) 
            join periods using(PeriodID)
	    where PersonID=" . $_SESSION["User"]->PersonID);

        if (count($dt) > 0) {
            $_SESSION["STOREUSER"] = $dt[0];
            //$_SESSION["PURCHASEUSER"]["PeriodID"] = $_SESSION["STOREUSER"]['PeriodID'];
            $dt = PdoDataAccess::runquery("select * from PCH_UserState join periods using(PeriodID)
                        where PersonID=" . $_SESSION["User"]->PersonID);
            if (count($dt) > 0)
                $_SESSION["PURCHASEUSER"] = $dt[0];
        }*/
    }

}