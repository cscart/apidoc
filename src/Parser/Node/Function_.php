<?php
namespace CSCart\ApiDoc\Parser\Node;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class Function_
 *
 * @ODM\Document
 * @ODM\Index(keys={"name"="text", "namespace"="text"})
 *
 * @package CSCart\ApiDoc\Parser\Node
 */
class Function_ extends Generic
{
    /**
     * @ODM\EmbedMany(targetDocument="FunctionArgument")
     * @var array|FunctionArgument[]
     */
    protected $arguments = [];

    /**
     * @ODM\Field
     * @var string $return_type
     */
    protected $return_type;

    /**
     * @ODM\Field
     * @var string $return_description
     */
    protected $return_description;

    /**
     * @ODM\ReferenceMany(targetDocument="Hook")
     * @var array|Hook[]
     */
    protected $hooks = [];

    public function __construct()
    {
        $this->hooks = new ArrayCollection();
        $this->arguments = new ArrayCollection();
    }

    /**
     * @return array|FunctionArgument[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array|FunctionArgument[] $arguments
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @return string
     */
    public function getReturnType()
    {
        return $this->return_type;
    }

    /**
     * @param string $return_type
     */
    public function setReturnType($return_type)
    {
        $this->return_type = $return_type;
    }

    /**
     * @return string
     */
    public function getReturnDescription()
    {
        return $this->return_description;
    }

    /**
     * @param string $return_description
     */
    public function setReturnDescription($return_description)
    {
        $this->return_description = $return_description;
    }

    /**
     * @return array|Hook[]
     */
    public function getHooks()
    {
        return $this->hooks;
    }

    /**
     * @param array|Hook[] $hooks
     */
    public function setHooks($hooks)
    {
        $this->hooks = $hooks;
    }

    public function addHook(Hook $hook)
    {
        $this->hooks[] = $hook;
    }

    public function addArgument(FunctionArgument $argument)
    {
        $this->arguments[$argument->getName()] = $argument;
    }

    public function getFQN()
    {
        return parent::getFQN() . '()';
    }
}