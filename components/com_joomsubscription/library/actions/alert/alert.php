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

class JoomsubscriptionActionAlert extends JoomsubscriptionAction
{

	public function onActive($subscription)
	{
		if(!$this->params->get('email') || !JMailHelper::isEmailAddress($this->params->get('email')))
		{
			return;
		}

		$mail   = JFactory::getMailer();
		$config = JFactory::getConfig();
		$plan   = JoomsubscriptionApi::getPlan($subscription->plan_id);

		$body = $this->_prepare($this->params->get('body'), $subscription, $plan);
		$body = JHtml::_('content.prepare', $body);

		$subject = $this->_prepare($this->params->get('subject'), $subscription, $plan);

		$is_html = !(strlen(strip_tags($body)) == strlen($body));

		$body = Mint::markdown($body);

		$mail->IsHTML(TRUE);

		$sender[0] = $config->get('mailfrom');
		$sender[1] = $config->get('fromname');

		$mail->setSender($sender);
		$mail->setBody(JMailHelper::cleanBody($body));
		$mail->setSubject(JMailHelper::cleanSubject($subject));

		if($this->params->get('file') && JFile::exists(JPATH_ROOT.'/'.ltrim($this->params->get('file'), "/\\")))
		{
			$mail->addAttachment(JPATH_ROOT.'/'.ltrim($this->params->get('file'), "/\\"));
		}

		if($this->params->get('mode') == 1)
		{
			$mail->AddAddress(JFactory::getUser($subscription->user_id)->get('email'));
		}
		else
		{
			$mail->AddAddress($this->params->get('email'));
		}

		return $mail->Send();

	}

	public function onDisactive($subscription)
	{
		return NULL;
	}

	public function getDescription()
	{
		return JText::sprintf('X_ALERT_DESCR', $this->params->get('email'));
	}

	private function _prepare($text, $subscr, $plan)
	{
		$user = JFactory::getUser($subscr->user_id);

		$change = array(
			'[ID]'             => $subscr->id,
			'[USER_NAME]'      => $user->get('username'),
			'[USER_EMAIL]'     => $user->get('email'),
			'[PRICE]'          => $subscr->price,
			'[PLAN_NAME]'      => $plan->name,
			'[GROUPNAME]'      => $plan->cname,
			'[GATEWAY]'        => $subscr->gateway,
			'[TRANSACTION_ID]' => $subscr->gateway_id
		);

		$text = str_replace(array_keys($change), $change, $text);

		return $text;
	}
}