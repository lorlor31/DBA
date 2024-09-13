<ul class="order_details">
    <li><?php echo __('Paiement effectué avec succès'); ?></li>
    <?php if($successUrl):?>
    <script>
        setTimeout(function () {
            window.location = '<?php echo $successUrl?>';
        }, 0);
    </script>
    <?php endif;?>
</ul>
