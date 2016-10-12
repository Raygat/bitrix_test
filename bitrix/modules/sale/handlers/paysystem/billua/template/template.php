<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title><?=Loc::getMessage('SALE_HPS_BILLUA')?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=LANG_CHARSET?>">
<style>
	table { border-collapse: collapse; }
	table.acc td { padding: 0pt; vertical-align: top; }
	table.it td { border: 1pt solid #000000; padding: 0pt 3pt; }
	table.sign td { font-weight: bold; vertical-align: bottom; }
</style>
</head>

<?

if ($_REQUEST['BLANK'] == 'Y')
	$blank = true;

$pageWidth  = 595.28;
$pageHeight = 841.89;

$background = '#ffffff';
if ($params['BILLUA_BACKGROUND'])
{
	$path = $params['BILLUA_BACKGROUND'];
	if (intval($path) > 0)
	{
		if ($arFile = CFile::GetFileArray($path))
			$path = $arFile['SRC'];
	}

	$backgroundStyle = $params['BILLUA_BACKGROUND_STYLE'];
	if (!in_array($backgroundStyle, array('none', 'tile', 'stretch')))
		$backgroundStyle = 'none';

	if ($path)
	{
		switch ($backgroundStyle)
		{
			case 'none':
				$background = "url('" . $path . "') 0 0 no-repeat";
				break;
			case 'tile':
				$background = "url('" . $path . "') 0 0 repeat";
				break;
			case 'stretch':
				$background = sprintf(
					"url('%s') 0 0 repeat-y; background-size: %.02fpt %.02fpt",
					$path, $pageWidth, $pageHeight
				);
				break;
		}
	}
}

$margin = array(
	'top' => intval($params['BILLUA_MARGIN_TOP'] ?: 15) * 72/25.4,
	'right' => intval($params['BILLUA_MARGIN_RIGHT'] ?: 15) * 72/25.4,
	'bottom' => intval($params['BILLUA_MARGIN_BOTTOM'] ?: 15) * 72/25.4,
	'left' => intval($params['BILLUA_MARGIN_LEFT'] ?: 20) * 72/25.4
);

$width = $pageWidth - $margin['left'] - $margin['right'];

?>

<body style="margin: 0pt; padding: 0pt;"<? if ($_REQUEST['PRINT'] == 'Y') { ?> onload="setTimeout(window.print, 0);"<? } ?>>

<div style="margin: 0pt; padding: <?=join('pt ', $margin); ?>pt; width: <?=$width; ?>pt; background: <?=$background; ?>">

<b><?=Loc::getMessage('SALE_HPS_BILLUA_TITLE', array('#PAYMENT_NUMBER#' => htmlspecialcharsbx($params["ACCOUNT_NUMBER"]), '#PAYMENT_DATE#' => $params["DATE_INSERT"])); ?></b>
<br>
<br>

<?

$buyerPhone = $params["BUYER_PERSON_COMPANY_PHONE"];
$buyerFax = $params["BUYER_PERSON_COMPANY_FAX"];

?>

<table class="acc">
	<tr>
		<td><?=Loc::getMessage('SALE_HPS_BILLUA_SELLER')?>:</td>
		<td style="padding-left: 4pt; ">
			<?=$params["SELLER_COMPANY_NAME"]; ?>
			<br>
			<?=Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_RS')?> <?=$params["SELLER_COMPANY_BANK_ACCOUNT"]; ?>,
			<?=Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_BANK')?> <?=$params["SELLER_COMPANY_BANK_NAME"]; ?>,
			<?=Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_MFO')?> <?=$params["SELLER_COMPANY_MFO"]; ?>
			<br><?
			$sellerAddr = '';
			if ($params["SELLER_COMPANY_ADDRESS"])
			{
				$sellerAddr = $params["SELLER_COMPANY_ADDRESS"];
				if (is_array($sellerAddr))
					$sellerAddr = implode(', ', $sellerAddr);
				else
					$sellerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($sellerAddr));
			}
			?>
			<?=Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_ADDRESS')?>: <?= $sellerAddr ?>,
			<?=Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_PHONE')?>: <?=$params["SELLER_COMPANY_PHONE"]; ?>
			<br>
			<?=Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_EDRPOY')?>: <?=$params["SELLER_COMPANY_EDRPOY"]; ?>,
			<?=Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_IPN');?>: <?=$params["SELLER_COMPANY_IPN"];?>,
			<?=Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_PDV');?>: <?=$params["SELLER_COMPANY_PDV"]; ?>
			<? if ($params["SELLER_COMPANY_SYS"]) { ?>
			<br>
			<?=$params["SELLER_COMPANY_SYS"]; ?>
			<? } ?>
		</td>
	</tr>
	<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
	<tr>
		<td><?=Loc::getMessage('SALE_HPS_BILLUA_BUYER');?>:</td>
		<td style="padding-left: 4pt; ">
			<?=$params["BUYER_PERSON_COMPANY_NAME"]; ?>
			<? if ($buyerPhone || $buyerFax) { ?>
			<br>
			<? if ($buyerPhone) { ?><?=Loc::getMessage('SALE_HPS_BILLUA_BUYER_PHONE')?>: <?=$buyerPhone; ?><? if ($buyerFax) { ?>, <? } ?><? } ?>
			<? if ($buyerFax) { ?><?=Loc::getMessage('SALE_HPS_BILLUA_BUYER_FAX')?>: <?=$buyerFax; ?><? } ?>
			<? } ?><?
			if ($params["BUYER_PERSON_COMPANY_ADDRESS"])
			{
				$buyerAddr = $params["BUYER_PERSON_COMPANY_ADDRESS"];
				if (is_array($buyerAddr))
					$buyerAddr = implode(', ', $buyerAddr);
				else
					$buyerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($buyerAddr));
				?><br><?
				?><?=Loc::getMessage('SALE_HPS_BILLUA_BUYER_ADDRESS')?>: <?= $buyerAddr ?><?
			}
			?>
		</td>
	</tr>
