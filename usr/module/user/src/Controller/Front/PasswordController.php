<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\User\Form\PasswordForm;
use Module\User\Form\PasswordFilter;
use Module\User\Form\ResetPasswordForm;
use Module\User\Form\ResetPasswordFilter;
use Module\User\Form\FindPasswordForm;
use Module\User\Form\FindPasswordFilter;

/**
 * Password controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class PasswordController extends ActionController
{
    /**
     * Change password for current user
     *
     * @return array|void
     */
    public function indexAction()
    {
        Pi::service('authentication')->requireLogin();
        Pi::api('profile', 'user')->requireComplete();
        $view = Pi::service('view');
        $view->getHelper('footScript')->prependFile($view->getHelper('assetModule')->__invoke('front/pwstrength-bootstrap.min.js', 'user'));
        $view->getHelper('footScript')->prependFile($view->getHelper('assetModule')->__invoke('front/pwstrength-boostrap.init.js', 'user'));

        $uid = Pi::user()->getId();

        $result = array(
            'status'    => 0,
            'message'   => __('Reset password failed.'),
        );

        $form = new PasswordForm('password-change');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new PasswordFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                /*
                // Verify password
                $row = Pi::model('user_account')->find($uid, 'id');
                if ($row['credential'] == $row->transformCredential($values['credential'])) {
                    //if ($credential == $row->credential) {
                    // Update password
                    Pi::api('user', 'user')->updateAccount(
                        $uid,
                        array(
                            'credential' => $values['credential-new']
                        )
                    );
                    Pi::service('event')->trigger('password_change', $uid);

                    $result['status'] = 1;
                    $result['message'] = __('Reset password successfully.');
                } else {
                    $result['message'] = __('Invalid password.');
                }
                */
                // Update password
                Pi::api('user', 'user')->updateAccount(
                    $uid,
                    array(
                        'credential' => $values['credential-new']
                    )
                );
                Pi::service('event')->trigger('password_change', $uid);

                $result['status'] = 1;
                $result['message'] = __('Reset password successfully.');
            }

            $this->view()->assign('result', $result);
        }

        // Get side nav items
        //$groups = Pi::api('group', 'user')->getList();
        //$user   = Pi::api('user', 'user')->get($uid, array('uid', 'name'));

        $piConfig = Pi::user()->config();
        $minChars = $piConfig['password_min'];
        $maxChars = $piConfig['password_max'];
        $strenghtenPassword = $piConfig['strenghten_password'];

        $showPasswordLabel = __('Show my password');

        $wordLength = __("Your password is too short");
        $wordNotEmail = __("Do not use your email as your password");
        $wordSimilarToUsername = __("Your password cannot contain your username");
        $wordTwoCharacterClasses = __("Use different character classes");
        $wordRepetitions = __("Too many repetitions");
        $wordSequences = __("Your password contains sequences");
        $errorList = __("Errors:");
        $veryWeak = __("Very week");
        $weak = __("Week");
        $normal = __("Normal");
        $medium = __("Medium");
        $strong = __("Strong");
        $veryStrong = __("Very Strong");

        $message = __("Password must contain at lease one uppercase letter, one lowercase letter and one digit character");

        $script = <<<HTML
        
        <label>{$message}</label>
<script>

    var minChar = {$minChars};
    
    var wordLength = "{$wordLength}";
    var wordNotEmail = "{$wordNotEmail}";
    var wordSimilarToUsername = "{$wordSimilarToUsername}";
    var wordTwoCharacterClasses = "{$wordTwoCharacterClasses}";
    var wordRepetitions = "{$wordRepetitions}";
    var wordSequences = "{$wordSequences}";
    var errorList = "{$errorList}";
    var veryWeak = "{$veryWeak}";
    var weak = "{$weak}";
    var normal = "{$normal}";
    var medium = "{$medium}";
    var strong = "{$strong}";
    var veryStrong = "{$veryStrong}";
