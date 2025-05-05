<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

class JoomsubscriptionRuleCom_joommedia extends JoomsubscriptionRule
{
	public function isProtected()
	{
		$db = JFactory::getDbo();
		$id = $this->input->getInt('id', FALSE);

		if($this->input->getCmd('view') != 'mediaitem' && !$id)
		{
			return FALSE;
		}

		if($this->params->get('ids', FALSE))
		{
			$ids = JoomsubscriptionHelper::getValues($this->params->get('ids'), TRUE);
			if(in_array($id, $ids))
			{
				return TRUE;
			}
		}

		if($this->params->get('type_article'))
		{
			$item = $db->setQuery("SELECT description FROM #__joommedia_media WHERE id = " . $id);
			$item = $db->loadResult();
			$text = strip_tags($item);
			if(preg_match("/{JOOMSUBSCRIPTION P=([0-9 ,]*)}/iU", $text, $matches))
			{
				$plan_ids = JoomsubscriptionHelper::getValues($matches[1], TRUE);
				ArrayHelper::toInteger($plan_ids);
				if(in_array($this->plan_id, $plan_ids))
				{
					return TRUE;
				}
			}

			if(preg_match("/{JOOMSUBSCRIPTION SKIP}/", $text))
			{
				return FALSE;
			}
		}

		if($this->params->get('catids'))
		{
			$db->setQuery("SELECT category_id FROM #__joommedia_category_map WHERE element_id = " . $id);
			$item_cats = $db->loadColumn();

			if($item_cats)
			{
				foreach($item_cats as $icat)
				{
					if(in_array($icat, $this->params->get('catids')))
					{
						return TRUE;
					}
				}
			}
		}

		return FALSE;
	}

	public function getDescription()
	{
		$out = array();

		if($this->params->get('catids'))
		{
			$out[] = JText::sprintf('JOOMMEDIA_READ_RESTRICT', implode(', ', $this->params->get('catids')));
		}
		if($this->params->get('ids'))
		{
			$out[] = JText::sprintf('JOOMMEDIA_LIST_RESTRICT', $this->params->get('ids'));
		}

		return count($out) > 1 ? '<ul><li>' . implode('</li><li>', $out) . '</li></ul>' : implode('', $out);
		$db = JFactory::getDbo();
	}
}
