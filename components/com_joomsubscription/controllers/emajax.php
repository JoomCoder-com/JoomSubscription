<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 *
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

jimport('mint.mvc.controller.form');
jimport('mint.forms.helper');

class JoomsubscriptionControllerEmAjax extends MControllerForm
{
	public function selectorShow()
	{
		echo json_encode(
			array(
				'html' => JoomsubscriptionSelectorHelper::render('', $this->input->getString('plans'), $this->input->getString('groups'), array(), 0, $this->input->get('layout', 'list'))
			)
		);
		JFactory::getApplication()->close();
	}

	public function cleanSerials()
	{
		$db = JFactory::getDbo();
		$db->setQuery(sprintf("DELETE FROM #__joomsubscription_serial WHERE field_id = %d and active = 0", $this->input->get('field_id')));
		$db->execute();

		echo json_encode(
			array(
				'success' => 1
			)
		);

		JFactory::getApplication()->close();
	}

	public function fieldparams()
	{

		$app = JFactory::getApplication();

		$type = $app->input->get('field_type');
		if(empty($type))
		{
			echo JText::_('EMFIELDNOTSELECTED');
			$app->close();
		}

		JoomsubscriptionFieldsHelper::load_lang($type);

		$table = JTable::getInstance('EmField', 'JoomsubscriptionTable');
		$table->load($app->input->get('field_id'));
		$table->params = new JRegistry(json_decode($table->params, TRUE));

		$params = new JForm('params', array('control' => 'params'));
		$params->loadFile(JPATH_COMPONENT . "/library/fields/{$type}/{$type}.xml");

		$form = MFormHelper::renderForm($params, $table->params->toArray(), 'params', MFormHelper::FIELDSET_SEPARATOR_FIELDSET, MFormHelper::STYLE_CLASSIC, MFormHelper::GROUP_SEPARATOR_NONE);

		if(empty($form))
		{
			echo JText::_('EMFIELDNOFORM');
			$app->close();
		}

		echo $form;

		$app->close();
	}

	public function getstates()
	{
		$id   = strtoupper($this->input->get('id'));
		$name = $this->input->getString('name', 'invoiceto[fields][state]');
		$db   = JFactory::getDbo();

		$db->setQuery("SELECT id as value, label as text, state FROM #__joomsubscription_states WHERE country ='{$id}'");
		$states = $db->loadObjectList();

		foreach($states AS &$s)
		{
			$s->text = Mint::_('STATE_'.$s->state, $s->text);
		}

		if($states)
		{
			array_unshift($states, JHtml::_('select.option', '', JText::_('EMR_SELECT_STATE')));
			echo JHtml::_('select.genericlist', $states, $name, '', 'value', 'text', $this->input->get('default'));
		}


		JFactory::getApplication()->close();
	}

	public function deleteRule()
	{
		$id    = $this->input->post->get('id');
		$rules = JTable::getInstance('EmRules', 'JoomsubscriptionTable');
		$rules->load($id);
		$rules->delete();

		echo '[1]';

		JFactory::getApplication()->close();
	}

	public function setRuleForm()
	{
		$array = $this->input->post->get('rules', array(), 'array');

		if(empty($array))
		{
			JoomsubscriptionAjaxHelper::error(JText::_('EMER_RULENODATA'));
		}

		foreach($array AS $key => $val)
		{
			if(is_array($val) && !(implode('', $val)))
			{
				unset($array[$key]);
			}
		}


		$rules = JTable::getInstance('EmRules', 'JoomsubscriptionTable');

		if(!empty($array['id']))
		{
			$rules->load($array['id']);
			$rules->rule       = json_encode($array);
			$rules->option     = ($this->input->post->get('component') == 'default' ? $array['component'] : $this->input->post->get('component'));
			$rules->controller = $this->input->post->get('component');
			$rules->store();
		}
		else
		{
			$data['option']     = ($this->input->post->get('component') == 'default' ? $array['component'] : $this->input->post->get('component'));
			$data['controller'] = $this->input->post->get('component');
			$data['rule']       = json_encode($array);
			$data['plan_id']    = $this->input->post->get('plan_id');

			$db    = JFactory::getDbo();
			$query = $db->getQuery(TRUE)
				->select('name')
				->from('#__extensions')
				->where($db->quoteName('type') . ' = ' . $db->quote('component'))
				->where($db->quoteName('element') . ' = ' . $db->quote($data['option']));
			$db->setQuery($query);

			$data['name'] = $db->loadResult();

			$rules->load($data);

			if($rules->id)
			{
				JoomsubscriptionAjaxHelper::error(JText::_('EMER_RULEEXISTS'));
			}

			if(!$rules->save($data))
			{
				JoomsubscriptionAjaxHelper::error(JText::_('EMER_CANNOPTSAVERUL'));
			}
		}


		$out = array('html' => JoomsubscriptionRulesHelper::description($rules), 'id' => $rules->id);
		echo json_encode($out);

		JFactory::getApplication()->close();
	}

