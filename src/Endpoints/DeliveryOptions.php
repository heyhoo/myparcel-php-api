<?php

namespace Mvdnbrk\MyParcel\Endpoints;

use Mvdnbrk\MyParcel\Support\Str;
use Mvdnbrk\MyParcel\Resources\Time;
use Tightenco\Collect\Support\Collection;
use Mvdnbrk\MyParcel\Resources\PickupLocation;
use Mvdnbrk\MyParcel\Exceptions\InvalidPostalCodeException;
use Mvdnbrk\MyParcel\Exceptions\InvalidHousenumberException;

class DeliveryOptions extends BaseEndpoint
{
    /**
     * The carrier from wich to get delivery options.
     */
    const CARRIER = 1;

    /**
     * @var string
     */
    public $postal_code;

    /**
     * @var string
     */
    public $housenumber;

    /**
     * @var  array
     */
    public $validPostalCodes = [
        'BE' => '/^[1-9]{1}\d{3}$/',
        'NL' => '/^[1-9]{1}\d{3}[A-Z]{2}$/',
    ];

    /**
     * @var string
     */
    protected $country;

    /**
     * @var string
     */
    protected $carrier = self::CARRIER;

    /**
     * @var \Mvdnbrk\MyParcel\Resources\Time
     */
    protected $cutoffTime;

    /**
     * Boot the DeliveryOptions endpoint.
     *
     * @return void
     */
    protected function boot()
    {
        $this->cutoffTime = new Time('15:30');
        $this->pickup = collect();
    }

    /**
     * Get delivery options for an address based on postal code and house number.
     *
     * @param  string $postal_code
     * @param  int  $housenumber
     * @param  array  $filters
     * @return \Tightenco\Collect\Support\Collection
     */
    public function get($postal_code, $housenumber, array $filters = [])
    {
        $this->setPostalCode($postal_code);
        $this->setHousenumber($housenumber);

        $response = $this->performApiCall(
            'GET',
            'delivery_options' . $this->buildQueryString($this->getFilters($filters))
        );

        $collection = new Collection();

        collect($response->data->pickup)->each(function ($item) use ($collection) {
            $collection->push(new PickupLocation($item));
        });

        return $collection;
    }

    /**
     * Get the country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Get query filters.
     *
     * @param  array  $filters
     * @return array
     */
    protected function getFilters($filters)
    {
        return array_merge([
            'cc' => $this->getCountry(),
            'carrier' => $this->carrier,
            'number' => $this->housenumber,
            'postal_code' => $this->postal_code,
            'cutoff_time' => $this->cutoffTime->get(),
        ], $filters);
    }

    /**
     * Sets the country based on a postal code.
     *
     * @return  void
     */
    protected function setCountry()
    {
        $postalCodes = collect($this->validPostalCodes);

        $postalCodes->each(function ($value, $key) {
            if (preg_match($value, $this->postal_code)) {
                $this->country = $key;
            }
        });
    }

    /**
     * Sets the cut off time.
     *
     * @param string  $value
     * @return  $this
     */
    public function setCutoffTime($value)
    {
        $this->cutoffTime = new Time($value);

        return $this;
    }

    /**
     * Sets the house number.
     *
     * @param int  $value
     * @return $this
     * @throws \Mvdnbrk\MyParcel\Exceptions\InvalidHousenumberException
     */
    public function setHousenumber($value)
    {
        $this->housenumber = filter_var($value, FILTER_SANITIZE_NUMBER_INT);

        if (! is_numeric($this->housenumber)) {
            throw new InvalidHousenumberException;
        }

        return $this;
    }

    /**
     * Sets the postal code.
     *
     * @param string  $value
     * @return  $this
     * @throws \Mvdnbrk\MyParcel\Exceptions\InvalidPostalCodeException
     */
    public function setPostalCode($value)
    {
        $this->postal_code = preg_replace('/\s+/', '', Str::upper($value));
        $this->setCountry();

        if ($this->country === null) {
            throw new InvalidPostalCodeException;
        }

        return $this;
    }
}
