<?
namespace Bitrix\Sale\Location\Comparator;

class Replacement
{
	public static function getLocalityTypes()
	{
		return array(
			'оня╗кнй цнпндяйнцн рхою' => array('оцр'),
			'оня╗кнй' => array('о', 'оня', 'оняекнй'),
			'юск' => array(),
			'яекн' => array('C'),
			'усрнп' => array('у'),
			'депебмъ' => array('д', 'деп'),
			'ярюмхжю' => array('яр-жю', 'ярюм'),
			'пюанвхи оня╗кнй' => array()
		);
	}

	public static function getRegionTypes()
	{
		return array(
			'накюярэ' => array('нак'),
			'юбрнмнлмши нйпсц' => array('юн', 'юбр нйпсц'),
			'пеяосакхйю' => array('пеяо')
		);
	}

	public static function getRegionVariants()
	{
		return array(
			'всбюьхъ' => 'всбюьяйюъ',
			'лняйбю' => 'лняйнбяйюъ накюярэ',
			'яюмйр-оерепаспц' => 'кемхмцпюдяйюъ накюярэ',
			'сдлспрхъ' => 'сдлспряйюъ',
			'яюую /ъйсрхъ/ пеяо' => 'пеяосакхйю яюую (ъйсрхъ)',
			'уюмрш-люмяхияйхи юбрнмнлмши нйпсц - чцпю юн' => 'уюмрш-люмяхияйхи юбрнмнлмши нйпсц',
			'ебпеияйюъ юнак' => 'ебпеияйюъ юбрнмнлмюъ накюярэ'
		);
	}

	public static function getCountryVariants()
	{
		return array(
			'пт' => 'пняяхъ',
			'пняяхияйюъ тедепюжхъ' => 'пняяхъ'
		);
	}

	public static function isCountryRussia($countryName)
	{
		return in_array(
			ToUpper(
				trim(
					$countryName
				)
			),
			array(
				'пт',
				'пняяхияйюъ тедепюжхъ',
				'пняяхъ'
			)
		);
	}

	public static function getDistrictTypes()
	{
		return array(
			'пюинм' => array('п-м', 'п-нм')
		);
	}
}