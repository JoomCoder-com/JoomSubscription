<?php
/**
 * @package          HikaShop for Joomla!
 * @version          2.2.2
 * @author           hikashop.com
 * @copyright    (C) 2010-2013 HIKARI SOFTWARE. All rights reserved.
 * @license          GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

class JFormFieldJsptprofile extends JFormFieldList
{

	protected $type = 'jsptprofile';

	public function getOptions()
	{
		$db = JFactory::getDbo();

		$db->setQuery("SELECT `id` as `value`, `name` as `text` FROM `#__xipt_profiletypes`");
		$options = $db->loadObjectList();

		return $options;
	}
}
