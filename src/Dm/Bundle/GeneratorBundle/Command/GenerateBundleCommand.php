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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\HttpKernel\KernelInterface;
use Dm\Bundle\GeneratorBundle\Generator\BundleGenerator;
use Dm\Bundle\GeneratorBundle\Manipulator\KernelManipulator;
use Dm\Bundle\GeneratorBundle\Manipulator\RoutingManipulator;
use Dm\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;

/**
 * 生成 bundles.
 */
class GenerateBundleCommand extends GeneratorCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('namespace', '', InputOption::VALUE_REQUIRED, 'The namespace of the bundle to create'),
                new InputOption('dir', '', InputOption::VALUE_REQUIRED, 'The directory where to create the bundle'),
                new InputOption('bundle-name', '', InputOption::VALUE_REQUIRED, 'The optional bundle name'),
                new InputOption('format', '', InputOption::VALUE_REQUIRED, 'Use the format for configuration files (php, xml, yml, or annotation)'),
                new InputOption('structure', '', InputOption::VALUE_NONE, 'Whether to generate the whole directory structure'),
            ))
            ->setDescription('生成一个Bundle')
            ->setHelp(<<<EOT
<info>dm:generate:bundle</info> 命令用来生成一个新的Bundle.

默认情况下,该命令通过与开发人员交互来对Bundle的生成过程进行调整.  在交互过程中所有传入的参数将作为一个默认值被使用.(如果您遵循格式
约定，<comment>--namespace</comment> 参数将是唯一一个必须的参数):

<info>php app/console generate:bundle --namespace=Acme/BlogBundle</info>

你可以使用 <comment>/</comment> 代替 <comment>\\ </comment>作为命名空间的分隔符来避免一些问题.

如果你想直接生产Bundle而不需要任何交互, 使用参数 <comment>--no-interaction</comment>, 但是不要忘记传入必要的参数:

<info>php app/console generate:bundle --namespace=Acme/BlogBundle --dir=src [--bundle-name=...] --no-interaction</info>

注意,Bundle的命名空间必须以"Bundle"结尾.
EOT
            )
            ->setName('dm:generate:bundle')
        ;
    }

    /**
     * @see Command
     *
     * @throws \InvalidArgumentException When namespace doesn't end with Bundle
     * @throws \RuntimeException         When bundle can't be executed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        if ($input->isInteractive()) {
            if (!$questionHelper->ask($input, $output, new ConfirmationQuestion($questionHelper->getQuestion('你确定要生成吗', 'yes', '?'), true))) {
                $output->writeln('<error>命令中断</error>');

                return 1;
            }
        }

        foreach (array('namespace', 'dir') as $option) {
            if (null === $input->getOption($option)) {
                throw new \RuntimeException(sprintf('"%s" 参数必须提供.', $option));
            }
        }

        // validate the namespace, but don't require a vendor namespace
        $namespace = Validators::validateBundleNamespace($input->getOption('namespace'), false);
        if (!$bundle = $input->getOption('bundle-name')) {
            $bundle = strtr($namespace, array('\\' => ''));
        }
        $bundle = Validators::validateBundleName($bundle);
        $dir = Validators::validateTargetDir($input->getOption('dir'), $bundle, $namespace);
        if (null === $input->getOption('format')) {
            $input->setOption('format', 'annotation');
        }
        $format = Validators::validateFormat($input->getOption('format'));
        $structure = $input->getOption('structure');

        $questionHelper->writeSection($output, 'Bundle 生成');

        if (!$this->getContainer()->get('filesystem')->isAbsolutePath($dir)) {
            $dir = getcwd().'/'.$dir;
        }

        $generator = $this->getGenerator();
        $generator->generate($namespace, $bundle, $dir, $format, $structure);

        $output->writeln('生成Bundle代码: <info>OK</info>');

        $errors = array();
        $runner = $questionHelper->getRunner($output, $errors);

        // check that the namespace is already autoloaded
        $runner($this->checkAutoloader($output, $namespace, $bundle, $dir));

        // register the bundle in the Kernel class
        $runner($this->updateKernel($questionHelper, $input, $output, $this->getContainer()->get('kernel'), $namespace, $bundle));

        // routing
        $runner($this->updateRouting($questionHelper, $input, $output, $bundle, $format));

        $questionHelper->writeGeneratorSummary($output, $errors);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, '欢迎使用 Symfony2 Bundle 生成器');

        // namespace
        $namespace = null;
        try {
            // validate the namespace option (if any) but don't require the vendor namespace
            $namespace = $input->getOption('namespace') ? Validators::validateBundleNamespace($input->getOption('namespace'), false) : null;
        } catch (\Exception $error) {
            $output->writeln($questionHelper->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $namespace) {
            $output->writeln(array(
                '',
                '你的应用程序必须写在 <comment>bundles</comment> 中. 这条命令将帮助你方便的生成一个 Bundle',
                '',
                '每个Bundle都位于一个特定的命名空间下 (例如: <comment>Acme/Bundle/BlogBundle</comment>).',
                '命名空间应该以一个自定义的名称开始, 比如你的公司名称, 你的项目名称, 随后是一个或多个目录名称.',
                '命名空间必须以 <comment>Bundle</comment> 结尾.',
                '',
                '查看更多命名空间名称的约定信息: http://symfony.com/doc/current/cookbook/bundles/best_practices.html#index-1',
                '',
                '使用 <comment>/</comment> 代替 <comment>\\ </comment> 作为命名空间的分隔符来避免一些常见的问题.',
                '',
            ));

            $acceptedNamespace = false;
            while (!$acceptedNamespace) {
                $question = new Question($questionHelper->getQuestion('Bundle 命名空间', $input->getOption('namespace')), $input->getOption('namespace'));
                $question->setValidator(function ($answer) {
                    return Validators::validateBundleNamespace($answer, false);
                });
                $namespace = $questionHelper->ask($input, $output, $question);

                // mark as accepted, unless they want to try again below
                $acceptedNamespace = true;

                // see if there is a vendor namespace. If not, this could be accidental
                if (false === strpos($namespace, '\\')) {
                    // language is (almost) duplicated in Validators
                    $msg = array();
                    $msg[] = '';
                    $msg[] = sprintf('命名空间必须包含一个自定义名称 (e.g. <info>VendorName/BlogBundle</info> 而不是简单的 <info>%s</info>).', $namespace, $namespace);
                    $msg[] = '如果你在输入一个自定义名称,尝试使用一个正斜杠 <info>/</info> (<info>Acme/BlogBundle</info>)?';
                    $msg[] = '';
                    $output->writeln($msg);

                    $question = new ConfirmationQuestion($questionHelper->getQuestion(
                        sprintf('继续使用 <comment>%s</comment> 作为一个Bundle的命名空间 (选择 no 重新输入)?', $namespace),
                        'yes'
                    ), true);
                    $acceptedNamespace = $questionHelper->ask($input, $output, $question);
                }
            }
            $input->setOption('namespace', $namespace);
        }

        // bundle name
        $bundle = null;
        try {
            $bundle = $input->getOption('bundle-name') ? Validators::validateBundleName($input->getOption('bundle-name')) : null;
        } catch (\Exception $error) {
            $output->writeln($questionHelper->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $bundle) {
            $bundle = strtr($namespace, array('\\Bundle\\' => '', '\\' => ''));

            $output->writeln(array(
                '',
                '在代码中,一个Bundle经常通过他的名称被到处引用',
                '我们建议使用 <comment>'.$bundle.'</comment> 作为Bundle名称.',
                '',
            ));
            $question = new Question($questionHelper->getQuestion('Bundle 名称', $bundle), $bundle);
            $question->setValidator(
                 array('Dm\Bundle\GeneratorBundle\Command\Validators', 'validateBundleName')
            );
            $bundle = $questionHelper->ask($input, $output, $question);
            $input->setOption('bundle-name', $bundle);
        }

        // target dir
        $dir = null;
        try {
            $dir = $input->getOption('dir') ? Validators::validateTargetDir($input->getOption('dir'), $bundle, $namespace) : null;
        } catch (\Exception $error) {
            $output->writeln($questionHelper->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $dir) {
            $dir = dirname($this->getContainer()->getParameter('kernel.root_dir')).'/src';

            $output->writeln(array(
                '',
                'Bundle可以被生成到任何目录中, 但是根据约定, 我们建议使用默认的目录',
                '',
            ));
            $question = new Question($questionHelper->getQuestion('目标目录', $dir), $dir);
            $question->setValidator(function ($dir) use ($bundle, $namespace) {
                    return Validators::validateTargetDir($dir, $bundle, $namespace);
            });
            $dir = $questionHelper->ask($input, $output, $question);
            $input->setOption('dir', $dir);
        }

        // format
        $format = null;
        try {
            $format = $input->getOption('format') ? Validators::validateFormat($input->getOption('format')) : null;
        } catch (\Exception $error) {
            $output->writeln($questionHelper->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $format) {
            $output->writeln(array(
                '',
                '确定 Bundle 使用的配置格式.',
                '',
            ));
            $question = new Question($questionHelper->getQuestion('配置格式 (yml, xml, php, or annotation)', $input->getOption('format')), $input->getOption('format'));
            $question->setValidator(
                array('Dm\Bundle\GeneratorBundle\Command\Validators', 'validateFormat')
            );
            $format = $questionHelper->ask($input, $output, $question);
            $input->setOption('format', $format);
        }

        // optional files to generate
        $output->writeln(array(
            '',
            '为了更方便, 生成器将在APPKernel.php, routing.yml中帮你生成一些代码块儿',
            '',
        ));

        $structure = $input->getOption('structure');
        $question = new ConfirmationQuestion($questionHelper->getQuestion('确定生成一个完整的Bundle目录结构吗', 'no', '?'), false);
        if (!$structure && $questionHelper->ask($input, $output, $question)) {
            $structure = true;
        }
        $input->setOption('structure', $structure);

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('生成汇总', 'bg=blue;fg=white', true),
            '',
            sprintf("你正在生成了一个 \"<info>%s\\%s</info>\" Bundle\n位于 \"<info>%s</info>\" 目录中, 使用 \"<info>%s</info>\" 格式.", $namespace, $bundle, $dir, $format),
            '',
        ));
    }

    protected function checkAutoloader(OutputInterface $output, $namespace, $bundle, $dir)
    {
        $output->write('检查Bundle是否已经被自动加载: ');
        if (!class_exists($namespace.'\\'.$bundle)) {
            return array(
                '- Edit the <comment>composer.json</comment> file and register the bundle',
                '  namespace in the "autoload" section:',
                '',
            );
        }
    }

    protected function updateKernel(QuestionHelper $questionHelper, InputInterface $input, OutputInterface $output, KernelInterface $kernel, $namespace, $bundle)
    {
        $auto = true;
        if ($input->isInteractive()) {
            $question = new ConfirmationQuestion($questionHelper->getQuestion('是否自动更新你的AppKernel', 'yes', '?'), true);
            $auto = $questionHelper->ask($input, $output, $question);
        }

        $output->write('在AppKernel中注册Bundle: ');
        $manip = new KernelManipulator($kernel);
        try {
            $ret = $auto ? $manip->addBundle($namespace.'\\'.$bundle) : false;

            if (!$ret) {
                $reflected = new \ReflectionObject($kernel);

                return array(
                    sprintf('- 编辑 <comment>%s</comment>', $reflected->getFilename()),
                    '  添加Bundle到 <comment>AppKernel::registerBundles()</comment> 方法中:',
                    '',
                    sprintf('    <comment>new %s(),</comment>', $namespace.'\\'.$bundle),
                    '',
                );
            }
        } catch (\RuntimeException $e) {
            return array(
                sprintf('Bundle <comment>%s</comment> 在 <comment>AppKernel::registerBundles()</comment> 中已经被定义.', $namespace.'\\'.$bundle),
                '',
            );
        }
    }

    protected function updateRouting(QuestionHelper $questionHelper, InputInterface $input, OutputInterface $output, $bundle, $format)
    {
        $auto = true;
        if ($input->isInteractive()) {
            $question = new ConfirmationQuestion($questionHelper->getQuestion('是否自动更新路由配置文件 routing.yml', 'yes', '?'), true);
            $auto = $questionHelper->ask($input, $output, $question);
        }

        $output->write('引入Bundle的路由配置文件: ');
        $routing = new RoutingManipulator($this->getContainer()->getParameter('kernel.root_dir').'/config/routing.yml');
        try {
            $ret = $auto ? $routing->addResource($bundle, $format) : false;

            if (!$ret) {
                if ('annotation' === $format) {
                    $help = sprintf("        <comment>resource: \"@%s/Controller/\"</comment>\n        <comment>type:     annotation</comment>\n", $bundle);
                } else {
                    $help = sprintf("        <comment>resource: \"@%s/Resources/config/routing.%s\"</comment>\n", $bundle, $format);
                }
                $help .= "        <comment>prefix:   /</comment>\n";

                return array(
                    '- 引入Bundle 路由配置到主App路由配置文件中:',
                    '',
                    sprintf('    <comment>%s:</comment>', $bundle),
                    $help,
                    '',
                );
            }
        } catch (\RuntimeException $e) {
            return array(
                sprintf('Bundle <comment>%s</comment> 配置已经被引入.', $bundle),
                '',
            );
        }
    }

    protected function createGenerator()
    {
        return new BundleGenerator($this->getContainer()->get('filesystem'));
    }
}
