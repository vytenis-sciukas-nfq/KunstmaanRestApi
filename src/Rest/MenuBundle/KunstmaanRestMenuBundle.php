<?php

namespace Kunstmaan\Rest\MenuBundle;

use Kunstmaan\Rest\MenuBundle\DependencyInjection\Compiler\NelmioDefinitionsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class KunstmaanRestFormBundle
 */
class KunstmaanRestMenuBundle extends Bundle
{
    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new NelmioDefinitionsCompilerPass());
    }
}
