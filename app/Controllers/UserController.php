<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class UserController extends BaseController {

    // Handle the "Create Account" form (register.php / register.js)
    // Expects JSON: { name, email, password, confirm_password, terms }

    public function register() {

        $input = $this->request->getJSON( true );
        if ( ! $input ) {
            $input = $this->request->getPost();
        }

        $rules = [
            'first_name'       => 'required|min_length[2]|max_length[50]',
            'last_name'        => 'required|min_length[2]|max_length[50]',
            'email'            => 'required|valid_email|is_unique[user.EMAIL]',
            'phone'            => 'required|regex_match[/^[0-9]{11}$/]',
            'password'         => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]',
            'terms'            => 'required',
        ];

        $messages = [
            'email' => [
                'is_unique'   => 'An account with this email already exists.',
                'valid_email' => 'Please enter a valid email address.',
            ],
            'phone' => [
                'required'    => 'Please enter a phone number.',
                'regex_match' => 'Phone number must be exactly 11 digits.',
            ],
            'password' => [
                'min_length' => 'Password must be at least 8 characters.',
            ],
            'confirm_password' => [
                'matches' => 'Passwords do not match.',
            ],
            'terms' => [
                'required' => 'Please agree to the Terms of Service & Privacy Policy.',
            ],
        ];

        if ( ! $this->validateData( $input, $rules, $messages ) ) {
            $errors = $this->validator->getErrors();
            return $this->response->setStatusCode( 422 )->setJSON( [
                'success' => false,
                'message' => reset( $errors ),
                'errors'  => $errors,
            ] );
        }

        $userModel = new UserModel();

        $data = [
            'FIRST_NAME' => $input['first_name'],
            'LAST_NAME'  => $input['last_name'],
            'EMAIL'      => $input['email'],
            'PHONE_NO'   => $input['phone'],
            'PASSWORD'   => password_hash( $input['password'], PASSWORD_DEFAULT ),
            'ROLE'       => 'customer',
        ];

        $userModel->insert( $data );

        return $this->response->setJSON( [
            'success' => true,
            'message' => 'Account created successfully! You can now log in.',
        ] );
    }



 

public function login() {

    $input = $this->request->getJSON( true );
    if ( ! $input ) {
        $input = $this->request->getPost();
    }

    $rules = [
        'email'    => 'required|valid_email',
        'password' => 'required',
    ];

    if ( ! $this->validateData( $input, $rules ) ) {
        return $this->response->setStatusCode( 422 )->setJSON( [
            'success' => false,
            'message' => 'Please enter a valid email and password.',
        ] );
    }

    $userModel = new UserModel();
    $user = $userModel->where( 'EMAIL', $input['email'] )->first();

    if ( ! $user || ! password_verify( $input['password'], $user['PASSWORD'] ) ) {
        // TEMPORARY DEBUG — remove after testing
        return $this->response->setStatusCode( 401 )->setJSON( [
            'success' => false,
            'message' => 'Invalid email or password.',
            'debug' => [
                'email_received' => $input['email'] ?? null,
                'user_found'     => (bool) $user,
                'stored_email'   => $user['EMAIL'] ?? null,
                'hash_prefix'    => isset($user['PASSWORD']) ? substr($user['PASSWORD'], 0, 10) : null,
                'verify_result'  => $user ? password_verify( $input['password'], $user['PASSWORD'] ) : null,
            ],
        ] );
    }


    // Log the user in
    $session = session();
    $session->regenerate();
    $session->set( [
        'user_id'    => $user['USER_ID'],
        'first_name' => $user['FIRST_NAME'],
        'email'      => $user['EMAIL'],
        'role'       => $user['ROLE'],
        'isLoggedIn' => true,
    ] );

    $redirect = $user['ROLE'] === 'admin' ? '/admin/home' : '/customer/home';

    return $this->response->setJSON( [
        'success'  => true,
        'message'  => 'Logged in successfully!',
        'redirect' => $redirect,
    ] );
}

    public function logout() {

        $session = session();
        $session->destroy();
        return redirect()->to( '/' );
        //return view('html1/login');
    }


    // TEMPORARY — dumps the current session contents as JSON so you
    // can confirm login is populating it correctly. Remove this
    // method (and its route) once you're done checking.

    public function debugSession() {
        return $this->response->setJSON( session()->get() );
    }


}