<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 1/27/15
 * Time: 14:21
 */

defined('_JEXEC') or die('Restricted access');


class JoomsubscriptionFieldSerial extends JoomsubscriptionField
{
	public function onHistory($subscription)
	{
		$serial = JTable::getInstance('EmSerial', 'JoomsubscriptionTable');
		$serial->load(
			array(
				'user_id'         => $subscription->user_id,
				'subscription_id' => $subscription->sid ? $subscription->sid : $subscription->id,
				'field_id'        => $this->id
			)
		);

		if($serial->id && $serial->active == 1)
		{
			return JText::sprintf($this->params->get('params.note'), $serial->serial);
		}
	}

	public function onSuccess($subscription)
	{
		$serial = JTable::getInstance('EmSerial', 'JoomsubscriptionTable');
		$serial->load(
			array(
				'user_id'         => $subscription->user_id,
				'subscription_id' => $subscription->id,
				'field_id'        => $this->id
			)
		);

		if($serial->id)
		{
			$serial->active = 1;
			$serial->store();
		}
	}

	public function onCreate($subscription)
	{
		$serial = JTable::getInstance('EmSerial', 'JoomsubscriptionTable');

		$serial->load(
			array(
				'user_id'         => $subscription->user_id,
				'subscription_id' => $subscription->id,
				'field_id'        => $this->id
			)
		);

		$list   = $this->_getList();
		$number = array_shift($list);

		$serial->bind(
			array(
				'user_id'         => $subscription->user_id,
				'subscription_id' => $subscription->id,
				'field_id'        => $this->id,
				'active'          => 0,
				'serial'          => $number
			)
		);
		$serial->store();

		$field = JTable::getInstance('EmField', 'JoomsubscriptionTable');
		$field->load($this->id);
		$field_params               = json_decode($field->params);
		$field_params->params->list = implode("\n", $list);
		$field->params              = json_encode($field_params);
		$field->store();
	}

	public function isReady()
	{
		$list = $this->_getList();

		if($this->params->get('params.email') &&
			$this->params->get('params.alert') > 0 &&
			(count($list) <= $this->params->get('params.alert')))
		{
			$mail      = JFactory::getMailer();
			$config    = JFactory::getConfig();
			$sender[0] = $config->get('mailfrom');
			$sender[1] = $config->get('fromname');

			$mail->setSender($sender);
			$mail->AddAddress($this->params->get('params.email'));
			$mail->setBody(JMailHelper::cleanBody(JText::sprintf('EMR_SERIAL_SUBJECT', $this->getLabel())));
			$mail->setSubject(JMailHelper::cleanSubject(JText::sprintf('EMR_SERIAL_BODY', $this->getLabel())));
			$mail->isHtml(FALSE);
			$mail->Send();
		}

		if(!$list)
		{
			return FALSE;
		}

		return TRUE;
	}

	private function _getList()
	{
		$list = explode("\n", str_replace("\r", "", $this->params->get('params.list')));

		foreach($list AS $k => $v)
		{
			if(empty($v))
			{
				unset($list[$k]);
			}
		}

		return $list;
	}
}
