<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

if (!CSalePdf::isPdfAvailable())
	die();

if ($_REQUEST['BLANK'] == 'Y')
	$blank = true;

$pdf = new CSalePdf('P', 'pt', 'A4');

if ($params['BILLDE_BACKGROUND'])
{
	$pdf->SetBackground(
		$params['BILLDE_BACKGROUND'],
		$params['BILLDE_BACKGROUND_STYLE']
	);
}

$pageWidth  = $pdf->GetPageWidth();
$pageHeight = $pdf->GetPageHeight();

$pdf->AddFont('Font', '', 'pt_sans-regular.ttf', true);
$pdf->AddFont('Font', 'B', 'pt_sans-bold.ttf', true);

$fontFamily = 'Font';
$fontSize   = 10.5;

$margin = array(
	'top' => intval($params['BILLDE_MARGIN_TOP'] ?: 15) * 72/25.4,
	'right' => intval($params['BILLDE_MARGIN_RIGHT'] ?: 15) * 72/25.4,
	'bottom' => intval($params['MBILLDE_ARGIN_BOTTOM'] ?: 15) * 72/25.4,
	'left' => intval($params['BILLDE_MARGIN_LEFT'] ?: 20) * 72/25.4
);

$width = $pageWidth - $margin['left'] - $margin['right'];

$pdf->SetDisplayMode(100, 'continuous');
$pdf->SetMargins($margin['left'], $margin['top'], $margin['right']);
$pdf->SetAutoPageBreak(true, $margin['bottom']);

$pdf->AddPage();

$y0 = $pdf->GetY();
$logoHeight = 0;
$logoWidth = 0;

if ($params['BILLDE_PATH_TO_LOGO'])
{
	list($imageHeight, $imageWidth) = $pdf->GetImageSize($params['BILLDE_PATH_TO_LOGO']);

	$imgDpi = intval($params['BILLDE_LOGO_DPI']) ?: 96;
	$imgZoom = 96 / $imgDpi;

	$logoHeight = $imageHeight * $imgZoom + 5;
	$logoWidth  = $imageWidth * $imgZoom + 5;

	$pdf->Image($params['BILLDE_PATH_TO_LOGO'], $pdf->GetX(), $pdf->GetY(), -$imgDpi, -$imgDpi);
}

$pdf->Ln(10);

$pdf->SetFont($fontFamily, 'B', $fontSize*3);

$pdf->SetX($pdf->GetX() + $logoWidth);
$pdf->MultiCell(0, 30, CSalePdf::prepareToPdf($params["SELLER_COMPANY_NAME"]), 0, 'L');
$pdf->Ln();
$pdf->SetY(max($y0 + $logoHeight, $pdf->GetY()));

$pdf->Ln(10);


$pdf->SetFont($fontFamily, 'B', $fontSize-2);

$seller = $params["SELLER_COMPANY_NAME"];
if ($params["SELLER_COMPANY_ADDRESS"])
{
	$sellerAddr = $params["SELLER_COMPANY_ADDRESS"];
	if (is_array($sellerAddr))
		$sellerAddr = implode(', ', $sellerAddr);
	else
		$sellerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($sellerAddr));
	$seller .= ' - ';
	$seller .= $sellerAddr;
	$seller .= '  ';
}

$seller = CSalePdf::prepareToPdf($seller);
$pdf->Cell($pdf->GetStringWidth($seller), 10, $seller, 'B');

$pdf->Ln();
$pdf->Ln();
$pdf->Ln();

