<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
#------------------------------------------------------
# Library for Dashboard Widgets
#------------------------------------------------------
class Widgets {

	#------------------------------------------------------
	# Is widget active? If not show it as an "inactive" option.
	# - Also check if user has permissions to view it
	# 	@param $widget = widget name
	# 	@param $mywidgets = user's active widgets
	# 	@param $permission_check = the permission to check against
	#------------------------------------------------------
	function is_active($widget, $mywidgets=array(), $permission_check=false) {
		
		$lb = "\n"; 

		// Check permissions and if this widget is already active
		if ($permission_check) {
			foreach ($mywidgets as $item) {
				if ($item->widget === $widget) {
		            $this_widget = TRUE;
				}
			}
		} else {
			$this_widget = TRUE;
		}

		// If not true, display the widget option
		if (!$this_widget) {

			$html  = "<li>$lb";
			$html .= "<label>$widget</label>$lb";
			$html .= "<input type=\"hidden\" name=\"widget[]\" value=\"$widget\">$lb";
			$html .= "<input type=\"hidden\" name=\"id[]\" value=\"0\" />$lb";
			$html .= "</li>$lb";
	
			echo $html;
		
		}
	
	}

}