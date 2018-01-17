<?php

// Remove the script time limit.
set_time_limit( 0 );

require_once __DIR__ . '/WarpWallet.php';

$address = $argv[ 1 ] ?? '1MkupVKiCik9iyfnLrJoZLx9RH4rkF3hnA';
$salt = $argv[ 2 ] ?? 'a@b.c';

echo 'Using address "' . $address . '" and salt "' . $salt . '".' . PHP_EOL . PHP_EOL;

$startTime = microtime( true );

$totalTries = 0;

while( true )
{
  $totalTries++;
  $lastTime = microtime( true );
  $hashesPerSecond = ( $totalTries / ( $lastTime - $startTime ) ) ?? 0;

  $lastRandomPhrase = WarpWallet::getRandomString( 8 );
  // The heavy part
  $resultingAddress = WarpWallet::generateBitcoinKeypair( $lastRandomPhrase, $salt );

  if( $resultingAddress === $address )
  {
    echo 'Found the warpwallet challenge phrase: ' . $lastRandomPhrase . PHP_EOL . PHP_EOL;
  }

  echo "\r\033[K\033[1A\r\033[K\r"; // Clears the last printed line and moves it up 1 line.
  echo sprintf( 'speed=%01.2fh/s, last=%s, tries=%d, elapsed=%01.2fs', $hashesPerSecond, $lastRandomPhrase, $totalTries, $lastTime - $startTime ) . PHP_EOL;
}