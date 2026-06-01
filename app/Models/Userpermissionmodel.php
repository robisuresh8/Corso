<?php

namespace App\Models;

use CodeIgniter\Model;

class UserPermissionModel extends Model
{
    protected $table         = 'user_permissions';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['user_id', 'permission_id', 'created_at'];
    protected $useTimestamps = false;
}