<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e\WordPress;

/**
 * Has the ability to configure and build WordPress post queries.
 */
interface PostQueryBuilderInterface
{
    /**
     * Build a configured post iterator.
     *
     * @return \N7e\WordPress\PostIterator Configured post iterator.
     */
    public function build(): PostIterator;
}
