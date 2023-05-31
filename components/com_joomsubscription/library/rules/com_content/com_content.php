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

class JoomsubscriptionRuleCom_content extends JoomsubscriptionRule
{

	public function getDescription()
	{
		$out = array();
		if($this->params->get('type_article'))
		{
			$out[] = JText::_('EMR_ARTINDIVON');
		}
		if($this->params->get('type_user'))
		{
			$out[] = JText::_('EMR_USRINDIVON');
		}
		if($this->params->get('category'))
		{
			$out[] = JText::sprintf('EMR_CATRESTRICT', implode(', ', $this->params->get('category')));
		}
		if($this->params->get('ids'))
		{
			$out[] = JText::sprintf('EMR_ARTRESTRICT', $this->params->get('ids'));
		}
		if($this->params->get('time'))
		{
			$out[] = JText::sprintf('EMR_TIMESKIP', $this->params->get('time'));
		}


		return "<ul><li>" . implode("</li><li>", $out) . "</li></ul>";
	}

	public function isProtected()
	{
		$input = JFactory::getApplication()->input;
		$user  = JFactory::getUser();

		if($input->getCmd('view') != 'article')
		{
			return FALSE;
		}

		if(!($id = $input->getInt('id')))
		{
			return FALSE;
		}

		$article = JTable::getInstance('Content', 'JTable');
		$article->load($id);

		if($article->created_by == $user->id)
		{
			return FALSE;
		}

		if($this->params->get('time'))
		{
			$articletime = JDate::getInstance($article->created)->toUnix();
			$point       = time() - ($this->params->get('time') * 86400);

			if($articletime < $point)
			{
				return FALSE;
			}
		}

		$text = strip_tags($article->introtext . $article->fulltext);

		if(preg_match("/{JOOMSUBSCRIPTION SKIP}/", $text))
		{
			return FALSE;
		}

		if(preg_match("/{JCSBOT SKIP}/", $text))
		{
			return FALSE;
		}

		if($this->params->get('ids'))
		{
			$ids = JoomsubscriptionHelper::getValues($this->params->get('ids'), TRUE);
			if(in_array($id, $ids))
			{
				return TRUE;
			}
		}

		if(in_array($article->catid, (array)$this->params->get('category', array())))
		{
			return TRUE;
		}


		if($this->params->get('type_user'))
		{
			if(preg_match("/{JOOMSUBSCRIPTION U=([0-9 ,]*)}/iU", $text, $matches))
			{
				$user_ids = JoomsubscriptionHelper::getValues($matches[1], TRUE);
				//JArrayHelper::toInteger($user_ids);
				if(in_array($user->id, $user_ids))
				{
					return TRUE;
				}
			}

			if(preg_match("/{JCSBOT USER=([0-9 ,]*)}/iU", $text, $matches))
			{
				$user_ids = JoomsubscriptionHelper::getValues($matches[1], TRUE);
				//JArrayHelper::toInteger($user_ids);
				if(in_array($user->id, $user_ids))
				{
					return TRUE;
				}
			}
		}

		if($this->params->get('type_article'))
		{
			if(preg_match("/{JOOMSUBSCRIPTION P=([0-9 ,]*)}/iU", $text, $matches))
			{
				$plan_ids = JoomsubscriptionHelper::getValues($matches[1], TRUE);
				//JArrayHelper::toInteger($plan_ids);
				if(in_array($this->plan_id, $plan_ids))
				{
					return TRUE;
				}
			}

			if(preg_match("/{JCSBOT SUBSCRIPTION=([0-9 ,]*)}/iU", $text, $matches))
			{
				$plan_ids = JoomsubscriptionHelper::getValues($matches[1], TRUE);
				//JArrayHelper::toInteger($plan_ids);
				if(in_array($this->plan_id, $plan_ids))
				{
					return TRUE;
				}
			}
		}

		return FALSE;
	}
}
