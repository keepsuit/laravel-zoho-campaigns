<?php

namespace Keepsuit\Campaigns\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Keepsuit\Campaigns\Models\Token;
use Keepsuit\Campaigns\Models\TokenType;

class TokenFactory extends Factory
{
    protected $model = Token::class;

    public function definition(): array
    {
        return [
            'token_type' => TokenType::Access,
            'token' => 'access-token',
            'expires_at' => now()->addHour(),
        ];
    }

    public function refresh(): TokenFactory
    {
        return $this->state(function (array $attributes) {
            return [
                'token_type' => TokenType::Refresh,
                'token' => 'refresh-token',
                'expires_at' => null,
            ];
        });
    }
}
