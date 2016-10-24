<?php
//....................
function PMT($CustomerWage, $InstallmentCount, $PartAmount, $YearMonths, $PayInterval) {  
	
	if($CustomerWage == 0)
		return $PartAmount/$InstallmentCount;
	
	if($PayInterval == 0)
		return $PartAmount;
	
	$CustomerWage = $CustomerWage/($YearMonths*100);
	$PartAmount = -$PartAmount;
	return $CustomerWage * $PartAmount * pow((1 + $CustomerWage), $InstallmentCount) / (1 - pow((1 + $CustomerWage), $InstallmentCount)); 
} 
function ComputeInstallmentAmount($TotalAmount,$IstallmentCount,$PayInterval){
		
	if($PayInterval == 0)
		return $TotalAmount;

	return $TotalAmount/$IstallmentCount;
}

function ComputeWage($PartAmount, $CustomerWagePercent, $InstallmentCount, $YearMonths, $PayInterval){
	
	if($PayInterval == 0)
		return 0;
	
	if($PayInterval*1 > 0)
		$InstallmentCount = $InstallmentCount*$PayInterval;
	
	if($CustomerWagePercent == 0)
		return 0;
	
	return ((($PartAmount*$CustomerWagePercent/$YearMonths*
		( pow((1+($CustomerWagePercent/$YearMonths)),$InstallmentCount)))/
		((pow((1+($CustomerWagePercent/$YearMonths)),$InstallmentCount))-1))*$InstallmentCount)-$PartAmount;
}
function roundUp($number, $digits){
	$factor = pow(10,$digits);
	return ceil($number*$factor) / $factor;
}
function YearWageCompute($PartObj, $TotalWage, $YearMonths){

	/*@var $PartObj LON_ReqParts */
	
	$startDate = DateModules::miladi_to_shamsi($PartObj->PartDate);
	$startDate = DateModules::AddToJDate($startDate, $PartObj->DelayDays, $PartObj->DelayMonths); 
	$startDate = preg_split('/[\-\/]/',$startDate);
	$PayMonth = $startDate[1]*1;
	
	$FirstYearInstallmentCount = floor((12 - $PayMonth)/(12/$YearMonths));
	$FirstYearInstallmentCount = $PartObj->InstallmentCount < $FirstYearInstallmentCount ? 
			$FirstYearInstallmentCount - $PartObj->InstallmentCount : $FirstYearInstallmentCount;
	$MidYearInstallmentCount = floor(($PartObj->InstallmentCount-$FirstYearInstallmentCount) / $YearMonths);
	$MidYearInstallmentCount = $MidYearInstallmentCount < 0 ? 0 : $MidYearInstallmentCount;
	$LastYeatInstallmentCount = ($PartObj->InstallmentCount-$FirstYearInstallmentCount) % $YearMonths;
	$LastYeatInstallmentCount = $LastYeatInstallmentCount < 0 ? 0 : $LastYeatInstallmentCount;
	$F9 = $PartObj->InstallmentCount*(12/$YearMonths);
	
	$yearNo = 1;
	$StartYear = $startDate[0]*1;
	$returnArr = array();
	while(true)
	{
		if($yearNo > $MidYearInstallmentCount+2)
			break;
		
		$BeforeMonths = 0;
		if($yearNo == 2)
			$BeforeMonths = $FirstYearInstallmentCount;
		else if($yearNo > 2)
			$BeforeMonths = $FirstYearInstallmentCount + ($yearNo-2)*$YearMonths;

		$curMonths = $FirstYearInstallmentCount;
		if($yearNo > 1 && $yearNo <= $MidYearInstallmentCount+1)
			$curMonths = $YearMonths;
		else if($yearNo > $MidYearInstallmentCount+1)
			$curMonths = $LastYeatInstallmentCount;
		
		$BeforeMonths = $BeforeMonths*(12/$YearMonths);
		$curMonths = $curMonths*(12/$YearMonths);

		$val = (((($F9-$BeforeMonths)*($F9-$BeforeMonths+1))-
			($F9-$BeforeMonths-$curMonths)*($F9-$BeforeMonths-$curMonths+1)))/($F9*($F9+1))*$TotalWage;

		$returnArr[ $StartYear ] = $val;
		$StartYear++;
		$yearNo++;
	}
	
	return $returnArr;
}
function YearDelayCompute($PartObj, $PayDate, $PayAmount, $wage){
	
	$startDate = DateModules::miladi_to_shamsi($PayDate);
	$endDate = DateModules::AddToJDate($startDate, $PartObj->DelayDays, $PartObj->DelayMonths); 

	$arr = preg_split('/[\-\/]/',$startDate);
	$StartYear = $arr[0]*1;
	  
	$totalDays = 0;
	$yearDays = array();
	$newStartDate = $startDate;
	while(DateModules::CompareDate($newStartDate, $endDate) < 0){
		
		$arr = preg_split('/[\-\/]/',$newStartDate);
		$LastDayOfYear = DateModules::lastJDateOfYear($arr[0]);
		if(DateModules::CompareDate($LastDayOfYear, $endDate) > 0)
			$LastDayOfYear = $endDate;
		
		$yearDays[$StartYear] = DateModules::JDateMinusJDate($LastDayOfYear, $newStartDate)+1;
		$totalDays += $yearDays[$StartYear];
		$StartYear++;
		$newStartDate = DateModules::AddToJDate($LastDayOfYear, 1);
	}
	
	$DelayDuration = DateModules::JDateMinusJDate(
		DateModules::AddToJDate($startDate, $PartObj->DelayDays, $PartObj->DelayMonths), $startDate)+1;
	if($PartObj->DelayDays*1 == 0)
		$TotalDelayAmount = round($PayAmount*$wage*$PartObj->DelayMonths/1200);
	else
		$TotalDelayAmount = round($PayAmount*$wage*$DelayDuration/36500);

	$sum = 0;
	foreach($yearDays as $year => $days)
	{
		//$yearDays[$year] = round($PayAmount*$wage*$yearDays[$year]/36500);
		$yearDays[$year] = round($days*$TotalDelayAmount/$totalDays);
		$sum += $yearDays[$year];
	}
	if($sum <> $TotalDelayAmount)
		$yearDays[$year] += $TotalDelayAmount-$sum;
	
	return $yearDays;
}
//....................
?>
