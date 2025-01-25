<?php
/**
 * Created by PhpStorm.
 * User: Sergey
 * Date: 6/2/16
 * Time: 21:54
 */

$class     = !empty($this->class) ? ' class="radio ' . $this->class . '"' : ' class="radio"';
$required  = $this->required ? ' required aria-required="true"' : '';
$autofocus = $this->autofocus ? ' autofocus' : '';
$disabled  = $this->disabled ? ' disabled' : '';
$readonly  = $this->readonly;

$options = $this->getOptions();
?>

<style>
	.<?php echo $this->id ?> div.btn-group[data-toggle=buttons-radio] input[type=radio] {
		display: block;
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		opacity: 0;
	}
</style>

<div class="<?php echo $this->id ?>">
	<div class="btn-group" data-toggle="buttons-radio">
		<?php foreach($options as $i => $option): ?>
			<?php
			$checked = ((string)$option->value == (string)$this->value) ? ' checked="checked"' : '';
			$active  = ((string)$option->value == (string)$this->value) ? ' active btn-primary' : '';
			$class   = !empty($option->class) ? ' class="' . $option->class . '"' : '';

			$disabled = !empty($option->disable) || ($readonly && !$checked);

			$disabled = $disabled ? ' disabled' : '';

			// Initialize some JavaScript option attributes.
			$onclick  = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';
			$onchange = !empty($option->onchange) ? ' onchange="' . $option->onchange . '"' : '';
			$value    = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
			?>

			<button id="btn-<?php echo $this->id . $i; ?>" type="button" class="btn <?php echo $active ?>">
				<?php echo JText::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)) ?>
				<input type="radio" id="<?php echo $this->id . $i ?>" name="<?php echo $this->name ?>" value="<?php echo $value ?>" <?php echo $checked . $class . $required . $onclick . $onchange . $disabled ?>>
			</button>
		<?php endforeach; ?>
	</div>
</div>

<script>
	(function($){
		var btns = $('.<?php echo $this->id ?> button');
		var inputs = $('.<?php echo $this->id ?> button input');
		$.each(btns, function(key, val){
			var btn = $(val);
			var input = $('input', btn);
			if(input.is(':checked')) {
				btn.removeClass();
				btn.addClass('btn active btn-primary');
			} else {
				btn.removeClass();
				btn.addClass('btn');
			}
			btn.click(function(){
				btns.removeClass();
				btns.addClass('btn');
				btn.addClass('active btn-primary');
				inputs.removeAttr('checked', 'checked');
				input.attr('checked', 'checked');
			});
		});
	}(jQuery));
</script>