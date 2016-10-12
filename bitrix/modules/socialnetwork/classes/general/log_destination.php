<?
$GLOBALS["SOCNET_LOG_DESTINATION"] = Array();
class CSocNetLogDestination
{
	/**
	* Retrieves last used users from socialnetwork/log_destination UserOption
	* @deprecated
	*/
	public static function GetLastUser()
	{
		global $USER;

		$userId = intval($USER->GetID());

		if(!isset($GLOBALS["SOCNET_LOG_DESTINATION"]["GetLastUser"][$userId]))
		{
			$arLastSelected = CUserOptions::GetOption("socialnetwork", "log_destination", array());
			$arLastSelected = (
				is_array($arLastSelected)
				&& strlen($arLastSelected['users']) > 0
				&& $arLastSelected['users'] != '"{}"'
					? array_reverse(CUtil::JsObjectToPhp($arLastSelected['users']))
					: array()
			);

			if (is_array($arLastSelected))
			{
				if (!isset($arLastSelected[$userId]))
				{
					$arLastSelected['U'.$userId] = 'U'.$userId;
				}
			}
			else
			{
				$arLastSelected['U'.$userId] = 'U'.$userId;
			}

			$count = 0;
			$arUsers = Array();
			foreach ($arLastSelected as $userId)
			{
				if ($count < 5)
				{
					$count++;
				}
				else
				{
					break;
				}

				$arUsers[$userId] = $userId;
			}
			$GLOBALS["SOCNET_LOG_DESTINATION"]["GetLastUser"][$userId] = array_reverse($arUsers);
		}

		return $GLOBALS["SOCNET_LOG_DESTINATION"]["GetLastUser"][$userId];
	}

	/**
	* Retrieves last used sonet groups from socialnetwork/log_destination UserOption
	* @deprecated
	*/
	public static function GetLastSocnetGroup()
	{
		$arLastSelected = CUserOptions::GetOption("socialnetwork", "log_destination", array());
		$arLastSelected = (
			is_array($arLastSelected)
			&& strlen($arLastSelected['sonetgroups']) > 0
			&& $arLastSelected['sonetgroups'] != '"{}"'
				? array_reverse(CUtil::JsObjectToPhp($arLastSelected['sonetgroups']))
				: array()
		);

		$count = 0;
		$arSocnetGroups = Array();
		foreach ($arLastSelected as $sgId)
		{
			if ($count <= 4)
			{
				$count++;
			}
			else
			{
				break;
			}

			$arSocnetGroups[$sgId] = $sgId;
		}
		return array_reverse($arSocnetGroups);
	}

	/**
	* Retrieves last used department from socialnetwork/log_destination UserOption
	* @deprecated
	*/
	public static function GetLastDepartment()
	{
		$arLastSelected = CUserOptions::GetOption("socialnetwork", "log_destination", array());
		$arLastSelected = (
			is_array($arLastSelected)
			&& strlen($arLastSelected['department']) > 0
			&& $arLastSelected['department'] != '"{}"'
				? array_reverse(CUtil::JsObjectToPhp($arLastSelected['department']))
				: array()
		);

		$count = 0;
		$arDepartment = Array();
		foreach ($arLastSelected as $depId)
		{
			if ($count < 4)
			{
				$count++;
			}
			else
			{
				break;
			}

			$arDepartment[$depId] = $depId;
		}

		return array_reverse($arDepartment);
	}

	public static function GetStucture($arParams = Array())
	{
		$bIntranetEnabled = false;
		if(
			IsModuleInstalled('intranet')
			&& IsModuleInstalled('iblock')
		)
		{
			$bIntranetEnabled = true;
		}

		$result = array(
			"department" => array(),
			"department_relation" => array(),
			"department_relation_head" => array(),
		);

		$department_id = (
			isset($arParams["DEPARTMENT_ID"])
			&& intval($arParams["DEPARTMENT_ID"]) > 0
				? intval($arParams["DEPARTMENT_ID"])
				: false
		);

		if($bIntranetEnabled)
		{
			if (!(CModule::IncludeModule('extranet') && !CExtranet::IsIntranetUser()))
			{
				if(($iblock_id = COption::GetOptionInt('intranet', 'iblock_structure', 0)) > 0)
				{
					global $CACHE_MANAGER;

					$ttl = (
						defined("BX_COMP_MANAGED_CACHE")
							? 2592000
							: 600
					);

					$cache_id = 'sonet_structure_new4_'.$iblock_id.(intval($department_id) > 0 ? "_".$department_id : "");
					$obCache = new CPHPCache;
					$cache_dir = '/sonet/structure';

					if($obCache->InitCache($ttl, $cache_id, $cache_dir))
					{
						$result = $obCache->GetVars();
					}
					else
					{
						CModule::IncludeModule('iblock');

						if(defined("BX_COMP_MANAGED_CACHE"))
						{
							$CACHE_MANAGER->StartTagCache($cache_dir);
						}

						$arFilter = array(
							"IBLOCK_ID" => $iblock_id,
							"ACTIVE" => "Y"
						);

						if (intval($department_id) > 0)
						{
							$rsSectionDepartment = CIBlockSection::GetList(
								array(), 
								array(
									"ID" => intval($department_id)
								),
								false, 
								array("ID", "LEFT_MARGIN", "RIGHT_MARGIN")
							);

							if ($arSectionDepartment = $rsSectionDepartment->Fetch())
							{
								$arFilter[">=LEFT_MARGIN"] = $arSectionDepartment["LEFT_MARGIN"];
								$arFilter["<=RIGHT_MARGIN"] = $arSectionDepartment["RIGHT_MARGIN"];
							}
						}

						$dbRes = CIBlockSection::GetList(
							array("left_margin"=>"asc"),
							$arFilter,
							false,
							array("ID", "IBLOCK_SECTION_ID", "NAME")
						);
						while ($ar = $dbRes->Fetch())
						{
							$result["department"]['DR'.$ar['ID']] = array(
								'id' => 'DR'.$ar['ID'],
								'entityId' => $ar["ID"],
								'name' => htmlspecialcharsbx($ar['NAME']),
								'parent' => 'DR'.intval($ar['IBLOCK_SECTION_ID']),
							);
						}
						if(defined("BX_COMP_MANAGED_CACHE"))
						{
							$CACHE_MANAGER->RegisterTag('iblock_id_'.$iblock_id);
							$CACHE_MANAGER->EndTagCache();
						}

						if($obCache->StartDataCache())
						{
							$obCache->EndDataCache($result);
						}
					}
				}
			}
		}

		if (
			!empty( $result["department"]) 
			&& !isset($arParams["LAZY_LOAD"])
		)
		{
			$result["department_relation"] = self::GetTreeList('DR'.(intval($department_id) > 0 ? $department_id : 0), $result["department"], true);
			if (intval($arParams["HEAD_DEPT"]) > 0)
			{
				$result["department_relation_head"] = self::GetTreeList('DR'.intval($arParams["HEAD_DEPT"]), $result["department"], true);
			}
		}

		return $result;
	}

