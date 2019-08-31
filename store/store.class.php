<?php
//-----------------------------
//	Programmer	: SH.Jafarkhani
//	Date		: 98.03
//-----------------------------

class STO_goods extends OperationClass {

	const TableName = "STO_goods";
	const TableKey = "GoodID"; 
 
    public $GoodID;
    public $ParentID;
    public $GoodName;
    public $ScaleID;
    public $depreciateType;
    public $depreciateRatio;
    public $CostID;
    public $IsActive;
    public $IncreasePriceRatio;


    static function GetAll($where = "", $whereParam = array(), $SelActive = 1) {
        $query = "
			select g.*, g3.GoodName p1Desc, g2.GoodName p2Desc , bi.title as GoodScale
                from STO_goods g
				join STO_goods g2 on(g.ParentID=g2.GoodID)
				join STO_goods g3 on(g2.ParentID=g3.GoodID)
				left join BaseInfo  bi on(bi.typeID=94 AND bi.InfoID=g.ScaleID) 
								
			where 1=1 
		";

        if ($SelActive > 0)
            $query .= " AND g.IsActive='YES'  ";
        $query .= ($where != "") ? " AND " . $where : "";
        return parent::runquery_fetchMode($query, $whereParam);
    }

    //-------------For Reports

    /* قیمت اولیه ، با افزایش و یا کاهش های بها - قیمت های تعمیراترا محاسبه می کند
     */
    function GetSumAmountAtStartOfPeriod($where = '', $whereParams = array()) {
        if ($UnitID == '')
            $UnitID = $_SESSION["STOREUSER"]["UnitID"];
        if (!empty($UnitID)) {
            $Where_Unit = ' AND ad.UnitID = :uid';
            $whereParams[':uid'] = $UnitID;
        } else {
            $Where_Unit = ' AND 1=1 ';
        }
        $query = "
                select 
                    IFNULL(sum(a.amount), 0) + IFNULL(sum(bi.param1 * adi.ChangeAmountType), 0) - IFNULL(sum(adi.FixAmount), 0) as res
                from
                    STO_assets a
                join STO_goods g USING (GoodID)
                left join STO_AssetDocItems adi USING (AssetID)
                left join STO_AssetDocs ad on ( ad.DocID = adi.DocID 
                                                and !(ad.DocMode = 'inner' and ad.DocType = 1) /*we get amount from a.amout*/
                                                and ad.DocStatus = 'ACC_CONFIRM' 
                                                and ad.PeriodID < " . $_SESSION['STOREUSER']['PeriodID'] . ")
                left join STO_BaseInfo bi ON (bi.TypeID = 5 and adi.ChangeAmountType = bi.InfoID)
                 where $where $Where_Unit
           ";
        $res = parent::runquery($query, $whereParams);
//echo PdoDataAccess::GetLatestQueryString().'<br><br>';

        return $res[0]['res'];
    }

    function GetSumAddedAmounts($where = '', $whereParams = array(), $UnitID = '') {
        if ($UnitID == '')
            $UnitID = $_SESSION["STOREUSER"]["UnitID"];
        /* گزارش برای یک واحد خاص باشد */
        if (!empty($UnitID)) {
            $Where_Unit = ' AND ad.UnitID = :uid';
            $whereParams[':uid'] = $UnitID;
        } else {
            $Where_Unit = ' AND 1=1 ';
        }
        /* برگشت از تعمیر و وبرگشت از اجاره و فلان .... ؟ */
        $query = "
            /* Changes in amounts*/
            (select IFNULL(sum(adi.ChangeAmountType),0) as res
            from STO_assets a
            join STO_goods g using (GoodID)
            join STO_AssetDocItems adi using (AssetID)
            join STO_AssetDocs ad using (DocID)
            join STO_BaseInfo bi on (bi.TypeID = 4 and adi.ChangeAmountType = bi.InfoID)
            where $where $Where_Unit and ad.DocStatus='ACC_CONFIRM' and ad.PeriodID = " . $_SESSION['STOREUSER']['PeriodID'] . "
                and !(ad.DocMode='inner' and ad.DocType=1) and adi.amount>0 and bi.param1>0)
            UNION
            ( /* If a new asset is bought within this period*/
            select IFNULL(sum(a.amount),0) as res
            from STO_assets a
            join STO_goods g using (GoodID)
            join STO_AssetDocItems adi using (AssetID)
            join STO_AssetDocs ad using (DocID)
            where $where $Where_Unit and ad.DocStatus='ACC_CONFIRM' and ad.PeriodID = " . $_SESSION['STOREUSER']['PeriodID'] . "
                and ad.DocMode='inner' and ad.DocType=1
            )";
        $res = parent::runquery($query, $whereParams);

        return $res[0]['res'] + $res[1]['res'];
    }

