<?php
/**
 * Message.php 2014-08-31 04:50
 * ----------------------------------------------
 *
 *
 * @author      Stanislav Kiryukhin <korsar.zn@gmail.com>
 * @copyright   Copyright (c) 2014-2021
 *
 * ----------------------------------------------
 * All Rights Reserved.
 * ----------------------------------------------
 */
namespace Phalcon\Mailer;

/**
 * Class Message
 */
class Message
{
    /**
     * content type of PLAIN text.
     */
    const CONTENT_TYPE_PLAIN = 'text/plain';

    /**
     * content type HTML text.
     */
    const CONTENT_TYPE_HTML = 'text/html';

    /**
     * @var \Phalcon\Mailer\Manager
     */
    protected $manager;

    /**
     * @var \Swift_Message
     */
    protected $swiftMessage;

    /**
     * An array of email which failed send to recipients.
     *
     * @var array
     */
    protected $failedRecipients = [];

    /**
     * Header for table
     * @var string
     */
    protected $header = '';

    /**
     * Create a new Message using $mailer for sending from SwiftMailer
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Set the from address of this message.
     *
     * You may pass an array of addresses if this message is from multiple people.
     * Example: array('receiver@domain.org', 'other@domain.org' => 'A name')
     *
     * If $name is passed and the first parameter is a string, this name will be
     * associated with the address.
     *
     * @param string|array $email
     * @param string|null $name optional
     *
     * @return $this
     *
     * @see \Swift_Message::setFrom()
     */
    public function from($email, $name = null)
    {
        $email = $this->normalizeEmail($email);
        $this->getSwiftMessage()->setFrom($email, $name);

        return $this;
    }

    /**
     * Get the from address of this message.
     *
     * @return string
     *
     * @see \Swift_Message::getFrom()
     */
    public function getFrom()
    {
        return $this->getSwiftMessage()->getFrom();
    }

    /**
     * Set the reply-to address of this message.
     *
     * You may pass an array of addresses if replies will go to multiple people.
     * Example: array('receiver@domain.org', 'other@domain.org' => 'A name')
     *
     * If $name is passed and the first parameter is a string, this name will be
     * associated with the address.
     *
     * @param string|array $email
     * @param string|null $name optional
     *
     * @return $this
     *
     * @see \Swift_Message::setReplyTo()
     */
    public function replyTo($email, $name = null)
    {
        $email = $this->normalizeEmail($email);
        $this->getSwiftMessage()->setReplyTo($email, $name);

        return $this;
    }

    /**
     * Get the reply-to address of this message.
     *
     * @return string
     *
     * @see \Swift_Message::getReplyTo()
     */
    public function getReplyTo()
    {
        return $this->getSwiftMessage()->getReplyTo();
    }

    /**
     * Set the to addresses of this message.
     *
     * If multiple recipients will receive the message an array should be used.
     * Example: array('receiver@domain.org', 'other@domain.org' => 'A name')
     *
     * If $name is passed and the first parameter is a string, this name will be
     * associated with the address.
     *
     * @param string|array $email
     * @param string|null $name optional
     *
     * @return $this
     *
     * @see \Swift_Message::setTo()
     */
    public function recipient($email, $name = null)
    {
        $email = $this->normalizeEmail($email);
        $this->getSwiftMessage()->setTo($email, $name);

        return $this;
    }

    /**
     * Get the To addresses of this message.
     *
     * @return array
     *
     * @see \Swift_Message::getTo()
     */
    public function getTo()
    {
        return $this->getSwiftMessage()->getTo();
    }

    /**
     * Set the Cc addresses of this message.
     *
     * If multiple recipients will receive the message an array should be used.
     * Example: array('receiver@domain.org', 'other@domain.org' => 'A name')
     *
     * If $name is passed and the first parameter is a string, this name will be
     * associated with the address.
     *
     * @param string|array $email
     * @param string|null $name optional
     *
     * @return $this
     *
     * @see \Swift_Message::setCc()
     */
    public function cc($email, $name = null)
    {
        $email = $this->normalizeEmail($email);
        $this->getSwiftMessage()->setCc($email, $name);

        return $this;
    }

    /**
     * Get the Cc address of this message.
     *
     * @return array
     *
     * @see \Swift_Message::getCc()
     */
    public function getCc()
    {
        return $this->getSwiftMessage()->getCc();
    }

