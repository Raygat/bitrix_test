<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$this->setFrameMode(true);

if (
	!is_array($arResult["PresetFilters"])
	&& !(
		array_key_exists("SHOW_SETTINGS_LINK", $arParams)
		&& $arParams["SHOW_SETTINGS_LINK"] == "Y"
	)
)
{
	return;
}

$isFiltered = false;
foreach (array("flt_created_by_id", "flt_group_id", "flt_to_user_id", "flt_date_datesel", "flt_show_hidden", "CREATED_BY_CODE", "TO_CODE") as $param)
{
	if (
		array_key_exists($param, $_GET)
		&& (
			(
				is_array($_GET[$param])
				&& !empty($_GET[$param])
			)
			|| (
				!is_array($_GET[$param])
				&& strlen($_GET[$param]) > 0
				&& $_GET[$param] !== "0"
			)
		)
	)
	{
		$isFiltered = true;
		break;
	}
}

if (!is_array($arResult["PageParamsToClear"]))
{
	$arResult["PageParamsToClear"] = array();
}

if ($arResult["MODE"] == "AJAX")
{
	$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/main.post.form/templates/.default/style.css');

	ob_end_clean();
	$APPLICATION->RestartBuffer();
	?>
	<script>
		BX.ready(function(){
			oLFFilter.initFilter();
		});
	</script>
	<div id="sonet-log-filter" class="sonet-log-filter-block">
		<div class="log-filter-title"><?=GetMessage("SONET_C30_T_FILTER_TITLE")?></div>
		<form class="log-filter-form" method="GET" name="log_filter" target="_self" action="<?=POST_FORM_ACTION_URI?>">
		<input type="hidden" name="SEF_APPLICATION_CUR_PAGE_URL" value="<?=GetPagePath()?>"><?
		?><div class="log-filter-field">
			<label class="log-filter-field-title" for="log-filter-field-created-by"><?=GetMessage("SONET_C30_T_FILTER_CREATED_BY");?></label>
			<div class="feed-add-post-destination-wrap feed-add-post-destination-filter" id="sonet-log-filter-created-by">
				<span id="sonet-log-filter-created-by-item"></span>
				<span class="feed-add-destination-input-box" style="display: inline-block;">
					<input type="text" value="" class="feed-add-destination-inp" id="filter-field-created-by">
				</span>
			</div>
		</div>
		<script>
			oLFFilter.initDestination({
				userNameTemplate: '<?=CUtil::JSEscape($arParams["NAME_TEMPLATE"])?>',
				name: 'feed-filter-created-by',
				inputName: 'filter-field-created-by',
				inputContainerName: 'sonet-log-filter-created-by-item',
				resultFieldName: 'CREATED_BY_CODE',
				extranetUser: <?=($arResult["bExtranetUser"] ? 'true' : 'false')?>,
				bindNode: BX('sonet-log-filter-created-by'),
				departmentSelectDisable: true,
				items: {
					users: <?=(empty($arResult["CREATED_BY_DEST"]["ITEMS"]["USERS"]) ? '{}': CUtil::PhpToJSObject($arResult["CREATED_BY_DEST"]["ITEMS"]["USERS"]))?>,
					department : <?=(empty($arResult["CREATED_BY_DEST"]["ITEMS"]['DEPARTMENT']) ? '{}' : CUtil::PhpToJSObject($arResult["CREATED_BY_DEST"]["ITEMS"]['DEPARTMENT']))?>,
					extranetRoot : <?=(empty($arResult["CREATED_BY_DEST"]["EXTRANET_ROOT"]) ? '{}' : CUtil::PhpToJSObject($arResult["CREATED_BY_DEST"]["EXTRANET_ROOT"]))?>
				},
				itemsLast: {
					users: <?=(empty($arResult["CREATED_BY_DEST"]["LAST"]["USERS"]) ? '{}': CUtil::PhpToJSObject($arResult["CREATED_BY_DEST"]["LAST"]["USERS"]))?>
				},
				itemsSelected : <?=(empty($arResult["CREATED_BY_DEST"]['SELECTED'])? '{}': CUtil::PhpToJSObject($arResult["CREATED_BY_DEST"]['SELECTED']))?>,
				destSort: <?=(empty($arResult["CREATED_BY_DEST"]["SORT"]) ? '{}' : CUtil::PhpToJSObject($arResult["CREATED_BY_DEST"]["SORT"]))?>
			});
		</script>
		<?
		$bChecked = (array_key_exists("flt_comments", $_REQUEST) && $_REQUEST["flt_comments"] == "Y");

		?><div class="log-filter-field" id="flt_comments_cont" style="display: <?=(intval($arParams["CREATED_BY_ID"]) > 0 ? "block" : "none")?>"><input type="checkbox" class="filter-checkbox" id="flt_comments" name="flt_comments" value="Y" <?=($bChecked ? "checked" : "")?>> <label class="log-filter-field-title log-filter-field-title-checkbox" for="flt_comments"><?=GetMessage("SONET_C30_T_FILTER_COMMENTS")?></label></div><?
		?><div class="log-filter-field">
			<label class="log-filter-field-title" for="log-filter-field-to"><?=GetMessage("SONET_C30_T_FILTER_TO");?></label>
			<div class="feed-add-post-destination-wrap feed-add-post-destination-filter" id="sonet-log-filter-to">
				<span id="sonet-log-filter-to-item"></span>
				<span class="feed-add-destination-input-box" style="display: inline-block;">
					<input type="text" value="" class="feed-add-destination-inp" id="filter-field-to">
				</span>
			</div>
		</div>
		<script>
			oLFFilter.initDestination({
				userNameTemplate: '<?=CUtil::JSEscape($arParams["NAME_TEMPLATE"])?>',
				name: 'feed-filter-to',
				inputName: 'filter-field-to',
				inputContainerName: 'sonet-log-filter-to-item',
				resultFieldName: 'TO_CODE',
				extranetUser: <?=($arResult["bExtranetUser"] ? 'true' : 'false')?>,
				bindNode: BX('sonet-log-filter-to'),
				items: {
					users: <?=(empty($arResult["TO_DEST"]["ITEMS"]["USERS"]) ? '{}': CUtil::PhpToJSObject($arResult["TO_DEST"]["ITEMS"]["USERS"]))?>,
					sonetgroups : <?=(empty($arResult["TO_DEST"]["ITEMS"]["SONETGROUPS"])? '{}': CUtil::PhpToJSObject($arResult["TO_DEST"]["ITEMS"]["SONETGROUPS"]))?>,
					department : <?=(empty($arResult["TO_DEST"]["ITEMS"]['DEPARTMENT']) ? '{}' : CUtil::PhpToJSObject($arResult["TO_DEST"]["ITEMS"]['DEPARTMENT']))?>,
					extranetRoot : <?=(empty($arResult["TO_DEST"]["EXTRANET_ROOT"]) ? '{}' : CUtil::PhpToJSObject($arResult["TO_DEST"]["EXTRANET_ROOT"]))?>
				},
				itemsLast: {
					users: <?=(empty($arResult["TO_DEST"]["LAST"]["USERS"]) ? '{}': CUtil::PhpToJSObject($arResult["TO_DEST"]["LAST"]["USERS"]))?>,
					sonetgroups: <?=(empty($arResult["TO_DEST"]["LAST"]["SONETGROUPS"]) ? '{}': CUtil::PhpToJSObject($arResult["TO_DEST"]["LAST"]["SONETGROUPS"]))?>,
					department : <?=(empty($arResult["TO_DEST"]['LAST']['DEPARTMENT'])? '{}': CUtil::PhpToJSObject($arResult["TO_DEST"]['LAST']['DEPARTMENT']))?>
				},
				itemsSelected : <?=(empty($arResult["TO_DEST"]['SELECTED'])? '{}': CUtil::PhpToJSObject($arResult["TO_DEST"]['SELECTED']))?>,
				destSort: <?=(empty($arResult["TO_DEST"]["SORT"]) ? '{}' : CUtil::PhpToJSObject($arResult["TO_DEST"]["SORT"]))?>
			});
		</script>
		<div class="log-filter-field log-filter-field-date-combobox">
			<label for="flt-date-datesel" class="log-filter-field-title"><?=GetMessage("SONET_C30_T_FILTER_DATE");?></label>
			<span class="log-filter-field-inp-container">
				<select name="flt_date_datesel" onchange="__logOnDateChange(this)" class="log-filter-field-inp" id="flt-date-datesel"><?
				foreach($arResult["DATE_FILTER"] as $k=>$v):
					?><option value="<?=$k?>"<?if($_REQUEST["flt_date_datesel"] == $k) echo ' selected="selected"'?>><?=$v?></option><?
				endforeach;
				?></select>
			</span>
			<span class="log-filter-date-interval log-filter-date-interval-after log-filter-date-interval-before">
				<span id="flt_date_day_span" style="display:none">
					<span class="log-filter-field-inp-container log-filter-day-interval">
						<input type="text" name="flt_date_days" value="<?=htmlspecialcharsbx($_REQUEST["flt_date_days"])?>" class="log-filter-date-days log-filter-field-inp" size="2" />
					</span>
				</span>
				<label class="log-filter-field-title" id="flt_date_day_text_span" style="display:none"><?echo GetMessage("SONET_C30_DATE_FILTER_DAYS")?></label>
			</span>
			<span class="log-filter-date-interval log-filter-date-interval-after log-filter-date-interval-before">
				<span class="log-filter-field-inp-container log-filter-field-inp-date" style="display:none" id="flt_date_from_span"><?
					?><input class="log-filter-field-inp" type="text" id="flt_date_from" name="flt_date_from" value="<?=(array_key_exists("LOG_DATE_FROM", $arParams) ? $arParams["LOG_DATE_FROM"] : "")?>" /><?
					?><div style="display: none;"><?
					$APPLICATION->IncludeComponent(
						"bitrix:main.calendar",
						"",
						array(
							"SHOW_INPUT" => "N",
							"INPUT_NAME" => "flt_date_from",
							"INPUT_VALUE" => (array_key_exists("LOG_DATE_FROM", $arParams) ? $arParams["LOG_DATE_FROM"] : ""),
							"FORM_NAME" => "log_filter",
							"SHOW_TIME" => "N",
							"HIDE_TIMEBAR" => "Y"
						),
						$component,
						array("HIDE_ICONS"	=> true)
					);?>
					</div>
				</span><?
				?><span class="log-filter-date-interval-hellip" style="display:none" id="flt_date_hellip_span">&hyphen;</span><?
				?><span class="log-filter-field-inp-container log-filter-field-inp-date" style="display:none" id="flt_date_to_span"><?
					?><input class="log-filter-field-inp" type="text" id="flt_date_to" name="flt_date_to" value="<?=(array_key_exists("LOG_DATE_TO", $arParams) ? $arParams["LOG_DATE_TO"] : "")?>" class="log-filter-date-interval-to" /><?
					?><div style="display: none;"><?
					$APPLICATION->IncludeComponent(
						"bitrix:main.calendar",
						"",
						array(
							"SHOW_INPUT" => "N",
							"INPUT_NAME" => "flt_date_to",
							"INPUT_VALUE" => (array_key_exists("LOG_DATE_TO", $arParams) ? $arParams["LOG_DATE_TO"] : ""),
							"FORM_NAME" => "log_filter",
						),
						$component,
						array("HIDE_ICONS"	=> true)
					);?>
					</div>
				</span>
			</span>
		</div>

		<script type="text/javascript">
			BX.ready(function(){
				__logOnDateChange(document.forms['log_filter'].flt_date_datesel);
			});
		</script>
		<?
		if ($arParams["SUBSCRIBE_ONLY"] == "Y")
		{
			$bChecked = (array_key_exists("flt_show_hidden", $_REQUEST) && $_REQUEST["flt_show_hidden"] == "Y");
			?><div class="log-filter-field"><input type="checkbox" class="filter-checkbox" id="flt_show_hidden" name="flt_show_hidden" value="Y" <?=($bChecked ? "checked" : "")?>> <label for="flt_show_hidden"><?=GetMessage("SONET_C30_T_SHOW_HIDDEN")?></label></div><?
		}

		?><div class="sonet-log-filter-submit"><?
			?><span class="popup-window-button popup-window-button-create" onclick="document.forms['log_filter'].submit();"><?
				?><span class="popup-window-button-left"></span><?
				?><span class="popup-window-button-text"><?=GetMessage("SONET_C30_T_SUBMIT")?></span><?
				?><span class="popup-window-button-right"></span><?
			?></span><?
			?><input type="hidden" name="log_filter_submit" value="Y"><?
			if ($isFiltered)
			{
				?><a href="<?=$APPLICATION->GetCurPageParam("preset_filter_id=".(array_key_exists("preset_filter_id", $_GET) && strlen($_GET["preset_filter_id"]) > 0 ? htmlspecialcharsbx($_GET["preset_filter_id"]) : "clearall"), array("flt_created_by_id","flt_group_id","flt_to_user_id","flt_date_datesel","flt_date_days","flt_date_from","flt_date_to","flt_date_to","flt_show_hidden","skip_subscribe","preset_filter_id","sessid","bxajaxid", "log_filter_submit", "FILTER_CREATEDBY","SONET_FILTER_MODE", "set_follow_type","CREATED_BY_CODE","TO_CODE"), false)?>" class="popup-window-button popup-window-button-link popup-window-button-link-cancel"><span class="popup-window-button-link-text"><?=GetMessage("SONET_C30_T_RESET")?></span></a><?
			}
		?></div>
		<input type="hidden" name="skip_subscribe" value="<?=(isset($_REQUEST["skip_subscribe"]) && $_REQUEST["skip_subscribe"] == "Y" ? "Y" : "N")?>">
		<input type="hidden" name="preset_filter_id" value="<?=(array_key_exists("preset_filter_id", $_GET) ? htmlspecialcharsbx($_GET["preset_filter_id"]) : "")?>" />
		</form>
	</div><?
	die();
}
else
{
	if ($arParams["USE_TARGET"] != "N")
	{
		$this->SetViewTarget((
			strpos(SITE_TEMPLATE_ID, "bitrix24") !== false
				? (strlen($arParams["PAGETITLE_TARGET"]) > 0 ? $arParams["PAGETITLE_TARGET"] : "pagetitle")
				: (strlen($arParams["TARGET_ID"]) > 0 ? $arParams["TARGET_ID"] : "sonet_blog_form")
			),
			50
		);
	}

	$isCompositeMode = defined("BITRIX24_INDEX_COMPOSITE");
	$isCompositeMode === false ?: ($dynamicArea = $this->createFrame()->begin(""));

	?><script type="text/javascript">

		function showLentaMenu(bindElement)
		{
			BX.addClass(bindElement, "lenta-sort-button-active");
			BX.PopupMenu.show("lenta-sort-popup", bindElement, [
				{
					text : "<?=(!empty($arResult["ALL_ITEM_TITLE"]) > 0 ? $arResult["ALL_ITEM_TITLE"] : GetMessageJS("SONET_C30_PRESET_FILTER_ALL"))?>",
					className : (window.bRefreshed !== undefined && window.bRefreshed ? "lenta-sort-item lenta-sort-item-selected" : "lenta-sort-item<?=(!$arResult["PresetFilterActive"] ? " lenta-sort-item-selected" : "")?>"),
					href : "<?=CUtil::JSEscape($APPLICATION->GetCurPageParam("preset_filter_id=clearall", array_merge($arResult["PageParamsToClear"], array("preset_filter_id"))))?>"
				},
				<?
				$buttonName = false;
				if (is_array($arResult["PresetFilters"]))
				{
					foreach($arResult["PresetFilters"] as $preset_filter_id => $arPresetFilter)
					{
						if ($arResult["PresetFilterActive"] == $preset_filter_id)
							$buttonName = $arPresetFilter["NAME"];
						?>{
							text : "<?=$arPresetFilter["NAME"]?>",
							className : (window.bRefreshed !== undefined && window.bRefreshed ? "lenta-sort-item" : "lenta-sort-item<?=($arResult["PresetFilterActive"] == $preset_filter_id ? " lenta-sort-item-selected" : "")?>"),
							href : "<?=CUtil::JSEscape($APPLICATION->GetCurPageParam("preset_filter_id=".$preset_filter_id, array_merge($arResult["PageParamsToClear"], array("preset_filter_id"))))?>"
						},<?
					}
				}
				?>
				{ delimiter : true },
				{
					text : "<?=GetMessageJS("SONET_C30_T_FILTER_TITLE")?>...",
					className : (window.bRefreshed !== undefined && window.bRefreshed ? "lenta-sort-item" : "lenta-sort-item<?=($isFiltered ? " lenta-sort-item-selected" : "")?>"),
					onclick: function() {
						this.popupWindow.close();
						oLFFilter.ShowFilterPopup(BX("lenta-sort-button"));
					}
				}
				<?
				if ($arParams["SHOW_FOLLOW"] != "N")
				{
					?>
					,{ delimiter : true },
					{
						text : "<?=GetMessageJS("SONET_C30_SMART_FOLLOW")?>",
						className : "lenta-sort-item<?=($arResult["FOLLOW_TYPE"] == "N" ? " lenta-sort-item-selected" : "")?>",
						href : "<?=CUtil::JSEscape($APPLICATION->GetCurPageParam("set_follow_type=".($arResult["FOLLOW_TYPE"] == "Y" ? "N" : "Y"), array("set_follow_type")))?>"
					}
					<?
				}

				if (
					$arParams["SHOW_EXPERT_MODE"] != "N"
					&& class_exists('\Bitrix\Socialnetwork\LogViewTable') // socialnetwork 16.5.0
				)
				{
					?>
					,{ delimiter : true },
					{
						text : "<?=GetMessageJS("SONET_C30_SMART_EXPERT_MODE")?>",
						className : "lenta-sort-item<?=($arResult["EXPERT_MODE"] == "Y" ? " lenta-sort-item-selected" : "")?>",
						href : "<?=CUtil::JSEscape($APPLICATION->GetCurPageParam("set_expert_mode=".($arResult["EXPERT_MODE"] == "Y" ? "N" : "Y"), array("set_expert_mode")))?>"
					}
					<?
				}
				?>
			],
			{
				offsetTop:2,
				offsetLeft : 43,
				angle : true,
				events : {
					onPopupClose : function() {
						BX.removeClass(this.bindElement, "lenta-sort-button-active");
					}
				}
			});
			return false;
		}

		<?
		if (
			isset($arResult["SHOW_EXPERT_MODE_POPUP"])
			&& $arResult["SHOW_EXPERT_MODE_POPUP"] == "Y"
		)
		{
			?>
			BX.ready(function() {
				setTimeout(function() {
					oLFFilter.__SLFShowExpertModePopup(null);
				}, 1000);
			});
			<?
		}
		?>
		BX.message({
			sonetLFAjaxPath: '<?=CUtil::JSEscape($arResult["AjaxURL"])?>',
			ajaxControllerURL: '<?=CUtil::JSEscape($arResult["ajaxControllerURL"])?>',
			sonetLFAllMessages: '<?=GetMessageJS("SONET_C30_PRESET_FILTER_ALL")?>',
			sonetLFDialogClose: '<?=GetMessageJS("SONET_C30_F_DIALOG_CLOSE_BUTTON")?>',
			sonetLFDialogRead: '<?=GetMessageJS("SONET_C30_F_DIALOG_READ_BUTTON")?>',
			sonetLFExpertModePopupTitle: '<?=GetMessageJS("SONET_C30_F_EXPERT_MODE_POPUP_TITLE")?>',
			sonetLFExpertModePopupText1: '<?=GetMessageJS("SONET_C30_F_EXPERT_MODE_POPUP_TEXT1")?>',
			sonetLFExpertModePopupText2: '<?=GetMessageJS("SONET_C30_F_EXPERT_MODE_POPUP_TEXT2")?>',
			sonetLFExpertModeImagePath: '<?=CUtil::JSEscape($this->GetFolder())?>/images/expert_mode/<?=GetMessageJS("SONET_C30_F_EXPERT_MODE_IMAGENAME")?>.png'
		});
	</script><?

	$isCompositeMode === false ?: $dynamicArea->end();
	$logCounter = intval($arResult["LOG_COUNTER"]);

	if (strpos(SITE_TEMPLATE_ID, "bitrix24") !== false)
	{
		?><a href="" id="lenta-sort-button" class="lenta-sort-button" onclick="return showLentaMenu(this);" onmousedown="BX.addClass(this, 'lenta-sort-button-press')" onmouseup="BX.removeClass(this,'lenta-sort-button-press')"><?
			?><span class="lenta-sort-button-left"></span><?
			?><span class="lenta-sort-button-text"><?
			?><span class="lenta-sort-button-text-internal" id="lenta-button"><?
				$frame = $this->createFrame("lenta-button", false)->begin(GetMessage("SONET_C30_PRESET_FILTER_ALL"));
				echo ($buttonName !== false ? $buttonName : GetMessage("SONET_C30_PRESET_FILTER_ALL") );
				echo ($isFiltered ? " (".GetMessageJS("SONET_C30_T_FILTER_TITLE").")" : "");
				if ($logCounter > 0 && Bitrix\Main\Page\Frame::isAjaxRequest()):?>
					<script type="text/javascript">BX("sonet_log_counter_preset").innerHTML="<?=$logCounter?>"</script><?
				endif;
				$frame->end();
			?></span><?
			if ($buttonName === false || $isCompositeMode):
				?><span id="sonet_log_counter_preset" class="pagetitle-but-counter"><?=(($logCounter > 0 && $arParams["ENTITY_TYPE"] != SONET_ENTITY_GROUP && !$isCompositeMode) ? $logCounter : "")?></span><?
			endif;
			?></span><?
			?><span class="lenta-sort-button-right"></span><?
		?></a><?
	}
	else
	{
		?><div id="lenta-sort-button" class="feed-filter-btn-wrap">
		<span class="feed-filter-btn" id="feed_filter_button" onclick="showLentaMenu(this)"><?
			?><?=($buttonName !== false ? $buttonName : GetMessage("SONET_C30_PRESET_FILTER_ALL") )?><?=($isFiltered ? " (".GetMessageJS("SONET_C30_T_FILTER_TITLE").")" : "")?><?
			if ($buttonName === false):
				?><i id="sonet_log_counter_preset"><?=((intval($arResult["LOG_COUNTER"]) > 0 && $arParams["ENTITY_TYPE"] != SONET_ENTITY_GROUP) ? $arResult["LOG_COUNTER"] : "")?></i><?
			endif;
			?></span>
		</div><?
	}

	if ($arParams["USE_TARGET"] != "N")
	{
		$this->EndViewTarget();
	}

	if (isset($_SESSION["SL_SHOW_FOLLOW_HINT"]))
	{
		unset($_SESSION["SL_SHOW_FOLLOW_HINT"]);
		?><div class="feed-smart-follow-hint"><?=GetMessage("SONET_C30_SMART_FOLLOW_HINT");?></div><?
	}
	elseif (isset($_SESSION["SL_EXPERT_MODE_HINT"]))
	{
		unset($_SESSION["SL_EXPERT_MODE_HINT"]);
		?><div class="feed-smart-follow-hint"><?=GetMessage("SONET_C30_EXPERT_MODE_HINT");?></div><?
	}
}
?>