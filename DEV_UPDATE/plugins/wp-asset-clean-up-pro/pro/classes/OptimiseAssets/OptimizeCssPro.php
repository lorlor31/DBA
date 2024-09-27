<?php
namespace WpAssetCleanUpPro\OptimiseAssets;

use WpAssetCleanUpPro\PositionsPro;

/**
 * Class OptimizeCssPro
 * @package WpAssetCleanUpPro
 */
class OptimizeCssPro
{
	/**
	 *
	 */
	public function init()
	{
        add_filter('wpacu_change_css_position', array($this, 'changeCssPosition'), 10, 1);
	}

	/**
	 * @param $htmlSource
	 *
	 * @return string
	 */
	public function changeCssPosition($htmlSource)
	{
		return PositionsPro::doChanges($htmlSource);
	}
}
