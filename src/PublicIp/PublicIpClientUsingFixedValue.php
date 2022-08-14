<?php

declare(strict_types=1);

namespace TransipDdns\PublicIp;

final class PublicIpClientUsingFixedValue implements PublicIpClient
{
	public function __construct(
		private readonly string $fixedValue
	) {
	}

	public function getPublicIp(): IpAddress
	{
		return new IpAddress($this->fixedValue);
	}
}
