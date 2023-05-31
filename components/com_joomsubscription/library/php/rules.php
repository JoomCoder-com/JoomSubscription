<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

class JoomsubscriptionRule extends JObject
{
	public function __construct($rule)
	{
		$data = $rule->rule;
		if(!is_object($data))
		{
			$data = new \JRegistry(json_decode($data, TRUE));
		}

		$this->params  = $data;
		$this->type    = $rule->option;
		$this->id      = $rule->id;
		$this->plan_id = $rule->plan_id;
		$this->input   = JFactory::getApplication()->input;
	}

	public function getDescription()
	{
	}

	public function isProtected()
	{
	}

	public function getRestrictionMessage($default)
	{
		return $this->params->get('message', $default);
	}
}