<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\GeneratorBundle\Command;

use Dm\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator;
use Dm\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Doctrine\DBAL\Types\Type;

/**
 * 在Bundle中初始化一个Doctrine entity.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class GenerateDoctrineEntityCommand extends GenerateDoctrineCommand
{
    protected function configure()
    {
        $this
            ->setName('dm:doctrine:generate:entity')
            ->setAliases(array('dm:generate:doctrine:entity'))
            ->setDescription('Generates a new Doctrine entity inside a bundle')
            ->addOption('entity', null, InputOption::VALUE_REQUIRED, 'The entity class name to initialize (shortcut notation)')
            ->addOption('fields', null, InputOption::VALUE_REQUIRED, 'The fields to create with the new entity')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Use the format for configuration files (php, xml, yml, or annotation)', 'annotation')
            ->addOption('with-repository', null, InputOption::VALUE_NONE, 'Whether to generate the entity repository or not')
            ->setHelp(<<<EOT
The <info>doctrine:generate:entity</info> task generates a new Doctrine
entity inside a bundle:

<info>php app/console doctrine:generate:entity --entity=AcmeBlogBundle:Blog/Post</info>

The above command would initialize a new entity in the following entity
namespace <info>Acme\BlogBundle\Entity\Blog\Post</info>.

You can also optionally specify the fields you want to generate in the new
entity:

<info>php app/console doctrine:generate:entity --entity=AcmeBlogBundle:Blog/Post --fields="title:string(255) body:text"</info>

The command can also generate the corresponding entity repository class with the
<comment>--with-repository</comment> option:

<info>php app/console doctrine:generate:entity --entity=AcmeBlogBundle:Blog/Post --with-repository</info>

By default, the command uses annotations for the mapping information; change it
with <comment>--format</comment>:

<info>php app/console doctrine:generate:entity --entity=AcmeBlogBundle:Blog/Post --format=yml</info>

To deactivate the interaction mode, simply use the `--no-interaction` option
without forgetting to pass all needed options:

<info>php app/console doctrine:generate:entity --entity=AcmeBlogBundle:Blog/Post --format=annotation --fields="title:string(255) body:text" --with-repository --no-interaction</info>
EOT
        );
    }

    /**
     * @throws \InvalidArgumentException When the bundle doesn't end with Bundle (Example: "Bundle/MySampleBundle")
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        if ($input->isInteractive()) {
            $question = new ConfirmationQuestion($questionHelper->getQuestion('确认生成吗', 'yes', '?'), true);
            if (!$questionHelper->ask($input, $output, $question)) {
                $output->writeln('<error>命令中断</error>');

                return 1;
            }
        }

        $entity = Validators::validateEntityName($input->getOption('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);
        $format = Validators::validateFormat($input->getOption('format'));
        $fields = $this->parseFields($input->getOption('fields'));

        $questionHelper->writeSection($output, 'Entity 生成');

        $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);

        $generator = $this->getGenerator();
        $generator->generate($bundle, $entity, $format, array_values($fields), $input->getOption('with-repository'));

        $output->writeln('生成Entity代码: <info>OK</info>');

        $questionHelper->writeGeneratorSummary($output, array());
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, '欢迎使用Doctrine2 Entity生成器');

        // namespace
        $output->writeln(array(
            '',
            '这个命令将帮助你生成一个Doctrine Entity.',
            '',
            '首先, 需要提供一个即将生成的Entity的名称.',
            '必须使用一个Entity的简称, 例如: <comment>AcmeBlogBundle:Post</comment>.',
            '',
        ));

        $bundleNames = array_keys($this->getContainer()->get('kernel')->getBundles());

        while (true) {
            $question = new Question($questionHelper->getQuestion('Entity简称', $input->getOption('entity')), $input->getOption('entity'));
            $question->setValidator(array('Dm\Bundle\GeneratorBundle\Command\Validators', 'validateEntityName'));
            $question->setAutocompleterValues($bundleNames);
            $entity = $questionHelper->ask($input, $output, $question);

            list($bundle, $entity) = $this->parseShortcutNotation($entity);

            // check reserved words
            if ($this->getGenerator()->isReservedKeyword($entity)) {
                $output->writeln(sprintf('<bg=red> "%s" 是一个保留词</>.', $entity));
                continue;
            }

            try {
                $b = $this->getContainer()->get('kernel')->getBundle($bundle);

                if (!file_exists($b->getPath().'/Entity/'.str_replace('\\', '/', $entity).'.php')) {
                    break;
                }

                $output->writeln(sprintf('<bg=red>Entity "%s:%s" 已存在</>.', $bundle, $entity));
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Bundle "%s" 不存在.</>', $bundle));
            }
        }
        $input->setOption('entity', $bundle.':'.$entity);

        // format
        $output->writeln(array(
            '',
            '确定Entity映射配置使用的格式.',
            '',
        ));

        $formats = array('yml', 'xml', 'php', 'annotation');

        $question = new Question($questionHelper->getQuestion('映射配置格式 (yml, xml, php, or annotation)', $input->getOption('format')), $input->getOption('format'));
        $question->setValidator(array('Dm\Bundle\GeneratorBundle\Command\Validators', 'validateFormat'));
        $question->setAutocompleterValues($formats);
        $format = $questionHelper->ask($input, $output, $question);
        $input->setOption('format', $format);

        // chinese name for comment and view
        // $question = new Question($questionHelper->getQuestion('Entity中文名称', null), null);
        // $chineseName = $questionHelper->ask($input, $output, $question);
        // $input->setOption('chinese-name', $chineseName);

        // fields
        $input->setOption('fields', $this->addFields($input, $output, $questionHelper));

        // repository?
        $output->writeln('');
        $question = new ConfirmationQuestion($questionHelper->getQuestion('你确定生成一个空的Repository类吗', $input->getOption('with-repository') ? 'yes' : 'no', '?'), $input->getOption('with-repository'));
        $withRepository = $questionHelper->ask($input, $output, $question);
        $input->setOption('with-repository', $withRepository);

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('生成汇总', 'bg=blue;fg=white', true),
            '',
            sprintf("你将要生成一个 \"<info>%s:%s</info>\" Doctrine2 Entity", $bundle, $entity),
            sprintf("使用 \"<info>%s</info>\" 配置格式.", $format),
            '',
        ));
    }

    private function parseFields($input)
    {
        if (is_array($input)) {
            return $input;
        }

        $fields = array();
        foreach (explode(' ', $input) as $value) {
            $elements = explode(':', $value);
            $name = $elements[0];
            if (strlen($name)) {
                $type = isset($elements[1]) ? $elements[1] : 'string';
                preg_match_all('/(.*)\((.*)\)/', $type, $matches);
                $type = isset($matches[1][0]) ? $matches[1][0] : $type;
                $length = isset($matches[2][0]) ? $matches[2][0] : null;

                $fields[$name] = array('fieldName' => $name, 'type' => $type, 'length' => $length);
            }
        }

        return $fields;
    }

    private function addFields(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $fields = $this->parseFields($input->getOption('fields'));
        $output->writeln(array(
            '',
            '你可以为一个空的Entity添加一些字段.',
            '主键已经自动添加了 (名称为 <comment>id</comment>).',
            '',
        ));
        $output->write('<info>可用的字段类型:</info> ');

        $types = array_keys(Type::getTypesMap());
        $count = 20;
        foreach ($types as $i => $type) {
            if ($count > 50) {
                $count = 0;
                $output->writeln('');
            }
            $count += strlen($type);
            $output->write(sprintf('<comment>%s</comment>', $type));
            if (count($types) != $i + 1) {
                $output->write(', ');
            } else {
                $output->write('.');
            }
        }
        $output->writeln('');

        $fieldValidator = function ($type) use ($types) {
            // FIXME: take into account user-defined field types
            if (!in_array($type, $types)) {
                throw new \InvalidArgumentException(sprintf('非法的类型 "%s".', $type));
            }

            return $type;
        };

        $lengthValidator = function ($length) {
            if (!$length) {
                return $length;
            }

            $result = filter_var($length, FILTER_VALIDATE_INT, array(
                'options' => array('min_range' => 1),
            ));

            if (false === $result) {
                throw new \InvalidArgumentException(sprintf('非法的长度 "%s".', $length));
            }

            return $length;
        };

        while (true) {
            $output->writeln('');
            $generator = $this->getGenerator();
            $question = new Question($questionHelper->getQuestion('新字段名(按回车停止添加字段)', null), null);
            $question->setValidator(function ($name) use ($fields, $generator) {
                if (isset($fields[$name]) || 'id' == $name) {
                    throw new \InvalidArgumentException(sprintf('字段 "%s" 已定义.', $name));
                }

                // check reserved words
                if ($generator->isReservedKeyword($name)) {
                    throw new \InvalidArgumentException(sprintf('字段名 "%s" 是一个保留词.', $name));
                }

                return $name;
            });

            $columnName = $questionHelper->ask($input, $output, $question);
            if (!$columnName) {
                break;
            }

            $defaultType = 'string';

            // try to guess the type by the column name prefix/suffix
            if (substr($columnName, -3) == '_at') {
                $defaultType = 'datetime';
            } elseif (substr($columnName, -3) == '_id') {
                $defaultType = 'integer';
            } elseif (substr($columnName, 0, 3) == 'is_') {
                $defaultType = 'boolean';
            } elseif (substr($columnName, 0, 4) == 'has_') {
                $defaultType = 'boolean';
            }

            $question = new Question($questionHelper->getQuestion('字段类型', $defaultType), $defaultType);
            $question->setValidator($fieldValidator);
            $question->setAutocompleterValues($types);
            $type = $questionHelper->ask($input, $output, $question);

            $data = array('columnName' => $columnName, 'fieldName' => lcfirst(Container::camelize($columnName)), 'type' => $type);

            if ($type == 'string') {
                $question = new Question($questionHelper->getQuestion('字段长度', 255), 255);
                $question->setValidator($lengthValidator);
                $data['length'] = $questionHelper->ask($input, $output, $question);
            }

            // add chinese name for comment and view
            $question = new Question($questionHelper->getQuestion('字段中文名称', null), null);
            $data['chineseName'] = $questionHelper->ask($input, $output, $question);

            $fields[$columnName] = $data;
        }

        return $fields;
    }

    protected function createGenerator()
    {
        return new DoctrineEntityGenerator($this->getContainer()->get('filesystem'), $this->getContainer()->get('doctrine'));
    }
}
