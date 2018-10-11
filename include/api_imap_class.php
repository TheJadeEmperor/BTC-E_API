<?php
class EmailImporter
{

    protected $hostname;
    protected $username;
    protected $password;
    protected $connection;
    protected $result = [];

    /**
     * EmailImporter constructor.
     * @param $hostname
     * @param $username
     * @param $password
     */
    public function __construct($hostname, $username, $password)
    {
        $this->hostname = $hostname;
        $this->hostname = $username;
        $this->hostname = $password;
        $this->connection = imap_open($hostname, $username, $password) or die('Cannot connect to Email: ' . imap_last_error());
		echo 'Connected to Email '.$username;
    }

    /**
     * @param $email
     * @param bool $printable
     * @return array
     * Will fetch unseen email coming from specific emails
     */
    public function getMailsReceivedFrom($email, $printable = false)
    {
        $emails = imap_search($this->connection, 'UNSEEN FROM "' . $email . '"');
        return $this->decodeMails($emails, $printable);
    }

    /**
     * @param $email
     * @param bool $printable
     * @return array
     * Will fetch email sent to specific email
     */
    public function getMailsSentTo($email, $printable = false)
    {
        $emails = imap_search($this->connection, 'TO "' . $email . '"');
        return $this->decodeMails($emails, $printable);
    }

    /**
     * @param $subject
     * @param bool $printable
     * @return array
     * Will fetch unseen emails from specific subject
     */
    public function getMailsBySubject($subject, $printable = false)
    {
        $emails = imap_search($this->connection, 'UNSEEN SUBJECT "' . $subject . '"');
        return $this->decodeMails($emails, $printable);
    }

    /**
     * @param $keyword
     * @param bool $printable
     * @return array
     * Will fetch email matches the keywords
     */
    public function getMailsByKeyword($keyword, $printable = false)
    {
        $emails = imap_search($this->connection, 'KEYWORD "' . $keyword . '"');
        return $this->decodeMails($emails, $printable);
    }

    /**
     * @param bool $printable
     * @return array
     * Will fetch all unseen emails
     */
    public function getMailsUnseen($printable = false)
    {
        $emails = imap_search($this->connection, 'UNSEEN');
        return $this->decodeMails($emails, $printable);
    }

    /**
     * @param bool $printable
     * @return array
     * Will fetch all seen and useen emails from inbox
     */
    public function getAllEmails($printable = false)
    {
        $emails = imap_search($this->connection, 'ALL');
        return $this->decodeMails($emails, $printable);
    }

    /**
     * @param array $email_ids
     * @param $printable
     * @return array
     * Will make emails readable and mark them seen
     */
    protected function decodeMails($email_ids = [], $printable)
    {
        if ($email_ids) {
            $output = [];
            rsort($email_ids);
            foreach ($email_ids as $key => $email_number) {
                $overview = imap_fetch_overview($this->connection, $email_number, 0);
                $output['subject'] = $overview[0]->subject;
                $output['from'] = $overview[0]->from;
                $output['date'] = $overview[0]->date;
                $output['text_msg_body'] = imap_fetchtext($this->connection, $email_number, 2);
                if ($printable) {
                    $output['html_msg_body'] = quoted_printable_decode(imap_fetchbody($this->connection, $email_number, 2));
                } else {
                    $output['html_msg_body'] = imap_fetchbody($this->connection, $email_number, 2);
                }
                imap_setflag_full($this->connection,imap_uid($this->connection,$email_number), '\\Seen',ST_UID);
                $this->result[$key] = $output;
            }
            return $this->result;
        } else {
            return $this->result;
        }
    }

}