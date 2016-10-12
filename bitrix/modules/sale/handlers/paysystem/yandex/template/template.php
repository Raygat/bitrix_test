<?
	use Bitrix\Main\Localization\Loc;
	use Bitrix\Sale\PriceMaths;

	Loc::loadMessages(__FILE__);

	$sum = PriceMaths::roundByFormatCurrency($params['PAYMENT_SHOULD_PAY'], $payment->getField('CURRENCY'));
?>

<span class="tablebodytext">
	<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_DESCRIPTION');?> <?=SaleFormatCurrency($params['PAYMENT_SHOULD_PAY'], $payment->getField('CURRENCY'));?>
<br />
</span>
<form name="ShopForm" action="<?=$params['URL'];?>" method="post">

<input name="ShopID" value="<?=$params['YANDEX_SHOP_ID'];?>" type="hidden">
<input name="scid" value="<?=$params['YANDEX_SCID'];?>" type="hidden">
<input name="customerNumber" value="<?=$params['PAYMENT_BUYER_ID'];?>" type="hidden">
<input name="orderNumber" value="<?=$params['PAYMENT_ID'];?>" type="hidden">
<input name="Sum" value="<?=number_format($sum, 2, '.', '')?>" type="hidden">
<input name="paymentType" value="<?=$params['PS_MODE']?>" type="hidden">
<input name="cms_name" value="1C-Bitrix" type="hidden">
<input name="BX_HANDLER" value="YANDEX" type="hidden">
<input name="BX_PAYSYSTEM_CODE" value="<?=$params['BX_PAYSYSTEM_CODE']?>" type="hidden">

<br />
<input name="BuyButton" value="<?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_BUTTON_PAID')?>" type="submit">

<p>
	<span class="tablebodytext"><?=Loc::getMessage('SALE_HANDLERS_PAY_SYSTEM_YANDEX_WARNING_RETURN');?></span>
</p>
</form>