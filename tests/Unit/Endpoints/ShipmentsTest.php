<?php

namespace Tests\Unit\Endpoints;

use Tests\TestCase;
use Mvdnbrk\MyParcel\Client;
use Mvdnbrk\MyParcel\Resources\Parcel;
use Mvdnbrk\MyParcel\Resources\Shipment;
use Mvdnbrk\MyParcel\Resources\PickupLocation;
use Mvdnbrk\MyParcel\Resources\ShipmentOptions;

/** @group integration */
class ShipmentsTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->client = new Client;
        $this->client->setApiKey(getenv('API_KEY'));
    }

    private function validRecipient($overrides = [])
    {
        return array_merge([
            'person' => 'John Doe',
            'street' => 'Poststraat',
            'number' => '1',
            'postal_code' => '1234AA',
            'city' => 'Amsterdam',
            'cc' => 'NL',
        ], $overrides);
    }

    /** @test */
    public function create_a_new_shipment_concept_for_a_parcel()
    {
        $array = [
            'reference_identifier' => 'test-123',
            'recipient' => [
                'company' => 'Test Company B.V.',
                'person' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '0101111111',
                'street' => 'Poststraat',
                'number' => '1',
                'number_suffix' => 'A',
                'postal_code' => '1234AA',
                'city' => 'Amsterdam',
                'region' => 'Noord-Holland',
                'cc' => 'NL',
            ],
            'options' => [
                'label_description' => 'Test label description',
                'large_format' => false,
                'only_recipient' => false,
                'package_type' => 1,
                'return' => false,
                'signature' => true,
            ],
        ];

        $parcel = new Parcel($array);

        $shipment = $this->client->shipments->concept($parcel);

        $this->assertInstanceOf(Shipment::class, $shipment);
        $this->assertInstanceOf(ShipmentOptions::class, $shipment->options);
        $this->assertNotNull($shipment->id);
    }

    /** @test */
    public function create_a_new_shipment_for_a_parcel_wich_includes_barcode_and_pdf_label()
    {
        $array = [
            'recipient' => $this->validRecipient(),
        ];

        $parcel = new Parcel($array);

        $shipment = $this->client->shipments->create($parcel);

        // WIP: create the label and set the barcode for the shipment.

        $this->assertInstanceOf(Shipment::class, $shipment);
        $this->assertInstanceOf(ShipmentOptions::class, $shipment->options);
        $this->assertNotNull($shipment->id);
    }

    /** @test */
    public function create_shipment_with_a_pick_up_location()
    {
        $array = [
            'recipient' => $this->validRecipient(),
            'pickup' => [
                'name' => 'Test pick up',
                'street' => 'Pickup street',
                'number' => '1',
                'postal_code' => '9999ZZ',
                'city' => 'Maastricht',
                'cc' => 'NL',
            ],
        ];

        $parcel = new Parcel($array);

        $shipment = $this->client->shipments->create($parcel);

        $this->assertInstanceOf(Shipment::class, $shipment);
        $this->assertInstanceOf(ShipmentOptions::class, $shipment->options);
        $this->assertInstanceOf(PickupLocation::class, $shipment->pickup);
        $this->assertNotNull($shipment->id);
    }
}
