<?php
namespace CSCart\ApiDoc\Parser\Node;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Class_
 *
 * @ODM\Document
 * @ODM\Index(keys={"name"="text", "namespace"="text"})
 *
 * @package CSCart\ApiDoc\Parser\Node
 */
class Class_ extends Generic
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
     * @ODM\Field
     * @var string $extends
     */
    protected $extends;

    /**
     * @ODM\ReferenceMany(targetDocument="ClassMethod", cascade={"persist", "remove"})
     * @var array|ClassMethod[] $methods
     */
    protected $methods = [];

    /**
     * @ODM\ReferenceMany(targetDocument="ClassProperty", cascade={"persist", "remove"})
     * @var array|ClassProperty[] $properties
     */
    protected $properties = [];

    public function __construct()
    {
        $this->methods = new ArrayCollection();
        $this->properties = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function getIsFinal()
    {
        return $this->is_final;
    }

    /**
     * @param bool $is_final
     *
     * @return $this
     */
    public function setIsFinal($is_final)
    {
        $this->is_final = $is_final;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsAbstract()
    {
        return $this->is_abstract;
    }

    /**
     * @param bool $is_abstract
     *
     * @return $this
     */
    public function setIsAbstract($is_abstract)
    {
        $this->is_abstract = $is_abstract;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtends()
    {
        return $this->extends;
    }

    /**
     * @param string $extends
     *
     * @return $this
     */
    public function setExtends($extends)
    {
        $this->extends = ltrim($extends, '\\');

        return $this;
    }

    /**
     * @return array|ClassMethod[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param array|ClassMethod[] $methods
     *
     * @return $this
     */
    public function setMethods($methods)
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     *
     * @return $this
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @param \CSCart\ApiDoc\Parser\Node\ClassMethod $method
     *
     * @return $this
     */
    public function addMethod(ClassMethod $method)
    {
        $this->methods[] = $method;

        return $this;
    }

    /**
     * @param \CSCart\ApiDoc\Parser\Node\ClassProperty $property
     *
     * @return $this
     */
    public function addProperty(ClassProperty $property)
    {
        $this->properties[] = $property;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setVersion($version)
    {
        parent::setVersion($version);

        foreach ($this->methods as $method) {
            $method->setVersion($version);
        }

        foreach ($this->properties as $property) {
            $property->setVersion($version);
        }
    }

    /**
     * @inheritdoc
     */
    public function getFQN()
    {
        return ltrim($this->namespace . '\\' . $this->name, '\\');
    }

    public function sortMethods()
    {
//        uasort($this->methods, [$this, 'sortByVisibility']);
    }

    public function sortProperties()
    {
//        uasort($this->properties, [$this, 'sortByVisibility']);
    }

    /**
     * @param ClassProperty|ClassMethod $a
     * @param ClassProperty|ClassMethod $b
     *
     * @return int
     */
    private function sortByVisibility($a, $b)
    {
        if ($a->getVisibility() == $b->getVisibility()) {
            return strcmp($a->getName(), $b->getName());
        } elseif ($a->getVisibility() == 'public') {
            return -1;
        } elseif ($b->getVisibility() == 'public') {
            return 1;
        } elseif ($a->getVisibility() == 'protected' && $b->getVisibility() == 'private') {
            return -1;
        } else {
            return 1;
        }
    }

    public function getSlug()
    {
        return str_replace('\\', '-', $this->getFQN());
    }
}