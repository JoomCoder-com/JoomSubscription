<?php
/**
 * Emerald by MintJoomla
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: http://www.mintjoomla.com/
 * @copyright Copyright (C) 2012 MintJoomla (http://www.mintjoomla.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');

JFormHelper::loadFieldClass('list');

class JFormFieldCipnurl extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Cipnurl';

	/**
	 * Method to get the field input markup fora grouped list.
	 * Multiselect is enabled by using the multiple attribute.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$id = JFactory::getApplication()->input->getInt('fid');

		if(!$id)
		{
			return JText::_('RK_SAVETOSEEURL');
		}

		return sprintf('<input type="text" readonly value="%s" >', JUri::root().'index.php?option=com_cobalt&task=pay.receivePayment&lang=en&processor='.$this->element['processor']);
	}
}