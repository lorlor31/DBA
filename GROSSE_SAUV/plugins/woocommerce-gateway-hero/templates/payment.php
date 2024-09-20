<div class="ln-pay">
    <h1>Vous serez redirigé vers payment.hero.fr</h1>
    <a class="checkout-button button alt btn btn-default" href="<?php echo $redirectUrl ?>">Le processus</a>
</div>

<script>
    setTimeout(function () {
        window.location = '<?php echo $redirectUrl?>';
    }, 0);
</script>

