<?php
namespace CSCart\ApiDoc\Parser\Node;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class ClassMethod
 *
 * @ODM\Document
 * @ODM\Index(keys={"name"="text", "namespace"="text"})
 * @package CSCart\ApiDoc\Parser\Node
 */
class ClassMethod extends Function_
{
    /**
     * @ODM\Field(type="bool")
     * @var bool $is_final
     */
    protected $is_final;

    /**
     * @ODM\Field(type="bool")
     * @var bool $is_abstract
     */
    protected $is_abstract;

    /**
     * @ODM\Field(type="bool")
     * @var bool $is_static
     */
    protected $is_static;

    /**
     * @ODM\Field
     * @var string $visibility
     */
    protected $visibility;

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
     * @return boolean
     */
    public function isIsFinal()
    {
        return $this->is_final;
    }

    /**
     * @param boolean $is_final
     */
    public function setIsFinal($is_final)
    {
        $this->is_final = $is_final;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isIsAbstract()
    {
        return $this->is_abstract;
    }

    /**
     * @param boolean $is_abstract
     */
    public function setIsAbstract($is_abstract)
    {
        $this->is_abstract = $is_abstract;

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
     */
    public function setIsStatic($is_static)
    {
        $this->is_static = $is_static;

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
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getFQN()
    {
        return $this->getOwnerClass()->getFQN() . '::' . $this->name . '()';
    }
}