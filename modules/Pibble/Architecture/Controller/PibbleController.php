<?php

declare(strict_types=1);

namespace Modules\Pibble\Architecture\Controller;

use Core\Annotations\Feature;
use Core\Enum\HttpCode;
use Core\Exception\HttpException\HttpConflictException;
use Core\Exception\HttpException\HttpNotFoundException;
use Illuminate\Routing\Controller;
use Modules\Pibble\Domain\DTO\BellyWashResponseDto;
use Modules\Pibble\Domain\DTO\PibbleRequestDto;
use Modules\Pibble\Domain\DTO\PibbleResponseDto;
use Modules\Pibble\Domain\Exception\PibbleAlreadyExistsException;
use Modules\Pibble\Domain\Exception\PibbleNotFoundException;
use Modules\Pibble\Domain\Service\PibbleServiceInterface;
use ReflectionClass;
use ReflectionMethod;

class PibbleController extends Controller {

    public function __construct(
        private readonly PibbleServiceInterface $pibbleService,
    ) {}

    public function getGreet() {
        return $this->pibbleService->greetPibble();
    }

    public function getPibble(string $name) {
        try {
            $pibble = $this->pibbleService->getPibble($name);
            return PibbleResponseDto::from($pibble);
        } catch (PibbleNotFoundException $e) {
            throw new HttpNotFoundException($e->getMessage());
        }
    }

    public function postPibble(string $name) {
        try {
            $pibble = $this->pibbleService->createPibble($name);
            return response()->json(
                PibbleResponseDto::from($pibble),
                HttpCode::CREATED->code()
            );
        } catch (PibbleAlreadyExistsException $e) {
            throw new HttpConflictException($e->getMessage());
        }
    }

    public function postBelly(PibbleRequestDto $data) {
        try {
            $washed = $this->pibbleService->washBelly($data->name);
            $res = BellyWashResponseDto::from([
                "cleanedRealGood" => $washed,
            ]);
            return $res;
        } catch (PibbleNotFoundException $e) {
            throw new HttpNotFoundException($e->getMessage());
        }
    }

}
