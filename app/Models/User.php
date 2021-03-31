<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'password',
        'fcm_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        // 'password',
        // 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
    }

    public function profile()
    {
        return $this->hasOne('App\Models\Profile');
    }

    public function scopeExceptMe($query, $id)
    {
        return $query->where('id', '<>', $id);
    }

    public function FollowerUserList() 
    {
        return $this->belongsTo('App\Models\Follow', 'followes_id');
    }
    
    public function FollowesUserList() 
    {
        return $this->hasMany('App\Models\Follow', 'follower_id');
    }

    public function scopeDistance($query, $latitude, $longitude, $distance, $user_id, $list)
    {
        return $query->whereRaw('((6371 * 2 * atan2(sqrt(sin((pi()/180) * ('.$latitude.' - users.latitude) / 2) * sin((pi()/180) * ('.$latitude.' - users.latitude) / 2) + cos((pi()/180) * (users.latitude)) * cos((pi()/180) * ('.$latitude.')) * sin((pi()/180) * ('.$longitude.' - users.longitude) / 2) * sin((pi()/180) * ('.$longitude.' - users.longitude) / 2)), sqrt(1 - sin((pi()/180) * ('.$latitude.' - users.latitude) / 2) * sin((pi()/180) * ('.$latitude.' - users.latitude) / 2) + cos((pi()/180) * (users.latitude)) * cos((pi()/180) * ('.$latitude.')) * sin((pi()/180) * ('.$longitude.' - users.longitude) / 2) * sin((pi()/180) * ('.$longitude.' - users.longitude) / 2))) <= '.$distance. ') && (users.id <> '.$user_id.')) || (users.id IN ('.$list. ') && (users.id <> '.$user_id.'))');
    }
}
