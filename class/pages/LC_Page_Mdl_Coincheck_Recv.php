<?php
/**
 * This file is part of EC-CUBE coincheck module
 *
 * @copyright 2014 coincheck All Rights Reserved.
 */

// {{{ requires
require_once CLASS_EX_REALDIR . 'page_extends/LC_Page_Ex.php';
require_once(MODULE_REALDIR . 'mdl_pg_mulpay/inc/include.php');
require_once(MDL_COINCHECK_CLASS_REALDIR . 'models/SC_Mdl_Coincheck_Models_Module.php');

/**
 * 決済モジュール 結果受信クラス
 *
 */
class LC_Page_Mdl_Coincheck_Recv extends LC_Page_Ex {

    var $objMdl;
    var $arrSetting;

    function LC_Page_Mdl_PG_MULPAY_Recv() {
    }

    // }}}
    // {{{ functions

    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init() {
        $this->skip_load_page_layout = true;
        parent::init();
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    function process() {
        $this->action();
//        $this->sendResponse();
    }

    /**
     * Page のアクション.
     *
     * @return void
     */
    function action() {
        $strRecvSecret = $_GET["recv_secret"];
        $arrModuleSetting = SC_Mdl_Coincheck_Models_Module::loadCurrentSetting();
        $boolVerified = $strRecvSecret === $arrModuleSetting["recv_secret"];
        # recv_secretが正しくない
        if (!$boolVerified) {
            SC_Response_Ex::sendHttpStatus(400);
            SC_Response_Ex::actionExit();
        }

        $strOrderId = $_GET["order_id"];
        $arrOrder = $_POST["order"];
        $strEventType = $arrOrder["event"]["type"];

        switch ($strEventType) {
            case "received":
                $objQuery = SC_Query::getSingletonInstance();
                $masterData = new SC_DB_MasterData_Ex();
                $arrOrderStatuses =  $masterData->getMasterData("mtb_order_status");
                $intReceiveBitcoinStatus = array_search(MDL_COINCHECK_ORDER_STATUS_CONFIRMING, $arrOrderStatuses);
                $objQuery->update("dtb_order", array("status" => $intReceiveBitcoinStatus), array("order_id" => $strOrderId));
                break;
            case "confirmed":
                $objQuery = SC_Query::getSingletonInstance();
                $objQuery->update("dtb_order", array("status" => ORDER_PRE_END), array("order_id" => $strOrderId));
                $objPurchase = new SC_Helper_Purchase_Ex();
                $objPurchase->sendOrderMail($strOrderId);
                break;
        }

        SC_Response_Ex::actionExit();
    }

    /**
     * デストラクタ.
     *
     * @return void
     */
    function destroy() {
        parent::destroy();
    }

}
