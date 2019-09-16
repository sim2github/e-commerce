<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChangePassword
{
    private $oldPassword;

    private $newPassword;

    public function loadValidatorMetadata(ClassMetadata $metadata, TranslatorInterface $translator)
    {
        $metadata->addPropertyConstraint(
            'oldPassword',
            new SecurityAssert\UserPassword([
                'message' => $translator->trans('user.invalid_password')
            ])
        );
        $metadata->addPropertyConstraint('newPassword', new Assert\Length([
            'min' => 5,
            'max' => 50,
            'minMessage' => $translator->trans('user.pasword_min', ['char' => 5]),
            'maxMessage' => $translator->trans('user.pasword_max', ['char' => 50])
        ]));
    }

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(string $oldPassword)
    {
        $this->oldPassword = $oldPassword;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword)
    {
        $this->newPassword = $newPassword;
    }
}
