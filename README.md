## Warp Wallet Cracker ##

This is a simple PHP class (WarpWallet.php) and accompanying PHP script (run.php) to demonstrate how anyone could (try to) brute-force the [Warpwallet challenge](https://keybase.io/warp/).
Written to look identical to [nachowski/warpwallet_cracker](https://github.com/nachowski/warpwallet_cracker) when running.

In PHP it uses a PHP wrapper for the [C implementation of Scrypt](https://github.com/Tarsnap/scrypt) and some boilerplate for [PBKDF2](https://defuse.ca/php-pbkdf2.htm).

#### Usage ####

###### Requirements:

- PHP 7.1, I think...
- git

###### Step 1: duh
> git clone https://github.com/mrfu2/warpwallet_cracker.git

###### Step 2: Install [PHP Scrypt](https://github.com/DomBlack/php-scrypt/blob/master/scrypt.php)
I think using the PECL install method is the easiest approach.

###### Step 3: Download PHP dependencies
> composer install

###### Step 4: Run the thing
> php run.php

###### Optional:
If you want to run it with different parameters, simply
> php run.php 1GjjGLYR7UhtM1n6z7QDpQskBicgmsHW9k mySalt

(Or encapsulate the strings in quotes, in case your OS is giving you trouble)
> php run.php "1GjjGLYR7UhtM1n6z7QDpQskBicgmsHW9k" "someSalt"

#### Possible improvements ####

- Use [Brainflayer](https://github.com/ryancdotorg/brainflayer) to brute-force the warpwallet challenge...
- Use Scrypt-Jane for improved Scrypt performance (since it can also leverage SSE2 instructions in a CPU, if available)
- Use PHP threading, yes that's a thing. You do however need a [threadsafe build of PHP](https://stackoverflow.com/questions/1623914/what-is-thread-safe-or-non-thread-safe-in-php), [PThreads](https://secure.php.net/manual/en/book.pthreads.php) installed on your machine and [a PHP library](https://github.com/krakjoe/pthreads) for that. With lots of compiler flags and so much more fun. (Hence, I haven't gotten to that yet.)
- Don't generate random strings, but iterate from 0 to 8^62, seed the PHP random ([srand](https://secure.php.net/manual/en/function.srand.php)) and then generate deterministic 'random' 8-character phrases, to prevent double tries. But you know, the math kind of makes that possibility tiny anyhow.
- [Buy 8 Nvidia 1080 GPU's, install HashCat](https://www.shellntel.com/blog/2017/2/8/how-to-build-a-8-gpu-password-cracker) and warm up the house.
- Learn a real programming language, like Rust or something.
...
Profit?

#### Performance ####

On __my__ Macbook Pro (MacOS High Sierra 10.13.1, model Mid 2015, 2.2 GHz Intel Core i7, 16GB RAM, SSD) this achieves ~1.4 hashes/sec, obviously on a single core because PHP.
So uh, if someone could explain to me why it is faster (or... looks like it is faster) than the Go implementation, please let me know.

(If I run the Warpwallet_cracker written in Go on a 32GB Intel Xeon E3-1270v6 8-core, I only get 1.41 hashes/second, so...)

### But, why... ? ####

I wrote this little thing because sometimes I get bored in the weekends.
Just remember, it could be worse... This could have been a javascript library...

##### OK Thnx Bye #####