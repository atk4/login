<?php

declare(strict_types=1);

namespace atk4\login\Cache;

//use atk4\login\Auth;
use atk4\core\DiContainerTrait;
use atk4\core\NameTrait;
use atk4\core\SessionTrait;

/**
 * Session cache for authentication controller.
 */
class Session // implementes CacheInterface
{
    use DiContainerTrait;
    use NameTrait;
    use SessionTrait;

    /**
     * Cached data expires in X seconds. False to never expire.
     *
     * @var int|false
     */
    public $expireTime = false;

    /**
     * Cache key. Set this if you want to use multiple cache objects at same time.
     *
     * @var string
     */
    public $key;

    /**
     * Timestamp when cache data was set.
     *
     * @int
     */
    protected $setAt = 0;

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
        if (php_sapi_name() !== 'cli') { // helps with unit tests
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
        return $this->key ?? static::class;
    }

    /**
     * Get data from session cache.
     */
    public function getData(): array
    {
        $key = $this->getKey();

        if (!isset($_SESSION[$key])) {
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
        $_SESSION[$this->getKey()] = $data;

        return $this;
    }
}
