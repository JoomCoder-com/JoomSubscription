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

class JoomsubscriptionRuleCom_kunena extends JoomsubscriptionRule
{
	public function getRestrictionMessage($default)
	{

	}

	public function isProtected()
	{
		if($this->params->get('cats'))
		{
			switch($this->input->getCmd('view'))
			{
				case 'topics':
				case 'category':
					if(!$this->params->get('inlist') && in_array($this->input->getInt('catid'), $this->params->get('cats')))
					{
						return TRUE;
					}
					break;
				case 'topic':
					if(!(in_array($this->input->getCmd('layout'),
							array(
								'create', 'edit'
							))) &&
						in_array($this->input->getInt('catid'), $this->params->get('cats'))
					)
					{
						return TRUE;
					}
					break;
			}
		}

		if($this->params->get('submit'))
		{
			if($this->input->getInt('catid'))
			{
				if(in_array($this->input->getCmd('layout'),
						array(
							'create', 'edit'
						)) &&
					in_array($this->input->getInt('catid'), $this->params->get('submit'))
				)
				{
					return TRUE;
				}
			}
			else
			{
				if(JoomsubscriptionApi::hasSubscription($this->plan_id, '', NULL, FALSE, FALSE) == FALSE)
				{
					$js = "
						jQuery(function(){
							var ids = [" . implode(',', $this->params->get('submit')) . "];

							function checkcat(el) {
								if(jQuery.inArray(el.val(), ids)) {
									if(confirm('" . JText::_('KUN_NO_ACCES_JS') . "')) {
										window.location = '" . JoomsubscriptionApi::getLink('list', FALSE, $this->params->get('submit')) . "';
									}
								}
							}

							jQuery('#postcatid').change(function(){
								checkcat(jQuery(this));
							});

							checkcat(jQuery('#postcatid'));
						});
					";
					JFactory::getDocument()->addScriptDeclaration($js);
				}
			}
		}

		if($this->params->get('replay') && $this->input->getCmd('view') == 'topic' && in_array($this->input->getInt('catid'), $this->params->get('replay')))
		{
			switch($this->input->getCmd('layout'))
			{
				case 'reply':
					return TRUE;
					break;

				case 'default':
				default:
					if(JoomsubscriptionApi::hasSubscription($this->plan_id, '', NULL, FALSE, FALSE) == FALSE)
					{
						$js = "
							jQuery(document).ready(function(){
								jQuery('.kqreply').click(function(e){
									jQuery('.alert-joomsubscription').remove();

									var form = jQuery('#' + jQuery(this).attr('id') + '_form');
									if(form.length == 0) {
										return;
									}

									var alert = jQuery(document.createElement('div'))
										.addClass('alert alert-error alert-joomsubscription')
										.html('" . htmlentities(JText::_('KUN_NO_ACCES_REP_JS'), ENT_QUOTES, 'UTF-8') . "')
										.append('<p><a href=\"" . JoomsubscriptionApi::getLink('list', FALSE, $this->plan_id) . "\">" . JText::_('KU_BECOMEMEM') . "</a></p>')
									form.before(alert);
								});
							});
						";
						JFactory::getDocument()->addScriptDeclaration($js);
					}
					break;
			}

			if($this->input->getCmd('task') == 'post' && $this->input->getInt('parentid'))
			{
				return TRUE;
			}
		}


		return FALSE;
	}

	public function getDescription()
	{
		$out = array();

		if($this->params->get('cats'))
		{
			$out[] = JText::_('KUN_READ_RESTRICT', implode(', ', $this->params->get('cats')));
		}
		if($this->params->get('submit'))
		{
			$out[] = JText::_('KUN_SUB_RESTRICT', implode(', ', $this->params->get('submit')));
		}
		if($this->params->get('replay'))
		{
			$out[] = JText::_('KUN_REP_RESTRICT', implode(', ', $this->params->get('replay')));
		}

		return count($out) > 1 ? '<ul><li>' . implode('</li><li>', $out) . '</li></ul>' : implode('', $out);
		$db = JFactory::getDbo();
	}
}
