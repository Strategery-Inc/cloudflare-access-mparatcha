<?php
declare(strict_types=1);

namespace Plus54\CloudFlareAccess\Service;

use Magento\Backend\Model\Auth;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\Plugin\AuthenticationException as PluginAuthenticationException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Security\Model\AdminSessionsManager;
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
    protected $auth;
    private MessageManagerInterface $messageManager;
    /**
     * @var AdminSessionsManager
     */
    private AdminSessionsManager $sessionsManager;

    /**
     * Construct
     *
     * @param AdminSessionsManager $sessionsManager
     * @param MessageManagerInterface $messageManager
     * @param UserInterfaceFactory $userFactory
     * @param UserResourceFactory $userResourceFactory
     * @param ManagerInterface $eventManager
     * @param Auth $auth
     */
    public function __construct(
        AdminSessionsManager $sessionsManager,
        MessageManagerInterface $messageManager,
        UserInterfaceFactory $userFactory,
        UserResourceFactory $userResourceFactory,
        ManagerInterface $eventManager,
        Auth $auth
    ) {
        $this->userFactory = $userFactory;
        $this->userResourceFactory = $userResourceFactory;
        $this->eventManager = $eventManager;
        $this->auth = $auth;
        $this->sessionsManager = $sessionsManager;
        $this->messageManager = $messageManager;
    }

    private function loadByEmail(string $email): UserInterface
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
        //   var_dump($user->getUserId());die;
        if ($user->getUserId() === null) {
            throw new \Exception('This user is not valid');
        }

        try {
            if (!$this->authenticate($user)) {
                throw new LoginByCloudflareException('LoginByCloudflareException: The user cannot authenticate by Cloudflare properly on LoginByEmail function');
            }
            // vendor/magento/module-user/Model/User.php:654
            // else
            //     throw LoginByCloudflareException
        } catch (\Throwable $e) {
            throw new LocalizedException(__('The user authentication was wrong.'));
        }
    }

    private function authenticate(UserInterface $user): bool
    {
        /** @var \Magento\User\Model\User $user */
        $result = false;
        try {
            $result = $this->verifyIdentity($user);
            $this->eventManager->dispatch(
                'admin_user_authenticate_before',
                ['email' => $user->getEmail(), 'user' => $user]
            );

            $this->eventManager->dispatch(
                'admin_user_authenticate_after',
                ['username' => $user->getUserName(), 'password' => '', 'user' => $user, 'result' => $result]
            );
            $userResource = $this->userResourceFactory->create();
            $userResource->recordLogin($user);

            $this->auth->getAuthStorage()->setUser($user);
            $this->auth->getAuthStorage()->processLogin();

            $this->eventManager->dispatch(
                'backend_auth_user_login_success',
                ['user' => $this->auth->getCredentialStorage()]
            );

            if (!$this->auth->getAuthStorage()->getUser()) {
                throw new AuthenticationException(
                    __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    )
                );
            }

            $this->sessionsManager->processLogin();
            if ($this->sessionsManager->getCurrentSession()->isOtherSessionsTerminated()) {
                $this->messageManager->addWarningMessage(__('All other open sessions for this account were terminated'));
            }
        } catch (LocalizedException $e) {
            $user->unsetData();
            throw $e;
        } catch (PluginAuthenticationException $e) {
            $this->eventManager->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => $user->getUserName(), 'exception' => $e]
            );
            throw $e;
        } catch (LocalizedException $e) {
            $this->eventManager->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => $user->getUserName(), 'exception' => $e]
            );
            throw new AuthenticationException(
                __(
                    $e->getMessage() ?: 'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                )
            );
        }

        if (!$result) {
            $user->unsetData();
            throw new \LocalizedException('Authenticate function not working properly');
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
        if (!$user->hasAssigned2Role($user->getUserId())) {
            throw new AuthenticationException(__('More permissions are needed to access this.'));
        }
        return true;
    }
}
