<?php

/*

Plugin Name: Selected Posts

Plugin URI: http://www.apinapress.com/products-services

Description: Allows you to select which posts to show in a widget or shortcode. Allows you to select what parts of the post is shown (title, featured image, etc.)

Version: 1.0.0

Author: Dean Robinson

Author URI: http://www.apinapress.com

License: GPL3



Plugin Template courtesry of Copyright 2011 Dave Shepard  (email : dave@kynatro.com)



This program is free software: you can redistribute it and/or modify

it under the terms of the GNU General Public License as published by

the Free Software Foundation, either version 3 of the License, or

(at your option) any later version.



This program is distributed in the hope that it will be useful,

but WITHOUT ANY WARRANTY; without even the implied warranty of

MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the

GNU General Public License for more details.



You should have received a copy of the GNU General Public License

along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/



// Include constants file

require_once( dirname( __FILE__ ) . '/lib/constants.php' );



class SelectedPosts {

    var $namespace = "selected-posts";

    var $friendly_name = "Selected Posts";

    var $version = "1.0.0";

    

    // Default plugin options

    var $defaults = array(

        'option_1' => "foobar"

    );

    

    /**

     * Instantiation construction

     * 

     * @uses add_action()

     * @uses SelectedPosts::wp_register_scripts()

     * @uses SelectedPosts::wp_register_styles()

     */

    function __construct() {

        // Name of the option_value to store plugin options in

        $this->option_name = '_' . $this->namespace . '--options';

		

        // Load all library files used by this plugin

        $libs = glob( SELECTEDPOSTS_DIRNAME . '/lib/*.php' );

        foreach( $libs as $lib ) {

            include_once( $lib );

        }

        

        /**

         * Make this plugin available for translation.

         * Translations can be added to the /languages/ directory.

         */

        load_theme_textdomain( $this->namespace, SELECTEDPOSTS_DIRNAME . '/languages' );



		// Add all action, filter and shortcode hooks

		$this->_add_hooks();

    }

    

    /**

     * Add in various hooks

     * 

     * Place all add_action, add_filter, add_shortcode hook-ins here

     */

    private function _add_hooks() {

        // Options page for configuration

        add_action( 'admin_menu', array( &$this, 'admin_menu' ) );

        // Route requests for form processing

        add_action( 'init', array( &$this, 'route' ) );

        

        // Add a settings link next to the "Deactivate" link on the plugin listing page

        add_filter( 'plugin_action_links', array( &$this, 'plugin_action_links' ), 10, 2 );

        

        // Register all JavaScripts for this plugin

        add_action( 'init', array( &$this, 'wp_register_scripts' ), 1 );

        // Register all Stylesheets for this plugin

        add_action( 'init', array( &$this, 'wp_register_styles' ), 1 );

    }

    

    /**

     * Process update page form submissions

     * 

     * @uses SelectedPosts::sanitize()

     * @uses wp_redirect()

     * @uses wp_verify_nonce()

     */

    private function _admin_options_update() {

        // Verify submission for processing using wp_nonce

        if( wp_verify_nonce( $_REQUEST['_wpnonce'], "{$this->namespace}-update-options" ) ) {

            $data = array();

            /**

             * Loop through each POSTed value and sanitize it to protect against malicious code. Please

             * note that rich text (or full HTML fields) should not be processed by this function and 

             * dealt with directly.

             */

            foreach( $_POST['data'] as $key => $val ) {

                $data[$key] = $this->_sanitize( $val );

            }

            

            /**

             * Place your options processing and storage code here

             */

            

            // Update the options value with the data submitted

            update_option( $this->option_name, $data );

            

            // Redirect back to the options page with the message flag to show the saved message

            wp_safe_redirect( $_REQUEST['_wp_http_referer'] . '&message=1' );

            exit;

        }

    }

    

    /**

     * Sanitize data

     * 

     * @param mixed $str The data to be sanitized

     * 

     * @uses wp_kses()

     * 

     * @return mixed The sanitized version of the data

     */

    private function _sanitize( $str ) {

        if ( !function_exists( 'wp_kses' ) ) {

            require_once( ABSPATH . 'wp-includes/kses.php' );

        }

        global $allowedposttags;

        global $allowedprotocols;

        

        if ( is_string( $str ) ) {

            $str = wp_kses( $str, $allowedposttags, $allowedprotocols );

        } elseif( is_array( $str ) ) {

            $arr = array();

            foreach( (array) $str as $key => $val ) {

                $arr[$key] = $this->_sanitize( $val );

            }

            $str = $arr;

        }

        

        return $str;

    }



    /**

     * Hook into register_activation_hook action

     * 

     * Put code here that needs to happen when your plugin is first activated (database

     * creation, permalink additions, etc.)

     */

    static function activate() {

        // Do activation actions

    }

	

    /**

     * Define the admin menu options for this plugin

     * 

     * @uses add_action()

     * @uses add_options_page()

     */

    function admin_menu() {

        //$page_hook = add_options_page( $this->friendly_name, $this->friendly_name, 'administrator', $this->namespace, array( &$this, 'admin_options_page' ) );

        

        // Add print scripts and styles action based off the option page hook

        add_action( 'admin_print_scripts-' . $page_hook, array( &$this, 'admin_print_scripts' ) );

        add_action( 'admin_print_styles-' . $page_hook, array( &$this, 'admin_print_styles' ) );

    }

    

    

    /**

     * The admin section options page rendering method

     * 

     * @uses current_user_can()

     * @uses wp_die()

     */

    function admin_options_page() {

        if( !current_user_can( 'manage_options' ) ) {

            wp_die( 'You do not have sufficient permissions to access this page' );

        }

        

        $page_title = $this->friendly_name . ' Options';

        $namespace = $this->namespace;

        

        include( SELECTEDPOSTS_DIRNAME . "/views/options.php" );

    }

    

    /**

     * Load JavaScript for the admin options page

     * 

     * @uses wp_enqueue_script()

     */

    function admin_print_scripts() {

        wp_enqueue_script( "{$this->namespace}-admin" );

    }

    

    /**

     * Load Stylesheet for the admin options page

     * 

     * @uses wp_enqueue_style()

     */

    function admin_print_styles() {

        wp_enqueue_style( "{$this->namespace}-admin" );

    }

    

    /**

     * Hook into register_deactivation_hook action

     * 

     * Put code here that needs to happen when your plugin is deactivated

     */

    static function deactivate() {

        // Do deactivation actions

    }

    

    /**

     * Retrieve the stored plugin option or the default if no user specified value is defined

     * 

     * @param string $option_name The name of the TrialAccount option you wish to retrieve

     * 

     * @uses get_option()

     * 

     * @return mixed Returns the option value or false(boolean) if the option is not found

     */

    function get_option( $option_name ) {

        // Load option values if they haven't been loaded already

        if( !isset( $this->options ) || empty( $this->options ) ) {

            $this->options = get_option( $this->option_name, $this->defaults );

        }

        

        if( isset( $this->options[$option_name] ) ) {

            return $this->options[$option_name];    // Return user's specified option value

        } elseif( isset( $this->defaults[$option_name] ) ) {

            return $this->defaults[$option_name];   // Return default option value

        }

        return false;

    }

    

    /**

     * Initialization function to hook into the WordPress init action

     * 

     * Instantiates the class on a global variable and sets the class, actions

     * etc. up for use.

     */

    static function instance() {

        global $SelectedPosts;

        

        // Only instantiate the Class if it hasn't been already

        if( !isset( $SelectedPosts ) ) $SelectedPosts = new SelectedPosts();

    }

	

	/**

	 * Hook into plugin_action_links filter

	 * 

	 * Adds a "Settings" link next to the "Deactivate" link in the plugin listing page

	 * when the plugin is active.

	 * 

	 * @param object $links An array of the links to show, this will be the modified variable

	 * @param string $file The name of the file being processed in the filter

	 */

	function plugin_action_links( $links, $file ) {

		if( $file == plugin_basename( SELECTEDPOSTS_DIRNAME . '/' . basename( __FILE__ ) ) ) {

            $old_links = $links;

            $new_links = array(

                "settings" => '<a href="options-general.php?page=' . $this->namespace . '">' . __( 'Settings' ) . '</a>'

            );

            $links = array_merge( $new_links, $old_links );

		}

		

		return $links;

	}

    

    /**

     * Route the user based off of environment conditions

     * 

     * This function will handling routing of form submissions to the appropriate

     * form processor.

     * 

     * @uses SelectedPosts::_admin_options_update()

     */

    function route() {

        $uri = $_SERVER['REQUEST_URI'];

        $protocol = isset( $_SERVER['HTTPS'] ) ? 'https' : 'http';

        $hostname = $_SERVER['HTTP_HOST'];

        $url = "{$protocol}://{$hostname}{$uri}";

        $is_post = (bool) ( strtoupper( $_SERVER['REQUEST_METHOD'] ) == "POST" );

        

        // Check if a nonce was passed in the request

        if( isset( $_REQUEST['_wpnonce'] ) ) {

            $nonce = $_REQUEST['_wpnonce'];

            

            // Handle POST requests

            if( $is_post ) {

                if( wp_verify_nonce( $nonce, "{$this->namespace}-update-options" ) ) {

                    $this->_admin_options_update();

                }

            } 

            // Handle GET requests

            else {

                

            }

        }

    }

    

    /**

     * Register scripts used by this plugin for enqueuing elsewhere

     * 

     * @uses wp_register_script()

     */

    function wp_register_scripts() {

        // Admin JavaScript

       // wp_register_script( "{$this->namespace}-admin", SELECTEDPOSTS_URLPATH . "/js/admin.js", array( 'jquery' ), $this->version, true );

    }

    

    /**

     * Register styles used by this plugin for enqueuing elsewhere

     * 

     * @uses wp_register_style()

     */

    function wp_register_styles() {

        // Admin Stylesheet

       // wp_register_style( "{$this->namespace}-admin", SELECTEDPOSTS_URLPATH . "/css/admin.css", array(), $this->version, 'screen' );

        wp_register_style( "{$this->namespace}-frontend", SELECTEDPOSTS_URLPATH . "/css/selectedposts.css", array(), $this->version, 'screen' );

    }

}

