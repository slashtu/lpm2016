<?php

/**
 *
 * @author      Bob Ulusoy
 * @copyright   Artbees LTD (c)
 * @link        http://artbees.net
 * @since       Version 5.0
 * @package     artbees
 */

// Exit if accessed directly
if (!defined('THEME_FRAMEWORK')) exit('No direct script access allowed');

// Don't duplicate me!
if (!class_exists('Mk_Options_Framework_Fields_Font_Weight')) {
    
    class Mk_Options_Framework_Fields_Font_Weight extends Mk_Options_Framework
    {
        
        /**
         * Field Constructor.
         *
         * @since       1.0.0
         * @access      public
         * @return      void
         */
        function __construct($value, $saved_options) {
            
            $this->saved_options = $saved_options;
            $this->field = $value['type'];
            $this->name = $value['name'];
            $this->id = $value['id'];
            $this->default = parent::saved_default_value($this->id, $value['default']);
            $this->description = isset($value['desc']) ? $value['desc'] : '';
            $this->options = array(
                '100' => '100',
                '200' => '200 (light)',
                '300' => '300',
                "400" => '400 (normal)',
                "500" => '500',
                "600" => '600',
                "700" => '700 (bold)',
                "bolder" => '800 (bolder)',
                "900" => '900',
            );
        }
        
        public function render() {
            
            $output = '<select class="mk-select" name="' . $this->id . '" id="' . $this->id . '">';
            $output.= '<option value="inherit">' . __('Select Option', 'mk_framework') . '</option>';
            if (!empty($this->options) && is_array($this->options)) {
                foreach ($this->options as $key => $option) {
                    $output.= '<option value="' . $key . '"';
                    if (isset($this->saved_options[$this->id])) {
                        if (stripslashes($this->saved_options[$this->id]) == $key) {
                            $output.= ' selected="selected"';
                        }
                    } 
                    else if ($key == $this->default) {
                        $output.= ' selected="selected"';
                    }
                    $output.= '>' . $option . '</option>';
                }
            }
            $output.= '</select>';
            
            return parent::field_wrapper($this->id, $this->name, $this->description, $output);
        }
        
        /**
         * Enqueue Function.
         * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
         *
         * @since       1.0.0
         * @access      public
         * @return      void
         */
        public function enqueue() {
        }
    }
}
