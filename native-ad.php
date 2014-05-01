<?php
/**
 * Plugin Name: Native Ad
 * Plugin URI: http://endocreative.com
 * Description: Add native ad functionality to your site by marking posts as sponsored
 * Version: 1.01
 * Author: Endo Creative
 * Author URI: http://endocreative.com
 * License: GPL2
 */

$plugin = plugin_basename(__FILE__); 

// Add settings link on plugin page
function na_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=native-ad/native-ad.php">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
add_filter( "plugin_action_links_$plugin" , 'na_settings_link' );

add_action( 'admin_enqueue_scripts', 'na_enqueue_color_picker' );
function na_enqueue_color_picker( ) {
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'na-script', plugins_url('script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
}

function na_create_options_page() {
 
    add_options_page(
        'Native Ad',           
        'Native Ad',          
        'manage_options',          
        __FILE__,   
        'na_settings_page'   
    );
 
} 
add_action('admin_menu', 'na_create_options_page');

function na_settings_page() {
 	?>
    <div class="wrap">

        <h2>Native Ad Options</h2>

      	<form method="post" action="options.php">
            <?php settings_fields( 'na_options' ); ?>
            <?php do_settings_sections( 'na_opt' ); ?>          
            <?php submit_button(); ?>
        </form>

    </div>

    <?php 
} 


add_action('admin_init', 'na_admin_init');
function na_admin_init() {
 
	register_setting(
        'na_options',
        'na_options',
        'na_validate_options'
    );
     

    add_settings_section(
        'na_general',        
        'General Options',                 
        '',
        'na_opt'                           
    );

    add_settings_field(
        'na_text',                  
        'Sponsored Text',                         
        'na_text_input',  
        'na_opt',                         
        'na_general'       
    );

     add_settings_field(
        'na_text_color',                  
        'Sponsored Text Color',                         
        'na_text_color_input',  
        'na_opt',                         
        'na_general'       
    );

    add_settings_field(
        'na_color',                  
        'Sponsored Background Color',                         
        'na_color_input',  
        'na_opt',                         
        'na_general'       
    );

     add_settings_field(
        'na_content_color',                  
        'Post Background Color',                         
        'na_content_color_input',  
        'na_opt',                         
        'na_general'       
    );
} 

function na_text_input() {

	$options = wp_parse_args( get_option( 'na_options' ), array('na_text' => 'Sponsored'));
	$text = $options['na_text'];
	echo "<input id='na_text' name='na_options[na_text]' value='$text'>";
}

function na_text_color_input() {

    $options = wp_parse_args( get_option( 'na_options' ), array('na_text_color' => '#ffffff'));
    $text_color = $options['na_text_color'];
    echo "<input class='color-picker'  id='na_text_color' name='na_options[na_text_color]' value='$text_color'>";
}

function na_color_input() {

    $options = wp_parse_args( get_option( 'na_options' ), array('na_color' => '#000000'));
    $color = $options['na_color'];
    echo "<input class='color-picker' id='na_color' name='na_options[na_color]' value='$color'>";
}

function na_content_color_input() {

    $options = wp_parse_args( get_option( 'na_options' ), array('na_content_color' => '#ffffff'));
    $content_color = $options['na_content_color'];
    echo "<input class='color-picker' id='na_content_color' name='na_options[na_content_color]' value='$content_color'>";

}

function na_validate_options( $input ) {
    return $input;
}


// add meta box to post screen
add_action( 'add_meta_boxes', 'na_meta_box_create' );
function na_meta_box_create() {

	add_meta_box( 'na-options', 'Native Ad Options', 'na_options_function', 'post', 'side', 'high' );

    add_meta_box( 'na-options', 'Native Ad Options', 'na_options_function', 'article', 'side', 'high' );

}

function na_options_function( $post ) {

	$is_ad = get_post_meta( $post->ID, '_na_ad', true );

	?>

	<table class="form-table">
		<tr valign="top">
			<td>
				<input id="na_ad" class="widefat" type="checkbox" name="na_ad" <?php checked( 'on', $is_ad ); ?> /> <label for="na_ad">This is a native ad</label>
			</td>
		</tr>
	</table>
	<?php 
}

add_action( 'save_post', 'na_options_save_meta' );
function na_options_save_meta( $post_id ) {

	if ( !empty( $_POST['na_ad'] ) ) {
		update_post_meta( $post_id, '_na_ad', $_POST['na_ad'] );
	} else {
		update_post_meta( $post_id, '_na_ad', 'off' );
	}

}


// add the visual indicator to the post title
add_filter( 'the_title', 'na_ad_post_title');
function na_ad_post_title ( $title ) {

    if ( !is_admin() ) { // so the markup won't display in the admin

        global $post;
        $is_ad = get_post_meta( $post->ID, '_na_ad', true );

        $options = get_option( 'na_options' );
        $text = $options['na_text'];

          // this will only show the indicator within the loop, off for now
          // if( $title == $post->post_title and !is_page() && $is_ad == 'on' && in_the_loop() ){

          //   $title = '<span class="native-ad-title">Sponsored</span> ' . $title;

          // }

            if ( !is_page() && $is_ad == 'on' && $title == $post->post_title ) {

                $title = '<span class="native-ad-indicator">' . $text . '</span> ' . $title;
            }
    }
	


      
  return $title;
}

// add a container to the content for styling purposes
add_filter( 'the_content', 'na_ad_post_content');
function na_ad_post_content ( $content ) {

	global $post;

	$is_ad = get_post_meta( $post->ID, '_na_ad', true );

    if ( !is_page() && $is_ad == 'on' && in_the_loop() ) {

        $content = '<div class="native-ad-content"> ' . $content. '</div>';

    }
  return $content;
}

// a class to the body element for styling purposes
add_filter( 'body_class', 'na_ad_body_classes');
function na_ad_body_classes ( $classes ) {

	global $post;

	$is_ad = get_post_meta( $post->ID, '_na_ad', true );

    if( !is_page() && $is_ad == 'on' ){

	   $classes[] = 'is-native-ad';

	}
  return $classes;
}

// add styles for the plugin to the head of the document
add_action( 'wp_head', 'na_ad_styles' );
function na_ad_styles() {

	$options = get_option( 'na_options' );
	$color = $options['na_color'];
    $text_color = $options['na_text_color'];
    $content_color = $options['na_content_color'];

	?>
	<style type="text/css" media="screen">
		.native-ad-indicator { color: <?php echo $text_color; ?>; font-size: .8em; background: <?php echo $color; ?>; border-radius: 2px; padding: 0 2px; display: inline-block; z-index: 99;}
        .native-ad-content {
            background: <?php echo $content_color; ?>; padding: 10px;
        }
	</style>
	<?php 
}

// add user role for Sponsor, who can edit posts and articles, but not delete them
register_activation_hook( __FILE__, 'na_sponsor_role_create' );

function na_sponsor_role_create() {

    add_role(
        'na_sponsor',
        __( 'Sponsor' ),
        array(
            'read'          => true,
            'edit_articles'    => true,
            'edit_others_articles' => true,
            'edit_published_articles' => true,
            'publish_articles' => true,
            'edit_others_posts' => true,
            'edit_posts'    => true,
            'edit_published_posts' => true,
            'publish_posts' => true,
            'edit_others_posts' => true,
            'moderate_comments' => false
        )
    );
}