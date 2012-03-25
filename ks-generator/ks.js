// Fix missing window.console.log method

if (!window.console || !window.console.log) {
	window.console = {};
	window.console.log = function (str) {};
}	
	
// Fix prototype fire method to allow native HTML events

Element.prototype_fire = Element.fire;
Element.fire = function (element, event) {
	if (event.indexOf(":") == -1) {
		if(document.createEvent){
			window.console.log("Fire good: " + element.id + ", " + event);
			// dispatch for firefox + others
			var evt = document.createEvent("HTMLEvents");
			evt.initEvent(event, true, true); // event type,bubbling,cancelable
			return !element.dispatchEvent(evt);
		} else {
			// dispatch for IE
			var evt = document.createEventObject();
			return element.fireEvent('on' + event, evt)
		}
	} else {
		return this.prototype_fire(element, event);
	}
}	

// Enable *.observe to handle multiple events, [ "click", "change", ... ]
Event.prototype_observe = Event.observe;
Event.observe = function (element, eventName, handler) {
	var ret = null;
	
	if (typeof eventName == "object" && 
		typeof eventName.length != "undefined") {
		for (var i = 0; i < eventName.length; i++) {
			window.console.log("Event: " + eventName[i]);
			ret = Event.prototype_observe(element, eventName[i], handler);
		}
	} else {
		ret = Event.prototype_observe(element, eventName, handler);
	}
	
	return ret;
}

Element.prototype_extend = Element.extend;
Element.extend = function (element) {	
	var v = Element.prototype_extend(element);
	if (v && v.observe) {
		v.observe = Event.observe.methodize();
	}
	return v;
}

function initForm() {	
	// Form event handlers
	
	$("rootpwcrypt").observe("click", function() {
		var pw = $("rootpw").value;
		if (pw != "") {
	  		var pwsalt = Javacrypt.crypt("", pw);
	  		pw = pwsalt[0];
	  		$("rootpw").value = pw;
	  		$("rootpwcrypted").checked = true;
	  		return false;
	  	}
	});
	
	$("source").observe("change", function () {
		if (this.options[this.selectedIndex].value == "url") {
			Element.show($("url").parentNode);
		} else {
			Element.hide($("url").parentNode);
		}
	});
	
	$("network").observe("change", function () {
		if (this.options[this.selectedIndex].value == "static") {
			Element.show($("ip").parentNode);
		} else {
			Element.hide($("ip").parentNode);
		}
	});
	
	$("ip").observe([ "change", "click", "keyup" ], function () {
		var v = this.value;
		if (v.match(/[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+/)) {
			$("dns").value = 
			$("gw").value = v.replace(/([0-9]+\.[0-9]+\.[0-9]+)\.[0-9]+/, "$1.1");
			
		}
	});
	
	$("firewall").observe("change", function () {
		if (this.options[this.selectedIndex].value == "enabled") {
			Element.show($("firewallports").parentNode);
		} else {
			Element.hide($("firewallports").parentNode);
		}
	});
	
	if ($("post").value != "") {
		$("post").style.display = "";
	}
	
	if ($("post").style.display == "none") {
		$("posthide").innerHTML = "show";
	} else {
		$("posthide").innerHTML = "hide";
	}
	
	$("posthide").observe("click", function (e) {
		if ($("post").style.display == "none") {
			$("posthide").innerHTML = "hide";
			$("post").style.display = "";
		} else {
			$("posthide").innerHTML = "show";
			$("post").style.display = "none";
		}
		
		e.stop();
		return false;
	});
	
	$("hostnameauto").observe("change", function () {
		if (this.checked) {
			$("hostname").value = "ks" + (new Date().getTime());
			$("hostname").disabled = true;
		} else {
			$("hostname").disabled = false;
		}
	});
	
	// Redraw all form now
	
	var f = document.FormBlock;	
	for (var i = 0; i < f.length; i++) {
		var ff = f[i];
		if (ff.name) {
			// Fire change event to redraw form
			Element.fire(ff, "change");				
		}
	}
}	
	
document.observe("dom:loaded", function() {
	var f = document.FormBlock;	
	
	// Set id in fields and fix select boxes
	
	for (var i = 0; i < f.length; i++) {
		var ff = f[i];
		if (ff.name) {
			ff.id = ff.name;
			
			if (ff.type == "select-one" || ff.type == "select-multi") {
				for (var j = 0; j < ff.options.length; j++) {
					if (!ff.options[j].value) {
						// Set value if only text
						ff.options[j].value = ff.options[j].text;
					}
				}
			}
		}
	}
		
	// Fire init method	
	window.setTimeout(initForm, 10);
});