    function GetSumMinusAmounts($where = '', $whereParams = array(), $UnitID = '') {
        if ($UnitID == '')
            $UnitID = $_SESSION["STOREUSER"]["UnitID"];
        /* گزارش برای یک واحد خاص باشد */
        if (!empty($UnitID)) {
            $Where_Unit = ' AND ad.UnitID = :uid';
            $whereParams[':uid'] = $UnitID;
        } else {
            $Where_Unit = ' AND 1=1 ';
        }
        /* هزینه های کسری : 
         * هزینه تعمیر
         * برگه های تغییر بها که بهای منفی دارند
         */
        /* کالاهای انتقالی و فروش و هدا  و ......؟ */
        $query = "           
            /*FIX amounts*/
            (
                select IFNULL(sum(adi.FixAmount),0) as res
                from STO_assets a
                join STO_goods g using (GoodID)
                join STO_AssetDocItems adi using (AssetID)
                join STO_AssetDocs ad on (ad.DocID = adi.DocID and ad.DocType=14)  /*takmile tamirat Docs*/              
                where $where $Where_Unit and ad.DocStatus='ACC_CONFIRM' and ad.PeriodID = " . $_SESSION['STOREUSER']['PeriodID'] . "                    
            )   
            /*Change amounts*/
            UNION (
                select IFNULL(sum(adi.ChangeAmountType),0) as res
                from STO_assets a
                join STO_goods g using (GoodID)
                join STO_AssetDocItems adi using (AssetID)
                join STO_AssetDocs ad on (ad.DocID = adi.DocID and ad.DocType= 15 )                
                join STO_BaseInfo bi on (bi.TypeID = 4 and adi.ChangeAmountType = bi.InfoID and bi.param1<0)
                where $where $Where_Unit and ad.DocStatus='ACC_CONFIRM' and ad.PeriodID = " . $_SESSION['STOREUSER']['PeriodID'] . "                    
            )";
        $res = parent::runquery($query, $whereParams);
        //echo parent::getLatestQueryString().'</br></br>';     
        return $res[0]['res'] + $res[1]['res'];
    }

    //////////////////////////

