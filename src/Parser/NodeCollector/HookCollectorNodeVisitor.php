<?php
namespace CSCart\ApiDoc\Parser\NodeCollector;

use CSCart\ApiDoc\Parser\Node\FunctionArgument;
use CSCart\ApiDoc\Parser\Node\Hook;
use phpDocumentor\Reflection\DocBlockFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;

class HookCollectorNodeVisitor extends \PhpParser\NodeVisitorAbstract
{
    /**
     * @var string
     */
    protected $set_hook_func_name;

    /**
     * @var array|Hook[]
     */
    protected $found_hooks = [];

    public function __construct($set_hook_func_name)
    {
        $this->set_hook_func_name = $set_hook_func_name;
    }

    /**
     * @return array|Hook[]
     */
    public function getFoundHooks()
    {
        return $this->found_hooks;
    }

    protected $current_class;
    protected $current_method;
    protected $current_function;

    public function enterNode(Node $node)
    {
        if ($node instanceof Class_) {
            $this->current_class = (string) $node->namespacedName;
        }
        if ($node instanceof ClassMethod) {
            $this->current_method = $node->name;
        }
        if ($node instanceof Function_) {
            $this->current_class = null;
            $this->current_method = null;
            $this->current_function = (string) $node->namespacedName;
        }

        if ($node instanceof FuncCall) {
            if ($node->name instanceof Name && $node->name->getLast() == $this->set_hook_func_name) {
                $hook = new Hook();
                $hook->setLine($node->getLine());

                if ($this->current_class !== null && $this->current_method !== null) {
                    $owner_type = 'method';
                    $owner_name = ltrim($this->current_class . '::' . $this->current_method . '()', '\\');
                } elseif ($this->current_function !== null) {
                    $owner_type = 'function';
                    $owner_name = ltrim($this->current_function . '()', '\\');
                } else {
                    $owner_type = 'file';
                    $owner_name = null;
                }

                $hook->setOwnerInfo([$owner_type, $owner_name]);

                $name_arg = reset($node->args);
                if ($name_arg->value instanceof String_) {
                    $hook->setName($name_arg->value->value);

                    array_shift($node->args);
                }

                $doc_comment = $node->getDocComment();
                if ($doc_comment instanceof Doc) {
                    try {
                        $docblock = DocBlockFactory::createInstance()
                            ->create($doc_comment->getText());

                        $hook->setDescription(trim(implode(PHP_EOL,
                            [$docblock->getSummary(), $docblock->getDescription()])));
                    } catch (\Exception $e) {

                    }
                }

                foreach ($node->args as $i => $hook_arg) {
                    if ($hook_arg->value instanceof Variable) {

                        $hook_argument = new FunctionArgument();
                        $hook_argument->setName($hook_arg->value->name);

                        if (isset($docblock)) {
                            foreach ($docblock->getTagsByName('param') as $docblock_param_tag) {
                                /** @var \phpDocumentor\Reflection\DocBlock\Tags\Param $docblock_param_tag */
                                if ($docblock_param_tag->getVariableName() == $hook_argument->getName()) {
                                    $hook_argument->setDescription((string) $docblock_param_tag->getDescription());
                                    $hook_argument->setType((string) $docblock_param_tag->getType());

                                    break;
                                }
                            }
                        }

                        $hook->addArgument($hook_argument);
                    }
                }

                $this->found_hooks[] = $hook;
            }
        }
    }
}