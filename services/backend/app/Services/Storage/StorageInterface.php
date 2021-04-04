<?php

namespace app\Services\Storage;

interface StorageInterface
{
    public function get(string $key);

    public function put(string $key, $body, array $parameters = []);

    public function delete(string $key);

    public function url(string $key);

    public function exists(string $key);
}
