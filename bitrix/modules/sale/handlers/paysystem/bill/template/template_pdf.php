<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

$arPaySysAction["ENCODING"] = "";

if (!CSalePdf::isPdfAvailable())
	die();

if ($_REQUEST['BLANK'] == 'Y')
	$blank = true;

/** @var CSaleTfpdf $pdf */
$pdf = new CSalePdf('P', 'pt', 'A4');

if ($params['BILL_BACKGROUND'])
{
	$pdf->SetBackground(
		$params['BILL_BACKGROUND'],
		$params['BILL_BACKGROUND_STYLE']
	);
}

$pageWidth  = $pdf->GetPageWidth();
$pageHeight = $pdf->GetPageHeight();

$pdf->AddFont('Font', '', 'pt_sans-regular.ttf', true);
$pdf->AddFont('Font', 'B', 'pt_sans-bold.ttf', true);

$fontFamily = 'Font';
$fontSize   = 10.5;

$margin = array(
	'top' => intval($params['BILL_MARGIN_TOP'] ?: 15) * 72/25.4,
	'right' => intval($params['BILL_MARGIN_RIGHT'] ?: 15) * 72/25.4,
	'bottom' => intval($params['BILL_MARGIN_BOTTOM'] ?: 15) * 72/25.4,
	'left' => intval($params['BILL_MARGIN_LEFT'] ?: 20) * 72/25.4
);

$width = $pageWidth - $margin['left'] - $margin['right'];

$pdf->SetDisplayMode(100, 'continuous');
$pdf->SetMargins($margin['left'], $margin['top'], $margin['right']);
$pdf->SetAutoPageBreak(true, $margin['bottom']);

$pdf->AddPage();


$y0 = $pdf->GetY();
$logoHeight = 0;
$logoWidth = 0;

if ($params['BILL_PATH_TO_LOGO'])
{
	list($imageHeight, $imageWidth) = $pdf->GetImageSize($params['BILL_PATH_TO_LOGO']);

	$imgDpi = intval($params['BILL_LOGO_DPI']) ?: 96;
	$imgZoom = 96 / $imgDpi;

	$logoHeight = $imageHeight * $imgZoom + 5;
	$logoWidth  = $imageWidth * $imgZoom + 5;

	$pdf->Image($params['BILL_PATH_TO_LOGO'], $pdf->GetX(), $pdf->GetY(), -$imgDpi, -$imgDpi);
}

$pdf->SetFont($fontFamily, 'B', $fontSize);

$pdf->SetX($pdf->GetX() + $logoWidth);
$pdf->Write(15, CSalePdf::prepareToPdf($params["SELLER_COMPANY_NAME"]));
$pdf->Ln();

if ($params["SELLER_COMPANY_ADDRESS"])
{
	$sellerAddr = $params["SELLER_COMPANY_ADDRESS"];
	if (is_array($sellerAddr))
		$sellerAddr = implode(', ', $sellerAddr);
	else
		$sellerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($sellerAddr));
	$pdf->SetX($pdf->GetX() + $logoWidth);
	$pdf->MultiCell(0, 15, CSalePdf::prepareToPdf($sellerAddr), 0, 'L');
}

