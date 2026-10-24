<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Blocks direct URL access to protected areas unless the user is logged in.
 *
 * Usage in Routes.php:
 *   $routes->group('', ['filter' => 'auth:customer'], function ($routes) { ... });
 *   $routes->group('', ['filter' => 'auth:admin'],    function ($routes) { ... });
 *
 * The optional argument ('customer' / 'admin') also enforces that a
 * customer can't reach the admin portal (and vice versa) just by
 * guessing the URL.
 */
class Authfilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // 1. Must be logged in at all.
        if ( ! $session->get('isLoggedIn') ) {
            $session->setFlashdata('error', 'Please log in to continue.');
            return redirect()->to('/');
        }

        // 2. If this route group specified a required role, enforce it.
        //    e.g. ['filter' => 'auth:admin'] means only ROLE === 'admin' may pass.
        if ( ! empty( $arguments ) ) {
            $requiredRole = $arguments[0];
            $userRole     = $session->get('role');

            if ( $userRole !== $requiredRole ) {
                // Logged in, but wrong portal — send them to their own home
                // instead of the login page.
                $ownHome = $userRole === 'admin' ? '/admin/home' : '/customer/home';
                return redirect()->to( $ownHome );
            }
        }

        return null; // all good, continue
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do after the request.
    }
}