<?php
	class Image extends CI_Controller {
		
		function Image() {
			parent::__construct();
		
			$this->load->database();

			$this->load->model('settings_model');
			$this->settings_model->initConfig();
			
			$this->load->library('image_lib');
		}
		
		function index() {
			redirect();
		}
				
		function resize() {
			
			ini_set("memory_limit","96M");
			
			$image 	= $_SERVER['DOCUMENT_ROOT'].$this->config->item('path_to_uploads').$this->uri->segment(3); //file to get from  server
			
			// Set new dimensions we want
			if ($this->uri->segment(4) != '' && $this->uri->segment(5) != ''):
				$newWidth 	= $this->uri->segment(4); //set thumbnail width via url
				$newHeight 	= $this->uri->segment(5); //set thumbnail height via url
			else:
				$newWidth 	= $this->config->item('image_width');  //set default thumbnail width
				$newHeight 	= $this->config->item('image_height'); //set default thumbnail height
			endif;


			$config['image_library'] 	= 'gd2';
			$config['source_image'] 	= $image;
			$config['create_thumb'] 	= FALSE;
			$config['dynamic_output'] 	= TRUE;
			$config['maintain_ratio'] 	= TRUE;
			$config['master_dim'] 		= 'auto';
			$config['width']  			= $newWidth;
			$config['height'] 			= $newHeight;
			$config['quality'] 			= $this->config->item('image_quality');
			
			$this->image_lib->initialize($config);
			
			$this->image_lib->resize();

		}
				

		function thumbnail() {
			
			ini_set("memory_limit","96M");

			$image 	= $_SERVER['DOCUMENT_ROOT'].$this->config->item('path_to_uploads').$this->uri->segment(3); //file to get from  server
			
			//echo $image;
			
			$config['image_library'] 	= 'gd2';
			$config['source_image'] 	= $image;
			$config['create_thumb'] 	= TRUE;
			$config['dynamic_output'] 	= TRUE;
			$config['maintain_ratio'] 	= TRUE;
			$config['master_dim'] 		= 'auto';
			$config['width']  			= $this->config->item('thumbnail_width');
			$config['height'] 			= $this->config->item('thumbnail_height');
			$config['quality'] 			= $this->config->item('image_quality');
			
			$this->image_lib->initialize($config);
			
			$this->image_lib->resize();
		    
		}
				




	}

?>