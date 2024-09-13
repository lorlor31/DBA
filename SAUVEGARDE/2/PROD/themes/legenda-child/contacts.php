<?php
/*
	Template name: Contact page
*/
?>
<?php
	$error_name  = false;
	$error_email = false;
	$error_phone   = false;
	$error_msg   = false;

	if(isset($_GET['contact-submit'])) {
		header("Content-type: application/json");
		$name = '';
		$phone = '';
		$ets = '';
		$email = '';
		$website = '';
		$message = '';
		$reciever_email = '';
		$return = array();

		if(trim($_GET['contact-name']) === '') {
			$error_name = true;
		} else{
			$name = trim($_GET['contact-name']);
		}
		if(trim($_GET['contact-phone']) === '') {
			$error_phone = true;
		} else{
			$phone = trim($_GET['contact-phone']);
		}

		if(trim($_GET['contact-email']) === '' || !isValidMail($_GET['contact-email'])) {
			$error_email = true;
		} else{
			$email = trim($_GET['contact-email']);
		}

		if(trim($_GET['contact-msg']) === '') {
			$error_msg = true;
		} else{
			$message = trim($_GET['contact-msg']);
		}

		$website = stripslashes(trim($_GET['contact-website']));

		// Check if we have errors

		if(!$error_name && !$error_email && !$error_msg  && !$error_phone) {
			// Get the received email
			$reciever_email = etheme_get_option('contacts_email');

			$subject = 'Vous avez été contacté par ' . $name;

			$body = "Vous avez été contacté par $name ($ets $phone). Son message est: " . PHP_EOL . PHP_EOL;
			$body .= $message . PHP_EOL . PHP_EOL;
			// $body .= "You can contact $name via email at $email";
			// if ($website != '') {
				// $body .= " and visit their website at $website" . PHP_EOL . PHP_EOL;
			// }
			$body .= PHP_EOL . PHP_EOL;

			$headers = "From $email ". PHP_EOL;
			$headers .= "Reply-To: $email". PHP_EOL;
			$headers .= "MIME-Version: 1.0". PHP_EOL;
			$headers .= "Content-type: text/plain; charset=utf-8". PHP_EOL;
			$headers .= "Content-Transfer-Encoding: quoted-printable". PHP_EOL;

			if(function_exists('et_mail') && et_mail($reciever_email, $subject, $body, $headers)) {
				$return['status'] = 'success';
				$return['msg'] = __('All is well, your email has been sent.', 'legenda');
			} else{
				$return['status'] = 'error';
				$return['msg'] = __('Error while sending a message!', 'legenda');
			}

		}else{
			// Return errors
			$return['status'] = 'error';
			$return['msg'] = __('Please, fill in the required fields!', 'legenda');
		}

		echo json_encode($return);
		die();
	}

?>

<?php
	get_header();
?>

<?php
	$contact_page = etheme_get_option('contact_page_type');
	$googleMap = etheme_get_option('google_map_enable');
	if(isset($_GET['cont']) && $_GET['cont'] == 2) {
		$contact_page = 'custom';
	}
?>


<?php et_page_heading(); ?>

<div class="container">
	<div class="page-content contact-page-<?php echo esc_attr($contact_page); ?>">
	<!-- Google Code for Lead Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 1024793162;
