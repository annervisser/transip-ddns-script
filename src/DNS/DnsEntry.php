<?php

declare(strict_types=1);

namespace TransipDdns\DNS;

use Webmozart\Assert\Assert;

class DnsEntry
{
	public const TYPE_A    = 'A';
	public const TYPE_AAAA = 'AAAA';

	public function __construct(
		public readonly string $domainName,
		public readonly string $name,
		public readonly int $TTL,
		public readonly string $type,
		public string $value,
	) {
		Assert::stringNotEmpty($this->domainName);
		Assert::stringNotEmpty($this->name);
		self::assertValidDnsType($this->type);
		Assert::ip($this->value);
	}

	/** @psalm-assert self::TYPE_* $value */
	public static function assertValidDnsType(string $value): void
	{
		Assert::oneOf($value, [
			self::TYPE_A,
			self::TYPE_AAAA,
		]);
	}
}
