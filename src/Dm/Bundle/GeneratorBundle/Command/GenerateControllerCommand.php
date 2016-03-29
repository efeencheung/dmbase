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
use Dm\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Dm\Bundle\GeneratorBundle\Generator\ControllerGenerator;

/**
 * Generates controllers.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class GenerateControllerCommand extends GeneratorCommand
{
    /**
     * @see Command
     */
    public function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('controller', '', InputOption::VALUE_REQUIRED, 'The name of the controller to create'),
                new InputOption('route-format', '', InputOption::VALUE_REQUIRED, 'The format that is used for the routing (yml, xml, php, annotation)', 'annotation'),
                new InputOption('template-format', '', InputOption::VALUE_REQUIRED, 'The format that is used for templating (twig, php)', 'twig'),
                new InputOption('actions', '', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The actions in the controller'),
            ))
            ->setDescription('Generates a controller')
            ->setHelp(<<<EOT
The <info>generate:controller</info> command helps you generates new controllers
inside bundles.

By default, the command interacts with the developer to tweak the generation.
Any passed option will be used as a default value for the interaction
(<comment>--bundle</comment> and <comment>--controller</comment> are the only
ones needed if you follow the conventions):

<info>php app/console generate:controller --controller=AcmeBlogBundle:Post</info>

If you want to disable any user interaction, use <comment>--no-interaction</comment>
but don't forget to pass all needed options:

<info>php app/console generate:controller --controller=AcmeBlogBundle:Post --no-interaction</info>

Every generated file is based on a template. There are default templates but they can
be overriden by placing custom templates in one of the following locations, by order of priority:

<info>BUNDLE_PATH/Resources/SensioGeneratorBundle/skeleton/controller
APP_PATH/Resources/SensioGeneratorBundle/skeleton/controller</info>

You can check https://github.com/sensio/SensioGeneratorBundle/tree/master/Resources/skeleton
in order to know the file structure of the skeleton
EOT
            )
            ->setName('dm:generate:controller')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        if ($input->isInteractive()) {
            $question = new Question($questionHelper->getQuestion('确定生成吗', 'yes', '?'), true);
            if (!$questionHelper->ask($input, $output, $question)) {
                $output->writeln('<error>命令中断</error>');

                return 1;
            }
        }

        if (null === $input->getOption('controller')) {
            throw new \RuntimeException('controller 选项必须提供.');
        }

        list($bundle, $controller) = $this->parseShortcutNotation($input->getOption('controller'));
        if (is_string($bundle)) {
            $bundle = Validators::validateBundleName($bundle);

            try {
                $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Bundle "%s" 不存在.</>', $bundle));
            }
        }

        $questionHelper->writeSection($output, 'Controller 生成');

        $generator = $this->getGenerator($bundle);
        $generator->generate($bundle, $controller, $input->getOption('route-format'), $input->getOption('template-format'), $this->parseActions($input->getOption('actions')));

        $output->writeln('Bundle代码生成: <info>OK</info>');

        $questionHelper->writeGeneratorSummary($output, array());
    }

    public function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, '欢迎使用Symfony Controller生成器');

        // namespace
        $output->writeln(array(
            '',
            '每个页面, 甚至页面的每个部分,都是由 <comment>controller</comment> 来渲染.',
            '该命令可以方便的生成一个Controller.',
            '',
            '首先, 需要提供即将生成的Controller名称.',
            '必须使用类似这样的简称 <comment>AcmeBlogBundle:Post</comment>',
            '',
        ));

        while (true) {
            $question = new Question($questionHelper->getQuestion('Controller名称', $input->getOption('controller')), $input->getOption('controller'));
            $question->setValidator(array('Dm\Bundle\GeneratorBundle\Command\Validators', 'validateControllerName'));
            $controller = $questionHelper->ask($input, $output, $question);
            list($bundle, $controller) = $this->parseShortcutNotation($controller);

            try {
                $b = $this->getContainer()->get('kernel')->getBundle($bundle);

                if (!file_exists($b->getPath().'/Controller/'.$controller.'Controller.php')) {
                    break;
                }

                $output->writeln(sprintf('<bg=red>Controller "%s:%s" 已存在.</>', $bundle, $controller));
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Bundle "%s" 不存在.</>', $bundle));
            }
        }
        $input->setOption('controller', $bundle.':'.$controller);

        // routing format
        $defaultFormat = (null !== $input->getOption('route-format') ? $input->getOption('route-format') : 'annotation');
        $output->writeln(array(
            '',
            '确定要使用的路由配置格式.',
            '',
        ));
        $question = new Question($questionHelper->getQuestion('路由格式 (php, xml, yml, annotation)', $defaultFormat), $defaultFormat);
        $question->setValidator(array('Dm\Bundle\GeneratorBundle\Command\Validators', 'validateFormat'));
        $routeFormat = $questionHelper->ask($input, $output, $question);
        $input->setOption('route-format', $routeFormat);

        // templating format
        $validateTemplateFormat = function ($format) {
            if (!in_array($format, array('twig', 'php'))) {
                throw new \InvalidArgumentException(sprintf('模板必须使用Twig或PHP, "%s" 无法生成', $format));
            }

            return $format;
        };

        $defaultFormat = (null !== $input->getOption('template-format') ? $input->getOption('template-format') : 'twig');
        $output->writeln(array(
            '',
            '确定模板的格式.',
            '',
        ));
        $question = new Question($questionHelper->getQuestion('模板格式(twig, php)', $defaultFormat), $defaultFormat);
        $question->setValidator($validateTemplateFormat);

        $templateFormat = $questionHelper->ask($input, $output, $question);
        $input->setOption('template-format', $templateFormat);

        // actions
        $input->setOption('actions', $this->addActions($input, $output, $questionHelper));

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('生成信息汇总', 'bg=blue;fg-white', true),
            '',
            sprintf('即将生成一个 "<info>%s:%s</info>" Controller', $bundle, $controller),
            sprintf('路由使用 "<info>%s</info>" 格式, 模板使用 "<info>%s</info>" 格式', $routeFormat, $templateFormat),
        ));
    }

    public function addActions(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $output->writeln(array(
            '',
            '除了以一个空白的Controller开始, 现在可以在其中添加一些Action.',
            'Action是一个与特定路由匹配的, 可执行的PHP函数或方法.',
            'Action名称必须以 <comment>Action</comment>结尾.',
            '',
        ));

        $templateNameValidator = function ($name) {
            if ('default' == $name) {
                return $name;
            }

            if (2 != substr_count($name, ':')) {
                throw new \InvalidArgumentException(sprintf('模板名称 "%s" 必须包含两个:分隔符', $name));
            }

            return $name;
        };

        $actions = $this->parseActions($input->getOption('actions'));

        while (true) {
            // name
            $output->writeln('');
            $question = new Question($questionHelper->getQuestion('Action名称(按回车停止添加Action)', null), null);
            $question->setValidator(function ($name) use ($actions) {
                if (null == $name) {
                    return $name;
                }

                if (isset($actions[$name])) {
                    throw new \InvalidArgumentException(sprintf('Action "%s" 已定义', $name));
                }

                if ('Action' != substr($name, -6)) {
                    throw new \InvalidArgumentException(sprintf('Action名称 "%s" 必须以一个"Action"结尾.', $name));
                }

                return $name;
            });

            $actionName = $questionHelper->ask($input, $output, $question);
            if (!$actionName) {
                break;
            }

            // route
            $question = new Question($questionHelper->getQuestion('Action 路由', '/'.substr($actionName, 0, -6)), '/'.substr($actionName, 0, -6));
            $route = $questionHelper->ask($input, $output, $question);
            $placeholders = $this->getPlaceholdersFromRoute($route);

            // template
            $defaultTemplate = $input->getOption('controller').':'.substr($actionName, 0, -6).'.html.'.$input->getOption('template-format');
            $question = new Question($questionHelper->getQuestion('模板名称 (可选)', $defaultTemplate), 'default');
            $template = $questionHelper->ask($input, $output, $question);

            // adding action
            $actions[$actionName] = array(
                'name'         => $actionName,
                'route'        => $route,
                'placeholders' => $placeholders,
                'template'     => $template,
            );
        }

        return $actions;
    }

    public function parseActions($actions)
    {
        if (is_array($actions)) {
            return $actions;
        }

        $newActions = array();

        foreach (explode(' ', $actions) as $action) {
            $data = explode(':', $action);

            // name
            if (!isset($data[0])) {
                throw new \InvalidArgumentException('Action 必须提供一个名字.');
            }
            $name = array_shift($data);

            // route
            $route = (isset($data[0]) && '' != $data[0]) ? array_shift($data) : '/'.substr($name, 0, -6);
            if ($route) {
                $placeholders = $this->getPlaceholdersFromRoute($route);
            } else {
                $placeholders = array();
            }

            // template
            $template = (0 < count($data) && '' != $data[0]) ? implode(':', $data) : 'default';

            $newActions[$name] = array(
                'name'         => $name,
                'route'        => $route,
                'placeholders' => $placeholders,
                'template'     => $template,
            );
        }

        return $newActions;
    }

    public function getPlaceholdersFromRoute($route)
    {
        preg_match_all('/{(.*?)}/', $route, $placeholders);
        $placeholders = $placeholders[1];

        return $placeholders;
    }

    public function parseShortcutNotation($shortcut)
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(sprintf('Controller简称必须包含一个冒号(:) (而不是"%s", 例如 AcmeBlogBundle:Post)', $entity));
        }

        return array(substr($entity, 0, $pos), substr($entity, $pos + 1));
    }

    protected function createGenerator()
    {
        return new ControllerGenerator($this->getContainer()->get('filesystem'));
    }
}
