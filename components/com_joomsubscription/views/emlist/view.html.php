<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

jimport('mint.mvc.view.base');
class JoomsubscriptionViewEmList extends MViewBase
{
	function display($tpl = null)
	{
		$app = \Joomla\CMS\Factory::getApplication();
		$doc = \Joomla\CMS\Factory::getDocument();
		$model = $this->getModel();
		$user = \Joomla\CMS\Factory::getUser();

		$this->mparams = ($app->getMenu()->getActive() ? $app->getMenu()->getActive()->getParams() : new \Joomla\Registry\Registry());
		$this->_prepareDocument();

		$plan_ids = $app->input->getString('id', false);
		$group_ids = $plan_ids ? null : $this->mparams->get("groups");

		if(($plan_ids || $group_ids) && $this->mparams->get('link', 0)) {
			JError::raiseNotice(100, \Joomla\CMS\Language\Text::sprintf('EM_NOT_ALL_PLANS', JoomsubscriptionApi::getLink('emlist')));
		}

		$items = $model->getPlans($plan_ids, $group_ids);

		if(count($items) <= 0 && !$user->get('id'))
		{
			\Joomla\CMS\Factory::getApplication()->redirect(
				\Joomla\CMS\Router\Route::_(\Joomla\CMS\Component\ComponentHelper::getParams('com_joomsubscription')->get('general_login_url') .
					'&return=' . urlencode(base64_encode(\Joomla\CMS\Uri\Uri::getInstance()->toString())), FALSE)
			);
		}

		$prepare = JoomsubscriptionHelper::preparePlans($items, $model);
		$this->cats = $prepare['cats'];
		$this->items = $prepare['plans'];

		$this->params = \Joomla\CMS\Component\ComponentHelper::getParams('com_joomsubscription');
		$this->usersubs = JoomsubscriptionHelper::getUserPlans();

		$this->menu = Mint::loadLayout('links', JPATH_COMPONENT .'/layouts');

		parent::display($tpl);
	}

	private function _prepareDocument()
	{
		$app = \Joomla\CMS\Factory::getApplication();
		$doc = \Joomla\CMS\Factory::getDocument();
		$this->addTemplatePath(JPATH_COMPONENT.'/views/elements/');

		$this->mparams->set('page_title', $this->mparams->get('page_title', \Joomla\CMS\Language\Text::_('EPURCHASENEW')));
		$doc->setTitle($this->mparams->get('page_title'));

		if($meta_key = $this->mparams->get('menu-meta_keywords'))
		{
			$doc->setMetaData('keywords', $meta_key);
		}

		if($meta_desc = $this->mparams->get('menu-meta_description'))
		{
			$doc->setMetaData('description', $meta_desc);
		}

		//$pathway = $app->getPathway();
		//$pathway->addItem($this->mparams->get('page_title'));
	}
}

?>