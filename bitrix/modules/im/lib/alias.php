<?php
namespace Bitrix\Im;

class Alias
{
	const ENTITY_TYPE_USER = 'USER';
	const ENTITY_TYPE_CHAT = 'CHAT';
	const ENTITY_TYPE_OPEN_LINE = 'LINES';
	const ENTITY_TYPE_OTHER = 'OTHER';

	const CACHE_TTL = 31536000;
	const CACHE_PATH = '/bx/im/alias/';

	public static function add(array $fields)
	{
		$alias = preg_replace("/[^\.\-0-9a-zA-Z]+/", "", $fields['ALIAS']);
		$entityType = $fields['ENTITY_TYPE'];
		$entityId = $fields['ENTITY_ID'];

		if (empty($entityId) || empty($entityType) || empty($alias))
		{
			return false;
		}

		$aliasData = self::get($alias);
		if ($aliasData)
			return false;

		$result = \Bitrix\Im\Model\AliasTable::add(Array(
			'ALIAS' => $alias,
			'ENTITY_TYPE' => $entityType,
			'ENTITY_ID' => $entityId,
		));
		if (!$result->isSuccess())
		{
			return false;
		}

		return true;
	}

	public static function delete($alias)
	{
		$aliasData = self::get($alias);
		if (!$aliasData)
			return false;

		\Bitrix\Im\Model\AliasTable::delete($aliasData['ID']);

		return true;
	}

	public static function get($alias)
	{
		$alias = preg_replace("/[^\.\-0-9a-zA-Z]+/", "", $alias);
		if (empty($alias))
		{
			return false;
		}

		$orm = \Bitrix\Im\Model\AliasTable::getList(Array(
			'filter' => Array('=ALIAS' => $alias)
		));

		return $orm->fetch();
	}
}