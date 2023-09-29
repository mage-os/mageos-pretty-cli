#!/usr/bin/env php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Color;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

$application = new Application();

$application->register('test:progress:custom')->setCode(
    function($input, $output) {
        $bar = new ProgressBar(new ConsoleOutput(), 15);

        ProgressBar::setPlaceholderFormatterDefinition('memory', function (ProgressBar $bar) {
            $mem = memory_get_usage();
            $colors = '96';
            return "\033[" . $colors . 'm ' . Helper::formatMemory($mem) . " \033[0m";
        });

        $bar->setFormat("\033[34m %title:-37s% \033[0m\n %current%/%max% %bar% %percent:3s%%\n 🏁  %remaining:-10s% %memory:37s%");
        $bar->setBarCharacter($done = "\033[92m█\033[0m");
        $bar->setEmptyBarCharacter($empty = "\033[91m█\033[0m");
        $bar->setProgressCharacter($progress = "\033[92;101m▶\033[0m");
        $bar->setMessage('Starting the demo... fingers crossed', 'title');
        $bar->start();

        $fillmemory = [];
        for ($i = 0; $i < 15; ++$i) {
            usleep(random_int(400_000,500_000));
            $fillmemory[$i] = range(1,10000);
            $bar->advance();
            if ($i === 6) $bar->setMessage('Almost there!', 'title');
        }

        $bar->setMessage('It works!', 'title');
        $bar->finish();

        $output->writeln("");
        $output->writeln('<info>finished.</info>');

    }

);
$application->register('test:emoji')->setCode(
    function($input, $output) {
        $output->writeln('<info>👍</info>');
    }

);
$application->register('test:ansi:class')->setCode(
    function($input, \Symfony\Component\Console\Output\OutputInterface $output) {

        // using symfony console Color class and taking --no-ansi into account
        $red = $output->isDecorated() ? new Color('bright-red', 'yellow') : new Color();
        $output->writeln("Using Console\Color: " . $red->apply('this is bright red on yellow') . ', nice');
    }

);
$application->register('test:ansi:tags')->setCode(
    function($input, \Symfony\Component\Console\Output\OutputInterface $output) {
        // using symfony console tags which already take --no-ansi into account
        $output->writeln("Using tags: <fg=bright-red;bg=yellow>this is bright red on yellow</> nice");
    }
);
$application->register('test:progress:style')->setCode(
    function($input, $output) {
        $io = new SymfonyStyle($input, $output);
        $io->title('Using SymfonyStyle');

        $io->progressStart(100);
        $i = 0;
        while ($i++ < 100) {
            usleep(10_000);
            $io->progressAdvance();
        }
        $io->progressFinish();

        $output->writeln("");
        $output->writeln('<info>finished.</info>');
    }
);

$application->register('test:progress:indicator')->setCode(
    function(\Symfony\Component\Console\Input\InputInterface$input, \Symfony\Component\Console\Output\ConsoleOutputInterface $output) {

        foreach (range(1,20) as $line) {
            $output->writeln('<info>line '.$line.'</info>');
        }
        $section1 = $output->section();
        $section2 = $output->section();
        $section3 = $output->section();

        $section2->writeln('prefix');

        $blue = "\033[34m";
        $reset = "\033[0m";
        $spinner = array_map(fn ($c) => "$blue$c$reset", [
            '⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇'
        ]);


        $progressIndicator = new ProgressIndicator($section1, 'verbose', 100, $spinner);
        $p2 = new ProgressIndicator($section2, 'verbose', 100, $spinner);
        $p3 = new ProgressIndicator($section3, 'verbose', 100, $spinner);

        // starts and displays the progress indicator with a custom message
        $progressIndicator->start('Processing...');
        $p2->start('Processing also...');
        $p3->start('Processing #3...');

        $i = 0;
        while ($i++ < 50) {
//            sleep(1);
            usleep(100_000);

            // clear section unless bug in ProgressIndicator is fixed
            $section1->clear();
            $progressIndicator->advance();
            $section2->clear();
            $p2->advance();
            $section3->clear();
            $p3->advance();
/*
            if ($i % 2 === 0) {
                $p2->advance();
            }
            if ($i < 30) {
                $progressIndicator->advance();
            }
            if ($i === 30) {
                $progressIndicator->finish('Finished');
            }
 */
        }

        // ensures that the progress indicator shows a final message
        $progressIndicator->finish('Also finished');
        $p2->finish('Also finished');
        $p3->finish('Also finished');

        $output->writeln("");
        $output->writeln('<info>finished.</info>');
    }
);

$application->register('test:multiprogress:indicator')->setCode(
    function(\Symfony\Component\Console\Input\InputInterface$input, \Symfony\Component\Console\Output\ConsoleOutputInterface $output) {

        foreach (range(1,20) as $line) {
            $output->writeln('<info>line '.$line.'</info>');
        }

        $multi = new \MageOS\PrettyCli\Progress\MultiProgressIndicator($output);

        $blue = "\033[34m";
        $reset = "\033[0m";
        $spinner = array_map(fn ($c) => "$blue$c$reset", [
            '⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇'
        ]);


        $p1 = $multi->addProgressIndicator('verbose', 100, $spinner);
        $p2 = $multi->addProgressIndicator('verbose', 100, $spinner);
        $p3 = $multi->addProgressIndicator('verbose', 100, $spinner);

        // starts and displays the progress indicator with a custom message
        $p1->start('Processing...');
        $p2->start('Processing also...');
        $p3->start('Processing #3...');

        $i = 0;
        while ($i++ < 50) {
            usleep(100_000);
            $p1->advance();
            $p2->advance();
            $p3->advance();
        }

        $p1->finish('Also finished');
        $p2->finish('Also finished');
        $p3->finish('Also finished');

        $output->writeln("");
        $output->writeln('<info>finished.</info>');
    }
);
$application->register('test:progress:animated')->setCode(
    function($input, $output) {
        $maxSteps = 4;
        $progressBar = new ProgressBar($output, $maxSteps);
        $progressBar->setFormat('🌑 %percent:2s%% %bar%');

        $progressBar->start();

        for ($i = 0; $i < $maxSteps; $i++) {
            usleep(1000000); // 50ms
            switch ($i % 4) {
                case 0:
                    $progressBar->setFormat('🌒 %percent:2s%% %bar% %elapsed:6s%');
                    break;
                case 1:
                    $progressBar->setFormat('🌓 %percent:2s%% %bar% %elapsed:6s%');
                    break;
                case 2:
                    $progressBar->setFormat('🌔 %percent:2s%% %bar% %elapsed:6s%');
                    break;
                    case 3:
                    $progressBar->setFormat('🌕 %percent:2s%% %bar% %elapsed:6s%');
                break;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $output->writeln("\nDone!");
    }
);

$application->run();

