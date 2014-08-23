<?php
/**
 * videnc Demo
 * Custom Functions
 *
 * @author      videnc <demo@videnc.com>
 * @copyright   2009 - 2014 CloneUI
 * @link        http://cloneui.com
 *
 * @since       Monday, August 18, 2014 / 02:30 PM GMT+1 (knoxm)
 * @edited      $Date$
 * @version     $Id$
 *
 * @package     videnc API Demo
*/

/**
 * determine the current URL
 *
 * @return  string
 */
function fetchCurrentURL()
{
	if(strlen(@$_SERVER['SHELL'])) {
		return $_SERVER['PHP_SELF'];
	}

	$pageURL = 'http';

	if (@$_SERVER['HTTPS'] == 'on') {
		$pageURL .= 's';
	}

	$pageURL    .= "://";
	$pageURL    .= (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : '';
	$pageURL    .= $_SERVER['PHP_SELF'];
	$queryString = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : '';

	if(strlen($queryString)) {
		$pageURL .= '?'.$queryString;
	}

	return $pageURL;
}

/**
 * determine the server URL
 *
 * @return  string
*/
function fetchServerURL()
{
	if( defined('SITE_URL') ) {
		return SITE_URL;	
	}
	
    $url = fetchCurrentURL();

    if(preg_match('/phpunit/', $url)) {
        return 'phpunit';
    }

    $url = parse_url($url);

    if(!strlen(@$url['path'])) {
        return;
    }

    $pathinfo   = pathinfo($url['path']);
    $serverURL  = 'http';

    if (@$_SERVER['HTTPS'] == 'on') {
	    $serverURL .= 's';
	}

	$serverURL    .= "://";
 	$serverURL    .= @$_SERVER['HTTP_HOST'];
 	$dirname		= array_filter( explode( '/', $pathinfo['dirname'] ) );

 	if( empty( $dirname ) ) {
		$pathinfo['dirname'] = ''; 		
 	}
 		 	
	$serverURL    .= $pathinfo['dirname'];
	 
    return $serverURL;
}

/**
 * Download a file w/ cURL
 * 
 * @param	string		$url
 * @param	string		$destinationFile	full path
 * @return	boolean
 */
function curlDownload( $url, $destinationFile = null )
{	
	if( !is_null( $destinationFile ) ) {
		$basename		= basename( $destinationFile );
		$newName		= fetchFilename( $basename );
		$ext		    = fetchFileExt( $basename );
		$newFilename	= $basename;		
	} else {
		$newName			= mt_rand();
		$destinationFile	= BASEDIR.'/data/files/video/encoded/'.$newName;
		$newFilename		= $newName;		
	}
	
	// create the required directories
	$dirname = dirname( $destinationFile );
	if( !file_exists( $dirname ) ) {
		@mkdir( $dirname, 0777, true );	
	}
	
	$logDir = BASEDIR.'/data/logs/fetch';
	if( !file_exists( $logDir ) ) {
		@mkdir( $logDir, 0777, true );	
	}
	
	/**
	 * Initialize the cURL session
	 */
	$ch         = curl_init();
	// Specify the username/password to use, or leave blank for no auth
	$user       = '';
	$password   = '';
	
	/**
	 * Set the URL of the page or file to download.
	 */
	$cURL_log = fopen( $logDir.'/'.$newFilename.'.log', 'w' );
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_STDERR, $cURL_log );
	fwrite( $cURL_log, date('l, F j, Y / h:i:s A T (\G\M\TO)'). "\n" );
	
	// provide credentials if they're established at the beginning of the script
	if( strlen( $user ) && strlen( $password ) ) {
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $user . ":" . $password);
	}
	
	if( preg_match( '/^https/', $url ) ) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	}
	
	/**
	 * Create a new file
	 */
	$fp = fopen( $destinationFile, 'w' );
	
	/**
	 * Ask cURL to write the contents to a file
	*/
	curl_setopt($ch, CURLOPT_FILE, $fp);
	
	// execute, and log the result to curl_put.log
	$result     = curl_exec($ch);
	
	// START DEBUG
	$txt        = var_export(curl_getinfo($ch), true);
	$error      = curl_error($ch);
	$error_no   = curl_errno($ch);
	$return     = (empty($result)) ? 'failed' : $result;
	if( empty( $error ) ) {
		fwrite($cURL_log, "curl Result: ".$return. "\n");
	} else {
		$message    = "curl error message: ".$error."\n";
		$message   .= "curl error #: ".$error_no;
		fwrite($cURL_log, $message. "\n", 1024);
	}
	// END DEBUG
	
	fwrite($cURL_log, $txt. "\n");
	fclose($cURL_log);
	$httpResponse = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	
	if( $httpResponse == 200 AND file_exists( $destinationFile ) AND ( filesize( $destinationFile ) > 0 ) )  {
		return true;
	} else {
		// failure
		file_put_contents( BASEDIR.'/logs/error/videnc/'.date('m-d-Y').'.log', "[" .date('l, F j, Y / h:i:s A T (\G\M\TO)'). "]\n", FILE_APPEND );
		file_put_contents( BASEDIR.'/logs/error/videnc/'.date('m-d-Y').'.log', "Error:  ".$url.".\nHTTP Response:  ".$httpResponse."\n\n", FILE_APPEND );
		
		return false;
	}	
}

/**
 * fetch file extension
 *
 * @param   string
 * @return  string
*/
function fetchFileExt($file)
{
	return strtolower( substr($file, strrpos($file, '.', -1) + 1) );
}

/**
 * fetch filename
 *
 * @param   string
 * @return  string
*/
function fetchFilename($file)
{
	return substr($file, 0, strrpos($file, '.', -1));
}

/**
 * Compare file modification 
 * times
 *
 * @param   string	$a
 * @param	string	$b
 * @return  int
*/
function filetime_sort( $a, $b )
{
	$filetimeA = filemtime( $a );
	$filetimeB = filemtime( $b );
	
	if ( $filetimeA == $filetimeB ) {
		return 0;
	}
	
	return ( $filetimeA > $filetimeB ) ? -1 : 1;
}