<?php
/**
 * Cobalt by MintJoomla
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mintjoomla.com/
 * @copyright Copyright (C) 2012 MintJoomla (http://www.mintjoomla.com). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('JPATH_PLATFORM') or die;

define('FORM_SEPARATOR_NONE', 0);
define('FORM_SEPARATOR_SLIDER', 1);
define('FORM_SEPARATOR_FIELDSET', 2);
define('FORM_SEPARATOR_H2', 3);

define('FORM_STYLE_CLASSIC', 1);
define('FORM_STYLE_TABLE', 2);
define('FORM_STYLE_PARAMS', 3);
jimport('joomla.form.form');

class MEFormHelper
{
	static public function checkExtension($ext)
	{
		$result = NULL;

		if(! JFolder::exists(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $ext))
		{
			switch($ext)
			{
				case 'com_mightytouch':
					$result = 'Please install <b>Mighty Touch</b> extensions';
				break;
				case '':
				break;
				case '':
				break;
				default:
					//$result = 'Please install <b>' . @$element['check'] . '</b> extensions';
			}
		}

		return JText::_($result);
	}

	static public function renderGroups($form, $groups, $defaults)
	{
		settype($groups, 'array');

// 		$out = JHtml::_('sliders.start', 'type-sliders', array(
// 			'useCookie' => 1
// 		));

		$out = '';

		foreach($groups as $group => $group_name)
		{
			$out_group = self::renderGroup($form, $defaults, $group);
			if($out_group != '')
			{
// 				$out .= JHtml::_('sliders.panel', $group_name, $group);
				$out .= $out_group;
				if($group == 'comments')
				{
					$out .= '<div id="comments-params"></div>';
				}
			}
		}

// 		$out .= JHtml::_('sliders.end');

		return $out;
	}

	static public function renderGroup($form, $defaults, $group)
	{
		$fieldsets = $form->getFieldsets($group);
		$out = '';
		foreach($fieldsets as $name => $fieldset)
		{
			//if(JText::_($fieldset->label)) $out .= "<legend>" . JText::_($fieldset->label) . "</legend>";
			$out .= self::renderFieldset($form, $name, $defaults, $group, FORM_STYLE_TABLE, 1);
		}
		return $out;
	}

	static public function renderForm($form, $defaults, $group = null, $separator = 0, $style = 1)
	{
		$fieldsets = $form->getFieldsets($group);

		$out = '';
		if($separator == FORM_SEPARATOR_SLIDER)
		{
			$pane = JPane::getInstance('Sliders');
			$out .= JHtml::_('sliders.start', 'type-sliders', array(
				'useCookie' => 1
			));
		}
		foreach($fieldsets as $name => $fieldset)
		{
			switch($separator)
			{
				case FORM_SEPARATOR_H2:
					$out .= "<h2>" . JText::_($fieldset->label) . "</h2>";
				break;
				case FORM_SEPARATOR_FIELDSET:
					$out .= sprintf('<legend>%s</legend>', JText::_($fieldset->label));
				break;

				case FORM_SEPARATOR_SLIDER:
					$out .= JHtml::_('sliders.panel', JText::_($fieldset->label), $fieldset->name);
				break;
			}
			$out .= self::renderFieldset($form, $name, $defaults, $group, $style, 0);
			switch($separator)
			{
				case FORM_SEPARATOR_FIELDSET:
					$out .= '';
				break;

				case FORM_SEPARATOR_SLIDER:
				//$out .= JHtml::_('sliders.end');
				break;
			}
		}

		if($separator == FORM_SEPARATOR_SLIDER)
		{
			$out .= JHtml::_('sliders.end');
		}

		return $out;
	}

	/**
	 *
	 * @param JForm $form form object
	 * @param string $name name of the fieldset
	 * @param string $group name of the group
	 * @param string $type may be classic, table, params
	 * @return string Raw HTML text
	 */
	static public function renderFieldset($form, $name, $defaults, $group = null, $type = 1, $title = 1)
	{
		$fieldsets = $form->getFieldsets($group);
		$fieldset = $fieldsets[$name];

		if(is_array($defaults))
		{
			$registry = new JRegistry();
			$registry->loadArray($defaults);
			$defaults = $registry;
		}

		/*else if(is_string($item->params))
		{
			$registry = new JRegistry;
			$registry->loadString($item->params);
			$defaults = $registry;
		}*/

		$fields = $form->getFieldset($name);
		$defaultGetName = empty($group) ? '%2$s' : '%s.%s';
		$doc = JFactory::getDocument();
		$doc->addStyleSheet(JURI::root() . 'libraries/mint/forms/style.css');
		$out = '';
		switch($type)
		{
			case FORM_STYLE_CLASSIC:
				if($title && $fieldset->label)
				{
					$out .= '<fieldset class="adminform whitebg"><legend>' . JText::_($fieldset->label) . '</legend>';
				}
				if(isset($fieldset->description) && ! empty($fieldset->description)) $out .= '<small class="small">' . JText::_($fieldset->description) . '</small>';
				$out .= '<ul class="adminformlist">';
				foreach($fields as $key => $field)
				{
					$out .= '<li>';
					if(! $field->hidden)
					{
						$out .= $field->label;
					}
					$out .= $form->getInput($field->fieldname, $group, $defaults->get(sprintf($defaultGetName, $group, $field->fieldname)));
					$out .= '</li>';
				}
				$out .= '</ul>';
				if($title) $out .= '</fieldset>';
			break;

			case FORM_STYLE_TABLE:
				if($title && $fieldset->label) $out .= '<legend>' . JText::_($fieldset->label) . '</legend>';
				if(isset($fieldset->description) && ! empty($fieldset->description)) $out .= '<small>' . JText::_($fieldset->description) . '</small><br /><br />';
				$out .= '<table class="table table-bordered  table-striped table-hover">';
				$i = 1;
				$hidden = array();
				foreach($fields as $key => $field)
				{
					if($field->hidden)
					{
						$hidden[] = $field;
						continue;
					}
					if($field->type == 'Caddress' || $field->type == 'Ccontacts' || $field->type == 'Clinks' || $field->type == 'Cobaltevents')
					{
						if(trim($out) == '<legend>' . JText::_($fieldset->label) . '</legend><table class="table table-bordered  table-striped table-hover">')
						{
							$out = '<legend>' . JText::_($fieldset->label) . '</legend>';
						}
						else 
						{
							$out .= '</table>';
						}
						//$out .= '<tr class="row'.$i = 1 - $i.'"><td colspan="2">';
						if($field->type != 'Cobaltevents') 
						{
							$out .= '<legend>' . $field->label . '</legend>';
							$out .= '<br />';
						}
						
						$out .= $form->getInput($field->fieldname, $group, $defaults->get(sprintf($defaultGetName, $group, $field->fieldname)));
						//$out .= '</td></tr>';
						$out .= '<br /><table class="table table-bordered  table-striped table-hover">';
					}
					else
					{
						$out .= '<tr><td>';
						if(substr($field->description, 0, 3) == 'XX_') $out .= '<img src="'.JUri::root(true).'/media/mint/icons/16/exclamation-button.png" alt="Important" class="float-end">';
						$out .= $field->label;
						$out .= '</td><td nowrap="nowrap">';
						$out .= $form->getInput($field->fieldname, $group, $defaults->get(sprintf($defaultGetName, $group, $field->fieldname)));
						$out .= '</td></tr>';
					}
				}
				$out .= '</table>';
				foreach($hidden as $field)
				{
					$out .= $field->input;
				}
			break;

			case FORM_STYLE_PARAMS:
				$hidden = array();
				if($title && $fieldset->label) $out .= '<h2 class="params-title">' . JText::_($fieldset->label) . '</h2>';
				if(isset($fieldset->description) && ! empty($fieldset->description)) $out .= '<p class="params-description">' . JText::_($fieldset->description) . '</p>';

				foreach($fields as $key => $field)
				{
					if($field->hidden)
					{
						$hidden[] = $field;
						continue;
					}
					$switch = array(
						'checkbox'
					);
					if(in_array(strtolower($field->type), $switch))
					{
						$out .= '<div class="params-line">';
						$out .= $form->getInput($field->fieldname, $group, $defaults->get(sprintf($defaultGetName, $group, $field->fieldname)));
						$out .= $field->label;
						$out .= '</div>';

					}
					else
					{
						$out .= '<div class="params-line">';
						$out .= $field->label;
						$out .= $form->getInput($field->fieldname, $group, $defaults->get(sprintf($defaultGetName, $group, $field->fieldname)));
						$out .= '</div>';
					}
				}
				foreach($hidden as $field)
				{
					$out .= $form->getInput($field->fieldname, $group, $defaults->get(sprintf($defaultGetName, $group, $field->fieldname)));
				}
				$out .= '<br />';
			break;
		}

		return $out;
	}
}