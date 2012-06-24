var Newpage = new Class.create();
Newpage.prototype = {
		
		initialize : function(){
		
		},
		
		openPage : function(){
			path = document.getElementById('path').value;
			editForm.submit();
			
		},
};

newpage = new Newpage();