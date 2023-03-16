<?php

namespace CodeDistortion\ClarityLogger\Tests\TestSupport;

use Illuminate\Database\Eloquent\Model;

/**
 * A sample user Model, used for testing.
 *
 * @property integer $id    The user's id.
 * @property string  $name  The user's name.
 * @property string  $email The user's email address.
 */
class UserModel extends Model
{
    /** @var array<string>|boolean The attributes that aren't mass assignable. */
    protected $guarded = false;
}