    function GetSumAmountBetweenPeriod($where = '', $UnitID, $from, $to) {
//       if ($UnitID == '')
//      $UnitID = $_SESSION["STOREUSER"]["UnitID"];
        if (!empty($UnitID)) {
            $Where_Unit = ' AND ad.UnitID = :uid';
            $whereParams[':uid'] = $UnitID;
        } else {
            $Where_Unit = ' AND ad.UnitID in (SELECT  o1.StoreUnitID AS UnitID
                                    FROM accountancy.units o1
                                     LEFT JOIN accountancy.`COM_offices` o2 ON (o2.OfficeID = o1.UnitID )
WHERE o1.`storeunitid` IS NOT NULL
AND o1.`UnitID` NOT IN(512,24,516,523)
ORDER BY UnitName) ';
        }
//    echo $UnitID;
        $query = "
                select 
                    IFNULL(sum(a.amount), 0) + IFNULL(sum(bi.param1 * adi.ChangeAmountType), 0) - IFNULL(sum(adi.FixAmount), 0) as res
                from
                    STO_assets a
                join STO_goods g USING (GoodID)
                left join STO_AssetDocItems adi USING (AssetID)
                left join STO_AssetDocs ad on ( ad.DocID = adi.DocID 
                                                and !(ad.DocMode = 'inner' and ad.DocType = 1) /*we get amount from a.amout*/
                                                and ad.DocStatus = 'ACC_CONFIRM' 
                                                  AND ad.PeriodID between $from and $to)
                left join STO_BaseInfo bi ON (bi.TypeID = 5 and adi.ChangeAmountType = bi.InfoID)
                 where $where $Where_Unit
           ";
        $res = parent::runquery($query, $whereParams);
//echo PdoDataAccess::GetLatestQueryString().'<br><br>';
        return $res[0]['res'];
    }

    function GetSumAddedAmountBetweenPeriod($where = '', $UnitID, $from, $to) {
//    if ($UnitID == '')
//      $UnitID = $_SESSION["STOREUSER"]["UnitID"];
        /* گزارش برای یک واحد خاص باشد */
        if (!empty($UnitID)) {
            $Where_Unit = ' AND ad.UnitID = :uid';
            $whereParams[':uid'] = $UnitID;
        } else {
            $Where_Unit = ' AND ad.UnitID in (SELECT  o1.StoreUnitID AS UnitID
                                    FROM accountancy.units o1
                                     LEFT JOIN accountancy.`COM_offices` o2 ON (o2.OfficeID = o1.UnitID )
WHERE o1.`storeunitid` IS NOT NULL
AND o1.`UnitID` NOT IN(512,24,516,523)
ORDER BY UnitName) ';
        }
        /* برگشت از تعمیر و وبرگشت از اجاره و فلان .... ؟ */
        // typeid=4 & parm1=1 افزایش 
        $query = "
            /* Changes in amounts*/
            (select IFNULL(sum(adi.ChangeAmountType),0) as res
            from STO_assets a
            join STO_goods g using (GoodID)
            join STO_AssetDocItems adi using (AssetID)
            join STO_AssetDocs ad using (DocID)
            join STO_BaseInfo bi on (bi.TypeID = 4 and adi.ChangeAmountType = bi.InfoID)
            where $where $Where_Unit and ad.DocStatus='ACC_CONFIRM' and ad.PeriodID between $from and $to 
                and !(ad.DocMode='inner' and ad.DocType=1) and adi.amount>0 and bi.param1>0)
            UNION
            ( /* If a new asset is bought within this period */
            select IFNULL(sum(a.amount),0) as res
            from STO_assets a
            join STO_goods g using (GoodID)
            join STO_AssetDocItems adi using (AssetID)
            join STO_AssetDocs ad using (DocID)
            where $where $Where_Unit and ad.DocStatus='ACC_CONFIRM' and ad.PeriodID between $from and $to 
                and ad.DocMode='inner' and ad.DocType=1
            )";
        $res = parent::runquery($query, $whereParams);

        return $res[0]['res'] + $res[1]['res'];
    }

    function GetSumMinusAmountBetweenPeriod($where = '', $UnitID, $from, $to) {
//    if ($UnitID == '')
//      $UnitID = $_SESSION["STOREUSER"]["UnitID"];
        /* گزارش برای یک واحد خاص باشد */
        if (!empty($UnitID)) {
            $Where_Unit = ' AND ad.UnitID = :uid';
            $whereParams[':uid'] = $UnitID;
        } else {
            $Where_Unit = ' AND ad.UnitID in (SELECT  o1.StoreUnitID AS UnitID
                                    FROM accountancy.units o1
                                     LEFT JOIN accountancy.`COM_offices` o2 ON (o2.OfficeID = o1.UnitID )
WHERE o1.`storeunitid` IS NOT NULL
AND o1.`UnitID` NOT IN(512,24,516,523)
ORDER BY UnitName) ';
        }
        /* هزینه های کسری : 
         * هزینه تعمیر
         * برگه های تغییر بها که بهای منفی دارند
         */
        /* کالاهای انتقالی و فروش و هدا  و ......؟ */
        $query = "           
             /*takmile tamirat Docs*/  
            (
                select IFNULL(sum(adi.FixAmount),0) as res
                from STO_assets a
                join STO_goods g using (GoodID)
                join STO_AssetDocItems adi using (AssetID)
                join STO_AssetDocs ad on (ad.DocID = adi.DocID and ad.DocType=14)             
                where $where $Where_Unit and ad.DocStatus='ACC_CONFIRM' and ad.PeriodID between $from and $to     
            )   
            /*Change amounts*/
            UNION (
                select IFNULL(sum(adi.ChangeAmountType),0) as res
                from STO_assets a
                join STO_goods g using (GoodID)
                join STO_AssetDocItems adi using (AssetID)
                join STO_AssetDocs ad on (ad.DocID = adi.DocID and ad.DocType= 15 )                
                join STO_BaseInfo bi on (bi.TypeID = 3 and adi.ChangeAmountType = bi.InfoID and bi.param1<0 )
                where $where $Where_Unit and ad.DocStatus='ACC_CONFIRM' and ad.PeriodID between $from and $to 
            )
              /*اسقاطی=انتقالی به انبار اسقاط*/ 
            UNION (
               select IFNULL(sum(a.amount), 0) as res
                from STO_assets a
                join STO_goods g using (GoodID)
                join STO_AssetDocItems adi using (AssetID)
                join STO_AssetDocs ad on (ad.DocID = adi.DocID and ad.DocType=4 and ad.UnitID=8659)      
                where $where $Where_Unit and ad.DocStatus='ACC_CONFIRM' and ad.PeriodID between $from and $to     
            )
            ";
        $res = parent::runquery($query, $whereParams);
