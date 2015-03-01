<?php

namespace BigHubBrother;

use Exception;
use BigHubBrother\Utility;
use BigHubBrother\GitHubWebhookRequest;
use Swift_Mailer;
use Swift_Message;
use Swift_MailTransport;

class Notifier
{
    public function __construct($options)
    {
        $options = array_merge(['config' => null, 'data' => null], $options);

        if (empty($options['config']))
            throw new Exception("Missing configuration.");
        if (empty($options['data']))
            throw new Exception("Missing data.");

        $this->config = $options['config'];
        $this->push = $options['data'];
    }

    public function notifyPeople()
    {
        $return = '';
        foreach ($this->config->getUsers() as $username => $info) {
            $repo = $this->config->getRepo($this->push['repository'], $username);
            if (!$repo)
                continue;

            $data = $this->_getNotifyData($username, $info);
            $email = $repo['e-mail'] ?: null;
            if (!empty($data) && !empty($email)) {
                $push = Utility::without($this->push, ['changes']);

                $return .= "\n\n" . $this->notifyUser(
                    [
                        'username' => $username,
                        'email' => $email
                    ],
                    Utility::merge($push, ['changes' => $data])
                );
            }
        }

        return trim($return);
    }

    public function notifyUser($userInfo, $data)
    {
        $mailer = Swift_Mailer::newInstance( Swift_MailTransport::newInstance() );

        $body = $this->_getMessageBody($data, ['html' => false]);
        $message = Swift_Message::newInstance()
            ->setSubject( $this->_getMessageSubject($data) )
            ->setBody( $body )
            ->addPart( $this->_getMessageBody($data, ['html' => true]) , 'text/html' )
            ->setFrom( $this->config->getFromMail() )
            ->setTo([ $userInfo['email'] => $userInfo['username'] ]);

        return $mailer->send($message) ?
            $userInfo['username'] . " notified, sent this mail:\n  | " . str_replace("\n", "\n  | ", $body) :
            "Failed to notify " . $userInfo['username'];

    }

    protected function _getNotifyData($username, $info)
    {

        $changesToNotify = [];

        foreach ($this->push['changes'] as $committer => $files) {
            if (!$this->config->isCommitterWatchedByUserInRepo($committer, $username, $this->push['repository'])) {
                continue;
            }

            if (!$this->config->areFilesWatchedByUserInRepo($files, $username, $this->push['repository'])) {
                continue;
            }

            $changesToNotify[$committer] = $files;
        }

        return $changesToNotify;
    }

    protected function _getMessageBody($data, $options)
    {
        $options = array_merge(['html' => false], $options);
        $html = !!$options['html'];
        $org = $data['organization'];
        $repo = $data['repository'];
        $branch = $data['branch'];
        $compareURL = $data['compare_url'];

        $template =
            "<p>Someone just pushed changes you want to be notified for in " .
            Utility::url(GitHubWebhookRequest::getBranchURL($branch, $repo), "$repo/$branch", $html)."</p>" .
            "<p>" . Utility::url($compareURL, "See the differences in details", $html) . "\n" .
            "<br>";

        foreach ($data['changes'] as $user => $files) {
            $template .= "<strong>Changes by " . Utility::url(GitHubWebhookRequest::getUserURL($user), "@$user", $html) . ":</strong>\n";
            $template .= "<ul>";
            foreach ($files as $file) {
                $template .= "<li>" . Utility::url(GitHubWebhookRequest::getFileURL($file, $branch, $repo), "$file", $html) . "</li>";
            }
            $template .= "</ul>";
        }

        $template .=
            "<br><br><hr>" .
            "<p style='font-size: small'>If you think you received this notification by mistake, please check your " .
            Utility::url(GitHubWebhookRequest::getOrgURL($org), "organization", $html) . " webhooks and <a href=\"https://github.com/Leimi/bighub-brother\">bighub-brother</a> settings.</p>";

        if (!$options['html'])
            $template = Utility::htmlToText($template);

        return $template;
    }

    protected function _getMessageSubject($data)
    {
        $repo = substr( strstr($data['repository'], '/'), 1 );
        return "[$repo] New watched file changes detected";
    }
}