</table>
<br>

<? if ($params["BUYER_PERSON_COMPANY_DOGOVOR"]) { ?>
<?=Loc::getMessage('SALE_HPS_BILLUA_BUYER_DOGOVOR')?>: <?=$params["BUYER_PERSON_COMPANY_DOGOVOR"]; ?>
<br>
<? } ?>
<br>

<?

$basketItems = array();

/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
$paymentCollection = $payment->getCollection();

/** @var \Bitrix\Sale\Order $order */
$order = $paymentCollection->getOrder();

/** @var \Bitrix\Sale\Basket $basket */
$basket = $order->getBasket();

if (count($basket->getBasketItems()) > 0)
{
	$arCells = array();
	$arProps = array();

	$n = 0;
	$sum = 0.00;
	$vat = 0;

	/** @var \Bitrix\Sale\BasketItem $basketItem */
	foreach ($basket->getBasketItems() as $basketItem)
	{
		$productName = $basketItem->getField("NAME");
		if ($productName == "OrderDelivery")
			$productName = Loc::getMessage('SALE_HPS_BILLUA_DELIVERY');
		else if ($productName == "OrderDiscount")
			$productName = Loc::getMessage('SALE_HPS_BILLUA_DISCOUNT');

		if ($basketItem->isVatInPrice())
			$basketItemPrice = $basketItem->getPrice();
		else
			$basketItemPrice = $basketItem->getPrice()*(1 + $basketItem->getVatRate());

		$arCells[++$n] = array(
			1 => $n,
			htmlspecialcharsbx($productName),
			roundEx($basketItem->getQuantity(), SALE_VALUE_PRECISION),
			$basketItem->getField("MEASURE_NAME") ? htmlspecialcharsbx($basketItem->getField("MEASURE_NAME")) : Loc::getMessage('SALE_HPS_BILLUA_MEASHURE'),
			SaleFormatCurrency($basketItem->getPrice(), $basketItem->getCurrency(), true),
			roundEx($basketItem->getVatRate()*100, SALE_VALUE_PRECISION) . "%",
			SaleFormatCurrency(
				$basketItemPrice * $basketItem->getQuantity(),
				$basketItem->getCurrency(),
				true
			)
		);

		$arProps[$n] = array();

		/** @var \Bitrix\Sale\BasketPropertyItem $basketPropertyItem */
		foreach ($basketItem->getPropertyCollection() as $basketPropertyItem)
		{
			if ($basketPropertyItem->getField('CODE') == 'CATALOG.XML_ID' || $basketPropertyItem->getField('CODE') == 'PRODUCT.XML_ID')
				continue;
			$arProps[$n][] = htmlspecialcharsbx(sprintf("%s: %s", $basketPropertyItem->getField("NAME"), $basketPropertyItem->getField("VALUE")));
		}

		$sum += doubleval($basketItem->getPrice() * $basketItem->getQuantity());
		$vat = max($vat, $basketItem->getVatRate());
	}

	/** @var \Bitrix\Sale\ShipmentCollection $shipmentCollection */
	$shipmentCollection = $order->getShipmentCollection();

	$shipment = null;

	/** @var \Bitrix\Sale\Shipment $shipmentItem */
	foreach ($shipmentCollection as $shipmentItem)
	{
		if (!$shipmentItem->isSystem())
		{
			$shipment = $shipmentItem;
			break;
		}
	}

	if ($shipment && (float)$shipment->getPrice() > 0)
	{
		$sDeliveryItem = Loc::getMessage('SALE_HPS_BILLUA_DELIVERY');
		if ($shipment->getDeliveryName())
			$sDeliveryItem .= sprintf(" (%s)", $shipment->getDeliveryName());
		$arCells[++$n] = array(
			1 => $n,
			htmlspecialcharsbx($sDeliveryItem),
			1,
			'',
			SaleFormatCurrency(
				$shipment->getPrice(),
				$shipment->getCurrency(),
				true
			),
			roundEx($vat*100, SALE_VALUE_PRECISION) . "%",
			SaleFormatCurrency(
				$shipment->getPrice(),
				$shipment->getCurrency(),
				true
			)
		);

		$sum += doubleval($shipment->getPrice());
	}

	$items = $n;

	$orderTax = 0;
	$taxes = $order->getTax();

	$taxesList = $taxes->getTaxList();
	if ($taxesList)
	{
		foreach ($taxesList as $tax)
		{
			$arCells[++$n] = array(
					1 => null,
					null,
					null,
					null,
					null,
					htmlspecialcharsbx(sprintf(
							"%s%s%s:",
							($tax["IS_IN_PRICE"] == "Y") ? Loc::getMessage('SALE_HPS_BILLUA_IN_PRICE') : "",
							($vat <= 0) ? $tax["TAX_NAME"] : Loc::getMessage('SALE_HPS_BILLUA_TAX'),
							($vat <= 0 && $tax["IS_PERCENT"] == "Y")
									? sprintf(' (%s%%)', roundEx($tax["VALUE"], SALE_VALUE_PRECISION))
									: ""
					)),
					SaleFormatCurrency(
							$tax["VALUE_MONEY"],
							$order->getCurrency(),
							true
					)
			);
			$orderTax += $tax["VALUE_MONEY"];
		}
	}

	$sumPaid = $paymentCollection->getPaidSum();
	if (DoubleVal($sumPaid) > 0)
	{
		$arCells[++$n] = array(
			1 => null,
			null,
			null,
			null,
			null,
			Loc::getMessage('SALE_HPS_BILLUA_PAYMENT_PAID').':',
			SaleFormatCurrency(
				$sumPaid,
				$order->getCurrency(),
				true
			)
		);
	}

	if (DoubleVal($order->getDiscountPrice()) > 0)
	{
		$arCells[++$n] = array(
			1 => null,
			null,
			null,
			null,
			null,
			Loc::getMessage('SALE_HPS_BILLUA_DISCOUNT').':',
			SaleFormatCurrency(
				$order->getDiscountPrice(),
				$order->getCurrency(),
				true
			)
		);
	}

	$arCells[++$n] = array(
		1 => null,
		null,
		null,
		null,
		null,
		$vat <= 0 ? Loc::getMessage('SALE_HPS_BILLUA_SUM_WITHOUT_TAX').':' : Loc::getMessage('SALE_HPS_BILLUA_SUM').':',
		SaleFormatCurrency(
			$payment->getSum(),
			$order->getCurrency(),
			true
		)
	);

	$showVat = false;
}

