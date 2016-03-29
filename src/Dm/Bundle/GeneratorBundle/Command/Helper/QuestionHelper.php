<?php

/*
 * 基于MIT开源协议发布
 *
 * (c) Efeen Cheung <261969254@qq.com>
 *
 * 有事请联系QQ:261969254, 微信:efeencheung, Github:efeencheung
 */

namespace Dm\Bundle\GeneratorBundle\Command\Helper;

use Symfony\Component\Console\Helper\QuestionHelper as BaseQuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Generates bundles.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class QuestionHelper extends BaseQuestionHelper
{
    public function writeGeneratorSummary(OutputInterface $output, $errors)
    {
        if (!$errors) {
            $this->writeSection($output, '你现在可以开始使用生成的代码了!');
        } else {
            $this->writeSection($output, array(
                '命令行无法自动配置, 你必须手动做以下更改.',
            ), 'error');

            $output->writeln($errors);
        }
    }

    public function getRunner(OutputInterface $output, &$errors)
    {
        $runner = function ($err) use ($output, &$errors) {
            if ($err) {
                $output->writeln('<fg=red>FAILED</>');
                $errors = array_merge($errors, $err);
            } else {
                $output->writeln('<info>OK</info>');
            }
        };

        return $runner;
    }

    public function writeSection(OutputInterface $output, $text, $style = 'bg=blue;fg=white')
    {
        $output->writeln(array(
            '',
            $this->getHelperSet()->get('formatter')->formatBlock($text, $style, true),
            '',
        ));
    }

    public function getQuestion($question, $default, $sep = ':')
    {
        return $default ? sprintf('<info>%s</info> [<comment>%s</comment>]%s ', $question, $default, $sep) : sprintf('<info>%s</info>%s ', $question, $sep);
    }
}
