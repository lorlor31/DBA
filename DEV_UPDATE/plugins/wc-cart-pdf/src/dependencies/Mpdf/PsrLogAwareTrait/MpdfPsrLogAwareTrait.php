<?php

namespace WCCartPDF\Mpdf\PsrLogAwareTrait;

use WCCartPDF\Psr\Log\LoggerInterface;

trait MpdfPsrLogAwareTrait
{

	/**
	 * @var \WCCartPDF\Psr\Log\LoggerInterface
	 */
	protected $logger;

	public function setLogger(LoggerInterface $logger): void
	{
		$this->logger = $logger;
		if (property_exists($this, 'services') && is_array($this->services)) {
			foreach ($this->services as $name) {
				if ($this->$name && $this->$name instanceof \WCCartPDF\Psr\Log\LoggerAwareInterface) {
					$this->$name->setLogger($logger);
				}
			}
		}
	}

}
