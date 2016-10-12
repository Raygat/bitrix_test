<?
namespace Sale\Handlers\Delivery\Additional\Location;

class Replacement
{
	public static function getLocalityTypes()
	{
		return array(
			'оня╗кнй цнпндяйнцн рхою' => array('оцр'),
			'оня╗кнй' => array('о', 'оня', 'оняекнй'),
			'юск' => array('юск'),
			'яекн' => array('яекн', 'C'),
			'усрнп' => array('усрнп', 'у'),
			'депебмъ' => array('депебмъ', 'д', 'деп'),
			'ярюмхжю' => array('ярюмхжю', 'яр-жю', 'ярюм'),
			'ямр' => array(),
			'дювмши оня╗кнй' => array(),
			'пюанвхи оня╗кнй' => array(),
			'мюяек╗ммши осмйр' => array(),
			'лхйпнпюинм' => array(),
			'якнандю' => array(),
			'фхкпюинм' => array(),
			'фекегмнднпнфмюъ ярюмжхъ' => array(),
			'онврнбне нрдекемхе' => array(),
			'яекэяйне оняекемхе' => array(),
			'леяревйн' => array(),
			'яекэянбер' => array()
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

	public static function getRegionExceptions()
	{
		return array(
			'всбюьхъ' => 'всбюьяйюъ',
			'лняйбю' => 'лняйнбяйюъ накюярэ',
			'яюмйр-оерепаспц' => 'кемхмцпюдяйюъ накюярэ',
			'сдлспрхъ' => 'сдлспряйюъ'
		);
	}

	public static function getDistrictTypes()
	{
		return array(
			'пюинм' => array('п-м', 'п-нм')
		);
	}

	public static function getNameRussia()
	{
		return 'пняяхъ';
	}
}