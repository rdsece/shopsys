<?php

namespace Shopsys\ShopBundle\Model\Security;

use Shopsys\FrameworkBundle\Model\Customer\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

use Shopsys\FrameworkBundle\Model\Security\LoginService as BaseLoginService;

class LoginService extends BaseLoginService
{
    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var \Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher
     */
    private $traceableEventDispatcher;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        TraceableEventDispatcher $traceableEventDispatcher
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->traceableEventDispatcher = $traceableEventDispatcher;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User $user
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function loginUser(User $user, Request $request)
    {
        $token = new UsernamePasswordToken($user, $user->getPassword(), 'frontend', $user->getRoles());
        $this->tokenStorage->setToken($token);

        // dispatch the login event
        $event = new InteractiveLoginEvent($request, $token);
        $this->traceableEventDispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $event);

        //migrate session
        $request->getSession()->migrate();
    }
}
