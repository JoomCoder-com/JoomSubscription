<label class="checkbox">
	<input id="field<?php echo $this->id ?>" value="1" name="fields[<?php echo $this->id ?>]" <?php echo $this->default ? 'checked="true"' : '' ?>  type="checkbox">
	<?php echo sprintf($this->params->get('params.name'), JoomsubscriptionApi::getPrice($this->params->get('params.price'), $this->plan->params)) ?>
</label>
<script>
	(function($){
		$('#field<?php echo $this->id ?>').change(function(){
			$('#formsubscr').submit();
		});
	}(jQuery));
</script>