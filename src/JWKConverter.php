<?php

namespace UaiRango\JWKToPEM;

use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;
use UaiRango\JWKToPEM\Util\Base64UrlDecoder;
use UaiRango\JWKToPEM\Exception\JWKConverterException;

class JWKConverter
{

    /** @var Base64UrlDecoder */
    private $base64UrlDecoder;

    public function __construct(Base64UrlDecoder $base64UrlDecoder = null)
    {
        $this->base64UrlDecoder = $base64UrlDecoder ? $base64UrlDecoder : new Base64UrlDecoder();
    }

    /**
     * @param array $jwkSet
     * @return string[]
     * @throws JWKConverterException
     * @throws Exception\Base64DecodeException
     */
    public function multipleToPem(array $jwkSet)
    {
        $keys = [];

        foreach($jwkSet as $jwk) {
            if(!is_array($jwk)) {
                throw new JWKConverterException('`multipleToPem` can only take in an array of JWKs.');
            }

            $keys[] = $this->toPEM($jwk);
        }

        return $keys;
    }

    /**
     * @param array $jwk
     * @return string
     * @throws Exception\Base64DecodeException
     * @throws JWKConverterException
     */
    public function toPEM(array $jwk)
    {
        if (!array_key_exists('e', $jwk) || !array_key_exists('n', $jwk) || !array_key_exists('kty', $jwk)) {
            throw new JWKConverterException();
        }

        if ($jwk['kty'] != 'RSA') {
            throw new JWKConverterException('RSA key type is currently only supported.');
        }

        if (array_key_exists('d', $jwk)) {
            throw new JWKConverterException('Public key is currently only supported.');
        }

        $rsa = new RSA();
        $rsa->loadKey(
            [
                'e' => new BigInteger(base64_decode($jwk['e']), 256),
                'n' => new BigInteger($this->base64UrlDecoder->decode($jwk['n']), 256)
            ]
        );
        
        return $rsa->getPublicKey();
    }

}