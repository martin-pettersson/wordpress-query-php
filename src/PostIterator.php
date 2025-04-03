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
     * Registered filter predicates.
     *
     * @var array
     */
    private array $filterPredicates;

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
     * @param array $filterPredicates Registered filter predicates.
     */
    public function __construct(WP_Query $query, array $filterPredicates = [])
    {
        $this->query = $query;
        $this->index = 0;
        $this->filterPredicates = $filterPredicates;
        $this->post = null;
    }

    #[Override]
    public function current(): ?WP_Post
    {
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
        if (! is_null($this->post)) {
            return true;
        }

        $post = $this->nextValidPost();

        if (is_null($post)) {
            wp_reset_postdata();

            return false;
        }

        $this->post = $post;

        return true;
    }

    #[Override]
    public function rewind(): void
    {
        $this->query->rewind_posts();
        $this->index = 0;
        $this->post = null;
    }

    /**
     * Produce a post iterator with given filter predicate.
     *
     * @param callable $predicate Arbitrary filter predicate.
     * @return \N7e\WordPress\PostIterator Post iterator with given filter predicate.
     */
    public function filter(callable $predicate): PostIterator
    {
        return new PostIterator($this->query, array_merge($this->filterPredicates, [$predicate]));
    }

    /**
     * Retrieve the next valid post.
     *
     * @return \WP_Post|null Next valid post if found.
     */
    private function nextValidPost(): ?WP_Post
    {
        while ($this->query->have_posts()) {
            $this->query->the_post();

            /** @var \WP_Post $post */
            $post = get_post();

            if ($this->isValid($post)) {
                return $post;
            }
        }

        return null;
    }

    /**
     * Determine whether a given post is considered valid.
     *
     * @param \WP_Post $post Arbitrary post.
     * @return bool True if the given post is considered valid.
     */
    private function isValid(WP_Post $post): bool
    {
        foreach ($this->filterPredicates as $predicate) {
            if (! $predicate($post)) {
                return false;
            }
        }

        return true;
    }
}
