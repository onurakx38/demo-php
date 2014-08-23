<?php
/**
 * videnc
 * API Uploader
 *
 * @author      videnc <demo@videnc.com>
 * @copyright   2009 - 2014 CloneUI
 * @link        http://cloneui.com
 *
 * @since       Monday, August 18, 2014 / 01:35 PM GMT+1 (knoxm)
 * @edited      $Date$
 * @version     $Id$
 *
 * @package     videnc API Demo
*/
require_once('includes/config.php');

// START:	check for existing videos
$videoPaths	= glob( BASEDIR.'/data/files/video/encoded/'.$_SESSION['uuid'].'/*.{flv,mp4}', GLOB_BRACE );
$imagePaths = glob( BASEDIR.'/data/files/thumbs/'.$_SESSION['uuid'].'/*.{jpg}', GLOB_BRACE );
$videos		= array();
$images		= array();

// sort by date
if( !empty( $videoPaths ) ) {
	usort( $videoPaths, 'filetime_sort' );
	foreach( $videoPaths AS $key => $value ) {
		$videos[] = BASEURL.'/data/files/video/encoded/'.$_SESSION['uuid'].'/'.basename( $value );
	}
}

// sort by date
if( !empty( $imagePaths ) ) {
	usort( $imagePaths, 'filetime_sort' );
	foreach( $imagePaths AS $key => $value ) {
		$images[] = BASEURL.'/data/files/thumbs/'.$_SESSION['uuid'].'/'.basename( $value );
	}
}
// END:		check for existing videos
?>
<!DOCTYPE html>
<html>
	<head>
	    <meta charset="utf-8" />
	    <title>videnc &mdash; Upload Demo</title>
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
			    	<a href=""><i class="fa fa-play-circle-o"></i></a> <a href="">videnc Demo</a>
			    </h1>
			</div>
			
			<div class="alert alert-info alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				This is a demo of <a class="white" href="http://videnc.com" target="_blank">videnc.com's</a> Video Encoding API
			</div>
			
			<div id="errorList" class="alert alert-warning" role="alert" style="display: none;"></div>
			<div id="goodList" class="alert alert-success" role="alert" style="display: none;"> 
				<div id="goodListContent"></div>
			</div>
			
			<!-- START:	Upload -->
			<div>
				<form class="col-md-12" name="frmUpload" id="frmUpload" method="POST" action="http://videnc.com/upload">
				    <div class="form-group">
				        <input autocomplete="off" id="apiKey" name="upload_key" type="text" class="form-control input-lg" placeholder="API Key">
				    </div>
				    <div class="form-group">
				        <div id="fileList"></div>
				    </div>
				    <div class="form-group pull-right">
						<div id="pluploadContainer">
					        <button id="pickFiles" class="btn btn-lg disabled" disabled="disabled">
					        	<i class="fa fa-film"></i> Select Video
					        </button> 
					        <button id="uploadFiles" class="btn btn-lg disabled" disabled="disabled">
					        	<i class="fa fa-cloud-upload"></i> Upload
					        </button>					
						</div>
				    </div>
				</form>
			</div>
			<!-- END:	Upload -->
			
			<!-- START:	Video List -->
			<div id="videoList" style="margin-top: 300px; margin-bottom: 80px; <?php if( empty( $videos ) ): ?> display: none;<?php endif; ?>" >
				<h1><i class="fa fa-film"></i> Videos Uploaded by You</h1>
				<hr>
				<?php if( !empty( $videos ) ): ?>
					<?php $count = count( $videos ); $i = 0; foreach( $videos AS $key => $value ): ?>
						<?php $i++; ?>
						<?php if( $i == 1 ): ?>
						<div class="row">
						<?php endif; ?>
							<div class="col-lg-4 col-sm-6 col-xs-12">
								<div style="width: 100%;"> 
									<a href="<?php echo BASEURL.'/watch?v='.fetchFilename( basename( $videos[$key] ) ); ?>">
										<img style="min-width: 300px; max-width: 300px; height: 200px; max-height: 200px;" class="thumbnail img-responsive" border="0" src="<?php echo $images[$key]; ?>">
									</a>									
								</div>
					    	</div>
					    <?php if( ( $i %3 == 0 ) OR ( $i == $count ) ): ?>
						</div>
						<?php endif; ?>
						<?php if( ( $i %3 == 0 ) ): ?>
						<div class="row" style="margin-top: 10px;">
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<!-- END:	Video List -->
		
		</div>	
		
		<!-- START:	console.log -->
		<script type="text/javascript" src="js/consolelog.min.js"></script>
		<script type="text/javascript" src="js/consolelog.detailprint.min.js"></script>
	    <!-- END:	console.log -->
			
	    <script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
	    <script type="text/javascript" src="js/jquery.blockUI.js"></script>
	    <script type="text/javascript" src="js/jquery.blockUI.defaults.js"></script>
	    <script type="text/javascript" src="js/bootstrap/bootstrap.min.js"></script>
	    	    	    	    	    
	    <!-- START:	Plupload -->
		<script type="text/javascript" src="js/plupload/plupload.full.min.js"></script>
		<script type="text/javascript" src="js/videnc/plupload.js"></script>
	    <!-- END:	Plupload -->
	    
	    <script type="text/javascript" src="js/videnc/demo.js"></script>
	    <script type="text/javascript">
	    	$(document).ready(function() {
		    	// START:	Plupload
	    		PLUPLOAD_VIDENC = new plupload.Uploader({
	    			runtimes: 'html5,flash,silverlight,html4',
	    			browse_button: 'pickFiles', 
	    			container: document.getElementById('pluploadContainer'),
	    			url: 'http://videnc.com/upload',
	    			chunk_size: '1mb',
	    			unique_names: false,
	    			filters: {
	    				max_file_size: VIDENC_MAX_FILESIZE,
	    				mime_types: [
	    				    {title: 'Videos', extensions: 'avi,divx,flv,m2ts,m4b,m4v,mov,mp4,mpeg,mpg,ogv,vob,wmv'}
	    		        ]
	    			},
	    		    multipart_params : {
	    		    	'apiKey': 				$('#apiKey').val(),
						<?php if( VIDENC_AUTOSEND_FILES ): ?>
	                	'custom_directives':	{
							'upload':				'custom_http',
							'post_url':				BASEURL + '/videnc.api.php',
							'ping_after_encode':	BASEURL + '/videnc.api.php',
							'ping_after_transfer':	BASEURL + '/videnc.api.php',
							'ping_on_error':		BASEURL + '/videnc.api.php',
							'target_format':		'mp4',
							'quality':				'hd'		                							
	                	},	
						<?php endif; ?>
	                	'custom_params':		{'uuid': UUID},
    	            	'directUpload':			1    		    		    	
	    		    },		 
	    			flash_swf_url: BASEURL + '/js/plupload/Moxie.swf',
	    			silverlight_xap_url: BASEURL + '/js/plupload/Moxie.xap',
	    	        preinit: {
	    	            Init: function(up, info) {
    						if( PLUPLOAD_DEBUG ) {
    							console.log('[Init]', 'Info:', info, 'Features:', up.features);	
    						}
	    	            },
	    	 
	    	            UploadFile: function(up, file) {		    	   	    	 
	    	                // You can override settings before the file is uploaded
	    	                // up.setOption('url', 'upload.php?id=' + file.id);
	    	                // up.setOption('multipart_params', {param1 : 'value1', param2 : 'value2'});
	    	                
	    	            	if( PLUPLOAD_DEBUG ) {
	    	                	console.log('[UploadFile]', file);
	    	            	}

	    	            	// set the API Key
	    	            	up.settings.multipart_params.apiKey = $('#apiKey').val();

							// set 'rename_to'
							up.settings.multipart_params.rename_to = 'original';
	    	            }
	    	        },
	    	 
	    	        // Post init events, bound after the internal events
	    	        init: {
	    	            PostInit: function() {
	    	                // Called after initialization is finished and internal event handlers bound
	    	            	if( PLUPLOAD_DEBUG ) {
	    	                	console.log('[PostInit]');
	    	            	}
	    	            },
	    	 
	    	            Browse: function(up) {
	    	                // Called when file picker is clicked
	    	            	if( PLUPLOAD_DEBUG ) {
	    	                	console.log('[Browse]');
	    	            	}
	    	            },
	    	 
	    	            Refresh: function(up) {
	    	                // Called when the position or dimensions of the picker change
	    	            	if( PLUPLOAD_DEBUG ) {
	    	                	console.log('[Refresh]');
	    	            	}
	    	            },
	    	  
	    	            StateChanged: function(up) {
	    	                // Called when the state of the queue is changed
	    	            	if( PLUPLOAD_DEBUG ) {
	    	                	console.log('[StateChanged]', up.state == plupload.STARTED ? "STARTED" : "STOPPED");
	    	            	}
	    	            },
	    	  
	    	            QueueChanged: function(up) {
	    	                // Called when queue is changed by adding or removing files
	    	            	if( PLUPLOAD_DEBUG ) {
	    	                	console.log('[QueueChanged]');
	    	            	}

	    	            	if( up.files.length > 0 ) {
								$('#uploadFiles').removeClass('disabled').addClass('btn-link').prop('disabled', false);
		    	            } else {
		    	            	$('#uploadFiles').removeClass('btn-link').addClass('disabled').prop('disabled', true);
			    	        }    	
	    	            },
	    	 
	    	            OptionChanged: function(up, name, value, oldValue) {
	    	                // Called when one of the configuration options is changed
	    	            	if( PLUPLOAD_DEBUG ) {
	    	                	console.log('[OptionChanged]', 'Option Name: ', name, 'Value: ', value, 'Old Value: ', oldValue);
	    	            	}
	    	            },
	    	 
	    	            BeforeUpload: function(up, file) {
	    	                // Called right before the upload for a given file starts, can be used to cancel it if required
	    	            	if( PLUPLOAD_DEBUG ) {
	    	                	console.log('[BeforeUpload]', 'File: ', file);
	    	            	}
	    	            },
	    	  
	    	            UploadProgress: function(up, file) {
	    	                // Called while file is being uploaded
	    	            	if( PLUPLOAD_DEBUG ) {
	    	                	console.log('[UploadProgress]', 'File:', file, "Total:", up.total);
	    	            	}

	    	            	$('.blockMsg').html( file.percent + '%' ).show();
	    	                document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
	    	            },
	    	 
	    	            FileFiltered: function(up, file) {
	    	                // Called when file successfully files all the filters
	    	            	if( PLUPLOAD_DEBUG ) {
	    	                	console.log('[FileFiltered]', 'File:', file);
	    	            	}
	    	            },
	    	  
	    	            FilesAdded: function(up, files) {
	    	                // Called when files are added to queue

	    	            	// clear the success list
	    	            	$('#goodListContent').html('');
	    	            	$('#goodList').hide();
	    	            	
							// clear & hide the error list
	    	            	$('#errorList').html('').hide();
	    	            	
	    	            	if( PLUPLOAD_DEBUG ) {
	    	                	console.log('[FilesAdded]');
	    	  
	    	                	plupload.each(files, function(file) {
	    	                    	console.log('  File:', file);
	    	                	});
	    	            	}

							plupload.each(files, function(file) {
								document.getElementById('fileList').innerHTML += '<div id="' + file.id + '"><i class="fa fa-film"></i> ' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>';
							});
	    	            },
	    	  
	    	            FilesRemoved: function(up, files) {
	    	                // Called when files are removed from queue
	    	            	if( PLUPLOAD_DEBUG ) {
	    	                	console.log('[FilesRemoved]');
	    	  
	    	                	plupload.each(files, function(file) {
	    	                    	console.log('  File:', file);
	    	                	});
	    	            	}
	    	            },
	    	  
	    	            FileUploaded: function(up, file, info) {
	    	                // Called when file has finished uploading
	    	            	if( PLUPLOAD_DEBUG ) {
	    	                	console.log('[FileUploaded] File:', file, "Info:", info);
	    	            	}

	    	            	// parse the response
	    	            	var json = $.parseJSON( info.response );
	    	            	if( json.status != 'OK' ) {
								up.stop();
								$('#errorList').append('<div><i class="fa fa-exclamation-triangle"></i> API Error:  ' + json.error + '</div>').show();
		    	            }
	    	            },
	    	  
	    	            ChunkUploaded: function(up, file, info) {
	    	                // Called when file chunk has finished uploading
	    	            	if( PLUPLOAD_DEBUG ) {
	    	                	console.log('[ChunkUploaded] File:', file, "Info:", info);
	    	            	}

	    	            	// parse the response
	    	            	var json = $.parseJSON( info.response );
	    	            	if( json.status != 'OK' ) {
								up.stop();
								$.unblockUI();
								$('#errorList').append('<div><i class="fa fa-exclamation-triangle"></i> API Error:  ' + json.error + '</div>').show();
		    	            }	
	    	            },
	    	 
	    	            UploadComplete: function(up, files) {
	    	                // Called when all files are either uploaded or failed
	    	            	if( PLUPLOAD_DEBUG ) {
	    	                	console.log('[UploadComplete]');
	    	            	}

	    	            	$('#goodListContent').append('<div><i class="fa fa-check-circle"></i> Upload complete. Refresh this page to see if your videos have been encoded.</div>');
	    	            	$('#goodList').show();
	    	            	
	    	            	$('.blockMsg').html('').hide();
	    	            	$.unblockUI();
	    	            },
	    	 
	    	            Destroy: function(up) {
	    	                // Called when uploader is destroyed
	    	            	if( PLUPLOAD_DEBUG ) {
	    	                	console.log('[Destroy] ');
	    	            	}
	    	            },
	    	  
	    	            Error: function(up, args) {
	    	                // Called when an error occurs
	    	            	if( PLUPLOAD_DEBUG ) {
	    	                	console.log('[Error] ', args);
	    	            	}
	    	            	
	    	                $('#errorList').append('<div><i class="fa fa-exclamation-triangle"></i> Error #' + args.code + ': ' + args.message + '</div>').show();
	    	            }
	    	        }
	    		});	    			
	    		
	    		PLUPLOAD_VIDENC.init();
	    	});
	    </script>	    
	</body>
</html>