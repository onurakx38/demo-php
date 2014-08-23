$(document).ready(function() {				
	$.blockUI.defaults.message = '<img border="0" src="'+ DEFAULT_PRELOADER_IMAGE + '">';
	$.blockUI.defaults.baseZ = 50000;
    // styles for the message when blocking; if you wish to disable 
    // these and use an external stylesheet then do this in your code: 
    // $.blockUI.defaults.css = {}; 
	$.blockUI.defaults.css = { 
	    padding:        	0, 
        margin:         	0, 
        width:          	'30%', 
        top:            	'40%', 
        left:           	'35%', 
        textAlign:      	'center', 
        color:          	'#FFFFFF', 
        border:         	'0', 
        backgroundColor:	'transparent', 
        cursor:         	'wait',
        'font-size':		'xx-large',
        'font-weight':		'bold'
    };

	$.blockUI.defaults.overlayCSS = { 
		backgroundColor: '#000000', 
		opacity:         0.6, 
		cursor:          'wait'
	};
});