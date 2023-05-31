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

class JoomsubscriptionGatewayInterkassa extends JoomsubscriptionGateway
{

	function accept(&$subscription, $plan)
	{
		$post = JFactory::getApplication()->input;

		if($this->_clean($this->_getHash($_POST)) != $this->_clean($post->get('ik_sign')))
		{
			$this->setError(JText::_('EMR_CANNOT_VERYFY'));
			$this->log('IK: Verification failed', $_POST);
			$this->log('IK: post', $this->_clean($this->_getHash($_POST)));
			$this->log('IK: or', $this->_clean($post->get('ik_sign')));

			return FALSE;
		}

		$subscription->gateway_id = $this->get_gateway_id();
		$subscription->published  = $post->get('ik_inv_st') == 'success' ? 1 : 0;

		return TRUE;
	}

	private function _clean($hash)
	{
		$hash = urldecode($hash);
		$hash = preg_replace('/[^A-Za-z0-9]/iU', '', $hash);
		$hash = strtoupper($hash);

		return $hash;
	}

	function pay($amount, $name, $subscription, $plan)
	{
		if(!$this->params->get('eshopid'))
		{
			$this->setError(JText::_("IK_NOT_ALL_SET"));

			return FALSE;
		}

		$param['ik_co_id'] = $this->params->get('eshopid');
		$param['ik_pm_no'] = $subscription->id;
		$param['ik_cur']   = $this->params->get('currency');
		$param['ik_am']    = $amount;
		$param['ik_desc']  = $name;
		$param['ik_usr']   = JFactory::getUser()->get('email');

		$param['ik_ia_u']  = $this->_get_notify_url($subscription->id);
		$param['ik_ia_m']  = 'POST';
		$param['ik_suc_u'] = $this->_get_return_url($subscription->id);
		$param['ik_suc_m'] = 'GET';
		$param['ik_pnd_u'] = $this->_get_return_url($subscription->id);
		$param['ik_pnd_m'] = 'GET';
		$param['ik_fal_u'] = $this->_get_return_url($subscription->id);
		$param['ik_fal_m'] = 'GET';

		$param['ik_sign'] = $this->_getHash($param);

		$url = 'http://sci.interkassa.com/?' . http_build_query($param);

		JFactory::getApplication()->redirect($url);
	}

	private function _getHash($array)
	{
		unset($array['ik_sign']);

        foreach($array AS $k => $a)
        {
            if(!preg_match("/^ik_(.*)/iU", $k))
            {
                continue;
            }
            $hash[$k] = $a;
        }

        ksort($hash, SORT_STRING);
		$sign = implode(':', $hash) . ':' . $this->params->get('secret');

		return base64_encode(md5($sign, TRUE));
	}

	function get_gateway_id($who = NULL)
	{
		$post = JFactory::getApplication()->input;

		return $post->get('ik_inv_id', $post->get('ik_trn_id'));
	}

	function get_subscrption_id($who)
	{
		$app = JFactory::getApplication();

		return $app->input->get('ik_pm_no', $app->input->get('em_id'));
	}
}
