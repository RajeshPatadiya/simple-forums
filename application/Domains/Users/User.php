<?php namespace App\Domains\Users;

use App\Exceptions\ValidationException;
use CodeIgniter\Entity;
use Config\Services;
use Myth\Auth\Authenticate\Password;

/**
 * User Entity
 *
 * Generated by Vulcan at 2017-07-03 22:10:pm
 */
class User extends Entity
{
	protected $id;
	protected $email;
	protected $username;
	protected $password_hash;
	protected $reset_hash;
	protected $activate_hash;
	protected $status;
	protected $status_message;
	protected $active = 0;
	protected $force_pass_reset = 0;
	protected $deleted = 0;
	protected $created_at;
	protected $updated_at;

	/**
	 * Maps names used in sets and gets against unique
	 * names within the class, allowing independence from
	 * database column names.
	 *
	 * Example:
	 *  $datamap = [
	 *      'db_name' => 'class_name'
	 *  ];
	 *
	 * @var array
	 */
	protected $datamap = [];

	/**
	 * Validates and normalizes the email address before saving it to the Entity.
	 *
	 * @param string $email
	 *
	 * @return $this
	 * @throws \App\Exceptions\ValidationException
	 */
	public function setEmail(string $email)
	{
		if (! filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			throw new ValidationException(lang('users.invalidEmail'));
		}

		$this->email = strtolower($email);

		return $this;
	}

	/**
	 * Hashes the passwords and saves it to the Entity.
	 *
	 * @param string $password
	 *
	 * @return $this
	 */
	public function setPassword(string $password)
	{
		$this->password_hash = Password::hashPassword($password);

		return $this;
	}

	//--------------------------------------------------------------------
	// Banning Users
	//--------------------------------------------------------------------

	/**
	 * Bans a user and saves the message to display to the user with the reason for banning.
	 *
	 * @param string $message
	 *
	 * @return $this
	 */
	public function banUser(string $message)
	{
		$this->status = 'banned';
		$this->status_message = $message;

		return $this;
	}

	/**
	 * Is the user currently banned?
	 *
	 * @return bool
	 */
	public function isBanned()
	{
		return $this->status === 'banned';
	}

	//--------------------------------------------------------------------
	// Authorization Helpers
	//--------------------------------------------------------------------

	/**
	 * Is the user an Administrator?
	 *
	 * @return bool
	 */
	public function isAdmin(): bool
	{
		$auth = Services::authorization();

		return $auth->inGroup('admins', $this->id);
	}

	/**
	 * Is the user a Moderator?
	 *
	 * @return bool
	 */
	public function isModerator()
	{
		$auth = Services::authorization();

		return $auth->inGroup('moderators', $this->id);
	}

	/**
	 * Arbitrary check whether user is in one or more groups.
	 *
	 * @param string|array $group
	 *
	 * @return bool
	 */
	public function inGroup($group)
	{
		$auth = Services::authorization();

		return $auth->inGroup($group, $this->id);
	}

	/**
	 * Adds a user to the specified group.
	 *
	 * @param string $groupName
	 *
	 * @return bool
	 */
	public function addToGroup(string $groupName)
	{
		$auth = Services::authorization();

		return $auth->addUserToGroup($this->id, $groupName);
	}

	/**
	 * Removes the user from the specified group.
	 *
	 * @param string $groupName
	 *
	 * @return mixed
	 */
	public function removeFromGroup(string $groupName)
	{
		$auth = Services::authorization();

		return $auth->removeUserFromGroup($this->id, $groupName);
	}

	/**
	 * Checks if the user has the specified permission.
	 *
	 * @param string $permission
	 *
	 * @return bool
	 */
	public function hasPermission(string $permission): bool
	{
		$auth = Services::authorization();

		return $auth->hasPermission($permission, $this->id);
	}

	/**
	 * Adds a single permission to this user only.
	 *
	 * @param string $permission
	 *
	 * @return bool|int
	 */
	public function addPermission(string $permission)
	{
		$auth = Services::authorization();

		return $auth->addPermissionToUser($permission, $this->id);
	}

	/**
	 * Removes the permission from this user, but not from the groups they're in.
	 *
	 * @param string $permission
	 *
	 * @return bool|null
	 */
	public function removePermission(string $permission)
	{
		$auth = Services::authorization();

		return $auth->removePermissionFromUser($permission, $this->id);
	}

}
