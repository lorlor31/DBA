<?php
/**
 * Checkout shipping information form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-shipping.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.6.0
 * @global WC_Checkout $checkout
 */
defined( 'ABSPATH' ) || exit;

global $woocommerce;

?>
<script>
	document.addEventListener('DOMContentLoaded', function() {
  // Récupérer les champs de formulaire
  var villeField = document.getElementById('billing_city');
  

  // Ajouter des écouteurs d'événement pour la saisie de caractères
  villeField.addEventListener('input', function(event) {
    var value = event.target.value;
    event.target.value = value.replace(/\d/g, ''); // Supprimer les chiffres

    if (value.match(/\d/)) {
      alert("Le champ ville ne peut pas contenir de chiffres");
    }
  });

  /*codePostalField.addEventListener('input', function(event) {
  var value = event.target.value;
  event.target.value = value.replace(/\D/g, ''); // Supprimer les caractères non numériques

  if (/\D/.test(value)) {
    alert("Entrez un Code Postal valide.");
  }
});*/

// Sélectionnez le premier formulaire de la page
var formulaire = document.querySelector('form.checkout woocommerce-checkout');

// Sélectionnez votre champ de code postal
var champCodePostal = document.getElementById('billing_postcode');

// Ajoutez un écouteur d'événements pour l'événement blur (désélection)
champCodePostal.addEventListener('blur', function() {
  // Récupérez la valeur du champ de code postal
  var valeur = champCodePostal.value;

  // Définissez les expressions régulières pour les codes postaux français, belges et luxembourgeois
  var regexFrance = /^[0-9]{5}$/;
  var regexBelgique = /^[1-9]{1}[0-9]{3}$/;
  var regexLuxembourg = /^[Ll]-[0-9]{4}$/;

  // Vérifiez si la valeur correspond à l'un des codes postaux
  if (!regexFrance.test(valeur) && !regexBelgique.test(valeur) && !regexLuxembourg.test(valeur)) {
    // Affichez une alerte si le code postal n'est pas valide
    alert("Veuillez entrer un Code Postale valide.");


    // Annulez l'action par défaut du formulaire pour empêcher sa soumission
    if (formulaire) {
      formulaire.addEventListener('submit', function(event) {
        event.preventDefault();
      });
    }
  }
});




});
</script>
<div class="woocommerce-shipping-fields">
	<?php if ( true === WC()->cart->needs_shipping_address() ) : ?>

		<h3 class="step-title"><?php esc_html_e( 'Shipping Address', 'legenda' ); ?></h3>
	
		<p class="form-row" id="ship-to-different-address">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" <?php checked( apply_filters( 'woocommerce_ship_to_different_address_checked', 'shipping' === get_option( 'woocommerce_ship_to_destination' ) ? 1 : 0 ), 0 ); ?> type="checkbox" name="ship_to_different_address" value="1" /> <label for="ship-to-different-address-checkbox" class="checkbox"><?php esc_html_e( 'Ship to a different address?', 'legenda' ); ?></label>
			</label>
		</p>

		<div class="shipping_address">

			<?php do_action( 'woocommerce_before_checkout_shipping_form', $checkout ); ?>
				<div class="woocommerce-shipping-fields__field-wrapper">
				<?php
					$fields = $checkout->get_checkout_fields( 'shipping' );

					foreach ( $fields as $key => $field ) {
						woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
					}
				?>
				</div>
			<?php do_action( 'woocommerce_after_checkout_shipping_form', $checkout ); ?>

		</div>

	<?php endif; ?>

	<div class="woocommerce-additional-fields">
		<?php do_action( 'woocommerce_before_order_notes', $checkout ); ?>

		<?php if ( apply_filters( 'woocommerce_enable_order_notes_field', get_option( 'woocommerce_enable_order_comments', 'yes' ) === 'yes' ) ) : ?>

			<?php if ( ! WC()->cart->needs_shipping() || wc_ship_to_billing_address_only() ) : ?>
				<h3><?php esc_html_e( 'Additional Information', 'legenda' ); ?></h3>
			<?php endif; ?>

			<div class="woocommerce-additional-fields__field-wrapper">
				<?php foreach ( $checkout->get_checkout_fields( 'order' )  as $key => $field ) : ?>
					<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
				<?php endforeach; ?>
			</div>

		<?php endif; ?>

		<?php do_action( 'woocommerce_after_order_notes', $checkout ); ?>
	</div>
	<?php if ( etheme_get_option( 'checkout_page' ) == 'stepbystep' ): ?>
		<a href="#" class="button active fl-r continue-checkout" data-next="5"><?php esc_html_e( 'Continue', 'legenda' ) ?></a>
	<?php endif ?>
</div>