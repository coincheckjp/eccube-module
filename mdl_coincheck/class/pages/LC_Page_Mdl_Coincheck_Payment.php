<?php
/**
 * This file is part of EC-CUBE coincheck module
 *
 * @copyright 2014 coincheck All Rights Reserved.
 */

require_once(CLASS_EX_REALDIR . "page_extends/LC_Page_Ex.php");
require_once(MDL_COINCHECK_CLASS_REALDIR . 'models/SC_Mdl_Coincheck_Models_Module.php');

/**
 * coincheck module payment page class
 *
 * @package Page
 * @author coincheck
 */
class LC_Page_Mdl_Coincheck_Payment extends LC_Page_Ex
{

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->httpCacheControl('nocache');
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    public function process()
    {
        $this->action();
        $this->sendResponse();
    }

    /**
     * Page のアクション.
     *
     * @return void
     */
    public function action()
    {
        $this->selectTemplate();

        $order_id = $this->getOrderId();
        if ($order_id === NULL) {
            SC_Utils_Ex::sfDispSiteError(FREE_ERROR_MSG, '', true, '注文情報の取得が出来ませんでした。<br />この手続きは無効となりました。');
        }

        $objPurchase = new SC_Helper_Purchase_Ex();
        $arrOrder = $objPurchase->getOrder($order_id);
        $this->tpl_title = $arrOrder['payment_method'];

        $this->validateOrderConsistency($arrOrder);

        $arrModuleSetting = SC_Mdl_Coincheck_Models_Module::loadCurrentSetting();

        switch ($this->getMode()) {
            default:
                $this->getButtonObject($arrModuleSetting, $arrOrder);
                break;
        }
        $this->tpl_url = $_SERVER['REQUEST_URI'];
    }

    /* テンプレートを設定する。携帯ははじく */
    private function selectTemplate()
    {
        switch (SC_Display_Ex::detectDevice()) {
            case DEVICE_TYPE_MOBILE:
                SC_Utils_Ex::sfDispSiteError(FREE_ERROR_MSG, '', true, '携帯電話からはビットコイン決済を利用できせん');
                break;
            default:
                $this->tpl_mainpage = MDL_COINCHECK_TEMPLATE_REALDIR . 'default/load_payment_module.tpl';
                break;
        }
    }

    /* order_id を SESSION から取得する */
    private function getOrderId()
    {
        if (isset($_SESSION['order_id'])
            && !SC_Utils_Ex::isBlank($_SESSION['order_id'])
            && SC_Utils_Ex::sfIsInt($_SESSION['order_id'])) {
            return $_SESSION['order_id'];
        }

        return NULL;
    }

    /* 注文のデータが一貫しており処理可能なものであることを確認する */
    private function validateOrderConsistency($arrOrder)
    {
        switch ($arrOrder['status']) {
            case ORDER_PENDING:
                // 対象ケース。以降で処理する
                break;

            // 会計済み。許容しうる
            case ORDER_NEW:
            case ORDER_PRE_END:
                SC_Response_Ex::sendRedirect(SHOPPING_COMPLETE_URLPATH);
                SC_Response_Ex::actionExit();
                break;

            // coincheck の決済では発生しない
            default:
                SC_Utils_Ex::sfDispSiteError(FREE_ERROR_MSG, '', true, '注文情報の状態が不正です。<br />この手続きは無効となりました。');
        }

        $objPayment = new SC_Helper_Payment_Ex();
        $arrPayment = $objPayment->get($arrOrder['payment_id']);
        if ($arrPayment === null || $arrPayment['module_id'] !== MDL_COINCHECK_ID) {
            SC_Utils_Ex::sfDispSiteError(FREE_ERROR_MSG, '', true, '支払方法が不正です。<br />この手続きは無効となりました。');
        }
    }

    /* 決済用のボタン作成 */
    private function getButtonObject($arrModuleSetting, $arrOrder)
    {
        $strUrl = MDL_COINCHECK_API_BASE . '/ec/buttons';
        $intNonce = time();
        $strCallbackUrl = HTTPS_URL . USER_DIR . "pg_coincheck_recv.php?recv_secret=" . $arrModuleSetting["recv_secret"] . "&order_id=" . $arrOrder["order_id"];
        $arrQuery = array("button" => array(
            "name" => ("注文 #" . $arrOrder["order_id"]),
            "email" => $arrOrder["order_email"],
            "currency" => "JPY",
            "amount" => $arrOrder["payment_total"],
            "callback_url" => $strCallbackUrl,
            "success_url" => $this->getLocation(SHOPPING_COMPLETE_URLPATH),
            "max_times" => 1
        ));
        $strAccessKey = $arrModuleSetting["access_key"];
        $strAccessSecret = $arrModuleSetting["access_secret"];
        $strMessage = $intNonce . $strUrl . http_build_query($arrQuery);

        # hmacで署名
        $strSignature = hash_hmac("sha256", $strMessage, $strAccessSecret);

        # http request
        $objReq = new HTTP_Request($strUrl);
        $objReq->setMethod('POST');
        $objReq->addHeader("ACCESS-KEY", $strAccessKey);
        $objReq->addHeader("ACCESS-NONCE", $intNonce);
        $objReq->addHeader("ACCESS-SIGNATURE", $strSignature);
        $objReq->setBody(http_build_query($arrQuery));
        $objReq->sendRequest();
        $arrJson = json_decode($objReq->getResponseBody(), true);
        $this->buttonHtml = $arrJson["button"]["html_tag"];
    }
}