    /**
     * Set the Bcc addresses of this message.
     *
     * If multiple recipients will receive the message an array should be used.
     * Example: array('receiver@domain.org', 'other@domain.org' => 'A name')
     *
     * If $name is passed and the first parameter is a string, this name will be
     * associated with the address.
     *
     * @param string|array $email
     * @param string|null $name optional
     *
     * @return $this
     *
     * @see \Swift_Message::setBcc()
     */
    public function bcc($email, $name = null)
    {
        $email = $this->normalizeEmail($email);
        $this->getSwiftMessage()->setBcc($email, $name);

        return $this;
    }

    /**
     * Get the Bcc addresses of this message.
     *
     * @return array
     *
     * @see \Swift_Message::getBcc()
     */
    public function getBcc()
    {
        return $this->getSwiftMessage()->getBcc();
    }

    /**
     * Set the sender of this message.
     *
     * This does not override the From field, but it has a higher significance.
     *
     * @param string|array $email
     * @param string|null $name optional
     *
     * @return $this
     *
     * @see \Swift_Message::setSender()
     */
    public function sender($email, $name = null)
    {
        $email = $this->normalizeEmail($email);
        $this->getSwiftMessage()->setSender($email, $name);

        return $this;
    }

    /**
     * Get the sender of this message.
     *
     * @return string
     *
     * @see \Swift_Message::getSender()
     */
    public function getSender()
    {
        return $this->getSwiftMessage()->getSender();
    }

    /**
     * Set the header of table
     *
     * @param string $header
     */
    public function header($header)
    {
        $this->header = $header;
    }

    /**
     * Set the subject of this message.
     *
     * @param string $subject
     *
     * @return $this
     *
     * @see \Swift_Message::setSubject()
     */
    public function subject($subject)
    {
        $this->getSwiftMessage()->setSubject($subject);
        return $this;
    }

    /**
     * Get the subject of this message.
     *
     * @return string
     *
     * @see \Swift_Message::getSubject()
     */
    public function getSubject()
    {
        return $this->getSwiftMessage()->getSubject();
    }

    /**
     * Set the body of this message, either as a string, or as an instance of
     * {@link \Swift_OutputByteStream}.
     *
     * @param mixed $content
     * @param string $contentType optional
     * @param string $charset optional
     *
     * @return $this
     *
     * @see \Swift_Message::setBody()
     */
    public function content($content, $contentType = self::CONTENT_TYPE_HTML, $charset = null)
    {
        $result = null;

        if(!empty($content)) {
            $table = '<table border="1">';
            $columns = count($content[0]);

            if($this->header !== null){
                $header = explode(";", $this->header);
                $tr = '<tr>';
                for($i = 0; $columns > $i; $i++ ){
                    $tr .= "<th>$header[$i]</th>";
                }
                $tr .= '</tr>'; 

                $table .= $tr;
            }

            foreach($content as $row){
                $tr = '<tr>';
                for($i = 0; $columns > $i; $i++ ){
                    $tr .= "<td>$row[$i]</td>";
                }
                $tr .= '</tr>'; 

                $table .= $tr;
            }
            $table .= '</table>';

            $result = $table;
        }

        $this->getSwiftMessage()->setBody($result, $contentType, $charset);

        return $this;
    }

    /**
     * Get the body of this message as a string.
     *
     * @return string
     *
     * @see \Swift_Message::getBody()
     */
    public function getContent()
    {
        return $this->getSwiftMessage()->getBody();
    }

    /**
     * Add optionally an alternative body
     *
     * @param string $content
     * @param string $contentType optional
     * @param string $charset optional
     *
     * @return $this
     */
    public function contentAlternative($content, $contentType = null, $charset = null)
    {
        $this->getSwiftMessage()->addPart($content, $contentType, $charset);

        return $this;
    }

    /**
     * Set the Content-type of this message.
     *
     * @param string $contentType
     *
     * @return $this
     *
     * @see \Swift_Message::setContentType()
     */
    public function contentType($contentType)
    {
        $this->getSwiftMessage()->setContentType($contentType);

        return $this;
    }

    /**
     * Get the Content-type of this message.
     *
     * @return string
     *
     * @see \Swift_Message::getContentType()
     */
    public function getContentType()
    {
        return $this->getSwiftMessage()->getContentType();
    }

    /**
     * Set the character set of this message.
     *
     * @param string $charset
     *
     * @return $this
     *
     * @see \Swift_Message::setCharset()
     */
    public function charset($charset)
    {
        $this->getSwiftMessage()->setCharset($charset);

        return $this;
    }

    /**
     * Get the character set of this message.
     *
     * @return string
     *
     * @see \Swift_Message::getCharset()
     */
    public function getCharset()
    {
        return $this->getSwiftMessage()->getCharset();
    }

