<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
class Admin extends Authenticatable implements JWTSubject
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
      protected $table = 'tbl_admin';
    protected $fillable = [
        'name',
        'email',
        'password',
        'username', 
        'phone',
    ];

  public $incrementing = false; // Disable auto-incrementing for UUID
  protected $keyType = 'string'; // Set key type to string for UUID

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'created_at', 
        'updated_at', 
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */

      // Implementasi metode JWTSubject
      public function getJWTIdentifier()
      {
      return $this->getKey();
      }

      public function getJWTCustomClaims()
      {
      return [];
      }
}