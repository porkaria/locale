<?php
App::import('Lib', 'Locale.LocaleException');
App::import('Lib', 'Locale.Formats');
App::import('Lib', 'Locale.Utils');
/**
 * Class to "unlocalize" special data like dates, timestamps and numbers
 * to US/ISO format.
 *
 * PHP version > 5.2.4
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright 2009-2012, Radig - Soluções em TI, www.radig.com.br
 * @link http://www.radig.com.br
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package Radig
 * @subpackage Radig.Locale.Libs
 */
class Unlocalize
{
	/**
	 * Current locale for input data
	 *
	 * @var string
	 */
	static public $currentLocale = 'pt_BR';

	/**
	 * Current instance
	 *
	 * @var Localize
	 */
	private static $_Instance = null;

	/**
	 * Singleton implementation
	 *
	 * @return Localize
	 */
	public static function getInstance()
	{
		if(self::$_Instance === null)
			self::$_Instance = new self;

		return self::$_Instance;
	}

	/**
	 * Set locale of input data
	 *
	 * @param string $locale Name of locale, the same format of setlocale php function
	 *
	 * @return Localize Current instance of that class, for chaining methods
	 */
	static public function setLocale($locale)
	{
		if(!setlocale(LC_ALL, array($locale . '.utf-8', $locale, Formats::$windowsLocaleMap[$locale])))
			throw new LocaleException("Locale {$locale} não disponível no seu sistema.");

		if(!isset(Formats::$input[$locale]))
			throw new LocaleException("Localização '{$locale}' não possuí formatação definida. Tente adicionar o formato antes de usa-lo.");

		self::$currentLocale = $locale;

		return self::getInstance();
	}

	/**
	 * Wrapper to Formats::addInput.
	 *
	 * @param string $locale Name of locale, the same format of setlocale php function
	 * @param string $format An array like in the description
	 *
	 * @return Localize Current instance of that class, for chaining methods
	 */
	static public function addFormat($locale, $format)
	{
		Formats::addInput($locale, $format);

		return self::getInstance();
	}

	/**
	 * Convert a localized date/timestamp to USA format date/timestamp
	 *
	 * @param string $value Your localized date
	 * @param bool $includeTime If the input date include time info
	 *
	 * @return mixed a string formatted date on Success, original Date on failure or null case
	 * date is null equivalent
	 */
	static public function date($value, $includeTime = false)
	{
		if(!isset(Formats::$input[self::$currentLocale]))
			throw new LocaleException('Localização não reconhecida pela Lib Localize. Tente adicionar o formato antes de usa-lo.');

		if(Utils::isNullDate($value))
			return null;

		$iso = $value;
		if(!Utils::isISODate($value))
		{
			if(!$includeTime)
			{
				$currentFormat = Formats::$input[self::$currentLocale]['date'];
				$slices = $currentFormat['slices'];
				$final = "\${$slices['y']}-\${$slices['m']}-\${$slices['d']}";
			}
			else
			{
				$currentFormat = Formats::$input[self::$currentLocale]['timestamp'];
				$slices = $currentFormat['slices'];
				$final = "\${$slices['y']}-\${$slices['m']}-\${$slices['d']} \${$slices['h']}:\${$slices['i']}:\${$slices['s']}";
			}

			if(preg_match($currentFormat['pattern'], $value) === 0)
				throw new LocaleException('Data inválida para localização');

			$iso = preg_replace($currentFormat['pattern'], $final, $value);
		}

		return self::normalizeDate($iso);
	}

	/**
	 * Convert a localized decimal/float to USA numeric
	 * format
	 *
	 * @param mixed $value A integer, float, double or numeric string input
	 *
	 * @return string $value
	 */
	static public function decimal($value)
	{
		if(empty($value))
			return $value;

		$currentFormat = localeconv();

		$v = (string)$value;

		$integer = $v;
		$decimal = 0;

		$decimalPoint = strrpos($v, $currentFormat['decimal_point']);
		if($decimalPoint !== false)
		{
			$decimal = substr($v, $decimalPoint + 1);

			$integer = substr($v, 0, $decimalPoint);
			$integer = str_replace(array($currentFormat['thousands_sep'], $currentFormat['mon_thousands_sep']), '', $integer);
		}

		$value = $integer;
		if($decimal > 0)
			$value .= '.' . $decimal;

		return $value;
	}

	/**
	 * Normalize a Date string.
	 *
	 * 1987-3-1 => 1987-03-01
	 * 87-3-1 => 1987-03-01
	 * 09-12-1 => 2009-12-01
	 * 29-02-1 => 2029-02-01
	 * 31-02-1 => 2031-02-01
	 *
	 * @param string $value Date string in US format
	 *
	 * @return string normalized date
	 */
	static public function normalizeDate($value)
	{
		$date = $value;

		if(strpos($value, ' ') !== false)
			list($date, $time) = explode(' ', $value);

		$date = explode('-', $date);

		if($date[0] < 99)
		{
			if($date[0] > 30)
				$date[0] = '19' . $date[0];
			else
				$date[0] = '20' . $date[0];
		}

		$date[1] = str_pad($date[1], 2, '0', STR_PAD_LEFT);
		$date[2] = str_pad($date[2], 2, '0', STR_PAD_LEFT);

		$value = implode('-', $date);

		if(isset($time))
			$value .= ' ' . $time;

		return $value;
	}
}