if( !isset( $SelectedPosts ) ) {

	SelectedPosts::instance();

}



register_activation_hook( __FILE__, array( 'SelectedPosts', 'activate' ) );

register_deactivation_hook( __FILE__, array( 'SelectedPosts', 'deactivate' ) );













//helper function from http://www.sean-barton.co.uk/2011/11/getting-the-wordpress-excerpt-outside-of-the-loop/#.UieCSzbI34E

// Why WP cant allow access to the excerpt outside the loop by id is well, annoying.

        function sp_get_the_excerpt($id=false, $sp_excerptlength) {

            global $post;



            $old_post = $post;

            if ($id != $post->ID) {

                $post = get_page($id);

            }



            if (!$excerpt = trim($post->post_excerpt)) {

                $excerpt = $post->post_content;

                $excerpt = strip_shortcodes( $excerpt );

                $excerpt = apply_filters('the_content', $excerpt);

                $excerpt = str_replace(']]>', ']]&gt;', $excerpt);

                $excerpt = strip_tags($excerpt);

                $excerpt_length = apply_filters('excerpt_length', $sp_excerptlength);

                $excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');



                $words = preg_split("/[\n\r\t ]+/", $excerpt, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);

                if ( count($words) > $excerpt_length ) {

                    array_pop($words);

                    $excerpt = implode(' ', $words);

                    $excerpt = $excerpt . $excerpt_more;

                } else {

                    $excerpt = implode(' ', $words);

                }

            }



            $post = $old_post;



            return $excerpt;

        }





