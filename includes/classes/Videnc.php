<?php
/**
 * videnc API Handler
 * Core
 *
 * @author      videnc <demo@videnc.com>
 * @copyright   2010 - 2014 CloneUI
 * @link        http://cloneui.com
 *
 * @since       Wednesday, January 20, 2010 / 07:30 AM GMT+1 (knoxm)
 * @edited      $Date: 2014-08-23 20:13:57 +0200 (Sat, 23 Aug 2014) $
 * @version     $Id: Videnc.php 5 2014-08-23 18:13:57Z dev@bizlogicdev.com $
 *
 * @package     videnc API Demo
*/

class Videnc
{
	private $_uuid;

	/**
	 * Post w/ cURL
	 * 
	 * @param	array	$post
	 * @param	string	$url
	 * @return	void
	*/
	public function httpPost( $post = array(), $url )
	{
		$log    = fopen( BASEDIR.'/data/logs/transfer/'.date('m-d-Y').'.log','a' );
		$copy   = BASEDIR.'/data/logs/transfer/'.date('m-d-Y').'.log';
			
		// upload
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 3600 );
		curl_setopt( $ch, CURLOPT_HEADER, true );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_VERBOSE, true );
		$userAgent = 'videnc/1.0 ('.$_SERVER['HTTP_HOST'].')';
		curl_setopt( $ch, CURLOPT_USERAGENT, $userAgent );
		fwrite( $log, date('l, F j, Y / h:i:s A T (\G\M\TO)'). "\n" );
	
		if (ini_get('open_basedir') == '' && ( ini_get('safe_mode' == 'Off') || ini_get('safe_mode') == 0) ) {
			curl_setopt($ch, CURLOPT_STDERR,$log);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
			// execute, and log the result to curl_put.log
			$result = curl_exec($ch);
		} else {
			curl_setopt($ch, CURLOPT_STDERR,$log);
			// execute, and log the result to curl_put.log
			$result = curl_exec($ch);
		}
		
		// DEBUG
		$txt        = var_export(curl_getinfo($ch), true);
		// DEBUG
		$error      = curl_error($ch);
		$error_no   = curl_errno($ch);
	
		$return     = (empty($result)) ? 'failed' : $result;
		if(empty($error)) {
			fwrite($log, "curl Result: ".$return. "\n", 1024);
		} else {
			$message    = "curl error message: ".$error."\n";
			$message   .= "curl error #: ".$error_no;
			fwrite($log, $message. "\n", 1024);
		}
	
		fwrite($log, $txt. "\n", 1024);
		fclose($log);
	
		if( $this->get_http_code($ch) != 200) {
			$copy   = file_get_contents($copy);
			$copy  .= 'HTTP ERROR: '.$this->get_http_code($ch);
		} else {
			// ...
		}
	
		curl_close($ch);
	}
	
	/**
	 * Returns an HTTP code
	 *
	 * @access  public
	 * @return  int
	*/
	public function get_http_code( $handle )
	{
		return curl_getinfo( $handle, CURLINFO_HTTP_CODE );
	}
		
	/**
	 * Handle Incoming API Events
	 * 
	 * @param	string	$event
	 * @param	string	$file_name
	 * @return	void
	*/
	public function event_handler( $event, $file_name )
    {
    	// log
		$this->log( BASEDIR.'/data/logs/videnc/'.date('m-d-Y').'.log', "Event: ".$event."\n\n" );

        switch( $event ) {
            case 'http_upload':
				$filename	= fetchFilename( $_POST['file'] );
				$fileExt	= fetchFileExt( $_POST['file'] );
				
				$this->_uuid = '';					
				if( isset( $_POST['custom_params'] ) ) {
					$_POST['custom_params'] = unserialize( rawurldecode( $_POST['custom_params'] ) );
					$this->_uuid = ( isset( $_POST['custom_params']['uuid'] ) ) ? $_POST['custom_params']['uuid'] : ''; 
				}
				
				$this->handle_video_upload();
				$this->handle_thumbnail_upload();

                break;

            case 'ping_on_error':
                if( $_POST['status'] == 'FAILED' ) {
					// error...
                }

                break;
                
			case 'ping_after_encode':
				if( $_POST['status'] == 'encoded' ) {
					$filename	= fetchFilename( $_POST['file'] );
					$fileExt	= fetchFileExt( $_POST['file'] );
					
					if( isset( $_POST['url'] ) ) {
						$_POST['url'] = unserialize( rawurldecode( $_POST['url'] ) );
					}
					
					$uuid = '';					
					if( isset( $_POST['custom_params'] ) ) {
						$_POST['custom_params'] = unserialize( rawurldecode( $_POST['custom_params'] ) );
						$uuid = ( isset( $_POST['custom_params']['uuid'] ) ) ? $_POST['custom_params']['uuid'] : ''; 
					}
					
					if( !VIDENC_AUTOSEND_FILES AND VIDENC_DOWNLOAD_FILES ) {
						if( isset( $_POST['url']['video'] ) ) {						
							if( strlen( $uuid ) ) {
								curlDownload( $_POST['url']['video'], BASEDIR.'/data/files/video/encoded/'.$uuid.'/'.$_POST['file'] );
							} else {
								curlDownload( $_POST['url']['video'], BASEDIR.'/data/files/video/encoded/'.$_POST['file'] );
							}
						}	

						if( isset( $_POST['url']['thumb'] ) ) {
							if( strlen( $uuid ) ) {
								curlDownload( $_POST['url']['thumb'], BASEDIR.'/data/files/thumbs/'.$uuid.'/'.$filename.'.jpg' );
							} else {
								curlDownload( $_POST['url']['thumb'], BASEDIR.'/data/files/thumbs/'.$filename.'.jpg' );
							}
						}						
					}										
                }
                
				break;                

            case 'ping_after_transfer':
                if( $_POST['status'] == 'NO_ERROR' ) {
                    $file       = $_POST['file'];
                    $file_name  = substr( $file, 0, strrpos( $file, '.' ) );
                    $file_ext   = strtolower( substr( $file, strrpos( $file, '.' ) + 1 ) );
                    $time       = $_POST['duration'];
                    $original   = $_POST['original'];
                    $path       = $_POST['endpoint_http_path'];
                }

                break;

            case 'update_duration':
                $file       = $_POST['file'];
                $file_name  = substr( $file, 0, strrpos( $file, '.' ) );
                $duration	= $_POST['duration'];

                break;
        }
    }

	/**
	 * Incoming Video Upload 
	 * Handler
	 * 
	 * @return	void
	*/
    public function handle_video_upload()
    {
		if( isset( $_FILES['encoded_video'] ) ) {
			if( !strlen( $this->_uuid ) ) {
				$dir = BASEDIR.'/data/files/video/encoded';
				if( !file_exists( $dir ) ) {
					@mkdir( $dir, 0777, true );
				}
 
				$move = move_uploaded_file( $_FILES['encoded_video']['tmp_name'], $dir.'/'.$_FILES['encoded_video']['name'] );
			} else {
				$dir = BASEDIR.'/data/files/video/encoded/'.$this->_uuid;
				if( !file_exists( $dir ) ) {
					@mkdir( $dir, 0777, true );
				} 

				$move = move_uploaded_file( $_FILES['encoded_video']['tmp_name'], $dir.'/'.$_FILES['encoded_video']['name'] );
			}
		}
    }

	/**
	 * Incoming Thumbnail Upload 
	 * Handler
	 * 
	 * @return	void
	*/
    public function handle_thumbnail_upload()
    {
		if( isset( $_FILES['thumbnail'] ) ) {
			$filename = fetchFilename( $_POST['newName'] );
			if( !strlen( $this->_uuid ) ) {
				$dir = BASEDIR.'/data/files/thumbs';
				if( !file_exists( $dir ) ) {
					@mkdir( $dir, 0777, true );
				} 

				$move = move_uploaded_file( $_FILES['thumbnail']['tmp_name'], $dir.'/'.$filename.'.jpg' );
			} else {
				$dir = BASEDIR.'/data/files/thumbs/'.$this->_uuid;
				if( !file_exists( $dir ) ) {
					@mkdir( $dir, 0777, true );
				} 

				$move = move_uploaded_file( $_FILES['thumbnail']['tmp_name'], $dir.'/'.$filename.'.jpg' );
			}
		}
    }

	/**
	 * Log to a file 
	 * 
	 * @param	string	$filename
	 * @param	string	$txt
	 * @param	string	$method
	 * @return	void
	*/
    public function log( $filename, $txt, $method = 'FILE_APPEND' )
    {
        file_put_contents( $filename, "[" .date('l, F j, Y / h:i:s A T (\G\M\TO)'). "] -- ", $method );
        file_put_contents( $filename, $txt, FILE_APPEND );
        file_put_contents( $filename, "\n", FILE_APPEND );
    }
}