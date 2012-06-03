var Sync = new Class.create();
Sync.prototype = {
		
		initialize : function(){
			Sync.prototype.checkUrl = '//www.localhost.com/magento/index.php/admin/demo/sync';
			Sync.prototype.run();
		},
		
        run : function(){
        	new Ajax.Request(Sync.prototype.checkUrl,{
                method: 'post',
                parameters: {
                    action: 'translation_files_sync'
                },
                onSuccess: function(transport){
                	var data = transport.responseText.evalJSON();
                	if (data.state) {
                		if (data.data) {
                			$('magestance-messages').update(data.data);
                		}
                		Sync.prototype.run();
                	} else {
                		$('magestance-messages').insert(' and done.');
                	}
                },
            });
        },
};

sync = new Sync();