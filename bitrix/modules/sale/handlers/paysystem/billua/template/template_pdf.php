<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!CSalePdf::isPdfAvailable())
	die();

if ($_REQUEST['BLANK'] == 'Y')
	$blank = true;

$pdf = new CSalePdf('P', 'pt', 'A4');

if ($params['BILLUA_BACKGROUND'])
{
	$pdf->SetBackground(
		$params['BILLUA_BACKGROUND'],
		$params['BILLUA_BACKGROUND_STYLE']
	);
}

$pageWidth  = $pdf->GetPageWidth();
$pageHeight = $pdf->GetPageHeight();

$pdf->AddFont('Font', '', 'pt_sans-regular.ttf', true);
$pdf->AddFont('Font', 'B', 'pt_sans-bold.ttf', true);

$fontFamily = 'Font';
$fontSize   = 10.5;

$margin = array(
	'top' => intval($params['BILLUA_MARGIN_TOP'] ?: 15) * 72/25.4,
	'right' => intval($params['BILLUA_MARGIN_RIGHT'] ?: 15) * 72/25.4,
	'bottom' => intval($params['BILLUA_MARGIN_BOTTOM'] ?: 15) * 72/25.4,
	'left' => intval($params['BILLUA_MARGIN_LEFT'] ?: 20) * 72/25.4
);

$width = $pageWidth - $margin['left'] - $margin['right'];

$pdf->SetDisplayMode(100, 'continuous');
$pdf->SetMargins($margin['left'], $margin['top'], $margin['right']);
$pdf->SetAutoPageBreak(true, $margin['bottom']);

$pdf->AddPage();


$pdf->SetFont($fontFamily, 'B', $fontSize);

$pdf->Write(15, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_TITLE', array('#PAYMENT_NUMBER#' => htmlspecialcharsbx($params["ACCOUNT_NUMBER"]), '#PAYMENT_DATE#' => $params["DATE_INSERT"]))));
$pdf->Ln();
$pdf->Ln();

$pdf->SetFont($fontFamily, '', $fontSize);

$title = CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_SELLER').': ');
$title_width = $pdf->GetStringWidth($title);
$pdf->Write(15, $title);

$pdf->Write(15, CSalePdf::prepareToPdf($params["SELLER_COMPANY_NAME"]));
$pdf->Ln();

$pdf->Cell($title_width, 15, '');
$pdf->MultiCell(0, 15, CSalePdf::prepareToPdf(sprintf(
		Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_RS').' %s, '.Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_BANK').' %s, '.Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_MFO').' %s',
	$params["SELLER_COMPANY_BANK_ACCOUNT"],
	$params["SELLER_COMPANY_BANK_NAME"],
	$params["SELLER_COMPANY_MFO"]
)));

$sellerAddr = '';
if ($params["SELLER_COMPANY_ADDRESS"])
{
	$sellerAddr = $params["SELLER_COMPANY_ADDRESS"];
	if (is_array($sellerAddr))
		$sellerAddr = implode(', ', $sellerAddr);
	else
		$sellerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($sellerAddr));
}

$pdf->Cell($title_width, 15, '');
$pdf->MultiCell(0, 15, CSalePdf::prepareToPdf(sprintf(
	Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_ADDRESS').': %s, '.Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_PHONE').': %s',
	$sellerAddr,
	$params["SELLER_COMPANY_PHONE"]
)));

$pdf->Cell($title_width, 15, '');
$pdf->MultiCell(0, 15, CSalePdf::prepareToPdf(sprintf(
	Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_EDRPOY').': %s, '.Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_IPN').': %s, '.Loc::getMessage('SALE_HPS_BILLUA_SELLER_COMPANY_PDV').': %s',
	$params["SELLER_COMPANY_EDRPOY"],
	$params["SELLER_COMPANY_IPN"],
	$params["SELLER_COMPANY_PDV"]
)));

if ($params["SELLER_COMPANY_SYS"])
{
	$pdf->Cell($title_width, 15, '');
	$pdf->Write(15, CSalePdf::prepareToPdf($params["SELLER_COMPANY_SYS"]));
	$pdf->Ln();
}
$pdf->Ln();

