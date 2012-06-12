var Sync = new Class.create();
Sync.prototype = {
		
		initialize : function(){
			Sync.prototype.checkUrl = '//www.localhost.com/magento/index.php/admin/demo/sync';
			Sync.prototype.run();
		},
		
        run : function(){
        	new Ajax.Request(Sync.prototype.checkUrl,{
                method: 'post',
                onSuccess: function(transport){
                	var data = transport.responseText.evalJSON();
                	if (data.url) {
                		window.open(data.url + '?magestanceScan');
                	}
            		if (data.data) {
            			$('magestance-messages').insert(data.data);
            		}
                	if (data.state) {
                		Sync.prototype.run();
                	} else {
                		$('magestance-messages').insert(' and done.');
                	}
                },
            });
        },
};

sync = new Sync();