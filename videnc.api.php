<?php
/**
 * videnc Demo
 * API Handler
 *
 * @author      videnc <demo@videnc.com>
 * @copyright   2009 - 2014 CloneUI
 * @link        http://cloneui.com
 *
 * @since       Saturday, October 24, 2009 / 10:57 PM GMT+1 (knoxm)
 * @edited      $Date$
 * @version     $Id$
 *
 * @package     videnc API Demo
*/

error_reporting( 'E_ALL' );
ini_set( 'display_errors', false );
ini_set( 'error_log', dirname(__FILE__).'/data/logs/error/php/'.date('m-d-Y').'-error.log' );

define('THIS_PAGE', 'videnc_api');
if ( function_exists('set_time_limit') AND get_cfg_var('safe_mode') == 0 ) {
	@set_time_limit(0);
}

require_once('includes/config.php');

// write to log file
$file   = BASEDIR.'/data/logs/videnc/'.date('m-d-Y').'.log';
$fp     = fopen( $file, 'a+' );
fwrite( $fp, "\n" .date('l, F j, Y / h:i:s A T (\G\M\TO)'). "\n" );
fwrite( $fp, "-------------------------------------\n\n" );

// change to match your server's hostname
if( gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) == 'ip-192-169-201-106.secureserver.net' ) {
	fwrite( $fp, "\$_POST:\n\n".var_export( $_POST, true )."\n\n\$_FILES:\n\n".var_export( $_FILES, true )."\n\n" );
	
	$Videnc = new Videnc();
	
    if( strlen( @$_POST['event'] ) ) {
        $file       = $_POST['file'];
        $file_name  = substr( $file, 0, strrpos( $file, '.' ) );
        $Videnc->event_handler( $_POST['event'], $file_name );
    }
} else {
    fwrite( $fp, 'Invalid access attempt:  '.$_SERVER['REMOTE_ADDR'].' ('.gethostbyaddr($_SERVER['REMOTE_ADDR']).')' );    
    header( 'Location: '.BASEURL );
}

fclose( $fp );