function sp_frontend_scripts() {

	wp_enqueue_style( 'selected-posts-frontend', get_stylesheet_uri() );

}



add_action( 'wp_enqueue_scripts', 'sp_frontend_scripts' );





/**

 * Authors Widget Class

 */

class sp_selected_posts extends WP_Widget {



    /** constructor */

    function sp_selected_posts() {

        parent::WP_Widget(false, $name = 'Selected Posts');

    }



    /** @see WP_Widget::widget */

    function widget($args, $instance) {

        extract( $args );

		global $wpdb;



        $title = apply_filters('widget_title', $instance['title']);

		$sp_featured = $instance['featured'];

		$sp_excerpt = $instance['excerpt'];

		$sp_excerptlength = $instance['excerptlength'];

		$sp_imgw = $instance['imgw'];

		$sp_posts = $instance['posts'];

		$sp_posttype = $instance['posttype'];

		

		if(!$size)

			$size = 40;



        ?>

              <?php echo $before_widget; ?>

                  <?php if ( $title )

                        echo $before_title . $title . $after_title; ?>

							<ul>

							<?php





$sp_posts = str_replace(' ', '', $sp_posts);

$sp_posts = explode(',', $sp_posts);



//if(!$sp_posttype) { $sp_posttype = 'post'; }



$args = array(

	'posts_per_page'   => -1,

	'orderby'          => 'post_date',

	'post_type'        => $sp_posttype,

	'post_status'      => 'publish',

	'suppress_filters' => true,

	'post__in'			=> $sp_posts,

	'ignore_sticky_posts' => 'true'

	);



$sp_post_data = get_posts($args);

//var_dump($sp_post_data);



								foreach($sp_post_data as $spposts) {



									$sp_post_title = $spposts->post_title;

									$sp_post_url =  $spposts->guid;

									$sp_post_thumb = wp_get_attachment_url(get_post_thumbnail_id($spposts->ID, 'thumbnail'));

									//echo $sp_post_thumb;



									echo '<li class="sp_list">';



										echo '<div class="sp_div">';



										if($sp_featured) { echo '<img class="sp_img" src="' . $sp_post_thumb . '" width="' . $sp_imgw . '" alt="' . $sp_post_title . '">'; }



										echo '</div>';



										echo '<a class="sp_title" href="' . $sp_post_url .'">';

											echo $sp_post_title;

										echo '</a> <br />';

										

										if($sp_excerpt) {

											$theexcerpty = sp_get_the_excerpt($spposts, $sp_excerptlength);

											echo '<p class="sp_excerpt">' . $theexcerpty . '</p>';

										}



									echo '</li>';

								}

							?>

							</ul>

              <?php echo $after_widget; ?>

        <?php

    }



