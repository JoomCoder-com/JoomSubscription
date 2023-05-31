function addTitleInterface(data) {
	filename_div = 'filename' + data.id;
	
	var input_title = jQuery('<input>').attr(
			{
				'name': 'filetitle',
				'type': 'text',
			});
	
	var save_title = jQuery('<div>').attr(
			{
				'class': 'filetitlesave',
				
			}).text('save');
	
	var save_func = function(event) {
		data = event.data.obj;
		jQuery.ajax({
			type: "POST",
			url: Cobalt.field_call_url,
			dataType: 'json',
			data: {
				field_id: data.table.field_id,
				func: 'onSaveTitle',
				field: 'upload',
				record_id: data.table.record_id,
				id: data.id,
				text: input_title.val()
			}
		})
			.done(function( json ) {
				if(!json.success) {
					alert(json.error);
				}
				else {
					console.log('success');
					/*el.set('html', json.result);
					input_title.destroy;
					save_title.destroy;
					el.addEvent('click', function() {
						input_title.set('value', el.get('html'));
						el.set('html', '');
						el.adopt(input_title);
						el.adopt(save_title);
						el.removeEvents('click');
					});*/
				}
		});
		
		
		/*var req = new Request.JSON({
			url: Cobalt.field_call_url,
			method: 'post',
			autoCancel: true,
			data: {
				field_id: opt.field_id,
				func: 'onSaveTitle',
				field: 'upload',
				record_id: opt.record_id,
				id: data_id,
				text: input_title.value
			},
			onComplete: function(json) {
				if(!json.success) {
					alert(json.error);
				}
				else {
					el.set('html', json.result);
					input_title.destroy;
					save_title.destroy;
					el.addEvent('click', function() {
						input_title.set('value', el.get('html'));
						el.set('html', '');
						el.adopt(input_title);
						el.adopt(save_title);
						el.removeEvents('click');
					});
				}
			}
		}).send();*/
	};
	
	jQuery('#'+filename_div).append(input_title);
	jQuery('#'+filename_div).append(save_title);
	
	save_title.bind('click', {obj: data}, save_func);

	/*	save_title.addEvent('click', save_func.pass([el, opt]));

		input_title.removeEvents();
		input_title.addEvent('keydown', function(event) {
			if(event.key == 'enter') {
				save_func.pass([el, opt]);
			}
		});

		input_title.set('value', el.get('html'));
		el.set('html', '');
		el.adopt(input_title);
		el.adopt(save_title);
		el.removeEvents('click');*/

	//filename_div.addEvent('click', func_filename_title.pass([filename_div, this.options]));
}
