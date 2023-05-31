<label class="checkbox">
	<input id="field<?php echo $this->id ?>" value="1" name="fields[<?php echo $this->id ?>]" <?php echo $this->default ? 'checked="true"' : '' ?>  type="checkbox">
	<div class="alert alert-info">
		<?php echo $this->note; ?>
	</div>
</label>


<script>
	(function($){
		$('#field<?php echo $this->id ?>').change(function(){
			$('#formsubscr').submit();
		});
	}(jQuery));
</script>