<?php

declare(strict_types=1);

namespace Core\Proxies;

class PibbleServiceInterfaceProxy_15260c6c2743cc5d implements \Modules\Pibble\Domain\Service\PibbleServiceInterface
{
    public function __construct(
        private readonly \Core\ServiceProxy\ServiceProxy $proxy
    ) {}


    public function greetPibble(): string
    {
        return $this->proxy->call('greetPibble', []);
    }

    public function getPibble(string $name): \Modules\Pibble\Domain\Model\Pibble
    {
        return $this->proxy->call('getPibble', [$name]);
    }

    public function createPibble(string $name): \Modules\Pibble\Domain\Model\Pibble
    {
        return $this->proxy->call('createPibble', [$name]);
    }

    public function washBelly(string $name): bool
    {
        return $this->proxy->call('washBelly', [$name]);
    }

}