if ($params["BUYER_PERSON_COMPANY_NAME"])
{
	$pdf->SetFont($fontFamily, 'B', $fontSize);
	$pdf->Write(15, CSalePdf::prepareToPdf($params["BUYER_PERSON_COMPANY_NAME"]));
	$pdf->Ln();
	$pdf->SetFont($fontFamily, '', $fontSize);
	if ($params["BUYER_PERSON_COMPANY_PAYER_NAME"])
	{
		$pdf->Write(15, CSalePdf::prepareToPdf($params["BUYER_PERSON_COMPANY_PAYER_NAME"]));
		$pdf->Ln();
	}
	$buyerAddress = $params["BUYER_PERSON_COMPANY_ADDRESS"];
	if($buyerAddress)
	{
		if(is_string($buyerAddress))
		{
			$buyerAddress = explode("\n", str_replace(array("\r\n", "\n", "\r"), "\n", $buyerAddress));
			if (count($buyerAddress) === 1)
				$buyerAddress = $buyerAddress[0];
		}
		if (is_array($buyerAddress))
		{
			if (!empty($buyerAddress))
			{
				foreach ($buyerAddress as $item)
				{
					$pdf->Write(15, CSalePdf::prepareToPdf($item));
					$pdf->Ln();
				}
				unset($item);
			}
		}
		else
		{
			$pdf->Write(15, CSalePdf::prepareToPdf($buyerAddress));
			$pdf->Ln();
		}
	}
}

$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();
$pdf->Ln();

$pdf->SetFont($fontFamily, 'B', $fontSize*2);
$pdf->Write(15, CSalePdf::prepareToPdf('Rechnung'));

$pdf->Ln();
$pdf->Ln();
$pdf->Ln();



$pdf->SetFont($fontFamily, 'B', $fontSize);

$pdf->Cell(0.35*$width, 15, CSalePdf::prepareToPdf(sprintf(
	'Rechnung Nr. %s',
	$params["ACCOUNT_NUMBER"]
)));

if ($params["BUYER_PERSON_COMPANY_ID"])
{
	$pdf->Cell(0.35*$width, 15, CSalePdf::prepareToPdf(sprintf(
		'Kunden-Nr.: %s',
			$params["BUYER_PERSON_COMPANY_ID"]
	)));
}

$pdf->Cell(0, 15, CSalePdf::prepareToPdf(sprintf(
	'Datum: %s',
		$params["DATE_INSERT"]
)), 0, 0, 'R');
$pdf->Ln();

if ($params["DATE_PAY_BEFORE"])
{
	$pdf->Cell(0, 15, CSalePdf::prepareToPdf(sprintf(
		'Bezahlen bis: %s',
		ConvertDateTime($params["DATE_PAY_BEFORE"], FORMAT_DATE)
			?: $params["DATE_PAY_BEFORE"]
	)), 0, 0, 'R');
	$pdf->Ln();
}

$pdf->SetFont($fontFamily, 'B', $fontSize-2);
$pdf->Write(15, CSalePdf::prepareToPdf('Bitte bei Zahlungen und Schriftverkehr angeben!'));

$pdf->Ln();
$pdf->Ln();


$pdf->SetFont($fontFamily, '', $fontSize);


$basketItems = array();

/** @var \Bitrix\Sale\PaymentCollection $paymentCollection */
$paymentCollection = $payment->getCollection();

/** @var \Bitrix\Sale\Order $order */
$order = $paymentCollection->getOrder();

/** @var \Bitrix\Sale\Basket $basket */
$basket = $order->getBasket();

