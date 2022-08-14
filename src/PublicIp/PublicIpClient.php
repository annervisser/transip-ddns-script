<?php

declare(strict_types=1);

namespace TransipDdns\PublicIp;

interface PublicIpClient
{
	public function getPublicIp(): IpAddress;
}
