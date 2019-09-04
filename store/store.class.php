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

class STO_GoodProperties extends OperationClass {

	const TableName = "STO_GoodProperties";
	const TableKey = "PropertyID"; 
	
	public $PropertyID;
	public $GoodID;
	public $PropertyTitle;
	public $PropertyType;
	public $PropertyValues;
	public $IsActive;
	
	function Remove($pdo = null) {
		
		$this->IsActive = "NO";
		return $this->Edit($pdo);		
	}
}


class Asset extends PdoDataAccess {

    public $AssetID;
    public $LabelNo;
    public $GoodID;
    public $RegDate;
    public $amount;
    public $StatusCode;
    public $IsOuter;

    function __construct($AssetID = "") {
        if ($AssetID != "")
            parent::FillObject($this, "select * from STO_assets where AssetID=?", array($AssetID));
    }

    static function CreateAssetPersonsTMPTable($where = '', $JoinCond = ' 1=1 ') {
        parent::runquery("
                CREATE TEMPORARY TABLE STO_TMPAssetPersons as
		select AssetID,PersonID , PersonName, DocID, LabelNo 
                from
                (   select a.AssetID , adi.PersonID,  concat(p.pfname,' ' ,p.plname) PersonName, ad.DocID, a.LabelNo
                    from STO_assets a
                    join STO_AssetDocItems adi on ($JoinCond and adi.AssetID = a.AssetID and adi.PersonID is not null)
                    join STO_AssetDocs ad on (ad.DocID = adi.DocID and (ad.DocStatus='CONFIRM' || ad.DocStatus='ACC_CONFIRM' || ad.DocID<10000))
                    join hrmstotal.persons p on(p.PersonId = adi.PersonID)
                    $where 
                    order by a.AssetID, ad.DocID DESC
                ) q 
                group by AssetID ");

        //parent::runquery("ALTER TABLE STO_TMPAssetPersons ADD INDEX(AssetID)");
    }

    static function CreateAssetPlacesTMPTable($where = '', $JoinCond = ' 1=1 ') {
        parent::runquery("
                CREATE TEMPORARY TABLE STO_TMPAssetPlaces as
		select AssetID,PlaceID , PlaceTitle from
                (   select a.AssetID , adi.PlaceID, p.PlaceTitle
                    from STO_assets a
                    join STO_AssetDocItems adi on ($JoinCond and adi.AssetID = a.AssetID and adi.PersonID is not null)
                    join STO_AssetDocs ad on (ad.DocID = adi.DocID and (ad.DocStatus='CONFIRM' || ad.DocStatus='ACC_CONFIRM' || ad.DocID<10000))
                    join STO_places p on(p.PlaceID = adi.PlaceID)
                    $where
                    order by a.AssetID, ad.DocID DESC
                ) q 
                group by AssetID");

        //parent::runquery("ALTER TABLE STO_TMPAssetPlaces ADD INDEX(AssetID)");
    }

    static function GetAll($where = "", $whereParam = array(), $FullInfo = false) {
        $query = "select a.*, GoodName, bf.title StatusDesc, 
				g.depreciateType,g.depreciateRatio"
                . ($FullInfo == true ? ",GN.Name as OldGoodName,pl.PlaceID,pl.PlaceTitle, a.PersonID, concat(per.pfname,' ',per.plname) as PersonName, GROUP_CONCAT(PropertyTitle, ':', PropertyValue SEPARATOR ',') properties, aoi.*   " : "" ) . " ,a.AssetID
			from STO_assets a
			join STO_goods g using(GoodID)
			join STO_BaseInfo bf on(TypeID=3 AND bf.InfoID=a.StatusCode)
			";
        /* مشخصات هر دارایی را هم برگرداند */
        if ($FullInfo) {
            //Asset::CreateAssetPersonsTMPTable("where a.UnitID = " . $_SESSION["STOREUSER"]["UnitID"], " a.UnitID = " . $_SESSION["STOREUSER"]["UnitID"]);
            //Asset::CreateAssetPlacesTMPTable("where a.UnitID = " . $_SESSION["STOREUSER"]["UnitID"], " a.UnitID = " . $_SESSION["STOREUSER"]["UnitID"]);

            /* مشخصات دارایی */
            $query .= " LEFT JOIN STO_AssetProperties ap on (ap.AssetID = a.AssetID)
                        LEFT JOIN STO_GoodProperties gp on (gp.PropertyID = ap.PropertyID) ";
            /* تحویل گیرنده */
            /* $query .= " LEFT JOIN STO_TMPAssetPersons per on (per.AssetID = a.AssetID) "
              . " LEFT JOIN STO_TMPAssetPlaces pl on (pl.AssetID = a.AssetID) "; */
            $query .= " LEFT JOIN STO_places pl on (pl.PlaceID = a.PlaceID)
                        LEFT JOIN hrmstotal.persons per on (per.PersonId = a.PersonID) ";
            /* Old Info */
            $query .= " left JOIN STO_AssetsOldInfo aoi ON (a.AssetID = aoi.AssetID) "
                    . " left join goods.MainGood on (goods.MainGood.Label = aoi.OldLabel and IF(aoi.OldLabelId>0 , goods.MainGood.id = aoi.OldLabelID ,1=1) and
                    (goods.MainGood.FormNo = aoi.OldFormNo      
                    OR
                    (goods.MainGood.FormNo != aoi.OldFormNo and goods.MainGood.OldFormNo = aoi.OldFormNo))
                    and goods.MainGood.UnitID =  ( Select OldUnitID FROM STO_units where STO_units.UnitID= " . $_SESSION['STOREUSER']['UnitID'] . ")
                        and (goods.MainGood.removeReason='' or goods.MainGood.removeReason='-1')
                    )
                    left join goods.GoodNames GN on (goods.MainGood.GoodId = GN.id) 
            ";            
        }        
        if (isset($whereParam[':DocID'])){
            $query .= " join STO_AssetDocItems adi on (adi.AssetID = a.AssetID) ";
        }
        $query .= ($where != "") ? " where " . $where : "";
        $query .= " group by a.AssetID ";
        $query .= dataReader::makeOrder();
        return parent::runquery_fetchMode($query, $whereParam);
    }

    static function IsValidLabelNo($labelNo, $UnitID = '') {
        $UnitID = $UnitID == '' ? $_SESSION["STOREUSER"]["UnitID"] : $UnitID;
        $dt = PdoDataAccess::runquery("select * from STO_assets where LabelNo=?", array($labelNo));
        if (count($dt) > 0) {
            ExceptionHandler::PushException("duplicate");
            return false;
        }
        /* $dt = PdoDataAccess::runquery("select * from goodsrepos.MainGoods where LabelNo=?", array($labelNo));
          if (count($dt) > 0) {
          ExceptionHandler::PushException("duplicate");
          return false;
          } */
        $dt = PdoDataAccess::runquery("select * from STO_LabelRanges
			where UnitID=" . $UnitID . "
			AND ? between FromNo AND ToNo", array($labelNo));
        if (count($dt) == 0) {
            // echo PdoDataAccess::GetLatestQueryString();
            ExceptionHandler::PushException("NotInRange");
            return false;
        }
        return true;
    }

    static function IsLabelNoUsedInADoc($LabelNo) {
        $res = PdoDataAccess::runquery("select count(*) as count from STO_AssetDocs
                                join STO_AssetDocItems using (DocID)
                                join STO_assets using (AssetID)
                                where LabelNo=? AND (STO_AssetDocs.DocStatus = 'CONFIRM' OR  STO_AssetDocs.DocStatus = 'ACC_CONFIRM')
                                limit 1 ", array($LabelNo));
        $rec = $res[0]['count'];
        if ($rec > 0)
            return true;
        return false;
    }

    /*
      اولین پلاک آزاد واحد را بر می گرداند */

    static function GetFirstFreeLabelNo($UnitID) {
        $UnitID = $UnitID == "" ? $_SESSION["STOREUSER"]["UnitID"] : $UnitID;
        $MinFromNo = parent::runquery("select min(FromNo) as LabelNo from STO_LabelRanges where UnitID = :uid", array(":uid" => $UnitID));
        $res = parent::runquery("select count(*) from STO_assets where LabelNo = ? limit 1", array($MinFromNo[0]['LabelNo']));
        if ($res[0]['count(*)'] == 0) {
            $res2 = parent::runquery("select count(*) from goodsrepos.MainGoods where LabelNo=? limit 1", array($MinFromNo[0]['LabelNo']));
            if ($res2[0]['count(*)'] == 0) {
                return $MinFromNo[0][0];
            }
        }

        return Asset::GetNextLabelNo($MinFromNo[0]['LabelNo'], $UnitID);
    }

    /* آخرین پلاکی که توسط واحد مربوطه برگه خورده است را برمی گرداند */
    /* تابع جایی استفاده نشد */

    static function GetLastUsedLabelNo($UnitID = "") {
        $UnitID = $UnitID == "" ? $_SESSION["STOREUSER"]["UnitID"] : $UnitID;
        $res = parent::runquery("SELECT LabelNo 
                FROM accountancy.STO_AssetDocItems adi
                join accountancy.STO_AssetDocs ad using (DocID)
                join STO_assets using (AssetID)
                where ad.UnitID = 2
                order by RowID DESC
                limit 1");
        $rec = $res[0]['LabelNo'];
        return $rec;
    }

    /* از شماره پلاک داده شده شروع به جستجو می کند
     * برای واحد تعیین شده یا واحد یوزر لاگین کرده، شماره پلاک بعدی که آزاد است را در می آورد
     * */

    static function GetNextLabelNo($LabelNo, $UnitID = "", $pdo = null) {
        $UnitID = $UnitID == "" ? $_SESSION["STOREUSER"]["UnitID"] : $UnitID;
        $ranges = parent::runquery("select *
                        from STO_LabelRanges
                        where UnitID = :uid and FromNo >= 
                                    (select FromNo
                                from STO_LabelRanges
                                where :lb between FromNo and ToNo and UnitID = :uid
                                )
                        order by FromNo", array(":lb" => $LabelNo, ":uid" => $UnitID), $pdo);
        $res = parent::runquery("select count(*) from STO_assets where LabelNo = ?", array($LabelNo));
        if ($res[0][0] == 0)
            $add = 1;
        else
            $add = 0;

        foreach ($ranges as $range) {
            $FromNo = $range['FromNo'] > $LabelNo ? $range['FromNo'] : $LabelNo + $add;
            $ToNo = $range['ToNo'];
            $range_numbers = range($FromNo, $ToNo);
            $used_range_numbers_new = PdoDataAccess::runquery("SELECT LabelNo FROM accountancy.STO_assets
		where LabelNo between :fr and :to", array(":fr" => $FromNo, ":to" => $ToNo));
            $used_range_numbers_old = PdoDataAccess::runquery("SELECT LabelNo FROM goodsrepos.MainGoods
		where LabelNo between :fr and :to", array(":fr" => $FromNo, ":to" => $ToNo));
            $used_range_numbers = array_merge($used_range_numbers_new, $used_range_numbers_old);

            $new = array();
            foreach ($used_range_numbers as $val) {
                $keys = array_keys($val);
                $new[$val[$keys[0]]] = intval($val[$keys[1]]);
            }
            $used_range_numbers = $new;
            $res = array_diff($range_numbers, $used_range_numbers);

            if (count($res) > 0) {
                return array_pop(array_reverse($res));
            }
        }
        return false;
    }

    static function GetLastLabelNo($UnitID = "", $pdo = null) {

        $UnitID = $UnitID == "" ? $_SESSION["STOREUSER"]["UnitID"] : $UnitID;

        $dt = parent::runquery("select * from STO_LabelRanges 
			where UnitID=" . $UnitID . "
			order by FromNo");

        //foreach range
        foreach ($dt as $row) {
            /*            $dt2 = parent::runquery("select ifnull(max(LabelNo),0) from STO_assets 
              where LabelNo>=? AND LabelNo<=?", array($row["FromNo"], $row["ToNo"])); */
            //select biggest used labelno
            $dt2 = parent::runquery("
				select ifnull(max(LabelNo),0)
				from (
					select ifnull(max(LabelNo),0) LabelNo from STO_assets where LabelNo>=:f AND LabelNo<=:t
					union All
					select ifnull(max(LabelNo),0) LabelNo from goodsrepos.MainGoods where LabelNo>=:f AND LabelNo<=:t
				)t", array(":f" => $row["FromNo"], ":t" => $row["ToNo"]), $pdo);

            if ($dt2[0][0] * 1 == 0)
                return $row["FromNo"] * 1;
            if ($dt2[0][0] * 1 < $row["ToNo"] * 1)
                return $dt2[0][0] * 1 + 1;
        }
        return false;
    }

    static function remove($where = '1=1', $whereParams = array(), $pdo = null) {
        $q = "delete from STO_assets where $where";
        if (parent::runquery($q, $whereParams, $pdo) === false) {
            echo "**";
            print_r(parent::PopAllExceptions());
            return false;
        }
        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_delete;
        $daObj->MainObjectID = PdoDataAccess::InsertID($pdo);
        $daObj->TableName = "STO_assets";
        $daObj->execute($pdo);
        return true;
    }

    static function AreAssetsRemoveable($assets, $pdo = null) {
        $res = parent::runquery("select count(*) from STO_AssetDocItems where AssetID in ($assets)", array(), $pdo);
        if ($res[0][0] > 0)
            return false;
        return true;
    }

    /* این تابع هم استتوس کد و هم آخرین تحویل گیرنده و مکان استقرار را آپدیت می کند */

    static function SetAssetStatusCode($Mode, $id, $pdo = null) {
        switch ($Mode) {
            case 'AssetID':
                $StatusCode = self::UpdateAssetLastStatusCode($id, $pdo);
                if ($StatusCode === false)
                    return false;
                if (self::UpdateAssetLastPersonID($id, $StatusCode, $pdo) === false)
                    return false;
                if (self::UpdateAssetLastPlaceID($id, $StatusCode, $pdo) === false)
                    return false;

                return true;

            case 'DocID':
                $AssetIDs = PdoDataAccess::runquery("select AssetID from STO_AssetDocItems where DocId = ?", array($id), $pdo);
                foreach ($AssetIDs as $AssetID) {
                    $StatusCode = self::UpdateAssetLastStatusCode($AssetID['AssetID'], $pdo);
                    if ($StatusCode === false)
                        return false;
                    if (self::UpdateAssetLastPersonID($AssetID['AssetID'], $StatusCode, $pdo) === false)
                        return false;
                    if (self::UpdateAssetLastPlaceID($AssetID['AssetID'], $StatusCode, $pdo) === false)
                        return false;
                }
                return true;
            /* case 'DocItemID':
              break; */
        }
    }

    /* آخرین وضعیت دارایی را بر اساس آخرین برگه اش در میاورد */

    private static function UpdateAssetLastStatusCode($AssetID, $pdo = null) {
        /* برگه تحویل به شخص :  
         * نیاز به تایید رییس اداره اموال ندارد
         * .استتوسش را با تایید امین اموال آپدیت میکنیم */
        // DocType 15 -> taghire baha
        $res = PdoDataAccess::runquery("select ad.DocType,ad.DocMode , bi.param2, adi.PersonID, ad.UnitID
                from STO_AssetDocs ad
                join STO_AssetDocItems adi on (adi.AssetID = ? and adi.DocID = ad.DocID)
                join STO_BaseInfo bi on (bi.TypeID = 2 and bi.InfoID = ad.DocType)
                where ad.DocType not in (15) 
                    and ((ad.DocStatus='ACC_CONFIRM') OR (ad.DocStatus = 'CONFIRM' and ad.DocType in (2) ) OR (ad.DocID<10000))
                order by DocDate DESC, adi.DocID DESC
                limit 1", array($AssetID), $pdo);

        $DocMode = $res[0]['DocMode'];
        $DocType = $res[0]['DocType'];
        $STO_assetstatus = $res[0]["param2"];

        // For DocID<10000
        if ($res[0]['PersonID'] > 0)
            $STO_assetstatus = 2;

        if ($STO_assetstatus == 0) {
            if ($DocMode == "outer") {
                switch ($DocType) {
                    case 4 : // transfer
                        $STO_assetstatus = 3;
                        break;
                    case 7 : // loan
                        $STO_assetstatus = 4;
                        break;
                    case 11 : // rent
                        $STO_assetstatus = 5;
                        break;
                }
            }
        }
        if ($STO_assetstatus == 0) {
            $STO_assetstatus = 1;
        }

        $obj = new Asset();
        $obj->AssetID = $AssetID;
        $obj->StatusCode = $STO_assetstatus;
        $obj->UnitID = $res[0]['UnitID'];
        $result = $obj->Edit($pdo);
        if ($result == false)
            return false;
        return $obj->StatusCode;
    }

    /* آخرین تحویل گیرنده کالا را در می آورد و در فیلد مربوطه آپدیتش می کند */

    private static function UpdateAssetLastPersonID($AssetID, $StatusCode, $pdo = null) {
        if ($StatusCode != 2) {
            $obj = new Asset();
            $obj->AssetID = $AssetID;
            $obj->PersonID = 0;
            return $obj->Edit($pdo);
        }
        $res = PdoDataAccess::runquery("select adi.PersonID
                    from STO_AssetDocs ad
                    join STO_AssetDocItems adi on (adi.AssetID = :AssetID and adi.PersonID is not null and ad.DocID = adi.DocID)
                    where (ad.DocStatus='CONFIRM' || ad.DocStatus='ACC_CONFIRM' || ad.DocID<10000)                    
                    order by ad.DocDate DESC
                    limit 1", array(":AssetID" => $AssetID), $pdo);
        $obj = new Asset();
        $obj->AssetID = $AssetID;
        $obj->PersonID = $res[0]['PersonID'];
        return $obj->Edit($pdo);
    }

    /* آخرین محل استقرار کالا را در می آورد و در فیلد مربوطه آپدیتش می کند */

    private static function UpdateAssetLastPlaceID($AssetID, $StatusCode, $pdo = null) {
        if ($StatusCode != 2) {
            $obj = new Asset();
            $obj->AssetID = $AssetID;
            $obj->PersonID = 0;
            return $obj->Edit($pdo);
        }
        $res = PdoDataAccess::runquery("select adi.PlaceID
                    from STO_AssetDocs ad
                    join STO_AssetDocItems adi on (adi.AssetID = :AssetID and adi.PlaceID is not null and ad.DocID = adi.DocID)
                    where (ad.DocStatus='CONFIRM' || ad.DocStatus='ACC_CONFIRM' || ad.DocID<10000)                    
                    order by ad.DocDate DESC
                    limit 1", array(":AssetID" => $AssetID), $pdo);
        $obj = new Asset();
        $obj->AssetID = $AssetID;
        $obj->PlaceID = $res[0]['PlaceID'];
        return $obj->Edit($pdo);
    }

    function Add($DocID = "", $ReceiptID = "", $pdo = null, $FakeDate = false) {
        if (!parent::insert("STO_assets", $this, $pdo))
            return false;

        $this->AssetID = parent::InsertID($pdo);

        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_add;
        $daObj->MainObjectID = $this->AssetID;
        $daObj->TableName = "STO_assets";
        $daObj->execute($pdo);


        //--------- add asset doc ---------------
        if ($DocID != -1) {
            if ($DocID != "") {
                $obj = new AssetDoc($DocID);
                if ($obj->DocStatus == "ACC_CONFIRM") {
                    ExceptionHandler::PushException("AccConfirmError");
                    return false;
                }
            }

            if ($DocID == "" || empty($obj->DocID)) {
                $obj = new AssetDoc();
                $obj->DocDate = $FakeDate == false ? PDONOW : $FakeDate;
                $obj->RegDate = PDONOW;
                $obj->DocMode = "inner";
                $obj->ReceiptID = $ReceiptID;
                $obj->DocType = 1;
                $obj->DocStatus = 'CONFIRM';
                $obj->RegPersonID = $_SESSION["User"]->PersonID;
                $obj->UnitID = $_SESSION["STOREUSER"]["UnitID"];
                $obj->PeriodID = $_SESSION["STOREUSER"]["PeriodID"];
                if (!$obj->Add($pdo))
                    return false;
            }
            $dobj = new STO_AssetDocItems();
            $dobj->AssetID = $this->AssetID;
            $dobj->DocID = $obj->DocID;
            $dobj->StatusCode = 1;
            if (!empty($_REQUEST['description']))
                $dobj->description = $_REQUEST['description'];
            if (!$dobj->Add($pdo))
                return false;
        }
        //----------------------------------------
        return $obj->DocID;
    }

    function Edit($pdo = null) {
        $whereParams = array();
        $whereParams[":aid"] = $this->AssetID;

        if (parent::update("STO_assets", $this, " AssetID=:aid", $whereParams, $pdo) === false)
            return false;

        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_update;
        $daObj->MainObjectID = $this->AssetID;
        $daObj->TableName = "STO_assets";
        $daObj->execute();
        return true;
    }

    function CopyAsset($ToLabelNo, $DocID, $pdo = null) {
        $SameAsset = new Asset($this->AssetID);
        unset($SameAsset->AssetID);
        $SameAsset->LabelNo = $ToLabelNo;
        if (!$SameAsset->Add($DocID, "", $pdo)) {
            echo PdoDataAccess::GetLatestQueryString();
            return false;
        }
        $AssetProps = new STO_AssetProperties();
        $AssetProps->AssetID = $this->AssetID;
        if (!$AssetProps->copyAssetProperties($SameAsset->AssetID, $pdo))
            return false;
        return true;
    }

    function AddOnlyAsset($pdo = null) {
        if (!parent::insert("STO_assets", $this, $pdo)) {
            return false;
        }

        $this->AssetID = parent::InsertID();

        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_add;
        $daObj->MainObjectID = $this->AssetID;
        $daObj->TableName = "STO_assets";
        $daObj->execute($pdo);

        return $this->AssetID;
    }

    function ChangeLabel($newLabelNo) {
        //get AssetId
        $query = "SELECT STO_assets.AssetID
                FROM STO_assets 
                WHERE LabelNo = " . $this->LabelNo;
        $res = parent::runquery($query);
        $rec = $res[0];
        $this->AssetID = $rec['AssetID'];

        //change
        $this->LabelNo = $newLabelNo;
        return $this->Edit();
    }

    /* تمام کالاهایی که دست این شخص هست را در میاورد */

    static function GetMyAssets($PersonID, $UnitID = '') {
        /*      Asset::CreateAssetPersonsTMPTable("", " a.UnitID = " . $_SESSION['STOREUSER']['UnitID'], array(":p" => $PersonID));
          parent::runquery("ALTER TABLE STO_TMPAssetPersons ADD INDEX(PersonID)");
          Asset::CreateAssetPlacesTMPTable("", " a.UnitID = " . $_SESSION['STOREUSER']['UnitID'], array(":p" => $PersonID));
          parent::runquery("ALTER TABLE STO_TMPAssetPlaces ADD INDEX(PlaceID)");

          $res = PdoDataAccess::runquery("select * from STO_TMPAssetPersons "
          . " left join STO_TMPAssetPlaces using(AssetID) "
          . " where STO_TMPAssetPersons.PersonID = ? limit 10000", array($PersonID));
         */
        $where = "";
        $params = array($PersonID);
        if ($UnitID != "") {
            $where = " and STO_assets.UnitID = ?";
            $params[] = $UnitID;
        }

        $res = PdoDataAccess::runquery("select * from STO_assets "
                        . " left join STO_places using(PlaceID) "
                        . " where STO_assets.PersonID = ? $where limit 10000", $params);

        return $res;
        /*
          $q = "select * from
          (select * from
          (
          select a.AssetID , ad.DocDate, ad.docId , a.LabelNo, adi.PersonID
          from
          (
          / *asset hayi ke ye bar be in shakhs tahvil shodan, bara speed of query* /
          select a.AssetID,a.LabelNo , adi.DocID
          from STO_assets a
          join STO_AssetDocItems adi on (a.AssetID = adi.AssetID and adi.PersonID = :pid)
          where a.StatusCode=2
          ) a
          join STO_AssetDocItems adi on (adi.AssetID = a.AssetID and adi.PersonId>0)
          join STO_AssetDocs ad on (ad.DocID = adi.DocID and ad.DocStatus='ACC_CONFIRM')
          order by ad.DocDate DESC
          ) assets / *each asset with its delivery docs* /
          group by assets.AssetID
          ) fnl / *group by -> each asset with its last delivery doc* /
          where PersonId = :pid / *where last person is me* /
          ";
          $res = parent::runquery_fetchMode($q,array(":pid"=>$PersonID)); */
    }

    /* استخراج بعضی اطلاعات نمایش داده شده در کارت اموال */

    function GetAssetCardInfo() {
        $query = "select a.LabelNo, /*a.amount as FinalAmount,*/ a.StatusCode as status,
                         g.GoodName, g.depreciateType, 
                         g.depreciateRatio,
                         STO_AssetsOldInfo.OldLabel
			from STO_assets a
                        join STO_goods g using (GoodID)
                        left join STO_AssetsOldInfo using (AssetID)
                        where AssetID = ?
                        limit 1
			";

        return parent::runquery($query, array($this->AssetID));
    }

    /* استخراج بهای تمام شده ی دارایی */
    /* قیمت فروش ندارد */

    function GetAssetFinalAmount() {
        $AssetAmountRes = parent::runquery("select amount from STO_assets where AssetID = ?", array($this->AssetID));

        $query = "SELECT 
                    ( IFNULL(sum(param1 * amount),0) - IFNULL(sum(FixAmount),0) ) as amount
                    FROM accountancy.STO_AssetDocs ad
                    join accountancy.STO_AssetDocItems adi using (DocID)
                    left join STO_BaseInfo b on (b.InfoID = adi.ChangeAmountType    and b.TypeID = 4)
                    where !(DocMode='inner' and DocType=1)
                    and ad.DocStatus = 'ACC_CONFIRM' and adi.AssetID = ?
			";
        $ChangesRes = parent::runquery($query, array($this->AssetID));
        return $AssetAmountRes[0]['amount'] - $ChangesRes[0]['amount'];
    }

    /* استخراج بعضی اطلاعات نمایش داده شده در کارت اموال */
    /* اولین قبض اموال، تاریخ خرید مبلغ خرید تاریخ قبض */

    function GetAssetFirstDocInfo() {
        $query = "SELECT DocID , g2j(DocDate) as BuyDate, g2j(Date(RegDate)) as RegDate, STO_AssetDocItems.amount as BuyAmount  
                    FROM accountancy.STO_AssetDocs
                    join STO_AssetDocItems using (DocID)
                    where AssetID  = ?
                    order by RegDate ASC
                    limit 1";
//echo $query . '</br>' . $this->AssetID . '</br>' ;

        return parent::runquery($query, array($this->AssetID));
    }

    /* استخراج بعضی اطلاعات نمایش داده شده در کارت اموال */
    /* تحویل گیرنده-محل استقرار-تاریخ بهره برداری */

    function GetAssetFirstPerInfo() {
        $query = "SELECT g2j(STO_AssetDocs.DocDate) , STO_AssetDocItems.PersonID,STO_AssetDocItems.PlaceID ,
                        concat (persons.pfname , ' ' , persons.plname ) as PersonName,
                        STO_places.PlaceTitle 
                    FROM accountancy.STO_AssetDocs
                    join STO_AssetDocItems using (DocID)
                    left join hrmstotal.persons using (PersonID)
                    left join STO_places on (STO_places.PlaceID = AssetDocItems.PlaceID )
                    where AssetID  = ? and  STO_AssetDocItems.PersonID is not null                    
                    order by RegDate DESC
                    limit 1";

        return parent::runquery($query, array($this->AssetID));
    }

    /* استخراج بعضی اطلاعات نمایش داده شده در کارت اموال */
    /* لیست افزاریش و کاهش بهای مال مورد نظر */

    function GetAssetCostChangesInfo() {

        $q = "
            select * 
            from STO_AssetDocs
            join STO_AssetDocItems using (DocID)
            join STO_BaseInfo b on (b.InfoID = STO_AssetDocItems.ChangeAmountType and b.TypeID = 4) 
            where 
                DocMode = 'change' 
                and DocType = 15
                and STO_AssetDocItems.AssetID = ?
            order by DocDate
        ";

        return parent::runquery($q, array($this->AssetID));
    }

    /* استخراج بعضی اطلاعات نمایش داده شده در کارت اموال */
    /* اطلاعات خروج دارایی */

    function GetAssetOutInfo() {
        $q = "
                SELECT b.InfoID, b.title , g2j(DocDate) as DocDate, adi.ReturnDate,  amount, 
                       concat(persons.pfname,' ',persons.plname) as person
                FROM accountancy.STO_AssetDocs
                    join STO_AssetDocItems adi using (DocID)
                    join STO_BaseInfo b on (b.InfoID = STO_AssetDocs.DocType and b.param1 in (2,0) and b.TypeID = 2)
                    left join hrmstotal.persons using (PersonID) 
                WHERE DocMode='outer' and AssetID  = ? 
                order by RegDate ASC
        ";

        return parent::runquery($q, array($this->AssetID));
    }

    /* استخراج بعضی اطلاعات نمایش داده شده در کارت اموال */
    /* تاریخ بهره برداری : اولین */

    function GetAssetFirstUseInfo() {
        $query = "SELECT STO_AssetDocs.*  , STO_AssetDocItems.*,  b.* , g2j(STO_AssetDocs.DocDate) as UseDate
                    FROM accountancy.STO_AssetDocs
                    join STO_AssetDocItems using (DocID)
                    join STO_BaseInfo b on (b.InfoID = STO_AssetDocs.DocType and b.param1 in (2,0) and b.TypeID = 2) 
                    where AssetID  = ? 
                    order by RegDate ASC
                    ";

        return parent::runquery($query, array($this->AssetID));
    }

    /**/

    function GetAssetInfo() {

        $q = "select gp.PropertyTitle , ap.PropertyValue /*ifnull(ap.PropertyValue,' ') as PropertyValue*/
                from STO_assets a
                join STO_goods g using (GoodID)
                join STO_goods g2 on (g.ParentID=g2.GoodID)
                join STO_GoodProperties gp on (gp.GoodID = g.GoodID OR gp.GoodID =g.ParentID OR gp.GoodID =g2.ParentID)
                left join STO_AssetProperties ap on (ap.PropertyID = gp.PropertyID and ap.AssetID = a.AssetID)
                where a.AssetID = ?";
        return parent::runquery($q, array($this->AssetID));
    }

    /* استخراج بعضی اطلاعات نمایش داده شده در کارت اموال */
    /* ذخیره استهلاک */

    function GetAssetDepInfo() {
        $q = "SELECT sum(amount) as DepAmount
                FROM accountancy.STO_depreciates
                join accountancy.STO_DepreciateItems using (DepID)
                where AssetID = ?";
        return parent::runquery($q, array($this->AssetID));
    }

    /* استخراج بعضی اطلاعات نمایش داده شده در کارت اموال */
    /* کل سابقه ش  */

    function GetAssetHistoryInfo() {
        $q = "SELECT  
                STO_AssetDocs.DocID , STO_AssetDocs.DocMode, STO_AssetDocs.DocType, g2j(STO_AssetDocs.DocDate) as DocDate, STO_AssetDocs.description, 
		STO_AssetDocItems.PlaceID, STO_AssetDocItems.PersonID, STO_AssetDocItems.amount, STO_AssetDocItems.FixAmount,
                STO_AssetDocs.organization,
		STO_AssetDocItems.lifetime, STO_AssetDocItems.StatusCode, 
                g2j(STO_AssetDocItems.ReturnDate) as ReturnDate, STO_AssetDocItems.amount,
		b.title as DocTypeTitle, b2.title as ChangeTypeTitle,
                concat(persons.pfname,' ',persons.plname) as person, STO_places.PlaceTitle,
                STO_units.UnitName as RefUnitTitle

            FROM accountancy.STO_AssetDocs
                join accountancy.STO_AssetDocItems using (DocID)
                join accountancy.STO_BaseInfo b on (b.InfoID = STO_AssetDocs.DocType and b.TypeID=2)
                left join accountancy.STO_BaseInfo b2 on (b2.InfoID = STO_AssetDocItems.ChangeAmountType and b2.TypeID=4)
                left join hrmstotal.persons using (PersonID)
                left join accountancy.STO_places using (PlaceID)
                left join accountancy.STO_units on (STO_units.UnitID= STO_AssetDocs.UnitID)
            where AssetID = ? 
            order by DocDate";
        return parent::runquery($q, array($this->AssetID));
    }

    static function GetAllLabels($where = "", $whereParam = array()) {
        $query = "SELECT STO_assets.LabelNo, STO_goods.GoodName, STO_assets.AssetID
                FROM STO_assets 
                left join STO_goods using (GoodID)";
        $query .= ($where != "") ? " where " . $where : "";

        return parent::runquery_fetchMode($query, $whereParam);
    }

}

class STO_AssetProperties extends PdoDataAccess {

    public $AssetID;
    public $PropertyID;
    public $PropertyValue;

    function Add($pdo = null) {
        if (parent::insert("STO_AssetProperties", $this, $pdo) === false)
            return false;

        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_add;
        $daObj->MainObjectID = $this->AssetID;
        $daObj->SubObjectID = $this->PropertyID;
        $daObj->TableName = "STO_assets";
        $daObj->execute();
        return true;
    }

    static function removeAll($AssetID, $pdo = null) {
        parent::delete("STO_AssetProperties", "AssetID=?", array($AssetID), $pdo);
    }

    static function remove($where = '1=1', $whereParams = array(), $pdo = null) {
        $q = "delete from STO_AssetProperties where $where";
        if (parent::runquery($q, $whereParams, $pdo) === false) {
            echo "**";
            print_r(parent::PopAllExceptions());
            return false;
        }
        $daObj = new DataAudit();
        $daObj->ActionType = DataAudit::Action_delete;
        $daObj->MainObjectID = PdoDataAccess::InsertID($pdo);
        $daObj->TableName = "STO_AssetProperties";
        $daObj->execute($pdo);
        return true;
    }

    function copyAssetProperties($ToAssetID, $pdo = null) {
        $properties = parent::runquery("SELECT * FROM STO_AssetProperties where AssetID = ?", array($this->AssetID));
        $New = new STO_AssetProperties();
        $New->AssetID = $ToAssetID;
        foreach ($properties as $property) {
            $New->PropertyID = $property['PropertyID'];
            $New->PropertyValue = $property['PropertyValue'];
            if (!$New->Add($pdo))
                return false;
        }
        return true;
    }

}
?>