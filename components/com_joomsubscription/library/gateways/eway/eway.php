<?php
/**
 * Card Not Present Test Account
 * API Login ID: 2Jfv4PZ6WgPG
 * Transaction Key: 9C8Dc46mqtf9BA6D
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.menu');

include_once __DIR__ . '/RapidAPI.php';

class JoomsubscriptionGatewaySkrill extends JoomsubscriptionGateway
{

	function accept(&$subscription, $plan)
	{
		$this->log('Start check eWay');

		$result  = $this->_getResponse();
		$service = $this->_getService();

		if(!$result)
		{
			JError::raiseWarning(100, JText::_('EW_ERRRES'));

			return FALSE;
		}

		if(in_array((string)$result->ResponseCode, array('00', '10', '11', '08', '16')))
		{
			$subscription->published = 1;
		}

		$subscription->gateway_id = $this->get_gateway_id();

		if(isset($result->ResponseMessage))
		{
			$ResponseMessageArray = explode(",", $result->ResponseMessage);
			$responseMessage      = "";
			foreach($ResponseMessageArray as $message)
			{
				$real_message = $service->getMessage($message);
				if($message != $real_message)
				{
					$responseMessage .= $message . " " . $real_message . "<br>";
				}
				else
				{
					$responseMessage .= $message;
				}
			}
			$subscription->note = $responseMessage;
		}

		return TRUE;
	}

	function pay($amount, $name, $subscription, $plan)
	{

		if(!$this->params->get('password') || !$this->params->get('username'))
		{
			$this->setError(JText::_('EW_CONFIG'));

			return FALSE;
		}

		$request = new eWAY\CreateAccessCodesSharedRequest();
		$user    = JFactory::getUser();

		if($subscription->invoice_id)
		{
			$invoice = new JoomsubscriptionModelsEmInvoiceTo();
			$data    = $invoice->getText($subscription->invoice_id);

			$request->Customer->CompanyName = $data->get('company');
			$request->Customer->City        = $data->get('city');
			$request->Customer->Street1     = $data->get('address');
			$request->Customer->State       = $data->get('state');
			$request->Customer->PostalCode  = $data->get('zip');
			$request->Customer->Country     = $data->get('country');
			$request->Customer->Email       = $user->get('email');
			$request->Customer->FirstName   = $user->get('name');
		}

		$item                        = new eWAY\LineItem();
		$item->SKU                   = $plan->id;
		$item->Description           = $name;
		$request->Items->LineItem[0] = $item;

		$request->Payment->TotalAmount        = $amount;
		$request->Payment->InvoiceNumber      = $subscription->id;
		$request->Payment->InvoiceDescription = $name;
		$request->Payment->InvoiceReference   = $subscription->id;
		$request->Payment->CurrencyCode       = $this->params->get('currency');


		$request->RedirectUrl     = $this->_get_notify_url($subscription->id);
		$request->CancelUrl       = $this->_get_return_url($subscription->id);
		$request->Method          = 'ProcessPayment';
		$request->TransactionType = 'Purchase';

		$langs             = array('en', 'es', 'fr', 'de', 'nl');
		$lang              = substr(JFactory::getLanguage()->getTag(), 0, 2);
		$request->Language = strtoupper(in_array($lang, $langs) ? $lang : '');

		$service = $this->_getService();
		$result  = $service->CreateAccessCodesShared($request);

		if(isset($result->Errors))
		{
			$ErrorArray = explode(",", $result->Errors);
			$lblError   = "";
			foreach($ErrorArray as $error)
			{
				$error = $service->getMessage($error);
				$lblError .= $error . "<br />\n";
			}
			$this->setError($lblError);

			return FALSE;
		}
		else
		{
			JFactory::getApplication()->redirect($result->SharedPaymentUrl);
			exit();
		}

	}

	function get_gateway_id()
	{
		$result = $this->_getResponse();

		return $result->TransactionID;
	}

	function get_subscrption_id($who)
	{
		$post = JFactory::getApplication()->input;

		return $post->getInt('em_id');
	}

	function _getService()
	{
		$options = array();
		if($this->params->get('demo'))
		{
			$options['sandbox'] = TRUE;
		}

		return eWAY\RapidAPI($this->params->get('username'), $this->params->get('password'), $options);
	}

	function _getResponse()
	{
		static $result = NULL;

		if(!$result)
		{
			$app        = JFactory::getApplication();
			$AccessCode = $app->input->get('AccessCode');
			$service    = $this->_getService();

			$request             = new eWAY\GetAccessCodeResultRequest();
			$request->AccessCode = $AccessCode;

			$result = $service->GetAccessCodeResult($request);


			if(isset($result->Errors))
			{
				$ErrorArray = explode(",", $result->Errors);
				$lblError   = "";
				foreach($ErrorArray as $error)
				{
					$error = $service->getMessage($error);
					$lblError .= $error . "<br />\n";
				}
				$this->log('Error response: ', $lblError);

				return FALSE;
			}

			$this->log('ger results: ', $result);
		}

		return $result;
	}
}