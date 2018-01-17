<?php

require_once __DIR__ . '/vendor/autoload.php';

use BitWasp\Bitcoin\Key\PrivateKeyFactory;

class WarpWallet
{
  const SCRYPT_CONCATENATION_BYTE = "\x1";
  const SCRYPT_ITERATION_COUNT = 262144; // N = 2^18 (1<<18)
  const SCRYPT_CPU_DIFFICULTY = 8; // r
  const SCRYPT_PARALLEL_DIFFICULTY = 1; // p
  const SCRYPT_KEY_LENGTH = 32; // keyLength
  const PBKDF2_ITERATION_COUNT = 65536; // 2^16 (1<<16)
  const PBKDF2_CONCATENATION_BYTE = "\x2";

  /**
   * @param string $passPhrase
   * @param string $salt
   * @return string
   */
  public static function getPBKDF2( string $passPhrase, string $salt ) : string
  {
    return self::pbkdf2( 'sha256', $passPhrase . self::PBKDF2_CONCATENATION_BYTE, $salt . self::PBKDF2_CONCATENATION_BYTE, self::PBKDF2_ITERATION_COUNT, 32 );
  }

  /**
   * @param string $algorithm
   * @param string $password
   * @param string $salt
   * @param int $count
   * @param int $keyLength
   * @param bool $rawOutput
   * @return string
   */
  public static function pbkdf2( string $algorithm, string $password, string $salt, int $count, int $keyLength, bool $rawOutput = false ) : string
  {
    $algorithm = strtolower( $algorithm );
    if( !in_array( $algorithm, hash_algos(), true ) )
    {
      trigger_error( 'PBKDF2 ERROR: Invalid hash algorithm.', E_USER_ERROR );
    }
    if( $count <= 0 || $keyLength <= 0 )
    {
      trigger_error( 'PBKDF2 ERROR: Invalid parameters.', E_USER_ERROR );
    }

    if( function_exists( 'hash_pbkdf2' ) )
    {
      // The output length is in NIBBLES (4-bits) if $raw_output is false!
      if( !$rawOutput )
      {
        $keyLength = $keyLength * 2;
      }
      return hash_pbkdf2( $algorithm, $password, $salt, $count, $keyLength, $rawOutput );
    }

    $hash_length = strlen( hash( $algorithm, '', true ) );
    $block_count = ceil( $keyLength / $hash_length );

    $output = '';
    for( $i = 1; $i <= $block_count; $i++ )
    {
      // $i encoded as 4 bytes, big endian.
      $last = $salt . pack( 'N', $i );
      // first iteration
      $last = $xorsum = hash_hmac( $algorithm, $last, $password, true );
      // perform the other $count - 1 iterations
      for( $j = 1; $j < $count; $j++ )
      {
        $xorsum ^= ( $last = hash_hmac( $algorithm, $last, $password, true ) );
      }
      $output .= $xorsum;
    }

    if( $rawOutput )
    {
      return substr( $output, 0, $keyLength );
    }
    else
    {
      return bin2hex( substr( $output, 0, $keyLength ) );
    }
  }

  /**
   * @param string $passPhrase
   * @param string $salt
   * @return string
   */
  public static function generateBitcoinKeypair( string $passPhrase, string $salt, bool $showAddress = true , bool $showPrivateKey = false ) : string
  {
    $s1 = self::getScrypt( $passPhrase, $salt );
    $s2 = self::getPBKDF2( $passPhrase, $salt );
    $s3 = self::stringXor( $s1, $s2 );
    $privateKey = PrivateKeyFactory::fromHex( $s3 );
    return ( $showPrivateKey ? ( $privateKey->toWif() . ' ' ) : '' ) . ( $showAddress ? $privateKey->getAddress()->getAddress() : '' );
  }

  /**
   * @param string $input
   * @return string
   */
  public static function hexlify( string $input ) : string
  {
    // ARRAYS STARTING AT 1 !! (╯°□°）╯︵ ┻━┻
    return unpack( 'H*', $input )[ 1 ];
  }

  /**
   * @param string $input
   * @return string
   */
  public static function unhexlify( string $input ) : string
  {
    return pack( 'H*', $input );
  }

  /**
   * @param string $s1
   * @param string $s2
   * @return string
   */
  public static function stringXor( string $s1, string $s2 ) : string
  {
    if( empty( $s2 ) )
    {
      return $s1;
    }
    $s1 = self::unhexlify( $s1 );
    $s2 = self::unhexlify( $s2 );
    return self::hexlify( $s1 ^ $s2 );
  }

  /**
   * Uses a PHP wrapper for the C implementation of Scrypt.
   * @see https://github.com/DomBlack/php-scrypt
   * @param string $passPhrase
   * @param string $salt
   * @return string
   */
  public static function getScrypt( string $passPhrase, string $salt ) : string
  {
    return scrypt( $passPhrase . self::SCRYPT_CONCATENATION_BYTE, $salt . self::SCRYPT_CONCATENATION_BYTE, self::SCRYPT_ITERATION_COUNT, self::SCRYPT_CPU_DIFFICULTY, self::SCRYPT_PARALLEL_DIFFICULTY, self::SCRYPT_KEY_LENGTH );
  }

  public static function getRandomString( int $nrOfChars ) : string
  {
    $chars = [
      '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
      'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
      'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
    ];

    $charsCount = 61; // 61 - 1 to make it 0 indexed !

    $returnWord = '';
    for( $i = 0; $i < $nrOfChars; $i++ )
    {
      $returnWord .= $chars[ rand( 0, $charsCount ) ];
    }

    return $returnWord;
  }
}