if (count($basket->getBasketItems()) > 0)
{
	$arColsCaption = array(
		1 => CSalePdf::prepareToPdf('Pos.'),
		CSalePdf::prepareToPdf('Leistung'),
		CSalePdf::prepareToPdf('Anzahl'),
		CSalePdf::prepareToPdf('Einheit'),
		CSalePdf::prepareToPdf('Einzelpreis'),
		CSalePdf::prepareToPdf('MwSt.'),
		CSalePdf::prepareToPdf('Gesamtpreis')
	);
	$arCells = array();
	$arProps = array();
	$arRowsWidth = array(1 => 0, 0, 0, 0, 0, 0, 0);

	for ($i = 1; $i <= 7; $i++)
		$arRowsWidth[$i] = max($arRowsWidth[$i], $pdf->GetStringWidth($arColsCaption[$i]));

	$n = 0;
	$sum = 0.00;
	$vat = 0;
	$vats = array();

	/** @var \Bitrix\Sale\BasketItem $basketItem */
	foreach ($basket->getBasketItems() as $basketItem)
	{
		// @TODO: replace with real vatless price
		if ($basketItem->isVatInPrice())
			$vatLessPrice = roundEx($basketItem->getPrice() / (1 + $basketItem->getVatRate()), SALE_VALUE_PRECISION);
		else
			$vatLessPrice = $basketItem->getPrice();

		$productName = $basketItem->getField('NAME');
		if ($productName == "OrderDelivery")
			$productName = "Schifffahrt";
		else if ($productName == "OrderDiscount")
			$productName = "Rabatt";

		$arCells[++$n] = array(
			1 => CSalePdf::prepareToPdf($n),
			CSalePdf::prepareToPdf($productName),
			CSalePdf::prepareToPdf(roundEx($basketItem->getQuantity(), SALE_VALUE_PRECISION)),
			CSalePdf::prepareToPdf($basketItem->getField("MEASURE_NAME") ? $basketItem->getField("MEASURE_NAME") : 'St.'),
			CSalePdf::prepareToPdf(SaleFormatCurrency($vatLessPrice, $basketItem->getCurrency(), false)),
			CSalePdf::prepareToPdf(roundEx($basketItem->getVatRate()*100, SALE_VALUE_PRECISION)."%"),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$vatLessPrice * $basketItem->getQuantity(),
				$basketItem->getCurrency(),
				false
			))
		);

		$arProps[$n] = array();

		/** @var \Bitrix\Sale\BasketPropertyItem $basketPropertyItem */
		foreach ($basketItem->getPropertyCollection() as $basketPropertyItem)
		{
			if ($basketPropertyItem->getField('CODE') == 'CATALOG.XML_ID' || $basketPropertyItem->getField('CODE') == 'PRODUCT.XML_ID')
				continue;
			$arProps[$n][] = htmlspecialcharsbx(sprintf("%s: %s", $basketPropertyItem->getField("NAME"), $basketPropertyItem->getField("VALUE")));
		}

		for ($i = 1; $i <= 7; $i++)
			$arRowsWidth[$i] = max($arRowsWidth[$i], $pdf->GetStringWidth($arCells[$n][$i]));

		$sum += doubleval($vatLessPrice * $basketItem->getQuantity());
		$vat = max($vat, $basketItem->getVatRate());
		if ($basketItem->getVatRate() > 0)
		{
			if (!isset($vats[$basketItem->getVatRate()]))
				$vats[$basketItem->getVatRate()] = 0;

			if ($basketItem->isVatInPrice())
				$vats[$basketItem->getVatRate()] += ($basketItem->getPrice() - $vatLessPrice) * $basketItem->getQuantity();
			else
				$vats[$basketItem->getVatRate()] += ($basketItem->getPrice()*(1 + $basketItem->getVatRate()) - $vatLessPrice) * $basketItem->getQuantity();
		}
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
		$sDeliveryItem = "Schifffahrt";
		if (strlen($shipment->getDeliveryName()) > 0)
			$sDeliveryItem .= sprintf(" (%s)", $shipment->getDeliveryName());
		$arCells[++$n] = array(
			1 => CSalePdf::prepareToPdf($n),
			CSalePdf::prepareToPdf($sDeliveryItem),
			CSalePdf::prepareToPdf(1),
			CSalePdf::prepareToPdf(''),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$shipment->getPrice() / (1 + $vat),
				$shipment->getCurrency(),
				false
			)),
			CSalePdf::prepareToPdf(roundEx($vat*100, SALE_VALUE_PRECISION)."%"),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$shipment->getPrice() / (1 + $vat),
				$shipment->getCurrency(),
				false
			))
		);

		for ($i = 1; $i <= 7; $i++)
			$arRowsWidth[$i] = max($arRowsWidth[$i], $pdf->GetStringWidth($arCells[$n][$i]));

		$sum += roundEx(
			doubleval($shipment->getPrice() / (1 + $vat)),
			SALE_VALUE_PRECISION
		);

		if ($vat > 0)
			$vats[$vat] += roundEx(
				$shipment->getPrice() * $vat / (1 + $vat),
				SALE_VALUE_PRECISION
			);
	}

	$items = $n;

	if ($sum < $payment->getSum())
	{
		$arCells[++$n] = array(
			1 => null,
			null,
			null,
			null,
			null,
			CSalePdf::prepareToPdf("Nettobetrag:"),
			CSalePdf::prepareToPdf(SaleFormatCurrency($sum, $order->getCurrency(), false))
		);

		$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));
	}

	if (!empty($vats))
	{
		// @TODO: remove on real vatless price implemented
		$delta = intval(roundEx(
			$payment->getSum() - $sum - array_sum($vats),
			SALE_VALUE_PRECISION
		) * pow(10, SALE_VALUE_PRECISION));

		if ($delta)
		{
			$vatRates = array_keys($vats);
			rsort($vatRates);

			$ful = intval($delta / count($vatRates));
			$ost = $delta % count($vatRates);

			foreach ($vatRates as $vatRate)
			{
				$vats[$vatRate] += ($ful + $ost) / pow(10, SALE_VALUE_PRECISION);

				if ($ost > 0)
					$ost--;
			}
		}

		foreach ($vats as $vatRate => $vatSum)
		{
			$arCells[++$n] = array(
				1 => null,
				null,
				null,
				null,
				null,
				CSalePdf::prepareToPdf(sprintf(
					"zzgl. %s%% MwSt:",
					roundEx($vatRate * 100, SALE_VALUE_PRECISION)
				)),
				CSalePdf::prepareToPdf(SaleFormatCurrency(
					$vatSum,
					$order->getCurrency(),
					false
				))
			);

			$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));
		}
	}
	else
	{
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
								($tax["IS_IN_PRICE"] == "Y") ? "inkl." : "zzgl.",
								sprintf(' %s%% ', roundEx($tax["VALUE"], SALE_VALUE_PRECISION)),
								$tax["TAX_NAME"]
						)),
						CSalePdf::prepareToPdf(SaleFormatCurrency(
								$tax["VALUE_MONEY"],
								$payment->getField('CURRENCY'),
								false
						))
				);

				$arRowsWidth[7] = max($arRowsWidth[7], $pdf->GetStringWidth($arCells[$n][7]));
			}
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
			CSalePdf::prepareToPdf("Payment made:"),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$sumPaid,
				$order->getCurrency(),
				false
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
			CSalePdf::prepareToPdf("Rabatt:"),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$order->getDiscountPrice(),
				$order->getCurrency(),
				false
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
		CSalePdf::prepareToPdf("Gesamtbetrag:"),
		CSalePdf::prepareToPdf(SaleFormatCurrency(
			$payment->getSum(),
			$order->getCurrency(),
			false
		))
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

		if (!is_null($arCells[$n][6])) {
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
$pdf->Ln();
$pdf->Ln();


$pdf->SetFont($fontFamily, 'B', $fontSize);

if ($params["BILLDE_COMMENT1"] || $params["BILLDE_COMMENT2"])
{
	$pdf->SetFont($fontFamily, '', $fontSize);

	if ($params["BILLDE_COMMENT1"])
	{
		$pdf->Write(15, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf($params["BILLDE_COMMENT1"])
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}

	if ($params["BILLDE_COMMENT2"])
	{
		$pdf->Write(15, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf($params["BILLDE_COMMENT2"])
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}
}

$pdf->Ln();

if (!$blank && $params['BILLDE_PATH_TO_STAMP'])
{
	list($stampHeight, $stampWidth) = $pdf->GetImageSize($params['BILLDE_PATH_TO_STAMP']);

	if ($stampHeight && $stampWidth)
	{
		if ($stampHeight > 120 || $stampWidth > 120)
		{
			$ratio = 120 / max($stampHeight, $stampWidth);
			$stampHeight = $ratio * $stampHeight;
			$stampWidth  = $ratio * $stampWidth;
		}

		$pdf->Image(
			$params['BILLDE_PATH_TO_STAMP'],
			$margin['left']+40, $pdf->GetY(),
			$stampWidth, $stampHeight
		);
	}
}

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

$pdf->Ln();
$pdf->Ln();
$pdf->Ln();


$pdf->SetFont($fontFamily, '', $fontSize-2);

$sellerName = $params["SELLER_COMPANY_NAME"];
$sellerAddr = $params["SELLER_COMPANY_ADDRESS"];
if (is_array($sellerAddr))
	$sellerAddr = implode(', ', $sellerAddr);
else
	$sellerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($sellerAddr));

$sellerData = array();

if ($sellerName)
	$sellerData[] = $sellerName;
if ($sellerAddr)
	$sellerData[] = $sellerAddr;

if (!empty($sellerData))
{
	$pdf->Cell(0, 15, CSalePdf::prepareToPdf(join(' - ', $sellerData)), 0, 0, 'C');
	$pdf->Ln();
}


$sellerPhone = $params["SELLER_COMPANY_PHONE"];
$sellerEmail = $params["SELLER_COMPANY_EMAIL"];

$sellerData = array();

if ($sellerPhone)
	$sellerData[] = sprintf('Telefon: %s', $sellerPhone);
if ($sellerEmail)
	$sellerData[] = sprintf('Mail: %s', $sellerEmail);

if (!empty($sellerData))
{
	$pdf->Cell(0, 15, CSalePdf::prepareToPdf(join(' - ', $sellerData)), 0, 0, 'C');
	$pdf->Ln();
}


$bankAccNo = $params["SELLER_COMPANY_BANK_ACCOUNT"];
$bankBlz   = $params["SELLER_COMPANY_BANK_BIC"];
$bankIban  = $params["SELLER_COMPANY_BANK_IBAN"];
$bankSwift = $params["SELLER_COMPANY_BANK_SWIFT"];
$bank      = $params["SELLER_COMPANY_BANK_NAME"];

$bankData = array();

if ($bankAccNo)
	$bankData[] = sprintf('Konto Nr.: %s', $bankAccNo);
if ($bankBlz)
	$bankData[] = sprintf('BLZ: %s', $bankBlz);
if ($bankIban)
	$bankData[] = sprintf('IBAN: %s', $bankIban);
if ($bankSwift)
	$bankData[] = sprintf('BIC/SWIFT: %s', $bankSwift);
if ($bank)
	$bankData[] = $bank;

if (!empty($bankData))
{
	$pdf->Cell(0, 15, CSalePdf::prepareToPdf(join(' - ', $bankData)), 0, 0, 'C');
	$pdf->Ln();
}


$sellerEuInn = $params["SELLER_COMPANY_EU_INN"];
$sellerInn   = $params["SELLER_COMPANY_INN"];
$sellerReg   = $params["SELLER_COMPANY_REG"];
$sellerDir   = $params["SELLER_COMPANY_DIRECTOR_NAME"];

$sellerData = array();

if ($sellerEuInn)
	$sellerData[] = sprintf('USt-IdNr.: %s', $sellerEuInn);
if ($sellerInn)
	$sellerData[] = sprintf('Steuernummer: %s', $sellerInn);
if ($sellerReg)
	$sellerData[] = $sellerReg;
if ($sellerDir)
	$sellerData[] = $sellerDir;

if (!empty($sellerData))
{
	$pdf->Cell(0, 15, CSalePdf::prepareToPdf(join(' - ', $sellerData)), 0, 0, 'C');
	$pdf->Ln();
}


$dest = 'I';
if ($_REQUEST['GET_CONTENT'] == 'Y')
	$dest = 'S';
else if ($_REQUEST['DOWNLOAD'] == 'Y')
	$dest = 'D';

return $pdf->Output(
	sprintf(
		'Rechnung Nr. %s (Datum %s).pdf',
		$params["ACCOUNT_NUMBER"],
		ConvertDateTime($payment->getField("DATE_BILL"), 'YYYY-MM-DD')
	), $dest
);
?>