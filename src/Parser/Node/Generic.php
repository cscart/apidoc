<?php
namespace CSCart\ApiDoc\Parser\Node;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class Generic
 *
 * @ODM\Document(collection="nodes")
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\DiscriminatorField("node_type")
 * @ODM\Index(keys={"name"="text", "namespace"="text"})
 *
 * @package CSCart\ApiDoc\Parser\Node
 */
class Generic
{
    /**
     * @ODM\Id
     * @var string
     */
    protected $id;

    /**
     * @ODM\Field
     * @var string
     */
    protected $namespace;

    /**
     * @ODM\Field
     * @var string
     */
    protected $name;

    /**
     * @ODM\Field
     * @var string
     */
    protected $description;

    /**
     * @ODM\Field
     * @var string
     */
    protected $file;

    /**
     * @ODM\Field
     * @var string
     */
    protected $line;

    /**
     * @ODM\Field
     * @var string
     */
    protected $version;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param mixed $namespace
     *
     * @return $this
     */
    public function setNamespace($namespace)
    {
        $this->namespace = trim($namespace, '\\');

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @param mixed $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     *
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @param mixed $line
     *
     * @return $this
     */
    public function setLine($line)
    {
        $this->line = $line;

        return $this;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getFQN()
    {
        return ltrim($this->namespace . '\\' . $this->name, '\\');
    }
}