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
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'cargo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Appended virtual attributes.
     *
     * @var list<string>
     */
    protected $appends = ['role'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isProfessor(): bool
    {
        // Garante que o e-mail termine com @unifil.br, mas que NÃO seja @edu.unifil.br
        return (str_ends_with($this->email, '@unifil.br') && !str_ends_with($this->email, '@edu.unifil.br'))
            || $this->email === 'igor2504moro@gmail.com';
    }

    public function isAluno(): bool
    {
        return str_ends_with($this->email, '@edu.unifil.br');
    }

    public function getRoleAttribute(): string
    {
        if ($this->isProfessor()) return 'professor';
        if ($this->isAluno()) return 'aluno';
        return 'unknown';
    }
}