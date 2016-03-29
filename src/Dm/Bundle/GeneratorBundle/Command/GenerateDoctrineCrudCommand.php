<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\GeneratorBundle\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Dm\Bundle\GeneratorBundle\Command\AutoComplete\EntitiesAutoCompleter;
use Dm\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Dm\Bundle\GeneratorBundle\Generator\DoctrineCrudGenerator;
use Dm\Bundle\GeneratorBundle\Generator\DoctrineFormGenerator;
use Dm\Bundle\GeneratorBundle\Manipulator\RoutingManipulator;

/**
 * Generates a CRUD for a Doctrine entity.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class GenerateDoctrineCrudCommand extends GenerateDoctrineCommand
{
    private $formGenerator;

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('entity', InputArgument::OPTIONAL, 'The entity class name to initialize (shortcut notation)'),
                new InputOption('entity', '', InputOption::VALUE_REQUIRED, 'The entity class name to initialize (shortcut notation)'),
                new InputOption('chinese-name', '', InputOption::VALUE_REQUIRED, 'Entity 中文名称'),
                new InputOption('route-prefix', '', InputOption::VALUE_REQUIRED, 'The route prefix'),
                new InputOption('with-write', '', InputOption::VALUE_NONE, 'Whether or not to generate create, new and delete actions'),
                new InputOption('format', '', InputOption::VALUE_REQUIRED, 'Use the format for configuration files (php, xml, yml, or annotation)', 'annotation'),
                new InputOption('overwrite', '', InputOption::VALUE_NONE, 'Do not stop the generation if crud controller already exist, thus overwriting all generated files'),
            ))
            ->setDescription('Generates a CRUD based on a Doctrine entity')
            ->setHelp(<<<EOT
The <info>doctrine:generate:crud</info> command generates a CRUD based on a Doctrine entity.

The default command only generates the list and show actions.

<info>php app/console doctrine:generate:crud --entity=AcmeBlogBundle:Post --route-prefix=post_admin</info>

Using the --with-write option allows to generate the new, edit and delete actions.

<info>php app/console doctrine:generate:crud --entity=AcmeBlogBundle:Post --route-prefix=post_admin --with-write</info>

Every generated file is based on a template. There are default templates but they can be overridden by placing custom templates in one of the following locations, by order of priority:

<info>BUNDLE_PATH/Resources/SensioGeneratorBundle/skeleton/crud
APP_PATH/Resources/SensioGeneratorBundle/skeleton/crud</info>

And

<info>__bundle_path__/Resources/SensioGeneratorBundle/skeleton/form
__project_root__/app/Resources/SensioGeneratorBundle/skeleton/form</info>

You can check https://github.com/sensio/SensioGeneratorBundle/tree/master/Resources/skeleton
in order to know the file structure of the skeleton
EOT
            )
            ->setName('dm:doctrine:generate:crud')
            ->setAliases(array('dm:generate:doctrine:crud'))
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        if ($input->isInteractive()) {
            $question = new ConfirmationQuestion($questionHelper->getQuestion('确定生成吗', 'yes', '?'), true);
            if (!$questionHelper->ask($input, $output, $question)) {
                $output->writeln('<error>命令中断</error>');

                return 1;
            }
        }

        $entity = Validators::validateEntityName($input->getOption('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $format = Validators::validateFormat($input->getOption('format'));
        $prefix = $this->getRoutePrefix($input, $entity);
        $withWrite = $input->getOption('with-write');
        $forceOverwrite = $input->getOption('overwrite');
        $entityChineseName = $input->getOption('chinese-name');

        $questionHelper->writeSection($output, 'CRUD 生成');

        $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle).'\\'.$entity;
        $metadata    = $this->getEntityMetadata($entityClass);
        $bundle      = $this->getContainer()->get('kernel')->getBundle($bundle);

        $generator = $this->getGenerator($bundle);
        $generator->generate($bundle, $entity, $metadata[0], $format, $prefix, $withWrite, $forceOverwrite, $entityChineseName);

        $output->writeln('CRUD代码生成: <info>OK</info>');

        $errors = array();
        $runner = $questionHelper->getRunner($output, $errors);

        // form
        if ($withWrite) {
            $output->write('Form代码生成: ');
            if ($this->generateForm($bundle, $entity, $metadata)) {
                $output->writeln('<info>OK</info>');
            } else {
                $output->writeln('<comment>已存在, 跳过</comment>');
            }
        }

        // routing
        if ('annotation' != $format) {
            $runner($this->updateRouting($questionHelper, $input, $output, $bundle, $format, $entity, $prefix));
        }

        $questionHelper->writeGeneratorSummary($output, $errors);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, '欢迎使用Doctrine CRUD生成器');

        // namespace
        $output->writeln(array(
            '',
            '该命令用来生成CRUD操作的控制器和模板.',
            '',
            '首先需要提供一个Entity, 也可以提供一个不存在的Entity, 生成器会在生成过程交互中帮你定义.',
            '',
            '必须使用一个类似这样的Entity简称 <comment>AcmeBlogBundle:Post</comment>.',
            '',
        ));

        if ($input->hasArgument('entity') && $input->getArgument('entity') != '') {
            $input->setOption('entity', $input->getArgument('entity'));
        }

        $question = new Question($questionHelper->getQuestion('Entity简称', $input->getOption('entity')), $input->getOption('entity'));
        $question->setValidator(array('Dm\Bundle\GeneratorBundle\Command\Validators', 'validateEntityName'));

        $autocompleter = new EntitiesAutoCompleter($this->getContainer()->get('doctrine')->getManager());
        $autocompleteEntities = $autocompleter->getSuggestions();
        $question->setAutocompleterValues($autocompleteEntities);
        $entity = $questionHelper->ask($input, $output, $question);

        $input->setOption('entity', $entity);
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $question = new Question($questionHelper->getQuestion('Entity中文名称', null), null);
        $question->setValidator(array('Dm\Bundle\GeneratorBundle\Command\Validators', 'validateChineseName'));
        $entityChineseName = $questionHelper->ask($input, $output, $question);
        $input->setOption('chinese-name', $entityChineseName);

        // write?
        $withWrite = $input->getOption('with-write') ?: false;
        $output->writeln(array(
            '',
            '默认情况下, 生成器创建两个Action: listAction和showAction.',
            '你可以选择yes来生成: newAction, updateAction, deleteAction.',
            '',
        ));
        $question = new ConfirmationQuestion($questionHelper->getQuestion('确定生成可写的操作吗', $withWrite ? 'yes' : 'no', '?', $withWrite), $withWrite);

        $withWrite = $questionHelper->ask($input, $output, $question);
        $input->setOption('with-write', $withWrite);

        // format
        $format = $input->getOption('format');
        $output->writeln(array(
            '',
            '确定CRUD代码使用的配置格式.',
            '',
        ));
        $question = new Question($questionHelper->getQuestion('配置格式 (yml, xml, php, or annotation)', $format), $format);
        $question->setValidator(array('Dm\Bundle\GeneratorBundle\Command\Validators', 'validateFormat'));
        $format = $questionHelper->ask($input, $output, $question);
        $input->setOption('format', $format);

        // route prefix
        $prefix = $this->getRoutePrefix($input, $entity);
        $output->writeln(array(
            '',
            '输入路由前缀, 如果输入, Controller中所有Action对应的路由将像下面这样添加前缀',
            'prefix: /prefix/, /prefix/new, ...).',
            '',
        ));
        $prefix = $questionHelper->ask($input, $output, new Question($questionHelper->getQuestion('路由前缀', '/'.$prefix), '/'.$prefix));
        $input->setOption('route-prefix', $prefix);

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('生产前汇总', 'bg=blue;fg=white', true),
            '',
            sprintf("即将为 \"<info>%s:%s</info>\" 生成一个CRUD控制器", $bundle, $entity),
            sprintf("使用 \"<info>%s</info>\" 格式.", $format),
            '',
        ));
    }

    /**
     * Tries to generate forms if they don't exist yet and if we need write operations on entities.
     */
    protected function generateForm($bundle, $entity, $metadata)
    {
        try {
            $this->getFormGenerator($bundle)->generate($bundle, $entity, $metadata[0]);
        } catch (\RuntimeException $e) {
            return false;
        }

        return true;
    }

    protected function updateRouting(QuestionHelper $questionHelper, InputInterface $input, OutputInterface $output, BundleInterface $bundle, $format, $entity, $prefix)
    {
        $auto = true;
        if ($input->isInteractive()) {
            $question = new ConfirmationQuestion($questionHelper->getQuestion('确定是否自动更新路由', 'yes', '?'), true);
            $auto = $questionHelper->ask($input, $output, $question);
        }

        $output->write('导入CRUD路由: ');
        $this->getContainer()->get('filesystem')->mkdir($bundle->getPath().'/Resources/config/');
        $routing = new RoutingManipulator($bundle->getPath().'/Resources/config/routing.yml');
        try {
            $ret = $auto ? $routing->addResource($bundle->getName(), $format, '/'.$prefix, 'routing/'.strtolower(str_replace('\\', '_', $entity))) : false;
        } catch (\RuntimeException $exc) {
            $ret = false;
        }

        if (!$ret) {
            $help = sprintf("        <comment>resource: \"@%s/Resources/config/routing/%s.%s\"</comment>\n", $bundle->getName(), strtolower(str_replace('\\', '_', $entity)), $format);
            $help .= sprintf("        <comment>prefix:   /%s</comment>\n", $prefix);

            return array(
                '- 在Bundle的路由配置文件中导入新的路由配置',
                sprintf('  (%s).', $bundle->getPath().'/Resources/config/routing.yml'),
                '',
                sprintf('    <comment>%s:</comment>', $bundle->getName().('' !== $prefix ? '_'.str_replace('/', '_', $prefix) : '')),
                $help,
                '',
            );
        }
    }

    protected function getRoutePrefix(InputInterface $input, $entity)
    {
        $prefix = $input->getOption('route-prefix') ?: strtolower(str_replace(array('\\', '/'), '_', $entity));

        if ($prefix && '/' === $prefix[0]) {
            $prefix = substr($prefix, 1);
        }

        return $prefix;
    }

    protected function createGenerator($bundle = null)
    {
        return new DoctrineCrudGenerator($this->getContainer()->get('filesystem'));
    }

    protected function getFormGenerator($bundle = null)
    {
        if (null === $this->formGenerator) {
            $this->formGenerator = new DoctrineFormGenerator($this->getContainer()->get('filesystem'));
            $this->formGenerator->setSkeletonDirs($this->getSkeletonDirs($bundle));
        }

        return $this->formGenerator;
    }

    public function setFormGenerator(DoctrineFormGenerator $formGenerator)
    {
        $this->formGenerator = $formGenerator;
    }
}
