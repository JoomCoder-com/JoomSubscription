<?php
/**
 * by JoomCoder
 * a component for Joomla! 3.x CMS (http://www.joomla.org)
 * Author Website: https://www.joomcoder.com/
 * @copyright Copyright (C) 2007-2014 JoomCoder (https://www.joomcoder.com/). All rights reserved.
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

class JFormFieldBmmlists extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'Bmmlists';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 * @since    1.6
	 */
	protected function getInput()
	{
		$name = md5($this->name);
		$out = "
			<div id=\"bmm-lists-{$name}\"></div>
			<script>
				(function($){
					var container = $('#bmm-lists-{$name}');
					var bar = '<div class=\"progress progress-striped active\"><div class=\"bar\" style=\"width: 100%;\"></div></div>';
					var button = $(document.createElement('div'))
						.attr({
							class:'btn btn-get-list-{$name}',
							type: 'button'
						})
						.html('".JText::_('BMMGETLISTS')."');

					container.html(button);
					update();

					function update() {
						$('.btn-get-list-{$name}').click(function(){
							if(!$('#actions_action_api_key').val() || !$('#actions_action_api_pass').val()) {
								alert('".JText::_('BMM_ENTERDETAILS')."');
								return;
							}

							container.html(bar);

							$.ajax({
								url: '".JRoute::_('index.php?option=com_joomsubscription&task=emapi.action', false)."',
								type: 'post',
								dataType: 'json',
								data: {
									method: 'getlists',
									action: 'bmm',
									name: $('#actions_action_api_key').val(),
									pass: $('#actions_action_api_pass').val(),
									fname: '{$this->name}'
								}
							})
							.done(function(json){
								if(json.success) {
									container.html(json.result);
									return true;
								}
								container.html(button);
								container.append('<div class=\"alert alert-warning\">'+json.error+'</div>');
								update();
							})
							.fail(function(){
								container.html(button);
								update();
							});
						});
					}
				}(jQuery))
			</script>
		";

		return $out;
	}
}