<?php

namespace WCCartPDF\Mpdf\Language;

interface ScriptToLanguageInterface
{

	public function getLanguageByScript($script);

	public function getLanguageDelimiters($language);

}
