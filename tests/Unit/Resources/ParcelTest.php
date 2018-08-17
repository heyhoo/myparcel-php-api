<?php

namespace Tests\Unit\Resources;

use Tests\TestCase;
use Mvdnbrk\MyParcel\Resources\Parcel;

class ParcelTest extends TestCase
{
    /** @test */
    public function create_a_new_parcel()
    {
        $parcel = new Parcel([
            'reference_identifier' => 'test-123',
            'recipient' => [
                'company' => 'Test Company B.V.',
                'person' => 'John Doe',
            ],
            'options' => [
                'description' => 'Test label description',
                'signature' => 1,
            ],
        ]);

        $this->assertEquals('test-123', $parcel->reference_identifier);
        $this->assertEquals('Test Company B.V.', $parcel->recipient->company);
        $this->assertEquals('John Doe', $parcel->recipient->person);
        $this->assertEquals('Test label description', $parcel->options->label_description);
        $this->assertSame(1, $parcel->options->signature);
    }

    /** @test */
    public function reference_may_be_used_as_an_alias_to_reference_identifier()
    {
        $parcel = new Parcel([
            'reference' => 'test-123',
        ]);

        $this->assertEquals('test-123', $parcel->reference_identifier);
        $this->assertEquals('test-123', $parcel->reference);
    }

    /** @test */
    public function to_array_test()
    {
        $array = [
            'carrier' => 1,
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
                'delivery_type' => 2,
                'label_description' => 'Test label description',
                'large_format' => 0,
                'only_recipient' => 0,
                'package_type' => 1,
                'return' => 0,
                'signature' => 1,
            ],
        ];

        $parcel = new Parcel($array);

        $this->assertSame($array, $parcel->toArray());
    }
}