<div class="sale-personal-account-wallet-container">
<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="sale-personal-account-wallet-title">
	<?=Bitrix\Main\Localization\Loc::getMessage('SPA_BILL_AT')?>
	<?echo $arResult["DATE"];?>
</div>
<div class="sale-personal-account-wallet-list-container">
	<div class="sale-personal-account-wallet-list">
		<?
		if (empty($arResult["ACCOUNT_LIST"]))
		{
			$arResult["ACCOUNT_LIST"][]['NEW_INFO'] = array(
				'SUM' => SaleFormatCurrency(0, $arResult['BASE_CURRENCY']['CODE']),
				'CURRENCY' => $arResult['BASE_CURRENCY']['TEXT']
			);
		}
		foreach($arResult["ACCOUNT_LIST"] as $val)
		{
			?>
			<div class="sale-personal-account-wallet-list-item">
				<span class="sale-personal-account-wallet-sum"><?=$val["NEW_INFO"]['SUM']?></span>
				<span class="sale-personal-account-wallet-currency"><?=$val["NEW_INFO"]['CURRENCY']?></span>
			</div>
			<?
		}
		?>
	</div>
</div>
</div>