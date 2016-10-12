<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Type\Date,
	\Bitrix\Main\HttpApplication;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
	die();

Loc::loadMessages(__FILE__);

class ImRouterComponent extends \CBitrixComponent
{
	/** @var HttpRequest $request */
	protected $request = array();
	protected $errors = array();
	protected $aliasData = array();

	private function showFullscreenChat()
	{
		$this->includeComponentTemplate();
	}

	private function showLiveChat()
	{
		define('SKIP_TEMPLATE_AUTH_ERROR', true);
		$this->arResult['REQUEST'] = '<pre>'.print_r($_GET, 1).'</pre>';
		$this->arResult['REQUEST'] .= '<pre>'.print_r($this->aliasData, 1).'</pre>';

		if ($this->request->get('iframe') == 'Y')
		{
			global $APPLICATION;
			$APPLICATION->restartBuffer();

			$this->setTemplateName("livechat.iframe");
			$this->includeComponentTemplate();
			\CMain::finalActions();
			die();
		}
		else
		{
			$this->setTemplateName("livechat");
			$this->includeComponentTemplate();
		}

		return true;
	}

	public function executeComponent()
	{
		if (!$this->checkModules())
		{
			$this->showErrors();
			return;
		}

		$this->request = \Bitrix\Main\Context::getCurrent()->getRequest();

		if ($this->request->get('alias'))
		{
			$this->aliasData = \Bitrix\Im\Alias::get($this->request->get('alias'));
			if ($this->aliasData['ENTITY_TYPE'] == \Bitrix\Im\Alias::ENTITY_TYPE_OPEN_LINE)
			{
				$this->showLiveChat();
			}
			else
			{
				LocalRedirect('/');
			}
		}
		else
		{
			global $USER;
			if ($USER->IsAuthorized())
			{
				$this->showFullscreenChat();
			}
			else
			{
				LocalRedirect('/');
			}
		}
	}

	protected function checkModules()
	{
		if(!Loader::includeModule('im'))
		{
			$this->errors[] = Loc::getMessage('IM_COMPONENT_MODULE_NOT_INSTALLED');
			return false;
		}
		return true;
	}

	protected function hasErrors()
	{
		return (count($this->errors) > 0);
	}

	protected function showErrors()
	{
		if(count($this->errors) <= 0)
		{
			return;
		}

		foreach($this->errors as $error)
		{
			ShowError($error);
		}
	}
}