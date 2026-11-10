## PHP-DI applicaiton demo

This repository demonstrates how to write a simple application using PHP-DI without any framework.

### Run

To run this demo, you need to clone it and install dependencies;

```bash
git clone https://github.com/mazezen/demos
cd demos/php/php-di-demo
composer install
```

You can then run the web application using PHP's built-in server:

```bash
php -S 0.0.0.0:8000 -t web/
```

The web application is running at http://localhost:8000.

You can alose run the CLI application:

```bash
php console.php
```

The following commands are available:

php console.php articles: lists the blog articles
php console.php article [id]: displays a blog article by its ID