</script>
HTML;

        $form->get('credential')
            ->setAttribute('description', $script);

        $this->view()->assign(array(
            'form'      => $form,
            //'groups'    => $groups,
            //'cur_group' => 'password',
            //'user'      => $user,
        ));

        $this->view()->headTitle(__('Change password'));
        $this->view()->headdescription(__('To ensure your account security, complex password is required.'), 'set');
        $this->view()->headkeywords($this->config('head_keywords'), 'set');
    }

    /**
     * 1. Display find password form
     * 2. Verify email
     * 3. Send verify email
     *
     */
    public function findAction()
    {
        // Check usrr not login
        if (Pi::service('user')->hasIdentity()) {
            $this->redirect()->toUrl(Pi::service('user')->getUrl('profile'));
            return false;
        }

        $result = array(
            'status'  => 0,
            'message' => __('Find password failed.'),
        );
        $form = new FindPasswordForm('find-password');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new FindPasswordFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $value = $form->getData();

                // Check if email exists
                //$userRow = $this->getModel('account')->find($value['email'], 'email');
                $userRow = Pi::service('user')->getUser($value['email'], 'email');
                if (!$userRow) {
                    $this->view()->assign(array(
                        'form'   => $form,
                        'result' => $result,
                    ));

                    return;
                }

                // Set user data
                $uid    = (int) $userRow->id;
                $token  = $this->createToken($uid, $value['email']);
                Pi::user()->data()->set(
                    $uid,
                    'find-password',
                    $token,
                    'user',
                    $this->config('email_expiration') * 3600
                );

                // Send verify email
                $to = $userRow->email;
                $url = $this->url('', array(
                        'action' => 'process',
                        'token'  => $token
                    )
                );
                $link = Pi::url($url, true);

                $params = array(
                    'username'          => $userRow->identity,
                    'find_password_url' => $link,
                    'expiration'        => $this->config('email_expiration'),
                );

                // Load from HTML template
                $data = Pi::service('mail')->template(
                    'find-password-html',
                    $params
                );

                // Mail body logging
                Pi::user()->data()->set(
                    $uid,
                    'find-password-body',
                    $data['body']
                );

                // Set subject and body
                $subject    = $data['subject'];
                $body       = $data['body'];
                $type       = $data['format'];

                $message = Pi::service('mail')->message($subject, $body, $type);
                $message->addTo($to);
                Pi::service('mail')->send($message);

                $result['status'] = 1;
                $result['message'] = __('Confirmation email sent successfully. Please check email and reset password.');
            }

            $this->view()->assign('result', $result);
        }

        $this->view()->assign('form', $form);
        $this->view()->setTemplate('password-find');

        $this->view()->headTitle(__('Find password'));
        $this->view()->headdescription(__('Find password'), 'set');
        $this->view()->headkeywords($this->config('head_keywords'), 'set');
    }

    /**
     * 1. Verify find password link
     * 2. Update user information
     */
    public function processAction()
    {
        $result = array(
            'status'  => 0,
            'message' => __('Invalid token for password reset.'),
        );
        $token = _get('token');

        $view = $this->view();
        $fallback = function () use ($view, $result) {
            $view->assign('result', $result);
        };
        // Verify link invalid
        if (!$token) {
            return $fallback();
        }

        $userData = Pi::user()->data()->find(array(
            'name'  => 'find-password',
            'value' => $token
        ));
        if (!$userData) {
            return $fallback();
        }

        /*
        // Check link expire time
        $expire = $this->config('email_expiration');
        if ($expire) {
            $expire  = $userData['time'] + $expire * 3600;
            if (time() > $expire) {
                return $fallback();
            }
        }
        */

        $uid = (int) $userData['uid'];
        $userRow = $this->getModel('account')->find($uid, 'id');
        if (!$userRow) {
            return $fallback();
        }

        $uid  = $userRow->id;
        $form = new ResetPasswordForm('find-password', 'find');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new ResetPasswordFilter('find'));
            $form->setData($data);

            if ($form->isValid()) {
                $values = $form->getData();

                // Update user account data
                Pi::api('user', 'user')->updateAccount(
                    $uid,
                    array('credential' => $values['credential-new'])
                );

                Pi::service('event')->trigger('password_change', $uid);
                // Delete find password verify token
                Pi::user()->data()->delete($uid, 'find-password');
                $result['message'] = __('Password reset successfully.');
                $result['status']  = 1;
            } else {
                $form->setData(array('token' => $token));
                $this->view()->assign(array(
                    'form' => $form
                ));
            }
            $this->view()->assign('result', $result);
        } else {
            $form->setData(array('token' => $token));
            $this->view()->assign(array(
                'form' => $form
            ));
        }
    }

    /**
     * Creates token
     *
     * @param int $uid
     * @param string $email
     *
     * @return string
     */
    protected function createToken($uid, $email)
    {
        $token = md5($uid . $email . Pi::config('salt') . mt_rand());

        return $token;
    }
}
