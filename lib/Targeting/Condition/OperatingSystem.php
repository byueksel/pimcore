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

namespace Pimcore\Targeting\Condition;

use Pimcore\Targeting\DataProvider\Device;
use Pimcore\Targeting\DataProviderDependentInterface;
use Pimcore\Targeting\Model\VisitorInfo;

class OperatingSystem extends AbstractVariableCondition implements DataProviderDependentInterface
{
    private ?string $system = null;

    /**
     * Mapping from admin UI values to DeviceDetector results
     *
     * @var array
     */
    protected static array $osMapping = [
        'MAC' => 'macos',
        'WIN' => 'windows',
        'LIN' => 'linux',
        'AND' => 'android',
        'IOS' => 'ios',
    ];

    public function __construct(string $system = null)
    {
        $this->system = $system;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromConfig(array $config): static
    {
        return new static($config['system'] ?? null);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataProviderKeys(): array
    {
        return [Device::PROVIDER_KEY];
    }

    /**
     * {@inheritdoc}
     */
    public function canMatch(): bool
    {
        return !empty($this->system);
    }

    /**
     * {@inheritdoc}
     */
    public function match(VisitorInfo $visitorInfo): bool
    {
        $device = $visitorInfo->get(Device::PROVIDER_KEY);

        if (!$device || true === ($device['is_bot'] ?? false)) {
            return false;
        }

        $osInfo = $device['os'] ?? null;
        if (!$osInfo) {
            return false;
        }

        $os = $osInfo['short_name'] ?? null;
        if (!empty($os) && isset(static::$osMapping[$os])) {
            $os = static::$osMapping[$os];
        }

        if ($this->matchesOperatingSystem($os)) {
            $this->setMatchedVariable('os', $os);

            return true;
        }

        return false;
    }

    private function matchesOperatingSystem(string $os = null): bool
    {
        if (empty($os)) {
            return false;
        }

        if ('all' === $this->system) {
            return true;
        }

        return $os === $this->system;
    }
}
