<?php
declare(strict_types=1);

namespace Plus54\CloudFlareAccess\Testing;
use PHPUnit\Framework\TestCase;
use Plus54\CloudFlareAccess\Service\TokenValidator;

final class TokenValidatorTest extends TestCase
{

    public function testProducerPublicKeys(string $url) : array
    {
        $tokenValidator = new TokenValidator($curl, $json);

    }
    public function testGetPublicKeys(): void
    {
        $this->assertSame(expected: )
    }
}