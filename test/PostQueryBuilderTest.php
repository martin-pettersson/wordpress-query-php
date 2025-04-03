<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e\WordPress;

use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use wpdb;

#[CoversClass(PostQueryBuilder::class)]
final class PostQueryBuilderTest extends TestCase
{
    use PHPMock;

    private string $postType;
    private PostQueryBuilder $postQueryBuilder;

    private function queryParametersOf(PostIterator $postIterator)
    {
        $queryProperty = new ReflectionProperty(PostIterator::class, 'query');

        $queryProperty->setAccessible(true);

        return $queryProperty->getValue($postIterator)->query;
    }

    #[Before]
    public function setUp(): void
    {
        global $wpdb;

        $wpdb = $this->getMockBuilder(wpdb::class)->disableOriginalConstructor()->getMock();

        $this->postType = 'post';
        $this->postQueryBuilder = new PostQueryBuilder($this->postType);
    }

    #[Test]
    public function shouldProvideSensibleDefaultParameters(): void
    {
        $parameters = $this->queryParametersOf($this->postQueryBuilder->build());

        $this->assertEquals($this->postType, $parameters['post_type']);
    }
}
