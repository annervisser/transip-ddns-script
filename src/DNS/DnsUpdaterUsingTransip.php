<?php

declare(strict_types=1);

namespace TransipDdns\DNS;

use Transip\Api\Library\Entity\Domain\DnsEntry as TransipDnsEntry;
use Transip\Api\Library\TransipAPI;
use Webmozart\Assert\Assert;

use function array_filter;
use function reset;

final class DnsUpdaterUsingTransip implements DnsUpdater
{
	public function __construct(
		private readonly TransipAPI $client
	) {
	}

	public function getDnsEntryForDomain(string $domainName, string $entryName, string $entryType): DnsEntry
	{
		$dnsEntries = $this->client->domainDns()->getByDomainName($domainName);

		$transipDnsEntry = self::findDnsEntry($dnsEntries, $entryName, $entryType);

		return self::dnsEntryFromTransipEntry($domainName, $transipDnsEntry);
	}

	public function updateEntry(DnsEntry $entry): void
	{
		$transipEntry = self::dnsEntryToTransipEntry($entry);
		$this->client->domainDns()->updateEntry($entry->domainName, $transipEntry);
	}

	/**
	 * @param TransipDnsEntry[] $entries
	 * @psalm-param DnsEntry::TYPE_* $type
	 */
	private static function findDnsEntry(array $entries, string $name, string $type): TransipDnsEntry
	{
		$filtered = array_filter(
			$entries,
			static fn (TransipDnsEntry $entry) => $entry->getName() === $name && $entry->getType() === $type
		);
		Assert::count($filtered, 1);

		return reset($filtered);
	}

	private static function dnsEntryFromTransipEntry(string $domainName, TransipDnsEntry $transipEntry): DnsEntry
	{
		return new DnsEntry(
			$domainName,
			$transipEntry->getName(),
			$transipEntry->getExpire(),
			$transipEntry->getType(),
			$transipEntry->getContent(),
		);
	}

	private static function dnsEntryToTransipEntry(DnsEntry $dnsEntry): TransipDnsEntry
	{
		$transipEntry = new TransipDnsEntry();
		$transipEntry->setName($dnsEntry->name);
		$transipEntry->setExpire($dnsEntry->TTL);
		$transipEntry->setType($dnsEntry->type);
		$transipEntry->setContent($dnsEntry->value);

		return $transipEntry;
	}
}
