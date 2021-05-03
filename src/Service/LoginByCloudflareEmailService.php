<?php
declare(strict_types=1);

namespace Plus54\CloudFlareAccess\Service;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Plus54\CloudFlareAccess\Service\LoginByCloudflareException;
use Magento\User\Api\Data\UserInterface;
use Magento\User\Api\Data\UserInterfaceFactory;
use Magento\User\Model\ResourceModel\User;
use Magento\User\Model\ResourceModel\UserFactory as UserResourceFactory;

class LoginByCloudflareEmailService
{
    /**
     * User model factory
     *
     * @var UserInterfaceFactory
     */
    protected $userFactory;
    private $userResourceFactory;
    protected $eventManager;

    /**
     * Construct
     *
     * @param UserInterfaceFactory $userFactory
     * @param UserResourceFactory $userResourceFactory
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        UserInterfaceFactory $userFactory,
        UserResourceFactory $userResourceFactory,
        ManagerInterface $eventManager
    ) {
        $this->userFactory = $userFactory;
        $this->userResourceFactory = $userResourceFactory;
        $this->eventManager = $eventManager;
    }

    public function loadByEmail(string $email): UserInterface
    {
        /** @var User $userResource */
        $userResource = $this->userResourceFactory->create();
        $connection = $userResource->getConnection();
        $select = $connection->select()->from($userResource->getMainTable())->where('email=:email');
        $binds = ['email' => $email];
        $result = $connection->fetchRow($select, $binds);
        /** @var \Magento\User\Model\User $user */
        $user = $this->userFactory->create();
        if (empty($result)) {
            return $user;
        }
        $user->setData($result);
        return $user;
    }

    public function loginByEmail(string $email): void
    {
        $user = $this->loadByEmail($email);
        if ($user->getId() === null) {
            throw new \Exception('This user is not valid');
        }

        try {
            if (!$this->authentication($user)) {
                throw new LoginByCloudflareException('LoginByCloudflareException: The user cannot authenticate by Cloudflare properly on LoginByEmail function');
            } else {
                $user->getResource()->recordLogin($user);
            }
            // vendor/magento/module-user/Model/User.php:654
            // else
            //     throw LoginByCloudflareException
        } catch (\Throwable $e) {
            throw new LocalizedException(__('The user authentication was wrong.'));
        }
    }

    public function authentication(UserInterface $user): bool
    {
        $result = false;
        try {
            $this->verifyIdentity($user);

            $this->eventManager->dispatch(
                'admin_user_authenticate_before',
                ['email' => $user->getEmail(), 'user' => $user]
            );

            $this->eventManager->dispatch(
                'admin_user_authenticate_after',
                ['username' => $user->getUserName(), 'password' => '', 'user' => $this, 'result' => $result]
            );
        } catch (LocalizedException $e) {
            $user->unsetData();
            throw $e;
        }

        if (!$result) {
            $user->unsetData();
        }
        return $result;
    }

    private function verifyIdentity(UserInterface $user): bool
    {
        // vendor/magento/module-user/Model/User.php:627
        if ($user->getIsActive() != '1') {
            throw new AuthenticationException(
                __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                )
            );
        }
        if (!$user->hasAssigned2Role($user->getId())) {
            throw new AuthenticationException(__('More permissions are needed to access this.'));
        }
        $result = true;
    }
}
