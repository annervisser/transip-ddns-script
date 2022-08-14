<?php

declare(strict_types=1);

namespace TransipDdns\DNS;

interface DnsUpdater
{
	public function getDnsEntryForDomain(string $domainName, string $entryName, string $entryType): DnsEntry;

	public function updateEntry(DnsEntry $entry): void;
}