	public static function GetExtranetUser(array $arParams = array())
	{
		global $USER;

		$userId = intval($USER->GetID());

		if(!isset($GLOBALS["SOCNET_LOG_DESTINATION"]["GetExtranetUser"][$userId]))
		{
			$arExtParams = Array(
				"FIELDS" => Array("ID", "LAST_NAME", "NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION", "IS_ONLINE")
			);
			$arFilter = Array();
			if (CModule::IncludeModule('extranet') && !CExtranet::IsIntranetUser())
			{
				$arSelect = Array($userId);
				$arSocnetGroups = array();

				$rsGroups = CSocNetUserToGroup::GetList(
					array("GROUP_NAME" => "ASC"),
					array(
						"USER_ID" => $userId,
						"<=ROLE" => SONET_ROLES_USER,
						"GROUP_SITE_ID" => SITE_ID,
						"GROUP_ACTIVE" => "Y",
						"!GROUP_CLOSED" => "Y"
					),
					false,
					array("nTopCount" => 500),
					array("ID", "GROUP_ID")
				);
				while($arGroup = $rsGroups->Fetch())
				{
					$arGroupTmp = array(
						"id" => $arGroup["GROUP_ID"],
						"entityId" => $arGroup["GROUP_ID"]
					);
					$arSocnetGroups[$arGroup["GROUP_ID"]] = $arGroupTmp;
				}

				if (count($arSocnetGroups) > 0)
				{
					$arUserSocNetGroups = Array();
					foreach ($arSocnetGroups as $groupId => $ar)
					{
						$arUserSocNetGroups[] = $groupId;
					}

					$dbUsersInGroup = CSocNetUserToGroup::GetList(
						array(),
						array(
							"GROUP_ID" => $arUserSocNetGroups,
							"<=ROLE" => SONET_ROLES_USER,
							"USER_ACTIVE" => "Y"
						),
						false,
						false,
						array("ID", "USER_ID", "GROUP_ID")
					);
					while ($ar = $dbUsersInGroup->GetNext(true, false))
					{
						$arSelect[] = intval($ar["USER_ID"]);
					}
				}
				$arFilter['ID'] = implode('|', $arSelect);
			}

			$arUsers = Array();

			if (!empty($arFilter['ID']))
			{
				$dbUsers = CUser::GetList(($sort_by = Array('last_name'=>'asc', 'IS_ONLINE'=>'desc')), ($dummy=''), $arFilter, $arExtParams);
				while ($arUser = $dbUsers->GetNext())
				{
					$sName = trim(CUser::FormatName(CSite::GetNameFormat(), $arUser, true, false));

					if (empty($sName))
					{
						$sName = $arUser["~LOGIN"];
					}

					$arFileTmp = CFile::ResizeImageGet(
						$arUser["PERSONAL_PHOTO"],
						array(
							"width" => (intval($arParams["THUMBNAIL_SIZE_WIDTH"]) > 0 ? $arParams["THUMBNAIL_SIZE_WIDTH"] : 32),
							"height" => (intval($arParams["THUMBNAIL_SIZE_HEIGHT"]) > 0 ? $arParams["THUMBNAIL_SIZE_HEIGHT"] : 32)
						),
						BX_RESIZE_IMAGE_EXACT,
						false
					);

					$arUsers['U'.$arUser["ID"]] = Array(
						'id' => 'U'.$arUser["ID"],
						'entityId' => $arUser["ID"],
						'name' => $sName,
						'avatar' => empty($arFileTmp['src'])? '': $arFileTmp['src'],
						'desc' => $arUser['WORK_POSITION'] ? $arUser['WORK_POSITION'] : ($arUser['PERSONAL_PROFESSION']?$arUser['PERSONAL_PROFESSION']:'&nbsp;'),
					);
				}
			}

			$GLOBALS["SOCNET_LOG_DESTINATION"]["GetExtranetUser"][$userId] = $arUsers;
		}
		return $GLOBALS["SOCNET_LOG_DESTINATION"]["GetExtranetUser"][$userId];
	}

	public static function GetUsers($arParams = Array(), $bSelf = true)
	{
		global $USER, $CACHE_MANAGER;

		$userId = intval($USER->GetID());
		$extranetUserIdList = \Bitrix\Socialnetwork\ComponentHelper::getExtranetUserIdList();

		if (
			isset($arParams['all'])
			&& $arParams['all'] == 'Y'
		)
		{
			if (IsModuleInstalled("intranet"))
			{
				return self::GetUsersAll($arParams);
			}
			else
			{
				$arParamsNew = $arParams;
				$arParamsNew["id"] = array($userId);
				unset($arParamsNew["all"]);
				return CSocNetLogDestination::GetUsers($arParamsNew, $bSelf);
			}
		}

		$bExtranet = false;
		$arFilter = Array('ACTIVE' => 'Y');

		if (
			IsModuleInstalled("intranet")
			|| COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y"
		)
		{
			$arFilter["CONFIRM_CODE"] = false;
		}

		$arExtParams = Array(
			"FIELDS" => array("ID", "LAST_NAME", "NAME", "SECOND_NAME", "LOGIN", "EMAIL", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION", "IS_ONLINE", "EXTERNAL_AUTH_ID"),
			"SELECT" => array()
		);

		if (
			isset($arParams["CRM_ENTITY"])
			&& $arParams["CRM_ENTITY"] == "Y"
		)
		{
			$arExtParams['SELECT'][] = 'UF_USER_CRM_ENTITY';
		}

		if (array_key_exists('id', $arParams))
		{
			if (empty($arParams['id']))
			{
				$arFilter['ID'] = $userId;
			}
			else
			{
				$arSelect = array($userId);
				foreach ($arParams['id'] as $value)
				{
					if (
						intval($value) > 0
						&& !in_array($value, $arSelect)
					)
					{
						$arSelect[] = intval($value);
					}
				}
				sort($arSelect);
				$arFilter['ID'] = implode('|', $arSelect);
			}
		}
		elseif (isset($arParams['deportament_id']))
		{
			if (is_array($arParams['deportament_id']))
			{
				$arFilter['UF_DEPARTMENT'] = $arParams['deportament_id'];
			}
			else
			{
				if ($arParams['deportament_id'] == 'EX')
				{
					$bExtranet = true;
				}
				else
				{
					$arFilter['UF_DEPARTMENT'] = intval($arParams['deportament_id']);
				}
			}

			$arExtParams['SELECT'][] = 'UF_DEPARTMENT';
		}

		$avatarSize = array(
			"width" => (intval($arParams["THUMBNAIL_SIZE_WIDTH"]) > 0 ? $arParams["THUMBNAIL_SIZE_WIDTH"] : 32),
			"height" => (intval($arParams["THUMBNAIL_SIZE_HEIGHT"]) > 0 ? $arParams["THUMBNAIL_SIZE_HEIGHT"] : 32)
		);

		$cacheTtl = 3153600;
		$cacheId = 'socnet_destination_getusers_'.md5(serialize($arFilter)).$bSelf.($bExtranet ? '_ex_'.$userId : '').md5(serialize($avatarSize));
		$cacheDir = '/socnet/dest/'.(
			isset($arParams['id']) 
				? 'user'
				: 'dept'
		).'/';

		$obCache = new CPHPCache;
		if($obCache->InitCache($cacheTtl, $cacheId, $cacheDir))
		{
			$arUsers = $obCache->GetVars();
		}
		else
		{
			$obCache->StartDataCache();
			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->StartTagCache($cacheDir);
			}

			if (
				$bExtranet
				&& CModule::IncludeModule("extranet")
			)
			{
				$arUsers = Array();
				$arExtranetUsers = CExtranet::GetMyGroupsUsersFull(CExtranet::GetExtranetSiteID(), $bSelf);
				foreach($arExtranetUsers as $arUserTmp)
				{
					$sName = trim(CUser::FormatName(empty($arParams["NAME_TEMPLATE"]) ? CSite::GetNameFormat(false) : $arParams["NAME_TEMPLATE"], $arUserTmp, true, false));
					if (empty($sName))
					{
						$sName = $arUserTmp["~LOGIN"];
					}

					$arFileTmp = CFile::ResizeImageGet(
						$arUserTmp["PERSONAL_PHOTO"],
						$avatarSize,
						BX_RESIZE_IMAGE_EXACT,
						false
					);

					$arUsers['U'.$arUserTmp["ID"]] = Array(
						'id' => 'U'.$arUserTmp["ID"],
						'entityId' => $arUserTmp["ID"],
						'name' => $sName,
						'avatar' => empty($arFileTmp['src'])? '': $arFileTmp['src'],
						'desc' => $arUserTmp['WORK_POSITION'] ? $arUserTmp['WORK_POSITION'] : ($arUserTmp['PERSONAL_PROFESSION'] ? $arUserTmp['PERSONAL_PROFESSION'] : '&nbsp;'),
					);
					if (defined("BX_COMP_MANAGED_CACHE"))
					{
						$CACHE_MANAGER->RegisterTag("USER_NAME_".IntVal($arUserTmp["ID"]));
					}
				}
			}
			else
			{
				$bExtranetInstalled = CModule::IncludeModule("extranet");
				CSocNetTools::InitGlobalExtranetArrays();

				if (
					!isset($arFilter['UF_DEPARTMENT'])
					&& $bExtranetInstalled
				)
				{
					$arUserIdVisible = CExtranet::GetMyGroupsUsersSimple(SITE_ID);
				}

				$arUsers = Array();

				$dbUsers = CUser::GetList(
					($sort_by = array('last_name'=> 'asc', 'IS_ONLINE'=>'desc')),
					($dummy=''),
					$arFilter,
					$arExtParams
				);

				while ($arUser = $dbUsers->GetNext())
				{
					if (
						!$bSelf
						&& is_object($USER)
						&& $userId == $arUser["ID"]
					)
					{
						continue;
					}

					if (
						!isset($arFilter['UF_DEPARTMENT']) // all users
						&& $bExtranetInstalled
					)
					{
						if (
							isset($arUser["UF_DEPARTMENT"])
							&& (
								!is_array($arUser["UF_DEPARTMENT"])
								|| empty($arUser["UF_DEPARTMENT"])
								|| intval($arUser["UF_DEPARTMENT"][0]) <= 0
							) // extranet user
							&& (
								empty($arUserIdVisible)
								|| !is_array($arUserIdVisible)
								|| !in_array($arUser["ID"], $arUserIdVisible)
							)
						)
						{
							continue;
						}
					}

					$sName = trim(CUser::FormatName(empty($arParams["NAME_TEMPLATE"]) ? CSite::GetNameFormat(false) : $arParams["NAME_TEMPLATE"], $arUser, true, false));

					if (empty($sName))
					{
						$sName = $arUser["~LOGIN"];
					}

					$arFileTmp = CFile::ResizeImageGet(
						$arUser["PERSONAL_PHOTO"],
						$avatarSize,
						BX_RESIZE_IMAGE_EXACT,
						false
					);

					$arUsers['U'.$arUser["ID"]] = Array(
						'id' => 'U'.$arUser["ID"],
						'entityId' => $arUser["ID"],
						'name' => $sName,
						'avatar' => empty($arFileTmp['src'])? '': $arFileTmp['src'],
						'desc' => $arUser['WORK_POSITION'] ? $arUser['WORK_POSITION'] : ($arUser['PERSONAL_PROFESSION'] ? $arUser['PERSONAL_PROFESSION'] : '&nbsp;'),
						'isExtranet' => (in_array($arUser["ID"], $extranetUserIdList) ? "Y" : "N"),
						'isEmail' => ($arUser['EXTERNAL_AUTH_ID'] == 'email' ? 'Y' : 'N'),
						'isCrmEmail' => (
							$arUser['EXTERNAL_AUTH_ID'] == 'email'
							&& !empty($arUser['UF_USER_CRM_ENTITY'])
								? 'Y'
								: 'N'
						)
					);

					if ($arUser['EXTERNAL_AUTH_ID'] == 'email')
					{
						$arUsers['U'.$arUser["ID"]]['email'] = $arUser['EMAIL'];
					}

					$arUsers['U'.$arUser["ID"]]['checksum'] = md5(serialize($arUsers['U'.$arUser["ID"]]));

					if (defined("BX_COMP_MANAGED_CACHE"))
					{
						$CACHE_MANAGER->RegisterTag("USER_NAME_".IntVal($arUser["ID"]));
					}
				}
			}

			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->RegisterTag("USER_NAME");
				$CACHE_MANAGER->EndTagCache();
			}

			$obCache->EndDataCache($arUsers);
		}

