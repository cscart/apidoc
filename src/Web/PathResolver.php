<?php
namespace CSCart\ApiDoc\Web;

use CSCart\ApiDoc\Parser\Node\Class_;
use CSCart\ApiDoc\Parser\Node\ClassMethod;
use CSCart\ApiDoc\Parser\Node\ClassProperty;
use CSCart\ApiDoc\Parser\Node\Function_;
use CSCart\ApiDoc\Parser\Node\Generic;
use CSCart\ApiDoc\Parser\Node\Hook;
use Slim\Interfaces\RouterInterface;

class PathResolver
{
    /**
     * @var \Slim\Interfaces\RouterInterface
     */
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getNodeURL(Generic $generic_node)
    {
        $slug = $generic_node->getFQN();

        if ($generic_node instanceof Class_) {
            $route_type = 'class';
        } elseif ($generic_node instanceof Hook) {
            if ($generic_node->getOwner() instanceof ClassMethod) {
                $route_type = 'class';

                $slug = $generic_node->getOwner()->getOwnerClass()->getFQN() . '#' . $generic_node->getName();

            } elseif ($generic_node->getOwner() instanceof Function_) {
                $route_type = 'function';
            } else {
                $route_type = 'hook';

                $slug = $generic_node->getName();
            }
        }elseif ($generic_node instanceof ClassMethod) {
            $route_type = 'class';
        } elseif ($generic_node instanceof ClassProperty) {
            $route_type = 'class';
        } elseif ($generic_node instanceof Function_) {
            $route_type = 'function';
        }

        $slug = str_replace(
            ['\\', '::', '(', ')'],
            ['.', '#', '', ''],
            $slug
        );

        return $this->router->pathFor($route_type, [
            'slug' => $slug,
            'version' => $generic_node->getVersion(),
        ]);
    }
}