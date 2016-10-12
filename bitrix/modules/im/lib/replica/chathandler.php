<?php
namespace Bitrix\Im\Replica;

class ChatHandler extends \Bitrix\Replica\Client\BaseHandler
{
	protected $tableName = "b_im_chat";
	protected $moduleId = "im";
	protected $className = "\\Bitrix\\Im\\Model\\ChatTable";
	protected $primary = array(
		"ID" => "auto_increment",
	);
	protected $predicates = array(
		"AUTHOR_ID" => "b_user.ID",
	);
	protected $translation = array(
		"ID" => "b_im_chat.ID",
		"AUTHOR_ID" => "b_user.ID",
		"AVATAR" => "b_file.ID",
		"LAST_MESSAGE_ID" => "b_im_message.ID",
	);
	protected $children = array(
		"ID" => "b_im_relation.CHAT_ID",
	);
	protected $fields = array(
		"TITLE" => "text",
		"DESCRIPTION" => "text",
	);

	/**
	 * Method will be invoked before new database record inserted.
	 *
	 * @param array &$newRecord All fields of inserted record.
	 *
	 * @return void
	 */
	public function beforeInsertTrigger(array &$newRecord)
	{
		unset($newRecord["DISK_FOLDER_ID"]);
	}

	/**
	 * Method will be invoked before an database record updated.
	 *
	 * @param array $oldRecord All fields before update.
	 * @param array &$newRecord All fields after update.
	 *
	 * @return void
	 */
	public function beforeUpdateTrigger(array $oldRecord, array &$newRecord)
	{
		unset($newRecord["DISK_FOLDER_ID"]);
	}

	/**
	 * Method will be invoked after an database record updated.
	 *
	 * @param array $oldRecord All fields before update.
	 * @param array $newRecord All fields after update.
	 *
	 * @return void
	 */
	public function afterUpdateTrigger(array $oldRecord, array $newRecord)
	{
		if ($oldRecord['TITLE'] !== $newRecord['TITLE'])
		{
			if (\CModule::IncludeModule("pull"))
			{
				$ar = \CIMChat::GetRelationById($newRecord['CHAT_ID']);

				$clearCacheOpen = false;
				foreach ($ar as $rel)
				{
					if ($rel['MESSAGE_TYPE'] == IM_MESSAGE_OPEN)
					{
						$clearCacheOpen = true;
					}
					else
					{
						\CIMContactList::CleanChatCache($rel['USER_ID']);
					}

					\CPullStack::AddByUser($rel['USER_ID'], Array(
						'module_id' => 'im',
						'command' => 'chatRename',
						'params' => Array(
							'chatId' => $newRecord['CHAT_ID'],
							'chatTitle' => htmlspecialcharsbx($newRecord['TITLE']),
						),
					));
				}
				if ($clearCacheOpen)
				{
					\CIMContactList::CleanAllChatCache();
				}
			}
		}
		if ($oldRecord['AVATAR'] !== $newRecord['AVATAR'])
		{
			if (\CModule::IncludeModule('pull'))
			{
				$avatarImage = \CIMChat::GetAvatarImage($newRecord['AVATAR']);
				$ar = \CIMChat::GetRelationById($newRecord['CHAT_ID']);

				$clearCacheOpen = false;
				foreach ($ar as $relation)
				{
					if ($relation['MESSAGE_TYPE'] == IM_MESSAGE_OPEN)
					{
						$clearCacheOpen = true;
					}
					else
					{
						\CIMContactList::CleanChatCache($relation['USER_ID']);
					}

					\CPullStack::AddByUser($relation['USER_ID'], Array(
						'module_id' => 'im',
						'command' => 'chatAvatar',
						'params' => Array(
							'chatId' => $newRecord['CHAT_ID'],
							'chatAvatar' => $avatarImage,
						),
					));
				}
				if ($clearCacheOpen)
				{
					\CIMContactList::CleanAllChatCache();
				}
			}
		}
		if ($oldRecord['COLOR'] !== $newRecord['COLOR'])
		{
			if (\CModule::IncludeModule('pull'))
			{
				$ar = \CIMChat::GetRelationById($newRecord['CHAT_ID']);

				$clearCacheOpen = false;
				foreach ($ar as $relation)
				{
					if ($relation['MESSAGE_TYPE'] == IM_MESSAGE_OPEN)
					{
						$clearCacheOpen = true;
					}
					else
					{
						\CIMContactList::CleanChatCache($relation['USER_ID']);
					}

					\CPullStack::AddByUser($relation['USER_ID'], Array(
						'module_id' => 'im',
						'command' => 'chatChangeColor',
						'params' => Array(
							'chatId' => $newRecord['CHAT_ID'],
							'chatColor' => \Bitrix\Im\Color::getColor($newRecord['COLOR']),
						),
					));
				}
				if ($clearCacheOpen)
				{
					\CIMContactList::CleanAllChatCache();
				}
			}
		}
	}
}
