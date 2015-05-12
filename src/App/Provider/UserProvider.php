<?php

namespace App\Provider;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use App\Model;

/**
 * Provide users to the security service
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
class UserProvider extends Model\BaseModel implements UserProviderInterface {

    /**
     * Load user by username
     * @param $user
     * @return $user
     */
    public function loadUserByUsername($username) {

        // tratamento dos caracteres escape usados em SQL injections
        $username = $this->db->real_escape_string($username);

        $user = new Model\UserModel($this->db);
        $user->getByUsername($username);

        if (!$user->getResult()) {
            throw new \Exception('Não foi possível consultar no banco de dados.');
        }

        if (!$row = $user->fetch()) {
            throw new \Exception(sprintf('Usuário "%s" não encontrado.', $username));
        }

        return new User($row['username'], $row['password'], explode(',', $row['roles']), true, true, true, true);
    }

    public function refreshUser(UserInterface $user) {

        if (!$user instanceof User) {
            throw new \Exception(sprintf('A interface "%s" não é suportado.'), get_class($user));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class) {

        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}
