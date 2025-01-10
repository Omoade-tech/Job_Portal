<?php
namespace App\Traits;

use Illuminate\Support\Str;
use App\Models\Token;

trait HasApiTokens
{
    /**
     * Create a new token for the user.
     */
    public function createToken($name)
    {
        $token = Str::random(60);

        $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $token), // Store the hashed token
            'abilities' => json_encode(['*']),
        ]);

        return (object) [
            'plainTextToken' => $token,
        ];
    }
}