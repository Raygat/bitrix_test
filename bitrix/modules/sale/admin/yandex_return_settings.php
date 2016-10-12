<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Sale\PaySystem;
use \Bitrix\Main\Config;

Loc::loadMessages(__FILE__);

\Bitrix\Main\Loader::includeModule('sale');

$ID = (int)$_GET['pay_system_id'];
$application = \Bitrix\Main\Application::getInstance();
$context = $application->getContext();
$request = $context->getRequest();
$personTypeId = $request->getQuery("personTypeId");
$personTypeList = \Bitrix\Sale\BusinessValue::getPersonTypes();
$errorMsg = '';

if (!PaySystem\Manager::getById($ID))
{
	LocalRedirect("sale_pay_system.php?lang=".LANG);
}

\CUtil::InitJSCore();

if ($request->getQuery("csr") == 1)
{
	PaySystem\YandexCert::getCsr($ID, $request->getQuery("personTypeId"));
}

if (($request->getPost("Update") || $request->getPost("Apply")) && check_bitrix_sessid())
{
	$sitesData = $request->getPost("settings");
	if ($sitesData)
	{
		foreach ($sitesData as $personTypeId => $fields)
		{
			if ($fields["SETTINGS_CLEAR"])
				PaySystem\YandexCert::clear($ID, $personTypeId);

			$certFile = $request->getFile("CERT_FILE_".$personTypeId);
			if (file_exists($certFile['tmp_name']))
				PaySystem\YandexCert::setCert($certFile, $ID, $personTypeId);

			if (array_key_exists($personTypeId, PaySystem\YandexCert::$errors))
			{
				if (array_key_exists($personTypeId, $personTypeList))
					$errorMsg .= $personTypeList[$personTypeId]['NAME'].': ';

				foreach (PaySystem\YandexCert::$errors[$personTypeId] as $error)
					$errorMsg .= $error.' ';
			}
		}
	}

	if ($errorMsg === '')
	{
		LocalRedirect($APPLICATION->GetCurPage()."?pay_system_id=".$ID."&lang=".LANG);
	}
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(Loc::getMessage('SALE_YANDEX_RETURN_TITLE'));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($errorMsg !== '')
	CAdminMessage::ShowMessage(array("DETAILS"=>$errorMsg, "TYPE"=>"ERROR", "HTML"=>true));

$personTypeTabs = array();
$personTypeTabs[] = array(
	"PERSON_TYPE" => 0,
	"DIV" => 0,
	"TAB" => Loc::getMessage('SALE_YANDEX_RETURN_PT_BY_DEFAULT'),
	"TITLE" => Loc::getMessage("SALE_YANDEX_RETURN_TITLE").": ".Loc::getMessage('SALE_YANDEX_RETURN_PT_BY_DEFAULT')
);

foreach ($personTypeList as $personTypeId)
{
	$personTypeTabs[] = array(
		"PERSON_TYPE" => $personTypeId["ID"],
		"DIV" => $personTypeId["ID"],
		"TAB" => $personTypeId["NAME"]." (".$personTypeId['LID'].")",
		"TITLE" => Loc::getMessage("SALE_YANDEX_RETURN_TITLE").": ".$personTypeId["NAME"]
	);
}

$tabRControl = new \CAdminTabControl("tabRControl", $personTypeTabs);
$showButton = false;
?>

<?
$aMenu = array(
	array(
		"TEXT" => Loc::getMessage("SPSN_2FLIST"),
		"LINK" => "/bitrix/admin/sale_pay_system_edit.php?ID=".$ID."&lang=".$context->getLanguage(),
		"ICON" => "btn_list"
	)
);

$contextMenu = new CAdminContextMenu($aMenu);
$contextMenu->Show();
?>
<?$tabRControl->Begin();?>

<form method="POST" enctype="multipart/form-data" action="<?=$APPLICATION->GetCurPage()?>?pay_system_id=<?=$ID;?>&lang=<?echo LANG?>" id="<?=$personTypeId?>_form-upload">
	<?=bitrix_sessid_post();?>
	<?foreach($personTypeTabs as $tab) :?>
	<?
		$personTypeId = $tab["PERSON_TYPE"];
		$tabRControl->BeginNextTab();
	?>
		<input type="hidden" name="settings[<?=$personTypeId;?>][PERSON_TYPE_ID]" value="<?=$personTypeId;?>">
		<?if (\Bitrix\Sale\BusinessValue::get('YANDEX_SHOP_ID', 'PAYSYSTEM_'.$ID, $personTypeId)):?>
			<?
			$showButton = true;
			$strCN = PaySystem\YandexCert::getCn($ID, $personTypeId);
			?>
			<tr class="heading">
				<td colspan="2"><?=Loc::getMessage('SALE_YANDEX_RETURN_SUBTITLE');?></td>
			</tr>
			<tr>
				<td width="40%" class="adm-detail-content-cell-l"><?=Loc::getMessage("SALE_YANDEX_RETURN_CERT")?>:</td>

				<td width="60%" class="adm-detail-content-cell-r">
					<?if (!PaySystem\YandexCert::isLoaded($ID, $personTypeId)):?>
						<input type="file" name="CERT_FILE_<?=$personTypeId;?>" size="40">
					<?else:?>
						<?=Loc::getMessage('SALE_YANDEX_RETURN_TEXT_SUCCESS')?><br>
						<?=Loc::getMessage('SALE_YANDEX_RETURN_TEXT_CLEAR')?>
						<input id='settings[<?=$personTypeId;?>][SETTINGS_CLEAR]' type="checkbox" name='settings[<?=$personTypeId;?>][SETTINGS_CLEAR]'>
					<?endif;?>
					<br>
				</td>
			</tr>
			<tr>
				<td colspan="2"><?=Loc::getMessage("SALE_YANDEX_RETURN_HELP")?></td>
			</tr>
			<tr class="heading">
				<td colspan="2"><?=Loc::getMessage("SALE_YANDEX_RETURN_HOW")?></td>
			</tr>
			<tr>
				<td colspan="2">
					<ol>
						<li><?=sprintf(Loc::getMessage("SALE_YANDEX_RETURN_HOW_ITEM1"),"sale_yandex_return_settings.php?mid=&lang=ru&csr=1&personTypeId=".$personTypeId."&pay_system_id=".$ID)?></li>
						<li><?=Loc::getMessage("SALE_YANDEX_RETURN_HOW_ITEM2")?></li>
						<li><?=Loc::getMessage("SALE_YANDEX_RETURN_HOW_ITEM3")?></li>
						<li><?=Loc::getMessage("SALE_YANDEX_RETURN_HOW_ITEM4")?></li>
						<li><?=Loc::getMessage("SALE_YANDEX_RETURN_HOW_ITEM5")?></li>
					</ol>
				</td>
			</tr>
			<tr class="heading">
				<td colspan="2"><?=Loc::getMessage("SALE_YANDEX_RETURN_STATEMENT")?></td>
			</tr>
			<tr>
				<td colspan="2"><?=Loc::getMessage("SALE_YANDEX_RETURN_STATEMENT_INTRO")?></td>
			</tr>
			<tr>
				<td class="adm-detail-valign-top adm-detail-content-cell-l"><strong><?=Loc::getMessage("SALE_YANDEX_RETURN_STATEMENT_CN")?></strong>:</td>
				<td class="adm-detail-content-cell-r"><?=$strCN?></td>
			</tr>
			<tr>
				<td class="adm-detail-valign-top adm-detail-content-cell-l"><strong><?=Loc::getMessage("SALE_YANDEX_RETURN_STATEMENT_SIGN")?></strong>:</td>
				<td class="adm-detail-content-cell-r">
					<textarea cols="55" disabled="" rows="13" >
						<?=PaySystem\YandexCert::getSign($ID, $personTypeId)?>
					</textarea>

				</td>
			</tr>
			<tr>
				<td class="adm-detail-valign-top adm-detail-content-cell-l"><strong><?=Loc::getMessage("SALE_YANDEX_RETURN_STATEMENT_CAUSE")?></strong>:</td>
				<td class="adm-detail-content-cell-r"><?=Loc::getMessage("SALE_YANDEX_RETURN_STATEMENT_CAUSE_VAL")?></td>
			</tr>
			<?else:?>
				<tr>
					<td colspan="2">
						<?
							CAdminMessage::ShowMessage(array("DETAILS"=>Loc::getMessage("SALE_YANDEX_RETURN_ERROR_SHOP_ID"), "TYPE"=>"ERROR", "HTML"=>true));
						?>
					</td>
				</tr>
		<?endif;?>
		<?$tabRControl->EndTab();?>

	<? endforeach; ?>

	<?if ($showButton):?>
		<?$tabRControl->Buttons();?>
		<input type="submit" name="Update" value="<?=Loc::getMessage("SALE_YANDEX_RETURN_SAVE")?>">
		<input type="hidden" name="Update" value="Y">
	<?endif;?>
</form>

<?$tabRControl->End();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");