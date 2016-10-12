<?
	use Bitrix\Main\Localization\Loc;
	Loc::loadMessages(__FILE__);
?>
<form action="<?=$params['URL']?>" method="post" target="_blank">
	<font class="tablebodytext">
		<?=Loc::getMessage("SALE_HPS_ROBOXCHANGE_TEMPL_TITLE")?><br>
		<?=Loc::getMessage("SALE_HPS_ROBOXCHANGE_TEMPL_ORDER");?> <?echo $params['PAYMENT_ID']."  ".$params["PAYMENT_DATE_INSERT"]?><br>
		<?=Loc::getMessage("SALE_HPS_ROBOXCHANGE_TEMPL_TO_PAY");?> <b><?=SaleFormatCurrency($params['PAYMENT_SHOULD_PAY'], $params["PAYMENT_CURRENCY"])?></b>
		<p>
		<input type="hidden" name="FinalStep" value="1">
		<input type="hidden" name="MrchLogin" value="<?=$params['ROBOXCHANGE_SHOPLOGIN'];?>">
		<input type="hidden" name="OutSum" value="<?=$params['PAYMENT_SHOULD_PAY'];?>">
		<input type="hidden" name="InvId" value="<?=$params['PAYMENT_ID'];?>">
		<input type="hidden" name="Desc" value="<?=$params['ROBOXCHANGE_ORDERDESCR'];?>">
		<input type="hidden" name="SignatureValue" value="<?=$params['SIGNATURE_VALUE'];?>">
		<input type="hidden" name="Email" value="<?=$params['BUYER_PERSON_EMAIL']?>">
		<input type="hidden" name="SHP_HANDLER" value="ROBOXCHANGE">
		<input type="hidden" name="SHP_BX_PAYSYSTEM_CODE" value="<?=$params['BX_PAYSYSTEM_CODE'];?>">
		<?if ($params['PS_IS_TEST'] == 'Y'):?>
			<input type="hidden" name="IsTest" value="1">
		<?endif;?>
		<?if ($params['PS_MODE'] != "0"):?>
			<input type="hidden" name="IncCurrLabel" value="<?=$params['PS_MODE'];?>">
		<?endif;?>

		<input type="submit" name="Submit" value="<?=Loc::getMessage("SALE_HPS_ROBOXCHANGE_TEMPL_BUTTON")?>">
		</p>
	</font>
</form>