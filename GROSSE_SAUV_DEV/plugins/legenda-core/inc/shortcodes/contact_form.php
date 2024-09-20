<?php 

// **********************************************************************//
// ! Contact form
// **********************************************************************//

if(!function_exists('et_contact_form')) {
  function et_contact_form($atts) {

    extract( shortcode_atts( array(
      'class' => ''
    ), $atts ) );

    ob_start();
    ?>
        <div id="contactsMsgs"></div>
        <form action="<?php the_permalink(); ?>" method="get" id="contact-form" class="contact-form <?php echo $class; ?>">

            <div class="form-group">
              <p class="form-name">
                <label for="name" class="control-label"><?php esc_html_e('Name and Surname', 'legenda-core') ?> <span class="required">*</span></label>
                <input type="text" name="contact-name" class="required-field form-control" id="contact-name">
              </p>
            </div>

            <div class="form-group">
                <p class="form-name">
                  <label for="contact-email" class="control-label"><?php esc_html_e('Email', 'legenda-core') ?> <span class="required">*</span></label>
                  <input type="text" name="contact-email" class="required-field form-control" id="contact-email">
                </p>
            </div>

            <div class="form-group">
              <p class="form-name">
                <label for="contact-website" class="control-label"><?php esc_html_e('Website', 'legenda-core') ?></label>
                <input type="text" name="contact-website" class="form-control" id="contact-website">
              </p>
            </div>


            <div class="form-group">
              <p class="form-textarea">
                <label for="contact_msg" class="control-label"><?php esc_html_e('Message', 'legenda-core'); ?> <span class="required">*</span></label>
                <textarea name="contact-msg" id="contact-msg" class="required-field form-control" cols="30" rows="7"></textarea>
              </p>
            </div>
      
      <?php et_display_captcha(); ?>

            <p class="pull-right">
              <input type="hidden" name="contact-submit" id="contact-submit" value="true" >
              <span class="spinner"><?php esc_html_e('Sending...', 'legenda-core') ?></span>
              <button class="btn btn-black big" id="submit" type="submit"><?php esc_html_e('Send message', 'legenda-core') ?></button>
            </p>

            <div class="clearfix"></div>
        </form>
    <?php
    $output = ob_get_contents();
    ob_end_clean();

    return $output;
  }
}


?>