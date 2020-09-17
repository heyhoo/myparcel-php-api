<?php

namespace Mvdnbrk\MyParcel\Resources;

use JsonSerializable;
use Mvdnbrk\MyParcel\Contracts\Arrayable;
use Mvdnbrk\MyParcel\Contracts\Jsonable;
use Mvdnbrk\MyParcel\Exceptions\JsonEncodingException;

abstract class BaseResource implements Arrayable, Jsonable, JsonSerializable
{
    use Concerns\HasAttributes;

    /**
     * Create a new resource instance.
     *
     * @param  array|object  $attributes
     */
    public function __construct($attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Fill the resource with an array of attributes.
     *
     * @param  array|object  $attributes
     * @return $this
     */
    public function fill($attributes): self
    {
        collect($attributes)->each(function ($value, $key) {
            $this->setAttribute($key, $value);
        });

        return $this;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return collect($this->attributesToArray())
            ->reject(function ($value) {
                return $value === null;
            })
            ->all();
    }

    /**
     * @throws \Mvdnbrk\MyParcel\Exceptions\JsonEncodingException
     */
    public function toJson(int $options = 0): string
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw JsonEncodingException::forResource($this, json_last_error_msg());
        }

        return $json;
    }

    /**
     * Dynamically retrieve attributes on the resource.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the resource.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }
}
