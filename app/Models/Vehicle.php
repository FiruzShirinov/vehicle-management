<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'make',
        'model',
        'year',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function assignedUser()
    {
        return $this->users()->first();
    }

    public function isAssigned()
    {
        return $this->users()->exists();
    }

    public function assignedUserIs(User $user)
    {
        return $this->users()->where('users.id', $user->id)->exists();
    }

    public function assignUser(User $user)
    {
        if ($this->isAssigned()) {
            return $this->assignedUser();
        }
        $this->users()->attach($user->id);
        return $user;
    }

    public function unassignUser(User $user)
    {
        if ($this->isAssigned() && $this->assignedUserIs($user)) {
            $this->users()->detach($user->id);
        }
    }
}