$pdf->Cell($title_width, 15, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_BUYER').': '));

$pdf->Write(15, CSalePdf::prepareToPdf($params["BUYER_PERSON_COMPANY_NAME"]));
$pdf->Ln();

$buyerPhone = $params["BUYER_PERSON_COMPANY_PHONE"];
$buyerFax = $params["BUYER_PERSON_COMPANY_FAX"];
if ($buyerPhone || $buyerFax)
{
	$pdf->Cell($title_width, 15, '');

	if ($buyerPhone)
	{
		$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(Loc::getMessage('SALE_HPS_BILLUA_BUYER_PHONE').': %s', $buyerPhone)));
		if ($buyerFax)
			$pdf->Write(15, CSalePdf::prepareToPdf(', '));
	}

	if ($buyerFax)
		$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(Loc::getMessage('SALE_HPS_BILLUA_BUYER_FAX').': %s', $buyerFax)));

	$pdf->Ln();
}

if ($params["BUYER_PERSON_COMPANY_ADDRESS"])
{
	$buyerAddr = $params["BUYER_PERSON_COMPANY_ADDRESS"];
	if (is_array($buyerAddr))
		$buyerAddr = implode(', ', $buyerAddr);
	else
		$buyerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($buyerAddr));
	$pdf->Cell($title_width, 15, '');
	$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(
		Loc::getMessage('SALE_HPS_BILLUA_BUYER_ADDRESS').': %s',
		$buyerAddr
	)));
	$pdf->Ln();
}

$pdf->Ln();

if ($params["BUYER_PERSON_COMPANY_DOGOVOR"])
{
	$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(
		Loc::getMessage('SALE_HPS_BILLUA_BUYER_DOGOVOR').': %s',
			$params["BUYER_PERSON_COMPANY_DOGOVOR"]
	)));

	$pdf->Ln();
}

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
	$arRowsWidth = array(1 => 0, 0, 0, 0, 0, 0, 0);

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
			1 => CSalePdf::prepareToPdf($n),
			CSalePdf::prepareToPdf($productName),
			CSalePdf::prepareToPdf(roundEx($basketItem->getQuantity(), SALE_VALUE_PRECISION)),
			CSalePdf::prepareToPdf($basketItem->getField("MEASURE_NAME") ? $basketItem->getField("MEASURE_NAME") : Loc::getMessage('SALE_HPS_BILLUA_MEASHURE')),
			CSalePdf::prepareToPdf(SaleFormatCurrency($basketItem->getPrice(), $basketItem->getCurrency(), true)),
			CSalePdf::prepareToPdf(roundEx($basketItem->getVatRate()*100, SALE_VALUE_PRECISION)."%"),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$basketItemPrice * $basketItem->getQuantity(),
				$basketItem->getCurrency(),
				true
			))
		);

		$arProps[$n] = array();

		/** @var \Bitrix\Sale\BasketPropertyItem $basketPropertyItem */
		foreach ($basketItem->getPropertyCollection() as $basketPropertyItem)
		{
			if ($basketPropertyItem->getField('CODE') == 'CATALOG.XML_ID' || $basketPropertyItem->getField('CODE') == 'PRODUCT.XML_ID')
				continue;
			$arProps[$n][] = CSalePdf::prepareToPdf(sprintf("%s: %s", $basketPropertyItem->getField("NAME"), $basketPropertyItem->getField("VALUE")));
		}

		for ($i = 1; $i <= 7; $i++)
			$arRowsWidth[$i] = max($arRowsWidth[$i], $pdf->GetStringWidth($arCells[$n][$i]));

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
			1 => CSalePdf::prepareToPdf($n),
			CSalePdf::prepareToPdf($sDeliveryItem),
			CSalePdf::prepareToPdf(1),
			CSalePdf::prepareToPdf(''),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$shipment->getPrice(),
				$shipment->getCurrency(),
				true
			)),
			CSalePdf::prepareToPdf(roundEx($vat*100, SALE_VALUE_PRECISION)."%"),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$shipment->getPrice(),
				$shipment->getCurrency(),
				true
			))
		);

		for ($i = 1; $i <= 7; $i++)
			$arRowsWidth[$i] = max($arRowsWidth[$i], $pdf->GetStringWidth($arCells[$n][$i]));

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
				CSalePdf::prepareToPdf(sprintf(
					"%s%s%s:",
					($tax["IS_IN_PRICE"] == "Y") ? Loc::getMessage('SALE_HPS_BILLUA_IN_PRICE') : "",
					($vat <= 0) ? $tax["TAX_NAME"] : Loc::getMessage('SALE_HPS_BILLUA_TAX'),
					($vat <= 0 && $tax["IS_PERCENT"] == "Y")
						? sprintf(' (%s%%)', roundEx($tax["VALUE"],SALE_VALUE_PRECISION))
						: ""
				)),
				CSalePdf::prepareToPdf(SaleFormatCurrency(
					$tax["VALUE_MONEY"],
					$order->getCurrency(),
					true
				))
			);

			$orderTax += $tax["VALUE_MONEY"];

			$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));
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
			CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_PAYMENT_PAID').":"),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$sumPaid,
				$order->getCurrency(),
				true
			))
		);

		$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));
	}

	if (DoubleVal($order->getDiscountPrice()) > 0)
	{
		$arCells[++$n] = array(
			1 => null,
			null,
			null,
			null,
			null,
			CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_DISCOUNT').":"),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$order->getDiscountPrice(),
				$order->getCurrency(),
				true
			))
		);

		$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));
	}

	$arCells[++$n] = array(
		1 => null,
		null,
		null,
		null,
		null,
		CSalePdf::prepareToPdf($vat <= 0 ? Loc::getMessage('SALE_HPS_BILLUA_SUM_WITHOUT_TAX').':' : Loc::getMessage('SALE_HPS_BILLUA_SUM').':'),
		CSalePdf::prepareToPdf(SaleFormatCurrency(
			$payment->getSum(),
			$order->getCurrency(),
			true
		))
	);

	$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));

	$showVat = false;

	$arCurFormat = CCurrencyLang::GetCurrencyFormat($order->getCurrency());
	$currency = trim(str_replace('#', '', $arCurFormat['FORMAT_STRING']));

	$arColsCaption = array(
		1 => CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_POS')),
		CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEM')),
		CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEM_QUANTITY')),
		CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEM_MEASURE')),
		CSalePdf::prepareToPdf(($vat <= 0 ? Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEM_PRICE').', ' : Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEM_PRICE_TAX').', ').$currency),
		CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEM_TAX')),
		CSalePdf::prepareToPdf(($vat <= 0 ? Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEM_SUM').', ' : Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEM_SUM_TAX').', ').$currency)
	);
	for ($i = 1; $i <= 7; $i++)
		$arRowsWidth[$i] = max($arRowsWidth[$i], $pdf->GetStringWidth($arColsCaption[$i]));

	for ($i = 1; $i <= 7; $i++)
		$arRowsWidth[$i] += 10;
	if (!$showVat)
		$arRowsWidth[6] = 0;
	$arRowsWidth[2] = $width - (array_sum($arRowsWidth)-$arRowsWidth[2]);
}
$pdf->Ln();

