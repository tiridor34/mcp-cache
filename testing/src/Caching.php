<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Cache\Testing;

use MCP\Cache\CachingTrait;

/**
 * @codeCoverageIgnore
 */
class Caching
{
    use CachingTrait {
        cache as public;
        getFromCache as public;
        setToCache as public;
    }
}
