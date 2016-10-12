<?php
namespace Bitrix\Im;

class User
{
	private static $instance = Array();
	private $userId = 0;
	private $userData = null;

	function __construct($userId = null)
	{
		global $USER;

		$this->userId = (int)$userId;
		if ($this->userId <= 0 && is_object($USER) && $USER->GetID() > 0)
		{
			$this->userId = (int)$USER->GetID();
		}
	}

	/**
	 * @param null $userId
	 * @return User
	 */
	public static function getInstance($userId = null)
	{
		global $USER;

		$userId = (int)$userId;
		if ($userId <= 0 && is_object($USER) && $USER->GetID() > 0)
		{
			$userId = (int)$USER->GetID();
		}

		if (!isset(self::$instance[$userId]))
		{
			self::$instance[$userId] = new self($userId);
		}

		return self::$instance[$userId];
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->userId;
	}

	/**
	 * @return string
	 */
	public function getFullName()
	{
		$fields = $this->getFields();

		return $fields? $fields['name']: '';
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		$fields = $this->getFields();

		return $fields? $fields['firstName']: '';
	}

	/**
	 * @return string
	 */
	public function getLastName()
	{
		$fields = $this->getFields();

		return $fields? $fields['lastName']: '';
	}

	/**
	 * @return string
	 */
	public function getAvatar()
	{
		$fields = $this->getFields();

		return $fields? $fields['avatar']: '';
	}

	/**
	 * @return string
	 */
	public function getWorkPosition()
	{
		$fields = $this->getFields();

		return $fields? $fields['workPosition']: '';
	}

	/**
	 * @return string
	 */
	public function getGender()
	{
		$fields = $this->getFields();

		return $fields? $fields['gender']: '';
	}

	/**
	 * @return string
	 */
	public function getColor()
	{
		$fields = $this->getFields();

		return $fields? $fields['color']: '';
	}

	/**
	 * @return bool
	 */
	public function isExtranet()
	{
		$fields = $this->getFields();

		return $fields? (bool)$fields['extranet']: null;
	}

	/**
	 * @return bool
	 */
	public function isNetwork()
	{
		$fields = $this->getFields();

		return $fields? (bool)$fields['network']: null;
	}

	/**
	 * @return bool
	 */
	public function isBot()
	{
		$fields = $this->getFields();

		return $fields? (bool)$fields['bot']: null;
	}

	/**
	 * @return bool
	 */
	public function isConnector()
	{
		$fields = $this->getFields();

		return $fields? (bool)$fields['connector']: null;
	}

	/**
	 * @return bool
	 */
	public function isExists()
	{
		$fields = $this->getFields();

		return $fields? true: false;
	}

	/**
	 * @return array|null
	 */
	public function getFields()
	{
		$params = $this->getParams();

		return $params? $params['user']: null;
	}

	/**
	 * @return array|null
	 */
	private function getParams()
	{
		if (is_null($this->userData))
		{
			$userData = \CIMContactList::GetUserData(Array(
				'ID' => self::getId(),
				'PHONES' => 'Y'
			));
			if (isset($userData['users'][self::getId()]))
			{
				$this->userData['user'] = $userData['users'][self::getId()];
			}
		}
		return $this->userData;
	}

	/**
	 * @return bool
	 */
	public function updateParams()
	{
		$this->userData = null;

		return true;
	}
}