	public function getRuleForm()
	{

		$rules     = JFolder::folders(JPATH_ROOT . '/components/com_joomsubscription/library/rules/');
		$component = $this->input->get('component');

		if(preg_match("/^[0-9]*$/iU", $component))
		{
			$db = JFactory::getDbo();
			$db->setQuery("SELECT * FROM #__joomsubscription_plans_rules WHERE id = " . $component);
			$result    = $db->loadObject();
			$component = $result->controller;
		}

		if(!in_array($component, $rules))
		{
			$component = 'default';
		}

		$file = JPATH_ROOT . '/components/com_joomsubscription/library/rules/' . $component . '/' . $component . '.xml';
		if(!JFile::exists($file))
		{
			echo "File not found: {$file}";
		}

		JoomsubscriptionRulesHelper::load_lang($component);

		$form = new JForm('params', array('control' => 'rules'));
		$form->loadFile($file, TRUE, 'fields');
		if(!empty($result->id))
		{
			$form->bind(array('rule' => json_decode($result->rule)));
		}

		$out = MFormHelper::renderGroup($form, array(), 'rule', MFormHelper::FIELDSET_SEPARATOR_HEADER, MFormHelper::STYLE_TABLE);

		if($this->input->get('component') == 'com_cobalt')
		{
			$out = '<p>' . JText::_('X_COBALTBASIC') . '</p>' . $out;
		}

		if(!empty($result->id))
		{
			$out .= "<input type=hidden name=rules[rule][id] value={$result->id}>";
		}


		echo $out;

		JFactory::getApplication()->close();
	}

	public function getActionForm()
	{
		$type = $this->input->getString('type', 'sql');

		if(preg_match("/^[0-9]*$/iU", $type))
		{
			$db = JFactory::getDbo();
			$db->setQuery("SELECT * FROM #__joomsubscription_plans_actions WHERE id = " . $type);
			$result = $db->loadObject();
			$type   = $result->type;
		}

		$file = JPATH_ROOT . '/components/com_joomsubscription/library/actions/' . $type . '/' . $type . '.xml';
		if(!JFile::exists($file))
		{
			echo "File not found: {$file}";
		}

		JoomsubscriptionActionsHelper::load_lang($type);

		$form = new JForm('params',
			array(
				'control' => 'actions'
			));

		$form->loadFile($file, TRUE, 'fields');
		if(!empty($result->id))
		{
			$form->bind(array('action' => json_decode($result->action)));
		}

		$out = MFormHelper::renderGroup($form, array(), 'action', MFormHelper::FIELDSET_SEPARATOR_NONE, MFormHelper::STYLE_TABLE);
		if(!empty($result->id))
		{
			$out .= "<input type=hidden name=actions[action][id] value={$result->id}>";
		}

		echo $out;

		JFactory::getApplication()->close();
	}

	public function sendActionForm()
	{
		$array = $this->input->post->get('actions', array(), 'array');
		MintArrayHelper::clean_r($array);

		if(empty($array))
		{
			JoomsubscriptionAjaxHelper::error(JText::_('EMER_ACTIONNODATA'));
		}

		$actions = JTable::getInstance('EmActions', 'JoomsubscriptionTable');

		if(!empty($array['id']))
		{
			$actions->load($array['id']);
			$actions->action = json_encode($array);
			$actions->store();
		}
		else
		{
			$data['type']    = $this->input->getString('type');
			$data['action']  = json_encode($array);
			$data['plan_id'] = $this->input->getInt('plan_id');

			$actions->load($data);

			if($actions->id)
			{
				JoomsubscriptionAjaxHelper::error(JText::_('EMER_ACTIONEXISTS'));
			}

			if(!$actions->save($data))
			{
				JoomsubscriptionAjaxHelper::error(JText::_('EMER_CANNOTSAVEACTION'));
			}
		}


		$out = array('html' => JoomsubscriptionActionsHelper::description($actions), 'id' => $actions->id);
		echo json_encode($out);

		JFactory::getApplication()->close();
	}

	public function deleteAction()
	{
		$id      = $this->input->getInt('id');
		$actions = JTable::getInstance('EmActions', 'JoomsubscriptionTable');
		$actions->load($id);
		$actions->delete();

		echo '[1]';

		JFactory::getApplication()->close();
	}

	public function mainJS()
	{
		header('content-type: application/javascript');

		include JPATH_ROOT . '/components/com_joomsubscription/library/js/main.js';

		JFactory::getApplication()->close();
	}

	public function getSelectorList()
	{
		$id        = $this->input->getString('id', NULL);
		$group_ids = $this->input->getString('groups_ids', array());

		echo JoomsubscriptionSelectorHelper::selector_list($id, $group_ids);

		JFactory::getApplication()->close();
	}
}



