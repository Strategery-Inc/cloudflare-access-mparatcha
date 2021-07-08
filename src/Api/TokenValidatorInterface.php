<?php

namespace Plus54\CloudFlareAccess\Api;

interface TokenValidatorInterface
{
    /**
     * @param $token
     * @param $url
     * @param $algorithm
     * @return object
     */
    public function validateToken(string $token): object;
}
