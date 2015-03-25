<?php

namespace Minetro\Imap;

use Nette\InvalidArgumentException;
use stdClass;

/**
 * Imap Message
 *
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
class ImapMessage
{

    // Message flags
    const FLAG_SEEN = '\\Seen';
    const FLAG_ANSWERED = '\\Answered';
    const FLAG_FLAGGED = '\\Flagged';
    const FLAG_DELETED = '\\Deleted';
    const FLAG_DRAFT = '\\Draft';

    /** @var int */
    private $number;

    /** @var stdClass */
    private $headers;

    /** @var stdClass */
    private $structure;

    /** @var array */
    private $body = [];

    /**
     * @param int $number
     * @param stdClass $headers
     * @param stdClass $structure
     * @param array $body
     */
    function __construct($number, $headers, $structure, array $body)
    {
        $this->number = $number;

        // Convert all headers to UTF-8
        $this->headers = $this->utf8($headers);

        $this->structure = $structure;
        $this->body = $body;
    }

    /**
     * GETTERS *****************************************************************
     * *************************************************************************
     */

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return stdClass
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return stdClass
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param int $section
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getBodySection($section)
    {
        if ($section > count($this->body) || !isset($this->body[$section])) {
            throw new InvalidArgumentException('Section #' . $section . ' not found.');
        }

        return $this->body[$section];
    }

    /**
     * Returns text of the e-mail converted to utf-8.
     *
     * @param int $section
     * @param int $encoding [optional]
     * @return string
     */
    public function getBodySectionText($section, $encoding = NULL)
    {
        $text = $this->getBodySection($section);
        $encoding = $encoding ? $encoding : (isset($this->structure->parts[$section]) ? $this->structure->parts[$section]->encoding : $this->structure->encoding);

        switch ($encoding) {
            # 7BIT
            case 0:
                $etext = $text;
                break;
            # 8BIT
            case 1:
                $etext = quoted_printable_decode(imap_8bit($text));
                break;
            # BINARY
            case 2:
                $etext = imap_binary($text);
                break;
            # BASE64
            case 3:
                $etext = imap_base64($text);
                break;
            # QUOTED-PRINTABLE
            case 4:
                $etext = quoted_printable_decode($text);
                break;
            # OTHER
            case 5:
                $etext = $text;
                break;
            # UNKNOWN
            default:
                $etext = $text;
        }

        $charset = $this->getBodyCharset($section);
        $charset = $charset ?: mb_detect_encoding($etext, mb_detect_order(), TRUE);
        if ($charset === FALSE) {
            return $etext;
        } else {
            return iconv($charset, "UTF-8//TRANSLIT", $etext);
        }
    }

    /**
     * Returns charset defined in e-mail headers.
     *
     * @return string|NULL
     */
    public function getBodyCharset()
    {
        foreach ($this->structure->parameters as $pair) {
            if (isset($pair->attribute) && $pair->attribute == 'charset') {
                return $pair->value;
            }
        }
        return NULL;
    }

    /**
     * @return int
     */
    public function countBodies()
    {
        return count($this->body);
    }


    /**
     * HELPERS *****************************************************************
     * *************************************************************************
     */

    /**
     * @param mixed $data
     * @return stdClass
     */
    private function utf8($data)
    {
        $array = json_decode(json_encode($data), TRUE);
        array_walk_recursive($array, function ($v, $k) {
            return is_array($v) ? $v : imap_utf8($v);
        });

        return json_decode(json_encode($array));
    }
}