<?php
namespace Users\Model\Table\Traits;

use Cake\Core\Configure;
use Cake\Validation\Validator;

trait AccountValidationTrait
{
    /**
     * Account validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationAccount(Validator $validator)
    {
        $validator = $this->validationDefault($validator);
        $validator->remove('password');
        $validator->allowEmpty('confirm_password');
        $validator->add('confirm_password', 'no-misspelling', [
            'rule' => ['compareWith', 'password'],
            'message' => 'Passwords are not equal',
        ]);

        $this->addAvatarValidationRule($validator);

        return $validator;
    }

    /**
     * Conditionally add a validation rule for avatars if Users.enableAvatarUploads
     * is enabled
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    protected function addAvatarValidationRule(Validator $validator)
    {
        if (Configure::read('Users.enableAvatarUploads') !== true) {
            return $validator;
        }

        $validator->allowEmpty('avatar');
        $validator->add('avatar', 'valid-image', [
            'rule' => ['uploadedFile', [
                'types' => [
                    'image/bmp',
                    'image/gif',
                    'image/jpeg',
                    'image/pjpeg',
                    'image/png',
                    'image/vnd.microsoft.icon',
                    'image/x-windows-bmp',
                    'image/x-icon',
                    'image/x-png',
                ],
                'optional' => true,
            ]],
            'message' => 'The uploaded avatar was not a valid image'
        ]);
        $validator->add('avatar', 'not-upload-error', [
            'rule' => ['uploadError', true],
            'message' => 'There was an error uploading your avatar',
        ]);

        return $validator;
    }
}