    /**
     * Set the priority of this message.
     *
     * The value is an integer where 1 is the highest priority and 5 is the lowest.
     *
     * @param int $priority
     *
     * @return $this
     *
     * @see \Swift_Message::setPriority()
     */
    public function priority($priority)
    {
        $this->getSwiftMessage()->setPriority($priority);

        return $this;
    }

    /**
     * Get the priority of this message.
     *
     * The returned value is an integer where 1 is the highest priority and 5
     * is the lowest.
     *
     * @return int
     *
     * @see \Swift_Message::getPriority()
     */
    public function getPriority()
    {
        return $this->getSwiftMessage()->getPriority();
    }

    /**
     * Ask for a delivery receipt from the recipient to be sent to $addresses
     *
     * @param array $email
     *
     * @return $this
     *
     * @see \Swift_Message::setReadReceiptTo()
     */
    public function setReadReceiptTo($email)
    {
        $email = $this->normalizeEmail($email);
        $this->getSwiftMessage()->setReadReceiptTo($email);

        return $this;
    }

    /**
     * An array of email which failed send to recipients.
     *
     * @return array
     */
    public function getFailedRecipients()
    {
        return $this->failedRecipients;
    }

    /**
     * Get the addresses to which a read-receipt will be sent.
     *
     * @return string
     *
     * @see \Swift_Message::getReadReceiptTo()
     */
    public function getReadReceiptTo()
    {
        return $this->getSwiftMessage()->getReadReceiptTo();
    }

    /**
     * Set the return-path (the bounce address) of this message.
     *
     * @param string $email
     *
     * @return $this
     *
     * @see \Swift_Message::setReturnPath()
     */
    public function setReturnPath($email)
    {
        $this->getSwiftMessage()->setReturnPath($email);

        return $this;
    }

    /**
     * Get the return-path (bounce address) of this message.
     *
     * @return string
     *
     * @see \Swift_Message::getReturnPath()
     */
    public function getReturnPath()
    {
        return $this->getSwiftMessage()->getReturnPath();
    }

    /**
     * Set the format of this message (flowed or fixed).
     *
     * @param string $format
     *
     * @return string
     *
     * @see \Swift_Message::setFormat()
     */
    public function setFormat($format)
    {
        $this->getSwiftMessage()->setFormat($format);

        return $this;
    }

    /**
     * Get the format of this message (i.e. flowed or fixed).
     *
     * @return string
     *
     * @see \Swift_Message::getFormat()
     */
    public function getFormat()
    {
        return $this->getSwiftMessage()->getFormat();
    }

    /**
     * Attach a file to the message.
     *
     * Events:
     * - mailer:beforeAttachFile
     * - mailer:afterAttachFile
     *
     * @param  string $file
     * @param  array $options optional
     *
     * @return $this
     *
     * @see Phalcon\Ext\Mailer\Message::createAttachmentViaPath()
     * @see Phalcon\Ext\Mailer\Message::prepareAttachment()
     */
    public function attachment($file, array $options = [])
    {
        $attachment = $this->createAttachmentViaPath($file);

        return $this->prepareAttachment($attachment, $options);
    }

    /**
     * Attach in-memory data as an attachment.
     *
     * @param  string $data
     * @param  string $name
     * @param  array $options optional
     *
     * @return Message
     *
     * @see Phalcon\Ext\Mailer\Message::createAttachmentViaData()
     * @see Phalcon\Ext\Mailer\Message::prepareAttachment()
     */
    public function attachmentData($data, $name, array $options = [])
    {
        $attachment = $this->createAttachmentViaData($data, $name);

        return $this->prepareAttachment($attachment, $options);
    }

    /**
     * Embed a file in the message and get the CID.
     *
     * @param  string $file
     *
     * @return string
     */
    public function embed($file)
    {
        $embed = $this->createEmbedViaPath($file);

        return $this->getSwiftMessage()->embed($embed);
    }

    /**
     * Embed in-memory data in the message and get the CID.
     *
     * @param  string $data
     * @param  string $name
     * @param  string $contentType
     *
     * @return string
     */
    public function embedData($data, $name, $contentType = null)
    {
        $embed = $this->createEmbedViaData($data, $name, $contentType);

        return $this->getSwiftMessage()->embed($embed);
    }

    /**
     * Return a {@link \Swift_Message} instance
     *
     * @return \Swift_Message
     */
    public function getSwiftMessage()
    {
        if (!$this->swiftMessage) {
            $this->swiftMessage = $this->getManager()->getSwift()->createMessage();
        }

        return $this->swiftMessage;
    }

