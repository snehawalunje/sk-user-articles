<?php
/**
 * Plugin Name: User Articles
 * Plugin URI: 
 * Description: This plugin Fetch the articles and user from API.
 * Version: 1.0.0
 * Author: Sneha Walunje
 * Text Domain: user-articles
 */

/**
 * check the wordpress function is exist, just to make sure that file is requested by wordpress,
 * else stop further execution
 *
 */
if ( ! function_exists( 'add_action' ) ) {
    echo 'Uh huh! Plugin can not do much when called directly.';
    exit;
}

/**
 * Define contants for the plugin,which we can use in any plugin file.
 * This is just a static value, which is fixed and not going to change
 * plugin_dir_url will save plugin derectory url which will used for enqueing js (JavaScript) files
 * plugin_dir_path will save plugin derectory path and will be used to include php files
 */
define( 'USER_ARTICLES_VERSION', '1.0.0' );
define( 'USER_ARTICLES_PLUGIN_URL', plugin_dir_url(__FILE__) );
define( 'USER_ARTICLES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * check if class is exist with the same name by other plugin or theme, to avoid fatal error
 */
if ( ! class_exists( 'USER_articles_Init' ) ) {
    /**
     *
     * Initiate Plugin Class
     * define class with the plugin name init which will handle all required stuff which plugin needs
     * which includes actions, files or adding shortcodes
     *
     * @since 1.0.0
     */

    class USER_articles_Init {
       
        public function __construct() {
            $this->call_actions();
        }

        /**
         * declare and call hooks ( actions and filters )
         *
         * @return void
         */
        public function call_actions() {
            // declare actions here
            add_action( 'init', array( $this, 'user_articles_custom_post_type'));
            add_action( 'wp_enqueue_scripts', array( $this, 'user_articles_style_scripts') );
            add_action( 'admin_enqueue_scripts', array( $this, 'user_articles_style_scripts') );
            add_action( 'admin_menu',array( $this, 'user_articles_register_submenu_page') );
            add_action( 'wp_ajax_get_data_ajax_submission', array( $this,'renderUserData_Ajax'));
            add_action( 'wp_ajax_get_post_ajax_submission', array( $this,'renderpostData_Ajax'));
            add_action( 'show_user_profile',  array( $this,'additional_user_fields') );
            add_action( 'edit_user_profile',  array( $this,'additional_user_fields') );
        }

     
        public function user_articles_custom_post_type() {
          
            // Set UI labels for Custom Post Type
            $labels = array(
                'name'                => _x( 'Articles', 'Post Type General Name', 'user-articles' ),
                'singular_name'       => _x( 'Articles', 'Post Type Singular Name', 'user-articles' ),
                'menu_name'           => __( 'Articles', 'user-articles' ),
                'parent_item_colon'   => __( 'Parent Articles', 'user-articles' ),
                'all_items'           => __( 'All Articles', 'user-articles' ),
                'view_item'           => __( 'View Articles', 'user-articles' ),
                'add_new_item'        => __( 'Add New Articles', 'user-articles' ),
                'add_new'             => __( 'Add New', 'user-articles' ),
                'edit_item'           => __( 'Edit Articles', 'user-articles' ),
                'update_item'         => __( 'Update Articles', 'user-articles' ),
                'search_items'        => __( 'Search Articles', 'user-articles' ),
                'not_found'           => __( 'Not Found', 'user-articles' ),
                'not_found_in_trash'  => __( 'Not found in Trash', 'user-articles' ),
            );

            // Set other options for Custom Post Type
            $args = array(
                'label'               => __( 'Articles', 'user-articles' ),
                'description'         => __( 'Articles', 'user-articles' ),
                'labels'              => $labels,
                // Features this CPT supports in Post Editor
                'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields'),
                'hierarchical'        => false,
                'public'              => true,
                'show_ui'             => true,
                'rewrite'             => array( 'slug' => 'user-articles' ),
                'show_in_menu'        => true,
                'show_in_nav_menus'   => true,
                'show_in_admin_bar'   => true,
                'menu_icon'           => 'dashicons-welcome-write-blog',
                'menu_position'       => 5,
                'can_export'          => true,
                'has_archive'         => false,
                'exclude_from_search' => true,
                'publicly_queryable'  => true,
                'query_var'           => true,
                'capability_type'     => 'post',
            );
            // Registering your Custom Post Type
            register_post_type( 'user-articles', $args );
        }


        /**
         *
         * admin menu callback function
         *
         */
        public function user_articles_register_submenu_page() {
            //get user and articles Sub Menu   
            add_submenu_page('edit.php?post_type=user-articles', 'Get User & Articles', 'Get User & Articles', "manage_options", 'get_usernarticles', array($this, 'getuser_articles'), '');
        }

        /**
         *
         * add submenu page callback function
         *
         */
        public function getuser_articles(){
            echo '<h2>Get User and Post</h2>
            <button type="button" data-id="get-user" class="get_usr_list">Fetch User</button>
            <button type="button" data-id="get-post" class="get_post_list">Fetch Post</button>
            </br>
            <div id="success_message" class="ajax_response"></div>';
            return;
        }
    
        /**
         * add style and scripts which are required for the plugin
         * place this files in assets folder on respective css/js folders
         */
        public function user_articles_style_scripts() {
            wp_enqueue_script( 'user_articles-plugin-script', USER_ARTICLES_PLUGIN_URL . 'assets/js/user-articles.js' , array( 'jquery') );
            wp_localize_script( 'user_articles-plugin-script', 'getdatauserajaxsubmission', array( 'ajax_url' => admin_url('admin-ajax.php')) );      
            wp_enqueue_style( 'user_articles-plugin-style', USER_ARTICLES_PLUGIN_URL . 'assets/css/user-articles.css' );
        }

        /**
         *
         * ajax callback function to get and create the user
         *
         */
        public function renderUserData_Ajax() {
            $get_user_id = $_POST['data'];
            if($get_user_id=='get-user'){
                $response = wp_remote_get('https://jsonplaceholder.typicode.com/users');
                try {
                    $user_data = json_decode( $response['body'] );
                    $user_data_arr = array_slice($user_data, 0, 5);
                    $user_ids = array();
                    foreach($user_data_arr as $user_info){
                        $website = $user_info->website;
                        $uname = explode(' ',$user_info->name);
                        $user_id = username_exists( $user_info->username );
                        if ( ! $user_id && false == email_exists( $user_info->email ) ) {
                            $userdata = array(
                                'user_login' =>  $user_info->username,
                                'user_url'   =>  $website,
                                'user_pass'  =>  wp_generate_password( $length = 12, $include_standard_special_chars = false ),
                                'display_name'=> $user_info->name,
                                'first_name'  => $uname[0],
                                'last_name'   => $uname[1],
                                'user_email'  => $user_info->email, 
                                'user_nicename'=>$user_info->username,
                                'role'        => 'author', 
                            );
                            $user_id = wp_insert_user( $userdata );
                            // On success.
                            if ( ! is_wp_error( $user_id ) ) {
                                $user_ids[] = $user_id;
                                $html = 'User created Successfully';
                            }
                        } else {
                            $exist = "User already exist";
                        }
                        if($user_id){
                            update_user_meta( $user_id, 'user_articles_street', $user_info->address->street );
                            update_user_meta( $user_id, 'user_articles_suite', $user_info->address->suite );
                            update_user_meta( $user_id, 'user_articles_city', $user_info->address->city );
                            update_user_meta( $user_id, 'user_articles_pincode', $user_info->address->zipcode );
                            update_user_meta( $user_id, 'user_articles_phone', $user_info->phone );
                            update_user_meta( $user_id, 'user_articles_company', $user_info->company->name );
                        }
                    }
                    if(!empty($user_ids)){
                        update_option( 'user_author_ids', $user_ids );
                    }
                } catch ( Exception $ex ) {
                    $user_data = null;
                }
            }
            $return = array(
            'html'  => $html,
            'exist' => $exist,
            );
            wp_send_json($return);

        }

        /**
         *
         * ajax callback function to get and create the post
         *
         */
        public function renderpostData_Ajax() {
            $get_post_id = $_POST['data'];
            $user_id_arr = get_option( 'user_author_ids' );//get user id
            if($get_post_id=='get-post'){
                $response = wp_remote_get('https://jsonplaceholder.typicode.com/posts');
                try {
                    $post_data = json_decode( $response['body'] );
                    $post_data_arr = array_slice($post_data, 0, 5);
                    $i=0;
                    foreach($post_data_arr as $post_info){
                        $post_title = $post_info->title;
                        $post_body = $post_info->body;
                        if ( post_exists( $post_title ) == 0 ) {
                            $insert_post = array(
                              'post_title'    => $post_title,
                              'post_content'  => $post_body,
                              'post_status'   => 'publish',
                              'post_type'     => 'user-articles',
                              'post_author'   => $user_id_arr[$i]
                            );
                            // Insert the post into the database
                            $post_id = wp_insert_post( $insert_post );
                            if ( ! is_wp_error( $post_id ) ) {
                                $html = 'Post created Successfully';
                            }
                        }else{
                             $exist = "Post already exist";
                        }
                        $i++;
                    }
                } catch ( Exception $ex ) {
                    $post_data = null;
                }

            }
            $return = array(
            'html'  => $html,
            'exist' => $exist,
            );
            wp_send_json($return);
        }

        /**
         *
         * Create additional user profile fields
         *
         */
        public function additional_user_fields( $user ) {

            $user_street = esc_attr( get_the_author_meta( 'user_articles_street', $user->ID ) );
            $user_suit = esc_attr( get_the_author_meta( 'user_articles_suite', $user->ID ) );
            $user_city = esc_attr( get_the_author_meta( 'user_articles_city', $user->ID ) );
            $user_pincode = esc_attr( get_the_author_meta( 'user_articles_pincode', $user->ID ) );
            $user_ph = esc_attr( get_the_author_meta( 'user_articles_phone', $user->ID ) );
            $user_company = esc_attr( get_the_author_meta( 'user_articles_company', $user->ID ) );

         ?>

            <h3><?php _e("Additional profile information", "blank"); ?></h3>
            <div style="padding:15px;" >
                <div style="width: 15%;display: inline-block;">
                <label style="font-weight: bold;" for="user_articles_street"><?php  _e('Street','user-articles'); ?></label>
                </div>
                <div style="display: inline-block;"><input style="width: 300px;height:35px" type="text" name="user_articles_street" value="<?php echo $user_street; ?>" id="user_street" /></div>
            </div>
            <div style="padding:15px;" >
                <div style="width: 15%;display: inline-block;">
                <label style="font-weight: bold;" for="user_articles_suite"><?php  _e('Suite','user-articles'); ?></label>
                </div>
                <div style="display: inline-block;"><input style="width: 300px;height:35px" type="text" name="user_articles_suite" value="<?php echo $user_suit; ?>" id="user_suite" /></div>
            </div>
            <div style="padding:15px;" >
                <div style="width: 15%;display: inline-block;">
                <label style="font-weight: bold;" for="user_articles_city"><?php  _e('City','user-articles'); ?></label>
                </div>
                <div style="display: inline-block;"><input style="width: 300px;height:35px" type="text" name="user_articles_city" value="<?php echo $user_city; ?>" id="user_city" /></div>
            </div>
            <div style="padding:15px;" >
                <div style="width: 15%;display: inline-block;">
                <label style="font-weight: bold;" for="user_articles_pincode"><?php  _e('Pincode','user-articles'); ?></label>
                </div>
                <div style="display: inline-block;"><input style="width: 300px;height:35px" type="text" name="user_articles_pincode" value="<?php echo $user_pincode; ?>" id="user_pincode" /></div>
            </div>
            <div style="padding:15px;" >
                <div style="width: 15%;display: inline-block;">
                <label style="font-weight: bold;" for="user_articles_phone"><?php  _e('Phone','user-articles'); ?></label>
                </div>
                <div style="display: inline-block;"><input style="width: 300px;height:35px" type="text" name="user_articles_phone" value="<?php echo $user_ph; ?>" id="user_phone" /></div>
            </div>
            <div style="padding:15px;" >
                <div style="width: 15%;display: inline-block;">
                <label style="font-weight: bold;" for="user_articles_company"><?php  _e('Company Name','user-articles'); ?></label>
                </div>
                <div style="display: inline-block;"><input style="width: 300px;height:35px" type="text" name="user_articles_company" value="<?php echo $user_company; ?>" id="user_company" /></div>
            </div>

        <?php } 

    }// End class
    new USER_articles_Init();
}// End if().

?>