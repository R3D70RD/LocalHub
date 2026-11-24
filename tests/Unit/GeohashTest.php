<?php

namespace Tests\Unit;

use App\Support\Geohash;
use PHPUnit\Framework\TestCase;

class GeohashTest extends TestCase
{
    public function test_encode_une_valeur_de_reference(): void
    {
        $this->assertSame('u4pruydqqvj', Geohash::encode(57.64911, 10.40744, 11));
    }

    public function test_encode_respecte_la_precision_demandee(): void
    {
        $this->assertSame(7, strlen(Geohash::encode(45.5019, -73.5674, 7)));
    }

    public function test_la_precision_est_bornee(): void
    {
        $this->assertSame(1, strlen(Geohash::encode(45.5, -73.5, 0)));
        $this->assertSame(12, strlen(Geohash::encode(45.5, -73.5, 99)));
    }
}
