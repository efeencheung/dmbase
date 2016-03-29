<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\GeneratorBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Dm\Bundle\GeneratorBundle\Generator\DoctrineFormGenerator;

/**
 * 根据一个Entity 生成一个Form 表单
 */
class GenerateDoctrineFormCommand extends GenerateDoctrineCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('entity', InputArgument::REQUIRED, 'Entity 类名的简称'),
            ))
            ->setDescription('基于一个Entity生成一个Form Type类')
            ->setHelp(<<<EOT
The <info>doctrine:generate:form</info> command generates a form class based on a Doctrine entity.

<info>php app/console doctrine:generate:form AcmeBlogBundle:Post</info>

Every generated file is based on a template. There are default templates but they can be overriden by placing custom templates in one of the following locations, by order of priority:

<info>BUNDLE_PATH/Resources/SensioGeneratorBundle/skeleton/form
APP_PATH/Resources/SensioGeneratorBundle/skeleton/form</info>

You can check https://github.com/sensio/SensioGeneratorBundle/tree/master/Resources/skeleton
in order to know the file structure of the skeleton
EOT
            )
            ->setName('dm:doctrine:generate:form')
            ->setAliases(array('dm:generate:doctrine:form'))
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entity = Validators::validateEntityName($input->getArgument('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle).'\\'.$entity;
        $metadata = $this->getEntityMetadata($entityClass);
        $bundle   = $this->getApplication()->getKernel()->getBundle($bundle);
        $generator = $this->getGenerator($bundle);

        $generator->generate($bundle, $entity, $metadata[0]);

        $output->writeln(sprintf(
            '在 %s 目录中生成了一个新的 %s.php 类文件.',
            $generator->getClassName(),
            $generator->getClassPath()
        ));
    }

    protected function createGenerator()
    {
        return new DoctrineFormGenerator($this->getContainer()->get('filesystem'));
    }
}
