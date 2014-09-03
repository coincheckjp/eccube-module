<?php
/**
 * This file is part of EC-CUBE coincheck module
 *
 * @copyright 2014 ResuPress All Rights Reserved.
 *
 */

define('MDL_COINCHECK', true);
define('MDL_COINCHECK_ID', '48571');
define('MDL_COINCHECK_CODE', 'mdl_coincheck');
define('MDL_COINCHECK_VERSION', '1.0.0');

define('MDL_COINCHECK_API_BASE', 'https://coincheck.jp/api');
define('MDL_COINCHECK_SITE', 'https://coincheck.jp/');

// paths
define('MDL_COINCHECK_REALDIR', MODULE_REALDIR . 'mdl_coincheck/');
define('MDL_COINCHECK_CLASS_REALDIR', MDL_COINCHECK_REALDIR . 'class/');
define('MDL_COINCHECK_TEMPLATE_REALDIR', MDL_COINCHECK_REALDIR . 'templates/');
define('MDL_COINCHECK_INCLUDEFILE_REALFILE', MDL_COINCHECK_REALDIR . 'inc/include . php');

// status
define('MDL_COINCHECK_ORDER_STATUS_CONFIRMING', 'ビットコイン確認中');
