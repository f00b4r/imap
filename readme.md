# Imap

[![Downloads total](https://img.shields.io/packagist/dt/minetro/imap.svg?style=flat)](https://packagist.org/packages/minetro/imap)
[![Latest stable](https://img.shields.io/packagist/v/minetro/imap.svg?style=flat)](https://packagist.org/packages/minetro/imap)

Simple IMAP wrapper.

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


