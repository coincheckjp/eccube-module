<?php
/**
 * This file is part of EC-CUBE coincheck module
 *
 * @copyright 2014 ResuPress All Rights Reserved.
 */

/**
 * Coincheck module database accessor
 *
 * @author Coincheck
 */
class SC_Mdl_Coincheck_Models_Module
{
    private static $requiredKeys = array('access_key', 'access_secret', 'recv_secret');

    /**
     * 現在の設定を dtb_module からロード
     *
     * @param  boolean    $initialize true ならデータが存在しない場合に array() を返す。 false なら完全性チェックをおこない、データが不正な場合に null を返す
     * @return array|null 現在の設定値。ない場合は空配列かnull
     */
    public static function loadCurrentSetting($initialize = false)
    {
        $retval = $initialize ? array() : null;

        $objQuery = SC_Query::getSingletonInstance();
        $objQuery->setLimit(1);
        $arrModule = $objQuery->select('module_id, sub_data', 'dtb_module', 'module_code = ?', array(MDL_COINCHECK_CODE));
        if ($arrModule !== NULL && !empty($arrModule)) {
            $data = $arrModule[0]['sub_data'];

            if ($initialize) {
                if ($data !== null) {
                    $retval = unserialize($data);
                }
            } else {
                $retval = self::lfCompleteSettingOrNull($data);
            }
        }

        return $retval;
    }

    /* ModuleSetting の完全性チェックをおこない、不正なら null を返す */
    private static function lfCompleteSettingOrNull($data)
    {
        if ($data === null)
            return null;
        try {
            $arrSetting = unserialize($data);
        } catch (Exception $e) {
            return null;
        }
        foreach (self::$requiredKeys as $key) {
            if (!array_key_exists($key, $arrSetting) || $arrSetting[$key] === null || $arrSetting[$key] === '')
                return null;
        }

        return $arrSetting;
    }

    /**
     * dtb_module に Coincheck のエントリを挿入する
     * 初期化用。インストール時に実行する
     */
    public static function insert()
    {
        $objQuery = SC_Query::getSingletonInstance();
        $objQuery->setLimit(1);
        $arrModule = $objQuery->select('module_id, sub_data', 'dtb_module', 'module_code = ?', array(MDL_COINCHECK_CODE));
        if ($arrModule !== NULL && !empty($arrModule)) {
            return false;
        }

        $strRecvSecret = self::generateRandomString(32);
        $arrVal = array(
            'module_id' => MDL_COINCHECK_ID,
            'module_code' => MDL_COINCHECK_CODE,
            'module_name' => 'coincheck決済モジュール',
            'auto_update_flg' => 0,
            'del_flg' => 0,
            'create_date' => 'CURRENT_TIMESTAMP',
            'update_date' => 'CURRENT_TIMESTAMP',
            'sub_data' => serialize(array("recv_secret" => $strRecvSecret, 'valid' => 'on')),
        );
        if ($objQuery->insert('dtb_module', $arrVal) !==  1) {
            die('coincheckモジュールの初期化に失敗しました');
        }

        return true;
    }

    /*
     * ランダムな文字列を生成
     */
    private static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    /*
     * 必要なファイルをEC-CUBE内にコピーする
     */
    public static function copyFiles()
    {
        $arrUpdateFiles = array(
            array(
                "src" => "pg_coincheck_recv.php",
                "dst" => USER_REALDIR . "pg_coincheck_recv.php"
            ),
        );
        foreach($arrUpdateFiles as $file) {
            $dst_file = $file['dst'];
            $src_file = MDL_COINCHECK_REALDIR . 'copy/' . $file['src'];
            // ファイルがない、またはファイルはあるが異なる場合
            if (!file_exists($dst_file) || sha1_file($src_file) != sha1_file($dst_file)) {
                if (is_writable($dst_file) || is_writable(dirname($dst_file)) || $this->mkdirr(dirname($dst_file),0777)) {
                    if (file_exists($dst_file)) {
                        // _Exファイルは上書き対象外
                        if (substr($dst_file,-7) == '_Ex.php') {
                            continue;
                        } else {
                            @copy($dst_file, $dst_file . '.bak.' . date('YmdHis'));
                        }
                    }
                    if (!copy($src_file, $dst_file)) {
                        //                $this->failedCopyFile[] = $dst_file;
                    }
                } else {
                    //            $this->failedCopyFile[] = $dst_file;
                }
            }
        }
    }

    /*
     * 注文状況でビットコイン用のものを追加する
     */
    public static function insertOrderStatus()
    {
        $masterData = new SC_DB_MasterData_Ex();
        $objQuery = SC_Query::getSingletonInstance();
        $objQuery->setLimit(1);
        $arrStatus = $objQuery->select('id, name', 'mtb_order_status', 'name = ?', array(MDL_COINCHECK_ORDER_STATUS_CONFIRMING));
        if ($arrStatus === NULL || empty($arrStatus)) {
            $lastStatus = $objQuery->setOrder("id DESC")->setLimit(1)->select('id', 'mtb_order_status');
            $id = $lastStatus[0]["id"] + 1;
            $statusVal = array(
                $id => MDL_COINCHECK_ORDER_STATUS_CONFIRMING
            );
            $masterData->registMasterData("mtb_order_status", array("id", "name", "rank"), $statusVal);
            $masterData->createCache("mtb_order_status");
            $masterData->registMasterData("mtb_customer_order_status", array("id", "name", "rank"), $statusVal);
            $masterData->createCache("mtb_customer_order_status");
        }
    }
}
