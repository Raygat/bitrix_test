<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>

<?
if (strlen($arCurrentValues["mail_charset"]) <= 0)
	$arCurrentValues["mail_charset"] = "windows-1251";
if (strlen($arCurrentValues["mail_message_type"]) <= 0)
	$arCurrentValues["mail_message_type"] = "plain";
?>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPMA_PD_FROM") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'mail_user_from', $arCurrentValues['mail_user_from'], Array('size'=> 50))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPMA_PD_TO") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'mail_user_to', $arCurrentValues['mail_user_to'], Array('size'=> 50))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><span class="adm-required-field"><?= GetMessage("BPMA_PD_SUBJECT") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'mail_subject', $arCurrentValues['mail_subject'], Array('size'=> 50))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"><span class="adm-required-field"><?= GetMessage("BPMA_PD_BODY") ?>:</span></td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("text", 'mail_text', $arCurrentValues['mail_text'], Array('rows'=> 7))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPMA_PD_MESS_TYPE") ?>:</td>
	<td width="60%">
		<select name="mail_message_type">
			<option value="plain"<?= $arCurrentValues["mail_message_type"] == "plain" ? " selected" : "" ?>><?= GetMessage("BPMA_PD_TEXT") ?></option>
			<option value="html"<?= $arCurrentValues["mail_message_type"] == "html" ? " selected" : "" ?>>Html</option>
		</select>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPMA_PD_CP") ?>:</td>
	<td width="60%">
		<?=CBPDocument::ShowParameterField("string", 'mail_charset', $arCurrentValues['mail_charset'], Array('size'=> 50))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPMA_PD_DIRRECT_MAIL") ?>:</td>
	<td width="60%">
		<input type="radio" name="dirrect_mail" value="Y" id="dirrect_mail_Y"<?= ($arCurrentValues["dirrect_mail"] == "Y") ? " checked": "" ?>><label for="dirrect_mail_Y"><?= GetMessage("BPMA_PD_DIRRECT_MAIL_Y") ?></label><br />
		<input type="radio" name="dirrect_mail" value="N" id="dirrect_mail_N"<?= ($arCurrentValues["dirrect_mail"] == "N") ? " checked": "" ?>><label for="dirrect_mail_N"><?= GetMessage("BPMA_PD_DIRRECT_MAIL_N") ?></label>
	</td>
</tr>
<tr>
	<td align="right" width="40%" valign="top"><?= GetMessage("BPMA_PD_MAIL_SITE") ?>:</td>
	<td width="60%">
		<select name="mail_site">
			<option value="">(<?= GetMessage("BPMA_PD_MAIL_SITE_OTHER") ?>)</option>
			<?
			$bFound = false;
			$dbSites = CSite::GetList(($b = ""), ($o = ""), Array("ACTIVE" => "Y"));
			while ($site = $dbSites->GetNext())
			{
				$bFound = ($site["LID"] == $arCurrentValues["mail_site"]);
				?><option value="<?= $site["LID"] ?>"<?= ($site["LID"] == $arCurrentValues["mail_site"]) ? " selected" : ""?>>[<?= $site["LID"] ?>] <?= $site["NAME"] ?></option><?
			}
			?>
		</select><br>
		<?=CBPDocument::ShowParameterField("string", 'mail_site_x', $arCurrentValues['mail_site'], Array('size'=> 20))?>
	</td>
</tr>
<tr>
	<td align="right" width="40%"><?= GetMessage("BPMA_PD_MAIL_SEPARATOR") ?>:</td>
	<td width="60%">
		<input type="text" name="mail_separator" size="4" value="<?= htmlspecialcharsbx($arCurrentValues["mail_separator"]) ?>" />
	</td>
</tr>