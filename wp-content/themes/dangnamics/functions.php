<?php
/****
 * Custom Login Screen
 ****/

// Custom error message 
add_filter('login_errors','login_error_msg');
function login_error_msg($error){
	global $errors;
	$error_codes = $errors->get_error_codes();

	//Change for username error
	if(in_array('invalid_username', $error_codes) || in_array('empty_username', $error_codes) || in_array('invalid_email', $error_codes)  ){
		$error .= '<div id="error-username">* This field is required</div>';
	}
	//Change for password error
	if(in_array('incorrect_password', $error_codes) || in_array('empty_password', $error_codes)){
		$error .= '<div class="error-pw">* This field is required</div>';
	}
	//Change for email error
	if(in_array('invalid_email', $error_codes) || in_array('empty_email', $error_codes)){
		$error .= '<div class="error-email">* This field is required</div>';
	}
	return $error;
}

//override the registration success message
add_filter( 'wp_login_errors', 'override_reg_complete_msg', 10, 2 );
function override_reg_complete_msg( $errors, $redirect_to ) {
	if( isset( $errors->errors['registered'] ) ) {
		
	$needle = __('Registration complete. Please check your email');
		foreach( $errors->errors['registered'] as $index => $msg ) {
			if(str_contains($msg, $needle))  {
			$errors->errors['registered'][$index] = '<h2>Registration Complete!</h2><p>Registration Complete. Please check your email. </p><p> <a href="./wp-login.php">Login Here</a> </p>';
			}
		}
	}
	return $errors;
}

//Custom containers on login screen for slider and static image using ACF
add_filter('login_message', 'custom_login_container');

function custom_login_container(){ 
	echo "<div class=loginSliderContainer>";
	//Slider for only registration
	$enableSlider = get_field('enable_slider', 'option');
	if ($enableSlider){
		 echo '<div class="slider">
	<ul class=items>'; 
if( have_rows('slider_image_and_caption', 'option') ):
	while( have_rows('slider_image_and_caption', 'option') ) : the_row();
		echo '<li class="item'; 
		 if(get_row_index() == '1' ){ 
			echo ' current';
		 } 
		echo '">
				<div class=rightImageContainer style="background:url('. get_sub_field('slider_background_image') . ')">
					<div class="foregroundBg">
						<img src="'. get_sub_field('slider_image') .'">
					</div>
					<div class="captionContainer" style="background:'. get_sub_field('caption_background_color') .'">
						<div class="captionText"><h2>'. get_sub_field('slider_caption') .'</h2></div>
						<div class="captionSubText"><p>'. get_sub_field('slider_sub_caption') .'</p></div>
					</div>
				</div>
			</li>';
	endwhile;
	reset_rows();
endif;
	echo '</ul><div class=dots>';
	while( have_rows('slider_image_and_caption', 'option') ) : the_row();
	echo '<button type=button class="dot';
		 if(get_row_index() == '1' ){ 
			echo ' current';
		 }
	echo '"></button>';
	endwhile;
	echo '	
	</div>
	</div>';
	}else{
		if( have_rows('hero_image', 'option') ):
			while( have_rows('hero_image', 'option') ) : the_row();
				$heroImage = get_sub_field('image');
				$heroImageBg = get_sub_field('image_background');
				$heroCaption = get_sub_field('image_caption');
				$heroSubCaption = get_sub_field('image_sub_caption');
				$heroCaptionBgColor = get_sub_field('caption_background_color');
			endwhile;
		endif;
		echo '<div class="non-slider">
		<div class=rightImageContainer style="background:url(' . $heroImageBg . ')">
		
		
				<div class="foregroundBg">
					<img src="'. $heroImage  .'">
				</div>
				<div class="captionContainer" style="background:'. $heroCaptionBgColor .'">
					<div class="captionText"><h2>' . $heroCaption . ' </h2></div>
					<div class="captionSubText"><p>' . $heroSubCaption . '</p></div>
				</div>
		</div>	
	</div>';
	}
	echo "</div>
	<div class='formLoginContainer'><div class='formContainer'>"; //open div tag to wrap the form and other elements in a container
}

