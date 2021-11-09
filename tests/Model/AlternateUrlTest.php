<?php

/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

declare(strict_types=1);

namespace Elgentos\AlternateUrls\Tests\Model;

use PHPUnit\Framework\TestCase;
use Elgentos\AlternateUrls\Model\AlternateUrl;

/**
 * @coversDefaultClass \Elgentos\AlternateUrls\Model\AlternateUrl
 */
class AlternateUrlTest extends TestCase
{
    public function testGetHreflang(): void
    {
        $subject = new AlternateUrl(
            'nl-nl',
            'https://domain.com/test-url'
        );

        $this->assertIsString($subject->getHreflang());
    }

    public function testGetUrl(): void
    {
        $subject = new AlternateUrl(
            'nl-nl',
            'https://domain.com/test-url'
        );

        $this->assertIsString($subject->getUrl());
    }
}
