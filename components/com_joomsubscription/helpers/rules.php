<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die();

include_once JPATH_ROOT.'/components/com_joomsubscription/views/emrules/html.php';
include_once JPATH_ROOT.'/components/com_joomsubscription/models/emrules.php';

class JoomsubscriptionRulesHelper
{
	public static function rules_form($plan)
	{

		$template = JFactory::getApplication()->getTemplate();
		$view = new JoomsubscriptionViewsEmRulesHtml();

		$view->addTemplatePath(JPATH_ROOT . '/components/com_joomsubscription/views/emrules/tmpl');
		$view->addTemplatePath(JPATH_ROOT . '/templates/'.$template.'/html/com_joomsubscription/emrules');

		$view->setLayout('default');
		$view->plan = $plan;

		$view->display();
	}

	public static function get_rule_class($rule)
	{
		$rules = JFolder::folders(JPATH_ROOT . '/components/com_joomsubscription/library/rules/');
		$name = $rule->controller;

		if(!in_array($name, $rules))
		{
			$name = 'default';
		}

		self::load_lang($name);

		include_once JPATH_ROOT . '/components/com_joomsubscription/library/rules/'.$name . '/' . $name . '.php';

		$class = 'JoomsubscriptionRule'.ucfirst($name);

		return new $class($rule);
	}

	public static function description($rule)
	{
		$data = $rule->rule;
		if(!is_array($data))
		{
			$data = json_decode($data);
		}
		$out = self::get_rule_class($rule)->getDescription();

		JFactory::getLanguage()->load($rule->option.'.sys', JPATH_ADMINISTRATOR);

		$name = '<span class="pull-right"><small data-rule-edit="'.$rule->id.'" data-controller="'.$rule->controller.'">Edit</small></span>';
		$name .= JText::_($rule->option);

		if($name != $rule->option)
		{
			$name .= " <small>$rule->option</small>";
		}

		return sprintf('<h4 data-toggle="collapse" data-target="#rule-%s">%s</h4><div id="rule-%s" class="collapse fade"><small>%s</small></div>', $rule->id, $name, $rule->id, $out);

	}

	public static function load_lang($rule)
	{
		$lang = JFactory::getLanguage();
		$tag  = $lang->getTag();
		if($tag != 'en-GB')
		{
			if(!JFile::exists(JPATH_BASE . "/language/{$tag}/{$tag}.com_joomsubscription_rule_{$rule}.ini"))
			{
				$tag == 'en-GB';
			}
		}

		$lang->load('com_joomsubscription_rule_' . $rule, JPATH_ROOT, $tag, TRUE);
	}

	public static function get_rule_name($type)
	{
		self::load_lang($type);
		$path = JPATH_COMPONENT.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'rules';
		$xml = $path.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$type.'.xml';
		$xml = new SimpleXMLElement($xml, null, true);
		return $type. ' - ' . JText::_($xml->name);
	}
}