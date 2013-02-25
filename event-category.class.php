<?php

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

if ( !class_exists( 'EventCategoryExtension' ) ) {

	class EventCategoryExtension {
		const TAXONOMY = 'tribe_events_cat';
		const POSTTYPE = 'tribe_events';

		protected static $instance;

		public $pluginDir;
		public $pluginPath;
		public $pluginUrl;

		/* Static Singleton Factory Method */
		public static function instance() {
			if (!isset(self::$instance)) {
				$className = __CLASS__;
				self::$instance = new $className;
			}
			return self::$instance;
		}

		protected function __construct( ) {
			$this->pluginPath = trailingslashit( dirname(__FILE__) );
			$this->pluginDir = trailingslashit( basename( $this->pluginPath ) );
			$this->pluginUrl = plugins_url().'/'.$this->pluginDir;

			add_action( 'created_' . self::TAXONOMY, array( $this, 'create_term' ), 15, 2 );
			add_action( 'edit_term', array( $this, 'update_term' ), 15, 3 );
			add_action( 'save_post', array( $this, 'addPrivateCategoryPostMeta' ), 15, 2 );
			add_action( 'wp_ajax_private_category_check', array( $this, 'ajaxPrivateCategoryCheck' ) );

			global $pagenow;
			
			if ( in_array( $pagenow, array('edit-tags.php') ) )	{
				add_action( self::TAXONOMY . '_edit_form', array( $this, 'addPrivateOptionBox' ), 1, 0 );
				add_action( self::TAXONOMY . '_add_form_fields', array( $this, 'addPrivateOptionBoxFront' ), 1, 0 );
			}
		}


		public function addPrivateCategoryPostMeta( $postId, $post ) {
			// only continue if it's an event post
			if ( $post->post_type != self::POSTTYPE || defined('DOING_AJAX') ) {
				return;
			}
			// don't do anything on autosave or auto-draft either or massupdates
			if ( wp_is_post_autosave( $postId ) || $post->post_status == 'auto-draft' || isset($_GET['bulk_edit']) || (isset($_REQUEST['action']) && $_REQUEST['action'] == 'inline-save') ) {
				return;
			}

			if ( !current_user_can( 'edit_tribe_events' ) )
				return;

			if ($this->isPrivate($postId)) {
				if( remove_action( 'save_post', array( $this, 'addPrivateCategoryPostMeta' ), 15 ) ) {

					if ($post->post_status == 'publish') {
						$eventPost = array();
						$eventPost['ID'] = $postId;
						$eventPost['post_status'] = 'private';
						wp_update_post($eventPost);
					}

					add_action( 'save_post', array( $this, 'addPrivateCategoryPostMeta' ), 15, 2 );
				}
			}

		}

		public function addPrivateOptionBox( ) {
			$event_cat_is_private = get_option('event_cat_is_private');

			$checked = '';
			if($event_cat_is_private) {
				if (array_key_exists('tag_ID', $_GET)) {
					if (array_key_exists($_GET['tag_ID'], $event_cat_is_private)) {
						if ($event_cat_is_private[$_GET['tag_ID']]) {
							$checked = 'checked="checked"';
						}
					}
				}
			}

			$private_option_meta_box_template = $this->pluginPath . 'admin-views/private-meta-box.php';
			include( $private_option_meta_box_template );
		}


		public function addPrivateOptionBoxFront( ) {
			
			$private_option_meta_box_template = $this->pluginPath . 'admin-views/private-meta-box-front.php';
			include( $private_option_meta_box_template );
		}

		public function create_term( $term_id, $tt_id ) {
			
			$event_cat_is_private = get_option('event_cat_is_private');
			if (!$event_cat_is_private) {
				$event_cat_is_private = array();
			}

			if (array_key_exists('private', $_POST)) {
				if ($_POST['private'] == 1) {
					// set this to private
					$event_cat_is_private[$term_id] = 1;
					update_option('event_cat_is_private', $event_cat_is_private);

					// TODO: update visibility settings for all affected events

					return;
				}
			}

			// set this to public
			$event_cat_is_private[$term_id] = 0;
			update_option('event_cat_is_private', $event_cat_is_private);

		}

		public function update_term( $term_id, $tt_id, $taxonomy ) {
			if ($taxonomy != self::TAXONOMY) {
				return;
			}

			$event_cat_is_private = get_option('event_cat_is_private');
			if (!$event_cat_is_private) {
				$event_cat_is_private = array();
			}

			if (array_key_exists('private', $_POST)) {
				if ($_POST['private'] == 1) {
					// set this to private
					$event_cat_is_private[$_POST['tag_ID']] = 1;
					update_option('event_cat_is_private', $event_cat_is_private);

					// update visibility settings for all affected events
					$this->updateAllEvent($term_id, 'publish', 'private');

					return;
				}
			}

			// set this to public
			$event_cat_is_private[$_POST['tag_ID']] = 0;
			update_option('event_cat_is_private', $event_cat_is_private);
			// update visibility settings for all affected events
			$this->updateAllEvent($term_id, 'private', 'publish');
		}

		public function updateAllEvent( $term_id, $from, $to) {

			$query = new WP_Query( 
				array(
					'post_type' => self::POSTTYPE, 
					'posts_per_page' => -1,
					'eventDisplay' => 'all',
					)
				);

			if ($query->have_posts()) {
				foreach ($query->posts as $event) {
					$categories = get_the_terms( $event->ID , self::TAXONOMY );

					if ($categories) {
						foreach ($categories as $cat) {
							if (($cat->term_id == $term_id) and ($event->post_status == $from)) {
								$eventPost = array();
								$eventPost['ID'] = $event->ID;
								$eventPost['post_status'] = $to;
								wp_update_post($eventPost);
							}
						}
					}

				}
			}
			
		}

		public function isCategoryPrivate( $category_id ) {
			$event_cat_is_private = get_option('event_cat_is_private');
			if (!$event_cat_is_private) {
				return false;
			}

			$category_id = strval($category_id);
			if (array_key_exists($category_id, $event_cat_is_private)) {
				if ($event_cat_is_private[$category_id]) {
					return true;
				}
			}
			return false;
		}

		public function isPrivate ( $postId ) {
			$categories = get_the_terms( $postId , self::TAXONOMY );
			$private = false;

			if ($categories) {
				foreach ($categories as $cat) {
					if ($this->isCategoryPrivate($cat->term_id)) {
						$private = true;
					}
				}
			}

			return $private;
		}


		public function ajaxPrivateCategoryCheck() {
			$data = array();
			$data['availabilities'] = array();

			if (array_key_exists('category_slugs', $_POST) && is_array($_POST['category_slugs'])) {
				$data['category_slugs'] = $_POST['category_slugs'];

				foreach ($data['category_slugs'] as $slug) {
					$term = get_term_by('slug', $slug, self::TAXONOMY);
					if ($term) {
						$data['availabilities'][$slug] = $this->isCategoryPrivate($term->term_id);
					}
				}
			}
			else {
				header("HTTP/1.0 400 Bad Request");
				die();
			}

			header('Content-Type: application/json');
			echo json_encode($data);
			die();
		}
	}

	function private_category_show_plugin_fail_message() {
		if ( current_user_can( 'activate_plugins' ) ) {
			$url = 'plugin-install.php?tab=plugin-information&plugin=the-events-calendar&TB_iframe=true';
			$title = __( 'The Events Calendar: Private Category', 'tribe-events-calendar-private-category' );
			echo '<div class="error"><p>'.sprintf( __( 'To begin using Events Calendar: Private Category, please install the latest version of <a href="%s" class="thickbox" title="%s">The Events Calendar</a>.', 'tribe-events-calendar-pro' ),$url, $title ).'</p></div>';
		}
	}

	function private_category_plugin_loaded() {
		if ( !class_exists( 'TribeEvents' ) ) {
			add_action( 'admin_notices', 'private_category_show_plugin_fail_message' );
		}
	}

	add_action( 'plugins_loaded', 'private_category_plugin_loaded', 1);


}