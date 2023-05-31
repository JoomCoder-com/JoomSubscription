<?php
/**
 *
 * @license   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 */
defined('_JEXEC') or die('Restricted access');

class JoomsubscriptionViewsEMInvoiceToHtml extends JViewHtml
{
	function render()
	{
		switch($this->getLayout())
		{
			case 'form':
				$this->form = $this->model->getForm();
				$params = JComponentHelper::getParams('com_joomsubscription');

				if($params->get('vies') == 0)
				{
					$this->form->removeField('vies', 'fields');
				}

				if($params->get('tax_id_rec', 1) == 1)
				{
					$this->form->setFieldAttribute('tax_id', 'required', true);
				}

				$this->defaults = JFactory::getApplication()->getUserState('com_joomsubscription.invoiceto.data', array());
				break;
			case 'text':
				$this->data = $this->model->getText(JFactory::getApplication()->input->getInt('id'));
				break;
		}

		return parent::render();
	}
	function getName()
	{
		return 'eminvoice';
	}

}