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

class JFormFieldHikaproduct extends JFormFieldList
{

	protected $type = 'Hikaproduct';

	public function getOptions()
	{
		$options = array();

		$db = JFactory::getDbo();

		$db->setQuery("SELECT product_id, category_id FROM #__hikashop_product_category");
		$product_cats = $db->loadAssocList('product_id', 'category_id');

		$db->setQuery("SELECT category_id, category_name FROM #__hikashop_category");
		$cats = $db->loadAssocList('category_id', 'category_name');

		$db->setQuery("SELECT product_id, product_name FROM #__hikashop_product WHERE product_published = 1");
		$products = $db->loadObjectList();

		foreach($products AS $product)
		{
			$options[$cats[$product_cats[$product->product_id]]][] = JHtml::_('select.option', $product->product_id, $product->product_name);
		}

		return $options;
	}

	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string)$this->element['class'] . '"' : '';
		$attr .= ((string)$this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int)$this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string)$this->element['onchange'] . '"' : '';

		$this->extend = isset($this->element['extend']);
		// Get the field groups.
		$groups = (array)$this->getOptions();

		// Create a read-only list (no name) with a hidden input to store the value.
		if((string)$this->element['readonly'] == 'true')
		{
			$html[] = JHtml::_('select.groupedlist', $groups, NULL,
				array(
					 'list.attr'          => $attr,
					 'id'                 => $this->id,
					 'list.select'        => $this->value,
					 'group.items'        => NULL,
					 'option.key.toHtml'  => FALSE,
					 'option.text.toHtml' => FALSE
				));
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
		}
		// Create a regular list.
		else
		{
			$html[] = JHtml::_('select.groupedlist', $groups, $this->name,
				array(
					 'list.attr'          => $attr,
					 'id'                 => $this->id,
					 'list.select'        => $this->value,
					 'group.items'        => NULL,
					 'option.key.toHtml'  => FALSE,
					 'option.text.toHtml' => FALSE
				));
		}

		return implode($html);
	}
}
