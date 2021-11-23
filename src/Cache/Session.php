<?php

declare(strict_types=1);

namespace Atk4\Login\Cache;

use Atk4\Core\DiContainerTrait;
use Atk4\Core\NameTrait;
use Atk4\Core\SessionTrait;

/**
 * Session cache for authentication controller.
 */
class Session // implements CacheInterface
{
    use DiContainerTrait;
    use NameTrait;
    use SessionTrait;

    /**
     * Cached data expires in X seconds. False to never expire.
     *
     * @var float|false
     */
    public $expireTime = false;

    /**
     * Cache key. Set this if you want to use multiple cache objects at same time.
     *
     * @var string|null
     */
    public $key;

    /**
     * Constructor.
     */
    public function __construct(array $options = [])
    {
        $this->setDefaults($options);
    }

    /**
     * Initialize cache.
     */
    public function init(): void
    {
        if (\PHP_SAPI !== 'cli') { // helps with unit tests
            $this->startSession();
        }
    }

    /**
     * Return cache key.
     *
     * @return mixed
     */
    public function getKey()
    {
        $this->init();

        return static::class . ':' . ($this->key ?? $this->name);
    }

    /**
     * Get data from session cache.
     */
    public function getData(): array
    {
        $key = $this->getKey();

        if (!isset($_SESSION[$key]) || ($this->expireTime && $_SESSION[$key . '-at'] + $this->expireTime < microtime(true))) {
            $_SESSION[$key] = [];
        }

        return $_SESSION[$key];
    }

    /**
     * Store data in session cache.
     *
     * @return $this
     */
    public function setData(array $data)
    {
        $key = $this->getKey();
        $_SESSION[$key] = $data;
        $_SESSION[$key . '-at'] = microtime(true);

        return $this;
    }
}
