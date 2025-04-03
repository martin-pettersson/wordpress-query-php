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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WP_Post;
use WP_Query;

#[CoversClass(PostIterator::class)]
final class PostIteratorTest extends TestCase
{
    use PHPMock;

    private MockObject $queryMock;
    private PostIterator $postIterator;

    #[Before]
    public function setUp(): void
    {
        $this->queryMock = $this->getMockBuilder(WP_Query::class)->getMock();
        $this->postIterator = new PostIterator($this->queryMock);
    }

    #[Test]
    public function shouldDeterminePostCount(): void
    {
        $count = 42;

        $this->queryMock->found_posts = $count;

        $this->assertEquals($count, $this->postIterator->count());
    }

    #[Test]
    public function shouldDeterminePageCount(): void
    {
        $count = 7;

        $this->queryMock->max_num_pages = $count;

        $this->assertEquals($count, $this->postIterator->pageCount());
    }

    #[Test]
    public function shouldOnlySetUpCurrentPostOnce(): void
    {
        $postMock = $this->getMockBuilder(WP_Post::class)->disableOriginalConstructor()->getMock();

        $this->queryMock->expects($this->once())->method('the_post');
        $this->getFunctionMock(__NAMESPACE__, 'get_post')->expects($this->once())->willReturn($postMock);

        $this->assertSame($postMock, $this->postIterator->current());
        $this->assertSame($postMock, $this->postIterator->current());
        $this->assertSame($postMock, $this->postIterator->current());
    }

    #[Test]
    public function shouldResetPostOnEveryIteration(): void
    {
        $postMock = $this->getMockBuilder(WP_Post::class)->disableOriginalConstructor()->getMock();

        $this->queryMock->expects($this->exactly(2))->method('the_post');
        $this->getFunctionMock(__NAMESPACE__, 'get_post')->expects($this->exactly(2))->willReturn($postMock);

        $this->postIterator->current();

        $this->assertEquals(0, $this->postIterator->key());

        $this->postIterator->next();
        $this->postIterator->current();

        $this->assertEquals(1, $this->postIterator->key());
    }

    #[Test]
    public function shouldDetermineWhetherIteratorIsValid(): void
    {
        $this->queryMock->expects($this->once())->method('have_posts')->willReturn(true);

        $this->assertTrue($this->postIterator->valid());
    }

    #[Test]
    public function shouldResetPostDataWhenIteratorIsInvalid(): void
    {
        $this->queryMock->method('have_posts')->willReturn(false);
        $this->getFunctionMock(__NAMESPACE__, 'wp_reset_postdata')->expects($this->once());

        $this->assertFalse($this->postIterator->valid());
    }

    #[Test]
    public function shouldRewindQuery(): void
    {
        $this->queryMock->expects($this->once())->method('rewind_posts');

        $this->postIterator->next();

        $this->assertEquals(1, $this->postIterator->key());

        $this->postIterator->rewind();

        $this->assertEquals(0, $this->postIterator->key());
    }
}
