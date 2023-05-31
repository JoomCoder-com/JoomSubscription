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

class JoomsubscriptionActionHikashop extends JoomsubscriptionAction
{
	public function onActive($subscription)
	{
	}

	public function onDisactive($subscription)
	{
		if($this->params->get('status'))
		{
			if(!$subscription->gateway_id || !preg_match("/^[0-9]*\-[0-9]*$/iU", $subscription->gateway_id))
			{
				return;
			}

			if(!include_once(JPATH_ROOT . '/administrator/components/com_hikashop/helpers/helper.php'))
			{
				return;
			}

			list($order_id, $product_id) = explode('-', $subscription->gateway_id);

			$order               = new stdClass();
			$order->order_id     = $order_id;
			$order->order_status = $this->params->get('status');

			$orderClass = hikashop_get('class.order');
			$orderClass->save($order);
		}
	}

	public function getDescription()
	{
		$db  = JFactory::getDbo();
		$out = array();

		if($this->params->get('products'))
		{
			$db->setQuery("SELECT product_name FROM #__hikashop_product WHERE product_id IN (" . implode(', ', $this->params->get('products')) . ")");
			$products = $db->loadColumn();
			$out[]    = sprintf('<p>%s</p>%s',
				JText::plural('EM_HIKA_ACTION_DESCRIPTION', count($products)),
				count($products) > 1 ? sprintf('<ul><li>%s</li></ul>', implode('</li><li>', $products)) : implode('', $products)
			);
		}
		if($this->params->get('category'))
		{
			$db->setQuery("SELECT category_name FROM #__hikashop_category WHERE category_id IN (" . implode(', ', $this->params->get('category')) . ")");
			$cats  = $db->loadColumn();
			$out[] = sprintf('<p>%s</p>%s',
				JText::plural('EM_HIKA_ACTION_DESCRIPTION_CAT', count($cats)),
				count($cats) > 1 ? sprintf('<ul><li>%s</li></ul>', implode('</li><li>', $cats)) : implode('', $cats)
			);
		}

		return implode("", $out);
	}
}
