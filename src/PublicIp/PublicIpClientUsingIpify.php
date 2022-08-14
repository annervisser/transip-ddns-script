<?php

declare(strict_types=1);

namespace TransipDdns\PublicIp;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

final class PublicIpClientUsingIpify implements PublicIpClient
{
	private const IPIFY_API_ENDPOINT = 'https://api.ipify.org/';

	public function __construct(
		private readonly ClientInterface $client
	) {
	}

	/**
	 * @throws ClientExceptionInterface
	 */
	public function getPublicIp(): IpAddress
	{
		$response = $this->client->sendRequest(new Request('GET', self::IPIFY_API_ENDPOINT));

		$body = $response->getBody()->getContents();

		return new IpAddress($body);
	}
}
