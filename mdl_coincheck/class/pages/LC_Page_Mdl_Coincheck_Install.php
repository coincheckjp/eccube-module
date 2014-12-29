<?php
/**
 * This file is part of EC-CUBE coincheck module
 *
 * @copyright 2014 coincheck All Rights Reserved.
 */

// {{{ requires
require_once CLASS_EX_REALDIR . 'page_extends/LC_Page_Ex.php';
require_once(MDL_COINCHECK_CLASS_REALDIR . 'models/SC_Mdl_Coincheck_Models_Module.php');

/**
 * coincheckのインストールページクラス
 *
 */
class LC_Page_Mdl_Coincheck_Install extends LC_Page_Ex {

    function LC_Page_Mdl_Coincheck_Install() {
    }

    // }}}
    // {{{ functions

    /**
     * Page を初期化する.
     *
     * @return void
     */
    function init() {
        $this->setTemplate(MDL_COINCHECK_TEMPLATE_REALDIR . 'default/install.tpl');
        parent::init();
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    function process() {
        $this->action();
        $this->sendResponse();
    }

    /**
     * Page のアクション.
     *
     * @return void
     */
    function action() {
      $this->nextUrl = ROOT_URLPATH . ADMIN_DIR . 'load_module_config.php?module_id=' . MDL_COINCHECK_ID;
      SC_Mdl_Coincheck_Models_Module::insert();
    }

}

