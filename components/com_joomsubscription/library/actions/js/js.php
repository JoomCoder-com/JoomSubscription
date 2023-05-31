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

class JoomsubscriptionActionJs extends JoomsubscriptionAction
{
	public function onActive($subscription)
	{
		$doc  = JFactory::getDocument();
		$from = array(
			'[PLAN_ID]',
			'[USER_ID]',
			'[USER_SUBSCR_ID]',
			'[PRICE]',
			'[GATEWAY_ID]',
			'[GATEWAY]',
			'[START_DATE]',
			'[END_DATE]'
		);

		$to = array(
			$subscription->plan_id,
			$subscription->user_id,
			$subscription->id,
			$subscription->price,
			$subscription->gateway_id,
			$subscription->gateway,
			$subscription->ctime,
			$subscription->extime
		);
		if($this->params->get('html'))
		{
			$html = $this->params->get('html');
			$html = str_replace($from, $to, $html);
			echo $html;
		}

		if($this->params->get('js'))
		{
			$js = str_replace($from, $to, $this->params->get('js'));

			$doc->addScriptDeclaration($js);
		}
	}

	public function getDescription()
	{
		$out = '';
		if($this->params->get('js'))
		{
			$out .= $this->params->get('js');
		}

		return $out;
	}
}
