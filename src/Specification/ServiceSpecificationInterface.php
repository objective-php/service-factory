<?php

namespace ObjectivePHP\ServicesFactory\Specification;


interface ServiceSpecificationInterface
{

    /**
     * @return string Service identifier - must be unique
     */
    public function getId();

    /**
     * @return array Service aliases
     */
    public function getAliases();

    /**
     * @return array
     */
    public function getAutoAliases();

    /**
     * Tells whether a new service instance should be instantiated each time it's requested or not
     *
     * @return boolean
     */
    public function isStatic();

    /**
     * Tells whether a service can be overridden or not
     *
     * @return bool
     */
    public function isFinal();

}
