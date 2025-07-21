<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        'is_admin' => 'boolean',
        ];

    /**
     * Get the time logs for the user.
     */
    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Get total hours logged by this user.
     */
    public function getTotalHoursLogged(): float
    {
        return $this->timeLogs()->sum('total_minutes') / 60;
    }

    /**
     * Get total tasks logged by this user.
     */
    public function getTotalTasksLogged(): int
    {
        return $this->timeLogs()->count();
    }

    /**
     * Get unique dates logged by this user.
     */
    public function getUniqueDatesLogged(): int
    {
        return $this->timeLogs()->distinct('work_date')->count('work_date');
    }
}
