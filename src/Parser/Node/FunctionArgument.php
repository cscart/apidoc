<?php
namespace CSCart\ApiDoc\Parser\Node;

use \Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class FunctionArgument
 *
 * @ODM\EmbeddedDocument
 * 
 * @package CSCart\ApiDoc\Parser\Node
 */
class FunctionArgument
{
    /**
     * @ODM\Field
     * @var string $name
     */
    protected $name;

    /**
     * @ODM\Field
     * @var string $type
     */
    protected $type;

    /**
     * @ODM\Field
     * @var string $description
     */
    protected $description;

    /**
     * @ODM\Field
     * @var string $default_value
     */
    protected $default_value;

    /**
     * @ODM\Field(type="bool")
     * @var bool $by_reference
     */
    protected $by_reference = false;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return FunctionArgument
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return FunctionArgument
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return FunctionArgument
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->default_value;
    }

    /**
     * @param string $default_value
     *
     * @return FunctionArgument
     */
    public function setDefaultValue($default_value)
    {
        $this->default_value = $default_value;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isByReference()
    {
        return $this->by_reference;
    }

    /**
     * @param boolean $by_reference
     *
     * @return FunctionArgument
     */
    public function setByReference($by_reference)
    {
        $this->by_reference = $by_reference;

        return $this;
    }
}