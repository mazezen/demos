<?php

namespace Mazezen\PhpDiDemo\Controller;

use Mazezen\PhpDiDemo\Model\ArticleRepository;
use Twig\Environment;

class HomeController
{
    /**
     * @var ArticleRepository
     */
    private $repository;

    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(ArticleRepository $repository, Environment $twig)
    {
        $this->repository = $repository;
        $this->twig = $twig;
    }

    public function __invoke()
    {
        echo $this->twig->render('home.twig', [
            'articles' => $this->repository->getArticles(),
        ]);
    }
}
