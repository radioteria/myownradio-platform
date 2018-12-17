<?php

namespace app\Services\Storage;

interface StorageInterface
{
    public function get($key);

    public function put($key, $body, array $parameters = []);

    public function delete($key);

    public function url($key);

    public function exists($key);
}
