<?php
/**
 * Cobalt by MintJoomla
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mintjoomla.com/
 * @copyright Copyright (C) 2012 MintJoomla (http://www.mintjoomla.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('JPATH_PLATFORM') or die;
jimport('joomla.html.html');
jimport('joomla.form.formfield');

JFormHelper::loadFieldClass('melist');

class JFormFieldMeresourcesfields extends JFormMEFieldList
{
	public $type = 'Meresourcesfields';
	
	protected function getOptions()
	{
		
		JHtml::addIncludePath(JPATH_ROOT. DIRECTORY_SEPARATOR .'administrator'. DIRECTORY_SEPARATOR .'components'. DIRECTORY_SEPARATOR .'com_cobalt'. DIRECTORY_SEPARATOR .'library'. DIRECTORY_SEPARATOR .'php'. DIRECTORY_SEPARATOR .'html');
		$key = ( $this->element['key'] ?  $this->element['key'] : 'key');
		
		$select = ($this->multiple || $this->element['multi']) ? false : true; 
		$sections = JHtml::_('cobalt.types', $select, $this->element['filters'], $key, $this->element['client']);
		
		return $sections;
	}
}