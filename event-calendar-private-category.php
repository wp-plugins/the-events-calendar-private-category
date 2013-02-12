<?php
/*
Plugin Name: The Events Calendar: Private Category
Description: Add-on to The Events Calendar. This plug-in allows event categories to be set as private and viewable only to logged-in users.
Version: 1.0
Author: don4g, arifwn
Author URI: http://www.smeans.com/
Text Domain: 
License: 
*/

require_once( dirname(__FILE__) . '/event-category.class.php' );

EventCategoryExtension::instance();
