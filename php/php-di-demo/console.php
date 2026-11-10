<?php

use Mazezen\PhpDiDemo\Model\ArticleRepository;
use Symfony\Component\Console\Output\OutputInterface;

$container = require __DIR__ . '/app/bootstrap.php';

$app = new Silly\Application();

$app->useContainer($container, $injectWithTypeHint = true);

$app->command('articles', function (OutputInterface $output, ArticleRepository $repository) {
    $output->writeln('<comment>Here are the articles in the blog:</comment>');

    $articles = $repository->getArticles();

    foreach ($articles as $article) {
        $output->writeln(sprintf(
            'Article #%d: <info>%s</info>',
            $article->getId(),
            $article->getTitle()
        ));
    }
});


$app->command('article [id]', 'SuperBlog\Command\ArticleDetailCommand');

$app->run();
