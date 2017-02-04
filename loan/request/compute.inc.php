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

function ComputeWage($PartAmount, $CustomerWagePercent, $InstallmentCount, $IntervalType, $PayInterval){
	
	if($PayInterval == 0)
		return 0;
	
	if($CustomerWagePercent == 0)
		return 0;
	
	if($IntervalType == "DAY")
		$PayInterval = $PayInterval/30;
		
	$R = ($CustomerWagePercent/12)*$PayInterval;
	$F7 = $PartAmount;
	$F9 = $InstallmentCount;
	return ((($F7*$R*pow(1+$R,$F9))/(pow(1+$R,$F9)-1))*$F9)-$F7;
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
			$PartObj->InstallmentCount : $FirstYearInstallmentCount;
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
function ComputeWageOfSHekoofa($partObj){
	
	$payments = LON_payments::Get(" AND RequestID=?", array($partObj->RequestID), " order by PayDate");
	$payments = $payments->fetchAll();
	//--------------- total pay months -------------
	$firstPay = DateModules::miladi_to_shamsi($payments[0]["PayDate"]);
	$LastPay = DateModules::miladi_to_shamsi($payments[count($payments)-1]["PayDate"]);
	$paymentPeriod = DateModules::GetDiffInMonth($firstPay, $LastPay);
	//----------------------------------------------
	$totalWage = 0;
	$wages = array();
	foreach($payments as $row)
	{
		$wages[] = array();
		$wageindex = count($wages)-1;
		for($i=0; $i < $partObj->InstallmentCount; $i++)
		{
			$monthplus = $paymentPeriod + $partObj->DelayMonths*1 + ($i+1)*$partObj->PayInterval*1;
			
			$installmentDate = DateModules::miladi_to_shamsi($payments[0]["PayDate"]);
			$installmentDate = DateModules::AddToJDate($installmentDate, 0, $monthplus);
			$installmentDate = DateModules::shamsi_to_miladi($installmentDate);
			
			$jdiff = DateModules::GDateMinusGDate($installmentDate, $row["PayDate"]);
			
			$wage = round(($row["PayAmount"]/$partObj->InstallmentCount)*$jdiff*$partObj->CustomerWage/36500);
			$wages[$wageindex][] = $wage;
			$totalWage += $wage;
		}
	}
	
	return $totalWage;
}

function SplitYears($startDate, $endDate, $TotalAmount){
	
	$startDate = DateModules::miladi_to_shamsi($startDate);
	$endDate = DateModules::miladi_to_shamsi($endDate);
	
	if(substr($startDate,0,1) == 2)
		$startDate = DateModules::miladi_to_shamsi ($startDate);
	if(substr($endDate,0,1) == 2)
		$endDate = DateModules::miladi_to_shamsi ($endDate);
	
	
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
	$TotalDays = DateModules::JDateMinusJDate($endDate, $startDate)+1;
	$sum = 0;
	foreach($yearDays as $year => $days)
	{
		$yearDays[$year] = round(($days/$TotalDays)*$TotalAmount);
		$sum += $yearDays[$year];
		
		//echo  $year . " " . $days . " " . $yearDays[$year] . "\n";
	}
	
	if($sum <> $TotalAmount)
		$yearDays[$year] += $TotalAmount-$sum;
	
	return $yearDays;
}

function ComputeWagesAndDelays($PartObj, $PayAmount, $StartDate, $PayDate){
	$MaxWage = max($PartObj->CustomerWage*1 , $PartObj->FundWage);
	if($PartObj->PayInterval > 0)
		$YearMonths = ($PartObj->IntervalType == "DAY" ) ? 
			floor(365/$PartObj->PayInterval) : 12/$PartObj->PayInterval;
	else
		$YearMonths = 12;
	
	$TotalWage = round(ComputeWage($PayAmount, $MaxWage/100, $PartObj->InstallmentCount, 
			$PartObj->IntervalType, $PartObj->PayInterval));	
	
	$CustomerFactor =	$MaxWage == 0 ? 0 : $PartObj->CustomerWage/$MaxWage;
	$FundFactor =		$MaxWage == 0 ? 0 : $PartObj->FundWage/$MaxWage;
	$AgentFactor =		$MaxWage == 0 ? 0 : ($PartObj->CustomerWage-$PartObj->FundWage)/$MaxWage;
	
	///...........................................................
	if($PartObj->MaxFundWage*1 > 0)
	{
		if($PartObj->WageReturn == "INSTALLMENT")
			$FundYears = YearWageCompute($PartObj, $PartObj->MaxFundWage*1, $YearMonths);
		else 
			$FundYears = array();
	}	
	else
	{
		$years = YearWageCompute($PartObj, $TotalWage*1, $YearMonths);
		$FundYears = array();
		foreach($years as $year => $amount)
			$FundYears[$year] = round($FundFactor*$amount);
	}	
	$AgentYears = array();
	foreach($years as $year => $amount)
		$AgentYears[$year] = round($amount - $FundYears[$year]);
	//.............................................................
	$endDelayDate = DateModules::AddToGDate($PayDate, $PartObj->DelayDays*1, $PartObj->DelayMonths*1);
	$DelayDuration = DateModules::GDateMinusGDate($endDelayDate, $PayDate)+1;
	if($StartDate == $PayDate)
	{
		if($PartObj->DelayDays*1 > 0)
		{
			$CustomerDelay = round($PayAmount*$PartObj->DelayPercent*$DelayDuration/36500);
			$FundDelay = round($PayAmount*$PartObj->FundWage*$DelayDuration/36500);
			$AgentDelay = round($PayAmount*($PartObj->DelayPercent - $PartObj->FundWage)*$DelayDuration/36500);		
		}
		else
		{
			$CustomerDelay = round($PayAmount*$PartObj->DelayPercent*$PartObj->DelayMonths/1200);
			$FundDelay = round($PayAmount*$PartObj->FundWage*$PartObj->DelayMonths/1200);
			$AgentDelay = round($PayAmount*($PartObj->DelayPercent - $PartObj->FundWage)*$PartObj->DelayMonths/1200);
		}
	}
	else
	{
		$endDelayDate = DateModules::AddToGDate($StartDate, $PartObj->DelayDays*1, $PartObj->DelayMonths*1);
		$DelayDuration = DateModules::GDateMinusGDate($endDelayDate, $PayDate)+1;
		$CustomerDelay = round($PayAmount*$PartObj->DelayPercent*$DelayDuration/36500);
		$FundDelay = round($PayAmount*$PartObj->FundWage*$DelayDuration/36500);
		$AgentDelay = round($PayAmount*($PartObj->DelayPercent - $PartObj->FundWage)*$DelayDuration/36500);		
	}	
	$CustomerYearDelays = SplitYears($PayDate, $endDelayDate, $CustomerDelay);
	//.............................................................
	
	return array(
		"TotalFundWage" => round($TotalWage*$FundFactor),
		"TotalAgentWage" => round($TotalWage*$AgentFactor),
		"TotalCustomerWage" => round($TotalWage*$CustomerFactor),
		"FundWageYears" => $FundYears,
		"AgentWageYears" => $AgentYears,
		
		"TotalCustomerDelay" => $CustomerDelay,
		"TotalFundDelay" => $FundDelay,
		"TotalAgentDelay" => $AgentDelay,
		"CustomerYearDelays" => $CustomerYearDelays
	);
}
?>
