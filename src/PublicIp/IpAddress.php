<?php

declare(strict_types=1);

namespace TransipDdns\PublicIp;

use Webmozart\Assert\Assert;

use function trim;

class IpAddress
{
	public readonly string $ip;

	public function __construct(
		string $ip
	) {
		$ip = trim($ip);
		Assert::ip($ip);
		$this->ip = $ip;
	}

	public function equals(IpAddress $publicIp): bool
	{
		return $this->ip === $publicIp->ip;
	}
}
