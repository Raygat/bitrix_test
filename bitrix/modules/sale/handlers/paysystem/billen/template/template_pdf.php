<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

if (!CSalePdf::isPdfAvailable())
	die();

if ($_REQUEST['BLANK'] == 'Y')
	$blank = true;

$pdf = new CSalePdf('P', 'pt', 'A4');

if ($params['BILLEN_BACKGROUND'])
{
	$pdf->SetBackground(
		$params['BILLEN_BACKGROUND'],
		$params['BILLEN_BACKGROUND_STYLE']
	);
}

$pageWidth  = $pdf->GetPageWidth();
$pageHeight = $pdf->GetPageHeight();

$pdf->AddFont('Font', '', 'pt_sans-regular.ttf', true);
$pdf->AddFont('Font', 'B', 'pt_sans-bold.ttf', true);

$fontFamily = 'Font';
$fontSize   = 10.5;

$margin = array(
	'top' => intval($params['BILLEN_MARGIN_TOP'] ?: 15) * 72/25.4,
	'right' => intval($params['BILLEN_MARGIN_RIGHT'] ?: 15) * 72/25.4,
	'bottom' => intval($params['BILLEN_MARGIN_BOTTOM'] ?: 15) * 72/25.4,
	'left' => intval($params['BILLEN_MARGIN_LEFT'] ?: 20) * 72/25.4
);

$width = $pageWidth - $margin['left'] - $margin['right'];

$pdf->SetDisplayMode(100, 'continuous');
$pdf->SetMargins($margin['left'], $margin['top'], $margin['right']);
$pdf->SetAutoPageBreak(true, $margin['bottom']);

$pdf->AddPage();


$y0 = $pdf->GetY();
$logoHeight = 0;
$logoWidth = 0;

if ($params['BILLEN_PATH_TO_LOGO'])
{
	list($imageHeight, $imageWidth) = $pdf->GetImageSize($params['BILLEN_PATH_TO_LOGO']);

	$imgDpi = intval($params['BILLEN_LOGO_DPI']) ?: 96;
	$imgZoom = 96 / $imgDpi;

	$logoHeight = $imageHeight * $imgZoom + 5;
	$logoWidth  = $imageWidth * $imgZoom + 5;

	$pdf->Image($params['BILLEN_PATH_TO_LOGO'], $pdf->GetX(), $pdf->GetY(), -$imgDpi, -$imgDpi);
}

$pdf->SetFont($fontFamily, 'B', $fontSize);

$pdf->SetX($pdf->GetX() + $logoWidth);
$pdf->Write(15, CSalePdf::prepareToPdf($params["SELLER_COMPANY_NAME"]));
$pdf->Ln();

if ($params["SELLER_COMPANY_ADDRESS"])
{
	$sellerAddress = $params["SELLER_COMPANY_ADDRESS"];
	if (is_string($sellerAddress))
	{
		$sellerAddress = explode("\n", str_replace(array("\r\n", "\n", "\r"), "\n", $sellerAddress));
		if (count($sellerAddress) === 1)
			$sellerAddress = $sellerAddress[0];
	}
	if (is_array($sellerAddress))
	{
		if (!empty($sellerAddress))
		{
			$i = 0;
			foreach ($sellerAddress as $item)
			{
				if ($i++ > 0)
					$pdf->Ln();
				$item = $pdf->prepareToPdf($item);
				$pdf->SetX($pdf->GetX() + $logoWidth);
				$pdf->Cell($width, 15, $item, 0, 0, 'L');
			}
			unset($item);
		}
	}
	else
	{
		$sellerAddress = $pdf->prepareToPdf($sellerAddress);
		$pdf->SetX($pdf->GetX() + $logoWidth);
		$pdf->Cell($width, 15, $sellerAddress, 0, 0, 'L');
	}
}

if ($params["SELLER_COMPANY_PHONE"])
{
	$pdf->Ln();
	$pdf->SetX($pdf->GetX() + $logoWidth);
	$pdf->Write(15, CSalePdf::prepareToPdf(sprintf("Tel.: %s", $params["SELLER_COMPANY_PHONE"])));
	$pdf->Ln();
}

$pdf->Ln();
$pdf->SetY(max($y0 + $logoHeight, $pdf->GetY()));
$pdf->Ln();


$pdf->SetFont($fontFamily, 'B', $fontSize*2);
$pdf->Cell(0, 15, CSalePdf::prepareToPdf('Invoice'), 0, 0, 'C');

