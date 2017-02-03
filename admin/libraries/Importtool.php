<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
#------------------------------------------------------
# Library to import data
#------------------------------------------------------
class Importtool {
	
	#------------------------------------------------------
	# Initialise our variables
	#------------------------------------------------------
	function initialize($params = array()) {

		if (count($params) == 0) return;
		
		foreach ($params as $key => $val) {
			$this->$key = $val;			
		}
		
	}

	#------------------------------------------------------
	# Upload file
	#------------------------------------------------------
	function upload_file($field_name='userfile') {
		
		$shopit =& get_instance();
				
		// We need to upload the file to the server so we can keep it accessible during 
		// the import wizard. Temp files are deleted immediately after the script 
		// finishes its execution, so they're no good!!
		$this->filepath = $_SERVER['DOCUMENT_ROOT'] . "/data_uploads";
		
		// Load the upload helper
		$config['upload_path'] 	 = $this->filepath;
		$config['allowed_types'] = "*";
		$config['overwrite'] 	 = true;
		$shopit->load->library('upload', $config);
		
		// Upload the file
		if ($shopit->upload->do_upload($field_name)) {
			// Return the full path to the file so
			// we can pass to other functions
			$data = $shopit->upload->data();
			return $data['full_path'];
		} else {
			return FALSE;
		}
		
	}
	
	#------------------------------------------------------
	# Match the columns
	#------------------------------------------------------
	function match_columns($file) {

		// Auto-detect line endings - we need this!
		ini_set('auto_detect_line_endings', TRUE);

		// Set some vars here
		$i = 0;
		$r = 0;	
		$k = 0;
		$select_html = "";
		$html = "";

		//Begin looping through the csv file to get the headings and create a select dropdown
		if (($handle = fopen($file, "r")) !== FALSE) {
		
			// Set the opening <select> tag - notice the {col_name} tag 
			// which we'll substitute later on
			$select_html  = '<select name="col_match[{col_name}]" class="dropdown">' . "\n";
			$select_html .= '<option value="-1">-- IGNORE -- </option>' . "\n";

			while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {

				// Incremental counter
		    	$i++;
		    	
		    	// We only need to get the first row for the headers
		    	if ($i == 1) {
			    	
			    	// Loop through each field and create the <select>'s option. 
			    	// $key is the column number (remember 0 is the first col).
			    	foreach ($data as $key => $label) {
				    	$select_html .= '<option value="'.$key.'">'.$label.'</option>' . "\n";
			    	}
		    	
					// Stop the while loop so we don't go any further 
					// than the first row in the csv file.
					break;
		    			    	
		    	}
		    	
		    } // End while
		    
		    // Close off the <select> dropdown
		    $select_html .= "</select>\n";
		
		} // End if
		
		// Now, we'll loop through each of our table columns and pre-select the options
		foreach ($this->columns as $label => $col_name) {

			// Increment counter
			$r++;
			$k++;
			
			// Set a class of even or odd - for display purposes
			$post = ($r&1) ? "odd" : "even";
			
			// Auto-select the option if it's in the <select> we created earlier
			$auto_select_html = preg_replace("@>(".uppercase($col_name).")<\/@", ' selected="selected">$1</', $select_html);			
			
			// Create the table row html
			$html .= '<tr class="'.$post.'">' . "\n";
			$html .= "<td><strong>$label</strong></td>\n";
			$html .= '<td class="smallprint">';
			if ($this->update) {
			$unique_id_autoselect = ($k == 1) ? 'checked="checked"' : '';
			$html .= '<input type="radio" name="unique_id" value="' .$col_name . '" title="Unique ID" ' . $unique_id_autoselect . ' /> &nbsp;';
			}
			$html .= uppercase($col_name)."</td>\n";
			$html .= "<td>".str_replace('{col_name}', $col_name, $auto_select_html)."</td>\n";
			$html .= "</tr>\n";
			
		}

		// Return the html
		return $html;
		
	}

