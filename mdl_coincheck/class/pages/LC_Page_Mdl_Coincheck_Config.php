<?php
/**
 * This file is part of EC-CUBE coincheck module
 *
 * @copyright 2014 ResuPress All Rights Reserved.
 */

require_once(CLASS_EX_REALDIR . "page_extends/admin/LC_Page_Admin_Ex.php");
require_once(MDL_COINCHECK_CLASS_REALDIR . 'models/SC_Mdl_Coincheck_Models_Module.php');

/**
 * coincheck module admin config page class
 *
 * @package Page
 * @author coincheck
 */
class LC_Page_Mdl_Coincheck_Config extends LC_Page_Admin_Ex
{

    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_mainpage = MDL_COINCHECK_TEMPLATE_REALDIR . 'admin/config.tpl';
        $this->tpl_subtitle = 'coincheck';
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
        $this->initPaymentMethod();
        $arrSetting = SC_Mdl_Coincheck_Models_Module::loadCurrentSetting(true);

        $objFormParam = new SC_FormParam_Ex();
        $this->initFormParam($objFormParam, $arrSetting);

        switch ($this->getMode()) {
            case 'register':
                $objFormParam->setParam($_REQUEST);
                $objFormParam->convParam();
                $this->arrErr = $objFormParam->checkError();
                if (empty($this->arrErr)) {
                    $arrSetting = $objFormParam->getHashArray();
                    $this->updateModuleSetting($arrSetting);
                    if ($arrSetting["valid"] == "on") {
                        $this->enablePaymentMethod();
                    } else {
                        $this->disablePaymentMethod();
                    }
                }
                break;
            default:
                $objFormParam->setParam($arrSetting);
                $objFormParam->convParam();
                $this->arrErr = $objFormParam->checkError();
                break;
        }
        $this->arrForm = $objFormParam->getFormParamList();
    }

    /**
     * dtb_payment に coincheck モジュールによるビットコイン決済がない場合は追加する
     *
     * @return boolean 実行した場合は true
     */
    private function initPaymentMethod()
    {
        $objQuery = SC_Query::getSingletonInstance();
        $isExists = $objQuery->exists('dtb_payment', 'module_id = ?', array(MDL_COINCHECK_ID));
        if ($isExists) {
            return false;
        }

        // rank, create_date, update_date, payment_id は自動設定される
        $arrVal = array(
            'payment_method' => 'ビットコイン決済',
            'charge_flg' => 2, // 決済手数料設定不可
            'rule_min' => 50, // 設定できる最低金額の下限
            'upper_rule_max'  => 9999999, // 設定できる最高金額の上限
            'module_id' => MDL_COINCHECK_ID,
            'module_path' => MDL_COINCHECK_REALDIR . 'payment.php',
            'memo03' => MDL_COINCHECK_CODE,
            'del_flg' => 1, // デフォルトでは無効状態
        );
        $objPayment = new SC_Helper_Payment_Ex();
        $objPayment->save($arrVal);

        return true;
    }

    /**
     * dtb_paymentでビットコイン決済を無効にする
     */
    private function disablePaymentMethod()
    {
        $objQuery = SC_Query::getSingletonInstance();
        $objQuery->update('dtb_payment', array("del_flg" => 1), 'module_id = ?', array(MDL_COINCHECK_ID));
    }

    /**
     * dtb_paymentでビットコイン決済を有効にする
     */
    private function enablePaymentMethod()
    {
        $objQuery = SC_Query::getSingletonInstance();
        $objQuery->update('dtb_payment', array("del_flg" => 0), 'module_id = ?', array(MDL_COINCHECK_ID));
        # order_statusにビットコイン確認中を追加する
        SC_Mdl_Coincheck_Models_Module::insertOrderStatus();
        # ファイルをコピー
        SC_Mdl_Coincheck_Models_Module::copyFiles();
    }

    /* パラメーター情報の初期化 */
    private function initFormParam($objFormParam)
    {
        $max_length = 256;
        $objFormParam->addParam('アクセスキー', 'access_key', $max_length, 'a', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'GRAPH_CHECK'));
        $objFormParam->addParam('シークレットアクセスキー', 'access_secret', $max_length, 'a', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'GRAPH_CHECK'));
        $objFormParam->addParam('コールバック用シークレットキー（変更禁止）', 'recv_secret', $max_length, 'a', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'GRAPH_CHECK'));
        $objFormParam->addParam('モジュールの有効化', 'valid', $max_length, 'a', array());
    }

    /* バリデーション済みの設定値を dtb_module に保存 */
    private function updateModuleSetting($arrSetting)
    {
        $objQuery = SC_Query::getSingletonInstance();
        if ($objQuery->begin() !== MDB2_OK) {
            die('coincheckモジュールの初期化に失敗しました');
        }
        $arrVal = array('sub_data' => serialize($arrSetting), 'update_date' => 'CURRENT_TIMESTAMP');
        $objQuery->update('dtb_module', $arrVal, 'module_code = ?', array(MDL_COINCHECK_CODE));
        if ($objQuery->commit() !== MDB2_OK) {
            die('coincheckモジュールの初期化に失敗しました');
        }
    }
}
