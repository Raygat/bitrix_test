<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_TITLE'),
	'SORT' => 1500,
	'CODES' => array(
		'DATE_INSERT' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_DATE'),
			'SORT' => 100,
			'GROUP' => 'PAYMENT',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_DATE_DESC'),
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'DATE_BILL_DATE',
				'PROVIDER_KEY' => 'PAYMENT'
			)
		),
		'DATE_PAY_BEFORE' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_PAY_BEFORE'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_PAY_BEFORE_DESC'),
			'SORT' => 200,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'DATE_PAY_BEFORE',
				'PROVIDER_KEY' => 'PAYMENT'
			)
		),
		'SELLER_COMPANY_NAME' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_SUPPLI'),
			'GROUP' => 'SELLER_COMPANY',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_SUPPLI_DESC'),
			'SORT' => 300
		),
		'SELLER_COMPANY_ADDRESS' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_ADRESS_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_ADRESS_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 400
		),
		'SELLER_COMPANY_PHONE' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_PHONE_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_PHONE_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 500
		),
		'SELLER_COMPANY_BANK_NAME' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_BANK_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_BANK_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 600,
		),
		'SELLER_COMPANY_BANK_ACCOUNT' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_BANK_ACCNO_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_BANK_ACCNO_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 700,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_BILL_LA_BANK_ACCNO_SUPPLI_VAL'),
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'SELLER_COMPANY_BANK_ADDR' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_BANK_ADDR_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_BANK_ADDR_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 800,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_BILL_LA_BANK_ADDR_SUPPLI_VAL'),
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'SELLER_COMPANY_BANK_PHONE' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_BANK_PHONE_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_BANK_PHONE_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 900,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_BILL_LA_BANK_PHONE_SUPPLI_VAL'),
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'SELLER_COMPANY_BANK_ACCOUNT_CORR' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_BANK_ROUTENO_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_BANK_ROUTENO_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 1000,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_BILL_LA_BANK_ROUTENO_SUPPLI_VAL'),
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'SELLER_COMPANY_BANK_SWIFT' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_BANK_SWIFT_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_BANK_SWIFT_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 1100,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_BILL_LA_BANK_SWIFT_SUPPLI_VAL'),
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'SELLER_COMPANY_DIRECTOR_POSITION' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_DIR_POS_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_DIR_POS_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 1200,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_BILL_LA_DIR_POS_SUPPLI_VAL'),
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'SELLER_COMPANY_ACCOUNTANT_POSITION' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_ACC_POS_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_ACC_POS_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 1300,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_BILL_LA_ACC_POS_SUPPLI_VAL'),
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'SELLER_COMPANY_DIRECTOR_NAME' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_DIR_SUPPLI'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_DIR_SUPPLI_DESC'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 1400
		),
		'SELLER_COMPANY_ACCOUNTANT_NAME' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_ACC_SUPPLI'),
			'GROUP' => 'SELLER_COMPANY',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_ACC_SUPPLI_DESC')
		),

		'BUYER_PERSON_COMPANY_NAME' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_CUSTOMER'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_CUSTOMER_DESC'),
			'SORT' => 1500,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'COMPANY_NAME',
				'PROVIDER_KEY' => 'PROPERTY'
			)
		),
		'BUYER_PERSON_COMPANY_ADDRESS' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_CUSTOMER_ADRES'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_CUSTOMER_ADRES_DESC'),
			'SORT' => 1600,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'ADDRESS',
				'PROVIDER_KEY' => 'PROPERTY'
			)
		),
		'BUYER_PERSON_COMPANY_PHONE' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_CUSTOMER_PHONE'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_CUSTOMER_PHONE_DESC'),
			'SORT' => 1700,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'PHONE',
				'PROVIDER_KEY' => 'PROPERTY'
			)
		),
		'BUYER_PERSON_COMPANY_FAX' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_CUSTOMER_FAX'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_CUSTOMER_FAX_DESC'),
			'SORT' => 1800,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'FAX',
				'PROVIDER_KEY' => 'PROPERTY'
			)
		),
		'BUYER_PERSON_COMPANY_NAME_CONTACT' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_CUSTOMER_PERSON'),
			'GROUP' => 'BUYER_PERSON_COMPANY',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_CUSTOMER_PERSON_DESC'),
			'SORT' => 1900,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => 'PAYER_NAME',
				'PROVIDER_KEY' => 'PROPERTY'
			)
		),
		'BILLLA_COMMENT1' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_COMMENT1'),
			'GROUP' => 'CONNECT_SETTINGS_BILLLA',
			'SORT' => 2000,
			'DEFAULT' => array(
				'PROVIDER_VALUE' => Loc::getMessage('SALE_HPS_BILL_LA_COMMENT1_VALUE'),
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'BILLLA_COMMENT2' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_COMMENT2'),
			'GROUP' => 'CONNECT_SETTINGS_BILLLA',
			'SORT' => 2100,
		),
		'BILLLA_PATH_TO_LOGO' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_LOGO'),
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_LOGO_DESC'),
			'SORT' => 2200,
			'GROUP' => 'CONNECT_SETTINGS_BILLLA',
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		'BILLLA_LOGO_DPI' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_LOGO_DPI'),
			'SORT' => 2300,
			'GROUP' => 'CONNECT_SETTINGS_BILLLA',
			'TYPE' => 'SELECT',
			'INPUT' => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'96' => Loc::getMessage('SALE_HPS_BILL_LA_LOGO_DPI_96'),
					'600' => Loc::getMessage('SALE_HPS_BILL_LA_LOGO_DPI_600'),
					'300' => Loc::getMessage('SALE_HPS_BILL_LA_LOGO_DPI_300'),
					'150' => Loc::getMessage('SALE_HPS_BILL_LA_LOGO_DPI_150'),
					'72' => Loc::getMessage('SALE_HPS_BILL_LA_LOGO_DPI_72')
				)
			)
		),
		'BILLLA_PATH_TO_STAMP' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_PRINT'),
			'SORT' => 2400,
			'GROUP' => 'CONNECT_SETTINGS_BILLLA',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_PRINT_DESC'),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		'SELLER_COMPANY_DIR_SIGN' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_DIR_SIGN_SUPPLI'),
			'SORT' => 2500,
			'GROUP' => 'SELLER_COMPANY',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_DIR_SIGN_SUPPLI_DESC'),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		'SELLER_COMPANY_ACC_SIGN' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_ACC_SIGN_SUPPLI'),
			'GROUP' => 'SELLER_COMPANY',
			'SORT' => 2600,
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_ACC_SIGN_SUPPLI_DESC'),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		'BILLLA_BACKGROUND' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_BACKGROUND'),
			'SORT' => 2700,
			'GROUP' => 'CONNECT_SETTINGS_BILLLA',
			'DESCRIPTION' => Loc::getMessage('SALE_HPS_BILL_LA_BACKGROUND_DESC'),
			'INPUT' => array(
				'TYPE' => 'FILE'
			)
		),
		'BILLLA_BACKGROUND_STYLE' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_BACKGROUND_STYLE'),
			'SORT' => 2800,
			'TYPE' => 'SELECT',
			'GROUP' => 'CONNECT_SETTINGS_BILLLA',
			'INPUT' => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'tile' => Loc::getMessage('SALE_HPS_BILL_LA_BACKGROUND_STYLE_TILE'),
					'stretch' => Loc::getMessage('SALE_HPS_BILL_LA_BACKGROUND_STYLE_STRETCH')
				)
			)
		),
		'BILLLA_MARGIN_TOP' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_MARGIN_TOP'),
			'SORT' => 2900,
			'GROUP' => 'CONNECT_SETTINGS_BILLLA',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => '15',
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'BILLLA_MARGIN_RIGHT' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_MARGIN_RIGHT'),
			'SORT' => 3000,
			'GROUP' => 'CONNECT_SETTINGS_BILLLA',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => '15',
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'BILLLA_MARGIN_BOTTOM' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_MARGIN_BOTTOM'),
			'SORT' => 3100,
			'GROUP' => 'CONNECT_SETTINGS_BILLLA',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => '15',
				'PROVIDER_KEY' => 'VALUE'
			)
		),
		'BILLLA_MARGIN_LEFT' => array(
			'NAME' => Loc::getMessage('SALE_HPS_BILL_LA_MARGIN_LEFT'),
			'SORT' => 3200,
			'GROUP' => 'CONNECT_SETTINGS_BILLLA',
			'DEFAULT' => array(
				'PROVIDER_VALUE' => '20',
				'PROVIDER_KEY' => 'VALUE'
			)
		)
	)
);