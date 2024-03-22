<?php

declare(strict_types=1);

namespace Atk4\Login\Cache;

use Atk4\Core\AppScopeTrait;
use Atk4\Core\DiContainerTrait;
use Atk4\Core\NameTrait;
use Atk4\Ui\App;
use Atk4\Ui\SessionTrait;

class Session // implements CacheInterface
{
    use AppScopeTrait;
    use DiContainerTrait;
    use NameTrait;
    use SessionTrait;

    /** @var float|false Cached data expires in X seconds. False to never expire. */
    public $expireTime = false;

    /** @var string|null Cache key. Set this if you want to use multiple cache objects at same time. */
    public $key;

    public function __construct(App $app, array $options = [])
    {
        $this->setApp($app);
        $this->name = '__atk4_login';

        $this->setDefaults($options);
    }

    /**
     * Return cache key.
     */
    public function getKey(): string
    {
        return static::class . ':' . ($this->key ?? $this->name);
    }

    /**
     * Get data from session cache.
     */
    public function getData(): array
    {
        $key = $this->getKey();
        $data = $this->recall($key);
        if ($data === null || ($this->expireTime && $this->recall($key . '-at') + $this->expireTime < microtime(true))) {
            $data = [];
        }

        return $data;
    }

    /**
     * Store data in session cache.
     *
     * @return $this
     */
    public function setData(array $data)
    {
        $key = $this->getKey();
        $this->memorize($key, $data);
        $this->memorize($key . '-at', microtime(true));

        return $this;
    }
}
