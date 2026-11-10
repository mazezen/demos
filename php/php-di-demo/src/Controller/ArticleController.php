<?php

namespace Mazezen\PhpDiDemo\Controller;

use Mazezen\PhpDiDemo\Model\ArticleRepository;
use Twig\Environment;

class ArticleController
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

    public function show($id)
    {
        $article = $this->repository->getArticle($id);
        
        echo $this->twig->render('article.twig', [
            'article' => $article,
        ]);
    }
}
