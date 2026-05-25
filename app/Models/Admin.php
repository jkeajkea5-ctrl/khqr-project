<?php

namespace App\Models;

use App\Models\Support\AppAuthenticatable;
use App\Support\MediaPath;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Admin extends AppAuthenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_STOCK_CONTROLLER = 'stock_controller';
    public const ROLE_EMPLOYEE = 'employee';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function getPhotoAttribute($value): ?string
    {
        return MediaPath::resolve($value);
    }

    public function setPhotoAttribute($value): void
    {
        $this->attributes['photo'] = MediaPath::normalize($value);
    }

    public static function roleOptions(): array
    {
        return [
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_STOCK_CONTROLLER => 'Stock Controller',
            self::ROLE_EMPLOYEE => 'Employee',
        ];
    }

    public function hasRole(string $role): bool
    {
        return $this->currentRole() === $role;
    }

    public function hasAnyRole(string ...$roles): bool
    {
        return in_array($this->currentRole(), $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function canManageAdmins(): bool
    {
        return $this->isAdmin();
    }

    public function canViewUsers(): bool
    {
        return $this->hasAnyRole(
            self::ROLE_ADMIN,
            self::ROLE_STOCK_CONTROLLER,
            self::ROLE_EMPLOYEE,
        );
    }

    public function canViewProducts(): bool
    {
        return $this->canViewUsers();
    }

    public function canManageProducts(): bool
    {
        return $this->hasAnyRole(self::ROLE_ADMIN, self::ROLE_STOCK_CONTROLLER);
    }

    public function canDeleteProducts(): bool
    {
        return $this->isAdmin();
    }

    public function canViewCategories(): bool
    {
        return $this->canViewUsers();
    }

    public function canManageCategories(): bool
    {
        return $this->hasAnyRole(self::ROLE_ADMIN, self::ROLE_STOCK_CONTROLLER);
    }

    public function canDeleteCategories(): bool
    {
        return $this->isAdmin();
    }

    public function canManageSlides(): bool
    {
        return $this->isAdmin();
    }

    public function canViewOrders(): bool
    {
        return $this->canViewUsers();
    }

    public function canViewPayments(): bool
    {
        return $this->canViewUsers();
    }

    public function roleLabel(): string
    {
        return self::roleOptions()[$this->currentRole()] ?? ucfirst(str_replace('_', ' ', $this->currentRole()));
    }

    public function photoStoragePath(): ?string
    {
        return MediaPath::normalize($this->getRawOriginal('photo'));
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];
        $initials = collect($parts)
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'A';
    }

    private function currentRole(): string
    {
        return $this->role ?: self::ROLE_ADMIN;
    }
}
