<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.menu');

class JoomsubscriptionGatewayVcs extends JoomsubscriptionGateway
{
	public function accept(&$subscription, $plan)
	{
		$post = JFactory::getApplication()->input;

		if($post->get('pam') != $this->params->get('pam'))
		{
			$this->setError(JText::_('VCS_PAM_FAIL'));
			$this->log('Pam filed', $this->params->get('pam'));

			return FALSE;
		}

		if($subscription->gateway_id && ($subscription->gateway_id != $this->get_gateway_id()))
		{
			$subscription->add_new($plan, $this->get_gateway_id());
		}
		$subscription->gateway_id = $this->get_gateway_id();

		switch($post->get('TransactionType'))
		{
			case 'Settlement':
			case 'Authorisation':
				if($post->getInt('p12') == 0)
				{
					$subscription->published = 1;
				}
				else
				{
					$subscription->note      = $post->getInt('p3');
					$subscription->published = 0;
				}
				break;
			case 'Refund':
				$subscription->published = 0;
				break;
		}

		echo '<CallbackResponse>Accepted</CallbackResponse>';

		return TRUE;
	}

	public function pay($amount, $name, $subscription, $plan)
	{
		$user = JFactory::getUser();

		if(!$this->params->get('p1'))
		{
			$this->setError(JText::_('VCS_NO_TRANS'));

			return FALSE;
		}

		$post_variables = Array(
			"p1" => $this->params->get('p1'),
			"p2" => $subscription->id,
			"p3" => $name,
			"p4" => $amount,
			"p5" => $this->params->get('currency'),
		);

		if($this->params->get('recurred'))
		{
			$post_variables['p6'] = $this->params->get('recurred_num');
			$post_variables['p7'] = $this->params->get('recurred_friq');
		}

		$post_variables["p10"] = $this->_get_return_url($subscription->id);

		if($this->params->get('recurred'))
		{
			$post_variables['p11'] = $user->get('email');
		}

		$post_variables["Budget"]           = $this->params->get('budget');
		$post_variables["CardholderEmail"] = $subscription->user_id;

		$hash = md5(implode('', $post_variables) . $this->params->get('md5key'));

		$post_variables['hash'] = $hash;

		$post_variables['UrlsProvided'] = 'Y';
		$post_variables['ApprovedUrl']  = $this->_get_return_url($subscription->id);
		$post_variables['DeclinedUrl']  = $this->_get_return_url($subscription->id);

		$url = "https://www.vcs.co.za/vvonline/vcspay.aspx";
		$c = JText::_('VCS_WIT_TRANS');
		$c .= '<form action="' . $url . '" method="post" name="vcsform" id="vcsform">';
		foreach($post_variables as $name => $value)
		{
			$c .= '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
		}
		$c .= '</form>';
		$c .= '<script>document.getElementById("vcsform").submit();</script>';

		echo $c;
		JFactory::getApplication()->close();
	}

	public function get_subscrption_id($who = null)
	{
		$app = JFactory::getApplication();

		return $app->input->get('em_id', $app->input->get('p2'));
	}

	public function get_gateway_id()
	{
		return JFactory::getApplication()->input->get('Uti');
	}
}