if ($params["SELLER_COMPANY_PHONE"])
{
	$pdf->SetX($pdf->GetX() + $logoWidth);
	$pdf->Write(15, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_SELLER_COMPANY_PHONE', array('#PHONE#' => $params["SELLER_COMPANY_PHONE"]))));
	$pdf->Ln();
}

$pdf->Ln();
$pdf->SetY(max($y0 + $logoHeight, $pdf->GetY()));

if ($params["SELLER_COMPANY_BANK_NAME"])
{
	$sellerBankCity = '';
	if ($params["SELLER_COMPANY_BANK_CITY"])
	{
		$sellerBankCity = $params["SELLER_COMPANY_BANK_CITY"];
		if (is_array($sellerBankCity))
			$sellerBankCity = implode(', ', $sellerBankCity);
		else
			$sellerBankCity = str_replace(array("\r\n", "\n", "\r"), ', ', strval($sellerBankCity));
	}
	$sellerBank = sprintf(
		"%s %s",
		$params["SELLER_COMPANY_BANK_NAME"],
		$sellerBankCity
	);
	unset($sellerBankCity);
	$sellerRs = $params["SELLER_COMPANY_BANK_ACCOUNT"];
}
else
{
	$rsPattern = '/\s*\d{10,100}\s*/';

	$sellerBank = trim(preg_replace($rsPattern, ' ', $params["SELLER_COMPANY_BANK_ACCOUNT"]));

	preg_match($rsPattern, $params["SELLER_COMPANY_BANK_ACCOUNT"], $matches);
	$sellerRs = trim($matches[0]);
}

$pdf->SetFont($fontFamily, '', $fontSize);

$x0 = $pdf->GetX();
$y0 = $pdf->GetY();

$pdf->Cell(
	150, 18,
	($params["SELLER_COMPANY_INN"])
		? CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_INN', array('#INN#' => $params["SELLER_COMPANY_INN"])))
		: ''
);
$x1 = $pdf->GetX();
$pdf->Cell(
	150, 18,
	($params["SELLER_COMPANY_KPP"])
		? CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_KPP', array('#KPP#' => $params["SELLER_COMPANY_KPP"])))
		: ''
);
$x2 = $pdf->GetX();
$pdf->Cell(50, 18);
$x3 = $pdf->GetX();
$pdf->Cell(0, 18);
$x4 = $pdf->GetX();

$pdf->Line($x0, $y0, $x4, $y0);

$pdf->Ln();
$y1 = $pdf->GetY();

$pdf->Line($x1, $y0, $x1, $y1);

$pdf->Cell(300, 18, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_SELLER_NAME')));
$pdf->Cell(50, 18);
$pdf->Cell(0, 18);

$pdf->Line($x0, $y1, $x2, $y1);

$pdf->Ln();
$y2 = $pdf->GetY();

$text = $params["SELLER_COMPANY_NAME"];
while ($pdf->GetStringWidth($text) > 0)
{
	list($string, $text) = $pdf->splitString($text, 300-5);

	$pdf->Cell(300, 18, CSalePdf::prepareToPdf($string));
	if ($text)
		$pdf->Ln();
}
$pdf->Cell(50, 18, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_SELLER_ACC')));
$size = $pdf->GetPageWidth()-$pdf->GetX()-$margin['right'];
$sellerRs = CSalePdf::prepareToPdf($sellerRs);
while ($pdf->GetStringWidth($sellerRs) > 0)
{
	list($string, $sellerRs) = $pdf->splitString($sellerRs, $size-5);

	$pdf->Cell(0, 18, $string);
	if ($sellerRs)
	{
		$pdf->Ln();
		$pdf->Cell(300, 18, '');
		$pdf->Cell(50, 18, '');
	}
}

$pdf->Ln();
$y3 = $pdf->GetY();

$pdf->Cell(300, 18, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_SELLER_BANK_NAME')));
$pdf->Cell(50, 18, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_SELLER_BANK_BIK')));
$pdf->Cell(0, 18, CSalePdf::prepareToPdf($params["SELLER_COMPANY_BANK_BIC"]));

$pdf->Line($x0, $y3, $x4, $y3);

$pdf->Ln();
$y4 = $pdf->GetY();

$text = CSalePdf::prepareToPdf($sellerBank);
while ($pdf->GetStringWidth($text) > 0)
{
	list($string, $text) = $pdf->splitString($text, 300-5);

	$pdf->Cell(300, 18, $string);
	if ($text)
		$pdf->Ln();
}
$pdf->Cell(50, 18, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_SELLER_ACC_CORR')));

$bankAccountCorr = CSalePdf::prepareToPdf($params["SELLER_COMPANY_BANK_ACCOUNT_CORR"]);
while ($pdf->GetStringWidth($bankAccountCorr) > 0)
{
	list($string, $bankAccountCorr) = $pdf->splitString($bankAccountCorr, $size-5);

	$pdf->Cell(0, 18, $string);
	if ($bankAccountCorr)
	{
		$pdf->Ln();
		$pdf->Cell(300, 18, '');
		$pdf->Cell(50, 18, '');
	}
}

$pdf->Ln();
$y5 = $pdf->GetY();

$pdf->Line($x0, $y5, $x4, $y5);

$pdf->Line($x0, $y0, $x0, $y5);
$pdf->Line($x2, $y0, $x2, $y5);
$pdf->Line($x3, $y0, $x3, $y5);
$pdf->Line($x4, $y0, $x4, $y5);

$pdf->Ln();
$pdf->Ln();

$pdf->SetFont($fontFamily, 'B', $fontSize*2);
$billNo_tmp = CSalePdf::prepareToPdf(
	Loc::getMessage('SALE_HPS_BILL_SELLER_TITLE', array('#PAYMENT_NUM#' => $params["ACCOUNT_NUMBER"], '#PAYMENT_DATE#' => $params["PAYMENT_DATE_INSERT"]))
);
$billNo_width = $pdf->GetStringWidth($billNo_tmp);
$pdf->Cell(0, 20, $billNo_tmp, 0, 0, 'C');
$pdf->Ln();

$pdf->SetFont($fontFamily, '', $fontSize);

if ($params["BILL_ORDER_SUBJECT"])
{
	$pdf->Cell($width/2-$billNo_width/2-2, 15, '');
	$pdf->MultiCell(0, 15, CSalePdf::prepareToPdf($params["BILL_ORDER_SUBJECT"]), 0, 'L');
}

if ($params["PAYMENT_DATE_PAY_BEFORE"])
{
	$pdf->Cell($width/2-$billNo_width/2-2, 15, '');
	$pdf->MultiCell(0, 15, CSalePdf::prepareToPdf(
			Loc::getMessage('SALE_HPS_BILL_SELLER_DATE_END', array('#PAYMENT_DATE_END#' => ConvertDateTime($params["PAYMENT_DATE_PAY_BEFORE"], FORMAT_DATE) ?: $params["PAYMENT_DATE_PAY_BEFORE"]))), 0, 'L');
}

$pdf->Ln();

if ($params["BUYER_PERSON_COMPANY_NAME"])
{
	$pdf->Write(15, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_BUYER_NAME', array('#BUYER_NAME#' => $params["BUYER_PERSON_COMPANY_NAME"]))));
	if ($params["BUYER_PERSON_COMPANY_INN"])
		$pdf->Write(15, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_BUYER_PERSON_INN', array('#INN#' => $params["BUYER_PERSON_COMPANY_INN"]))));
	if ($params["BUYER_PERSON_COMPANY_ADDRESS"])
	{
		$buyerAddr = $params["BUYER_PERSON_COMPANY_ADDRESS"];
		if (is_array($buyerAddr))
			$buyerAddr = implode(', ', $buyerAddr);
		else
			$buyerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($buyerAddr));
		$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(", %s", $buyerAddr)));
	}
	if ($params["BUYER_PERSON_COMPANY_PHONE"])
		$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(", %s", $params["BUYER_PERSON_COMPANY_PHONE"])));
	if ($params["BUYER_PERSON_COMPANY_FAX"])
		$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(", %s", $params["BUYER_PERSON_COMPANY_FAX"])));
	if ($params["BUYER_PERSON_COMPANY_NAME_CONTACT"])
		$pdf->Write(15, CSalePdf::prepareToPdf(sprintf(", %s", $params["BUYER_PERSON_COMPANY_NAME_CONTACT"])));
	$pdf->Ln();
}

/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
$paymentCollection = $payment->getCollection();

/** @var \Bitrix\Sale\Order $order */
$order = $paymentCollection->getOrder();

/** @var \Bitrix\Sale\Basket $basket */
$basket = $order->getBasket();

if (count($basket->getBasketItems()) > 0)
{
	$arCurFormat = CCurrencyLang::GetCurrencyFormat($payment->getField('CURRENCY'));
	$currency = preg_replace('/(^|[^&])#/', '${1}', $arCurFormat['FORMAT_STRING']);

	$arColsCaption = array(
		1 => CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_NUMBER')),
		CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_BASKET_ITEM_NAME')),
		CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_BASKET_ITEM_QUANTITY')),
		CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_BASKET_MEASURE')),
		CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_BASKET_ITEM_PRICE').', '.$currency),
		CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_BASKET_ITEM_VAT_RATE')),
		CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_BASKET_ITEM_SUM').', '.$currency)
	);
	$arCells = array();
	$arProps = array();
	$arRowsWidth = array(1 => 0, 0, 0, 0, 0, 0, 0);

	for ($i = 1; $i <= 7; $i++)
		$arRowsWidth[$i] = max($arRowsWidth[$i], $pdf->GetStringWidth($arColsCaption[$i]));

	$n = 0;
	$sum = 0.00;
	$vat = 0;
	/** @var \Bitrix\Sale\BasketItem $basketItem */
	foreach ($basket->getBasketItems() as $basketItem)
	{
		$productName = $basketItem->getField("NAME");
		if ($productName == "OrderDelivery")
			$productName = Loc::getMessage('SALE_HPS_BILL_DELIVERY');
		else if ($productName == "OrderDiscount")
			$productName = Loc::getMessage('SALE_HPS_BILL_DISCOUNT');

		if ($basketItem->isVatInPrice())
			$basketItemPrice = $basketItem->getPrice();
		else
			$basketItemPrice = $basketItem->getPrice()*(1 + $basketItem->getVatRate());

		$arCells[++$n] = array(
			1 => CSalePdf::prepareToPdf($n),
			CSalePdf::prepareToPdf($productName),
			CSalePdf::prepareToPdf(roundEx($basketItem->getQuantity(), SALE_VALUE_PRECISION)),
			CSalePdf::prepareToPdf($basketItem->getField("MEASURE_NAME") ? $basketItem->getField("MEASURE_NAME") : Loc::getMessage('SALE_HPS_BILL_BASKET_MEASURE_DEFAULT')),
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

			$arProps[$n][] = $pdf::prepareToPdf(sprintf("%s: %s", $basketPropertyItem->getField("NAME"), $basketPropertyItem->getField("VALUE")));
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

	if ($shipment !== null && $shipment->getPrice() > 0)
	{
		$sDeliveryItem = Loc::getMessage('SALE_HPS_BILL_DELIVERY');
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
			CSalePdf::prepareToPdf(SaleFormatCurrency($shipment->getPrice(), $shipment->getCurrency(), true))
		);

		for ($i = 1; $i <= 7; $i++)
			$arRowsWidth[$i] = max($arRowsWidth[$i], $pdf->GetStringWidth($arCells[$n][$i]));

		$sum += doubleval($shipment->getPrice());
	}

	$cntBasketItem = $n;

	if ($sum < $payment->getSum())
	{
		$arCells[++$n] = array(
			1 => null,
			null,
			null,
			null,
			null,
			CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_SUBTOTAL')),
			CSalePdf::prepareToPdf(SaleFormatCurrency($sum, $payment->getField('CURRENCY'), true))
		);

		$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));
	}

	/** @var \Bitrix\Sale\Tax $taxes */
	$taxes = $order->getTax();

	$taxList = $taxes->getTaxList();
	if ($taxList)
	{
		foreach ($taxes->getTaxList() as $tax)
		{
			$arCells[++$n] = array(
				1 => null,
				null,
				null,
				null,
				null,
				CSalePdf::prepareToPdf(sprintf(
						"%s%s%s:",
						($tax["IS_IN_PRICE"] == "Y") ? Loc::getMessage('SALE_HPS_BILL_INCLUDING') : "",
						$tax["TAX_NAME"],
						($vat <= 0 && $tax["IS_PERCENT"] == "Y")
								? sprintf(' (%s%%)', roundEx($tax["VALUE"], SALE_VALUE_PRECISION))
								: ""
				)),
				CSalePdf::prepareToPdf(SaleFormatCurrency($tax["VALUE_MONEY"], $payment->getField('CURRENCY'), true))
			);

			$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));
		}
	}

	if (!$taxList)
	{
		$arCells[++$n] = array(
			1 => null,
			null,
			null,
			null,
			null,
			CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_TOTAL_VAT_RATE')),
			CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_TOTAL_VAT_RATE_NO'))
		);

		$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));
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
			CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_TOTAL_PAID')),
			CSalePdf::prepareToPdf(SaleFormatCurrency($sumPaid, $payment->getField('CURRENCY'), true))
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
				CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_TOTAL_DISCOUNT')),
				CSalePdf::prepareToPdf(SaleFormatCurrency($order->getDiscountPrice(), $order->getCurrency(), true))
		);

		$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));
	}

	$arCells[++$n] = array(
			1 => null,
			null,
			null,
			null,
			null,
			CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_TOTAL_SUM')),
			CSalePdf::prepareToPdf(SaleFormatCurrency($payment->getSum(), $payment->getField('CURRENCY'), true))
	);

	$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));

	for ($i = 1; $i <= 7; $i++)
		$arRowsWidth[$i] += 10;
	if ($vat <= 0)
		$arRowsWidth[6] = 0;
	$arRowsWidth[2] = $width - (array_sum($arRowsWidth)-$arRowsWidth[2]);
}
$pdf->Ln();

