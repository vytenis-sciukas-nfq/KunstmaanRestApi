<?php

declare(strict_types=1);

namespace Kunstmaan\Rest\CoreBundle\Serializer\JMS\Expression;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class ArrayFunctionsProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        return [
            new ExpressionFunction(
                'array_values',
                function ($arg) {
                    return sprintf('array_values(%s)', $arg);
                }, function (array $variables, $value) {
                    return array_values($value);
                }
            )
        ];
    }
}
