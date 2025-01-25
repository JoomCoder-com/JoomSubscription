<?php
/**
 * Cobalt by MintJoomla
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mintjoomla.com/
 * @copyright Copyright (C) 2012 MintJoomla (http://www.mintjoomla.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

jimport('joomla.html.html');
jimport('joomla.form.formfield');
JFormHelper::loadFieldClass('melist');

class JFormFieldCsectionstypess extends JFormMEFieldList
{

	public $type = 'Csectionstypess';

	protected function getOptions()
	{
		JHtml::addIncludePath(JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_cobalt' . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'html');
		$sections = JHtml::_('cobalt.sections');
		
		$options = array();
		if($this->element['select'] == 1)
		{
			$options[] = JHTML::_('select.option', '', JText::_('- Select Section -'));
		}
		foreach($sections as $type)
		{
			$options[] = JHTML::_('select.option', $type->value, $type->text);
		}
		return $options;
	}
	protected function getInput()
	{
		$this->element['onchange'] = 'ajax_reloadTypes(\''.$this->formControl.$this->group.$this->element['type_elem_name'].'\', this.value);';
		
		$html = parent::getInput();
		
		$doc = JFactory::getDocument();
		$uri = JFactory::getURI();
		$doc->addScriptDeclaration("
			function ajax_reloadTypes(id, value)
			{
				var sel = $(id);
				new Request.HTML({
					url:'".JURI::root()."administrator/index.php?option=com_cobalt&task=ajax.loadsectiontypes&no_html=1',
					method:'post',
					autoCancel:true,
					data:{section_id: value, selected: selected_types},
					update: $(id),
		        }).send();
			}
				
			window.addEvent('domready', function(){
				ajax_reloadTypes('".$this->formControl.$this->group.$this->element['type_elem_name']."', '".$this->value."');
			});
		");
		return $html;
	}
}
?>
