//---------------------------
// programmer:	Jafarkhani
// create Date:	89.03
//---------------------------

function LOV_PersonID()
{
	returnVal = showLOV("/HumanResources/global/LOV/PersonLOV.php", 780, 430);
	return (returnVal) ? returnVal : "";
}

function LOV_staff()
{
	returnVal = showLOV("/HumanResources/global/LOV/StaffLOV.php", 780, 430);
	return (returnVal) ? returnVal : "";
}

function LOV_OrgUnit()
{
	returnVal = showLOV("/HumanResources/global/LOV/OrgUnitLOV.php", 500, 600);
	return (returnVal) ? returnVal : "";
}

function LOV_Post(v)
{
	returnVal = showLOV("/HumanResources/global/LOV/PostLOV.php?Param=" + v , 1200, 430);
	return (returnVal) ? returnVal : "";
}