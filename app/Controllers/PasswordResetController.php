<?php

namespace App\Controllers;

use App\Models\UserModel;

/**
 * PasswordResetController — "Forgot password" for customers
 * -----------------------------------------------------------------
 * No email and no extra database table:
 *   1. POST /forgot-password        customer types their email. If an account
 *                                   has that email, it is remembered in the
 *                                   session for 10 minutes.
 *   2. POST /forgot-password/reset  customer types a new password for that
 *                                   email, and it is saved.
 *
 * >>> Adjust the COL_* names below to match your users table. <<<
 */
class PasswordResetController extends BaseController
{
    private const COL_EMAIL    = 'EMAIL';
    private const COL_ID       = 'USER_ID';    // primary key of the users table
    private const COL_PASSWORD = 'PASSWORD';   // the column that holds the password hash

    private const MIN_LENGTH = 8;     // minimum new-password length
    private const STEP_TTL   = 600;   // seconds the customer has to finish after step 1
    private const MAX_TRIES  = 10;    // email checks allowed per IP...
    private const TRY_WINDOW = 600;   // ...in this many seconds

    /** POST /forgot-password — step 1: is there an account with this email? */
    public function checkEmail()
    {
        // Slow down anyone guessing at emails.
        $key   = 'fp_tries_' . md5( $this->request->getIPAddress() );
        $tries = (int) cache( $key );
        if ( $tries >= self::MAX_TRIES ) {
            return $this->json( [ 'success' => false, 'message' => 'Too many attempts. Please wait a few minutes and try again.' ], 429 );
        }
        cache()->save( $key, $tries + 1, self::TRY_WINDOW );

        $email = trim( (string) $this->request->getPost( 'email' ) );
        if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
            return $this->json( [ 'success' => false, 'message' => 'Please enter a valid email address.' ], 422 );
        }

        $user = ( new UserModel() )->where( self::COL_EMAIL, $email )->first();
        if ( ! $user ) {
            return $this->json( [ 'success' => false, 'message' => "We couldn't find an account with that email." ], 404 );
        }

        session()->set( [
            'pw_reset_email' => $user[ self::COL_EMAIL ],
            'pw_reset_until' => time() + self::STEP_TTL,
        ] );

        return $this->json( [ 'success' => true, 'email' => $user[ self::COL_EMAIL ] ] );
    }

    /** POST /forgot-password/reset — step 2: save the new password. */
    public function resetPassword()
    {
        $email = session()->get( 'pw_reset_email' );
        $until = (int) session()->get( 'pw_reset_until' );

        if ( ! $email || time() > $until ) {
            session()->remove( [ 'pw_reset_email', 'pw_reset_until' ] );
            return $this->json( [ 'success' => false, 'expired' => true, 'message' => 'Your session expired. Please enter your email again.' ], 403 );
        }

        $password = (string) $this->request->getPost( 'password' );
        $confirm  = (string) $this->request->getPost( 'confirm_password' );

        if ( strlen( $password ) < self::MIN_LENGTH ) {
            return $this->json( [ 'success' => false, 'message' => 'Your password must be at least ' . self::MIN_LENGTH . ' characters.' ], 422 );
        }
        if ( $password !== $confirm ) {
            return $this->json( [ 'success' => false, 'message' => 'The two passwords do not match.' ], 422 );
        }

        $userModel = new UserModel();
        $user      = $userModel->where( self::COL_EMAIL, $email )->first();
        if ( ! $user ) {
            session()->remove( [ 'pw_reset_email', 'pw_reset_until' ] );
            return $this->json( [ 'success' => false, 'expired' => true, 'message' => 'Account not found. Please enter your email again.' ], 404 );
        }

        // NOTE: if your UserModel already hashes passwords in a callback
        // (beforeUpdate), pass the plain $password here instead.
        $userModel->update( $user[ self::COL_ID ], [
            self::COL_PASSWORD => password_hash( $password, PASSWORD_DEFAULT ),
        ] );

        session()->remove( [ 'pw_reset_email', 'pw_reset_until' ] );

        return $this->json( [ 'success' => true, 'message' => 'Your password has been changed. You can now log in with it.' ] );
    }

    /** JSON reply that also hands back the fresh CSRF token (it rotates after every POST). */
    private function json( array $data, int $status = 200 )
    {
        $data['csrfName'] = csrf_token();
        $data['csrfHash'] = csrf_hash();

        return $this->response->setStatusCode( $status )->setJSON( $data );
    }
}