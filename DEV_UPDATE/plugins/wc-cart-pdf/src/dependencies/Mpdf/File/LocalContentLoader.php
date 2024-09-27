<?php

namespace WCCartPDF\Mpdf\File;

class LocalContentLoader implements \WCCartPDF\Mpdf\File\LocalContentLoaderInterface
{

	public function load($path)
	{
		return file_get_contents($path);
	}

}
