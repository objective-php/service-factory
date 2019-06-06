<?php

namespace ObjectivePHP\ServicesFactory\Builder;

use ObjectivePHP\Primitives\Collection\Collection;
use ObjectivePHP\ServicesFactory\Exception\ServiceNotFoundException;
use ObjectivePHP\ServicesFactory\Exception\ServicesFactoryException;
use ObjectivePHP\ServicesFactory\Specification\ClassServiceSpecification;
use ObjectivePHP\ServicesFactory\Specification\ClassServiceSpecificationInterface;
use ObjectivePHP\ServicesFactory\Specification\ServiceSpecificationInterface;

/**
 * Class ClassServiceBuilder
 *
 * @package ObjectivePHP\ServicesFactory\Builder
 */
class ClassServiceBuilder extends AbstractServiceBuilder
{
    /**
     * Service definition types this builder can handle
     *
     * @var array
     */
    protected $handledSpecs = [ClassServiceSpecification::class];

    /**
     * @param ClassServiceSpecificationInterface $serviceSpecification
     * @param array $params
     * @param string|null $serviceId
     * @return mixed
     * @throws ServiceNotFoundException
     * @throws ServicesFactoryException
     */
    public function build(ServiceSpecificationInterface $serviceSpecification, $params = [], string $serviceId = null)
    {
        // check compatibility with the service definition
        if (!$this->doesHandle($serviceSpecification)) {
            throw new ServicesFactoryException(
                sprintf(
                    '"%s" service definition is not handled by this builder.',
                    get_class($serviceSpecification)
                ),
                ServicesFactoryException::INCOMPATIBLE_SERVICE_DEFINITION
            );
        }

        $serviceClassName = $serviceSpecification->getClass();


        // check class existence
        if (!class_exists($serviceClassName)) {
            throw new ServicesFactoryException(
                sprintf(
                    'Unable to build service: class "%s" is unknown',
                    $serviceClassName
                ),
                ServicesFactoryException::INVALID_SERVICE_SPECS
            );
        }

        // merge service defined and runtime params
        $constructorParams = clone Collection::cast($params);
        $constructorParams->add($serviceSpecification->getConstructorParams());

        if ($constructorParams->isEmpty()) {
            // no constructor params has been set,
            // try to autowire service
            $constructorParams->add($this->getServicesFactory()->autowire($serviceClassName));
        }

        $constructorParams = $constructorParams->values()->toArray();
        // substitute params with referenced services
        if ($this->getServicesFactory()->hasConfig()) {
            $constructorParams = $this->getServicesFactory()->getConfig()->processParameters($constructorParams);
        }

        $service = new $serviceClassName(...$constructorParams);

        // call setters if any
        if ($setters = $serviceSpecification->getSetters()) {
            foreach ($setters as $setter => $setterParams) {
                $instanceSetterParams = (clone Collection::cast($setterParams))->values()->toArray();

                if ($this->getServicesFactory()->hasConfig()) {
                    $instanceSetterParams = $this
                        ->getServicesFactory()
                        ->getConfig()
                        ->processParameters($instanceSetterParams);
                }

                $service->$setter(...$instanceSetterParams);
            }
        }

        return $service;
    }


}