function custom_footer_login(){
	echo "</div></div>"; // closing div for formLoginContainer & formContainer;
}
add_action('login_footer.', 'custom_footer_login');


/**
 * Customized the login form using login_head.
 */
function default_login_page_head() {
	
	add_filter( 'gettext', 'change_login_form_register_keyword');
}
add_action( 'login_head', 'default_login_page_head' );

//Change Register text from the bottom of login form.

function change_login_form_register_keyword( $text ) {
	if (isset($_GET["action"]) && $_GET["action"] === 'register') {
	$text = str_replace( 'Register',  'Sign-Up',  $text ); //for the button text change
	}else{
	$text = str_replace( 'Register',  'Create One',  $text );
	}
    return $text;
}
//this will translate all instance, need to condition if  string text is used elsewhere
function login_text( $translated ) {
    $translated = str_ireplace('Log In',  'Login Here',  $translated);
    return $translated;
}
add_filter(  'gettext',  'login_text'  );

//modify create account text and link
add_filter('register', function ($reg_link) {
    $result = str_replace('<a',"Don't have an account? <a class='create-account-link'", $reg_link);
   return $result;
});

//removed the lost password link and disable instead of just hiding
class Password_Reset_Removed
{
 
  function __construct()
  {
    add_filter( 'show_password_fields', array( $this, 'disable' ) );
    add_filter( 'allow_password_reset', array( $this, 'disable' ) );
    add_filter( 'gettext',              array( $this, 'remove' ) );
  }
 
  function disable()
  {
    if ( is_admin() ) {
      $userdata = wp_get_current_user();
      $user = new WP_User($userdata->ID);
      if ( !empty( $user->roles ) && is_array( $user->roles ) && $user->roles[0] == 'administrator' )
        return true;
    }
    return false;
  }
 
  function remove($text)
  {
    return str_replace( array('Lost your password?', 'Lost your password'), '', trim($text, '?') );
  }
}
$pass_reset_removed = new Password_Reset_Removed();

// remove the error shake on wplogin
function wps_login_error() {
    remove_action('login_footer', 'wp_shake_js', 12);
}
add_action('login_footer', 'wps_login_error');

// Custom login form header
function login_header_text(){

	if (isset($_GET["action"]) && $_GET["action"] === 'register') {
	echo "<h3 class='formHeader'>Create Account</h3>";
	} elseif (isset($_GET["checkemail"]) && $_GET["checkemail"] === 'registered'){
		echo "<h3 class='formHeader'>Create Account</h3>";
	}else{
		echo "<h3 class='formHeader'>Login</h3>";
	}
}
add_filter( 'login_message', 'login_header_text' );

//custom stylesheet and js for custom login
function custom_login_style(){
	$the_theme = wp_get_theme();
	wp_enqueue_style('custom_login', get_stylesheet_directory_uri() . '/css/login.css', array(), $the_theme->get('Version'));
	wp_enqueue_script('custom_login', get_stylesheet_directory_uri() . '/assets/js/login.js',array('jquery'), $the_theme->get('Version') );
	//google font
	wp_enqueue_style( 'int-googlefonts', 'https://fonts.googleapis.com/css2?family=Freckle+Face&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap', array(), null );
}
add_action('login_enqueue_scripts','custom_login_style');

/*******
 * EOF Custom Login Screen
 *******/


 //ACF Admin options
 if( function_exists('acf_add_options_page') ) {

    acf_add_options_page(array(
        'page_title'    => 'Login & Registration Settings',
        'menu_title'    => 'Login & Registration Settings',
        'menu_slug'     => 'login-registration-settings',
        'capability'    => 'edit_posts',
        'redirect'      => false
    ));


}

?>
 