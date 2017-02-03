<?php

class Filebrowser extends CI_Controller {

	function Filebrowser() {
		parent::__construct();
		
		//Load helpers & libraries
		$this->load->database();
		#$this->load->helper('directory');
		$this->load->helper('file');
		$this->load->library('image_lib');

		//Load config settings
		$this->load->model('settings_model');
		$this->settings_model->initConfig();
		
		//Some additional settings for THIS controller
		$this->config->set_item('doc_path', $_SERVER['DOCUMENT_ROOT'].'/docs/');

		/* Login check */
		$this->load->model('login_model');
		if(!$this->login_model->check_session()){
			redirect('/login');
		}
		/* End: Login check */

	}

	#------------------------------------------------------
	# File Browser v1.0
	#------------------------------------------------------
	function index() {
						
		$files = get_dir_file_info(config_item('doc_path'));
				
		//Lets add a few things to the array...
		foreach($files as $file) {
		
			$mime = get_mime_by_extension($file['server_path']);
			
			//Get the mime info
			$mime_info = explode('/', $mime);
			
			//image, document, etc
			switch ($mime_info[0]) {
				
				case 'application':
					$kind = 'document';
					break;
				
				default:
					$kind = 'image';
					break;
				
			}
			
			$type = $mime_info[1]; //pdf, jpeg, etc
			
			$data['files'][] = array(
				'name' 			=> $file['name'],
				'server_path'	=> $file['server_path'],
				'size'			=> floor($file['size']/1000)."KB",
				'modified'		=> date('d/m/Y H:i A', ($file['date'])),
				'relative_path' => $file['relative_path'],
				'url'			=> site_root("docs/$file[name]"),
				'mime'			=> $mime,
				'kind'			=> $kind,
				'type'			=> $type,
			);
			
		}
		
		$data['title'] = "File Manager";
		$data['redirect_url'] = (isset($_GET)) ? sprintf('%s?%s', current_url(), http_build_query($_GET)) : current_url();
		$this->load->view('global/filebrowser', $data);
		
	}

	#------------------------------------------------------
	# Display thumbnail of image
	#------------------------------------------------------
	function thumb() {
		
		ini_set("memory_limit","968M");
		
		$image 	= config_item('doc_path') . ($this->uri->segment(3)); //file to get from  server
		
		//echo $image;
		
		$config['image_library'] 	= 'gd2';
		$config['source_image'] 	= $image;
		$config['create_thumb'] 	= TRUE;
		$config['dynamic_output'] 	= TRUE;
		$config['maintain_ratio'] 	= TRUE;
		$config['master_dim'] 		= 'auto';
		$config['width']  			= 35;
		$config['height'] 			= 35;
		$config['quality'] 			= 90;
		
		$this->image_lib->initialize($config);
		
		$this->image_lib->resize();
	    
	}

	#------------------------------------------------------
	# Upload file
	#------------------------------------------------------
	function upload() {
	
		ini_set("memory_limit","968M");
		error_reporting(1);

		$config['upload_path'] 	 = $_SERVER['DOCUMENT_ROOT'].'/docs/';
		$config['allowed_types'] = 'pdf|doc|docx|xls|xlsx|png|gif|jpg|jpeg|JPG|JPEG|txt|psd|tiff|TIFF|ppt|pptx|mp3|avi|flv|mov|mp4|ogg';
		$config['overwrite'] = TRUE;
		$this->load->library('upload', $config);
		$this->upload->do_upload('file');
		
		//Set flash data message
		if (!$this->upload->display_errors()) {
			$this->session->set_flashdata('notice', 'File uploaded successfully.');
		} else {
			$this->session->set_flashdata('notice', $this->upload->display_errors());
		}
		
		redirect($this->input->post('redirect_url'));

	}	
	
}