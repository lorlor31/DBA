<?php

use luckywp\tableOfContents\admin\Rate;
use luckywp\tableOfContents\core\Core;

?>
<div class="wrap">
    <a href="<?php echo Rate::LINK ?>" target="_blank" class="lwptocSettingsRate"><?php echo sprintf(
        /* translators: %s: ★★★★★ */
            esc_html__('Leave a %s plugin review on WordPress.org', 'luckywp-table-of-contents'),
            '★★★★★'
        ) ?></a>
    <h1><?php echo esc_html__('Table of Contents Settings', 'luckywp-table-of-contents') ?></h1>
    <?php Core::$plugin->settings->showPage(false) ?>
</div>
