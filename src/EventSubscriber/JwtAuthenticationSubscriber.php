<?php

namespace App\EventSubscriber;

use App\Service\JwtService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthenticationSubscriber implements EventSubscriberInterface
{
    private JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Skip authentication for public routes
        $publicRoutes = [
            '/api/auth/register',
            '/api/auth/login',
        ];

        $path = $request->getPathInfo();

        // Allow public routes without authentication
        foreach ($publicRoutes as $publicRoute) {
            if (str_starts_with($path, $publicRoute)) {
                return;
            }
        }

        // Only check authentication for API routes
        if (!str_starts_with($path, '/api/')) {
            return;
        }

        // Get the Authorization header
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            $response = new JsonResponse([
                'error' => 'Authentication required',
                'message' => 'Please provide a valid JWT token in the Authorization header'
            ], Response::HTTP_UNAUTHORIZED);

            $event->setResponse($response);
            return;
        }

        // Extract token
        $token = substr($authHeader, 7); // Remove "Bearer " prefix

        // Validate token
        $payload = $this->jwtService->validateToken($token);

        if (!$payload) {
            $response = new JsonResponse([
                'error' => 'Invalid or expired token',
                'message' => 'Please login again to get a new token'
            ], Response::HTTP_UNAUTHORIZED);

            $event->setResponse($response);
            return;
        }

        // Store user info in request attributes for use in controllers
        $request->attributes->set('user_id', $payload['user_id']);
        $request->attributes->set('user_email', $payload['email']);
    }
}
