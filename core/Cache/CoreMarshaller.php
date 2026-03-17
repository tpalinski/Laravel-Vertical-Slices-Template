<?php

declare(strict_types=1);

namespace Core\Cache;

use Symfony\Component\Cache\Marshaller\MarshallerInterface;

class CoreMarshaller implements MarshallerInterface {

    public function marshall(array $values, ?array &$failed): array
    {
        throw new \Exception('Not implemented');
    }

    public function unmarshall(string $value): mixed
    {
        throw new \Exception('Not implemented');
    }
}
