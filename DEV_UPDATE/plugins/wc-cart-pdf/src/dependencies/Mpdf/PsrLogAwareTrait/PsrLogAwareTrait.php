<?php

namespace WCCartPDF\Mpdf\PsrLogAwareTrait;

use WCCartPDF\Psr\Log\LoggerInterface;

trait PsrLogAwareTrait 
{

	/**
	 * @var \WCCartPDF\Psr\Log\LoggerInterface
	 */
	protected $logger;

	public function setLogger(LoggerInterface $logger): void
	{
		$this->logger = $logger;
	}
	
}
