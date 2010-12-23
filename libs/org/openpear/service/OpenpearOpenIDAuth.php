<?php

class OpenpearOpenIDAuth
{
    static public function login(Request &$request) {
        Pea::begin_loose_syntax();
        require_once 'Auth/OpenID/Consumer.php';
        require_once 'Auth/OpenID/FileStore.php';
        require_once 'Auth/OpenID/SReg.php';
        require_once 'Auth/OpenID/PAPE.php';

        if ((($request->in_vars('openid_url') != "") || $request->in_vars('openid_verify'))) {
            Log::debug("begin openid auth: ". $request->in_vars('openid_url'));

            // OpenID Auth
            $consumer = new Auth_OpenID_Consumer(new Auth_OpenID_FileStore(work_path('openid')));
            if ($request->is_vars('openid_verify')) {
                $response = $consumer->complete($request->request_url());
                if ($response->status == Auth_OpenID_SUCCESS) {
                    return $response->getDisplayIdentifier();
                }
            } else {
                $auth_request = $consumer->begin($request->in_vars('openid_url'));
                if (!$auth_request) {
                    throw new RuntimeException('invalid openid url');
                }
                $sreg_request = Auth_OpenID_SRegRequest::build(array('nickname'), array('fullname', 'email'));
                if ($sreg_request) {
                    $auth_request->addExtension($sreg_request);
                }
                if ($auth_request->shouldSendRedirect()) {
                    $redirect_url = $auth_request->redirectURL(url(), $request->request_url(false). '?openid_verify=true');
                    if (Auth_OpenID::isFailure($redirect_url)) {
                        throw new RuntimeException("Could not redirect to server: {$redirect_url->message}");
                    } else {
                        $request->redirect($redirect_url);
                    }
                } else {
                    $form_html = $auth_request->htmlMarkup(url(),
                        $request->request_url(false). '?openid_verify=true', false, array('id' => 'openid_message'));
                    if (Auth_OpenID::isFailure($form_html)) {
                        throw new RuntimeException("Could not redirect to server: {$form_html->message}");
                    } else {
                        echo $form_html;
                        exit;
                    }
                }
            }
        }
        Pea::end_loose_syntax();
        return null;
    }
}

