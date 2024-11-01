<?php
/*
License: Copyright 2010 Kurt Polinar, All Rights Reserved (http://kurtpolinar.com)
*/

// 
if (!function_exists('is_vector')) {
   function is_vector( &$array ) {
      if ( !is_array($array) || empty($array) ) {
         return -1;
      }
      $next = 0;
      foreach ( $array as $k => $v ) {
         if ( $k !== $next ) return false;
         $next++;
      }
      return true;
   }
}

// form generation functions
function textx_dropdown($name, $data, $option="") {
   if (get_option($name)) { $option = get_option($name); }

   ?>
   <select name="<?php echo $name ?>">
   <?php

   // If the array is a vector (0, 1, 2...)
   if (is_vector($data)) {
      foreach ($data as $item) {
         if ($item == $option) {
            echo '<option selected="selected">' . $item . "</option>\n";
         }
         else {
            echo "<option>$item</option>\n";
         }
      }
   }

   // If the array contains name-value pairs
   else {
      foreach ($data as $value => $text) {
         if ($value == $option) {
            echo '<option value="' . $value . '" selected="selected">' . $text . "</option>\n";
         }
         else {
            echo '<option value="' . $value . '">' . "$text</option>\n";
         }
      }
   }

   ?>
   </select>
   <?php
}

function textx_textbox($name, $value="", $options = array()) {
   if (get_option($name)) { $value = get_option($name); }

   $textbox = '<input type="text" name="'.$name.'" value="'.$value.'"';
	if (is_array($options) && !empty($options) && !is_vector($options)) {
		foreach ($options as $option_name => $option_value) {			
			$textbox .= ' '.$option_name.'="'.$option_value.'"';		
		}		
	} else {
		$textbox .= ' size="15"';
	}
	$textbox .= ' />';
   
   echo $textbox;

}

function textx_radio($name, $values=array(), $selected=false, $include_break = false) {
   if (get_option($name)) { $selected = get_option($name); }
	foreach ($values as $option_name => $option_value) {
   ?>
   <label style="text-align: left; width: auto; padding-left: 12px;"><?php echo $option_name; ?> <input type="radio" name="<?php echo $name ?>" value="<?php echo $option_value ?>" <?php echo ($option_value==$selected) ? "checked":""; ?> /></label>
   <?php echo ($include_break) ? "<br />":""; ?>
   <?php
	}
}

function textx_textarea($name, $value="", $options = array()) {
	if (get_option($name)) { $value = get_option($name); }

	$textarea = '<textarea name="'.$name.'"';
	if (is_array($options) && !empty($options) && !is_vector($options)) {
		foreach ($options as $option_name => $option_value) {		
				$textarea .= ' '.$option_name.'="'.$option_value.'"';
		
		}		
	} else {
		$textarea .= ' cols="50" rows="8"';
	}
	$textarea .= '>'.$value.'</textarea>';
   
   echo $textarea;
}

function textx_checkbox($name, $value = null, $option = false) {
   ?>
   <?php if (!$option && get_option($name)): ?>
   <input type="checkbox" name="<?php echo $name ?>" checked="checked" />
   <?php elseif ($option && $value == 'on'): ?>
   <input type="checkbox" name="<?php echo $name ?>" checked="checked"/>
   <?php else: ?>
   <input type="checkbox" name="<?php echo $name ?>"/>
   <?php endif; ?>
   <?php
}
?>