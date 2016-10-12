<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;

Loc::loadMessages(__FILE__);

class MailingChainTable extends Entity\DataManager
{

	const STATUS_NEW = 'N';
	const STATUS_SEND = 'S';
	const STATUS_PAUSE = 'P';
	const STATUS_WAIT = 'W';
	const STATUS_END = 'Y';

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_mailing_chain';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'autocomplete' => true,
				'primary' => true,
			),
			'MAILING_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'required' => true,
			),
			'POSTING_ID' => array(
				'data_type' => 'integer',
			),
			'PARENT_ID' => array(
				'data_type' => 'integer',
			),
			'CREATED_BY' => array(
				'data_type' => 'integer',
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'default_value' => new Type\DateTime(),
			),
			'STATUS' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => static::STATUS_NEW,
			),
			'REITERATE' => array(
				'data_type' => 'string',
				'default_value' => 'N',
			),
			'LAST_EXECUTED' => array(
				'data_type' => 'datetime',
			),

			'EMAIL_FROM' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SENDER_ENTITY_MAILING_CHAIN_FIELD_TITLE_EMAIL_FROM'),
				'validation' => array(__CLASS__, 'validateEmailForm'),
			),
			'SUBJECT' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SENDER_ENTITY_MAILING_CHAIN_FIELD_TITLE_SUBJECT')
			),
			'MESSAGE' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('SENDER_ENTITY_MAILING_CHAIN_FIELD_TITLE_MESSAGE')
			),

			'TEMPLATE_TYPE' => array(
				'data_type' => 'string',
			),

			'TEMPLATE_ID' => array(
				'data_type' => 'string',
			),

			'IS_TRIGGER' => array(
				'data_type' => 'string',
				'required' => true,
				'default_value' => 'N',
			),

			'TIME_SHIFT' => array(
				'data_type' => 'integer',
				'required' => true,
				'default_value' => 0,
			),

			'AUTO_SEND_TIME' => array(
				'data_type' => 'datetime',
			),

			'DAYS_OF_MONTH' => array(
				'data_type' => 'string',
			),
			'DAYS_OF_WEEK' => array(
				'data_type' => 'string',
			),
			'TIMES_OF_DAY' => array(
				'data_type' => 'string',
			),

			'PRIORITY' => array(
				'data_type' => 'string',
			),

			'LINK_PARAMS' => array(
				'data_type' => 'string',
			),

			'MAILING' => array(
				'data_type' => 'Bitrix\Sender\MailingTable',
				'reference' => array('=this.MAILING_ID' => 'ref.ID'),
			),
			'CURRENT_POSTING' => array(
				'data_type' => 'Bitrix\Sender\PostingTable',
				'reference' => array('=this.POSTING_ID' => 'ref.ID'),
			),
			'POSTING' => array(
				'data_type' => 'Bitrix\Sender\PostingTable',
				'reference' => array('=this.ID' => 'ref.MAILING_CHAIN_ID'),
			),
			'ATTACHMENT' => array(
				'data_type' => 'Bitrix\Sender\MailingAttachmentTable',
				'reference' => array('=this.ID' => 'ref.CHAIN_ID'),
			),
			'CREATED_BY_USER' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array('=this.CREATED_BY' => 'ref.ID'),
			),
		);
	}

	/**
	 * Returns validators for EMAIL_FROM field.
	 *
	 * @return array
	 */
	public static function validateEmailForm()
	{
		return array(
			new Entity\Validator\Length(null, 50),
			array(__CLASS__, 'checkEmail')
		);
	}

	/**
	 * @return mixed
	 */
	public static function checkEmail($value)
	{
		if(empty($value) || check_email($value))
			return true;
		else
			return Loc::getMessage('SENDER_ENTITY_MAILING_CHAIN_VALID_EMAIL_FROM');
	}

	/**
	 * @param $mailingChainId
	 * @return int|null
	 */
	public static function initPosting($mailingChainId)
	{
		$postingId = null;
		$chainPrimary = array('ID' => $mailingChainId);
		$arMailingChain = static::getRowById($chainPrimary);
		if($arMailingChain)
		{
			$needAddPosting = true;

			if(!empty($arMailingChain['POSTING_ID']))
			{
				$arPosting = PostingTable::getRowById(array('ID' => $arMailingChain['POSTING_ID']));
				if($arPosting)
				{
					if($arPosting['STATUS'] == PostingTable::STATUS_NEW)
					{
						$postingId = $arMailingChain['POSTING_ID'];
						$needAddPosting = false;
					}
					/*
					elseif($arMailingChain['IS_TRIGGER'] == 'Y')
					{
						$postingId = $arMailingChain['POSTING_ID'];
						$needAddPosting = false;
					}
					*/
				}
			}

			if($needAddPosting)
			{
				$postingAddDb = PostingTable::add(array(
					'MAILING_ID' => $arMailingChain['MAILING_ID'],
					'MAILING_CHAIN_ID' => $arMailingChain['ID'],
				));
				if ($postingAddDb->isSuccess())
				{
					$postingId = $postingAddDb->getId();
					static::update($chainPrimary, array('POSTING_ID' => $postingId));
				}
			}

			if($postingId && $arMailingChain['IS_TRIGGER'] != 'Y')
				PostingTable::initGroupRecipients($postingId);
		}

		return $postingId;
	}


	/**
	 * @param Entity\Event $event
	 * @return Entity\EventResult
	 */
	public static function onAfterAdd(Entity\Event $event)
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();

		if(!isset($data['fields']['IS_TRIGGER']) || $data['fields']['IS_TRIGGER'] != 'Y')
		{
			static::initPosting($data['primary']['ID']);
		}

		if(array_key_exists('STATUS', $data['fields']) || array_key_exists('AUTO_SEND_TIME', $data['fields']))
		{
			MailingManager::actualizeAgent(null, $data['primary']['ID']);
		}

		if(isset($data['fields']['PARENT_ID']))
		{
			TriggerManager::actualizeHandlerForChild();
		}

		return $result;
	}

	/**
	 * @param Entity\Event $event
	 * @return Entity\EventResult
	 */
	public static function onAfterUpdate(Entity\Event $event)
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();
		if(array_key_exists('STATUS', $data['fields']) || array_key_exists('AUTO_SEND_TIME', $data['fields']))
		{
			if(array_key_exists('STATUS', $data['fields']) && $data['fields']['STATUS'] == static::STATUS_NEW)
				static::initPosting($data['primary']['ID']);

			MailingManager::actualizeAgent(null, $data['primary']['ID']);
		}

		if(isset($data['fields']['PARENT_ID']))
		{
			TriggerManager::actualizeHandlerForChild();
		}

		return $result;
	}

	/**
	 * @param Entity\Event $event
	 * @return Entity\EventResult
	 */
	public static function onDelete(Entity\Event $event)
	{
		$result = new Entity\EventResult;
		$data = $event->getParameters();

		$deleteIdList = array();
		if(!empty($data['primary']))
		{
			$itemDb = static::getList(array(
				'select' => array('ID'),
				'filter' => $data['primary']
			));
			while($item = $itemDb->fetch())
			{
				$deleteIdList[] = $item['ID'];
			}
		}

		foreach($deleteIdList as $chainId)
		{
			MailingAttachmentTable::delete(array('CHAIN_ID' => $chainId));
			MailingTriggerTable::delete(array('MAILING_CHAIN_ID' => $chainId));
			PostingTable::delete(array('MAILING_CHAIN_ID' => $chainId));
		}

		return $result;
	}

	/**
	 * @param Entity\Event $event
	 * @return Entity\EventResult
	 */
	public static function onAfterDelete(Entity\Event $event)
	{
		TriggerManager::actualizeHandlerForChild();
	}

	/**
	 * @param $id
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function isReadyToSend($id)
	{
		$mailingChainDb = static::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=ID' => $id,
				'=MAILING.ACTIVE' => 'Y',
				'=STATUS' => array(static::STATUS_NEW, static::STATUS_PAUSE),
			),
		));
		$mailingChain = $mailingChainDb->fetch();

		return !empty($mailingChain);
	}

	/**
	 * @param $id
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function isManualSentPartly($id)
	{
		$mailingChainDb = static::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=ID' => $id,
				'=MAILING.ACTIVE' => 'Y',
				'=AUTO_SEND_TIME' => null,
				'!REITERATE' => 'Y',
				'=STATUS' => array(static::STATUS_SEND),
			),
		));
		$mailingChain = $mailingChainDb->fetch();

		return !empty($mailingChain);
	}

	/**
 * Return true if chain will auto send.
 *
 * @param $id
 * @return bool
 * @throws \Bitrix\Main\ArgumentException
 */
	public static function isAutoSend($id)
	{
		$mailingChainDb = static::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=ID' => $id,
				'!AUTO_SEND_TIME' => null,
				'!REITERATE' => 'Y',
			),
		));
		$mailingChain = $mailingChainDb->fetch();

		return !empty($mailingChain);
	}

	/**
	 * Return true if chain can resend mails to recipients who have error sending
	 *
	 * @param $id
	 * @return bool
	 */
	public static function canReSendErrorRecipients($id)
	{
		$mailingChainDb = static::getList(array(
			'select' => array('POSTING_ID'),
			'filter' => array(
				'=ID' => $id,
				'!REITERATE' => 'Y',
				'!POSTING_ID' => null,
				'=STATUS' => static::STATUS_END,
			),
		));
		if($mailingChain = $mailingChainDb->fetch())
		{
			$errorRecipientDb = PostingRecipientTable::getList(array(
				'select' => array('ID'),
				'filter' => array(
					'=POSTING_ID' => $mailingChain['POSTING_ID'],
					'=STATUS' => PostingRecipientTable::SEND_RESULT_ERROR
				),
				'limit' => 1
			));
			if($errorRecipientDb->fetch())
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Change status of recipients and mailing chain for resending mails to recipients who have error sending
	 *
	 * @param $id
	 * @return void
	 */
	public static function prepareReSendErrorRecipients($id)
	{
		if(!static::canReSendErrorRecipients($id))
		{
			return;
		}

		$mailingChain = static::getRowById(array('ID' => $id));
		$updateSql = 'UPDATE ' . PostingRecipientTable::getTableName() .
			" SET STATUS='" . PostingRecipientTable::SEND_RESULT_NONE . "'" .
			" WHERE POSTING_ID=" . intval($mailingChain['POSTING_ID']) .
			" AND STATUS='" . PostingRecipientTable::SEND_RESULT_ERROR . "'";
		\Bitrix\Main\Application::getConnection()->query($updateSql);
		PostingTable::update(array('ID' => $mailingChain['POSTING_ID']), array('STATUS' => PostingTable::STATUS_PART));
		static::update(array('ID' => $id), array('STATUS' => static::STATUS_SEND));
	}

	/**
	 * @param $mailingId
	 */
	public static function setStatusNew($mailingId)
	{
		static::update(array('MAILING_ID' => $mailingId), array('STATUS' => static::STATUS_NEW));
	}

	/**
	 * @return array
	 */
	public static function getStatusList()
	{
		return array(
			self::STATUS_NEW => Loc::getMessage('SENDER_CHAIN_STATUS_N'),
			self::STATUS_SEND => Loc::getMessage('SENDER_CHAIN_STATUS_S'),
			self::STATUS_PAUSE => Loc::getMessage('SENDER_CHAIN_STATUS_P'),
			self::STATUS_WAIT => Loc::getMessage('SENDER_CHAIN_STATUS_W'),
			self::STATUS_END => Loc::getMessage('SENDER_CHAIN_STATUS_Y'),
		);
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getDefaultEmailFromList()
	{
		$addressFromList = array();
		$siteEmailDb = \Bitrix\Main\SiteTable::getList(array('select'=>array('EMAIL')));
		while($siteEmail = $siteEmailDb->fetch())
		{
			$addressFromList[] = $siteEmail['EMAIL'];
		}

		try
		{
			$mainEmail = \COption::GetOptionString('main', 'email_from');
			if (!empty($mainEmail))
				$addressFromList[] = $mainEmail;

			$saleEmail = \COption::GetOptionString('sale', 'order_email');
			if(!empty($saleEmail))
				$addressFromList[] = $saleEmail;

			$addressFromList = array_unique($addressFromList);
			trimArr($addressFromList, true);

		}
		catch(\Exception $e)
		{

		}

		return $addressFromList;
	}

	/**
	 * @return array
	 */
	public static function getEmailFromList()
	{
		$addressFromList = static::getDefaultEmailFromList();
		$email = \COption::GetOptionString('sender', 'address_from');
		if(!empty($email))
		{
			$arEmail = explode(',', $email);
			$addressFromList = array_merge($arEmail, $addressFromList);
			$addressFromList = array_unique($addressFromList);
			trimArr($addressFromList, true);
		}

		return $addressFromList;
	}

	/**
	 * @param $email
	 */
	public static function setEmailFromToList($email)
	{
		$emailList = \COption::GetOptionString('sender', 'address_from');
		if(!empty($email))
		{
			$addressFromList = explode(',', $emailList);
			$addressFromList = array_merge(array($email), $addressFromList);
			$addressFromList = array_unique($addressFromList);
			trimArr($addressFromList, true);
			\COption::SetOptionString('sender', 'address_from', implode(',', $addressFromList));
		}
	}

	/**
	 * @return array
	 */
	public static function getEmailToMeList()
	{
		$addressToList = array();
		$email = \COption::GetOptionString('sender', 'address_send_to_me');
		if(!empty($email))
		{
			$addressToList = explode(',', $email);
			$addressToList = array_unique($addressToList);
			trimArr($addressToList, true);
		}

		return $addressToList;
	}

	/**
	 * @param $email
	 */
	public static function setEmailToMeList($email)
	{
		$emailList = \COption::GetOptionString('sender', 'address_send_to_me');
		if(!empty($email))
		{
			$addressToList = explode(',', $emailList);
			$addressToList = array_merge(array($email), $addressToList);
			$addressToList = array_unique($addressToList);
			trimArr($addressToList, true);
			\COption::SetOptionString('sender', 'address_send_to_me', implode(',', $addressToList));
		}
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function onPresetTemplateList($templateType = null, $templateId = null)
	{
		$resultList = array();

		if($templateType && $templateType !== 'MAILING')
		{
			return $resultList;
		}

		$filter = array();
		if($templateId)
		{
			$filter['ID'] = $templateId;
		}
		$templateDb = static::getList(array(
			'select' => array('ID', 'SUBJECT', 'MESSAGE'),
			'filter' => $filter,
			'order' => array('DATE_INSERT' => 'DESC'),
			'limit' => 15
		));
		while($template = $templateDb->fetch())
		{
			$resultList[] = array(
				'TYPE' => 'MAILING',
				'ID' => $template['ID'],
				'NAME' => $template['SUBJECT'],
				'ICON' => '',
				'HTML' => $template['MESSAGE']
			);
		}

		return $resultList;
	}
}

class MailingAttachmentTable extends Entity\DataManager
{

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_mailing_attachment';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'CHAIN_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
			'FILE_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
			),
		);
	}

}