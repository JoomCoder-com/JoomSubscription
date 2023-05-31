<?php
/**
 * JoomSubscription by JoomCoder
 * a component for Joomla! 3.0 CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2012 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die();

class JoomsubscriptionControllerEmSale extends MControllerForm
{
	public function getModel($name = 'EmSale', $prefix = 'JoomsubscriptionModel', $config = array())
	{
		return MModelBase::getInstance($name, $prefix, $config);
	}

	protected function allowEdit($data = array(), $key = 'id')
	{
		return TRUE;
	}

	protected function allowAdd($data = array())
	{
		return TRUE;
	}

	public function save($key = NULL, $urlVar = NULL)
	{
		$app  = JFactory::getApplication();
		$form = $app->input->get('jform', array(), 'array');
		if(empty($form['id']))
		{
			$context = "$this->option.edit.$this->context";
			$app->setUserState($context . '.data', $form);
			if(empty($form['user_id']) || empty($form['plan_id']))
			{
				JError::raiseWarning(100, JText::_('EMR_PLANCREATENOTALLSET'));
				$app->redirect(JRoute::_('index.php?option=com_joomsubscription&view=emsale&layout=edit', FALSE));

				return;
			}

			$recordId = JoomsubscriptionApi::addSubscription($form['user_id'], $form['plan_id'], $form['published'], $form['gateway'], NULL, $form['gateway_id']);

			if(!$recordId)
			{

				JError::raiseWarning(100, JText::_('EMR_CREATESUBSCRWRONG'));
				$app->redirect(JRoute::_('index.php?option=com_joomsubscription&view=emsale&layout=edit', FALSE));

				return FALSE;
			}

			$model = $this->getModel();
			$task  = $this->getTask();
			// Redirect the user and adjust session state based on the chosen task.
			switch($task)
			{
				case 'apply':
					// Set the record data in the session.
					$this->holdEditId($context, $recordId);
					$app->setUserState($context . '.data', NULL);
					$model->checkout($recordId);

					// Redirect back to the edit screen.
					$this->setRedirect(JRoute::_('index.php?option=com_joomsubscription&view=emsale' . $this->getRedirectToItemAppend($recordId), FALSE));
					break;

				case 'save2new':
					// Clear the record id and data from the session.
					$this->releaseEditId($context, $recordId);
					$app->setUserState($context . '.data', NULL);

					// Redirect back to the edit screen.
					$this->setRedirect(JRoute::_('index.php?option=com_joomsubscription&view=emsale' . $this->getRedirectToItemAppend(NULL), FALSE));
					break;

				default:
					// Clear the record id and data from the session.
					$this->releaseEditId($context, $recordId);
					$app->setUserState($context . '.data', NULL);

					// Redirect to the list screen.
					$this->setRedirect(JRoute::_('index.php?option=com_joomsubscription&view=emsales' . $this->getRedirectToListAppend(), FALSE));
					break;
			}

			return TRUE;
		}

		return parent::save($key, $urlVar);
	}

}