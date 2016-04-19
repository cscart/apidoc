<?php
namespace CSCart\ApiDoc\Parser\Node;

use \Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class Hook
 *
 * @ODM\Document
 * @ODM\Index(keys={"name"="text", "namespace"="text"})
 *
 * @package CSCart\ApiDoc\Parser\Node
 */
class Hook extends Function_
{
    /**
     * @var array $owner_info
     */
    protected $owner_info;

    /**
     * @ODM\ReferenceOne
     * @var Function_|ClassMethod|null
     */
    protected $owner;

    /**
     * @return array
     */
    public function getOwnerInfo()
    {
        return $this->owner_info;
    }

    /**
     * @param array $owner_info
     */
    public function setOwnerInfo($owner_info)
    {
        $this->owner_info = $owner_info;
    }

    /**
     * @return ClassMethod|Function_|null
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param ClassMethod|Function_|null $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    public function getFQN()
    {
        if ($this->getOwner() !== null) {
            return ltrim($this->getOwner()->getFQN() . '#' . $this->name, '#');
        } else {
            return $this->name;
        }
    }
}