$pdf->Ln();
$pdf->Ln();
$pdf->Ln();

$pdf->SetFont($fontFamily, 'B', $fontSize);

if ($params["BUYER_PERSON_COMPANY_NAME"])
{
	$pdf->Write(15, CSalePdf::prepareToPdf('To'));
}

$pdf->SetFont($fontFamily, '', $fontSize);

$invoiceNo = CSalePdf::prepareToPdf($params["ACCOUNT_NUMBER"]);
$invoiceNoWidth = $pdf->GetStringWidth($invoiceNo);

$invoiceDate = CSalePdf::prepareToPdf($params["DATE_INSERT"]);
$invoiceDateWidth = $pdf->GetStringWidth($invoiceDate);

$invoiceDueDate = CSalePdf::prepareToPdf(
	ConvertDateTime($params["DATE_PAY_BEFORE"], FORMAT_DATE)
		?: $params["DATE_PAY_BEFORE"]
);
$invoiceDueDateWidth = $pdf->GetStringWidth($invoiceDueDate);

$invoiceInfoWidth = max($invoiceNoWidth, $invoiceDateWidth, $invoiceDueDateWidth);

$pdf->Cell(0, 15, $invoiceNo, 0, 0, 'R');

$pdf->SetFont($fontFamily, 'B', $fontSize);

$title = CSalePdf::prepareToPdf('Invoice # ');
$titleWidth = $pdf->GetStringWidth($title);
$pdf->SetX($pdf->GetX() - $invoiceInfoWidth - $titleWidth - 6);
$pdf->Write(15, $title, 0, 0, 'R');
$pdf->Ln();

$pdf->SetFont($fontFamily, '', $fontSize);

if ($params["BUYER_PERSON_COMPANY_NAME"])
{
	$pdf->Write(15, CSalePdf::prepareToPdf($params["BUYER_PERSON_COMPANY_NAME"]));
}

$pdf->Cell(0, 15, $invoiceDate, 0, 0, 'R');

$pdf->SetFont($fontFamily, 'B', $fontSize);

$title = CSalePdf::prepareToPdf('Issue Date: ');
$titleWidth = $pdf->GetStringWidth($title);
$pdf->SetX($pdf->GetX() - $invoiceInfoWidth - $titleWidth - 6);
$pdf->Write(15, $title, 0, 0, 'R');
$pdf->Ln();

$pdf->SetFont($fontFamily, '', $fontSize);