		return $arUsers;
	}

	public static function GetGratMedalUsers($arParams = Array())
	{
		global $USER;

		$userId = intval($USER->GetID());

		if(!isset($GLOBALS["SOCNET_LOG_DESTINATION"]["GetGratMedalUsers"][$userId]))
		{
			$arSubordinateDepts = array();

			if (CModule::IncludeModule("intranet"))
			{
				$arSubordinateDepts = CIntranetUtils::GetSubordinateDepartments($userId, true);
			}

			$arFilter = Array(
				"ACTIVE" => "Y",
				"!UF_DEPARTMENT" => false
			);

			$arExtParams = Array(
				"FIELDS" => Array("ID", "LAST_NAME", "NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION", "IS_ONLINE"),
				"SELECT" => Array("UF_DEPARTMENT")
			);

			if (isset($arParams["id"]))
			{
				if (empty($arParams["id"]))
				{
					$arFilter["ID"] = $userId;
				}
				else
				{
					$arSelect = array();
					foreach ($arParams["id"] as $value)
					{
						$arSelect[] = intval($value);
					}
					$arFilter["ID"] = implode("|", $arSelect);
				}
			}

			$arGratUsers = Array();
			$arMedalUsers = Array();

			$dbUsers = CUser::GetList(($sort_by = Array("last_name" => "asc", "IS_ONLINE" => "desc")), ($dummy=''), $arFilter, $arExtParams);
			while ($arUser = $dbUsers->GetNext())
			{
				$sName = trim(CUser::FormatName(empty($arParams["NAME_TEMPLATE"]) ? CSite::GetNameFormat(false) : $arParams["NAME_TEMPLATE"], $arUser));

				if (empty($sName))
				{
					$sName = $arUser["~LOGIN"];
				}

				$arFileTmp = CFile::ResizeImageGet(
					$arUser["PERSONAL_PHOTO"],
					array("width" => 32, "height" => 32),
					BX_RESIZE_IMAGE_EXACT,
					false
				);

				$arGratUsers['U'.$arUser["ID"]] = Array(
					"id" => "U".$arUser["ID"],
					"entityId" => $arUser["ID"],
					"name" => $sName,
					"avatar" => empty($arFileTmp["src"]) ? '' : $arFileTmp["src"],
					"desc" => $arUser["WORK_POSITION"] ? $arUser["WORK_POSITION"] : ($arUser["PERSONAL_PROFESSION"] ? $arUser["PERSONAL_PROFESSION"] : "&nbsp;"),
				);

				if (
					count($arSubordinateDepts) > 0
					&& count(array_intersect($arSubordinateDepts, $arUser["UF_DEPARTMENT"])) > 0
				)
				{
					$arMedalUsers['U'.$arUser["ID"]] = $arGratUsers['U'.$arUser["ID"]];
				}
			}
			$GLOBALS["SOCNET_LOG_DESTINATION"]["GetGratMedalUsers"][$userId] = array("GRAT" => $arGratUsers, "MEDAL" => $arMedalUsers);
		}

		return $GLOBALS["SOCNET_LOG_DESTINATION"]["GetGratMedalUsers"][$userId];
	}

	public function __percent_walk(&$val)
	{
		$val = str_replace('%', '', $val)."%";
	}

	public static function SearchUsers($search, &$nt = "", $bSelf = true, $bEmployeesOnly = false, $bExtranetOnly = false, $departmentId = false)
	{
		global $USER, $DB;

		CUtil::JSPostUnescape();

		if (is_array($search))
		{
			$arParams = $search;
			$search = $arParams["SEARCH"];
			$nameTemplate = (isset($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : '');
			$bSelf = (isset($arParams["SELF"]) ? $arParams["SELF"] : true);
			$bEmployeesOnly = (isset($arParams["EMPLOYEES_ONLY"]) ? $arParams["EMPLOYEES_ONLY"] : false);
			$bExtranetOnly = (isset($arParams["EXTRANET_ONLY"]) ? $arParams["EXTRANET_ONLY"] : false);
			$departmentId = (isset($arParams["DEPARTAMENT_ID"]) ? $arParams["DEPARTAMENT_ID"] : false);
			$bEmailUsers = (isset($arParams["EMAIL_USERS"]) ? $arParams["EMAIL_USERS"] : false);
			$bCrmEmailUsers = (isset($arParams["CRMEMAIL_USERS"]) ? $arParams["CRMEMAIL_USERS"] : false);
		}
		else
		{
			$nameTemplate = $nt;
			$bEmailUsers = false;
			$bCrmEmailUsers = false;
		}

		$arUsers = array();
		$search = trim($search);
		if (
			strlen($search) <= 0
			|| !GetFilterQuery("TEST", $search)
		)
		{
			return $arUsers;
		}

		$bSearchByEmail = false;

		if (preg_match('/^([^<]+)\s<([^>]+)>$/i', $search, $matches)) // email
		{
			$search = $matches[2];
			$nt = $search;
			$bSearchByEmail = true;
		}

		$bIntranetEnabled = IsModuleInstalled('intranet');
		$bExtranetEnabled = CModule::IncludeModule('extranet');
		$bMailEnabled = IsModuleInstalled('mail');
		$bBitrix24Enabled = IsModuleInstalled('bitrix24');

		$bEmailUsersAll = ($bMailEnabled && \Bitrix\Main\Config\Option::get('socialnetwork', 'email_users_all', 'N') == 'Y');
 		$bExtranetUser = ($bExtranetEnabled && !CExtranet::IsIntranetUser());

		$current_user_id = intval($USER->GetID());

		if ($bExtranetEnabled)
		{
			CSocNetTools::InitGlobalExtranetArrays();
		}

		$arSearchValue = preg_split('/\s+/', trim(ToUpper($search)));
		array_walk($arSearchValue, array('CSocNetLogDestination', '__percent_walk'));

		$arMyUserId = array();

		if ($bIntranetEnabled)
		{
			$arLogicFilter = array(
				'LOGIC' => 'OR',
				'NAME' => $arSearchValue,
				'LAST_NAME' => $arSearchValue,
			);

			if (
				count($arSearchValue) == 1
				&& strlen($arSearchValue[0]) > 2
			)
			{
				$arLogicFilter['LOGIN'] = $arSearchValue[0];
			}

			$arFilter = array(
				$arLogicFilter,
				'=ACTIVE' => 'Y'
			);

			$arExternalAuthId = array();
			if (IsModuleInstalled('replica'))
			{
				$arExternalAuthId[] = 'replica';
			}
			if (IsModuleInstalled('im'))
			{
				$arExternalAuthId[] = 'bot';
			}
			if (IsModuleInstalled('imconnector'))
			{
				$arExternalAuthId[] = 'imconnector';
			}

			if (!empty($arExternalAuthId))
			{
				$arFilter['!=EXTERNAL_AUTH_ID'] = $arExternalAuthId;
			}

			if (
				($bEmailUsers || $bCrmEmailUsers)
				&& $bMailEnabled
				&& !$bEmailUsersAll
			)
			{
				$finderDestFilter = array(
					"USER_ID" => $current_user_id,
					"=CODE_TYPE" => "U",
					"=CODE_USER.EXTERNAL_AUTH_ID" => 'email'
				);
				$finderDestSelect = array(
					'CODE_USER_ID'
				);

				if ($bCrmEmailUsers)
				{
					$finderDestFilter['!=CODE_USER.UF_USER_CRM_ENTITY'] = false;
					$finderDestSelect[] = 'CODE_USER.UF_USER_CRM_ENTITY';
				}

				$rsUser = \Bitrix\Main\FinderDestTable::getList(array(
					'order' => array(),
					'filter' => $finderDestFilter,
					'group' => array("CODE_USER_ID"),
					'select' => $finderDestSelect
				));

				while ($arUser = $rsUser->fetch())
				{
					$arMyUserId[] = $arUser['CODE_USER_ID'];
				}
			}
		}
		else
		{
			$arFilter = array(
				array(
					'LOGIC' => 'OR',
					'NAME' => $arSearchValue,
					'LAST_NAME' => $arSearchValue,
				),
				'=ACTIVE' => 'Y'
			);
		}

		if (
			$bIntranetEnabled
			|| COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y"
		)
		{
			$arFilter["CONFIRM_CODE"] = false;
		}

		$bFilteredByMyUserId = false;

		if(
			$bIntranetEnabled
			&& $bExtranetEnabled
			&& !$bCrmEmailUsers
		) // consider extranet collaboration
		{
			CExtranet::fillUserListFilterORM(
				array(
					"CURRENT_USER_ID" => $current_user_id,
					"EXTRANET_USER" => $bExtranetUser,
					"INTRANET_ONLY" => ($bEmployeesOnly || ($bBitrix24Enabled && !$bExtranetEnabled)),
					"EXTRANET_ONLY" => $bExtranetOnly,
					"EMAIL_USERS_ALL" => $bEmailUsersAll,
					"MY_USERS" => $arMyUserId
				),
				$arFilter,
				$bFilteredByMyUserId
			);

			if (!$arFilter)
			{
				return $arUsers;
			}
		}

		if (
			!empty($arMyUserId)
			&& !$bFilteredByMyUserId
		)
		{
			if ($bCrmEmailUsers)
			{
				$arFilter["ID"] = $arMyUserId;
			}
			else
			{
				$arFilter[] = array(
					'LOGIC' => 'OR',
					'!=EXTERNAL_AUTH_ID' => 'email',
					'ID' => $arMyUserId,
				);
			}
		}

		$arSelect = array(
			"ID",
			"NAME",
			"LAST_NAME",
			"SECOND_NAME",
			"EMAIL",
			"LOGIN",
			"WORK_POSITION",
			"PERSONAL_PROFESSION",
			"PERSONAL_PHOTO",
			"PERSONAL_GENDER",
			"EXTERNAL_AUTH_ID",
			new \Bitrix\Main\Entity\ExpressionField('MAX_LAST_USE_DATE', 'MAX(%s)', array('\Bitrix\Main\FinderDest:CODE_USER_CURRENT.LAST_USE_DATE'))
		);

		if ($bCrmEmailUsers)
		{
			$arSelect[] = "UF_USER_CRM_ENTITY";
		}

//		$arFilter["\Bitrix\Main\FinderDest:CODE_USER_CURRENT.USER_ID"] = array(false, intval($GLOBALS["USER"]->GetID()));
		$helper = \Bitrix\Main\Application::getConnection()->getSqlHelper();
		$connection = \Bitrix\Main\Application::getConnection();
		$castType = (
			$connection instanceof \Bitrix\Main\DB\MysqlCommonConnection
				? 'UNSIGNED'
				: 'INT'
		);

		$arFilter["@ID"] = new \Bitrix\Main\DB\SqlExpression('
		(SELECT
			CAST('.$helper->quote("MAIN_USER_TMP20258").'.'.$helper->quote("ID").' AS '.$castType.') AS '.$helper->quote("ID").'
			FROM b_user '.$helper->quote("MAIN_USER_TMP20258").'
			LEFT JOIN
				b_finder_dest '.$helper->quote("TALIAS_1_TMP20258").'
				ON
					'.$helper->quote("TALIAS_1_TMP20258").'.'.$helper->quote("CODE_USER_ID").' = '.$helper->quote("MAIN_USER_TMP20258").'.'.$helper->quote("ID").'
					AND '.$helper->quote("TALIAS_1_TMP20258").'.'.$helper->quote("USER_ID").' = '.intval($USER->GetID()).'
			WHERE (
				'.$helper->quote("TALIAS_1_TMP20258").'.'.$helper->quote("USER_ID").' IS NULL
				or '.$helper->quote("TALIAS_1_TMP20258").'.'.$helper->quote("USER_ID").' in (0, '.intval($USER->GetID()).')
			)
		)');

		$rsUser = \Bitrix\Main\UserTable::getList(array(
			'order' => array(
				"\Bitrix\Main\FinderDest:CODE_USER_CURRENT.LAST_USE_DATE" => 'DESC',
				'LAST_NAME' => 'ASC'
			),
			'filter' => $arFilter,
			'select' => $arSelect,
			'limit' => 50,
			'data_doubling' => false
		));

		$queryResultCnt = 0;
		$bUseLogin = (strlen($search) > 3 && strpos($search, '@') > 0);
		while ($arUser = $rsUser->fetch())
		{
			$queryResultCnt++;
			if (
				!$bSelf
				&& $current_user_id == $arUser['ID']
			)
			{
				continue;
			}

			if (intval($departmentId) > 0)
			{
				$arUserGroupCode = CAccess::GetUserCodesArray($arUser["ID"]);

				if (!in_array("DR".intval($departmentId), $arUserGroupCode))
				{
					continue;
				}
			}

			$arUsers['U'.$arUser["ID"]] = self::formatUser($arUser, array(
				"NAME_TEMPLATE" => $nameTemplate,
				"USE_EMAIL" => $bSearchByEmail,
				"USE_LOGIN" => $bUseLogin
			));
		}

		if (
			$bEmailUsers
			&& !$queryResultCnt
			&& check_email($search, true)
		)
		{
			$arEmailFilter = array(
				'ACTIVE' => 'Y',
				'=EMAIL_OK' => 1
			);

			$rsUser = \Bitrix\Main\UserTable::getList(array(
				'order' => array(),
				'filter' => $arEmailFilter,
				'select' => array(
					"ID",
					"NAME",
					"LAST_NAME",
					"SECOND_NAME",
					"EMAIL",
					"LOGIN",
					"WORK_POSITION",
					"PERSONAL_PROFESSION",
					"PERSONAL_PHOTO",
					"PERSONAL_GENDER",
					"EXTERNAL_AUTH_ID",
					new \Bitrix\Main\Entity\ExpressionField('EMAIL_OK', 'CASE WHEN UPPER(%s) = "'.$DB->ForSql(strtoupper(str_replace('%', '%%', $search))).'" THEN 1 ELSE 0 END', 'EMAIL')
				),
				'limit' => 10
			));

			while ($arUser = $rsUser->fetch())
			{
				$arUsers['U'.$arUser["ID"]] = self::formatUser($arUser, array(
					"NAME_TEMPLATE" => $nameTemplate,
					"USE_EMAIL" => true
				));
			}
		}

		return $arUsers;
	}

	public static function SearchCrmEntities($arParams)
	{
		$result = array();

		$search = (!empty($arParams['SEARCH']) ? $arParams['SEARCH'] : false);
		if (
			$search
			&& CModule::IncludeModule('crm')
		)
		{
			if (check_email($search, true))
			{
				$result = array();

				$dbRes = CCrmContact::GetListEx(
					array(),
					array(
						'CHECK_PERMISSIONS' => 'Y',
						'FM' => array(
							'EMAIL' => array(
								'VALUE' => $search
							)
						)
					),
					false,
					false,
					array('ID', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'PHOTO')
				);
				while($ar = $dbRes->Fetch())
				{
					$formatted = self::formatCrmEmailEntity($ar, array(
						'TYPE' => 'CONTACT',
						'NAME_TEMPLATE' => $arParams["NAME_TEMPLATE"],
						'EMAIL' => $search
					));
					if (!empty($formatted))
					{
						$result[$formatted['id']] = $formatted;
					}
				}

				$dbRes = CCrmCompany::GetListEx(
					array(),
					array(
						'CHECK_PERMISSIONS' => 'Y',
						'FM' => array(
							'EMAIL' => array(
								'VALUE' => $search
							)
						)
					),
					false,
					false,
					array('ID', 'TITLE', 'LOGO')
				);
				while($ar = $dbRes->Fetch())
				{
					$formatted = self::formatCrmEmailEntity($ar, array(
						'TYPE' => 'COMPANY',
						'EMAIL' => $search
					));
					if (!empty($formatted))
					{
						$result[$formatted['id']] = $formatted;
					}
				}

				$dbRes = CCrmLead::GetListEx(
					array(),
					array(
						'CHECK_PERMISSIONS' => 'Y',
						'FM' => array(
							'EMAIL' => array(
								'VALUE' => $search
							)
						)
					),
					false,
					false,
					array('ID', 'TITLE', 'NAME', 'SECOND_NAME', 'LAST_NAME')
				);
				while($ar = $dbRes->Fetch())
				{
					$formatted = self::formatCrmEmailEntity($ar, array(
						'TYPE' => 'LEAD',
						'EMAIL' => $search
					));
					if (!empty($formatted))
					{
						$result[$formatted['id']] = $formatted;
					}
				}
			}
			else
			{
				$contacts = CCrmActivity::FindContactCommunications($search, 'EMAIL', 50);
				if (!empty($contacts))
				{
					$arId = $arEmail = array();
					foreach($contacts as $contact)
					{
						$arEmail[intval($contact["ENTITY_ID"])] = $contact["VALUE"];
						$arId[] = intval($contact["ENTITY_ID"]);
					}

					$dbRes = CCrmContact::GetListEx(
						array(),
						array(
							'CHECK_PERMISSIONS' => 'Y',
							'ID' => $arId
						),
						false,
						array('nTopCount' => 10),
						array('ID', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'PHOTO')
					);
					while($ar = $dbRes->fetch())
					{
						$formatted = self::formatCrmEmailEntity($ar, array(
							'TYPE' => 'CONTACT',
							'NAME_TEMPLATE' => $arParams["NAME_TEMPLATE"],
							'EMAIL' => $arEmail[$ar['ID']]
						));
						if (!empty($formatted))
						{
							$result[$formatted['id']] = $formatted;
						}
					}
				}

				$companies = CCrmActivity::FindCompanyCommunications($search, 'EMAIL', 50);
				if (!empty($companies))
				{
					$arId = $arEmail = array();
					foreach($companies as $company)
					{
						$arEmail[intval($company["ENTITY_ID"])] = $company["VALUE"];
						$arId[] = intval($company["ENTITY_ID"]);
					}

					$dbRes = CCrmCompany::GetListEx(
						array(),
						array(
							'CHECK_PERMISSIONS' => 'Y',
							'ID' => $arId
						),
						false,
						array('nTopCount' => 10),
						array('ID', 'TITLE', 'LOGO')
					);
					while($ar = $dbRes->Fetch())
					{
						$formatted = self::formatCrmEmailEntity($ar, array(
							'TYPE' => 'COMPANY',
							'EMAIL' => $arEmail[$ar['ID']]
						));
						if (!empty($formatted))
						{
							$result[$formatted['id']] = $formatted;
						}
					}
				}

				$leads = CCrmActivity::FindLeadCommunications($search, 'EMAIL', 50);
				if (!empty($leads))
				{
					$arId = $arEmail = array();
					foreach($leads as $lead)
					{
						$arEmail[intval($lead["ENTITY_ID"])] = $lead["VALUE"];
						$arId[] = intval($lead["ENTITY_ID"]);
					}

					$dbRes = CCrmLead::GetListEx(
						array(),
						array(
							'CHECK_PERMISSIONS' => 'Y',
							'ID' => $arId
						),
						false,
						array('nTopCount' => 10),
						array('ID', 'TITLE', 'NAME', 'SECOND_NAME', 'LAST_NAME')
					);
					while($ar = $dbRes->Fetch())
					{
						$formatted = self::formatCrmEmailEntity($ar, array(
							'TYPE' => 'LEAD',
							'EMAIL' => $arEmail[$ar['ID']]
						));
						if (!empty($formatted))
						{
							$result[$formatted['id']] = $formatted;
						}
					}
				}
			}
		}

		return $result;
	}

	public static function GetSocnetGroup($arParams = Array())
	{
		global $USER;

		$userId = intval($USER->GetID());

		$arSocnetGroups = array();
		$arSelect = Array();
		if (isset($arParams['id']))
		{
			if (empty($arParams['id']))
			{
				return $arSocnetGroups;
			}
			else
			{
				foreach ($arParams['id'] as $value)
				{
					$arSelect[] = intval($value);
				}
			}
		}

		$siteId = (
			isset($arParams['site_id'])
			&& strlen($arParams['site_id']) > 0
				? $arParams['site_id']
				: SITE_ID
		);

		$arFilter = array(
			"USER_ID" => $userId,
			"ID" => $arSelect,
			"<=ROLE" => SONET_ROLES_USER,
			"GROUP_SITE_ID" => $siteId,
			"GROUP_ACTIVE" => "Y"
		);

		if(isset($arParams['GROUP_CLOSED']))
		{
			$arFilter['GROUP_CLOSED'] = $arParams['GROUP_CLOSED'];
		}

		$arSocnetGroupsTmp = array();
		$rsGroups = CSocNetUserToGroup::GetList(
			array("GROUP_NAME" => "ASC"),
			$arFilter,
			false,
			array("nTopCount" => 500),
			array("ID", "GROUP_ID", "GROUP_NAME", "GROUP_DESCRIPTION", "GROUP_IMAGE_ID")
		);
		while($arGroup = $rsGroups->Fetch())
		{
			$arGroupTmp = array(
				"id" => $arGroup["GROUP_ID"],
				"entityId" => $arGroup["GROUP_ID"],
				"name" => htmlspecialcharsbx($arGroup["GROUP_NAME"]),
				"desc" => htmlspecialcharsbx($arGroup["GROUP_DESCRIPTION"])
			);
			if($arGroup["GROUP_IMAGE_ID"])
			{
				$imageFile = CFile::GetFileArray($arGroup["GROUP_IMAGE_ID"]);
				if ($imageFile !== false)
				{
					$arFileTmp = CFile::ResizeImageGet(
						$imageFile,
						array(
							"width" => (intval($arParams["THUMBNAIL_SIZE_WIDTH"]) > 0 ? $arParams["THUMBNAIL_SIZE_WIDTH"] : 30),
							"height" => (intval($arParams["THUMBNAIL_SIZE_HEIGHT"]) > 0 ? $arParams["THUMBNAIL_SIZE_HEIGHT"] : 30)
						),
						BX_RESIZE_IMAGE_PROPORTIONAL,
						false
					);
					$arGroupTmp["avatar"] = $arFileTmp["src"];
				}
			}
			$arSocnetGroupsTmp[$arGroupTmp['id']] = $arGroupTmp;
		}
		if (isset($arParams['features']) && !empty($arParams['features']))
		{
			self::GetSocnetGroupFilteredByFeaturePerms($arSocnetGroupsTmp, $arParams['features']);
		}

		foreach ($arSocnetGroupsTmp as $value)
		{
			$value['id'] = 'SG'.$value['id'];
			$arSocnetGroups[$value['id']] = $value;
		}

		return $arSocnetGroups;
	}

	public static function GetTreeList($id, $relation, $compat = false)
	{
		if ($compat)
		{
			$tmp = array();
			foreach($relation as $iid => $rel)
			{
				$p = $rel["parent"];
				if (!isset($tmp[$p]))
				{
					$tmp[$p] = array();
				}
				$tmp[$p][] = $iid;
			}
			$relation = $tmp;
		}

		$arRelations = Array();
		if (is_array($relation[$id]))
		{
			foreach ($relation[$id] as $relId)
			{
				$arItems = Array();
				if (
					isset($relation[$relId])
					&& !empty($relation[$relId])
				)
				{
					$arItems = self::GetTreeList($relId, $relation);
				}

				$arRelations[$relId] = Array('id'=>$relId, 'type' => 'category', 'items' => $arItems);
			}
		}

		return $arRelations;
	}

	private static function GetSocnetGroupFilteredByFeaturePerms(&$arGroups, $arFeaturePerms)
	{
		$arGroupsIDs = array();
		foreach($arGroups as $value)
		{
			$arGroupsIDs[] = $value["id"];
		}

		if (sizeof($arGroupsIDs) > 0)
		{
			$feature = $arFeaturePerms[0];
			$operations = $arFeaturePerms[1];
			if (!is_array($operations))
			{
				$operations = explode(",", $operations);
			}
			$arGroupsPerms = array();
			foreach($operations as $operation)
			{
				$tmpOps = CSocNetFeaturesPerms::CurrentUserCanPerformOperation(SONET_ENTITY_GROUP, $arGroupsIDs, $feature, $operation);
				if (is_array($tmpOps))
				{
					foreach ($tmpOps as $key=>$val)
					{
						if (!$arGroupsPerms[$key])
						{
							$arGroupsPerms[$key] = $val;
						}
					}
				}
			}
			$arGroupsActive = CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arGroupsIDs, $arFeaturePerms[0]);
			foreach ($arGroups as $key=>$group)
			{
				if (
					!$arGroupsActive[$group["id"]]
					|| !$arGroupsPerms[$group["id"]]
				)
				{
					unset($arGroups[$key]);
				}
			}
		}
	}

	public static function GetDestinationUsers($arCodes, $bFetchUsers = false)
	{
		global $DB;
		$arUsers = array();

		$arCodes2 = array();
		if (!$bFetchUsers)
		{
			foreach($arCodes as $code)
			{
				if (substr($code, 0, 1) === 'U' && $code !== 'UA')
				{
					$id = intVal(substr($code, 1));
					if($id > 0)
					{
						$arUsers[] = $id;
						continue;
					}
				}

				if (substr($code, 0, 2) === 'SG')
				{
					$arCodes2[] = $code.'_K';
				}
				$arCodes2[] = $code;
			}
			$bUnique = count($arCodes2) > 0 && count($arUsers) > 0;
		}
		else
		{
			foreach($arCodes as $code)
			{
				if (substr($code, 0, 2) === 'SG')
				{
					$arCodes2[] = $code.'_K';
				}
				$arCodes2[] = $code;
			}
			$bUnique = false;
		}

		$obUserFieldsSql = new CUserTypeSQL();
		$obUserFieldsSql->SetEntity("USER", "USER_ID");
		$obUserFieldsSql->SetFilter(array(
			"!UF_DEPARTMENT" => false
		));

		$where = $obUserFieldsSql->GetFilter();
		$join = $obUserFieldsSql->GetJoin("UA.USER_ID");

		$strCodes = in_array('UA', $arCodes2) ? "'G2'" : "'".join("','", $arCodes2)."'";

		if ($bFetchUsers)
		{
			$strSql = "SELECT DISTINCT UA.USER_ID, U.LOGIN, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.EMAIL, U.PERSONAL_PHOTO, U.WORK_POSITION ".
				"FROM b_user_access UA
				INNER JOIN b_user U ON (U.ID=UA.USER_ID)".
				$join.
				" WHERE ACCESS_CODE in (".$strCodes.") AND ".$where;
		}
		else
		{
			$strSql = "SELECT DISTINCT USER_ID ".
				"FROM b_user_access UA ".
				$join.
				" WHERE ACCESS_CODE in (".$strCodes.") AND ".$where;
		}

		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if ($bFetchUsers)
		{
			while($ar = $res->Fetch())
			{
				if ($ar > 0)
				{
					$arUsers[] = $ar;
				}
			}
		}
		else
		{
			while($ar = $res->Fetch())
			{
				if($ar['USER_ID'] > 0)
				{
					$arUsers[] = $ar['USER_ID'];
				}
			}
		}

		if ($bUnique)
		{
			$arUsers = array_unique($arUsers);
		}

		return $arUsers;
	}

	public static function GetDestinationSort($arParams = array())
	{
		global $USER;

		$arResult = array();

		$userId = (
			isset($arParams["USER_ID"])
			&& intval($arParams["USER_ID"]) > 0
				? intval($arParams["USER_ID"])
				: false
		);

		$arContextFilter = (
			isset($arParams["CONTEXT_FILTER"])
			&& is_array($arParams["CONTEXT_FILTER"])
				? $arParams["CONTEXT_FILTER"]
				: false
		);

		$arCodeFilter = (
			isset($arParams["CODE_FILTER"])
				? $arParams["CODE_FILTER"]
				: false
		);

		if (
			$arCodeFilter
			&& !is_array($arCodeFilter)
		)
		{
			$arCodeFilter = array($arCodeFilter);
		}

		if (!$userId)
		{
			if ($USER->IsAuthorized())
			{
				$userId = $USER->GetId();
			}
			else
			{
				return $arResult;
			}
		}

		$cacheTtl = defined("BX_COMP_MANAGED_CACHE") ? 3153600 : 3600*4;
		$cacheId = 'dest_sort'.$userId.serialize($arParams);
		$cacheDir = '/sonet/log_dest_sort/'.intval($userId / 100);

		$obCache = new CPHPCache;
		if($obCache->InitCache($cacheTtl, $cacheId, $cacheDir))
		{
			$arDestAll = $obCache->GetVars();
		}
		else
		{
			$obCache->StartDataCache();
			$arFilter = array(
				"USER_ID" => $USER->GetId()
			);

			if (
				IsModuleInstalled('mail')
				&& (
					!isset($arParams["ALLOW_EMAIL_INVITATION"])
					|| !$arParams["ALLOW_EMAIL_INVITATION"]
				)
			)
			{
				$arFilter["!=CODE_USER.EXTERNAL_AUTH_ID"] = 'email';
			}

			if (!empty($arParams["CODE_TYPE"]))
			{
				$arFilter["=CODE_TYPE"] = strtoupper($arParams["CODE_TYPE"]);
			}
			elseif (
				!empty($arParams["DEST_CONTEXT"])
				&& strtoupper($arParams["DEST_CONTEXT"]) != 'CRM_POST'
			)
			{
				$arFilter["!=CODE_TYPE"] = "CRM";
			}

			if (
				is_array($arContextFilter)
				&& !empty($arContextFilter)
			)
			{
				$arFilter["CONTEXT"] = $arContextFilter;
			}

			if (
				is_array($arCodeFilter)
				&& !empty($arCodeFilter)
			)
			{
				$arFilter["CODE"] = $arCodeFilter;
			}

			$arRuntime = array();
			$arOrder = array();

			if (!empty($arParams["DEST_CONTEXT"]))
			{
				$conn = \Bitrix\Main\Application::getConnection();
				$helper = $conn->getSqlHelper();

				$arRuntime = array(
					new \Bitrix\Main\Entity\ExpressionField('CONTEXT_SORT', "CASE WHEN CONTEXT = '".$helper->forSql($arParams["DEST_CONTEXT"])."' THEN 1 ELSE 0 END")
				);

				$arOrder = array(
					'CONTEXT_SORT' => 'DESC'
				);
			}

			$arOrder['LAST_USE_DATE'] = 'DESC';

			$rsDest = \Bitrix\Main\FinderDestTable::getList(array(
				'order' => $arOrder,
				'filter' => $arFilter,
				'group' => array("USER_ID"),
				'select' => array(
					'CONTEXT',
					'CODE',
					'LAST_USE_DATE'
				),
				'runtime' => $arRuntime
			));

			$arDestAll = array();

			while($arDest = $rsDest->Fetch())
			{
				$arDest["LAST_USE_DATE"] = MakeTimeStamp($arDest["LAST_USE_DATE"]->toString());
				$arDestAll[] = $arDest;
			}
			$obCache->EndDataCache($arDestAll);
		}

		foreach ($arDestAll as $arDest)
		{
			if(!isset($arResult[$arDest["CODE"]]))
			{
				$arResult[$arDest["CODE"]] = array();
			}

			$contextType = (
				isset($arParams["DEST_CONTEXT"])
				&& $arParams["DEST_CONTEXT"] == $arDest["CONTEXT"]
					? "Y"
					: "N"
			);

			if (
				$contextType == "Y"
				|| !isset($arResult[$arDest["CODE"]]["N"])
				|| $arDest["LAST_USE_DATE"] > $arResult[$arDest["CODE"]]["N"]
			)
			{
				$arResult[$arDest["CODE"]][$contextType] = $arDest["LAST_USE_DATE"];
			}
		}

		return $arResult;
	}

	private function CompareDestinations($a, $b)
	{
		if(!is_array($a) && !is_array($b))
		{
			return 0;
		}
		elseif(is_array($a) && !is_array($b))
		{
			return -1;
		}
		elseif(!is_array($a) && is_array($b))
		{
			return 1;
		}
		else
		{
			if(isset($a["SORT"]["Y"]) && !isset($b["SORT"]["Y"]))
			{
				return -1;
			}
			elseif(!isset($a["SORT"]["Y"]) && isset($b["SORT"]["Y"]))
			{
				return 1;
			}
			elseif(isset($a["SORT"]["Y"]) && isset($b["SORT"]["Y"]))
			{
				if(intval($a["SORT"]["Y"]) > intval($b["SORT"]["Y"]))
				{
					return -1;
				}
				elseif(intval($a["SORT"]["Y"]) < intval($b["SORT"]["Y"]))
				{
					return 1;
				}
				else
				{
					return 0;
				}
			}
			else
			{
				if(intval($a["SORT"]["N"]) > intval($b["SORT"]["N"]))
				{
					return -1;
				}
				elseif(intval($a["SORT"]["N"]) < intval($b["SORT"]["N"]))
				{
					return 1;
				}
				else
				{
					return 0;
				}
			}

			return 0;
		}

		return 0;
	}

	public static function SortDestinations(&$arAllDest, $arSort)
	{
		foreach($arAllDest as $type => $arLastDest)
		{
			if (is_array($arLastDest))
			{
				foreach($arLastDest as $key => $value)
				{
					if (isset($arSort[$key]))
					{
						$arAllDest[$type][$key] = array(
							"VALUE" => $value,
							"SORT" => $arSort[$key]
						);
					}
				}

				uasort($arAllDest[$type], array(self, 'CompareDestinations'));
			}
		}

		foreach($arAllDest as $type => $arLastDest)
		{
			if (is_array($arLastDest))
			{
				foreach($arLastDest as $key => $val)
				{
					if (is_array($val))
					{
						$arAllDest[$type][$key] = $val["VALUE"];
					}
				}
			}
		}
	}

	public static function fillLastDestination($arDestinationSort, &$arLastDestination, $arParams = array())
	{
		$iUCounter = $iSGCounter = $iDCounter = 0;
		$iCRMContactCounter = $iCRMCompanyCounter = $iCRMDealCounter = $iCRMLeadCounter = 0;
		$bCrm = (
			is_array($arParams)
			&& isset($arParams["CRM"])
			&& $arParams["CRM"] == "Y"
		);
		$bCrmEmails = (
			is_array($arParams)
			&& isset($arParams["CRMEMAILS"])
			&& $arParams["CRMEMAILS"] == "Y"
		);
		if (is_array($arDestinationSort))
		{
			foreach ($arDestinationSort as $code => $sortInfo)
			{
				if (
					(
						!$bCrm
						|| $bCrmEmails
					)
					&& preg_match('/^U(\d+)$/i', $code, $matches))
				{
					if ($iUCounter >= 11)
					{
						break;
					}
					if (!isset($arLastDestination['USERS']))
					{
						$arLastDestination['USERS'] = array();
					}
					$arLastDestination['USERS'][$code] = $code;
					$iUCounter++;
				}
				elseif (
					!$bCrm
					&& preg_match('/^SG(\d+)$/i', $code, $matches)
				)
				{
					if ($iSGCounter >= 6)
					{
						break;
					}
					if (!isset($arLastDestination['SONETGROUPS']))
					{
						$arLastDestination['SONETGROUPS'] = array();
					}
					$arLastDestination['SONETGROUPS'][$code] = $code;
					$iSGCounter++;
				}
				elseif (
					!$bCrm
					&& (
						preg_match('/^D(\d+)$/i', $code, $matches)
						|| preg_match('/^DR(\d+)$/i', $code, $matches)
					)
				)
				{
					if ($iDCounter >= 6)
					{
						break;
					}
					if (!isset($arLastDestination['DEPARTMENT']))
					{
						$arLastDestination['DEPARTMENT'] = array();
					}
					$arLastDestination['DEPARTMENT'][$code] = $code;
					$iDCounter++;
				}
				elseif (
					$bCrm
					&& preg_match('/^CRMCONTACT(\d+)$/i', $code, $matches)
				)
				{
					if ($iCRMContactCounter >= 6)
					{
						break;
					}
					if (!isset($arLastDestination['CONTACTS']))
					{
						$arLastDestination['CONTACTS'] = array();
					}
					$arLastDestination['CONTACTS'][$code] = $code;
					$iCRMContactCounter++;
				}
				elseif (
					$bCrm
					&& preg_match('/^CRMCOMPANY(\d+)$/i', $code, $matches)
				)
				{
					if ($iCRMCompanyCounter >= 6)
					{
						break;
					}
					if (!isset($arLastDestination['COMPANIES']))
					{
						$arLastDestination['COMPANIES'] = array();
					}
					$arLastDestination['COMPANIES'][$code] = $code;
					$iCRMCompanyCounter++;
				}
				elseif (
					$bCrm
					&& preg_match('/^CRMDEAL(\d+)$/i', $code, $matches)
				)
				{
					if ($iCRMDealCounter >= 6)
					{
						break;
					}
					if (!isset($arLastDestination['DEALS']))
					{
						$arLastDestination['DEALS'] = array();
					}
					$arLastDestination['DEALS'][$code] = $code;
					$iCRMDealCounter++;
				}
				elseif (
					$bCrm
					&& preg_match('/^CRMLEAD(\d+)$/i', $code, $matches)
				)
				{
					if ($iCRMLeadCounter >= 6)
					{
						break;
					}
					if (!isset($arLastDestination['LEADS']))
					{
						$arLastDestination['LEADS'] = array();
					}
					$arLastDestination['LEADS'][$code] = $code;
					$iCRMLeadCounter++;
				}
			}
		}
	}

	public static function fillEmails(&$arDest)
	{
		$arDest["EMAILS"] = array();
		$arDest["LAST"]["EMAILS"] = array();

		if (
			!empty($arDest)
			&& !empty($arDest["LAST"])
			&& !empty($arDest["LAST"]["USERS"])
			&& !empty($arDest["USERS"])
		)
		{
			foreach($arDest["LAST"]["USERS"] as $key => $value)
			{
				if (
					isset($arDest["USERS"][$key])
					&& is_array($arDest["USERS"][$key])
					&& isset($arDest["USERS"][$key]["isEmail"])
					&& $arDest["USERS"][$key]["isEmail"] == "Y"
				)
				{
					$arDest["EMAILS"][$key] = $arDest["USERS"][$key];
					$arDest["LAST"]["EMAILS"][$key] = $value;
				}
			}
		}
	}

	public static function fillCrmEmails(&$arDest)
	{
		$arDest["CRMEMAILS"] = array();
		$arDest["LAST"]["CRMEMAILS"] = array();

		if (
			!empty($arDest)
			&& !empty($arDest["LAST"])
			&& !empty($arDest["LAST"]["USERS"])
			&& !empty($arDest["USERS"])
		)
		{
			foreach($arDest["LAST"]["USERS"] as $key => $value)
			{
				if (
					isset($arDest["USERS"][$key])
					&& is_array($arDest["USERS"][$key])
					&& isset($arDest["USERS"][$key]["isCrmEmail"])
					&& $arDest["USERS"][$key]["isCrmEmail"] == "Y"
				)
				{
					$arDest["CRMEMAILS"][$key] = $arDest["USERS"][$key];
					$arDest["LAST"]["CRMEMAILS"][$key] = $value;
				}
			}
		}
	}

	public static function GetUsersAll($arParams)
	{
		global $DB, $USER;

		static $arFields = array(
			"ID" => Array("FIELD" => "U.ID", "TYPE" => "int"),
			"ACTIVE" => Array("FIELD" => "U.ACTIVE", "TYPE" => "string"),
			"NAME" => Array("FIELD" => "U.NAME", "TYPE" => "string"),
			"LAST_NAME" => Array("FIELD" => "U.LAST_NAME", "TYPE" => "string"),
			"SECOND_NAME" => Array("FIELD" => "U.SECOND_NAME", "TYPE" => "string"),
			"LOGIN" => Array("FIELD" => "U.LOGIN", "TYPE" => "string"),
			"PERSONAL_PHOTO" => Array("FIELD" => "U.PERSONAL_PHOTO", "TYPE" => "int"),
			"WORK_POSITION" => Array("FIELD" => "U.WORK_POSITION", "TYPE" => "string"),
			"CONFIRM_CODE" =>  Array("FIELD" => "U.CONFIRM_CODE", "TYPE" => "string"),
			"PERSONAL_PROFESSION" => Array("FIELD" => "U.PERSONAL_PROFESSION", "TYPE" => "string")
		);

		$currentUserId = $USER->GetId();
		$extranetUserIdList = \Bitrix\Socialnetwork\ComponentHelper::getExtranetUserIdList();

		if (!$currentUserId)
		{
			return array();
		}

		$bExtranetEnabled = CModule::IncludeModule("extranet");

		$bExtranetUser = (
			$bExtranetEnabled
			&& !CExtranet::IsIntranetUser()
		);

		$bExtranetWorkgroupsAllowed = (
			$bExtranetEnabled
			&& CExtranet::WorkgroupsAllowed()
		);

		$bShowAllContactsAllowed = (
			$bExtranetEnabled
			&& CExtranet::ShowAllContactsAllowed()
		);

		$rsData = CUserTypeEntity::GetList(
			array("ID" => "ASC"),
			array(
				"FIELD_NAME" => "UF_DEPARTMENT",
				"ENTITY_ID" => "USER"
			)
		);
		if($arRes = $rsData->Fetch())
		{
			$UFId = intval($arRes["ID"]);
		}
		else
		{
			return array();
		}

		if (
			$bExtranetUser
			&& !$bExtranetWorkgroupsAllowed
		) // limited extranet
		{
			return false;
		}

		$arOrder = array("ID" => "ASC");
		$arFilter = Array('ACTIVE' => 'Y');

		if (
			IsModuleInstalled("intranet")
			|| COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y"
		)
		{
			$arFilter["CONFIRM_CODE"] = false;
		}

		$arGroupBy = false;
		$arSelectFields = array("ID", "NAME", "LAST_NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION");

		$arSqls = CSocNetGroup::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);
		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "DISTINCT", $arSqls["SELECT"]);
		$strJoin = false;

		if ($bExtranetEnabled)
		{
			if ($bExtranetWorkgroupsAllowed)
			{
				if (!$bExtranetUser)
				{
					$strJoin = "
						LEFT JOIN b_utm_user UM ON UM.VALUE_ID = U.ID and FIELD_ID = ".intval($UFId)."
					";

					if (!$bShowAllContactsAllowed)
					{
						$strJoin .= "
							LEFT JOIN b_sonet_user2group UG ON UG.USER_ID = U.ID
							LEFT JOIN b_sonet_user2group UG_MY ON UG_MY.GROUP_ID = UG.GROUP_ID AND UG_MY.USER_ID = ".intval($currentUserId)."
						";
					}

					$arSqls["WHERE"] .= (strlen($arSqls["WHERE"]) > 0 ? " AND " : "")."
					(
						UM.VALUE_ID > 0
						".(!$bShowAllContactsAllowed ? " OR UG_MY.ID IS NOT NULL " : "")."
					)";
				}
				else
				{
					$strJoin = "
						INNER JOIN b_sonet_user2group UG ON UG.USER_ID = U.ID
						INNER JOIN b_sonet_user2group UG_MY ON UG_MY.GROUP_ID = UG.GROUP_ID AND UG_MY.USER_ID = ".intval($currentUserId)."
					";
				}
			}
			elseif (!$bShowAllContactsAllowed) // limited extranet, only for intranet users, don't show extranet
			{
				$strJoin = "LEFT JOIN b_utm_user UM ON UM.VALUE_ID = U.ID and FIELD_ID = ".intval($UFId);
				$arSqls["WHERE"] .= (strlen($arSqls["WHERE"]) > 0 ? " AND " : "")."UM.VALUE_ID > 0";
			}
		}

		$strSql =
			"SELECT
				".$arSqls["SELECT"]."
			FROM b_user U
				".$arSqls["FROM"]." ";

		if ($strJoin)
		{
			$strSql .= $strJoin." ";
		}

		if (strlen($arSqls["WHERE"]) > 0)
		{
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		}
		if (strlen($arSqls["ORDERBY"]) > 0)
		{
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";
		}

		//echo "!1!=".htmlspecialcharsbx($strSql)."<br>";

		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$maxCount = (IsModuleInstalled('bitrix24') ? 200 : 500);
		$resultCount = 0;
		$countExceeded = false;
		$arUsers = array();

		if ($bExtranetEnabled)
		{
			CSocNetTools::InitGlobalExtranetArrays();
		}

		while ($arUser = $dbRes->GetNext())
		{
			if ($resultCount > $maxCount)
			{
				$countExceeded = true;
				break;
			}

			$sName = trim(CUser::FormatName(empty($arParams["NAME_TEMPLATE"]) ? CSite::GetNameFormat(false) : $arParams["NAME_TEMPLATE"], $arUser, true, false));

			if (empty($sName))
			{
				$sName = $arUser["~LOGIN"];
			}

			$arFileTmp = CFile::ResizeImageGet(
				$arUser["PERSONAL_PHOTO"],
				array('width' => 32, 'height' => 32),
				BX_RESIZE_IMAGE_EXACT,
				false
			);

			$arUsers['U'.$arUser["ID"]] = Array(
				'id' => 'U'.$arUser["ID"],
				'entityId' => $arUser["ID"],
				'name' => $sName,
				'avatar' => empty($arFileTmp['src'])? '': $arFileTmp['src'],
				'desc' => $arUser['WORK_POSITION'] ? $arUser['WORK_POSITION'] : ($arUser['PERSONAL_PROFESSION'] ? $arUser['PERSONAL_PROFESSION'] : '&nbsp;'),
				'isExtranet' => (in_array($arUser["ID"], $extranetUserIdList) ? "Y" : "N")
			);

			$arUsers['U'.$arUser["ID"]]['checksum'] = md5(serialize($arUsers['U'.$arUser["ID"]]));

			$resultCount++;
		}

		if ($countExceeded)
		{
			return CSocNetLogDestination::GetUsers(
				array(
					"id" => array($currentUserId)
				),
				true
			);
		}

		return $arUsers;
	}

	public static function formatUser($arUser, $arParams = array())
	{
		static $siteNameFormat = false;
		static $isIntranetInstalled = false;
		static $extranetUserIdList = false;

		if ($siteNameFormat === false)
		{
			$siteNameFormat = CSite::GetNameFormat(false);
		}

		if ($isIntranetInstalled === false)
		{
			$isIntranetInstalled = (IsModuleInstalled('intranet') ? 'Y' : 'N');
		}

		if ($extranetUserIdList === false)
		{
			$extranetUserIdList = \Bitrix\Socialnetwork\ComponentHelper::getExtranetUserIdList();
		}

		$arFileTmp = CFile::ResizeImageGet(
			$arUser["PERSONAL_PHOTO"],
			array('width' => 32, 'height' => 32),
			BX_RESIZE_IMAGE_EXACT,
			false
		);

		$arRes = array(
			'id' => 'U'.$arUser["ID"],
			'entityId' => $arUser["ID"],
			'name' => CUser::FormatName(
				(
					!empty($arParams["NAME_TEMPLATE"])
						? $arParams["NAME_TEMPLATE"]
						: $siteNameFormat
				),
				$arUser,
				true,
				true
			),
			'avatar' => (
				empty($arFileTmp['src'])
					? ''
					: $arFileTmp['src']
			),
			'desc' => (
				$arUser['WORK_POSITION']
					? $arUser['WORK_POSITION']
					: (
						$arUser['PERSONAL_PROFESSION']
							? $arUser['PERSONAL_PROFESSION']
							: '&nbsp;'
				)
			),
			'isExtranet' => (
				in_array($arUser["ID"], $extranetUserIdList)
					? "Y"
					: "N"
			),
			'isEmail' => (
				isset($arUser['EXTERNAL_AUTH_ID'])
				&& $arUser['EXTERNAL_AUTH_ID'] == 'email'
					? 'Y'
					: 'N'
			)
		);

		if (!empty($arUser["UF_USER_CRM_ENTITY"]))
		{
			$arRes['crmEntity'] = $arUser["UF_USER_CRM_ENTITY"];
		}

		if (
			(
				isset($arParams['USE_EMAIL'])
				&& $arParams['USE_EMAIL']
			)
			|| $arRes['isEmail'] == 'Y'
		)
		{
			$arRes["email"] = $arUser['EMAIL'];
			if (
				strlen($arUser["NAME"]) > 0
				|| strlen($arUser["NAME"]) > 0
			)
			{
				$arRes['showEmail'] = "Y";
			}
		}

		$checksum = md5(serialize($arRes));
		$arRes['checksum'] = $checksum;
		$arRes['login'] = (
			$isIntranetInstalled == 'Y'
			&& isset($arParams['USE_LOGIN'])
			&& $arParams['USE_LOGIN']
				? $arUser["LOGIN"]
				: ''
		);

		return $arRes;
	}

	public static function formatCrmEmailEntity($fields, $params = array())
	{
		static $siteNameFormat = false;

		$result = array();
		$userParams = array();

		if (
			is_array($params)
			&& isset($params["TYPE"])
			&& in_array($params["TYPE"], array('CONTACT', 'COMPANY', 'LEAD'))
		)
		{
			if ($siteNameFormat === false)
			{
				$siteNameFormat = (
					!empty($params["NAME_TEMPLATE"])
						? $params["NAME_TEMPLATE"]
						: CSite::GetNameFormat(false)
				);
			}

			$prefix = '';
			if ($params["TYPE"] == 'CONTACT')
			{
				$prefix = 'C_';
				$imageField = 'PHOTO';
				$userParams = array(
					'name' => $fields['NAME'],
					'lastName' => $fields['LAST_NAME']
				);
				$name = CUser::FormatName(
					$siteNameFormat,
					$fields,
					true,
					true
				);
			}
			elseif ($params["TYPE"] == 'COMPANY')
			{
				$prefix = 'CO_';
				$imageField = 'LOGO';
				$name = $fields['TITLE'];
				$userParams = array(
					'name' => '',
					'lastName' => $fields['TITLE']
				);
			}
			elseif ($params["TYPE"] == 'LEAD')
			{
				$prefix = 'L_';
				$imageField = false;
				$name = $fields['TITLE'];
				$userParams = array(
					'name' => $fields['NAME'],
					'lastName' => $fields['LAST_NAME']
				);
				$username = CUser::FormatName(
					$siteNameFormat,
					$fields,
					true,
					true
				);
				if (!empty($username))
				{
					$name .= ', '.$username;
				}
			}

			if (
				$imageField
				&& isset($fields[$imageField])
			)
			{
				$arFileTmp = CFile::ResizeImageGet(
					$fields[$imageField],
					array('width' => 32, 'height' => 32),
					BX_RESIZE_IMAGE_EXACT,
					false
				);
			}
			else
			{
				$arFileTmp = array();
			}

			$result = array(
				'id' => $prefix.$fields["ID"],
				'crmEntity' => $prefix.$fields["ID"],
				'entityId' => $fields['ID'],
				'name' => $name,
				'avatar' => (
					empty($arFileTmp['src'])
						? ''
						: $arFileTmp['src']
				),
				'desc' => (!empty($params['EMAIL']) ? $params['EMAIL'] : ''),
				'email' => (!empty($params['EMAIL']) ? $params['EMAIL'] : ''),
				'isExtranet' => 'N',
				'isEmail' => 'Y',
				'params' => $userParams
			);
		}

		return $result;
	}
}
?>