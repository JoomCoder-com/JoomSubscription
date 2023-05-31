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

class JFormFieldK2cats extends JFormFieldList
{
	protected $options = array();
	protected $type = 'K2cats';

	public function getOptions()
	{
		if(!JFolder::exists(JPATH_ROOT.'/components/com_k2'))
		{
			return array(JHtml::_('select.option', '', 'K2 is not installed'));
		}
		$db = JFactory::getDbo();
		$db->setQuery("SELECT id as value, name as text, parent FROM #__k2_categories ORDER BY parent ASC, id ASC");
		$list = $db->loadObjectList();
		foreach($list AS $cat)
		{
			$cats[$cat->parent][] = $cat;
		}

		$this->_build($cats);

		return $this->options;
	}

	private function _build($cats, $id = 0, $level = 0)
	{
		foreach($cats[$id] AS $cat)
		{
			$cat->text = str_repeat('|-- ', $level).$cat->text;
			$this->options[] = $cat;
			if(!empty($cats[$cat->value]))
			{
				$this->_build($cats, $cat->value, $level + 1);
			}
		}
	}
}
