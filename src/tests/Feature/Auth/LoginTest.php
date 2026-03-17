<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test__一般ユーザーログイン時にメールアドレスが未入力の場合のバリデーションエラー()
    {
        $response = $this->post('/login', [
            "email" => "",
            "password" => "password",
            "password_confirmation" => "password",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "email" => "メールアドレスを入力してください"
        ]);
    }

    public function test__一般ユーザーログイン時にパスワードが未入力の場合のバリデーションエラー()
    {
        $response = $this->post('/login', [
            "email" => "test@test.com",
            "password" => "",
            "password_confirmation" => "",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "password" => "パスワードを入力してください"
        ]);
    }

    public function test__一般ユーザーログイン時に登録内容と一致しない場合のバリデーションエラー()
    {

        $user = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("password123"),
            "is_admin" => 0,
            "email_verified_at" => now(),
        ]);

        $response = $this->post('/login', [
            "email" => "test1@test.com",
            "password" => "password456",
            "password_confirmation" => "password456",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "email" => "ログイン情報が登録されていません"
        ]);
    }
}
