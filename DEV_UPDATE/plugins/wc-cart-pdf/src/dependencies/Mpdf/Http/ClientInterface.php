<?php

namespace WCCartPDF\Mpdf\Http;

use WCCartPDF\Psr\Http\Message\RequestInterface;

interface ClientInterface
{

	public function sendRequest(RequestInterface $request);

}
