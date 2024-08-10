<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devisi extends Model
{
    use HasFactory;
    protected $table = 'tbl_devisi';
     protected $fillable = [
     'id',
     'name',
     ];
     

      protected $hidden = [
      'created_at',
      'updated_at',
      ];

     public $incrementing = false; // Disable auto-incrementing for UUID
     protected $keyType = 'string'; 

   public function employees()
   {
   return $this->hasMany(Pegawai::class, 'division_id', 'id');
   }
}