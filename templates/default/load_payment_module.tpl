<!--{*
/*
 * This file is part of EC-CUBE coincheck module
 *
 * Copyright(c) 2014 ResuPress All Rights Reserved.
 */
*}-->
<div id="undercolumn">
    <div id="undercolumn_shopping">
        <h2 class="title"><!--{$tpl_title|h}--></h2>

        <form name="form1" id="form1" method="POST" action="<!--{$tpl_url}-->" autocomplete="off">
            <input type="hidden" name="<!--{$smarty.const.TRANSACTION_ID_NAME}-->" value="<!--{$transactionid}-->" />
            <input type="hidden" name="mode" value="pay" />
            <!--{$buttonHtml|smarty:nodefaults}-->
        </form>
    </div>
</div>
