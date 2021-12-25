<?php


namespace app\Services;


use app\Config\Config;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Tools\Singleton;
use Tools\SingletonInterface;

class EmailService implements SingletonInterface
{
    use Singleton;

    private string $senderEmail;
    private string $senderName;

    private string $smtpHost;
    private string $smtpUser;
    private string $smtpPassword;
    private int $smtpPort;

    public function __construct()
    {
        $config = Config::getInstance();

        $this->senderEmail = $config->getEmailSenderEmail();
        $this->senderName = $config->getEmailSenderName();

        $this->smtpHost = $config->getSmtpHost();
        $this->smtpUser = $config->getSmtpUser();
        $this->smtpPassword = $config->getSmtpPassword();
        $this->smtpPort = $config->getSmtpPort();
    }

    /**
     * @param string $address
     * @param string $subject
     * @param string $body
     * @param bool $isHtml
     * @throws EmailServiceException|Exception
     */
    public function send(string $address, string $subject, string $body, bool $isHtml = true): void
    {
        $mail = new PHPMailer(false);

        $mail->isHTML(true);
        $mail->isSMTP();
        $mail->Host = $this->smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $this->smtpUser;
        $mail->Password = $this->smtpPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $this->smtpPort;

        $mail->setFrom($this->senderEmail, $this->senderName);
        $mail->addAddress($address);

        $mail->Subject = $subject;
        $mail->Body = $body;

        $result = $mail->send();

        if ($result === false) {
            error_log($mail->ErrorInfo);

            throw new EmailServiceException($mail->ErrorInfo);
        }
    }
}
