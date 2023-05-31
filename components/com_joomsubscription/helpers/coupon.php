<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sergey
 * Date: 3/6/13
 * Time: 1:42 PM
 * To change this template use File | Settings | File Templates.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die();

class JoomsubscriptionHelperCoupon
{

	public static function getCoupon($value, $plan_id, $price = 0, $msg = FALSE, $numbercheck = TRUE)
	{
		if(!$value)
		{
			return new stdClass();
		}

		$app   = JFactory::getApplication();
		$user  = JFactory::getUser();
		$model = MModelBase::getInstance('EmPlans', 'JoomsubscriptionModel');

		if(substr($value, 0, 4) == "MUA-")
		{
			$mua = explode("-", $value);

			$db  = JFactory::getDbo();
			$sql = "SELECT count(*) FROM #__joomsubscription_subscriptions WHERE user_id = '{$user->id}' AND parent='{$mua[1]}'";
			$db->setQuery($sql);
			$user_sub = $db->LoadResult();

			if($user_sub > 0 && $numbercheck)
			{
				if($msg)
				{
					JError::raiseNotice(100, JText::_('EMR_COUPON_ALREADYUSED'));
				}

				return new stdClass();
			}

			$result = JoomsubscriptionHelper::getUserSubscr($mua[1]);

			if(empty($result->id))
			{
				if($msg)
				{
					JError::raiseNotice(100, JText::_('EMR_COUPON_MUAROOTNOTFOUND'));
				}

				return new stdClass();
			}

			if($result->expired)
			{
				if($msg)
				{
					JError::raiseNotice(100, JText::sprintf('EM_PARENTSUBSCR_EXPIRED', '<span class="label label-success">' . $value . '</span>'));
				}

				return new stdClass();
			}

			if($result->plan_id != $plan_id)
			{
				if($msg)
				{
					JError::raiseNotice(100, JText::sprintf('EM_PARENTSUBSCR_NOTTHATPLAN', '<span class="label label-success">' . $value . '</span>'));
				}

				return new stdClass();
			}

			$value2 = self::getMUACoupon($mua[1], $result);
			if($value != $value2)
			{
				if($msg)
				{
					JError::raiseNotice(100, JText::sprintf('EM_PARENTSUBSCR_NOTVALID', '<span class="label label-success">' . $value . '</span>'));
				}

				return new stdClass();
			}

			$mua_params = new JRegistry($result->params);
			$mua_model  = MModelBase::getInstance('EmMua', 'JoomsubscriptionModel');
			$mua_subscr = $mua_model->getSubscrMUA($mua[1]);
			if(count($mua_subscr) >= $mua_params->get('properties.muaccess'))
			{
				if($msg)
				{
					JError::raiseNotice(100, JText::sprintf('EMR_COUPON_MUACANNOTAPPLY'));
				}

				return new stdClass();
			}

			$out                 = new stdClass();
			$out->id             = 0;
			$out->value          = $value;
			$out->discount_total = $price;
			$out->discount_type  = 'MUA';
			$out->discount       = $price;
			$out->mua            = TRUE;
			$out->parent         = $mua[1];
			$out->extime         = $result->extime;
			$out->ctime          = $result->ctime;

			return $out;
		}


		$db  = JFactory::getDBO();
		$sql = "SELECT *, IF(`extime` > NOW() OR `extime` = '0000-00-00 00:00:00', 0, 1) AS expired FROM #__joomsubscription_coupons WHERE `value` = '$value'";
		$db->setQuery($sql);

		$coupon = $db->loadObject();

		if(empty($coupon->id))
		{
			if($msg)
			{
				JError::raiseNotice(100, JText::_('EMR_COUPON_NOTFOUND'));
			}

			return new stdClass();
		}

		if(($coupon->use_num > 0) && ($coupon->used_num >= $coupon->use_num))
		{
			if($msg)
			{
				JError::raiseNotice(100, JText::_('EMR_COUPON_USEOUT'));
			}

			return new stdClass();
		}

		if(($coupon->discount_type == 'sum') && ($coupon->discount <= 0))
		{
			if($msg)
			{
				JError::raiseNotice(100, JText::_('EMR_COUPON_USEOUT'));
			}

			return new stdClass();
		}

		if($coupon->expired)
		{
			if($msg)
			{
				JError::raiseNotice(100, JText::_('EMR_COUPON_EXPIRED'));
			}

			return new stdClass();
		}

		if($coupon->user_ids)
		{
			$user = JFactory::getUser();
			$ids  = explode(",", $coupon->user_ids);
			if(!in_array($user->get('id'), $ids))
			{
				if($msg)
				{
					JError::raiseNotice(100, JText::_('EMR_COUPON_NOTALLOWED'));
				}

				return new stdClass();
			}
		}

		if($coupon->plan_ids)
		{
			if(!is_array($coupon->plan_ids))
			{
				$coupon->plan_ids = json_decode($coupon->plan_ids, TRUE);
			}
			if(is_array($coupon->plan_ids) && !in_array($plan_id, $coupon->plan_ids))
			{
				if($msg)
				{
					JError::raiseNotice(100, JText::_('EMR_COUPON_NOTVALID'));
				}

				return new stdClass();
			}
		}
		if($coupon->use_user > 0)
		{
			$user = JFactory::getUser();

			$sql = "SELECT count(*) FROM #__joomsubscription_coupons_history WHERE `coupon_id` = '{$coupon->id}' AND user_id = " . $user->get('id');
			$db->setQuery($sql);
			$num = $db->loadResult();

			if($num >= $coupon->use_user)
			{
				if($msg)
				{
					JError::raiseNotice(100, JText::_('EMR_COUPON_YOUUSED'));
				}

				return new stdClass();
			}
		}

		$out = 0;
		if($price)
		{
			if(strtoupper($coupon->discount_type) == 'PROCENT')
			{
				$out = ($coupon->discount * ($price / 100));
			}
			else
			{
				$out = ($price - $coupon->discount <= 0 ? $price : $coupon->discount);
			}
		}
		$coupon->discount_total = round($out, 2);

		return $coupon;
	}

