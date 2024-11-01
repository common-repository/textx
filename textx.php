<?php

/*
 * Plugin Name:   TextX
 * Version:       1.0.1
 * Plugin URI:    http://www.kurtpolinar.com/projects/textx-plugin/
 * Description:   A simple widget on sidebar. Does the same with the Text widget but it has an option to exclude or include the widget on pages that are specified within the widget. It is mostly used on pages that require content to show once in every page. It supports multiple instances.
 * Author:        Kurt Polinar
 * Author URI:    http://www.kurtpolinar.com
 */

require_once "html_form_functions.php";

// Register widgets
function textx_register_widgets() {

   if ( !$options = get_option('widget_textx') )
		$options = array();

	$widget_ops = array('classname' => 'widget_textx', 'description' => __('Arbitrary text or HTML with X-clude & In-clude'));
	$control_ops = array('id_base' => 'textx', 'width' => 400, 'height' => 350);
	$name = __('TextX');

	$registered = false;
	foreach ( array_keys($options) as $o ) {
		// Old widgets can have null values for some reason
		if ( !isset($options[$o]['widget_textx_text']) )
			continue;

		$id = "textx-$o"; // Never never never translate an id
		$registered = true;
		wp_register_sidebar_widget( $id, $name, 'widget_textx', $widget_ops, array( 'number' => $o ) );
		wp_register_widget_control( $id, $name, 'widget_textx_control', $control_ops, array( 'number' => $o ) );
	}

	// If there are none, we register the widget's existance with a generic template
	if ( !$registered ) {
		wp_register_sidebar_widget( 'textx-1', $name, 'widget_textx', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'textx-1', $name, 'widget_textx_control', $control_ops, array( 'number' => -1 ) );
	}
}

// Example: Use custom fields to change the name of the widget
// Hint: Keep the title blank if you don't want a title in the sidebar

function widget_textx( $args, $widget_args = 1 ) {
	global $post;

	extract( $args, EXTR_SKIP );
		if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
		$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	// Data is stored as array:	 array( number => data for that instance of the widget, ... )
	$options = get_option('widget_textx');
	if ( !isset($options[$number]) ) return;
	
	$isInclude = ( (is_single($post->ID) || is_page($post->ID)) && in_array($post->ID, explode(',', $options[$number]['widget_textx_include'])) ) ? true:false;
	$isExclude = ( (is_single($post->ID) || is_page($post->ID)) && in_array($post->ID, explode(',', $options[$number]['widget_textx_exclude'])) ) ? true:false;
	$showAll = ($options[$number]['widget_textx_showall'] != '') ? true:false;
	
	if (($showAll && !$isExclude) || $isInclude) {
		$isFiltered = ($options[$number]['widget_textx_filtered'] != '') ? true:false;
		
		// Output the actual widget...
		echo $before_widget;
		echo $before_title . $options[$number]['widget_textx_title'] . $after_title;

		echo '<div class="textwidget">'.($isFiltered) ? wpautop($options[$number]['widget_textx_text']):$options[$number]['widget_textx_text'].'</div>';
		echo $after_widget;
	}
}

function widget_textx_control( $widget_args = 1 ) {
	global $wp_registered_widgets;
	static $updated = false; // Whether or not we have already updated the data after a POST submit

	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	// Data is stored as array:	 array( number => data for that instance of the widget, ... )
	$options = get_option('widget_textx');
	if ( !is_array($options) )
		$options = array();

	// We need to update the data
	if ( !$updated && !empty($_POST['sidebar']) ) {
		// Tells us what sidebar to put the data in
		$sidebar = (string) $_POST['sidebar'];

		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		foreach ( $this_sidebar as $_widget_id ) {
			if ( 'widget_textx' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				if ( !in_array( "textx-$widget_number", $_POST['widget-id'] ) ) // the widget has been removed
					unset($options[$widget_number]);
			}
		}

		foreach ( (array) $_POST['textx-widget'] as $widget_number => $widget_textx ) {
			if ( !isset($widget_textx['widget_textx_text']) && isset($options[$widget_number]) ) // user clicked cancel
				continue;

			$widget_textx_title = wp_specialchars( $widget_textx['widget_textx_title'] );
			$widget_textx_text = stripslashes( wp_filter_post_kses( addslashes( $widget_textx['widget_textx_text']) ) );
			$widget_textx_include = wp_specialchars( $widget_textx['widget_textx_include'] );
			$widget_textx_exclude = wp_specialchars( $widget_textx['widget_textx_exclude'] );
			$widget_textx_showall = wp_specialchars( $widget_textx['widget_textx_showall'] );
			$widget_textx_filtered = wp_specialchars( $widget_textx['widget_textx_filtered'] );

			$options[$widget_number] = compact('widget_textx_title', 'widget_textx_text', 'widget_textx_include', 'widget_textx_exclude', 'widget_textx_showall', 'widget_textx_filtered');
		}

		update_option('widget_textx', $options);
		$updated = true; // So that we don't go through this more than once
	}

	// Here we echo out the form
	if ( -1 == $number ) { // We echo out a template for a form which can be converted to a specific form later via JS
		$widget_textx_title = '';
		$widget_textx_text = '';
		$widget_textx_include = '';
		$widget_textx_exclude = '';		
		$number = '%i%';
	} else {
		$widget_textx_title = esc_attr($options[$number]['widget_textx_title']);
		$widget_textx_text = stripslashes(esc_attr($options[$number]['widget_textx_text']));
		$widget_textx_include = esc_attr($options[$number]['widget_textx_include']);
		$widget_textx_exclude = esc_attr($options[$number]['widget_textx_exclude']);
		$widget_textx_showall = esc_attr($options[$number]['widget_textx_showall']);
		$widget_textx_filtered = esc_attr($options[$number]['widget_textx_filtered']);
	}

   // Output the code for administrators to make changes
?>
  <label>Title:<br /></label> <?php textx_textbox('textx-widget['.$number.'][widget_textx_title]', $widget_textx_title, array('class'=>'widefat')); ?>
  <label>Text:<br /></label> <?php textx_textarea('textx-widget['.$number.'][widget_textx_text]', $widget_textx_text, array('cols'=>'20', 'rows'=>'16', 'class'=>'widefat')); ?>
  <label>Include:<br /></label> <?php textx_textbox('textx-widget['.$number.'][widget_textx_include]', $widget_textx_include, array('class'=>'widefat')); ?><br />
  <label>Exclude:<br /></label> <?php textx_textbox('textx-widget['.$number.'][widget_textx_exclude]', $widget_textx_exclude, array('class'=>'widefat')); ?><br />
  <small>Enter post/page ID separated by comma ","</small><br />
  <label><?php textx_checkbox('textx-widget['.$number.'][widget_textx_showall]', $widget_textx_showall, true); ?> Show on all posts/pages </label>
  <label><?php textx_checkbox('textx-widget['.$number.'][widget_textx_filtered]', $widget_textx_filtered, true); ?> Automatically add paragraphs </label>
<?php
}
add_filter('widget_textx', 'do_shortcode');

if (function_exists('add_action')) {
   add_action('widgets_init', 'textx_register_widgets');   
}


?>