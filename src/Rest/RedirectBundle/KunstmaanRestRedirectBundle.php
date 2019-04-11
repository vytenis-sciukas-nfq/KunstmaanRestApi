<?php

namespace Kunstmaan\Rest\RedirectBundle;

use Kunstmaan\Rest\RedirectBundle\DependencyInjection\Compiler\NelmioDefinitionsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KunstmaanRestRedirectBundle extends Bundle
{
    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new NelmioDefinitionsCompilerPass());
    }
}
