<?php

declare(strict_types=1);

namespace TransipDdns;

use TransipDdns\DNS\DnsEntry;
use Webmozart\Assert\Assert;

/** @psalm-immutable */
final class Settings
{
	public function __construct(
		public readonly string $domainName,
		public readonly string $dnsEntryName,
		public readonly string $dnsEntryType,
	) {
		Assert::stringNotEmpty($this->domainName);
		Assert::stringNotEmpty($this->dnsEntryName);
		DnsEntry::assertValidDnsType($dnsEntryType);
		Assert::oneOf($dnsEntryType, [DnsEntry::TYPE_A, DnsEntry::TYPE_AAAA]);
	}
}
