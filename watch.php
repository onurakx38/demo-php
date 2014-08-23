<?php
/**
 * videnc Demo
 * Watch Page
 *
 * @author      videnc <demo@videnc.com>
 * @copyright   2009 - 2014 CloneUI
 * @link        http://cloneui.com
 *
 * @since       Friday, August 22, 2014 / 08:52 PM GMT+1 (knoxm)
 * @edited      $Date$
 * @version     $Id$
 *
 * @package     videnc API Demo
*/
require_once('includes/config.php');

if( !isset( $_GET['v'] ) ) {
	header( 'Location: '.BASEURL );
	exit;	
} else {
	$videoKey = trim( $_GET['v'] );
	if( !strlen( $videoKey ) ) {
		header( 'Location: '.BASEURL );
		exit;		
	}	
}

// START:	check for existing videos
$videoPaths	= glob( BASEDIR.'/data/files/video/encoded/'.$_SESSION['uuid'].'/*.{flv,mp4}', GLOB_BRACE );
$imagePaths = glob( BASEDIR.'/data/files/thumbs/'.$_SESSION['uuid'].'/*.{jpg}', GLOB_BRACE );
$videos		= array();
$videoNames	= array();
$images		= array();

// sort videos by date
if( !empty( $videoPaths ) ) {
	usort( $videoPaths, 'filetime_sort' );
	foreach( $videoPaths AS $key => $value ) {		
		$filename				= fetchFilename( basename( $value ) );
		$videoUrl				= BASEURL.'/data/files/video/encoded/'.$_SESSION['uuid'].'/'.basename( $value );
		$videos[$filename]		= $videoUrl;			
		$videoNames[$filename]	= $videoUrl; 
	}
}

// sort images by date
if( !empty( $imagePaths ) ) {
	usort( $imagePaths, 'filetime_sort' );
	foreach( $imagePaths AS $key => $value ) {
		$filename			= fetchFilename( basename( $value ) );
		$images[$filename] 	= BASEURL.'/data/files/thumbs/'.$_SESSION['uuid'].'/'.basename( $value );
	}
}
// END:		check for existing videos

// no videos
if( empty( $videos ) ) {
	header( 'Location: '.BASEURL );
	exit;
}

// display the video
if( array_key_exists( $videoKey, $videoNames ) ) {
	$videoUrl = $videos[$videoKey];
?>
<!DOCTYPE html>
<html>
	<head>
	    <meta charset="utf-8" />
	    <title>videnc &mdash; Upload Demo &mdash; Video</title>
	    <meta name="viewport" content="width=device-width, initial-scale=1.0" />	
	    <link rel="stylesheet" type="text/css" href="css/bootstrap/bootstrap.min.css" />
	    <link rel="stylesheet" type="text/css" href="css/font-awesome/font-awesome.min.css" />
	    <link rel="stylesheet" type="text/css" href="css/demo.css" />
	    
	    <script type="text/javascript">
	    	var PLUPLOAD_VIDENC;
	    	var PLUPLOAD_DEBUG		= true;
	    	var VIDENC_MAX_FILESIZE = '1gb';
	    	var BASEURL				= window.location.protocol + '//' + window.location.host;
	    	if( window.location.pathname != '/' ) {
				BASEURL = BASEURL + '/' + window.location.pathname;
		    }
		    var DEFAULT_PRELOADER_IMAGE = BASEURL + '/images/preloader/default.gif';
		    var UUID = '<?php echo $_SESSION['uuid']; ?>';
	    </script>
	    
	    <script type="text/javascript" src="player/jwplayer/jwplayer.js"></script>
	</head>
	<body>	
		<div class="container" style="margin-top: 50px;">		
			<div class="page-header">
			    <h1>
			    	<a href="<?php echo BASEURL; ?>"><i class="fa fa-play-circle-o"></i></a> <a href="<?php echo BASEURL; ?>">videnc Demo</a>
			    </h1>
			</div>
			
			<div class="alert alert-info alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				This is a demo of <a class="white" href="http://videnc.com" target="_blank">videnc.com's</a> Video Encoding API
			</div>	
			
			<div class="row" style="margin-bottom: 10px;">
				<div class="col-lg-12">
					<div>
						<button id="lightSwitch" type="button" class="btn btn-default"><i class="fa fa-lightbulb-o"></i> Lights</button>
					</div>
				</div>			
			</div>
			
			<div class="row" style="margin-bottom: 100px;">
				<div class="col-lg-12">					
					<div id="videoContainer" style="width: 100%;"> 
						<div id="videncVideo" style="width: 100%;"></div>									
					</div>
					<script type="text/javascript">
						jwplayer('videncVideo').setup({
							flashplayer: BASEURL + '/player/jwplayer/player.swf',
							file: '<?php echo $videoUrl; ?>',
							image: '<?php echo $images[$videoKey]; ?>',
							startparam: 'starttime',
							stretching: 'fill',
							autostart: true,
							width: '100%',
							aspectratio: '16:9',
							events: {
								'onReady': function() {

								}
							} 
						});
					</script>
		    	</div>	
		    </div>			
		</div>	
		
		<!-- START:	console.log -->
		<script type="text/javascript" src="js/consolelog.min.js"></script>
		<script type="text/javascript" src="js/consolelog.detailprint.min.js"></script>
	    <!-- END:	console.log -->
			
	    <script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
	    <script type="text/javascript" src="js/jquery.blockUI.js"></script>
	    <script type="text/javascript" src="js/jquery.blockUI.defaults.js"></script>
	    <script type="text/javascript" src="js/bootstrap/bootstrap.min.js"></script>
	    
	    <script type="text/javascript" src="js/videnc/demo.js"></script>
	    <script type="text/javascript">
	    	$(document).ready(function() {
	    		$('#lightSwitch').click(function(event) {
					$.blockUI({ 
						message: null,
						baseZ: 1000, 
					    // styles for the overlay 
					    overlayCSS:  { 
					        backgroundColor: '#000000', 
					        opacity:         0.9, 
					        cursor:          'pointer' 
					    }, 
						onBlock: function() {										
							$('#videoContainer').css({ 'z-index': 1100 });
							$('#videncVideo').css({ 'z-index': 1150 });
	
						    $('html, body').animate({
						        scrollTop: $('#videoContainer').offset().top
						    }, 'slow');
	
						    $('.blockUI').live('click', function(event) {
								$.unblockUI();
							});
						}	
					});
		    	});
	    	});
	    </script>	    
	</body>
</html>
<?php	
} else {
	header( 'Location: '.BASEURL );
	exit;	
}