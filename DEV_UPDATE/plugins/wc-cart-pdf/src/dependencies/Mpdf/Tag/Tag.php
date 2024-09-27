<?php

namespace WCCartPDF\Mpdf\Tag;

use WCCartPDF\Mpdf\Strict;

use WCCartPDF\Mpdf\Cache;
use WCCartPDF\Mpdf\Color\ColorConverter;
use WCCartPDF\Mpdf\CssManager;
use WCCartPDF\Mpdf\Form;
use WCCartPDF\Mpdf\Image\ImageProcessor;
use WCCartPDF\Mpdf\Language\LanguageToFontInterface;
use WCCartPDF\Mpdf\Mpdf;
use WCCartPDF\Mpdf\Otl;
use WCCartPDF\Mpdf\SizeConverter;
use WCCartPDF\Mpdf\TableOfContents;

abstract class Tag
{

	use Strict;

	/**
	 * @var \WCCartPDF\Mpdf\Mpdf
	 */
	protected $mpdf;

	/**
	 * @var \WCCartPDF\Mpdf\Cache
	 */
	protected $cache;

	/**
	 * @var \WCCartPDF\Mpdf\CssManager
	 */
	protected $cssManager;

	/**
	 * @var \WCCartPDF\Mpdf\Form
	 */
	protected $form;

	/**
	 * @var \WCCartPDF\Mpdf\Otl
	 */
	protected $otl;

	/**
	 * @var \WCCartPDF\Mpdf\TableOfContents
	 */
	protected $tableOfContents;

	/**
	 * @var \WCCartPDF\Mpdf\SizeConverter
	 */
	protected $sizeConverter;

	/**
	 * @var \WCCartPDF\Mpdf\Color\ColorConverter
	 */
	protected $colorConverter;

	/**
	 * @var \WCCartPDF\Mpdf\Image\ImageProcessor
	 */
	protected $imageProcessor;

	/**
	 * @var \WCCartPDF\Mpdf\Language\LanguageToFontInterface
	 */
	protected $languageToFont;

	const ALIGN = [
		'left' => 'L',
		'center' => 'C',
		'right' => 'R',
		'top' => 'T',
		'text-top' => 'TT',
		'middle' => 'M',
		'baseline' => 'BS',
		'bottom' => 'B',
		'text-bottom' => 'TB',
		'justify' => 'J'
	];

	public function __construct(
		Mpdf $mpdf,
		Cache $cache,
		CssManager $cssManager,
		Form $form,
		Otl $otl,
		TableOfContents $tableOfContents,
		SizeConverter $sizeConverter,
		ColorConverter $colorConverter,
		ImageProcessor $imageProcessor,
		LanguageToFontInterface $languageToFont
	) {

		$this->mpdf = $mpdf;
		$this->cache = $cache;
		$this->cssManager = $cssManager;
		$this->form = $form;
		$this->otl = $otl;
		$this->tableOfContents = $tableOfContents;
		$this->sizeConverter = $sizeConverter;
		$this->colorConverter = $colorConverter;
		$this->imageProcessor = $imageProcessor;
		$this->languageToFont = $languageToFont;
	}

	public function getTagName()
	{
		$tag = get_class($this);
		return strtoupper(str_replace('WCCartPDF\Mpdf\Tag\\', '', $tag));
	}

	protected function getAlign($property)
	{
		$property = strtolower($property);
		return array_key_exists($property, self::ALIGN) ? self::ALIGN[$property] : '';
	}

	abstract public function open($attr, &$ahtml, &$ihtml);

	abstract public function close(&$ahtml, &$ihtml);

}
