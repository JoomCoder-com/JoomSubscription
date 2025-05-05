<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('JPATH_PLATFORM') or die;

define('EM_SEPARATOR_NONE', 0);
define('EM_SEPARATOR_SLIDER', 1);
define('EM_SEPARATOR_FIELDSET', 2);
define('EM_SEPARATOR_H2', 3);

define('EM_STYLE_CLASSIC', 1);
define('EM_STYLE_TABLE', 2);
define('EM_STYLE_PARAMS', 3);
jimport('joomla.form.form');

class EMFormHelper
{
	static public function renderGroups($form, $groups, $defaults)
	{
		settype($groups, 'array');


		$out = '';

		foreach($groups as $group => $group_name)
		{
			$out_group = self::renderGroup($form, $defaults, $group);
			if($out_group != '')
			{
				$out .= $out_group;
				if($group == 'comments')
				{
					$out .= '<div id="comments-params"></div>';
				}
			}
		}

		return $out;
	}

	static public function renderGroup($form, $defaults, $group)
	{
		$fieldsets = $form->getFieldsets($group);
		$out = '';
		foreach($fieldsets as $name => $fieldset)
		{
			//if(JText::_($fieldset->label)) $out .= "<legend>" . JText::_($fieldset->label) . "</legend>";
			$out .= self::renderFieldset($form, $name, $defaults, $group, EM_STYLE_TABLE, 1);
		}
		return $out;
	}

	static public function renderForm($form, $defaults, $group = null, $separator = 0, $style = 1)
	{
		$fieldsets = $form->getFieldsets($group);

		$out = '';
		if($separator == EM_SEPARATOR_SLIDER)
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
				case EM_SEPARATOR_H2:
					$out .= "<h2>" . JText::_($fieldset->label) . "</h2>";
				break;
				case EM_SEPARATOR_FIELDSET:
					$out .= sprintf('<legend>%s</legend>', JText::_($fieldset->label));
				break;

				case EM_SEPARATOR_SLIDER:
					$out .= JHtml::_('sliders.panel', JText::_($fieldset->label), $fieldset->name);
				break;
			}
			$out .= self::renderFieldset($form, $name, $defaults, $group, $style, 0);
			switch($separator)
			{
				case EM_SEPARATOR_FIELDSET:
					$out .= '';
				break;

				case EM_SEPARATOR_SLIDER:
				//$out .= JHtml::_('sliders.end');
				break;
			}
		}

		if($separator == EM_SEPARATOR_SLIDER)
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
		$out = '';
		switch($type)
		{
			case EM_STYLE_CLASSIC:
				if($title && $fieldset->label)
				{
					$out .= '<fieldset class="adminform whitebg"><legend>' . JText::_($fieldset->label) . '</legend>';
				}
				if(isset($fieldset->description) && ! empty($fieldset->description)) $out .= '<small class="small">' . JText::_($fieldset->description) . '</small>';
				$out .= '<ul class="unstyled">';
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

			case EM_STYLE_TABLE:
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
						$out .= sprintf('<label rel="tooltip" data-original-title="%s">%s</label>', htmlentities(JText::_($field->description), ENT_QUOTES, 'UTF-8'), strip_tags($field->label));
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

			case EM_STYLE_PARAMS:
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

	static public function getGateways($form, $defaults)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		$out = array();

		$gateways_path = JPATH_COMPONENT.'/library/gateways/';
		$gateways = JFolder::folders($gateways_path);

		foreach ($gateways as $gateway)
		{
			$file = $gateways_path. $gateway. DIRECTORY_SEPARATOR . $gateway.'.xml';
			if (!JFile::exists($file)) continue;

			$lang = JFactory::getLanguage();
			$tag  = $lang->getTag();
			if($tag != 'en-GB')
			{
				if(!JFile::exists(JPATH_BASE . "/language/{$tag}/{$tag}.com_joomsubscription_gateway_{$gateway}.ini"))
				{
					$tag == 'en-GB';
				}
			}

			$lang->load('com_joomsubscription_gateway_' . $gateway, JPATH_ROOT, $tag, TRUE);

			$xml = new SimpleXMLElement($file, null, true);
			$params = new JForm($gateway, array('control' => 'params[gateways]'));
			$params->loadFile($file, true,  'config');

			$out[$gateway] = array('title' => $xml->name, 'html' => EMFormHelper::renderGroup($params, $defaults, $gateway));
		}

		return $out;
	}
}