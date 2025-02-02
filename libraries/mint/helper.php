<?php
/**
 * Cobalt by MintJoomla
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mintjoomla.com/
 *
 * @copyright Copyright (C) 2012 MintJoomla (http://www.mintjoomla.com). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

use Joomla\CMS\Filter\InputFilter;

defined('JPATH_PLATFORM') or die;

class Mint
{
	static function _($text, $default = NULL, $jsSafe = FALSE, $interpretBackSlashes = TRUE, $script = FALSE)
	{
		// Initialize the InputFilter instance
		$inputFilter = InputFilter::getInstance();

// Clean the input text by stripping tags and sanitizing it as a command
		$cleanedText = $inputFilter->clean(strip_tags($text), 'cmd');

// Convert the cleaned text to uppercase
		$key = strtoupper($cleanedText);
		$trans = JText::_($key, $jsSafe, $interpretBackSlashes, $script);

		if($key === $trans)
		{
			return $default ? $default : $text;
		}

		return $trans;
	}

	static function markdown2($val, $extra = FALSE)
	{
		if(!class_exists('MarkdownInterface'))
		{
			include_once __DIR__ . '/Markdown/Markdown.inc.php';
		}

		if($extra)
		{
			include_once __DIR__ . '/Markdown/MarkdownExtra.inc.php';

			$val = \Michelf\MarkdownExtra::defaultTransform($val);
		}
		else
		{
			$val = \Michelf\Markdown::defaultTransform($val);
		}

		return $val;
	}
	static function markdown($val, $extra = FALSE)
	{
		if(!class_exists('MarkdownInterface'))
		{
			include_once __DIR__ . '/Markdown/MarkdownExtra.inc.php';
		}

		$val = \Michelf\MarkdownExtra::defaultTransform($val);

		return $val;
	}

	static public function loadLayout($name, $path)
	{
		$options = array();
		$params  = JComponentHelper::getParams('com_emerald');

		if($params->get('tmpl_prefix'))
		{
			$options['suffixes'] = [$params->get('tmpl_prefix')];
		}

		return new JLayoutFile($name, $path, $options);
	}
}

class MintArrayHelper
{

	public static function trim_r(&$array)
	{
		if(!is_array($array))
		{
			settype($array, 'array');
		}

		foreach($array as &$val)
		{
			if(is_array($val))
			{
				$val = self::trim_r($val);
			}
			elseif(is_string($val))
			{
				$val = trim($val);
			}
		}
	}

	public static function clean_r(&$array, $zero = FALSE)
	{
		if(!is_array($array))
		{
			settype($array, 'array');
		}

		foreach($array as $k => &$val)
		{
			if(is_array($val))
			{
				self::clean_r($val);
			}
			elseif(is_string($val))
			{
				$val = trim($val);
				$val = str_replace("\r", "", $val);
			}

			if(($val != "0" && empty($val)) || ($val == "0" && $zero))
			{
				unset($array[$k]);
				continue;
			}
		}
		if(!$array)
		{
			$array = array();
		}
	}

	public static function tolower_r(&$array)
	{
		if(!is_array($array))
		{
			settype($array, 'array');
		}
		foreach($array as $k => &$val)
		{
			$val = JString::strtolower($val);
		}
		if(!$array)
		{
			$array = array();
		}
	}

	public static function separate_r(&$array, $separator = '^')
	{
		if(!is_array($array))
		{
			settype($array, 'array');
		}
		foreach($array as $k => &$val)
		{
			$val = explode($separator, $val);
			$val = $val[0];
		}
		if(!$array)
		{
			$array = array();
		}
	}
}