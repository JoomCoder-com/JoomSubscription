<?php
require_once 'UploadHandler.php';
class CobaltUploadHandler extends UploadHandler
{

	// PHP File Upload error message codes:
	// http://php.net/manual/en/features.file-upload.errors.php
	protected $error_messages = array(
		1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
		2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
		3 => 'The uploaded file was only partially uploaded',
		4 => 'No file was uploaded',
		6 => 'Missing a temporary folder',
		7 => 'Failed to write file to disk',
		8 => 'A PHP extension stopped the file upload',
		'post_max_size' => 'The uploaded file exceeds the post_max_size directive in php.ini',
		'max_file_size' => 'File is too big',
		'min_file_size' => 'File is too small',
		'accept_file_types' => 'Filetype not allowed',
		'max_number_of_files' => 'Maximum number of files exceeded',
		'max_width' => 'Image exceeds maximum width',
		'min_width' => 'Image requires a minimum width',
		'max_height' => 'Image exceeds maximum height',
		'min_height' => 'Image requires a minimum height',
		'abort' => 'File upload aborted',
		'image_resize' => 'Failed to resize image'
	);

	protected $image_objects = array();

	function __construct($options = null, $initialize = true, $error_messages = null)
	{
		$options_main = array(
			'script_url' => $this->get_full_url() . '/',
			'upload_dir' => JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR,
			'upload_url' => $this->get_full_url() . '/files/',
			// Set the following option to 'POST', if your server does not support
			// DELETE requests. This is a parameter sent to the client:
			'delete_type' => 'DELETE',
			'inline_file_types' => '/\.(gif|jpe?g|png)$/i',
			// The maximum number of files for the upload directory:
			'max_number_of_files' => null,
			// Defines which files are handled as image files:
			'image_file_types' => '/\.(gif|jpe?g|png)$/i',
			// Image resolution restrictions:
			'max_width' => null,
			'max_height' => null,
			'min_width' => 1,
			'min_height' => 1,
			'image_versions' => array(),
			'thumbnail' => array(
				'max_width' => 80,
				'max_height' => 80
			)
		);

		$options = array_merge($options_main, $options);

		parent::__construct($options);
	}

	protected function get_full_url()
	{
		return JFactory::getURI()->base();
	}

	protected function get_download_url($file_name, $version = null, $direct = false)
	{
		if(! $direct && $this->options['download_via_php'])
		{
			$url = $this->options['script_url'] . $this->get_query_separator($this->options['script_url']) . $this->get_singular_param_name() . '=' . rawurlencode($file_name);
			if($version)
			{
				$url .= '&version=' . rawurlencode($version);
			}
			return $url . '&download=1';
		}
		if(empty($version))
		{
			$version_path = '';
		}
		else
		{
			$version_url = @$this->options['image_versions'][$version]['upload_url'];
			if($version_url)
			{
				return $version_url . $this->get_user_path() . rawurlencode($file_name);
			}
			$version_path = rawurlencode($version) . '/';
		}
		return $this->options['upload_url'] . $this->get_user_path() . $version_path . rawurlencode($file_name);
	}

	protected function set_additional_file_properties($file)
	{
		$file->deleteUrl = JRoute::_("index.php?option=com_cobalt&task=files.uploadremove&tmpl=component&filename=".$file->name);
		$file->deleteType = $this->options['delete_type'];
		if($file->deleteType !== 'DELETE')
		{
			$file->deleteUrl .= '&_method=DELETE';
		}

	}

	protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null, $content_range = null)
	{
		$file = new \stdClass();
		$file->realname = $name;
		$file->name = $this->get_file_name($uploaded_file, $name, $size, $type, $error, $index, $content_range);
		$file->size = $this->fix_integer_overflow((int)$size);
		$file->type = $type;
		$file->formname = $this->options['param_name'];
		if($this->validate($uploaded_file, $file, $error, $index))
		{
			$this->handle_form_data($file, $index);
			$upload_dir = $this->get_upload_path();
			if(! is_dir($upload_dir))
			{
				mkdir($upload_dir, $this->options['mkdir_mode'], true);
			}
			$file_path = $this->get_upload_path($file->name);
			$append_file = $content_range && is_file($file_path) && $file->size > $this->get_file_size($file_path);
			if($uploaded_file && is_uploaded_file($uploaded_file))
			{
				// multipart/formdata uploads (POST method uploads)
				if($append_file)
				{
					file_put_contents($file_path, fopen($uploaded_file, 'r'), FILE_APPEND);
				}
				else
				{
					move_uploaded_file($uploaded_file, $file_path);
				}
			}
			else
			{
				// Non-multipart uploads (PUT method support)
				file_put_contents($file_path, fopen('php://input', 'r'), $append_file ? FILE_APPEND : 0);
			}
			$file_size = $this->get_file_size($file_path, $append_file);
			if($file_size === $file->size)
			{
				$ext = JString::strtolower(JFile::getExt($file->name));
				$subfolder = $ext;
				$input = JFactory::getApplication()->input;
				if($field_id = $input->getInt('field_id'))
				{
					$field = JTable::getInstance('Field', 'CobaltTable');
					$field->load($field_id);
					$field->params = new JRegistry($field->params);
					$subfolder = $field->params->get('params.subfolder', $field->field_type);
				}

				$table = $this->savefile($file, $subfolder);

				if($table)
				{
					JFile::delete(JPATH_ROOT . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . $file->name);
				}

				if($this->is_valid_image_file($file->path))
				{
					$this->handle_image_file($file->path, $file);
				}
				$file->id = $table->id;
				$file->table = $table;
			}
			else
			{
				$file->size = $file_size;
				if(! $content_range && $this->options['discard_aborted_uploads'])
				{
					unlink($file_path);
					$file->error = $this->get_error_message('abort');
				}
			}
			$this->set_additional_file_properties($file);
		}
		return $file;
	}

	protected function handle_image_file($file_path, $file)
	{
		$file->thumbnailUrl = CImgHelper::getThumb($file->path, $this->options['thumbnail']['max_width'], $this->options['thumbnail']['max_height'], 'uploader', JFactory::getUser()->get('id'));
	}

	public function savefile(&$file, $subfolder)
	{
		$params = JComponentHelper::getParams('com_cobalt');
		$input = JFactory::getApplication()->input;
		$time = time();
		$date = date($params->get('folder_format', 'Y-m'), $time);
		$ext = JString::strtolower(JFile::getExt($file->name));
		$filename = $time . '_' . md5($file->name . '-' . $file->size . '-' . $time) . '.' . $ext;
		$src = JPATH_ROOT . '/tmp/' . $file->name;
		$file->filename = $filename;

		$dest = JPATH_ROOT . DIRECTORY_SEPARATOR . $params->get('general_upload') . DIRECTORY_SEPARATOR . $subfolder . DIRECTORY_SEPARATOR;
		$index = '<html><body></body></html>';
		if(! JFolder::exists($dest))
		{
			JFolder::create($dest, 0755);
			JFile::write($dest . DIRECTORY_SEPARATOR . 'index.html', $index);
		}

		$dest .= $date . DIRECTORY_SEPARATOR;
		if(! JFolder::exists($dest))
		{
			JFolder::create($dest, 0755);
			JFile::write($dest . DIRECTORY_SEPARATOR . 'index.html', $index);
		}
		$dest .= $filename;

		if(! JFile::copy($src, $dest))
		{
			return FALSE;
		}

		$data = array(
			'id' => NULL,
			'filename' => $filename,
			'realname' => urldecode($file->realname),
			'section_id' => $input->getInt('section_id'),
			'record_id' => $input->getInt('record_id'),
			'type_id' => $input->getInt('type_id'),
			'field_id' => $input->getInt('field_id'),
			'ext' => $ext,
			'fullpath' => JPath::clean($date . DIRECTORY_SEPARATOR . $filename, '/'),
			'size' => $file->size
		);

		$file->url = JUri::base().'/'.$params->get('general_upload').'/'.$subfolder.'/'.$date.'/'.$filename;
		$file->path = $dest;

		if(in_array(strtolower($ext), array(
			'jpg',
			'jpeg',
			'png',
			'gif',
			'bmp'
		)))
		{
			$size = @getimagesize(JPath::clean($dest));

			if($size && ! empty($size))
			{
				$data['width'] = $size[0];
				$data['height'] = $size[1];
			}

			$session = JFactory::getSession();
			$width = (int)$session->get('width', FALSE, $input->get('key'));
			$height = (int)$session->get('height', FALSE, $input->get('key'));

			if($width && $height && ($width < (int)@$size[0] || $height < (int)@$size[1]))
			{
				$resizer = new JS_Image_Resizer($dest);
				$resizer->quality = 100;
				$resizer->resize_limitwh($width, $height, $dest);
				$resizer->close();

				@chmod($dest, 0644);
			}

			if(function_exists('exif_read_data') && in_array(strtolower($ext), array(
				'jpg',
				'jpeg',
				'ttf'
			)))
			{
				$metadata = @exif_read_data(JPath::clean($src));
				$data['params'] = json_encode($metadata);
			}
		}

		if(in_array(strtolower($ext), array('mp3')))
		{
			$data['params'] = $this->_getID3(JPath::clean($src));
		}

		$table = JTable::getInstance('Files', 'CobaltTable');
		$table->load(array(
			'filename' => $filename
		));
		if(! $table->id)
		{
			$table->save($data);
		}

		return $table;
	}

	protected function handle_form_data($file, $index)
	{

	}
}
