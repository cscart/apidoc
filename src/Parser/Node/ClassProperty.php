<?php
namespace CSCart\ApiDoc\Parser\Node;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class ClassProperty
 *
 * @ODM\Document
 * @ODM\Index(keys={"name"="text", "namespace"="text"})
 * @package CSCart\ApiDoc\Parser\Node
 */
class ClassProperty extends Generic
{
    /**
     * @ODM\Field
     * @var string $type
     */
    protected $type;

    /**
     * @ODM\Field
     * @var string $visibility
     */
    protected $visibility;

    /**
     * @ODM\Field(type="bool")
     * @var bool $is_static
     */
    protected $is_static;

    /**
     * @ODM\Field
     * @var string $default_value
     */
    protected $default_value;

    /**
     * @ODM\ReferenceOne(targetDocument="Class_")
     *
     * @var Class_|null
     */
    protected $owner_class;

    /**
     * @return Class_|null
     */
    public function getOwnerClass()
    {
        return $this->owner_class;
    }

    /**
     * @param Class_|null $owner_class
     *
     * @return ClassMethod
     */
    public function setOwnerClass($owner_class)
    {
        $this->owner_class = $owner_class;

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
     * @return ClassProperty
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @param string $visibility
     *
     * @return ClassProperty
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isIsStatic()
    {
        return $this->is_static;
    }

    /**
     * @param boolean $is_static
     *
     * @return ClassProperty
     */
    public function setIsStatic($is_static)
    {
        $this->is_static = $is_static;

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
     * @return ClassProperty
     */
    public function setDefaultValue($default_value)
    {
        $this->default_value = $default_value;

        return $this;
    }

    public function getFQN()
    {
        return $this->getOwnerClass()->getFQN() . '::' . $this->name;
    }
}