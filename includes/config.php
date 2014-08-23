<?php
/**
 * videnc Demo
 * Global Config
 *
 * @author      videnc <demo@videnc.com>
 * @copyright   2009 - 2014 CloneUI
 * @link        http://cloneui.com
 *
 * @since       Monday, August 18, 2014 / 02:31 PM GMT+1 (knoxm)
 * @edited      $Date$
 * @version     $Id$
 *
 * @package     videnc API Demo
*/

error_reporting( E_ALL );
ini_set( 'display_errors', false );
date_default_timezone_set( 'UTC' );

// BASEDIR
define( 'BASEDIR', dirname( dirname( __FILE__ ) ) );

// error log location
ini_set( 'error_log', BASEDIR.'/data/logs/error/php/'.date('m-d-Y').'.log' );

require_once('functions.php');
require_once('constants.php');
require_once('classes/UUID.php');
require_once('classes/FirePHPCore/FirePHP.class.php');
require_once('classes/FirePHPCore/fb.php');
require_once('classes/Videnc.php');

// START:	FirePHP
$firephp = FirePHP::getInstance( true );
$firephp->registerErrorHandler( $throwErrorExceptions = false );
$firephp->registerExceptionHandler();
$firephp->registerAssertionHandler(
		$convertAssertionErrorsToExceptions=true,
		$throwAssertionExceptions=false
);
$firephp->setEnabled(true);
// END:		FirePHP

define( 'BASEURL', fetchServerURL() );

// START:	Setup the session
// until the end of time...
// @link	http://en.wikipedia.org/wiki/Year_2038_problem
if( !defined('COOKIE_TIMEOUT') ) {
	define('COOKIE_TIMEOUT', 2147483647);
}

if( !defined('GARBAGE_TIMEOUT') ) {
	define('GARBAGE_TIMEOUT', COOKIE_TIMEOUT);
}

ini_set('session.gc_maxlifetime', GARBAGE_TIMEOUT);
session_set_cookie_params(COOKIE_TIMEOUT, '/');

// setting session dir
if( isset($_SERVER['HTTP_HOST'] ) ) {
	$sessdir = '/tmp/'.$_SERVER['HTTP_HOST'];
} else {
	$sessdir = '/tmp/videnc';
}

// if session dir not exists, create directory
if ( !is_dir($sessdir) ) {
	@mkdir( $sessdir, 0777, true );
}

//if directory exists, then set session.savepath otherwise let it go as is
if( is_dir($sessdir) ) {
	ini_set('session.save_path', $sessdir);
}

session_start();
if( !isset( $_SESSION['uuid'] ) ) {
	// assign a UUID
	$_SESSION['uuid'] = UUID::mint( 4 )->__toString();	
}
// END:		Setup the session