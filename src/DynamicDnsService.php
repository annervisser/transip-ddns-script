<?php

declare(strict_types=1);

namespace TransipDdns;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use TransipDdns\DNS\DnsUpdater;
use TransipDdns\PublicIp\IpAddress;
use TransipDdns\PublicIp\PublicIpClient;
use Webmozart\Assert\Assert;

class DynamicDnsService
{
	private const PUBLIC_IP_CACHE_KEY = 'transip-ddns-public-ip';

	public function __construct(
		private readonly DnsUpdater $dnsUpdater,
		private readonly PublicIpClient $publicIpClient,
		private readonly CacheItemPoolInterface $cache,
		private readonly LoggerInterface $logger,
	) {
	}

	public function run(Settings $settings): void
	{
		$publicIp       = $this->publicIpClient->getPublicIp();
		$cachedPublicIp = $this->getCachedIp();
		$this->updateCachedIp($publicIp);

		if ($cachedPublicIp !== null && $publicIp === $cachedPublicIp) {
			$this->logger->info('Public IP matches value from cache. Not doing anything');
			exit(0);
		}

		$dnsEntry         = $this->getDnsEntry($settings);
		$currentIpAddress = new IpAddress($dnsEntry->value);

		if ($currentIpAddress->equals($publicIp)) {
			$this->logger->info('DNS entry is already same as our current IP. Not changing anything');
			exit(0);
		}

		if (! $currentIpAddress->equals($cachedPublicIp)) {
			$this->logger->error('! The current value of the DNS entry doesn\'t match with our cached value');
			$this->logger->info(
				'If this is the first time running the script, please manually set your record to match the current public ip'
			);
			exit(1);
		}

		$this->logger->info('DNS needs update');

		$dnsEntry->value = $publicIp->ip;
		$this->dnsUpdater->updateEntry($dnsEntry);

		$this->logger->info('DNS entry updated');
	}

	private function getCachedIp(): IpAddress|null
	{
		$cachedIpItem = $this->cache->getItem(self::PUBLIC_IP_CACHE_KEY);
		$cachedIp     = $cachedIpItem->get();
		Assert::nullOrIp($cachedIp);

		return $cachedIp !== null ? new IpAddress($cachedIp) : null;
	}

	private function updateCachedIp(IpAddress $ipAddress): void
	{
		$cachedIpItem = $this->cache->getItem(self::PUBLIC_IP_CACHE_KEY);
		$cachedIpItem->set($ipAddress->ip);
		$this->cache->save($cachedIpItem);
	}

	private function getDnsEntry(Settings $settings): DNS\DnsEntry
	{
		return $this->dnsUpdater->getDnsEntryForDomain(
			$settings->domainName,
			$settings->dnsEntryName,
			$settings->dnsEntryType
		);
	}
}
