<?php
/**
 *
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 */
defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionViewsEmSelectorHtml extends JViewHtml
{
	function render()
	{
		$this->params  = JComponentHelper::getParams('com_joomsubscription');

		$layout = $this->getLayout();
		$this->{'_' . $layout}();

		return parent::render();
	}

	private function _list()
	{
		include_once JPATH_ROOT.'/components/com_joomsubscription/models/emlist.php';
		$model = new JoomsubscriptionModelEmList();
		$items       = $model->getPlans($this->plans, $this->groups);
		$prepare     = JoomsubscriptionHelper::preparePlans($items);
		$this->cats  = $prepare['cats'];
		$this->items = $prepare['plans'];
	}

	private	function _confirm()
	{
		include_once JPATH_ROOT.'/components/com_joomsubscription/models/empayment.php';
		$model = new JoomsubscriptionModelEmPayment();

		$app = JFactory::getApplication();

		$this->plan = JoomsubscriptionApi::getPreparedPlan($app->input->get('id'));
		$this->coupon  = JoomsubscriptionHelperCoupon::getCoupon($app->input->get('coupon'), $this->plan->id, $this->plan->total, TRUE);
		$this->coupons = $model->getCouponsNumber($app->input->get('id', 0));
	}

	private	function _info()
	{
		$this->usersubs = JoomsubscriptionHelper::getUserPlans();
	}
	private function _default()
	{
	}
	function getName()
	{
		return 'emselector';
	}

}