    /**
     * Return a {@link \Phalcon\Mailer\Manager} instance
     *
     * @return \Phalcon\Mailer\Manager
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Send the given Message like it would be sent in a mail client.
     *
     * All recipients (with the exception of Bcc) will be able to see the other
     * recipients this message was sent to.
     *
     * Recipient/sender data will be retrieved from the Message object.
     *
     * The return value is the number of recipients who were accepted for
     * delivery.
     *
     * Events:
     * - mailer:beforeSend
     * - mailer:afterSend
     *
     * @return int
     *
     * @see \Swift_Mailer::send()
     */
    public function send()
    {
        $eventManager = $this->getManager()->getEventsManager();

        if ($eventManager) {
            $result = $eventManager->fire('mailer:beforeSend', $this);
        } else {
            $result = true;
        }

        if ($result !== false && !empty($this->getContent())) {

            $this->failedRecipients = [];

            $count = $this->getManager()->getSwift()->send($this->getSwiftMessage(), $this->failedRecipients);

            if ($eventManager) {
                $eventManager->fire('mailer:afterSend', $this, [$count, $this->failedRecipients]);
            }

            return $count;

        } else {
            return false;
        }
    }

    /**
     * Prepare and attach the given attachment.
     *
     * @param  \Swift_Attachment $attachment
     * @param  array $options optional
     *
     * @return $this
     *
     * @see \Swift_Message::attach()
     */
    protected function prepareAttachment(\Swift_Attachment $attachment, array $options = [])
    {
        if (isset($options['mime'])) {
            $attachment->setContentType($options['mime']);
        }

        if (isset($options['as'])) {
            $attachment->setFilename($options['as']);
        }

        $eventManager = $this->getManager()->getEventsManager();

        if ($eventManager) {
            $result = $eventManager->fire('mailer:beforeAttachFile', $this, [$attachment]);
        } else {
            $result = true;
        }

        if ($result !== false) {
            $this->getSwiftMessage()->attach($attachment);

            if ($eventManager) {
                $eventManager->fire('mailer:afterAttachFile', $this, [$attachment]);
            }
        }

        return $this;
    }

    /**
     * Create a Swift new Attachment from a filesystem path.
     *
     * @param   string $file
     *
     * @return \Swift_Attachment
     *
     * @see \Swift_Attachment::fromPath()
     */
    protected function createAttachmentViaPath($file)
    {
        /** @var $byteStream \Swift_ByteStream_FileByteStream */
        $byteStream = $this->getManager()->getDI()->get('\Swift_ByteStream_FileByteStream', [$file]);

        /** @var $image \Swift_Attachment */
        $attachment = $this->getManager()->getDI()->get('\Swift_Attachment')
            ->setFile($byteStream);

        return $attachment;
    }

    /**
     * Create a Swift Attachment instance from data.
     *
     * @param string $data
     * @param string $name optional
     *
     * @return \Swift_Attachment
     *
     * @see \Swift_Attachment::newInstance()
     */
    protected function createAttachmentViaData($data, $name)
    {
        return $this->getManager()->getDI()->get('\Swift_Attachment', [$data, $name]);
    }

    /**
     * Create a Swift new Image from a filesystem path.
     *
     * @param string $file
     *
     * @return \Swift_Image
     *
     * @see \Swift_Image::fromPath()
     */
    protected function createEmbedViaPath($file)
    {
        /** @var $byteStream \Swift_ByteStream_FileByteStream */
        $byteStream = $this->getManager()->getDI()->get('\Swift_ByteStream_FileByteStream', [$file]);

        /** @var $image \Swift_Image */
        $image = $this->getManager()->getDI()->get('\Swift_Image')
            ->setFile($byteStream);

        return $image;
    }

    /**
     * Create a Swift new Image.
     *
     * @param string $data
     * @param string|null $filename
     * @param string|null $contentType
     *
     * @return \Swift_Image
     *
     * @see \Swift_Image::newInstance()
     */
    protected function createEmbedViaData($data, $filename = null, $contentType = null)
    {
        return $this->getManager()->getDI()->get('\Swift_Image', [$data, $filename, $contentType]);
    }

    /**
     * Normalize IDN domains.
     *
     * @param $email
     *
     * @return array|string
     */
    protected function normalizeEmail($email)
    {
        if (is_array($email)) {

            $emails = [];

            foreach ($email as $k => $v) {
                if (is_int($k)) {
                    $emails[$k] = $this->getManager()->normalizeEmail($v);
                } else {
                    $k = $this->getManager()->normalizeEmail($k);
                    $emails[$k] = $v;
                }
            }

            return $emails;

        } else {
            return $this->getManager()->normalizeEmail($email);
        }
    }
}
