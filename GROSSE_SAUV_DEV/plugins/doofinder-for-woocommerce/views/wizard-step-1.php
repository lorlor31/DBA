<?php

namespace Doofinder\WP;

$error = $error ?? false;

use Doofinder\WP\Settings;
use Doofinder\WP\Setup_Wizard;

/** @var Setup_Wizard $this */


$sectors = [
    "Pharma & Cosmetics" => "parapharmacy",
    "Tech Products & Electronics" => "technology",
    "Apparel & Accessories" => "fashion",
    "Sport & Fitness" => "sport",
    "Childcare" => "childcare",
    "Pets" => "pets",
    "Home & Garden" => "home",
    "Food & Beverages" => "food",
    "Toys & Hobbies" => "toys",
    "Auto Parts & Accessories" => "autos",
    "Leisure & Culture" => "leisure",
    "Others" => "others"
];

$selected_sector = Settings::get_sector('')

?>
<form action="<?php echo Setup_Wizard::get_url(['step' => '1']); ?>" method="post">
    <div class="df-setup-step__actions">
        <select id="sector-select" name="sector" required>
            <option value="" selected disabled hidden> - <?php _e('Choose a sector', 'wordpress-doofinder'); ?> - </option>
            <?php
            foreach ($sectors as $sector => $key) {
                $selected = "";
                if ($selected_sector === $key) {
                    $selected = ' selected="selected"';
                }
                echo '<option value="' . $key . '"' . $selected . '>' . __($sector, 'wordpress-doofinder') . '</option>';
            }
            ?>
        </select>
        <button type="submit"><?php _e('Next', 'wordpress-doofinder'); ?></button>
        <input type="hidden" id="process-step-input" name="process-step" value="1" />
        <input type="hidden" id="next-step-input" name="next-step" value="2" />
    </div>
</form>