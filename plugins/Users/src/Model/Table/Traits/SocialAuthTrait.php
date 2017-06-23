<?php
namespace Users\Model\Table\Traits;

use Cake\Datasource\EntityInterface;
use RuntimeException;

trait SocialAuthTrait
{
    /**
     * Creates a new user for the given social profile
     *
     * @param \Cake\Datasource\EntityInterface $profile The social profile being used to create the user
     * @return \Cake\Datasource\EntityInterface user entity
     */
    public function getUserFromSocialProfile(EntityInterface $profile)
    {
        // Make sure here that all the required fields are actually present
        if (empty($profile->email)) {
            throw new RuntimeException('Could not find email in social profile.');
        }

        $user = $this->newEntity(['email' => $profile->email]);
        $user = $this->save($user);

        if (!$user) {
            throw new RuntimeException('Unable to save new user');
        }

        return $user;
    }
}
