<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Translation\TranslatorInterface;

class Mailer
{
    private $swiftMailer;

    private $twig;

    public function __construct(\Swift_Mailer $swiftMailer, \Twig_Environment $twig)
    {
        $this->swiftMailer = $swiftMailer;
        $this->twig = $twig;
    }

    public function orderConfirmation(User $user, TranslatorInterface $translator)
    {
        $message = (new \Swift_Message($translator->trans('order.email_confirm')))
            //TODO: hardcoded value of email
            ->setFrom('send@example.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->twig->render('emails/order_confirmation.html.twig'),
                'text/html'
            );

        $this->swiftMailer->send($message);
    }
}
