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

// JSON
header('Content-type: application/json; charset=utf-8');
$json = array();

// prevent caching
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

/* 
// Support CORS
header("Access-Control-Allow-Origin: *");
// other CORS headers if any...
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	exit; // finish preflight CORS requests here
}
*/

// 5 minutes execution time
@set_time_limit( 5 * 60 );

// Settings
$targetDir = BASEDIR.'/data/tmp/upload';
$cleanupTargetDir = true; // Remove old files
$maxFileAge = 5 * 3600; // Temp file age in seconds


// Create target dir
if ( !file_exists( $targetDir ) ) {
	@mkdir( $targetDir, 0777, true );
}

// Get a file name
if (isset($_REQUEST["name"])) {
	$fileName = $_REQUEST["name"];
} elseif ( !empty( $_FILES ) ) {
	$fileName = $_FILES["file"]["name"];
} else {
	$fileName = uniqid("file_");
}

$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

// Chunking might be enabled
$chunk	= isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;


// Remove old temp files	
if ($cleanupTargetDir) {
	if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
		$json['status'] 	= 'ERROR';
		$json['error']		= 'Failed to open temp directory';
		$json['error_code']	= 100;
		
		exit( json_encode( $json ) );
	}

	while (($file = readdir($dir)) !== false) {
		$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

		// If temp file is current file proceed to the next
		if ($tmpfilePath == "{$filePath}.part") {
			continue;
		}

		// Remove temp file if it is older than the max age and is not the current file
		if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
			@unlink($tmpfilePath);
		}
	}
	closedir($dir);
}	


// Open temp file
if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
	$json['status'] 	= 'ERROR';
	$json['error']		= 'Failed to open output stream';
	$json['error_code']	= 102;
	
	exit( json_encode( $json ) );
}

if (!empty($_FILES)) {
	if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
		$json['status'] 	= 'ERROR';
		$json['error']		= 'Failed to move uploaded file';
		$json['error_code']	= 103;
		
		exit( json_encode( $json ) );
	}

	// Read binary input stream and append it to temp file
	if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
		$json['status'] 	= 'ERROR';
		$json['error']		= 'Failed to open input stream';
		$json['error_code']	= 101;
		
		exit( json_encode( $json ) );
	}
} else {	
	if (!$in = @fopen("php://input", "rb")) {
		$json['status'] 	= 'ERROR';
		$json['error']		= 'Failed to open input stream';
		$json['error_code']	= 101;
		
		exit( json_encode( $json ) );
	}
}

while ($buff = fread($in, 4096)) {
	fwrite($out, $buff);
}

@fclose($out);
@fclose($in);

// Check if file has been uploaded
if ( ( $chunks == 0 ) OR ( $chunk == ( $chunks - 1 ) ) ) {
	// move 
	$move = rename( $filePath.'.part', $filePath );
	
	if( $move ) {
		// START:	Upload to videnc
		require_once( BASEDIR.'/includes/classes/videnc/Upload.php' );
		$Videnc_Upload = new Videnc_Upload();
		
		file_put_contents( BASEDIR.'/data/logs/transfer/'.date('m-d-Y').'_FILES.log', var_export( $_FILES, true ) );
		file_put_contents( BASEDIR.'/data/logs/transfer/'.date('m-d-Y').'_POST.log', var_export( $_POST, true ) );
	
		$post							= array();
		$post['upload_key']				= $_POST['upload_key'];
		$post['custom_directive']		= 'custom_http';
		$post['post_url']				= BASEURL.'/videnc.api.php';
		$post['ping_after_encode']		= BASEURL.'/videnc.api.php';
		$post['ping_after_transfer']	= BASEURL.'/videnc.api.php';
		$post['ping_on_error']			= BASEURL.'/videnc.api.php';
		$post['download_me']			= BASEURL.'/data/tmp/upload/'.basename( $filePath );
		
		$Videnc_Upload->httpPost( $post, 'http://videnc.com/upload' );
		// END:		Upload to videnc
	
		$json['status'] = 'OK';
		echo json_encode( $json );
	} else {
		$json['status'] 	= 'ERROR';
		$json['error']		= 'Failed to move uploaded file';
		$json['error_code']	= 103;
	
		echo json_encode( $json );
	}
} else {
	$json['status'] = 'OK';
	echo json_encode( $json );	
}