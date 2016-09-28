<?php
/**
 * @copyright (c) 2016 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace MCP\Cache;

use MCP\Cache\Item\Item;
use MCP\Cache\Utility\KeySaltingTrait;
use QL\MCP\Common\Time\Clock;
use Sk\Session;

/**
 * @internal
 */
class SkeletorSessionCache implements CacheInterface
{
    use KeySaltingTrait;

    /**
     * Properties read and used by the salting trait.
     *
     * @var string
     */
    const PREFIX = 'mcp-cache';
    const DELIMITER = '-';

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Clock
     */
    private $clock;

    /**
     * @var string|null
     */
    private $suffix;

    /**
     * An optional suffix can be provided which will be appended to the key
     * used to store and retrieve data. This can be used to throw away cached
     * data with a code push or other configuration change.
     *
     * @param Session $session
     * @param Clock $clock
     * @param string|null $suffix
     */
    public function __construct(Session $session, Clock $clock = null, $suffix = null)
    {
        $this->session = $session;
        $this->clock = $clock ?: new Clock('now', 'UTC');
        $this->suffix = $suffix;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $key = $this->salted($key, $this->suffix);

        $item = $this->session->get($key);
        if (!$item instanceof Item) {
            return null;
        }

        return $item->data($this->clock->read());
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = 0)
    {
        $key = $this->salted($key, $this->suffix);
        $expiry = null;

        if ($ttl > 0) {
            $delta = sprintf('+%d seconds', $ttl);
            $expiry = $this->clock->read()->modify($delta);
        }

        $item = new Item($value, $expiry);
        $this->session->set($key, $item);

        return true;
    }
}