	#------------------------------------------------------
	# Preview spreadsheet import
	# - create a final array of the data that's been selected
	#   for import from the original spreadsheet
	#------------------------------------------------------
	function preview($file) {
	
		// Set CI instance
		$shopit =& get_instance();

		// Auto-detect line endings - we need this!
		ini_set('auto_detect_line_endings', TRUE);
		
		// Reset some vars
		$i = 0;
		$r = 0;
		$d = 0;
		$col_count = 0; // We'll use this to count the number of columns
		$html = "";
		$tbl_header = "";
		$tbl_body = "";

		if ($shopit->input->post('first_row_header') != 'YES') {
			$i = 2;
		}
				
		// Create table header but only include those which are NOT ignored
		$tbl_header  = "<thead>\n";
		$tbl_header .= "<tr>\n";
		
		foreach ($_POST['col_match'] as $db_col_name => $file_col_number) {
			if ($file_col_number >= 0) {
			
				// Get human readable label if it exists
				$label = array_search($db_col_name, $this->columns);
				$col_label = ($label != false) ? $label : $db_col_name;
				
				$tbl_header .= "<th>$col_label</th>\n";
				$col_count++;
			}
		} 
		$tbl_header .= "</tr>\n";
		$tbl_header .= "</thead>\n";
		$tbl_body 	.= "<tbody>\n";

		// Begin looping through the csv file to get the 
		// headings and create a select dropdown
		if (($handle = fopen($file, "r")) !== FALSE) {

			while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {

				$i++;
		    	
		    	// Ignore the first row (only if the option was ticked at the very beginning)
		    	if ($i > 1) {
		    	
		    		$r++; // Used for odd/even class
		    		$d++; // Used for array key count
		    		$row_array = array(); // Used to create an array of data to post in one go

					// Set a class of even or odd - for display purposes
					$post = ($r&1) ? "odd" : "even";
					
					$tbl_body .= '<tr class="'.$post.'">'."\n";
		    		
		    		foreach ($_POST['col_match'] as $db_col_name=>$file_col_number) {
						if ($file_col_number >= 0) {
							
							// Clean up field data
							$cell = trim($data[$file_col_number]);
							$invalid_chars = array('�', '$', '�');
							foreach($invalid_chars as $char) {
								$cell = str_replace($char, '' , $cell);
							}
			    			
			    			// Check if this is a date field and convert to mysql's format
			    			// - Disabled due to frequent issues
			    			#if ( preg_match('@^([0-9]+)-(.+?)-([0-9+])@', $cell) ) {
			    			#	$cell = date('Y-m-d', strtotime($cell));
			    			#}
			    			
			    			// Check for any inches (e.g. 19")
			    			$cell = preg_replace('@([0-9]+)\"@', '$1-inch', $cell);
			    			
			    			$tbl_body .= "<td>\n";
			    			$tbl_body .= sprintf('<code>%s</code>', htmlentities($cell));
			    			// The following line has been removed due to the 1000 PHP $_POST field limit
			    			#$tbl_body .= '<input type="hidden" name="row['.$d.']['.$db_col_name.']" value="'.$cell.'" />';
			    			$tbl_body .= "</td>\n";
			    			
			    			// We'll now create an array which contains this columns data (with base64 encoded value to preserve it)
			    			$row_array[] = "$db_col_name => " . base64_encode($cell);
			    			
			    		}

		    		}

					// Convert the array into a semi-colon separated list
					$row_array_csv = implode('|', $row_array);
					
					// Now create the hidden field containing everything we need to post for this row and base64 encode it to protect it
					$tbl_body .= '<input type="hidden" name="row['.$d.']" value="'.base64_encode($row_array_csv).'" />';

					$tbl_body .= "</tr>\n";

		    	}
			
			}
			
		}

		$tbl_body .= "</tbody>\n";

		// Return the html
		$html  = $tbl_header;
		$html .= $tbl_body;
		
		return $html;

	}

