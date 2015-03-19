# Imap

[![Build Status](https://travis-ci.org/minetro/imap.svg?branch=master)](https://travis-ci.org/minetro/imap)
[![Downloads this Month](https://img.shields.io/packagist/dm/minetro/imap.svg?style=flat)](https://packagist.org/packages/minetro/imap)
[![Latest stable](https://img.shields.io/packagist/v/minetro/imap.svg?style=flat)](https://packagist.org/packages/minetro/imap)
[![HHVM Status](https://img.shields.io/hhvm/minetro/imap.svg?style=flat)](http://hhvm.h4cc.de/package/minetro/imap)

Simple IMAP wrapper.

## Install
```sh
$ composer require minetro/forms:~1.0.0
```

## Usage

```php
use Minetro\Imap\ImapReader;

$reader = new ImapReader('{yourdomain.cz:143/imap}INBOX', $username, $password);
$emails = $reader->read(ImapReader::CRITERIA_UNSEEN);

// Iterate all emails
foreach ($emails as $email) {
    
    // Iterate all email parts
    for ($i = 0; $i < $email->countBodies(); $i++) {
        
        // Get text (encode with right encoding..)
        $text = $email->getBodySectionText($i);
        
        echo $text;
    }
}
```


