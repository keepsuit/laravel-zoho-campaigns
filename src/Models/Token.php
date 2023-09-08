<?php

namespace Keepsuit\Campaigns\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property int         $id
 * @property string      $token
 * @property TokenType   $token_type
 * @property Carbon|null $expires_at
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 */
class Token extends Model
{
    protected $table = 'zoho_campaigns_tokens';

    protected $guarded = [];

    protected $casts = [
        'token' => 'encrypted',
        'token_type' => TokenType::class,
        'expires_at' => 'datetime',
    ];

    public static function findRefreshToken(): ?Token
    {
        return static::query()
            ->where('token_type', TokenType::Refresh)
            ->first();
    }

    public static function findActiveAccessToken(): ?Token
    {
        return static::query()
            ->where('token_type', TokenType::Access)
            ->where('expires_at', '>', now())
            ->first();
    }

    public static function saveRefreshToken(string $refreshToken): Token
    {
        return DB::transaction(function () use ($refreshToken) {
            Token::query()->where('token_type', TokenType::Refresh)->delete();

            return Token::create([
                'token_type' => TokenType::Refresh,
                'token' => $refreshToken,
                'expires_at' => null,
            ]);
        });
    }

    public static function saveAccessToken(string $accessToken, int $expiresIn): Token
    {
        return DB::transaction(function () use ($accessToken, $expiresIn) {
            Token::query()->where('token_type', TokenType::Access)->delete();

            return Token::create([
                'token_type' => TokenType::Access,
                'token' => $accessToken,
                'expires_at' => now()->addSeconds($expiresIn),
            ]);
        });
    }

    public function isValid(Carbon $validAt = null): bool
    {
        if ($this->expires_at === null) {
            return true;
        }

        return $this->expires_at->isAfter($validAt ?? Carbon::now());
    }
}
