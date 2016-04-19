<?php
namespace CSCart\ApiDoc\Parser;

use CSCart\ApiDoc\Parser\Node\Class_;
use CSCart\ApiDoc\Parser\Node\Function_;
use CSCart\ApiDoc\Parser\Node\Hook;

class Context
{
    /** @var string $version */
    public $version;

    /** @var string $sources_directory */
    public $sources_directory;

    /** @var array $exclusion_masks */
    public $exclusion_masks = [];

    /** @var array|Hook[] $hooks */
    public $hooks = [];

    /** @var array|Function_[] $functions */
    public $functions = [];

    /** @var array|Class_[] $classes */
    public $classes = [];

    public function linkHooksToOwners()
    {
        foreach ($this->classes as $class_) {
            foreach ($class_->getMethods() as $method) {
                foreach ($this->hooks as $hook) {
                    if ($hook->getOwnerInfo()[0] == 'method' && $hook->getOwnerInfo()[1] == ltrim($class_->getNamespace() . '\\' . $class_->getName() . '::' . $method->getName() . '()', '\\')) {
                        $method->addHook($hook);
                        $hook->setOwner($method);
                    }
                }
            }
        }

        foreach ($this->functions as $function_) {
            foreach ($this->hooks as $hook) {
                if ($hook->getOwnerInfo()[0] == 'function' && $hook->getOwnerInfo()[1] == ltrim($function_->getNamespace() . '\\' . $function_->getName() . '()', '\\')) {
                    $function_->addHook($hook);
                    $hook->setOwner($function_);
                }
            }
        }
    }

    public function linkVersionToNodes()
    {
        foreach ($this->functions as $function_) {
            $function_->setVersion($this->version);
        }
        
        foreach ($this->hooks as $hook) {
            $hook->setVersion($this->version);
        }
        
        foreach ($this->classes as $class_) {
            $class_->setVersion($this->version);
        }
    }
}