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

namespace Pimcore\Model\Document\Hardlink\Wrapper;

use Pimcore\Model;

/**
 * @method \Pimcore\Model\Document\Hardlink\Dao getDao()
 */
class Printcontainer extends Model\Document\Printcontainer implements Model\Document\Hardlink\Wrapper\WrapperInterface
{
    use Model\Document\Hardlink\Wrapper;
}
