<!--{*
/*
 * This file is part of EC-CUBE coincheck module
 *
 * Copyright(c) 2014 ResuPress All Rights Reserved.
 */
*}-->

<form name="form-coincheck-config" id="form-coincheck-config" method="post" action="<!--{$smarty.server.REQUEST_URI|escape}-->">
    <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
    <input type="hidden" name="mode" value="register" />
    <div class="contents-main">
        <h2>coincheck アクセスキー設定</h2>
        <p style="margin: 30px 0; font-weight: bold; line-height: 25px;"><a href="https://coincheck.jp/api_settings" target="_blank">coincheck APIキー設定画面</a>から新たにAPIキーを作成し、「アクセスキー」、「シークレットアクセスキー」を入力して下さい。<br />コールバック用シークレットアクセスキーは変更せず、そのままにして下さい。<br />設定後は、左上の「基本情報管理」メニューから「配送方法設定」→ 「編集」と進み、「取扱支払方法」で「ビットコイン決済」をONにして下さい。<br />詳細は<a href="https://coincheck.jp/documents/eccube" target="_blank">EC-CUBEにビットコイン決済を導入する方法</a>を参照して下さい。</p>
        <table class="form">
            <col width="20%" />
            <col width="80%" />
            <!--{foreach from=$arrForm key=key item=formItem}-->
                <!--{if $key != "valid"}-->
                    <tr>
                        <th width="40%" style="width: 40%"><!--{$formItem.disp_name}--><span class="attention"> *</span></th>
                        <td width="60%">
                            <span class="attention"><!--{$arrErr[$key]}--></span>
                            <!--{if $key == 'recv_secret'}-->
                                <input type="text" name="<!--{$formItem.keyname}-->" value="<!--{$formItem.value|h}-->" size="30" class="box50" maxlength="<!--{$formItem.length}-->" style="<!--{$arrErr[$key]|sfGetErrorColor}-->" readonly/>
                            <!--{else}-->
                                <input type="text" name="<!--{$formItem.keyname}-->" value="<!--{$formItem.value|h}-->" size="30" class="box50" maxlength="<!--{$formItem.length}-->" style="<!--{$arrErr[$key]|sfGetErrorColor}-->" />
                            <!--{/if}-->
                        </td>
                    </tr>
                <!--{/if}-->
            <!--{/foreach}-->
            <!--{assign var=key value="valid"}-->
            <tr>
                <th width="40%" style="width: 40%"><!--{$arrForm[$key].disp_name}--></th>
                <td width="60%" style="width: 60%">
                <input type="checkbox" name="<!--{$key}-->" value="on" id="<!--{$key}-->" <!--{if $arrForm[$key].value == "on"}-->checked="checked"<!--{/if}--> /><label for="<!--{$key}-->">モジュールを有効にする</label>
                </td>
            </tr>
        </table>

        <div class="btn-area">
            <ul>
                <li><a class="btn-action" href="javascript:;" onclick="(window.eccube || window).fnFormModeSubmit('form-coincheck-config', 'register', '', ''); return false;"><span class="btn-next">この内容で登録する</span></a></li>
            </ul>
        </div>
    </div>
</form>
