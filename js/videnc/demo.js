/**
 * videnc Upload Demo
 * Event Observeor
 * 
 * @author		BizLogic
 * @copyright	2014 BigzLogic
 * @link		http://bizlogicdev.com
 * @license     Commercial
 * 
 * @since		Sunday, August 17, 2014 / 10:18 PM GMT+1 (knoxm) 
 * @modified    $Date$ $Author$
 * @version     $Id$
 *
 * @category    JavaScript
 * @package     Demo	
*/

$(document).ready(function() {
	$('#apiKey').bind('change keyup paste', function() {
		var value = $.trim( $(this).val() );
		if( value.length > 0 ) {
			$('#pickFiles').removeClass('disabled').addClass('btn-primary').prop('disabled', false);	
		} else {
			$('#pickFiles').addClass('disabled btn-primary').prop('disabled', true);	
		}
	});

	$('#uploadFiles').click(function(event) {
		event.preventDefault();
		$.blockUI({ 
			message: '0%' 
		});
		PLUPLOAD_VIDENC.start();
		return false;
	});
});