	#------------------------------------------------------
	# Import the data
	# $autovals should be a string based on the import type
	# e.g. 'inventory' or 'orders', etc
	#------------------------------------------------------
	function do_insert($rows=array(), $redirect='dashboard', $autovals=FALSE) {
	
		// Load what we need
		$shopit =& get_instance();
		$shopit->load->database();
		
		// Add the row to the database
		if (!empty($rows)) {
			foreach ($rows as $row) {
			
				// Decode the passed data
				$row = base64_decode($row);
				
				// Split the row into columns by checking for the ";" seperator
				$column = explode('|', $row);
				
				// Loop through each column to create the $data array
				// we need to use to insert the data
				foreach($column as $string) {
					
					// Match the string for key and values. The last $ in the 
					// regex checks for null values.
					preg_match('@^(.+?) \=> (.+?)?$@', $string, $match);
					
					// Get the matches
					$key = trim($match[1]); // Key
					$val = trim(base64_decode($match[2])); // Value (decode it)
					
					// Add to the array
					$data[$key] = $val;
					
				}

				// Begin: We can add a few auto-created (or adjusted) values here
				switch ($autovals) {
					default:
						break;
					
					// Inventory import
					case "inventory":
						// If product_ image is set do the necessary
						if (isset($data['product_image'])) {

							// Add custom image processing code here...
							
							
							// Append the last semi-colon
							if ($data['product_image'] != '') {
								$data['product_image'] = $data['product_image'] . ";";
							}
							
						}
						
						// If product_description is set, auto-format it
						if (isset($data['product_description'])) {
							$data['product_description'] = autop( $this->cleanse($data['product_description']) );
						}
						
						// Likewise, do the same for the product_excerpt
						if (isset($data['product_excerpt'])) {
							$data['product_excerpt'] = autop($this->cleanse($data['product_excerpt']));
						}
						
						// Set the date added field
						$data['date_added'] = date('Y-m-d H:i:s', time());
						break;
				}
				// End;
				
				// Uncomment this line for testing purposes
				#echo "<pre>" . print_r($data, true) . "</pre>";
				
				// Now insert this row of data
				$shopit->db->insert($this->table, $data);
			}
		}
		
		// All import done, so delete the uploaded file
		$this->filepath = $shopit->input->post('file_upload');
		@unlink($this->filepath);
		
		// Redirect
		$shopit->session->set_flashdata('notice', "Woohoo! We've successfully imported your data...");
		redirect($redirect);
		
	}

	#------------------------------------------------------
	# Update existing data
	#------------------------------------------------------
	function do_update($rows=array(), $redirect='dashboard', $autovals=FALSE) {
	
		// Load what we need
		$shopit =& get_instance();
		$shopit->load->database();
		
		// Add the row to the database
		if (!empty($rows)) {
			foreach ($rows as $row) {
				
				// Decode the passed data
				$row = base64_decode($row);
				
				// Split the row into columns by checking for the ";" seperator
				$column = explode('|', $row);
				
				// Loop through each column to create the $data array
				// we need to use to insert the data
				foreach($column as $string) {
					
					// Match the string for key and values. The last $ in the 
					// regex checks for null values.
					preg_match('@^(.+?) \=> (.+?)?$@', $string, $match);
					
					// Get the matches
					$key = trim(($match[1])); // Key
					$val = trim(base64_decode($match[2])); // Value (decode it)
					
					// Add to the array
					$data[$key] = $val;
					
				}

				// Begin: We can add a few auto-created (or adjusted) values here
				switch ($autovals) {
					default:
						break;
					
					// Inventory import
					case "inventory":
						if (array_key_exists('product_description', $data)) {
							$data['product_description'] = autop( $this->cleanse($data['product_description']) );
						}
						if (array_key_exists('product_excerpt', $data)) {
							$data['product_excerpt'] = autop( $this->cleanse($data['product_excerpt']) );
						}
						break;
				}
				// End;

				// Uncomment this line for testing purposes
				#echo "<pre>" . print_r($data, true) . "</pre>";
				
				// Now update this row of data
				$shopit->db->where($shopit->input->post('unique_id'), $data[$shopit->input->post('unique_id')]);
				$shopit->db->update($this->table, $data);
			}
		}
		
		// All import done, so delete the uploaded file
		$this->filepath = $shopit->input->post('file_upload');
		@unlink($this->filepath);
		
		// Redirect
		$shopit->session->set_flashdata('notice', "Woohoo! We've successfully updated your data...");
		redirect($redirect);
		
	}

	#------------------------------------------------------
	# Remove invalid characters
	#------------------------------------------------------
	private function cleanse($string) {
		
		$chars = array(
			'Û�' 	 => '',
			'Ûù' 	 => '',
			'&nbsp;' => ' ',
			'�'		 => '&pound;',
			'w ¨'	 => '',
			'¨'	 => '',
			'Û_'	 => '',
			'Û'	 => '',
			'©'	 => ''
		);
		
		foreach ($chars as $find => $replace) {
			$string = str_replace($find, $replace, $string);
		}
	
		// Reject overly long 2 byte sequences, as well as characters above U+10000 and replace with ?
		$string = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.
		 '|[\x00-\x7F][\x80-\xBF]+'.
		 '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.
		 '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
		 '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
		 '', $string );
		 
		// Reject overly long 3 byte sequences and UTF-16 surrogates and replace with ?
		$string = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]'.
		 '|\xED[\xA0-\xBF][\x80-\xBF]/S','', $string );
	
		return $string;
		
	}

}