$x0 = $pdf->GetX();
$y0 = $pdf->GetY();

for ($i = 1; $i <= 7; $i++)
{
	if ($showVat || $i != 6)
		$pdf->Cell($arRowsWidth[$i], 20, $arColsCaption[$i], 0, 0, 'C');
	${"x$i"} = $pdf->GetX();
}

$pdf->Ln();

$y5 = $pdf->GetY();

$pdf->Line($x0, $y0, $x7, $y0);
for ($i = 0; $i <= 7; $i++)
{
	if ($showVat || $i != 6)
		$pdf->Line(${"x$i"}, $y0, ${"x$i"}, $y5);
}
$pdf->Line($x0, $y5, $x7, $y5);

$rowsCnt = count($arCells);
for ($n = 1; $n <= $rowsCnt; $n++)
{
	$arRowsWidth_tmp = $arRowsWidth;
	$accumulated = 0;
	for ($j = 1; $j <= 7; $j++)
	{
		if (is_null($arCells[$n][$j]))
		{
			$accumulated += $arRowsWidth_tmp[$j];
			$arRowsWidth_tmp[$j] = null;
		}
		else
		{
			$arRowsWidth_tmp[$j] += $accumulated;
			$accumulated = 0;
		}
	}

	$x0 = $pdf->GetX();
	$y0 = $pdf->GetY();

	$pdf->SetFont($fontFamily, '', $fontSize);

	if (!is_null($arCells[$n][2]))
	{
		$text = $arCells[$n][2];
		$cellWidth = $arRowsWidth_tmp[2];
	}
	else
	{
		$text = $arCells[$n][6];
		$cellWidth = $arRowsWidth_tmp[6];
	}

	for ($l = 0; $pdf->GetStringWidth($text) > 0; $l++)
	{
		list($string, $text) = $pdf->splitString($text, $cellWidth-5);

		if (!is_null($arCells[$n][1]))
			$pdf->Cell($arRowsWidth_tmp[1], 15, ($l == 0) ? $arCells[$n][1] : '', 0, 0, 'C');
		if ($l == 0)
			$x1 = $pdf->GetX();

		if (!is_null($arCells[$n][2]))
			$pdf->Cell($arRowsWidth_tmp[2], 15, $string);
		if ($l == 0)
			$x2 = $pdf->GetX();

		if (!is_null($arCells[$n][3]))
			$pdf->Cell($arRowsWidth_tmp[3], 15, ($l == 0) ? $arCells[$n][3] : '', 0, 0, 'R');
		if ($l == 0)
			$x3 = $pdf->GetX();

		if (!is_null($arCells[$n][4]))
			$pdf->Cell($arRowsWidth_tmp[4], 15, ($l == 0) ? $arCells[$n][4] : '', 0, 0, 'R');
		if ($l == 0)
			$x4 = $pdf->GetX();

		if (!is_null($arCells[$n][5]))
			$pdf->Cell($arRowsWidth_tmp[5], 15, ($l == 0) ? $arCells[$n][5] : '', 0, 0, 'R');
		if ($l == 0)
			$x5 = $pdf->GetX();

		if (!is_null($arCells[$n][6]))
		{
			if (is_null($arCells[$n][2]))
				$pdf->Cell($arRowsWidth_tmp[6], 15, $string, 0, 0, 'R');
			else if ($showVat)
				$pdf->Cell($arRowsWidth_tmp[6], 15, ($l == 0) ? $arCells[$n][6] : '', 0, 0, 'R');
		}
		if ($l == 0)
			$x6 = $pdf->GetX();

		if (!is_null($arCells[$n][7]))
			$pdf->Cell($arRowsWidth_tmp[7], 15, ($l == 0) ? $arCells[$n][7] : '', 0, 0, 'R');
		if ($l == 0)
			$x7 = $pdf->GetX();

		$pdf->Ln();
	}

	if (isset($arProps[$n]) && is_array($arProps[$n]))
	{
		$pdf->SetFont($fontFamily, '', $fontSize-2);
		foreach ($arProps[$n] as $property)
		{
			$pdf->Cell($arRowsWidth_tmp[1], 12, '');
			$pdf->Cell($arRowsWidth_tmp[2], 12, $property);
			$pdf->Cell($arRowsWidth_tmp[3], 12, '');
			$pdf->Cell($arRowsWidth_tmp[4], 12, '');
			$pdf->Cell($arRowsWidth_tmp[5], 12, '');
			if ($showVat)
				$pdf->Cell($arRowsWidth_tmp[6], 12, '');
			$pdf->Cell($arRowsWidth_tmp[7], 12, '', 0, 1);
		}
	}

	$y5 = $pdf->GetY();

	if ($y0 > $y5)
		$y0 = $margin['top'];
	for ($i = (is_null($arCells[$n][1])) ? 6 : 0; $i <= 7; $i++)
	{
		if ($showVat || $i != 5)
			$pdf->Line(${"x$i"}, $y0, ${"x$i"}, $y5);
	}

	$pdf->Line((!is_null($arCells[$n][1])) ? $x0 : $x6, $y5, $x7, $y5);
}
$pdf->Ln();