	/*public static function applyCoupon($coupon, $price)
	{
		$db   = JFactory::getDBO();
		$user = JFactory::getUser();
		settype($coupon, 'integer');

		$sql = "UPDATE #__joomsubscription_coupons SET used_num = used_num + 1 WHERE `id` = " . $coupon;
		$db->setQuery($sql);
		$db->query();

		$sql = "SELECT * FROM #__joomsubscription_coupons WHERE `value` = '" . addslashes($coupon) . "'";
		$db->setQuery($sql);
		$rows = $db->loadObjectList();
		$row  = $rows[0];
		$sid  = JRequest::getInt('subscr_id');

		//$price = JRequest::getVar('price'.$sid);


		$sql = "INSERT INTO #__joomsubscription_coupons_history
		(`id` ,	`user_id` ,	`coupon_id` ,	`ctime` ,	`plan_id` ,	`price` , `discount` , `discount_type`, `subscription_id` )
			VALUES (NULL, '" . $user->get('id') . "', '{$row->id}', NOW(), '$sid', '$price', '{$row->discount}', '{$row->discount_type}', '')";
		$db->setQuery($sql);
		$db->query();
		$res     = new stdClass();
		$res->id = $db->insertid();

		if($row->discount_type == 'procent')
		{
			$out      = $price - ($price * ($row->discount / 100));
			$out      = round($out, 2);
			$res->out = $out;

			return $res;
		}
		else
		{
			if($row->discount_type == 'sum')
			{
				$new = round(($row->discount - $price), 2);
				if($new < 0)
				{
					$new = 0;
				}
				$sql = "UPDATE #__joomsubscription_coupons SET discount = '{$new}' WHERE `value` = '" . addslashes($coupon) . "'";
				$db->setQuery($sql);
				$db->query();
			}

			$out = $price - $row->discount;
			if($out < 0)
			{
				$out = 0;
			}
			$out      = round($out, 2);
			$res->out = $out;

			return $res;
		}
	}*/

	static public function getMUACoupon($sid, $item)
	{
		$code = md5($item->user_id . $item->plan_id . $sid . $item->ctime);
		$code = substr($code, 0, 10);

		return 'MUA-' . $sid . '-' . strtoupper($code);
	}
}