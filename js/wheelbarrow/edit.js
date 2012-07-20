var StrForm = new Class.create();
StrForm.prototype = {
		
		initialize : function() {
			document.observe("dom:loaded", function() {
				$$("#parameters input.hardcoded").each(function(e) {
					e.observe("click", function(event) {
						var element = event.element();
						var checked = element.getValue();
						var tds = element.ancestors()[1];
						var val = tds.select("[name=\'value\']");
						var pos = tds.select("[name=\'code_position\']");
						if (checked) {
							val.each(function(v) {v.disabled = true;});
							pos.each(function(v) {v.disabled = false;});
						} else {
							val.each(function(v) {v.disabled = false;});
							pos.each(function(v) {v.disabled = true;});
						}
					});
				});
				
				if ($("string_id").getValue()) {
					$("string").observe("focus", function(event) {
						var element = event.element();
						element.addClassName('validation-failed');

						element.ancestors()[1].insert({after : "<tr id='string_warning'>" +
												"<td></td>" +
												"<td class='validation-advice'>Note that changing the string\'s value will remove " +
													"that string\'s path records, as it is technically no longer the same element " +
													"and won\'t apply in the same locations unless manually updated there too." +
												"</td>" +
											"</tr>"
							});
					});
				}
				
				$("string").observe("blur", function(event) {
					event.element().removeClassName('validation-failed');
					$("string_warning").remove();
				});
			});
		},
		
		removeParam : function(element) {
			if (confirm('This action is irreversible. Are you sure?')) {
				element.ancestors()[1].remove();
			}
		},
		
		addParam : function(element) {
			var tr = element.ancestors()[1];
			var key = 0;
			for (var i=0;i<tr.siblings().length;i++) {
				if (key<=parseInt(tr.siblings()[i].identify())) {
					key = parseInt(tr.siblings()[i].identify());
					key++;
				}
			}
			key = key.toString();
			tr.insert({before : "<tr id='" + key + "'>" +
					"<td style='border: 1px solid #AAA; padding: 2px; width: 60px;'>" +
					"<input class='input-text position' name='position' style='width:70%; padding: 3px;' type='text' value='" + key + "' />" +
					"</td><td style='border: 1px solid #AAA; width:140px; padding: 2px;'>" +
					"<input style='width:40px;;' class='input-text hardcoded' name='hardcoded' id='hardcoded"+key+"' type='checkbox' />" +
					"<input style='width:40px;' class='input-text code_position' name='code_position' type='text' value='' disabled /></td>" +
					"</td><td style='border: 1px solid #AAA; padding: 2px; width: 360px;'>" +
					"<input class='input-text value' name='value' style='width:96%; padding: 3px;' type='text' value='' /></td>" +
					"<td style='border: 1px solid #AAA; text-align: right; padding: 2px;'>" +
					"<input type='button' value='Remove Parameter' class='remove-param' onclick='str_form.removeParam(this)' /><td>" +
					"</tr>"});
			$("hardcoded"+key).on("click", function(event) {
					var element = event.element();
					var checked = element.getValue();
					var tds = element.ancestors()[1];
					var val = tds.select("[name=\'value\']");
					var pos = tds.select("[name=\'code_position\']");
					if (checked) {
						val.each(function(v) {v.disabled = true;});
						pos.each(function(v) {v.disabled = false;});
					} else {
						val.each(function(v) {v.disabled = false;});
						pos.each(function(v) {v.disabled = true;});
					}
				});
		},
		
		processTable : function() {
			elems = $$("table#parameters input.input-text");
			var data = "";
			for (var i=0;i<elems.length;i++) {
				data += elems[i].ancestors()[1].identify() + ">>>" + elems[i].getAttribute("name") + ">>>" + elems[i].getValue() + "&&&";
			}
			$("param").setAttribute("value", data);
		},
		
		processAndSave : function() {
			str_form.processTable();
			editForm.submit();
		},
		
		processAndContinue : function() {
			str_form.processTable();
			saveAndContinueEdit();
		},
		
};

str_form = new StrForm();