if ($params["BUYER_PERSON_COMPANY_NAME"])
{
	if ($params["BUYER_PERSON_COMPANY_ADDRESS"])
	{
		$buyerAddress = $params["BUYER_PERSON_COMPANY_ADDRESS"];
		if($buyerAddress)
		{
			if (is_string($buyerAddress))
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
}

if ($params["DATE_PAY_BEFORE"])
{
	$pdf->Cell(0, 15, $invoiceDueDate, 0, 0, 'R');

	$pdf->SetFont($fontFamily, 'B', $fontSize);

	$title = CSalePdf::prepareToPdf('Due Date: ');
	$titleWidth = $pdf->GetStringWidth($title);
	$pdf->SetX($pdf->GetX() - $invoiceInfoWidth - $titleWidth - 6);
	$pdf->Write(15, $title, 0, 0, 'R');
}

$pdf->Ln();
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
		1 => CSalePdf::prepareToPdf('#'),
		CSalePdf::prepareToPdf('Item / Description'),
		CSalePdf::prepareToPdf('Qty'),
		CSalePdf::prepareToPdf('Units'),
		CSalePdf::prepareToPdf('Unit Price'),
		CSalePdf::prepareToPdf('Tax Rate'),
		CSalePdf::prepareToPdf('Total')
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

		$productName = $basketItem->getField("NAME");
		if ($productName == "OrderDelivery")
			$productName = "Shipping";
		else if ($productName == "OrderDiscount")
			$productName = "Discount";

		$arCells[++$n] = array(
			1 => CSalePdf::prepareToPdf($n),
			CSalePdf::prepareToPdf($productName),
			CSalePdf::prepareToPdf(roundEx($basketItem->getQuantity(), SALE_VALUE_PRECISION)),
			CSalePdf::prepareToPdf($basketItem->getField("MEASURE_NAME") ? $basketItem->getField("MEASURE_NAME") : 'pcs'),
			CSalePdf::prepareToPdf(SaleFormatCurrency($vatLessPrice, $basketItem->getCurrency(), false)),
			CSalePdf::prepareToPdf(roundEx($basketItem->getVatRate()*100, SALE_VALUE_PRECISION)."%"),
			CSalePdf::prepareToPdf(SaleFormatCurrency(
				$vatLessPrice * $basketItem->getQuantity(),
				$basketItem->getCurrency(),
				false
			))
		);

		$arProps[$n] = array();

		/** @var \Bitrix\Sale\BasketPropertyItem $basketItemProperty */
		foreach ($basketItem->getPropertyCollection() as $basketItemProperty)
		{
			if ($basketItemProperty->getField('CODE') == 'CATALOG.XML_ID' || $basketItemProperty->getField('CODE') == 'PRODUCT.XML_ID')
				continue;

			$arProps[$n][] = CSalePdf::prepareToPdf(sprintf("%s: %s", $basketItemProperty->getField("NAME"), $basketItemProperty->getField("VALUE")));
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
		$sDeliveryItem = "Shipping";
		if ($shipment->getDeliveryName())
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
			CSalePdf::prepareToPdf("Subtotal:"),
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
					"Tax (%s%%):",
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
								($tax["IS_IN_PRICE"] == "Y") ? "Included " : "",
								$tax["TAX_NAME"],
								sprintf(' (%s%%)', roundEx($tax["VALUE"], SALE_VALUE_PRECISION))
						)),
						CSalePdf::prepareToPdf(SaleFormatCurrency(
								$tax["VALUE_MONEY"],
								$order->getCurrency(),
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
			CSalePdf::prepareToPdf("Discount:"),
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
		CSalePdf::prepareToPdf("Total:"),
		CSalePdf::prepareToPdf(SaleFormatCurrency(
			$payment->getSum(),
			$payment->getField('CURRENCY'),
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
$pdf->Ln();


$pdf->SetFont($fontFamily, 'B', $fontSize);

if ($params["BILLEN_COMMENT1"] || $params["BILLEN_COMMENT2"])
{
	$pdf->Write(15, CSalePdf::prepareToPdf('Terms & Conditions'));
	$pdf->Ln();

	$pdf->SetFont($fontFamily, '', $fontSize);

	if ($params["BILLEN_COMMENT1"])
	{
		$pdf->Write(15, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf($params["BILLEN_COMMENT1"])
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}

	if ($params["BILLEN_COMMENT2"])
	{
		$pdf->Write(15, HTMLToTxt(preg_replace(
			array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
			CSalePdf::prepareToPdf($params["BILLEN_COMMENT2"])
		), '', array(), 0));
		$pdf->Ln();
		$pdf->Ln();
	}
}

$pdf->Ln();
$pdf->Ln();
$pdf->Ln();

if ($params['BILLEN_PATH_TO_STAMP'])
{
	$filePath = $pdf->GetImagePath($params['BILLEN_PATH_TO_STAMP']);
	if ($filePath != '' && !$blank && \Bitrix\Main\IO\File::isFileExists($filePath))
	{
		list($stampHeight, $stampWidth) = $pdf->GetImageSize($params['BILLEN_PATH_TO_STAMP']);

		if ($stampHeight && $stampWidth)
		{
			if ($stampHeight > 120 || $stampWidth > 120)
			{
				$ratio = 120 / max($stampHeight, $stampWidth);
				$stampHeight = $ratio * $stampHeight;
				$stampWidth  = $ratio * $stampWidth;
			}

			$pdf->Image(
				$params['BILLEN_PATH_TO_STAMP'],
				$margin['left']+$width/2+45, $pdf->GetY(),
				$stampWidth, $stampHeight
			);
		}
	}
}

$y0 = $pdf->GetY();

$bankAccNo = $params["SELLER_COMPANY_BANK_ACCOUNT"];
$bankRouteNo = $params["SELLER_COMPANY_BANK_ACCOUNT_CORR"];
$bankSwift = $params["SELLER_COMPANY_BANK_SWIFT"];

if ($bankAccNo && $bankRouteNo && $bankSwift)
{
	$pdf->SetFont($fontFamily, 'B', $fontSize);

	$pdf->Write(15, CSalePdf::prepareToPdf("Bank Details"));
	$pdf->Ln();

	$pdf->SetFont($fontFamily, '', $fontSize);

	$bankDetails = '';

	if ($params["SELLER_COMPANY_NAME"])
	{
		$bankDetails .= CSalePdf::prepareToPdf(sprintf(
			"Account Name: %s\n",
			$params["SELLER_COMPANY_NAME"]
		));
	}

	$bankDetails .= CSalePdf::prepareToPdf(sprintf("Account #: %s\n", $bankAccNo));

	$bank = $params["SELLER_COMPANY_BANK_NAME"];
	$bankAddr = $params["SELLER_COMPANY_BANK_ADDR"];
	$bankPhone = $params["SELLER_COMPANY_BANK_PHONE"];

	if ($bank || $bankAddr || $bankPhone)
	{
		$bankDetails .= CSalePdf::prepareToPdf("Bank Name and Address: ");
		if ($bank)
			$bankDetails .= CSalePdf::prepareToPdf($bank);
		$bankDetails .= CSalePdf::prepareToPdf("\n");

		if ($bankAddr)
			$bankDetails .= CSalePdf::prepareToPdf(sprintf("%s\n", $bankAddr));

		if ($bankPhone)
		{
			$bankDetails .= CSalePdf::prepareToPdf(sprintf("%s\n", $bankPhone));
		}
	}

	$bankDetails .= CSalePdf::prepareToPdf(sprintf("Bank's routing number: %s\n", $bankRouteNo));
	$bankDetails .= CSalePdf::prepareToPdf(sprintf("Bank SWIFT: %s\n", $bankSwift));

	$pdf->MultiCell($width/2, 15, $bankDetails, 0, 'L');
}

$pdf->SetY($y0 + 15);
if ($params["SELLER_COMPANY_DIRECTOR_POSITION"])
{
	if ($params["SELLER_COMPANY_DIRECTOR_NAME"] || $params["SELLER_COMPANY_DIR_SIGN"])
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

		if ($params["SELLER_COMPANY_DIRECTOR_NAME"])
		{
			$pdf->SetX($pdf->GetX() + $width/2 + 15);
			$pdf->Write(15, CSalePdf::prepareToPdf($params["SELLER_COMPANY_DIRECTOR_NAME"]));
			$pdf->Ln();
			$pdf->Ln();
		}

		$pdf->SetX($pdf->GetX() + $width/2 + 15);
		$pdf->Write(15, CSalePdf::prepareToPdf($params["SELLER_COMPANY_DIRECTOR_POSITION"]));

		$pdf->Cell(0, 15, '', 'B');

		if ($isDirSign)
		{
			$pdf->Image(
				$params['SELLER_COMPANY_DIR_SIGN'],
				$pdf->GetX() - 150, $pdf->GetY() - $signHeight + 15,
				$signWidth, $signHeight
			);
		}

		$pdf->Ln();
		$pdf->Ln();
	}
}

if ($params["SELLER_COMPANY_ACCOUNTANT_POSITION"])
{
	if ($params["SELLER_COMPANY_ACCOUNTANT_NAME"] || $params["SELLER_COMPANY_ACC_SIGN"])
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

		if ($params["SELLER_COMPANY_ACCOUNTANT_NAME"])
		{
			$pdf->SetX($pdf->GetX() + $width/2 + 15);
			$pdf->Write(15, CSalePdf::prepareToPdf($params["SELLER_COMPANY_ACCOUNTANT_NAME"]));
			$pdf->Ln();
			$pdf->Ln();
		}

		$pdf->SetX($pdf->GetX() + $width/2 + 15);
		$pdf->Write(15, CSalePdf::prepareToPdf($params["SELLER_COMPANY_ACCOUNTANT_POSITION"]));

		$pdf->Cell(0, 15, '', 'B');

		if ($isAccSign)
		{
			$pdf->Image(
				$params['SELLER_COMPANY_ACC_SIGN'],
				$pdf->GetX() - 150, $pdf->GetY() - $signHeight + 15,
				$signWidth, $signHeight
			);
		}

		$pdf->Ln();
	}
}

$dest = 'I';
if ($_REQUEST['GET_CONTENT'] == 'Y')
	$dest = 'S';
else if ($_REQUEST['DOWNLOAD'] == 'Y')
	$dest = 'D';

return $pdf->Output(
	sprintf(
		'Invoice # %s (Issue Date %s).pdf',
		$params["ACCOUNT_NUMBER"],
		ConvertDateTime($payment->getField("DATE_BILL"), 'YYYY-MM-DD')
	), $dest
);
?>