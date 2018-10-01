<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * @name		Sound file representation
 * @package 	Files
 * @author 		Stanislav U. Alkimovich
 * @date 		2015-01-30
 *
 **/
class Kohana_File_Sound extends File_Regular
{
	/**
	 * @const string	autosave extension
	 */
	const EXTENSION = "";
	
	/** Content setter / getter
	 *
	 * @param 	Kohana_File	
	 * @param 	string		text for saving
	 * @param	string		insert mode
	 * @return 	string
	 */	
	public function content(Kohana_File $file, $text = NULL, $mode = NULL)
	{
	}

	/** Print html for playing
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @return 	array
	 */
	public function player(Kohana_File $file)
	{
		if ( ! acl('file_read', $file))
		{
			throw new Access_Exception;
		}
		
		InclStream::instance( )->add('file.sound.js');
		
		return Html::factory('anchor')->href($file->url( ))->classes(array('f-sound-play', 'cms-play'));
	}
	
	/** Print html for recording
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @return 	mixed
	 */
	public function recorder(Kohana_File $file)
	{
		if ( ! acl('file_write', $file))
		{
			throw new Access_Exception;
		}
		
// 		InclStream::instance( )->add('https://www.webrtc-experiment.com/RecordRTC.js', FALSE, 5);
		InclStream::instance( )->add('RecordRTC.js', FALSE, 5);
		InclStream::instance( )->add('file.sound.js');
		
		return Html::factory('anchor')->href($file->action('record'))->classes(array('f-sound-rec', 'cms-rec'));
	}
	
	/** Record file
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @return 	mixed
	 */
	public function action_record(Kohana_File $file)
	{
		if (isset($_FILES["audio-blob"]))
		{
			$dest = $file->dir( );
			if ( ! $dest->exists( ))
			{
				$dest->create( );
			}
			
			$tmp_filename = str_replace($file->ext( ), 'wav', $file->name( ));
			$tmp_filename_mp3 = str_replace($file->name( ), '~'.$file->name( ), $file->path( ));
			
			if ( ! Upload::save($_FILES["audio-blob"], $tmp_filename, $dest->path( ))) {
				throw new File_Exception('Cannot record sound to :dst', array(':dst' => $dest->path( )));
			}
			
			$file->remove( );
			
			$tmp = $dest->child($tmp_filename);
			$tmp_mp3 = ff($tmp_filename_mp3);
			
			// convert to mp3
			exec("ffmpeg -i ".$tmp->path( )." -ab 128k -ar 44100 ".$file->path( ));
			
			// trim at the beginning
			exec("sox ".$file->path( )." ".$tmp_mp3->path( )." silence 1 0.5 2%");
			
			// trim at the ending
			exec("sox ".$tmp_mp3->path( )." ".$file->path( )." reverse silence 1 0.5 2% reverse");

			// re-initialize file
			$file->init( );
			
			// remove temporary
			$tmp->remove( );
			$tmp_mp3->init( );
			$tmp_mp3->remove( );
			
			return Basic::json_safe_encode(array(
				'filename' => $file->path( ),
			));
		}
	}
}