var google_conversion_language = "fr";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
var google_conversion_label = "KuJwCJrp6W0QyrTU6AM";
var google_remarketing_only = false;
/* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/1024793162/?label=KuJwCJrp6W0QyrTU6AM&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
		<?php if ($contact_page == 'default' && $googleMap): ?>
			<div id="map" class="google-map googlemap-wide">
			    <p><?php echo esc_html__('Enable your JavaScript!', 'legenda'); ?></p>
			</div>
		<?php endif ?>
		<div class="row-fluid">
			<?php if(have_posts()): while(have_posts()) : the_post(); ?>
				<?php if($contact_page == 'default'): ?>
					<div class="span12">
						<div class="row-fluid">
							<div class="span7">
								<h3 class="contact-form-title"><?php _e('Contact Form', 'legenda') ?></h3>
								<div id="contactsMsgs"></div>
								<form action="<?php the_permalink(); ?>" method="post" id="contact-form">

										<div class="row-fluid">

											<div class="span6">
												<p class="form-name">
													<label for="name">Nom et Prénom <span class="required">*</span></label>
													<input type="text" name="contact-name" class="required-field" id="contact-name">
												</p>
											</div>
											<div class="span6">
												<p class="form-name">
													<label for="name">Etablissement</label>
													<input type="text" name="contact-ets" class="required-field" id="contact-ets">
												</p>
											</div>
											<div class="span6" style="margin-left:0!important;">
												<p class="form-name">
													<label for="name">Téléphone <span class="required">*</span></label>
													<input type="text" name="contact-phone" class="required-field" id="contact-phone">
												</p>
											</div>
											<div class="span6">
												<p class="form-name">
													<label for="name"><?php esc_html_e('Email', 'legenda') ?> <span class="required">*</span></label>
													<input type="text" name="contact-email" class="required-field" id="contact-email">
												</p>
											</div>
										</div>

										<p class="form-name hidden">
											<label for="name"><?php esc_html_e('Website', 'legenda') ?></label>
											<input type="text" name="contact-website" id="contact-website">
										</p>

										<p class="form-textarea">
											<label for="contact_msg"><?php esc_html_e('Message', 'legenda'); ?> <span class="required">*</span></label>
											<textarea name="contact-msg" id="contact-msg" class="required-field" cols="30" rows="7"></textarea>
										</p>
										
										<?php if ( etheme_get_option( 'contacts_privacy' ) ): ?>
											<p class="contacts-privacy" style="margin-bottom: 20px;"><?php etheme_option( 'contacts_privacy' ); ?></p>
										<?php endif; ?>
<input type="hidden" name="referal" value="" id="referal">
<input type="hidden" name="referal" value="" id="cpgn">
<input type="hidden" name="referal" value="" id="lpage">
										<?php et_display_captcha(); ?>

										<p class="a-right">
											<input type="hidden" name="contact-submit" id="contact-submit" value="true" >
											<span class="spinner"><?php esc_html_e('Sending...', 'legenda') ?></span>
											<button class="button" id="submit" type="submit"><?php esc_html_e('Send message', 'legenda') ?></button>
										</p>
									<div class="clear"></div>
								</form>
							</div>
							<div class="span5">
								<?php the_content(); ?>
							</div>
						</div>
					</div>
					<?php if ($googleMap): ?>
						<script type="text/javascript">
						    function etheme_google_map() {
						        var styles = {};

						        var myLatlng = new google.maps.LatLng(<?php etheme_option('google_map') ?>);
						        var myOptions = {
						            zoom: 17,
						            center: myLatlng,
						            mapTypeId: google.maps.MapTypeId.ROADMAP,
						            disableDefaultUI: true,
						            mapTypeId: '8theme',
						            draggable: true,
						            zoomControl: true,
									panControl: false,
									mapTypeControl: true,
									scaleControl: true,
									streetViewControl: true,
									overviewMapControl: true,
						            scrollwheel: false,
						            disableDoubleClickZoom: false
						        }
						        var map = new google.maps.Map(document.getElementById("map"), myOptions);
						        var styledMapType = new google.maps.StyledMapType(styles['8theme'], {name: '8theme'});
						        map.mapTypes.set('8theme', styledMapType);

						        var marker = new google.maps.Marker({
						            position: myLatlng,
						            map: map,
						            title:""
						        });
						    }
						    
						</script>

    					<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo etheme_option( 'google_map_api' ); ?>&callback=etheme_google_map" async defer></script>
					<?php endif ?>
				<?php else: ?>
					<div class="span8">
						<?php the_content(); ?>
					</div>
					<div class="span4">
						<h3 class="contact-form-title"><?php esc_html_e('Contact Form', 'legenda') ?></h3>
						<div id="contactsMsgs"></div>
						<form action="<?php the_permalink(); ?>" method="post" id="contact-form">
							<p class="form-name">
								<label for="name"><?php esc_html_e('Name and Surname', 'legenda') ?> <span class="required">*</span></label>
								<input type="text" name="contact-name" class="required-field" id="contact-name">
							</p>

							<p class="form-name">
								<label for="name"><?php esc_html_e('Email', 'legenda') ?> <span class="required">*</span></label>
								<input type="text" name="contact-email" class="required-field" id="contact-email">
							</p>

							<p class="form-name">
								<label for="name"><?php esc_html_e('Website', 'legenda') ?></label>
								<input type="text" name="contact-website" id="contact-website">
							</p>
							<p class="form-textarea">
								<label for="contact_msg"><?php esc_html_e('Message', 'legenda'); ?> <span class="required">*</span></label>
								<textarea name="contact-msg" id="contact-msg" class="required-field" cols="30" rows="7"></textarea>
							</p>

							<?php if ( etheme_get_option( 'contacts_privacy' ) ): ?>
								<p class="contacts-privacy" style="margin-bottom: 20px;"><?php etheme_option( 'contacts_privacy' ); ?></p>
							<?php endif; ?>

							<?php et_display_captcha(); ?>

							<div class="captcha-block">
								<img src="<?php echo esc_url($captcha_img); ?>">
								<input type="text" name="captcha-word" class="captcha-input">
								<input type="hidden" name="captcha-prefix" value="<?php echo esc_attr($prefix); ?>">
							</div>

							<div class="clear"></div>
							<p class="a-right">
								<input type="hidden" name="contact-submit" id="contact-submit" value="true" >
								<span class="spinner"><?php esc_html_e('Sending...', 'legenda') ?></span>
								<button class="button" id="submit" type="submit"><?php esc_html_e('Send message', 'legenda') ?></button>
							</p>
							<div class="clear"></div>
						</form>
					</div>
				<?php endif; ?>

			<?php endwhile; else: ?>

				<h1><?php esc_html_e('No pages were found!', 'legenda') ?></h1>

			<?php endif; ?>
		</div>

	</div>
</div>


<?php
	get_footer();
?>
