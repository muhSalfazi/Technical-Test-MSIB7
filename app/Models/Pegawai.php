<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
   use HasFactory;

   protected $table = 'tbl_pegawai';
   protected $fillable = [
      'id',
      'name',
      'phone',
      'image',
      'division_id',
      'position'
   ];

     protected $hidden = [
     'created_at',
     'updated_at',
     ];

public $incrementing = false; // Disable auto-incrementing for UUID
protected $keyType = 'string'; // Set key type to string for UUID

   public function division()
   {
      return $this->belongsTo(Devisi::class, 'division_id', 'id');
   }
}