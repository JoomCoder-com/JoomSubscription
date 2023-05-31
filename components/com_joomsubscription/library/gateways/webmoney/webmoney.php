<?php

defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionGatewayWebmoney extends JoomsubscriptionGateway
{
	function accept(&$subscription, $plan)
	{
		$post = new JRegistry($_POST);

		if(!$post->get('LMI_HASH'))
		{
			echo 'YES';

			return TRUE;
		}

		$this->log('post: ', $_POST);

		$key = $post->get('LMI_PAYEE_PURSE');
		$key .= $post->get('LMI_PAYMENT_AMOUNT');
		$key .= $post->get('LMI_PAYMENT_NO');
		$key .= $post->get('LMI_MODE');
		$key .= $post->get('LMI_SYS_INVS_NO');
		$key .= $post->get('LMI_SYS_TRANS_NO');
		$key .= $post->get('LMI_SYS_TRANS_DATE');
		$key .= $this->params->get("secret");
		$key .= $post->get('LMI_PAYER_PURSE');
		$key .= $post->get('LMI_PAYER_WM');

		$this->log('hash: ', strtoupper(hash('sha256', $key)));

		if(strtoupper(hash('sha256', $key)) != strtoupper($post->get('LMI_HASH')))
		{
			$this->setError(JText::_('EMR_CANNOT_VERYFY'));

			return FALSE;
		}

		$subscription->gateway_id = $this->get_gateway_id();
		$subscription->published = 1;

		return TRUE;
	}

	function pay($amount, $name, $subscription, $plan)
	{
		$user = JFactory::getUser();

		if(!$this->params->get('purse'))
		{
			$this->setError(JText::_('WM_ERR_NOPURSE'));

			return FALSE;
		}

		$post_variables = Array(
			"LMI_RESULT_URL"     => $this->_get_notify_url($subscription->id),

			"LMI_SUCCESS_URL"    => $this->_get_return_url($subscription->id),
			"LMI_SUCCESS_METHOD" => "2",
			"LMI_FAIL_URL"       => $this->_get_return_url($subscription->id),
			"LMI_FAIL_METHOD"    => "2",

			"LMI_PAYMENT_AMOUNT" => $amount,
			"LMI_PAYEE_PURSE"    => $this->params->get('purse'),
			"LMI_PAYMENT_DESC"   => $name,
			"LMI_PAYMENT_NO"     => $subscription->id,
			"LMI_MODE"           => $this->params->get("demo"),
			"LMI_SIM_MODE"       => "0",
			"LMI_PAYMER_EMAIL"   => $user->get("email"),

			"PLAN_ID"            => $plan->id,
			"USER_ID"            => $subscription->user_id
		);

		$url = "https://merchant.webmoney.ru/lmi/payment.asp?";
		$c = '';
		$c .= '<form action="' . $url . '" method="post" name="wmform" id="wmform" accept-charset="windows-1251">';
		$c .= '<input type="submit" value="'.JText::_('PAY_REDIRECTING_CONTINUE').'" name="formSubmit" class="button"/>';
		foreach($post_variables as $name => $value)
		{
			$c .= '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
		}
		$c .= '</form>';
		$c .= '<script>document.getElementById("wmform").submit();</script>';
		echo $c;
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::_('PAY_REDIRECTING'));

		return TRUE;
	}

	function get_plan_id()
	{
		return JFactory::getApplication()->input->getInt('PLAN_ID');
	}

	function get_user_id()
	{
		return JFactory::getApplication()->input->getInt('USER_ID');
	}

	function get_amount()
	{
		return JFactory::getApplication()->input->get('LMI_PAYMENT_AMOUNT');
	}

	function get_gateway_id()
	{
		return JFactory::getApplication()->input->get('LMI_SYS_INVS_NO');
	}
}