//    echo parent::getLatestQueryString().'</br></br>';     
        return $res[0]['res'] + $res[1]['res'];
    }

    function GetSummaryAmount($where = '') {
//echo '<hr>'.$where;
        $query = "
                SELECT
                 SUM(
                   STO_StoreDocItems.Goodcount * STO_StoreDocItems.GoodAmount
                 ) AS amount
               FROM
                 STO_StoreDocItems
                 JOIN STO_StoreDocs USING (DocID)
                 JOIN `STO_units` u
                   ON u.`UnitID` = `STO_StoreDocs`.`UnitID`
                 JOIN STO_goods g USING (GoodID)
                 JOIN STO_BaseInfo
                   ON (
                     STO_BaseInfo.typeID = 5
                     AND STO_BaseInfo.InfoID = DocMode
                   )
               WHERE  
                 $where
                 AND DocType NOT IN (11, 3)
                 AND DocStatus IN ('ACC_CONFIRM')
               HAVING SUM(STO_BaseInfo.param1 * GoodCount) > 0
               ";
        $res = parent::runquery($query);
if($res)
        return $res[0]['amount'];
else return 0;
    }

    //-------------------------

   
    static function MozoonMean($GoodID, $ToDate, $UnitID = '', $DocID = 0, $periodID = 0, $echoQuery = false) {
//    echo $UnitID;
        if ($periodID == 0)
            $periodID = $_SESSION["STOREUSER"]["PeriodID"];
        if ($UnitID == '')
            $UnitID = $_SESSION["STOREUSER"]["UnitID"];

        $q = "SELECT case when DocMode in(1,13) then 'in' else 'out' end DocMode, 
				GoodAmount , GoodCount
        FROM
            accountancy.STO_StoreDocItems
            join accountancy.STO_StoreDocs USING (DocID)
        where GoodID = :g and DocStatus='ACC_CONFIRM' and DocDate <= :date and DocID < :doc
                and STO_StoreDocs.PeriodID = :p
                and UnitID = :u
		order by DocDate,DocID
        ";
        $res = PdoDataAccess::runquery($q, array(
                    ":g" => $GoodID,
                    ":date" => $ToDate,
                    ":p" => $periodID,
                    ":u" => $UnitID,
                    ":doc" => $DocID
        ));

        if ($_SESSION["UserID"] == "sabbaghi") {
            if ($echoQuery)
                echo PdoDataAccess::GetLatestQueryString();
            //print_r($res);
        }

        $GoodCount = 0;
        $TotalAmount = 0;
        foreach ($res as $row) {
            if ($row["DocMode"] == "in") {
                $GoodCount += $row["GoodCount"];
                $TotalAmount += $row["GoodAmount"] * $row["GoodCount"];
            } else {
                if ($GoodCount == 0) {
                    $TotalAmount = 0;
                    $GoodCount = 0;
                } else {
                    $TotalAmount = ($TotalAmount / $GoodCount) * ($GoodCount - $row["GoodCount"]);
                    $GoodCount -= $row["GoodCount"];
                }
            }
        }

        return $GoodCount == 0 ? 0 : $TotalAmount / $GoodCount;

        /* $q = "SELECT sum(GoodAmount * GoodCount) / sum(GoodCount) as GoodAmount
          FROM
          accountancy.STO_StoreDocItems
          join accountancy.STO_StoreDocs USING (DocID)
          where
          DocMode in (1,13)  and GoodID = $GoodID and DocStatus='ACC_CONFIRM' and DocDate <= '$ToDate'
          and STO_StoreDocs.PeriodID = " . $periodID . "
          and UnitID = " . $UnitID . "
          ";
          $res = PdoDataAccess::runquery($q);
          //echo PdoDataAccess::GetLatestQueryString().'</br>';
          return $res[0]["GoodAmount"]; */
    }

    /* محاسبه موجودی کالا در حال حاضر ، برای چک کردن مقدار درج شده در قبض های انبار وغیره */

    static function GetCurrentCount($GoodID) {


        $query = "
		SELECT GoodID, GoodName,sum(bi1.param1*GoodCount) cnt                
		FROM STO_StoreDocItems join STO_StoreDocs using(DocID)
		join STO_goods using(GoodID)
		join STO_BaseInfo bi1 on(bi1.typeID=5 AND bi1.InfoID=DocMode)
                left join STO_BaseInfo bi2 on(bi2.typeID=6 AND bi2.InfoID=STO_goods.ScaleID)

		where UnitID=:uid AND PeriodID=:pid AND DocType not in(11,3) AND DocStatus!='RAW' AND STO_goods.GoodID = :gid";  //Modified to show also goods that are not confirmed by ghorbani

        $param = array(
            ":uid" => $_SESSION["STOREUSER"]["UnitID"],
            ":pid" => $_SESSION["STOREUSER"]["PeriodID"],
            ":gid" => $GoodID
        );

        $query .= " group by GoodID";
        $res = PdoDataAccess::runquery($query, $param);
        return $res[0]['cnt'];
    }

    static function IsGoodUsedInADoc($GoodID) {
        $res_a = parent::runquery("select count(*) from STO_assets where GoodID = ?", array($GoodID));
        if ($res_a[0]['count(*)'] > 0)
            return true;

        $res_di = parent::runquery("select count(*) from STO_StoreDocItems where GoodID = ?", array($GoodID));
        if ($res_di[0]['count(*)'] > 0)
            return true;

        $res_st = parent::runquery("select count(*) from STO_StockTakingDocItems where GoodID = ?", array($GoodID));
        if ($res_st[0]['count(*)'] > 0)
            return true;

        $res_st = parent::runquery("select count(*) from PCH_ReqItems where GoodID = ?", array($GoodID));
        if ($res_st[0]['count(*)'] > 0)
            return true;

        return false;
    }

    static function IsGoodNameDuplicate($GoodName, $where = '1=1', $whereParams = array()) {
        $whereParams[":gn"] = preg_replace('/\s+/', '', $GoodName);
        $res_g = parent::runquery("select count(*) from STO_goods where REPLACE( `GoodName` , ' ' , '' ) like :gn AND $where limit 1", $whereParams);
        if ($res_g[0]['count(*)'] > 0)
            return true;

        return false;
    }

}

?>