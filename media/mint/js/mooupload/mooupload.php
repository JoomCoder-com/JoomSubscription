<?php

/**
 *
 * Mooupload class
 *
 * Provides a easy way for recept and save files from MooUpload
 *
 * DISCLAIMER: You must add your own special rules for limit the upload of
 * insecure files like .php, .asp or .htaccess
 *
 * @author: Juan Lago <juanparati[at]gmail[dot].com>
 *
 */
class Mooupload
{
	// 	Container index for HTML4 and Flash method
	public $container_index = '_tbxFile';
	public $destpath = NULL;
	public $max_upload = NULL;
	
	
	public function is_HTML5_upload()
	{
		return empty ($_FILES);
	}
	
	public function HTML4_upload()
	{
		$app      = JFactory::getApplication();
		$response = array();

		foreach($_FILES as $k => $file)
		{
			$response ['key']  = ( int )substr($k, strpos($k, @$this->container_index) + strlen(@$this->container_index));
			$response ['name'] = basename($file ['name']);
			// 			Basename for security issues
			$response ['error']  = $file ['error'];
			$response ['size']   = $file ['size'];
			$ext                 = JFile::getExt($response ['name']);
			$session             = JFactory::getSession();
			$exts                = $session->get('file_formats', array(), $app->input->get('key'));
			$ext                 = JFile::getExt($response ['name']);
			$response ['finish'] = FALSE;
			
			if(!in_array(strtolower($ext), $exts))
			{
				$response ['error'] = JText::sprintf('File %s have unallowed extension %s', $response['name'], $ext);

				//U				PLOAD_ERR_EXTENSION;
				return $response;
			}
			
			$time                    = mktime(date('h'), 0, 0, date('m'), date('d'), date('y'));
			$filename                = $time . '_' . md5($response['name'] . '-' . time() . '-' . $time) . '.' . $ext;
			$response['upload_name'] = $filename;
			
			if($response ['error'] == 0)
			{
				if(move_uploaded_file($file ['tmp_name'], $this->destpath . $filename) === FALSE)
				{
					$response['error'] = UPLOAD_ERR_NO_TMP_DIR;
				}
				else
				{
					$response['finish'] = TRUE;
				}
			}
		}
		
		return $response;
	}
	
	public function HTML5_upload()
	{
		$app          = JFactory::getApplication();
		$max_upload   = $this->max_upload;
		$max_post     = $this->_convert_size(ini_get('post_max_size'));
		$memory_limit = $this->_convert_size(ini_get('memory_limit'));
		$limit        = min($max_upload, $max_post, $memory_limit);
		// 		Read headers
		$response = array();
		$headers  = $this->_read_headers();

		$response ['id']   = $headers ['X-File-Id'];
		$response ['name'] = basename($headers ['X-File-Name']);
		// 		Basename for security issues
		$response ['size']   = isset($headers ['Content-Length']) ? $headers ['Content-Length'] : $headers ['X-File-Size'];
		$response ['error']  = UPLOAD_ERR_OK;
		$response ['finish'] = FALSE;

		if($response ['size'] > $limit)
		{
			$response ['error'] = UPLOAD_ERR_INI_SIZE;
		}
		// 		Is resume?
		
		$flag    = ( bool )$headers ['X-File-Resume'] ? FILE_APPEND : 0;
		$session = JFactory::getSession();
		$exts    = $session->get('file_formats', array(), $app->input->get('key'));
		$ext     = strtolower(JFile::getExt($response ['name']));
		
		if(!in_array($ext, $exts))
		{
			$response ['error'] = JText::sprintf('File %s have unallowed extension %s', $response['name'], $ext);
			//U			PLOAD_ERR_EXTENSION;
			return $response;
		}
		
		$time = mktime(date('h'), 0, 0, date('m'), date('d'), date('y'));
		$filename = $time . '_' . md5($response['name'] . '-' . $headers['X-File-Id'] . '-' . $time) . '.' . $ext;
		$response ['upload_name'] = $filename;

		// 		Write file
		if(file_put_contents($this->destpath . $filename, file_get_contents('php://input'), $flag) === FALSE)
		{
			$response ['error'] = UPLOAD_ERR_CANT_WRITE;
		}
		else
		{
			$response['add'] = $headers ['X-File-Size'] . '-' . filesize($this->destpath . $filename);
			
			if(filesize($this->destpath . $filename) == $headers['X-File-Size'])
			{
				$response ['finish'] = TRUE;
			}
		}

		return $response;
	}

	public function upload()
	{
		$session = JFactory::getSession();
		$app = JFactory::getApplication();
		$this->max_upload = (int)$session->get('max_size', $this->_convert_size(ini_get('upload_max_filesize')), $app->input->get('key'));
		$this->destpath = JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
		
		return $this->is_HTML5_upload() ? $this->HTML5_upload() : $this->HTML4_upload();
	}

	public function _convert_size($val)
	{
		$val = trim($val);
		$last = strtolower($val [strlen($val) - 1]);

		switch($last)
		{
			case 'g' :
				$val *= 1024;

			case 'm' :
				$val *= 1024;

			case 'k' :
				$val *= 1024;
		}

		return $val;
	}
	
	public function _read_headers()
	{
		// 		GetAllHeaders doesn't work with PHP-CGI
		if(function_exists('getallheaders'))
		{
			$headers = array();
			foreach(getallheaders() as $name => $value)
			{
				$headers[$name] = $value;
			}
		}
		else
		{
			$headers                    = array();
			$headers ['Content-Length'] = @$_SERVER ['CONTENT_LENGTH'];
			$headers ['X-File-Id']      = @$_SERVER ['HTTP_X_FILE_ID'];
			$headers ['X-File-Name']    = @$_SERVER ['HTTP_X_FILE_NAME'];
			$headers ['X-File-Resume']  = @$_SERVER ['HTTP_X_FILE_RESUME'];
			$headers ['X-File-Size']    = @$_SERVER ['HTTP_X_FILE_SIZE'];
		}

		return $headers;
	}
}