$x0 = $pdf->GetX();
$y0 = $pdf->GetY();

for ($i = 1; $i <= 7; $i++)
{
	if ($vat > 0 || $i != 6)
		$pdf->Cell($arRowsWidth[$i], 20, $arColsCaption[$i], 0, 0, 'C');
	${"x$i"} = $pdf->GetX();
}

$pdf->Ln();

$y5 = $pdf->GetY();

$pdf->Line($x0, $y0, $x7, $y0);
for ($i = 0; $i <= 7; $i++)
{
	if ($vat > 0 || $i != 6)
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
		$width = ($cellWidth-5 > 0) ? $cellWidth-5 : 0;
		list($string, $text) = $pdf->splitString($text, $width);

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
			else if ($vat > 0)
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
			if ($vat > 0)
				$pdf->Cell($arRowsWidth_tmp[6], 12, '');
			$pdf->Cell($arRowsWidth_tmp[7], 12, '', 0, 1);
		}
	}

	$y5 = $pdf->GetY();

	if ($y0 > $y5)
		$y0 = $margin['top'];
	for ($i = (is_null($arCells[$n][1])) ? 6 : 0; $i <= 7; $i++)
	{
		if ($vat > 0 || $i != 5)
			$pdf->Line(${"x$i"}, $y0, ${"x$i"}, $y5);
	}

	$pdf->Line((!is_null($arCells[$n][1])) ? $x0 : $x6, $y5, $x7, $y5);
}
$pdf->Ln();


