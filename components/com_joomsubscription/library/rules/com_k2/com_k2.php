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

class JoomsubscriptionRuleCom_k2 extends JoomsubscriptionRule
{
	public function isProtected()
	{
		$item_cat = NULL;
		$db       = JFactory::getDbo();
		$id       = $this->input->getInt('id');

		if($this->input->getCmd('task') == 'download' && $id)
		{
			$db->setQuery("SELECT itemID FROM #__k2_attachments WHERE id = " . $id);
			$id = $db->loadResult();
		}

		if($this->input->getCmd('view') == 'item' && $id)
		{
			$db->setQuery("SELECT catid FROM #__k2_items WHERE id = " . $id);
			$item_cat = $db->loadResult();
		}

		if($this->params->get('cats'))
		{
			if(
				!$this->params->get('inlist') &&
				$this->input->getCmd('view') == 'itemlist' &&
				in_array($this->input->getInt('id'), $this->params->get('cats'))
			)
			{
				return TRUE;
			}

			if($item_cat && in_array($item_cat, $this->params->get('cats')))
			{
				return TRUE;
			}
		}

		$js = array();

		if($this->params->get('comment') && $item_cat && in_array($item_cat, $this->params->get('comment')))
		{
			$js[] = "jQuery('.itemCommentsForm').html('<div class=\"alert alert-warning\">" .
				addslashes(JText::_('K2_NO_COMM')) . "<br><a href=\"" .
				JoomsubscriptionApi::getLink('list', FALSE, $this->plan_id) . "\">" .
				addslashes(JText::_('K2_BECOMEMEM')) . "</a></div>');";
		}

		if($item_cat && $this->params->get('download') && in_array($item_cat, $this->params->get('download')))
		{
			if($this->input->getCmd('task') == 'download')
			{
				return TRUE;
			}
			$lock = sprintf('<img src="%s/media/mint/icons/16/lock.png" data-lock title="%s"> ', JUri::root(), addslashes(JText::_('K2_DOWNLOAD_TEXT')));
			$js[] = "
			jQuery('a[href*=\"option=com_k2&view=item&task=download\"]').before('{$lock}');
			jQuery('img[data-lock]').tooltip();
			";
		}
		if(!empty($js))
		{
			JFactory::getDocument()->addScriptDeclaration('jQuery(function(){' . implode("\n", $js) . '});');
		}

		return FALSE;
	}

	public function getDescription()
	{
		$out = array();

		if($this->params->get('cats'))
		{
			$out[] = JText::_('K2_READ_RESTRICT', implode(', ', $this->params->get('cats')));
			$out[] = JText::sprintf('K2_LIST_RESTRICT', $this->params->get('inlist') ? 'Yes' : 'No');
		}
		if($this->params->get('comment'))
		{
			$out[] = JText::_('K2_REP_RESTRICT', implode(', ', $this->params->get('comment')));
		}

		if($this->params->get('download'))
		{
			$out[] = JText::_('K2_DOWN_RESTRICT', implode(', ', $this->params->get('download')));
		}

		return count($out) > 1 ? '<ul><li>' . implode('</li><li>', $out) . '</li></ul>' : implode('', $out);
		$db = JFactory::getDbo();
	}
}
