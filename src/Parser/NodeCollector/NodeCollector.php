<?php
namespace CSCart\ApiDoc\Parser\NodeCollector;


use CSCart\ApiDoc\Parser\Context;

interface NodeCollector
{
    public function collectNodesToContext();

    public function setParserContext(Context $context);
}