$arCurFormat = CCurrencyLang::GetCurrencyFormat($order->getCurrency());
$currency = trim(str_replace('#', '', $arCurFormat['FORMAT_STRING']));
?>
<table class="it" width="100%">
	<tr>
		<td><nobr><?=Loc::getMessage('SALE_HPS_BILLUA_POS');?></nobr></td>
		<td><nobr><?=Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEM')?></nobr></td>
		<td><nobr><?=Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEM_QUANTITY')?></nobr></td>
		<td><nobr><?=Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEM_MEASURE')?></nobr></td>
		<td><nobr><? if ($vat <= 0) { ?><?=Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEM_PRICE')?><? } else { ?><?=Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEM_PRICE_TAX')?><? } ?>, <?=$currency; ?></nobr></td>
		<? if ($showVat) { ?>
		<td><nobr><?=Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEM_TAX')?></nobr></td>
		<? } ?>
		<td><nobr><? if ($vat <= 0) { ?><?=Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEM_SUM')?><? } else { ?><?=Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEM_SUM_TAX')?><? } ?>, <?=$currency; ?></nobr></td>
	</tr>
<?

$rowsCnt = count($arCells);
for ($n = 1; $n <= $rowsCnt; $n++)
{
	$accumulated = 0;

?>
	<tr valign="top">
		<? if (!is_null($arCells[$n][1])) { ?>
		<td align="center"><?=$arCells[$n][1]; ?></td>
		<? } else {
			$accumulated++;
		} ?>
		<? if (!is_null($arCells[$n][2])) { ?>
		<td align="left"
			style="word-break: break-word; word-wrap: break-word; <? if ($accumulated) {?>border-width: 0pt 1pt 0pt 0pt; <? } ?>"
			<? if ($accumulated) { ?>colspan="<?=($accumulated+1); ?>"<? $accumulated = 0; } ?>>
			<?=$arCells[$n][2]; ?>
			<? if (isset($arProps[$n]) && is_array($arProps[$n])) { ?>
			<? foreach ($arProps[$n] as $property) { ?>
			<br>
			<small><?=$property; ?></small>
			<? } ?>
			<? } ?>
		</td>
		<? } else {
			$accumulated++;
		} ?>
		<? for ($i = 3; $i <= 7; $i++) { ?>
			<? if (!is_null($arCells[$n][$i])) { ?>
				<? if ($i != 6 || $showVat || is_null($arCells[$n][2])) { ?>
				<td align="right"
					<? if ($accumulated) { ?>
					style="border-width: 0pt 1pt 0pt 0pt"
					colspan="<?=(($i == 6 && !$showVat) ? $accumulated : $accumulated+1); ?>"
					<? $accumulated = 0; } ?>>
					<nobr><?=$arCells[$n][$i]; ?></nobr>
				</td>
				<? }
			} else {
				$accumulated++;
			}
		} ?>
	</tr>
<?

}

