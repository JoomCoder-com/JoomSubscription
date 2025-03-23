jQuery(document).ready(function($){
	function Joomsubscription() {
		var floatnum = [];

		var $this = this;

		this.submitform = function(task, form) {
			if(typeof(form) === 'undefined') {
				form = document.getElementById('adminForm');
			}

			if(typeof(task) !== 'undefined' && task !== "") {
				form.task.value = task;
			}

			// Submit the form.
			if(typeof form.onsubmit == 'function') {
				form.onsubmit();
			}
			if(typeof form.fireEvent == "function") {
				form.fireEvent('submit');
			}
			form.submit();
		};

		this.setAndSubmit = function(el, val) {
			var elm = jQuery('#' + el);
			elm.val(val);
			elm.parents('form').submit();
		};

		this.submitbutton = function(pressbutton) {
			$this.submitform(pressbutton);
		};

		this.redrawBS = function() {
			$('*[rel=tooltip]').tooltip();
			$('*[rel=popover]').popover();
			$('.tip-bottom').tooltip({placement: "bottom"});

			jQuery('.radio.btn-group label').addClass('btn');
			jQuery(".btn-group label:not(.active)").click(function() {
				var label = jQuery(this);
				var input = jQuery('#' + label.attr('for'));

				if(!input.prop('checked')) {
					label.closest('.btn-group').find("label").removeClass('active btn-success btn-danger btn-primary');
					if(input.val() == '') {
						label.addClass('active btn-primary');
					} else if(input.val() == 0) {
						label.addClass('active btn-danger');
					} else {
						label.addClass('active btn-success');
					}
					input.prop('checked', true);
				}
			});
			jQuery(".btn-group input[checked=checked]").each(function(e) {
				if(jQuery(this).val() == '') {
					jQuery("label[for=" + jQuery(this).attr('id') + "]").addClass('active btn-primary');
				} else if(jQuery(this).val() == 0) {
					jQuery("label[for=" + jQuery(this).attr('id') + "]").addClass('active btn-danger');
				} else {
					jQuery("label[for=" + jQuery(this).attr('id') + "]").addClass('active btn-success');
				}
			});
		};

		this.formatInt = function(el, max) {
			var cur = el.value;
			reg = /[^\d\.]+/;
			cur = cur.replace(reg, "");
			if(max && parseInt(cur) > max) {
				cur = max;
			}
			el.value = cur;
		};

		this.formatFloat = function(obj, decimal, max) {
			if(floatnum[obj.id] == obj.value) {
				return;
			}

			var cur = obj.value;

			cur = cur.replace(',', '.');
			cur = cur.replace('..', '.');

			if(decimal > 0) {
				reg = /[^\d\.]+/;
			} else {
				reg = /[^\d]+/;
			}
			cur = cur.replace(reg, '');

			if((cur.lastIndexOf('.') >= 0) && (cur.indexOf('.') > 0) && (cur.indexOf('.') < cur.lastIndexOf('.'))) {
				reg2 = /\.$/;
				cur = cur.replace(reg2, '');
			}

			if(cur) {

				var myRe = /^([^\.]+)(.*)/i;
				var myArray = myRe.exec(cur);
				number = myArray[1];
				rest = myArray[2];

				if(number.length > decimal) {
					cur = number.substr(0, max) + rest;
				}

				if(decimal > 0 && (cur.indexOf('.') > 0)) {
					myRe = /([^\.]+)\.([^\.]*)/i;
					myArray = myRe.exec(cur);
					number = myArray[1];
					float = myArray[2];

					if(float.length > decimal) {
						cur = number + '.' + float.substr(0, decimal);
					}
				}
			}

			obj.value = cur;
			floatnum[obj.id] = obj.value;
		};
	}

	window.Joomsubscription = new Joomsubscription();
});
