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

class JFormFieldRemcontainers extends JFormFieldList
{
	protected $options = array();
	protected $type = 'Remcontainers';

	public function getOptions()
	{
		if(!JFolder::exists(JPATH_ROOT.'/components/com_remository'))
		{
			return array(JHtml::_('select.option', '', 'Remository is not installed'));
		}
		$db = JFactory::getDbo();
		$db->setQuery("SELECT `id`, `name`, parentid FROM `#__downloads_containers` ORDER BY `name` ASC");
		$list = $db->loadObjectList();

		foreach($list AS $container)
		{
			$out[$container->parentid][] = $container;
		}

		$levels = $this->_level($out, 0, 0);

		return $levels;
	}

	protected function _level($items, $id, $level)
	{
		static $out = array();

		if(empty($items[$id]))
		{
			return array();
		}

		foreach($items[$id] AS $item)
		{
			$out[] = JHtml::_('select.option', $item->id, str_repeat('-- ', $level).$item->name);

			if(!empty($items[$item->id]))
			{
				$this->_level($items, $item->id, $level + 1);
			}
		}

		return $out;
	}
}
