<?php
namespace Bitrix\Im\Model;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class BotTable
 *
 * Fields:
 * <ul>
 * <li> BOT_ID int mandatory
 * <li> MODULE_ID int mandatory
 * <li> TO_CLASS string(255) optional
 * <li> TO_METHOD string(255) optional
 * </ul>
 *
 * @package Bitrix\Im
 **/

class BotTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_bot';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'BOT_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('BOT_ENTITY_BOT_ID_FIELD'),
			),
			'MODULE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('BOT_ENTITY_MODULE_ID_FIELD'),
			),
			'CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateBotName'),
				'title' => Loc::getMessage('BOT_ENTITY_BOT_NAME_FIELD'),
			),
			'TYPE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateBotType'),
				'title' => Loc::getMessage('BOT_ENTITY_BOT_TYPE_FIELD'),
				'default_value' => 'B',
			),
			'CLASS' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateToClass'),
				'title' => Loc::getMessage('BOT_ENTITY_TO_CLASS_FIELD'),
			),
			'METHOD_BOT_DELETE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateToMethod'),
				'title' => Loc::getMessage('BOT_ENTITY_METHOD_BOT_DELETE_FIELD'),
			),
			'METHOD_MESSAGE_ADD' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateToMethod'),
				'title' => Loc::getMessage('BOT_ENTITY_METHOD_MESSAGE_ADD_FIELD'),
			),
			'METHOD_WELCOME_MESSAGE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateToMethod'),
				'title' => Loc::getMessage('BOT_ENTITY_METHOD_WELCOME_MESSAGE_FIELD'),
			),
			'COUNT_MESSAGE' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('BOT_ENTITY_COUNT_MESSAGE_FIELD'),
			),
			'COUNT_COMMAND' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('BOT_ENTITY_COUNT_COMMAND_FIELD'),
			),
			'COUNT_CHAT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('BOT_ENTITY_COUNT_CHAT_FIELD'),
			),
			'COUNT_USER' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('BOT_ENTITY_COUNT_USER_FIELD'),
			),
			'APP_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateAppId'),
				'title' => Loc::getMessage('BOT_ENTITY_APP_ID_FIELD'),
				'default_value' => '',
			),
		);
	}
	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 */
	public static function validateBotName()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	/**
	 * Returns validators for CLASS field.
	 *
	 * @return array
	 */
	public static function validateToClass()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for METHODS field.
	 *
	 * @return array
	 */
	public static function validateToMethod()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Returns validators for APP_ID field.
	 *
	 * @return array
	 */
	public static function validateAppId()
	{
		return array(
			new  Main\Entity\Validator\Length(null, 128),
		);
	}

	/**
	 * Returns validators for TYPE field.
	 *
	 * @return array
	 */
	public static function validateBotType()
	{
		return array(
			new  Main\Entity\Validator\Length(null, 1),
		);
	}
}