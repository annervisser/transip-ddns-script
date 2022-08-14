<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Transip\Api\Library\TransipAPI;
use TransipDdns\DNS\DnsUpdaterUsingTransip;
use TransipDdns\DynamicDnsService;
use TransipDdns\PublicIp\PublicIpClientUsingIpify;
use TransipDdns\Settings;

require_once 'vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required([
	'TRANSIP_USERNAME',
	'TRANSIP_PRIVATE_KEY',
	'TRANSIP_DOMAIN',
	'TRANSIP_DNS_ENTRY_NAME',
	'TRANSIP_DNS_ENTRY_TYPE',
])->notEmpty();
$dotenv->ifPresent(['TRANSIP_WHITELIST_ONLY', 'TRANSIP_TEST_MODE'])->isBoolean();

$logger = new ConsoleLogger(new ConsoleOutput(ConsoleOutput::VERBOSITY_DEBUG, true));

$transipUsername      = (string) $_ENV['TRANSIP_USERNAME'];
$transipPrivateKey    = (string) $_ENV['TRANSIP_PRIVATE_KEY'];
$transipWhitelistOnly = filter_var($_ENV['TRANSIP_WHITELIST_ONLY'], FILTER_VALIDATE_BOOLEAN);
$transipTestMode      = filter_var($_ENV['TRANSIP_TEST_MODE'], FILTER_VALIDATE_BOOLEAN);

$transipDomainName   = (string) $_ENV['TRANSIP_DOMAIN'];
$transipDNSEntryName = (string) $_ENV['TRANSIP_DNS_ENTRY_NAME'];
$transipDNSEntryType = (string) $_ENV['TRANSIP_DNS_ENTRY_TYPE'];

/** Include username and whitelist-only setting to ensure transip token cache is busted when settings change */
$cacheNamespace = sprintf(
	'transip-ddns-%s-%s',
	$transipUsername,
	$transipWhitelistOnly ? 'whitelist' : 'no-whitelist'
);
$cache          = new FilesystemAdapter($cacheNamespace);
$publicIpClient = new PublicIpClientUsingIpify(new Client());

$transipApi = new TransipAPI(
	$transipUsername,
	$transipPrivateKey,
	$transipWhitelistOnly,
	cache: $cache
);
$transipApi->setTestMode($transipTestMode);

if ($transipApi->getTestMode()) {
	$logger->warning('API running in test mode, no changes will be persisted');
}

if ($transipApi->test()->test() === true) {
	$logger->debug('TransIP API connection successful!');
}

$settings = new Settings(
	$transipDomainName,
	$transipDNSEntryName,
	$transipDNSEntryType,
);

$service = new DynamicDnsService(
	new DnsUpdaterUsingTransip($transipApi),
	$publicIpClient,
	$cache,
	$logger
);
$service->run($settings);