$pdf->SetFont($fontFamily, '', $fontSize);
$pdf->Write(15, CSalePdf::prepareToPdf(Loc::getMessage(
	'SALE_HPS_BILL_BASKET_TOTAL',
	array(
		'#BASKET_COUNT#' => $cntBasketItem,
		'#BASKET_PRICE#' => SaleFormatCurrency($payment->getField('SUM'), $payment->getField('CURRENCY'), false)
	)
)));
$pdf->Ln();

$pdf->SetFont($fontFamily, 'B', $fontSize);
if (in_array($payment->getField('CURRENCY'), array("RUR", "RUB")))
{
	$pdf->Write(15, CSalePdf::prepareToPdf(Number2Word_Rus($payment->getSum())));
}
else
{
	$pdf->Write(15, CSalePdf::prepareToPdf(SaleFormatCurrency(
		$payment->getSum(),
			$payment->getField("CURRENCY"),
		false
	)));
}
$pdf->Ln();
$pdf->Ln();

if ($params["BILL_COMMENT1"] || $params["BILL_COMMENT2"])
{
	$pdf->Write(15, CSalePdf::prepareToPdf(Loc::getMessage('SALE_HPS_BILL_COND_COMM')));
	$pdf->Ln();

	$pdf->SetFont($fontFamily, '', $fontSize);

	if ($params["BILL_COMMENT1"])
	{
		$pdf->Write(15, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf($params["BILL_COMMENT1"])
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}

	if ($params["BILL_COMMENT2"])
	{
		$pdf->Write(15, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf($params["BILL_COMMENT2"])
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}
}

$pdf->Ln();
$pdf->Ln();

if ($params['BILL_PATH_TO_STAMP'])
{
	$filePath = $pdf->GetImagePath($params['BILL_PATH_TO_STAMP']);

	if ($filePath != '' && !$blank && \Bitrix\Main\IO\File::isFileExists($filePath))
	{
		list($stampHeight, $stampWidth) = $pdf->GetImageSize($params['BILL_PATH_TO_STAMP']);
		if ($stampHeight && $stampWidth)
		{
			if ($stampHeight > 120 || $stampWidth > 120)
			{
				$ratio = 120 / max($stampHeight, $stampWidth);
				$stampHeight = $ratio * $stampHeight;
				$stampWidth = $ratio * $stampWidth;
			}

			if ($pdf->GetY() + $stampHeight > $pageHeight)
				$pdf->AddPage();

			$pdf->Image(
					$params['BILL_PATH_TO_STAMP'],
					$margin['left'] + 40, $pdf->GetY(),
					$stampWidth, $stampHeight
			);
		}
	}
}


$pdf->SetFont($fontFamily, 'B', $fontSize);

if ($params["SELLER_COMPANY_DIRECTOR_POSITION"])
{
	$isDirSign = false;
	if (!$blank && $params['SELLER_COMPANY_DIR_SIGN'])
	{
		list($signHeight, $signWidth) = $pdf->GetImageSize($params['SELLER_COMPANY_DIR_SIGN']);

		if ($signHeight && $signWidth)
		{
			$ratio = min(37.5/$signHeight, 150/$signWidth);
			$signHeight = $ratio * $signHeight;
			$signWidth  = $ratio * $signWidth;

			$isDirSign = true;
		}
	}

	$sellerDirPos = CSalePdf::prepareToPdf($params["SELLER_COMPANY_DIRECTOR_POSITION"]);
	if ($isDirSign && $pdf->GetStringWidth($sellerDirPos) <= 160)
		$pdf->SetY($pdf->GetY() + min($signHeight, 30) - 15);
	$pdf->MultiCell(150, 15, $sellerDirPos, 0, 'L');
	$pdf->SetXY($margin['left'] + 150, $pdf->GetY() - 15);

	if ($isDirSign)
	{
		$pdf->Image(
				$params['SELLER_COMPANY_DIR_SIGN'],
			$pdf->GetX() + 80 - $signWidth/2, $pdf->GetY() - $signHeight + 15,
			$signWidth, $signHeight
		);
	}

	$x1 = $pdf->GetX();
	$pdf->Cell(160, 15, '');
	$x2 = $pdf->GetX();

	if ($params["SELLER_COMPANY_DIRECTOR_NAME"])
		$pdf->Write(15, CSalePdf::prepareToPdf('('.$params["SELLER_COMPANY_DIRECTOR_NAME"].')'));
	$pdf->Ln();

	$y2 = $pdf->GetY();
	$pdf->Line($x1, $y2, $x2, $y2);

	$pdf->Ln();
}

if ($params["SELLER_COMPANY_ACCOUNTANT_POSITION"])
{
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

	$sellerAccPos = CSalePdf::prepareToPdf($params["SELLER_COMPANY_ACCOUNTANT_POSITION"]);
	if ($isAccSign && $pdf->GetStringWidth($sellerAccPos) <= 160)
		$pdf->SetY($pdf->GetY() + min($signHeight, 30) - 15);
	$pdf->MultiCell(150, 15, $sellerAccPos, 0, 'L');
	$pdf->SetXY($margin['left'] + 150, $pdf->GetY() - 15);

	if ($isAccSign)
	{
		$pdf->Image(
			$params['SELLER_COMPANY_ACC_SIGN'],
			$pdf->GetX() + 80 - $signWidth/2, $pdf->GetY() - $signHeight + 15,
			$signWidth, $signHeight
		);
	}

	$x1 = $pdf->GetX();
	$pdf->Cell(($params["SELLER_COMPANY_DIRECTOR_NAME"]) ? $x2-$x1 : 160, 15, '');
	$x2 = $pdf->GetX();

	if ($params["SELLER_COMPANY_ACCOUNTANT_NAME"])
		$pdf->Write(15, CSalePdf::prepareToPdf('('.$params["SELLER_COMPANY_ACCOUNTANT_NAME"].')'));
	$pdf->Ln();

	$y2 = $pdf->GetY();
	$pdf->Line($x1, $y2, $x2, $y2);
}


$dest = 'I';
if ($_REQUEST['GET_CONTENT'] == 'Y')
	$dest = 'S';
else if ($_REQUEST['DOWNLOAD'] == 'Y')
	$dest = 'D';

return $pdf->Output(
	sprintf(
		'Schet No %s ot %s.pdf',
		$params["ACCOUNT_NUMBER"],
		ConvertDateTime($params['PAYMENT_DATE_INSERT'], 'YYYY-MM-DD')
	), $dest
);
?>