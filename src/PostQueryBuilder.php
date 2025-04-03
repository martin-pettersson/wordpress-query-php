<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e\WordPress;

use Override;
use WP_Query;

/**
 * Has the ability to configure and build WordPress post queries.
 */
class PostQueryBuilder implements PostQueryBuilderInterface
{
    /**
     * Post type to query.
     *
     * @var string
     */
    private readonly string $postType;

    /**
     * Create a new post query builder instance.
     *
     * @param string $postType Post type to query.
     */
    public function __construct(string $postType)
    {
        $this->postType = $postType;
    }

    #[Override]
    public function build(): PostIterator
    {
        $parameters = [
            'post_type' => $this->postType
        ];

        return new PostIterator(new WP_Query($this->configureQueryParameters($parameters)));
    }

    /**
     * Configure query parameters allowing extending classes to modify them.
     *
     * @param array $parameters Arbitrary query parameters.
     * @return array Configured query parameters.
     */
    protected function configureQueryParameters(array $parameters): array
    {
        return $parameters;
    }
}