$pdf->SetFont($fontFamily, 'B', $fontSize);
$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(
	Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEMS_TOTAL'),
	$items,
	($payment->getField('CURRENCY') == "UAH")
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
)));
$pdf->Ln();

if ($vat > 0)
{
	$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(
			Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEMS_TAX'),
		($payment->getField('CURRENCY') == "UAH")
			? Number2Word_Rus($orderTax, "Y", $payment->getField('CURRENCY'))
			: SaleFormatCurrency($orderTax, $payment->getField('CURRENCY'), false)
	)));
}
else
{
	$pdf->Write(15, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_BASKET_ITEMS_WITHOUT_TAX')));
}
$pdf->Ln();
$pdf->Ln();

if ($params["BILLUA_COMMENT1"] || $params["BILLUA_COMMENT2"])
{
	$pdf->Write(15, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_COMMENT')));
	$pdf->Ln();

	$pdf->SetFont($fontFamily, '', $fontSize);

	if ($params["BILLUA_COMMENT1"])
	{
		$pdf->Write(15, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf($params["BILLUA_COMMENT1"])
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}

	if ($params["BILLUA_COMMENT2"])
	{
		$pdf->Write(15, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf($params["BILLUA_COMMENT2"])
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}
}

$pdf->Ln();
if ($params['BILLUA_PATH_TO_STAMP'])
{
	$filePath = $pdf->GetImagePath($params['BILLUA_PATH_TO_STAMP']);
	if ($filePath != '' && !$blank && \Bitrix\Main\IO\File::isFileExists($filePath))
	{
		list($stampHeight, $stampWidth) = $pdf->GetImageSize($params['BILLUA_PATH_TO_STAMP']);
		if ($stampHeight && $stampWidth)
		{
			if ($stampHeight > 120 || $stampWidth > 120)
			{
				$ratio = 120 / max($stampHeight, $stampWidth);
				$stampHeight = $ratio * $stampHeight;
				$stampWidth = $ratio * $stampWidth;
			}
			$pdf->Image(
					$params['BILLUA_PATH_TO_STAMP'],
					$margin['left'] + 40, $pdf->GetY(),
					$stampWidth, $stampHeight
			);
		}
	}
}

$pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX()+$width, $pdf->GetY());
$pdf->Ln();
$pdf->Ln();

$isAccSign = false;
if (!$blank && $params['SELLER_COMPANY_ACC_SIGN'])
{
	list($signHeight, $signWidth) = $pdf->GetImageSize($params['SELLER_COMPANY_ACC_SIGN']);

	if ($signHeight && $signWidth)
	{
		$ratio = min(37.5/$signHeight, 150/$signWidth);
		$signHeight = $ratio * $signHeight;
		$signWidth  = $ratio * $signWidth;

		$isAccSign = true;
	}
}

$pdf->SetFont($fontFamily, 'B', $fontSize);
$pdf->Write(15, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_WRITER').': '));

if ($isAccSign)
{
	$pdf->Image(
		$params['SELLER_COMPANY_ACC_SIGN'],
		$pdf->GetX() + 80 - $signWidth/2, $pdf->GetY() - $signHeight + 15,
		$signWidth, $signHeight
	);
}

$pdf->SetFont($fontFamily, '', $fontSize);
$pdf->Cell(160, 15, '', 'B', 0, 'C');

$pdf->Write(15, CSalePdf::prepareToPdf($params["SELLER_COMPANY_ACCOUNTANT_NAME"]));

$pdf->SetX(max($pdf->GetX()+20, $margin['left']+3*$width/5));

$pdf->SetFont($fontFamily, 'B', $fontSize);
$pdf->Write(15, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILLUA_ACC_POSITION').': '));

$pdf->SetFont($fontFamily, '', $fontSize);
$pdf->Cell(0, 15, CSalePdf::prepareToPdf($params["SELLER_COMPANY_ACCOUNTANT_POSITION"]), 'B', 0, 'C');

$pdf->Ln();
$pdf->Ln();
$pdf->Ln();

if ($params["DATE_PAY_BEFORE"])
{
	$pdf->SetFont($fontFamily, 'B', $fontSize);
	$pdf->Cell(0, 15, CSalePdf::prepareToPdf(sprintf(
		Loc::getMessage('SALE_HPS_BILLUA_DATE_PAID_BEFORE'),
		ConvertDateTime($params["DATE_PAY_BEFORE"], FORMAT_DATE)
			?: $params["DATE_PAY_BEFORE"]
	)), 0, 0, 'R');
}


$dest = 'I';
if ($_REQUEST['GET_CONTENT'] == 'Y')
	$dest = 'S';
else if ($_REQUEST['DOWNLOAD'] == 'Y')
	$dest = 'D';

return $pdf->Output(
	sprintf(
		'Rakhunok No%s vid %s.pdf',
		$params["ACCOUNT_NUMBER"],
		ConvertDateTime($payment->getField("DATE_BILL"), 'YYYY-MM-DD')
	), $dest
);
?>