<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class JoomsubscriptionControllerEmSales extends MControllerAdmin
{
	public function &getModel($name = 'EmSale', $prefix = 'JoomsubscriptionModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => TRUE));

		return $model;
	}

	protected function postDeleteHook(MModelBase $model, $id = NULL)
	{
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		$id  = implode(',', $cid);
		$db  = JFactory::getDbo();

		$db->setQuery("DELETE FROM `#__joomsubscription_url_history` WHERE subscription_id IN({$id})");
		$db->execute();

		$db->setQuery("DELETE FROM `#__joomsubscription_coupons_history` WHERE subscription_id IN({$id})");
		$db->execute();
	}
}
