<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die();

class JoomsubscriptionViewEmCoupon extends MViewBase
{

	protected $state;

	protected $item;

	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');
		$this->user = JFactory::getUser();

		if(!empty($this->item->plan_ids))
		{
			$this->form->setValue('plan_ids', null, json_decode($this->item->plan_ids));
		}

		// Check for errors.
		if(count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));

		JToolBarHelper::title(($isNew ? JText::_('ENEWCOUPON') : JText::_('EEDITCOUPON').': '.$this->item->value), ($isNew ? 'coupon_new.png' : 'coupon_edit.png'));

		if (!$checkedOut){
			JToolBarHelper::apply('coupon.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('coupon.save', 'JTOOLBAR_SAVE');
			JToolBarHelper::custom('coupon.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			if(!$isNew) JToolBarHelper::custom('coupon.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}
		JToolBarHelper::cancel('coupon.cancel', 'JTOOLBAR_CANCEL');
	}

	private function _prepareDocument()
	{
		$app	= JFactory::getApplication();
		$doc = JFactory::getDocument();
		$menus	= $app->getMenu();
		$pathway = $app->getPathway();
		$title = FALSE;

		if($this->item->id)
		{
			$title = JText::sprintf('EEDITCOUPON', $this->item->value);
			$pathway->addItem(strip_tags($title));
		}
		else
		{
			$title = JText::_('ENEWCOUPON');
			$pathway->addItem(strip_tags($title));
		}

		$this->appParams = $app->getParams();
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$title .= ' - '.$menu->getParams()->get('page_title', $menu->title);
			$this->appParams->def('page_heading', $title);
		}
		// Check for empty title and add site name if param is set
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		if (empty($title)) {
			$title = $this->item->value;
		}
		$doc->setTitle($title);
	}
}