    /** @see WP_Widget::update */

    function update($new_instance, $old_instance) {

		$instance = $old_instance;

		$instance['title'] = strip_tags($new_instance['title']);

		$instance['featured'] = strip_tags($new_instance['featured']);

		$instance['excerpt'] = strip_tags($new_instance['excerpt']);

		$instance['posts'] = strip_tags($new_instance['posts']);

		$instance['excerptlength'] = strip_tags($new_instance['excerptlength']);

		$instance['imgw'] = strip_tags($new_instance['imgw']);

		$instance['posttype'] = strip_tags($new_instance['posttype']);

        return $instance;

    }



    /** @see WP_Widget::form */

    function form($instance) {	



        $title = esc_attr($instance['title']);

		$sp_featured = esc_attr($instance['featured']);

		$sp_excerpt = esc_attr($instance['excerpt']);

		$sp_posts = esc_attr($instance['posts']);

		$sp_excerptlength = esc_attr($instance['excerptlength']);

		$sp_imgw = esc_attr($instance['imgw']);

		$sp_posttype = esc_attr($instance['posttype']);



        ?>

         <p>

          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>

          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />

        </p>



		<p>

          <input id="<?php echo $this->get_field_id('featured'); ?>" name="<?php echo $this->get_field_name('featured'); ?>" type="checkbox" value="1" <?php checked( '1', $sp_featured ); ?>/>

          <label for="<?php echo $this->get_field_id('featured'); ?>"><?php _e('Display Featured Image?'); ?></label>

        </p>

        

		<p>

          <label for="<?php echo $this->get_field_id('imgw'); ?>"><?php _e('Image Width in pixels:'); ?></label>

          <input class="widefat" id="<?php echo $this->get_field_id('imgw'); ?>" name="<?php echo $this->get_field_name('imgw'); ?>" type="text" value="<?php echo $sp_imgw; ?>" />

        </p>





		<p>

          <input id="<?php echo $this->get_field_id('excerpt'); ?>" name="<?php echo $this->get_field_name('excerpt'); ?>" type="checkbox" value="1" <?php checked( '1', $sp_excerpt ); ?>/>

          <label for="<?php echo $this->get_field_id('excerpt'); ?>"><?php _e('Display Excerpt?'); ?></label>

        </p>

        

		<p>

          <label for="<?php echo $this->get_field_id('excerptlength'); ?>"><?php _e('Excerpt length (in words):'); ?></label>

          <input class="small" id="<?php echo $this->get_field_id('excerptlength'); ?>" name="<?php echo $this->get_field_name('excerptlength'); ?>" type="text" value="<?php echo $sp_excerptlength; ?>" />

        </p>



        

        <?php

		global $wpdb;

		

				$args = array(

				'posts_per_page'   => -1,

				'post_type'        => 'post',

				'post_mime_type'   => '',

				'post_parent'      => '',

				'post_status'      => 'publish',

				'suppress_filters' => true 

				); 

				$sp_get_posts = get_posts($args);

				//var_dump($sp_get_posts);

				//var_dump($instance);

		?>

		<p>

          <label for="<?php echo $this->get_field_id('posts'); ?>"><?php _e('Enter Post IDs (commma separated):'); ?></label>

          <input class="widefat" id="<?php echo $this->get_field_id('posts'); ?>" name="<?php echo $this->get_field_name('posts'); ?>" type="text" value="<?php echo $sp_posts; ?>" />

        </p>



		<p>

          <label for="<?php echo $this->get_field_id('posttype'); ?>"><?php _e('Post Type'); ?></label>

          <input class="small" id="<?php echo $this->get_field_id('posttype'); ?>" name="<?php echo $this->get_field_name('posttype'); ?>" type="text" value="<?php echo $sp_posttype; ?>" />

        </p>





        <?php

    }



} 



add_action('widgets_init', create_function('', 'return register_widget("sp_selected_posts");'));