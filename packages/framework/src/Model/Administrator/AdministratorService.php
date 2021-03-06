<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class AdministratorService
{
    /**
     * @var \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     */
    public function __construct(
        EncoderFactoryInterface $encoderFactory,
        TokenStorageInterface $tokenStorage
    ) {
        $this->encoderFactory = $encoderFactory;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param string $password
     */
    public function setPassword(Administrator $administrator, $password)
    {
        $encoder = $this->encoderFactory->getEncoder($administrator);
        $passwordHash = $encoder->encodePassword($password, $administrator->getSalt());
        $administrator->setPassword($passwordHash);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param int $adminCountExcludingSuperadmin
     */
    public function delete(Administrator $administrator, $adminCountExcludingSuperadmin)
    {
        if ($adminCountExcludingSuperadmin === 1) {
            throw new \Shopsys\FrameworkBundle\Model\Administrator\Exception\DeletingLastAdministratorException();
        }
        if ($this->tokenStorage->getToken()->getUser() === $administrator) {
            throw new \Shopsys\FrameworkBundle\Model\Administrator\Exception\DeletingSelfException();
        }
        if ($administrator->isSuperadmin()) {
            throw new \Shopsys\FrameworkBundle\Model\Administrator\Exception\DeletingSuperadminException();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorData $administratorData
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null $administratorByUserName
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function edit(
        AdministratorData $administratorData,
        Administrator $administrator,
        Administrator $administratorByUserName = null
    ) {
        if ($administratorByUserName !== null
            && $administratorByUserName !== $administrator
            && $administratorByUserName->getUsername() === $administratorData->username
        ) {
            throw new \Shopsys\FrameworkBundle\Model\Administrator\Exception\DuplicateUserNameException($administrator->getUsername());
        }
        $administrator->edit($administratorData);
        if ($administratorData->password !== null) {
            $this->setPassword($administrator, $administratorData->password);
        }

        return $administrator;
    }
}
