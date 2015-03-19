<?php

namespace Minetro\Imap;

/**
 * Imap Reader
 *
 * @author Milan Felix Sulc <sulcmil@gmail.com>
 */
final class ImapReader
{

    // Message criteria
    const CRITERIA_ALL = 'ALL'; // Return all messages matching the rest of the criteria
    const CRITERIA_ANSWERED = 'ANSWERED'; // Match messages with the \\ANSWERED flag set
    const CRITERIA_BCC = 'BCC';  // Match messages with "string" in the Bcc: field
    const CRITERIA_BEFORE = 'BEFORE';  // Match messages with Date: before "date"
    const CRITERIA_BODY = 'BODY';  // Match messages with "string" in the body of the message
    const CRITERIA_CC = 'CC';  // Match messages with "string" in the Cc: field
    const CRITERIA_DELETED = 'DELETED'; // Match deleted messages
    const CRITERIA_FLAGGED = 'FLAGGED'; // Match messages with the \\FLAGGED (sometimes referred to as Important or Urgent) flag set
    const CRITERIA_FROM = 'FROM';  // Match messages with "string" in the From: field
    const CRITERIA_KEYWORD = 'KEYWORD';  // Match messages with "string" as a keyword
    const CRITERIA_NEW = 'NEW'; // Match new messages
    const CRITERIA_OLD = 'OLD'; // Match old messages
    const CRITERIA_ON = 'ON';  // Match messages with Date: matching "date"
    const CRITERIA_RECENT = 'RECENT'; // Match messages with the \\RECENT flag set
    const CRITERIA_SEEN = 'SEEN'; // Match messages that have been read (the \\SEEN flag is set)
    const CRITERIA_SINCE = 'SINCE'; // Match messages with Date: after "date"
    const CRITERIA_SUBJECT = 'SUBJECT';  // Match messages with "string" in the Subject:
    const CRITERIA_TEXT = 'TEXT'; // Match messages with teconst CRITERIA_t = 't'; "string"
    const CRITERIA_TO = 'TO'; // Match messages with "string" in the To:
    const CRITERIA_UNANSWERED = 'UNANSWERED'; // Match messages that have not been answered
    const CRITERIA_UNDELETED = 'UNDELETED'; // Match messages that are not deleted
    const CRITERIA_UNFLAGGED = 'UNFLAGGED'; // Match messages that are not flagged
    const CRITERIA_UNKEYWORD = 'UNKEYWORD'; // Match messages that do not have the keyword "string"
    const CRITERIA_UNSEEN = 'UNSEEN'; // Match messages which have not been read yet

    /** @var resource */
    private $imap;

    /** @var string */
    private $mailbox;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /**
     * Create IMAP resource
     *
     * @param string $mailbox
     * @param string $username
     * @param string $password
     */
    function __construct($mailbox, $username, $password)
    {
        $this->mailbox = $mailbox;
        $this->username = $username;
        $this->password = $password;

        $this->connect($mailbox, $username, $password);
    }

    /**
     * Close IMAP resource
     *
     * @return void
     */
    function __destruct()
    {
        $this->disconnect();
    }

    /**
     * API *********************************************************************
     * *************************************************************************
     */

    /**
     * @return bool
     */
    public function isAlive()
    {
        return (bool)@imap_ping($this->imap);
    }

    /**
     * @param string $criteria
     * @param int $options
     * @return ImapMessage[]
     */
    public function read($criteria = 'ALL', $options = SE_FREE)
    {
        $mails = imap_search($this->imap, $criteria, $options);
        if (!$mails) return [];

        $emails = [];
        foreach ($mails as $mailnum) {
            $structure = imap_fetchstructure($this->imap, $mailnum);
            $headers = imap_headerinfo($this->imap, $mailnum);
            if (!$structure || !$headers) continue;

            $sections = [];
            if (isset($structure->parts)) {
                foreach ($structure->parts as $partnum => $part) {
                    $sections[] = imap_fetchbody($this->imap, $mailnum, $part);
                }
            } else {
                $sections[] = imap_body($this->imap, $mailnum);
            }

            $emails[] = new ImapMessage($mailnum, $headers, $structure, $sections);
        }

        return $emails;
    }

    /**
     * Change IMAP reading folder
     *
     * @param string $folder
     * @return void
     */
    public function folder($folder)
    {
        // @todo
    }

    /**
     * Set message flag
     *
     * @param string|array $sequence Message numbers
     * @param string|array $flag Message flag
     * @return bool
     */
    public function flag($sequence, $flag)
    {
        $sequence = is_array($sequence) ? implode(',', $sequence) : $sequence;
        $flag = is_array($flag) ? implode(' ', $flag) : $flag;
        return imap_setflag_full($this->imap, $sequence, $flag);
    }

    /**
     * Unset message flag
     *
     * @param string|array $sequence Message numbers
     * @param string|array $flag Message flag
     * @return bool
     */
    public function unflag($sequence, $flag)
    {
        $sequence = is_array($sequence) ? implode(',', $sequence) : $sequence;
        $flag = is_array($flag) ? implode(' ', $flag) : $flag;
        return imap_clearflag_full($this->imap, $sequence, $flag);
    }

    /**
     * MAGIC *******************************************************************
     * *************************************************************************
     */

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    function __call($name, $arguments)
    {
        $args = $arguments;
        array_unshift($args, $this->imap);
        return call_user_func_array('imap_' . $name, $args);
    }

    /**
     * HELPERS *****************************************************************
     * *************************************************************************
     */

    /**
     * Connect to IMAP mailbox
     *
     * @return void
     */
    protected function connect()
    {
        $this->imap = imap_open($this->mailbox, $this->username, $this->password);
    }

    /**
     * Disconnect to IMAP mailbox
     *
     * @return void
     */
    protected function disconnect()
    {
        imap_close($this->imap);
    }
}