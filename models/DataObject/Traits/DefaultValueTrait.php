<?php
declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace Pimcore\Model\DataObject\Traits;

use Pimcore\Model\DataObject\ClassDefinition\DefaultValueGeneratorInterface;
use Pimcore\Model\DataObject\ClassDefinition\Helper\DefaultValueGeneratorResolver;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Exception\InheritanceParentNotFoundException;
use Pimcore\Model\DataObject\Localizedfield;
use Pimcore\Model\DataObject\Objectbrick\Data\AbstractData;

/**
 * @internal
 */
trait DefaultValueTrait
{
    public string $defaultValueGenerator = '';

    abstract protected function doGetDefaultValue(Concrete $object, array $context = []): mixed;

    /**
     * @param mixed $data
     * @param Concrete|null $object
     * @param array $params
     *
     * @return mixed $data
     */
    protected function handleDefaultValue(mixed $data, Concrete $object = null, array $params = []): mixed
    {
        $context = isset($params['context']) ? $params['context'] : [];
        $isUpdate = isset($params['isUpdate']) ? $params['isUpdate'] : true;

        /**
         * 1. only for create, not on update. otherwise there is no way to null it out anymore.
         */
        if ($isUpdate) {
            return $data;
        }

        /**
         * 2. if inheritance is enabled and there is no parent value then take the default value.
         * 3. if inheritance is disabled, take the default value.
         */
        if ($this->isEmpty($data)) {
            $class = null;
            $owner = isset($params['owner']) ? $params['owner'] : null;
            if ($owner instanceof Concrete) {
                $class = $owner->getClass();
            } elseif ($owner instanceof AbstractData) {
                $class = $owner->getObject()->getClass();
            }

            if ($object !== null && !empty($this->defaultValueGenerator)) {
                $defaultValueGenerator = DefaultValueGeneratorResolver::resolveGenerator($this->defaultValueGenerator);

                if ($defaultValueGenerator instanceof DefaultValueGeneratorInterface) {
                    if (!isset($params['context'])) {
                        $params['context'] = [];
                    }

                    if ($owner instanceof Concrete) {
                        $params['context'] = array_merge($params['context'], [
                            'ownerType' => 'object',
                            'fieldname' => $this->getName(),
                        ]);
                    } elseif ($owner instanceof Localizedfield) {
                        $params['context'] = array_merge($params['context'], [
                            'ownerType' => 'localizedfield',
                            'ownerName' => 'localizedfields',
                            'position' => $params['language'],
                            'fieldname' => $this->getName(),
                        ]);
                    } elseif ($owner instanceof \Pimcore\Model\DataObject\Fieldcollection\Data\AbstractData) {
                        $params['context'] = array_merge($params['context'], [
                            'ownerType' => 'fieldcollection',
                            'ownerName' => $owner->getFieldname(),
                            'fieldname' => $this->getName(),
                            'index' => $owner->getIndex(),
                        ]);
                    } elseif ($owner instanceof AbstractData) {
                        $params['context'] = array_merge($params['context'], [
                            'ownerType' => 'objectbrick',
                            'ownerName' => $owner->getFieldname(),
                            'fieldname' => $this->getName(),
                            'index' => $owner->getType(),
                        ]);
                    }

                    return $defaultValueGenerator->getValue($object, $this, $params['context']);
                }
            }

            // we check first if we even want to work with default values. if this is not the case then
            // we are also not allowed to inspect the parent value.

            // if the parent doesn't have a value then we take the configured value as fallback
            $configuredDefaultValue = $this->doGetDefaultValue($object, $context);
            if (!$this->isEmpty($configuredDefaultValue)) {
                if ($class && $class->getAllowInherit()) {
                    $params = [];

                    $inheritanceEnabled = Concrete::getGetInheritedValues();

                    try {
                        // make sure we get the inherited value of the parent
                        Concrete::setGetInheritedValues(true);

                        $data = $owner->getValueFromParent($this->getName(), $params);
                        if (!$this->isEmpty($data)) {
                            return null;
                        }
                    } catch (InheritanceParentNotFoundException $e) {
                        // no data from parent available, use the default value
                    } finally {
                        Concrete::setGetInheritedValues($inheritanceEnabled);
                    }
                }
            }
            $data = $configuredDefaultValue;
        }

        return $data;
    }

    public function getDefaultValueGenerator(): string
    {
        return $this->defaultValueGenerator;
    }

    public function setDefaultValueGenerator(string $defaultValueGenerator): void
    {
        $this->defaultValueGenerator = (string)$defaultValueGenerator;
    }
}
