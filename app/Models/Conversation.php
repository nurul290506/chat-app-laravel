<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    // Kolom ini yang wajib kita tambahkan agar Laravel mengizinkan pengisian data
    protected $fillable = [
        'name',
        'is_group'
    ];

    // Relasi ke tabel users (Satu room chat bisa memiliki banyak user)
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    // Relasi ke tabel messages (Satu room chat memiliki banyak pesan)
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}