	<?php $fd = etheme_get_option('footer_demo'); ?>	
	<?php $ft = ''; $ft = apply_filters('custom_footer_filter',$ft); ?>
    <?php global $etheme_responsive; ?>

	<?php if(is_active_sidebar('prefooter')): ?>
		<div class="prefooter prefooter-<?php echo esc_attr($ft); ?>">
			<div class="container">
				<div class="double-border">
	                <?php if ( !is_active_sidebar( 'prefooter' ) ) : ?>
	               		<?php //if($fd) etheme_footer_demo('prefooter'); ?>
	                <?php else: ?>
	                    <?php dynamic_sidebar( 'prefooter' ); ?>
	                <?php endif; ?>   
				</div>
			</div>
		</div>
	<?php endif; ?>


	<?php if(is_active_sidebar('footer1') ): ?>
		<div class="footer-top footer-top-<?php echo esc_attr($ft); ?>">
			<div class="container">
				<div class="double-border">
	                <?php if ( !is_active_sidebar( 'footer1' ) ) : ?>
	               		<?php if($fd) etheme_footer_demo('footer1'); ?>
	                <?php else: ?>
	                    <?php dynamic_sidebar( 'footer1' ); ?>
	                <?php endif; ?>   
				</div>
			</div>
		</div>
	<?php endif; ?>
	<?php if(is_active_sidebar('footer2') || $fd): ?>
		<footer class="footer footer-bottom-<?php echo esc_attr($ft); ?>">
			<div class="container">
                <?php if ( !is_active_sidebar( 'footer2' ) ) : ?>
               		<?php if($fd) etheme_footer_demo('footer2'); ?>
                <?php else: ?>
                    <?php dynamic_sidebar( 'footer2' ); ?>
                <?php endif; ?> 
			</div>
		</footer>
	<?php endif; ?>

	<?php if(is_active_sidebar('footer9') || is_active_sidebar('footer10') || $fd): ?>
		<div class="copyright copyright-<?php echo esc_attr($ft); ?>">
			<div class="container">
				<div class="row-fluid">
					<div class="span6">
						<?php if(is_active_sidebar('footer9')): ?> 
							<?php dynamic_sidebar('footer9'); ?>	
						<?php else: ?>
							<?php if($fd) etheme_footer_demo('footer9'); ?>
						<?php endif; ?>
					</div>

					<div class="span6 a-right">
						<?php if(is_active_sidebar('footer10')): ?> 
							<?php dynamic_sidebar('footer10'); ?>	
						<?php else: ?>
							<?php if($fd) etheme_footer_demo('footer10'); ?>
						<?php endif; ?>
					</div>
				</div>
	            <div class="row-fluid">
	                <?php if(etheme_get_option('responsive')): ?>
	                	<div class="span12 responsive-switcher a-center visible-phone visible-tablet <?php if(!$etheme_responsive) echo 'visible-desktop'; ?>">
	                    	<?php if($etheme_responsive): ?>
	                    		<a href="<?php echo home_url(); ?>/?responsive=off"><i class="icon-mobile-phone"></i></a> <?php esc_html_e('Mobile version',  'legenda') ?>: 
	                    		<a href="<?php echo home_url(); ?>/?responsive=off"><?php esc_html_e('Enabled',  'legenda') ?></a>
	                    	<?php else: ?>
	                    		<a href="<?php echo home_url(); ?>/?responsive=on"><i class="icon-mobile-phone"></i></a> <?php esc_html_e('Mobile version',  'legenda') ?>: 
	                    		<a href="<?php echo home_url(); ?>/?responsive=on"><?php esc_html_e('Disabled',  'legenda') ?></a>
	                    	<?php endif; ?>
	                	</div>
	                <?php endif; ?>  
	            </div>
			</div>
		</div>
	<?php endif; ?>

	</div> <!-- page wrapper -->
	<?php if (etheme_get_option('to_top')): ?>
		<div class="back-to-top">
			<span><?php esc_html_e('Back to top', 'legenda') ?></span>
		</div>
	<?php endif ?>

	<?php do_action('after_page_wrapper'); ?>
	<?php
		/* Always have wp_footer() just before the closing </body>
		 * tag of your theme, or you will break many plugins, which
		 * generally use this hook to reference JavaScript files.
		 */

		wp_footer();
	?>
<script type="text/javascript">var agSiteId="9257";</script>
<script src="https://www.societe-des-avis-garantis.fr/wp-content/plugins/ag-core/widgets/JsWidget.js" type="text/javascript" defer></script>
	
   <!-- Bing Ads -->
   <script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:"5105661"};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/bat.js","uetq");</script>
</body>
</html>