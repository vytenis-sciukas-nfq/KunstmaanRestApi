<?php

namespace Kunstmaan\Rest\CoreBundle\Helper;

use Kunstmaan\Rest\CoreBundle\Service\DataTransformerService;
use Ramsey\Uuid\Uuid;

trait  GenerateApiKeyFunctionTrait
{
    public function generateApiKey() : string
    {
        return Uuid::uuid4();
    }
}
