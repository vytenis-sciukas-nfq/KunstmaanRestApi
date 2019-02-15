<?php

namespace Kunstmaan\Rest\FormBundle;

use Kunstmaan\Rest\FormBundle\DependencyInjection\Compiler\NelmioDefinitionsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class KunstmaanRestFormBundle
 */
class KunstmaanRestFormBundle extends Bundle
{
    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new NelmioDefinitionsCompilerPass());
    }
}
