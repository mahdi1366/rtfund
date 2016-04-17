<?php

//-----------------------------
//	Programmer	: Fatemipour
//	Date		: 94.08
//-----------------------------
require_once 'CNTParentClass.class.php';

class CNT_documents extends CNTParentClass {

    const TableName = "CNT_documents";
    const TableKey = "DocId";

    /**
     * شماره یکتای ردیف جدول 
     * @var int 
     */
    public $DocId;

    /**
     *  نوع منبع آپلود فایل
     * @var int 
     */
    public $ObjectType;

    /**
     * کد منبع 
     * @var int 
     */
    public $ObjectId;

    /**
     *   نوع فایل
     * @var int 
     */
    public $DocType;

    /**
     *   وضعیت
     * @var int 
     */
    public $StatusCode;

    /**
     *   پسوند فایل
     * @var int 
     */
    Public $FileType;

    /**
     *   نام اصلی فایل که کاربر آپلود کرده است
     * @var int 
     */
    public $RealFileName;

    /**
     * سند تایید شده است یا خیر
     * @var int 
     */
    public $IsConfirm;



    /**
     * شخص ثبت کننده
     * @var int 
     */
    public $RegPersonID;


    /** 
     * تاریخ ثبت
     * @var int 
     */
    public $RegDate;


    function __construct($id = "") {
        parent::__construct($id);
    }

}

?>
