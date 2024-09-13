<?php
/**
 * Template Name: Custom Registration Page
 */
extract(etheme_get_page_sidebar());
//Check whether the user is already logged in
if (!$user_ID) {
        extract(etheme_get_page_sidebar());
        get_header();
        ?>

            <?php et_page_heading() ?>
            
            <div class="container et-registration">
                <div class="page-content sidebar-position-<?php echo esc_attr($position); ?> responsive-sidebar-<?php echo esc_attr($responsive); ?>">
                    <div class="row-fluid">
                        <?php if($position == 'left' || ($responsive == 'top' && $position == 'right')): ?>
                            <div class="<?php echo esc_attr($sidebar_span); ?> sidebar sidebar-left">
                                <?php etheme_get_sidebar($sidebarname); ?>
                            </div>
                        <?php endif; ?>

                        <div class="content <?php echo esc_attr($content_span); ?>">
                               <?php
                                if(get_option('users_can_register')) {
                                    ?>
                                    <div class="row-fluid">
                                        
                                        <div class="span6">
                                            <div class="content-box">
                                                <h3 class="title"><span><?php esc_html_e('Create Account', 'legenda'); ?></span></h3>
                                                <div id="result"></div> 

                                                <form id="wp_signup_form" action="" method="post" class="register">
                                                    <div class="login-fields">
                                                        <p class="form-row form-row">
                                                            <label class=""><?php esc_html_e( "Enter your username", 'legenda' ) ?> <span class="required">*</span></label>
                                                            <input id="username" type="text" name="username" class="text input-text" value="" />
                                                            <span class="hidden form-error empty"><?php esc_html_e( 'User name should not be empty.', 'legenda' ) ?></span>
                                                            <span class="hidden form-error exists"><?php esc_html_e( 'Username already exists. Please try another one.', 'legenda' ) ?></span>
                                                        </p>
                                                        <p class="form-row form-row">
                                                            <label class=""><?php esc_html_e( "Enter your E-mail address", 'legenda' ) ?> <span class="required">*</span></label>
                                                            <input id="email" type="text" name="email" class="text input-text" value="" />
                                                            <span class="hidden form-error empty"><?php esc_html_e( 'Email should not be empty.', 'legenda' ) ?></span>
                                                            <span class="hidden form-error invalid"><?php esc_html_e( 'Please enter a valid email.', 'legenda' ) ?></span>
                                                            <span class="hidden form-error exists"><?php esc_html_e( 'Email already exists. Please try another one..', 'legenda' ) ?></span>
                                                        </p>
                                                        <p class="form-row form-row">
                                                            <label class=""><?php esc_html_e( "Enter your password", 'legenda' ) ?> <span class="required">*</span></label>
                                                            <input id="et_pass" type="password" name="et_pass" class="text input-text" value="" />
                                                            <span class="hidden form-error empty"><?php esc_html_e( 'Password should not be empty.', 'legenda' ) ?></span>
                                                            <span class="hidden form-error length"><?php esc_html_e( 'Password should have more than 10 symbols.', 'legenda' ) ?></span>
                                                            <span class="hidden form-error match"><?php esc_html_e( 'The passwords do not match.', 'legenda' ) ?></span>
                                                        </p>
                                                        <p class="form-row form-row">
                                                            <label class=""><?php esc_html_e( "Re-enter your password", 'legenda' ) ?> <span class="required">*</span></label>
                                                            <input id="et_pass2" type="password" name="et_pass2" class="text input-text" value="" />
                                                            <span class="hidden form-error empty"><?php esc_html_e( 'Password should not be empty.', 'legenda' ) ?></span>
                                                            <span class="hidden form-error length"><?php esc_html_e( 'Password should have more than 10 symbols.', 'legenda' ) ?></span>
                                                            <span class="hidden form-error match"><?php esc_html_e( 'The passwords do not match.', 'legenda' ) ?></span>
                                                        </p>
                                                    </div>
                                                    <?php if ( etheme_get_option( 'registration_privacy' ) ): ?>
                                                        <p class="registration-privacy"><?php etheme_option( 'registration_privacy' ); ?></p>
                                                    <?php endif; ?>

                                                    <?php wp_nonce_field('et_new_user','et_new_user_nonce', true, true ); ?>

                                                    <?php et_display_captcha(); ?>

                                                    <p class="form-row right">
                                                        <input type="hidden" name="et_register" value="1">
                                                        <button class="button submitbtn fl-l active" type="submit"><span><?php esc_html_e( "Register", 'legenda' ) ?></span></button>
                                                        <div class="clear"></div>
                                                    </p>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="span6">
                                            <?php 
												if (have_posts()) :
												   while (have_posts()) :
												      the_post();
												      the_content();
												   endwhile;
												endif;
											 ?>
                                        </div>

                                    </div>

                                    <?php
                                }
                                else echo '<span class="error">' . esc_html__( 'Registration is currently disabled. Please try again later.', 'legenda' ) . '<span>';
                                ?>
                        </div>

                        <?php if($position == 'right' || ($responsive == 'bottom' && $position == 'left')): ?>
                            <div class="<?php echo esc_attr($sidebar_span); ?> sidebar sidebar-right">
                                <?php etheme_get_sidebar($sidebarname); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>


        <?php
        get_footer();
}
else {
   // echo "<script type='text/javascript'>window.location='". home_url() ."'</script>";
}
?>