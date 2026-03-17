<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test__管理者ログイン時にメールアドレスが未入力の場合のバリデーションエラー()
    {
        $response = $this->post('/admin/login', [
            "email" => "",
            "password" => "password",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "email" => "メールアドレスを入力してください"
        ]);
    }

    public function test__管理者ログイン時にパスワードが未入力の場合のバリデーションエラー()
    {
        $response = $this->post('/admin/login', [
            "email" => "test@test.com",
            "password" => "",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "password" => "パスワードを入力してください"
        ]);
    }

    public function test__管理者ログイン時に登録内容と一致しない場合のバリデーションエラー()
    {

        $adminuser = User::create([
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => Hash::make("password123"),
            "is_admin" => 1,
            "email_verified_at" => null,
        ]);

        $response = $this->post('/admin/login', [
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
