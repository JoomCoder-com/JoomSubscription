<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

jimport('mint.mvc.view.base');
class JoomsubscriptionViewEmMua extends MViewBase
{
	function display($tpl = NULL)
	{
		$app   = JFactory::getApplication();
		$user  = JFactory::getUser();
		$model = MModelBase::getInstance('EmMua', 'JoomsubscriptionModel');
		$doc   = JFactory::getDocument();

		$app->getPathway()->addItem(JText::_('EMR_MUA_TITLE'));
		$doc->setTitle(JText::_('EMR_MUA_TITLE'));


		$sid = $app->input->getInt('subscr_id');
		if(empty($sid))
		{
			JError::raiseWarning(404, JText::_('EMR_MUA_PLANNOTSELECTED'));
			$app->redirect(JRoute::_('index.php?option=com_joomsubscription&view=history', FALSE));

			return;
		}

		$item = JoomsubscriptionHelper::getUserSubscr($sid);

		if(empty($item->id))
		{
			JError::raiseWarning(404, JText::_('EMR_MUA_PLAN_NOTFOUND'));
			$app->redirect(JRoute::_('index.php?option=com_joomsubscription&view=history', FALSE));

			return;
		}

		if(!empty($item->parent))
		{
			JError::raiseWarning(404, JText::_('EMR_MUA_PLAN_CHILD'));
			$app->redirect(JRoute::_('index.php?option=com_joomsubscription&view=history', FALSE));

			return;
		}

		$mua_param = new JRegistry($item->plan_params);
		$allowed   = $mua_param->get('properties.muaccess');
		if(empty($allowed))
		{
			JError::raiseWarning(404, JText::_("EMR_MUA_NOMUA"));
			$app->redirect(JRoute::_('index.php?option=com_joomsubscription&view=history', FALSE));

			return;
		}

		$mua        = $model->getSubscrMUA($sid);
		$mua_coupon = JoomsubscriptionHelperCoupon::getMUACoupon($sid, $item);
		$number     = (count($mua) > 0) ? count($mua) : JText::_('None');

		if($allowed > count($mua))
		{
			$instruction = JText::_("LAYOUT_MUA_INSTRUCTIONS");
		}
		else
		{
			$instruction = JText::_("LAYOUT_MUA_LIMIT_ALERT");
		}

		$instruction = str_replace("[MUA_COUPON]", '<span class="label label-success">' . $mua_coupon . '</span>', $instruction);
		$instruction = str_replace("[USERNUMBER]", $number, $instruction);
		$instruction = str_replace("[ALLOWEDNUMBER]", $allowed, $instruction);

		if(!$item->active || $item->expired)
		{
			$instruction = '';
			JError::raiseNotice(100, JText::_("EMR_MUA_EXPIRED"));
		}

		$this->item        = $item;
		$this->instruction = $instruction;
		$this->mua         = $mua;
		$this->params      = JComponentHelper::getParams('com_joomsubscription');

		parent::display($tpl);
	}


}

?>