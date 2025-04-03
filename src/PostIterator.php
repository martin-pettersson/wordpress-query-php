<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e\WordPress;

use Iterator;
use Override;
use WP_Post;
use WP_Query;

/**
 * Has the ability to iterate WordPress post queries.
 *
 * The post object is set up for every iteration so that template functions can
 * be used as expected in a loop.
 */
final class PostIterator implements Iterator
{
    /**
     * Arbitrary post query.
     *
     * @var \WP_Query
     */
    private readonly WP_Query $query;

    /**
     * Current index.
     *
     * @var int
     */
    private int $index;

    /**
     * Current post.
     *
     * @var \WP_Post|null
     */
    private ?WP_Post $post;

    /**
     * Create a new post iterator instance.
     *
     * @param \WP_Query $query Arbitrary post query.
     */
    public function __construct(WP_Query $query)
    {
        $this->query = $query;
        $this->index = 0;
        $this->post = null;
    }

    #[Override]
    public function current(): WP_Post
    {
        if (is_null($this->post)) {
            $this->query->the_post();

            $this->post = get_post();
        }

        return $this->post;
    }

    #[Override]
    public function next(): void
    {
        $this->index++;
        $this->post = null;
    }

    #[Override]
    public function key(): int
    {
        return $this->index;
    }

    #[Override]
    public function valid(): bool
    {
        $valid = $this->query->have_posts();

        if (! $valid) {
            wp_reset_postdata();
        }

        return $valid;
    }

    #[Override]
    public function rewind(): void
    {
        $this->query->rewind_posts();
        $this->index = 0;
    }

    /**
     * Determine the number of posts in query.
     *
     * @return int Number of posts in query.
     */
    public function count(): int
    {
        return $this->query->found_posts;
    }

    /**
     * Determine the number of pages in query.
     *
     * @return int Number of pages in query.
     */
    public function pageCount(): int
    {
        return $this->query->max_num_pages;
    }
}
