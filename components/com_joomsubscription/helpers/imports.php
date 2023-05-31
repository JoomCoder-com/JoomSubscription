<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die();

include_once JPATH_ROOT.'/components/com_joomsubscription/library/php/imports.php';

class JoomsubscriptionImportsHelper
{

	public static function createImportObject($type)
	{
		$path = JPATH_COMPONENT.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'imports'.DIRECTORY_SEPARATOR;
		include_once $path.$type.'/'.$type.'.php';
		$classname = 'JoomsubscriptionImport'.ucfirst($type);
		$obj = new $classname();

		$lang = JFactory::getLanguage();
		$tag  = $lang->getTag();
		if($tag != 'en-GB')
		{
			if(!JFile::exists(JPATH_BASE . "/language/{$tag}/{$tag}.com_joomsubscription_import_{$type}.ini"))
			{
				$tag == 'en-GB';
			}
		}
		$lang->load('com_joomsubscription_import_' . $type, JPATH_ROOT, $tag, TRUE);

		$xml = new SimpleXMLElement($path.'/'.$type.'/'.$type.'.xml', null, true);

		$obj->title = JText::_($xml->name);
		$obj->description = JText::_($xml->description);
		$obj->type = $type;

		return $obj;
	}

}
