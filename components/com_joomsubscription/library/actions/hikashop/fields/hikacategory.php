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

class JFormFieldHikacategory extends JFormFieldList
{

	protected $type = 'Hikacategory';

	public function getOptions()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(TRUE)
			->select('a.category_id as value, a.category_name AS text, a.category_depth as level')
			->from('#__hikashop_category AS a')
			->where('a.category_depth > 1')
			->where('a.category_published = 1')
			->where("a.category_type = 'product'")
			->order('a.category_left');

		$db->setQuery($query);
		$items = $db->loadObjectList();

		foreach($items as &$item)
		{
			$item->text = str_repeat('- ', $item->level - 2) . $item->text;
		}

		//array_unshift($items, JHtml::_('select.option', 0, JText::_('EM_SELECT_CATEGORY')));

		return $items;
	}
}
