<?php
/**
 * Joomsubscription Payment Plugin by JoomCoder
 * a plugin for Joomla! 1.7 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.menu');
jimport('joomla.mail.mail');
jimport('joomla.mail.helper');
jimport('mint.helper');

class JoomsubscriptionGatewayOffline extends JoomsubscriptionGateway
{

	function accept(&$subscription, $plan)
	{
		$user = JFactory::getUser();
		$db   = JFactory::getDBO();

		$subscription->gateway_id = time();

		if($this->params->get('published', 0))
		{
			$subscription->published = 1;
		}

		$app  = JFactory::getApplication();
		$user = JFactory::getUser($subscription->user_id);

		$subject = $this->params->get('cus_subj');

		$sender[0] = $app->getCfg('mailfrom');
		$sender[1] = $app->getCfg('fromname');

		$from = array(
			'[USER]',
			'[SUBSCR_NAME]',
			'[ORDER_ID]',
			'[PRICE]',
			'[HISTORY_URL]',
			'[USER_EMAIL]',
			'[GROUPNAME]'
		);

		$to = array(
			$user->get('name'),
			$plan->name,
			$subscription->gateway_id,
			JoomsubscriptionApi::getPrice($subscription->price, $plan->params),
			JoomsubscriptionApi::getLink('emhistory'),
			$user->get('email'),
			$plan->cname
		);

		$body = str_replace($from, $to, Mint::_($this->params->get('cus_body')));
		$body = Mint::markdown($body);

		$mail = JFactory::getMailer();
		$mail->setSender($sender);
		$mail->AddAddress($user->get('email'));
		$mail->setBody(JMailHelper::cleanBody($body));
		$mail->setSubject(JMailHelper::cleanSubject($subject));

		if(JString::strlen($body) != JString::strlen(strip_tags($body)))
		{
			$mail->IsHTML(TRUE);
		}

		if(!$mail->Send())
		{
			$this->setError(JText::_('OFF_ERRORSENDEMAIL'));
			return false;
		}


		$subject = $this->params->get('subject');

		$sender[0] = $user->get('email');
		$sender[1] = $user->get('name');

		$body = str_replace($from, $to, $this->params->get('adm_body'));
		$body = Mint::markdown($body);

		$mail = JFactory::getMailer();
		$mail->setSender($sender);
		$mail->ClearAllRecipients();
		$mail->addRecipient(explode(',', $this->params->get('email', $app->getCfg('mailfrom'))), 'Admin');
		$mail->setBody(JMailHelper::cleanBody($body));
		$mail->setSubject(JMailHelper::cleanSubject($subject));

		if(JString::strlen($body) != JString::strlen(strip_tags($body)))
		{
			$mail->IsHTML(TRUE);
		}

		if(!$mail->Send())
		{
			$this->setError(JText::_('OFF_ERRORSENDADMINEMAIL'));
			return false;
		}

		JoomsubscriptionHelper::activateSubscription($subscription, $plan);
		$subscription->store();

		$app->enqueueMessage(JText::_('OFF_EMAILS_SENT'));
		if($this->params->get('redirect') && filter_var($this->params->get('redirect'), FILTER_VALIDATE_URL))
		{
			$app->redirect($this->params->get('redirect'));
		}
		else
		{
			JoomsubscriptionHelper::redirect($plan, $subscription->published);
		}
	}

	function pay($amount, $name, $subscription, $plan)
	{
		JFactory::getApplication()->redirect($this->_get_notify_url($subscription->id));
	}

}