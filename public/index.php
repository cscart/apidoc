<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file)) {
        return false;
    }
}
define('ROOT_DIR', realpath(__DIR__ . '/../'));

require __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
    ]
]);

/** @var \Pimple\Container $container */
$container = $app->getContainer();
$container->register(new \CSCart\ApiDoc\Provider\MongoODMProvider());

$container['render'] = function ($container) {
    return function (\Psr\Http\Message\ResponseInterface $response, $template, $variables) use ($container) {
        return $container->view->render($response, $template, array_merge($variables, $container['template.vars']));
    };
};

$container['resolveVersion'] = function ($container) {
    return function ($version) use ($container) {
        $all_versions = $container['data.all_versions'];

        if ($version == 'latest') {

            return reset($all_versions);

        }

        if (in_array($version, $all_versions)) {
            return $version;
        }

        return reset($all_versions);
    };
};

$container['data.all_versions'] = function ($container) {
    /** @var \Doctrine\ODM\MongoDB\DocumentManager $dm */
    $dm = $container['mongo.dm'];

    /** @var \Doctrine\MongoDB\ArrayIterator $versions_iterator */
    $versions_iterator = $dm->getDocumentCollection(\CSCart\ApiDoc\Parser\Node\Generic::class)
        ->distinct('version');

    $versions = [];
    foreach ($versions_iterator as $found_version) {
        if (!empty($found_version)) {
            $versions[] = $found_version;
        }
    }

    return $versions;
};

$container['template.vars'] = function ($container) {
    $vars = [
        'versions' => $container['data.all_versions'],
        'path' => $container['pathResolver'],
    ];

    return $vars;
};

$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../templates', [
        'cache' => false,
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));

    return $view;
};
$container['pathResolver'] = function ($container) {
    return new \CSCart\ApiDoc\Web\PathResolver($container['router']);
};

//$debug_bar = new DebugBar\StandardDebugBar();
//$debug_bar->addCollector($container['mongo.queryCollector']);
//
//$debug_bar_renderer = $debug_bar->getJavascriptRenderer('/phpdebugbar');
//$app->add(new PhpMiddleware\PhpDebugBar\PhpDebugBarMiddleware($debug_bar_renderer));

$version_placeholder = '{version:[0-9]+\.[0-9]+\.[0-9]+|latest}';

$app->get("/{$version_placeholder}", function ($request, $response, $args) {
    return $this['render']($response, 'index.twig', [
        'version' => $this['resolveVersion']($args['version']),
    ]);
})->setName('index');


$app->get("/{$version_placeholder}/function/{slug}", function ($request, $response, $args) {
    /** @var \Doctrine\ODM\MongoDB\DocumentManager $dm */
    $dm = $this['mongo.dm'];

    $fqn = $args['slug'];
    $fqn = str_replace('.', '\\', $fqn);
    $fqn = explode('\\', $fqn);

    $name = array_pop($fqn);
    $namespace = implode('\\', $fqn);

    $version = $this['resolveVersion']($args['version']);

    $function_ = $dm->getRepository(\CSCart\ApiDoc\Parser\Node\Function_::class)->findOneBy([
        'name' => $name,
        'namespace' => $namespace,
        'version' => $version
    ]);

    return $this['render']($response, 'nodes/function.twig', [
        'node' => $function_,
        'version' => $version,
    ]);
})->setName('function');

$app->get("/{$version_placeholder}/hook/{slug}", function ($request, $response, $args) {
    /** @var \Doctrine\ODM\MongoDB\DocumentManager $dm */
    $dm = $this['mongo.dm'];

    $name = $args['slug'];
    $version = $this['resolveVersion']($args['version']);

    $hook = $dm->getRepository(\CSCart\ApiDoc\Parser\Node\Hook::class)->findOneBy([
        'name' => $name,
        'version' => $version
    ]);

    return $this['render']($response, 'nodes/hook.twig', [
        'node' => $hook,
        'version' => $version,
    ]);
})->setName('hook');


$app->get("/{$version_placeholder}/class/{slug}", function ($request, $response, $args) {
    /** @var \Doctrine\ODM\MongoDB\DocumentManager $dm */
    $dm = $this['mongo.dm'];

    $class_fqn = $args['slug'];
    $class_fqn = str_replace('.', '\\', $class_fqn);
    $class_fqn = explode('\\', $class_fqn);

    $class_name = array_pop($class_fqn);
    $class_namespace = implode('\\', $class_fqn);
    $version = $this['resolveVersion']($args['version']);


    $class_ = $dm->getRepository(\CSCart\ApiDoc\Parser\Node\Class_::class)->findOneBy([
        'name' => $class_name,
        'namespace' => $class_namespace,
        'version' => $version,
    ]);

    return $this['render']($response, 'nodes/class.twig', [
        'node' => $class_,
        'version' => $version,
    ]);
})->setName('class');

$app->get("/{$version_placeholder}/search",
    function (\Psr\Http\Message\ServerRequestInterface $request, $response, $args) {

        if (empty($_GET['query']) || strlen(trim($_GET['query'])) < 3) {
            return;
        }

        $version = $this['resolveVersion']($args['version']);

        /** @var \Doctrine\ODM\MongoDB\DocumentManager $dm */
        $dm = $this['mongo.dm'];

        /** @var \CSCart\ApiDoc\Web\PathResolver $path_resolver */
        $path_resolver = $this['pathResolver'];

        /** @var \CSCart\ApiDoc\Parser\Node\Generic[] $search_result_list */
        $search_result_list = $dm->createQueryBuilder(\CSCart\ApiDoc\Parser\Node\Generic::class)
            ->field('name')
            ->equals(new MongoRegex(sprintf('/%s/', preg_quote(trim($_GET['query'])))))
            ->field('version')->equals($version)
            ->sort('name', 'asc')
            ->limit(10)
            ->getQuery()->execute();

        $suggestions = [];

        foreach ($search_result_list as $search_result) {
            $suggestion = [
                'value' => $search_result->getFQN(),
                'data' => [
                    'url' => $path_resolver->getNodeURL($search_result),
                ]
            ];

            $suggestions[] = $suggestion;
        }

        return $response->withJson(['suggestions' => $suggestions]);
    })->setName('search');

$app->get('/', function ($request, \Psr\Http\Message\ResponseInterface $response) {
    return $this->response
        ->withStatus(301)
        ->withHeader('Location',
            $this->router->pathFor('index', ['version' => 'latest'])
        );
});


$app->run();