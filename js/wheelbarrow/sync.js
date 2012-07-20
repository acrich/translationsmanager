var Sync = new Class.create();
Sync.prototype = {
		
		initialize : function(){
			Sync.prototype.checkUrl = window.location.href.substring(0, window.location.href.lastIndexOf('translator') + 10) + '/sync';
			Sync.prototype.run();
		},
		
        run : function(){
        	new Ajax.Request(Sync.prototype.checkUrl,{
                method: 'post',
                onSuccess: function(transport){
                	var data = transport.responseText.evalJSON();
                	if (data.url) {
                		window.open(data.url + '?translateScan');
                	}
            		if (data.data) {
            			$('sync-messages').update(data.data);
            		}
                	if (data.state) {
                		Sync.prototype.run();
                	} else {
                		$('sync-messages').insert(' Done.');
                	}
                },
            });
        },
};

sync = new Sync();
