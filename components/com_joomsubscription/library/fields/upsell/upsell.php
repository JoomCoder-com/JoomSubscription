<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 1/27/15
 * Time: 14:21
 */

defined('_JEXEC') or die('Restricted access');


class JoomsubscriptionFieldUpsell extends JoomsubscriptionField
{

	public function affectPrice()
	{
		if($this->default == 1)
		{
			return $this->params->get('params.price');
		}
	}

	public function hasAccess()
	{
		if($this->default != 1)
		{
			return;
		}

		$input = JFactory::getApplication()->input;

		if($input->get('option') != $this->params->get('params.component'))
		{
			return FALSE;
		}

		include_once JPATH_ROOT . '/components/com_joomsubscription/library/rules/default/default.php';

		$init          = new stdClass();
		$init->rule    = new JRegistry($this->params->get('params'));
		$init->option  = $this->params->get('params.component');
		$init->id      = 0;
		$init->plan_id = 0;

		$rule = new JoomsubscriptionRuleDefault($init);

		return $rule->isProtected();
	}
}
