<?php

namespace Kunstmaan\Rest\ArticleBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * Class NelmioDefinitionsCompilerPass
 */
class NelmioDefinitionsCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('nelmio_api_doc.describers.config');
        $config = $definition->getArgument(0);

        $locator = new FileLocator(__DIR__.'/../../Resources/config/');
        $bundleConfig = Yaml::parseFile($locator->locate('swagger_conf.yml'));

        if(!isset($config['components'])) {
           $config['components'] = [];
        }
        $config['components']['schemas'] = \array_merge(
            isset($config['components']['schemas']) ? $config['components']['schemas'] : [],
            $bundleConfig['nelmio_api_doc']['documentation']['components']['schemas']
        );

        $definition->replaceArgument(0, $config);
    }
}
