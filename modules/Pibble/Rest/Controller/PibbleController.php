<?php

declare(strict_types=1);

namespace Modules\Pibble\Rest\Controller;

use Illuminate\Routing\Controller;
use Modules\Pibble\Domain\Service\PibbleServiceInterface;

class PibbleController extends Controller {

    public function __construct(
        private readonly PibbleServiceInterface $service,
    ) {}

    public function greetPibble() {
        return $this->service->greetPibble();
    }
}
