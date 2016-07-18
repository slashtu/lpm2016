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
if (!class_exists('Mk_Options_Framework_Fields_Css_Class_Selector')) {
    
    class Mk_Options_Framework_Fields_Css_Class_Selector extends Mk_Options_Framework
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
            $this->description = $value['desc'];
            $this->options = array(
                'body' => 'Body',
                'h1' => "Heading 1",
                'h2' => "Heading 2",
                'h3' => "Heading 3",
                'h4' => "Heading 4",
                'h5' => "Heading 5",
                'h6' => "Heading 6",
                'p' => "Paragraphs",
                'a' => "Links",
                'textarea,input,select,button' => "Form Elements",
                '#mk-page-introduce' => 'Page Titles',
                ".the-title" => 'Blog & Portfolio Titles',
                ".mk-edge-title, .edge-title" => 'Edge Slider Title',
                ".mk-edge-desc, .edge-desc" => 'Edge Slider Description',
                '.main-navigation-ul, .mk-vm-menuwrapper' => 'Main Navigation',
                '#mk-footer-navigation ul li a' => 'Footer Navigation',
                '.vm-header-copyright' => 'Vertical Header Copyright Text',
                '.mk-footer-copyright' => 'Footer Copyright',
                '.mk-content-box' => 'Content Box',
                ".filter-portfolio a" => 'Portfolio Filter Links',
                ".mk-button" => 'Buttons',
                ".mk-blockquote" => 'Blockquote Shortcode',
                '.mk-pricing-table .mk-offer-title, .mk-pricing-table .mk-pricing-plan, .mk-pricing-table .mk-pricing-price' => 'Pricing Table Headings',
                '.mk-tabs-tabs a' => 'Tabs Shortcode',
                '.mk-accordion-tab' => 'Accordion Shortcode',
                '.mk-toggle-title' => 'Toggle Shortcode',
                '.mk-dropcaps' => 'Dropcaps Shortcode',
                '.mk-single-price, .mk-price' => 'Woocommerce Price Amount',
                '.mk-imagebox' => 'Image Box Shortcode',
                '.mk-event-countdown' => 'Event Countdown Shortcode',
                '.mk-fancy-title' => 'Fancy Title',
                '.mk-button-gradient' => 'Gradient Buttons',
                '.mk-iconBox-gradient' => 'Gradient Icon Box',
                '.mk-custom-box' => 'Custom Box',
                '.mk-ornamental-title' => 'Ornamental Title',
                '.mk-subscribe' => 'Subscribe',
                '.mk-timeline' => 'Timeline',
                '.mk-blog-container .mk-blog-meta .the-title, .post .blog-single-title, .mk-blog-hero .content-holder .the-title, .blog-blockquote-content, .blog-twitter-content' => 'Blog Headings',
                '.mk-blog-container .mk-blog-meta .the-excerpt p, .mk-single-content p' => 'Blog Body',
                '.mk-employees .mk-employee-item .team-info-wrapper .team-member-name' => 'Employee Title',
                '.mk-testimonial-quote' => 'Testimonial Quote'
            );
        } 
        public function render() {
            
            $output = '<select class="mk-select mk-chosen" name="' . $this->id . '[]" id="' . $this->id . '" multiple="multiple" style="width:70%;">';
            if (!empty($this->options) && is_array($this->options)) {
                foreach ($this->options as $key => $option) {
                    $output.= '<option value="' . $key . '"';
                    if (isset($this->saved_options[$this->id])) {
                        if (is_array($this->saved_options[$this->id])) {
                            if (in_array($key, $this->saved_options[$this->id])) {
                                $output.= ' selected="selected"';
                            }
                        }
                    } 
                    else if (in_array($key, $this->default)) {
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
