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

namespace Pimcore\Sitemap\Element\Processor;

use Pimcore\Model\Element\ElementInterface;
use Pimcore\Sitemap\Element\GeneratorContextInterface;
use Pimcore\Sitemap\Element\ProcessorInterface;
use Presta\SitemapBundle\Sitemap\Url\Url;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;

/**
 * Adds change frequency and priority entries based on document properties.
 */
class PropertiesProcessor implements ProcessorInterface
{
    const PROPERTY_CHANGE_FREQUENCY = 'sitemaps_changefreq';

    const PROPERTY_PRIORITY = 'sitemaps_priority';

    /**
     * {@inheritdoc}
     */
    public function process(Url $url, ElementInterface $element, GeneratorContextInterface $context): Url|UrlConcrete|null
    {
        if (!$url instanceof UrlConcrete) {
            return $url;
        }

        $changeFreq = $element->getProperty(self::PROPERTY_CHANGE_FREQUENCY);
        if (!empty($changeFreq)) {
            $url->setChangefreq($changeFreq);
        }

        $priority = $element->getProperty(self::PROPERTY_PRIORITY);
        if (!empty($priority)) {
            $url->setPriority($priority);
        }

        return $url;
    }
}
