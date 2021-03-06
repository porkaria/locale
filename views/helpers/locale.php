<?php
App::import('Lib', 'Locale.Localize');
App::import('Lib', 'Locale.Formats');
/**
 * Helper to localized formatting dates, numbers and currency from databases format.
 *
 * Based on Juan Basso cake_ptbr plugin: http://github.com/jrbasso/cake_ptbr
 *
 * Code comments in brazilian portuguese.
 * -----
 * Helper para formatação localizada de datas, números decimais e valores monetários.
 *
 * Baseado no plugin cake_ptbr do Juan Basso: http://github.com/jrbasso/cake_ptbr
 *
 * PHP version > 5.2.6
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright 2009-2012, Radig - Soluções em TI, www.radig.com.br
 * @link http://www.radig.com.br
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @package radig
 * @subpackage radig.l10n.views.helpers
 */
class LocaleHelper extends AppHelper
{
	/**
	 * Configurações que sobreescrevem as definições do locale
	 *
	 * @var array
	 */
	protected $_settings = array(
		'locale' => null,
	);

	/**
	 * Configurações em uso
	 *
	 * @var array
	 */
	protected $settings = array();

	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		if(func_num_args() == 1)
			$settings = func_get_arg(0);

		if(isset($settings) && is_array($settings))
			$this->settings = array_merge($this->_settings, $settings);

		if(!empty($this->settings['locale']))
			return;

		$os = strtolower(php_uname('s'));

		if(strpos($os, 'windows') === false)
		{
			$this->settings['locale'] = substr(setlocale(LC_ALL, "0"), 0, 5);
			return;
		}

		$winLocale = explode('.', setlocale(LC_CTYPE, "0"));
		$locale = array_search($winLocale[0], Formats::$windowsLocaleMap);

		if($locale !== false)
			$this->settings['locale'] = $locale;
	}

	/**
	 *
	 * @param string $d - Uma data
	 */
	public function date($date = null)
	{
		return Localize::setLocale($this->settings['locale'])
				->date($date);
	}

	/**
	 *
	 * @param string $dateTime
	 * @param bool $seconds
	 */
	public function dateTime($dateTime, $seconds = true)
	{
		return Localize::setLocale($this->settings['locale'])
				->dateTime($dateTime, $seconds);
	}

	/**
	 *
	 * @param string $dateTime
	 * @param string $displayTime
	 * @param string $format
	 */
	public function dateLiteral($dateTime, $displayTime = false, $format = null)
	{
		return Localize::setLocale($this->settings['locale'])
				->dateLiteral($dateTime, $displayTime, $format);
	}

	/**
	 *
	 * @param number $value
	 * @return string
	 */
	public function currency($value)
	{
		return Localize::setLocale($this->settings['locale'])
				->currency($value);
	}

	/**
	 *
	 * @param number $value
	 * @param int $precision
	 * @param boolean $thousands
	 * @return number
	 */
	public function number($value, $precision = 2, $thousands = false)
	{
		if(is_numeric($value))
			$value = Localize::setLocale($this->settings['locale'])
				->number($value, $precision, $thousands);

		return $value;
	}
}
