var Getpage = new Class.create();
Getpage.prototype = {
		
		initialize : function(){
			Getpage.prototype.checkUrl = '//www.localhost.com/magento/index.php/admin/demo/checkstate';
			Getpage.prototype.checkState = false;
			Getpage.prototype.getPath();
		},
		
        getPath : function(){
        	new Ajax.Request(Getpage.prototype.checkUrl,{
                method: 'post',
                onSuccess: function(transport){
                	var data = transport.responseText.evalJSON();
                	if (!Getpage.prototype.checkState && data.url) {
                		window.open(data.url + '?magestanceScan');
                		Getpage.prototype.checkState = true;
                	}
                	if (data.state) {
                		if (data.data) {
                			$('magestance-messages').update(data.data);
                		}
                		Getpage.prototype.getPath();
                	} else {
                		$('magestance-messages').insert(' and done.');
                	}
                },
            });
        },
};

getpage = new Getpage();
        