?>
</table>
<br>

<b><?=sprintf(
	Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEMS_TOTAL'),
	$items,
	($order->getCurrency() == "UAH")
		? Number2Word_Rus(
			$payment->getSum(),
			"Y",
			$payment->getField('CURRENCY')
		)
		: SaleFormatCurrency(
			$payment->getSum(),
			$payment->getField('CURRENCY'),
			false
		)
); ?></b>
<br>

<? if ($vat > 0) { ?>
<b><?=sprintf(
	Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEMS_TAX'),
	($order->getCurrency() == "UAH")
		? Number2Word_Rus($orderTax, "Y", $payment->getField('CURRENCY'))
		: SaleFormatCurrency($orderTax, $payment->getField('CURRENCY'), false)
); ?></b>
<? } else { ?>
<b><?=Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEMS_WITHOUT_TAX');?></b>
<? } ?>
<br>
<br>

<? if ($params["BILLUA_COMMENT1"] || $params["BILLUA_COMMENT2"]) { ?>
<b><?=Loc::getMessage('SALE_HPS_BILLUA_COMMENT')?></b>
<br>
	<? if ($params["BILLUA_COMMENT1"]) { ?>
	<?=nl2br(HTMLToTxt(preg_replace(
		array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
		htmlspecialcharsback($params["BILLUA_COMMENT1"])
	), '', array(), 0)); ?>
	<br>
	<br>
	<? } ?>
	<? if ($params["BILLUA_COMMENT2"]) { ?>
	<?=nl2br(HTMLToTxt(preg_replace(
		array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
		htmlspecialcharsback($params["BILLUA_COMMENT2"])
	), '', array(), 0)); ?>
	<br>
	<br>
	<? } ?>
<? } ?>

<div style="border-bottom: 1pt solid #000000; width:100%; ">&nbsp;</div>

<? if (!$blank) { ?>
<div style="position: relative; "><?=CFile::ShowImage(
	$params["BILLUA_PATH_TO_STAMP"],
	160, 160,
	'style="position: absolute; left: 40pt; "'
); ?></div>
<? } ?>

<br>

<div style="position: relative">
	<table class="sign">
		<tr>
			<td><?=Loc::getMessage('SALE_HPS_BILLUA_WRITER')?>:&nbsp;</td>
			<td style="width: 160pt; border: 1pt solid #000000; border-width: 0pt 0pt 1pt 0pt; text-align: center; ">
				<? if (!$blank) { ?>
				<?=CFile::ShowImage($params["SELLER_COMPANY_ACC_SIGN"], 200, 50); ?>
				<? } ?>
			</td>
			<td style="width: 160pt; ">
				<input
					style="border: none; background: none; width: 100%; "
					type="text"
					value="<?=$params["SELLER_COMPANY_ACCOUNTANT_NAME"]; ?>"
				>
			</td>
			<td style="width: 20pt; ">&nbsp;</td>
			<td><?=Loc::getMessage('SALE_HPS_BILLUA_ACC_POSITION')?>:&nbsp;</td>
			<td style="width: 160pt; border: 1pt solid #000000; border-width: 0pt 0pt 1pt 0pt; ">
				<input
					style="border: none; background: none; width: 100%; text-align: center; "
					type="text"
					value="<?=$params["SELLER_COMPANY_ACCOUNTANT_POSITION"]; ?>"
				>
			</td>
		</tr>
	</table>
</div>

<br>
<br>

<? if ($params["DATE_PAY_BEFORE"]) { ?>
<div style="text-align: right; "><b><?=sprintf(
	Loc::getMessage('SALE_HPS_BILLUA_DATE_PAID_BEFORE'),
	ConvertDateTime($params["DATE_PAY_BEFORE"], FORMAT_DATE)
		?: $params["DATE_PAY_BEFORE"]
); ?></b></div>
<? } ?>

</div>

</body>
</html>