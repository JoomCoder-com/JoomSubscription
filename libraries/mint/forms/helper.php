<?php
/**
 * Cobalt by MintJoomla
 * a component for Joomla! 1.7 - 2.5 CMS (http://www.joomla.org)
 * Author Website: http://www.mintjoomla.com/
 *
 * @copyright Copyright (C) 2012 MintJoomla (http://www.mintjoomla.com). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('JPATH_PLATFORM') or die;

class MFormHelper
{
	const FIELDSET_SEPARATOR_NONE     = 0;
	const FIELDSET_SEPARATOR_FIELDSET = 1;
	const FIELDSET_SEPARATOR_HEADER   = 2;

	const GROUP_SEPARATOR_NONE   = 0;
	const GROUP_SEPARATOR_TAB    = 1;
	const GROUP_SEPARATOR_SLIDER = 2;

	const STYLE_CLASSIC = 1;
	const STYLE_TABLE   = 2;

	static private $templates = [
		'default' => [
			'style-classic'       => '<div class="control-group">%s<div class="controls">%s</div></div>',
			'style-table-full'    => '<tr id="tr_%s"><td colspan="2"><h5>%s</h5>%s</td></tr>',
			'style-table-classic' => '<tr id="tr_%s"><td>%s</td><td>%s</td></tr>',
			'style-table-wrap'    => '<table id="table-id-%s" class="table table-bordered table-striped table-hover">%s</table>',
			'style-classic-wrap'  => '<div id="form-block-id-%s">%s</div>',
			'description'         => '<p><small>%s</small></p>',
			'separator2'          => '<h3>%s</h3>%s%s',
			'separator1'          => '<fieldset><legend>%s</legend>%s%s</fieldset>',
			'separator0'          => '%2$s%3$s',
			'label'               => '<label id="%s-lbl" for="%s" class="%s" data-toggle="tooltip" title="%s">%s</label>',
			'wrap'                => '<section id="fieldset-name-%s">%s</section>',
		],
		'warp' => [
			'style-classic'       => '<div class="uk-form-row">%s<div class="uk-form-controls">%s</div></div>',
			'style-table-full'    => '<tr id="tr_%s"><td colspan="2"><h5>%s</h5>%s</td></tr>',
			'style-table-classic' => '<tr id="tr_%s" class="uk-table-bold"><td>%s</td><td>%s</td></tr>',
			'style-table-wrap'    => '<table id="table-id-%s" class="uk-table uk-table-bordered uk-table-striped uk-table-hover">%s</table>',
			'style-classic-wrap'  => '<div id="form-block-id-%s" class="uk-form uk-form-horizontal">%s</div>',
			'description'         => '<div style="margin-top: 0" class="uk-alert uk-alert-success"><small>%s</small></div>',
			'separator2'          => '<h3>%s</h3>%s%s',
			'separator1'          => '<fieldset><legend>%s</legend>%s%s</fieldset>',
			'separator0'          => '%2$s%3$s',
			'label'               => '<label id="%s-lbl" for="%s" class="uk-form-label %s" data-uk-tooltip title="%s">%s</label>',
			'wrap'                => '<section id="fieldset-name-%s">%s</section>',
		]
	];

	/**
	 * @param JForm $form
	 * @param       $defaults
	 * @param       $groups
	 * @param int   $separator
	 * @param int   $style
	 * @param int   $group_separator
	 *
	 * @return string
	 */
	static public function renderGroups($form, $defaults, $groups, $separator = 3, $style = 2, $group_separator = 0)
	{
		settype($groups, 'array');

		$out = array();
		switch($group_separator)
		{
			case self::GROUP_SEPARATOR_SLIDER:
				$out[] = '<div class="accordion" id="' . str_replace('.', '_', $form->getName()) . '">';
				break;
			case self::GROUP_SEPARATOR_TAB:
				$out[] = '<ul class="nav nav-tabs" id="' . str_replace('.', '_', $form->getName()) . '">';
				foreach($groups as $group)
				{
					$out[] = '<li><a href="#' . $group . '" data-toggle="tab">' . JText::_('GROUP_' . strtoupper($group)) . '</a></li>';
				}
				$out[] = '</ul>';
				$out[] = '<div class="tab-content">';
				break;
		}

		foreach($groups as $group)
		{
			switch($group_separator)
			{
				case self::GROUP_SEPARATOR_SLIDER:
					$out[] = '<div class="accordion-group"><div class="accordion-heading">
						<a class="accordion-toggle" data-toggle="collapse" data-parent="#' . str_replace('.', '_', $form->getName()) . '" href="#' . $group . '">
						' . JText::_('GROUP_' . strtoupper($group)) . '</a></div><div id="' . $group . '" class="accordion-body collapse fade"><div class="accordion-inner">';
					break;
				case self::GROUP_SEPARATOR_TAB:
					$out[] = sprintf(' <div class="tab-pane" id="%s">', $group);
					break;
			}

			$out[] = self::renderGroup($form, $defaults, $group, $separator, $style);

			switch($group_separator)
			{
				case self::GROUP_SEPARATOR_SLIDER:
					$out[] = '</div></div></div>';
					break;
				case self::GROUP_SEPARATOR_TAB:
					$out[] = '</div>';
					break;
			}
		}

		switch($group_separator)
		{
			case self::GROUP_SEPARATOR_SLIDER:
				$out[] = '</div>';
				$out[] = sprintf("<script>jQuery('#%s .accordion-body:first').collapse('show');</script>", str_replace('.', '_', $form->getName()));
				break;
			case self::GROUP_SEPARATOR_TAB:
				$out[] = '</div>';
				$out[] = sprintf("<script>jQuery('#%s a:first').tab('show');</script>", str_replace('.', '_', $form->getName()));
				break;
		}

		return implode("\n", $out);
	}

	static public function renderGroup($form, $defaults, $group, $separator = 2, $style = 2)
	{
		$fieldsets = $form->getFieldsets($group);

		$out = array();

		foreach($fieldsets as $name => $fieldset)
		{
			$out[] = self::renderFieldset($form, $name, $defaults, $group, $separator, $style);
		}

		return implode("\n", $out);
	}

	/**
	 * Render form based on jform object.
	 *
	 * @param JForm $form
	 * @param array $defaults
	 * @param mixed $group group name or array of group names
	 * @param int   $separator
	 * @param int   $style
	 * @param int   $group_separator
	 *
	 * @return string HTML form
	 */
	static public function renderForm($form, array $defaults, $group = NULL, $separator = 3, $style = 2, $group_separator = 0)
	{
		settype($group, 'array');

		if(empty($group))
		{
			$xml = $form->getXml();

			foreach($xml->fields as $field)
			{
				if((string)$field->attributes()->name)
				{
					$group[] = (string)$field->attributes()->name;
				}
			}
		}

		return self::renderGroups($form, $defaults, $group, $separator, $style, $group_separator);
	}

	/**
	 * @param JForm $form
	 * @param       $name
	 * @param       $defaults
	 * @param       $group
	 * @param int   $title
	 * @param int   $style
	 *
	 * @return string
	 */
	static public function renderFieldset($form, $name, $defaults, $group, $separator = 2, $style = 2)
	{
		$tmpl = self::_get_templates();

		if(is_array($defaults))
		{
			$registry = new JRegistry();
			$registry->loadArray($defaults);
			$defaults = $registry;
		}

		$hidden = array();
		$fields    = $form->getFieldset($name);
		$fieldsets = $form->getFieldsets($group);
		$fieldset  = $fieldsets[$name];

		$row = array();
		foreach($fields as $key => $field)
		{
			$default = $defaults->get(sprintf(empty($group) ? '%2$s' : '%s.%s', $group, $field->fieldname));
			if($field->hidden)
			{
				$hidden[] = $form->getInput($field->fieldname, $group, $default);
				continue;
			}

			switch($style)
			{
				case self::STYLE_CLASSIC:

					$form->setFieldAttribute($field->fieldname, 'labelclass', 'control-label');
					$row[]    = sprintf($tmpl['style-classic'],
						self::_getLabel($field, TRUE), $form->getInput($field->fieldname, $group, $default));
					$row_tmpl = 'style-classic-wrap';
					break;

				case self::STYLE_TABLE:
					$full_width = array('Caddress', 'Ccontacts', 'Clinks', 'Cobaltevents', 'Editor');
					if(in_array($field->type, $full_width))
					{
						$pattern = $tmpl['style-table-full'];
					}
					else
					{
						$pattern = $tmpl['style-table-classic'];
					}

					$row[]    = sprintf($pattern, $field->id, self::_getLabel($field), $form->getInput($field->fieldname, $group, $default));
					$row_tmpl = 'style-table-wrap';

					break;
			}
		}

		$block = sprintf($tmpl[$row_tmpl], JText::_($fieldset->label), implode("\n", $row));

		$out = sprintf($tmpl['separator' . $separator],
			JText::_($fieldset->label),
			(!empty($fieldset->description) ? sprintf($tmpl['description'], JText::_($fieldset->description)) : NULL),
			$block
		);

		$out .= implode("\n", $hidden);

		return sprintf($tmpl['wrap'], $fieldset->name, $out);

	}

	protected static function _getLabel($field, $lc = FALSE)
	{
		$class = $field->required == TRUE ? 'required ' : '';
		$class .= $lc ? 'control-label' : NULL;

		$tooltip = '';
		$tmpl    = self::_get_templates();

		/*if($field->description)
		{
			JHtml::_('bootstrap.tooltip', '[data-toggle="tooltip"]');
			$tooltip = sprintf('data-toggle="tooltip" title="%s"', );
		}*/

		$label = sprintf($tmpl['label'],
			$field->id, $field->id, $class, htmlspecialchars(JText::_($field->description), ENT_COMPAT, 'UTF-8'), str_replace('*', '*', strip_tags($field->label))
		);

		return $label;
	}

	static public function getGateways($form, $defaults)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		$out = array();

		$gateways_path = JPATH_COMPONENT . '/library/gateways/';
		$gateways      = JFolder::folders($gateways_path);


		foreach($gateways as $gateway)
		{
			$file = $gateways_path . $gateway . DIRECTORY_SEPARATOR . $gateway . '.xml';
			if(!JFile::exists($file))
				continue;

			$lang = JFactory::getLanguage();
			$tag  = $lang->getTag();
			if($tag != 'en-GB')
			{
				if(!JFile::exists(JPATH_BASE . "/language/{$tag}/com_joomsubscription_gateway_{$gateway}.ini"))
				{
					$tag == 'en-GB';
				}
			}

			$lang->load('com_joomsubscription_gateway_' . $gateway, JPATH_ROOT, $tag, TRUE);

			$xml    = new SimpleXMLElement($file,0,true);
			$params = new JForm($gateway, array('control' => 'params[gateways]'));
			$params->loadFile($file, TRUE, 'config');

			$out[$gateway] = array('title' => $xml->name, 'html' => MFormHelper::renderGroup($params, $defaults, $gateway));
		}

		return $out;
	}

	private static function _get_templates()
	{
		$params = JComponentHelper::getParams(JFactory::getApplication()->input->get('option'));
		$prefix = $params->get('tmpl_prefix', 'default');
		if(!empty(self::$templates[$prefix]))
		{
			return self::$templates[$prefix];
		}

		return self::$templates['default'];
	}
}