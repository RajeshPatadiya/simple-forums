<?php namespace App\Domains\Users;

use CodeIgniter\Model;

/**
 * UserModel Model
 *
 * Generated by Vulcan at 2017-06-29 22:50pm
 */
class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';

	protected $returnType = 'App\Domains\Users\User';

	protected $useSoftDeletes = true;
	protected $useTimestamps = true;

	protected $allowedFields = [
		'email', 'username', 'password_hash', 'reset_hash', 'activate_hash', 'status', 'status_message',
		'active', 'deleted', 'force_pass_reset'
	];

    protected $dateFormat    = 'datetime';

    protected $validationRules    = [
    	'email'             => 'valid_email',
	    'username'          => 'min_length[5]|max_length[255]',
	    'password_hash'     => 'max_length[255]',
	    'reset_hash'        => 'max_length[40]',
	    'activate_hash'     => 'max_length[40]',
	    'status'            => 'max_length[255]',
	    'status_message'    => 'max_length[255]',
	    'active'            => 'max_length[1]|integer|in_list[0,1]',
	    'force_pass_reset'  => 'max_length[1]|integer|in_list[0,1]',
	    'deleted'           => 'max_length[1]|integer|in_list[0,1]',
    ];
    protected $validationMessages = [];
    protected $skipValidation     = false;

}
