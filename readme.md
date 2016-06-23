# Imap

Simple IMAP wrapper.

-----

[![Downloads total](https://img.shields.io/packagist/dt/minetro/imap.svg?style=flat-square)](https://packagist.org/packages/minetro/imap)
[![Latest stable](https://img.shields.io/packagist/v/minetro/imap.svg?style=flat-square)](https://packagist.org/packages/minetro/imap)

## Discussion / Help

[![Join the chat](https://img.shields.io/gitter/room/minetro/nette.svg?style=flat-square)](https://gitter.im/minetro/nette?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

## Install

```sh
$ composer require minetro/imap:~1.1.0
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
