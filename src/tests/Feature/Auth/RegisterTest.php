<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_一般ユーザー会員登録時の名前が未入力の場合のバリデーションエラー()
    {

        $response = $this->post('/register', [
            "name" => "",
            "email" => "test@test.com",
            "password" => "password123",
            "password_confirmation" => "password123",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "name" => "お名前を入力してください"
        ]);
    }

    public function test__一般ユーザー会員登録時のメールアドレスが未入力の場合のバリデーションエラー()
    {
        $response = $this->post('/register', [
            "name" => "テスト",
            "email" => "",
            "password" => "password123",
            "password_confirmation" => "password123",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "email" => "メールアドレスを入力してください"
        ]);
    }

    public function test__一般ユーザー会員登録時のパスワードが8文字未満の場合のバリデーションエラー()
    {
        $response = $this->post('/register', [
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => "1234567",
            "password_confirmation" => "1234567",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "password" => "パスワードは8文字以上で入力してください"
        ]);
    }

    public function test__一般ユーザー会員登録時のパスワードが統一しない場合のバリデーションエラー()
    {
        $response = $this->post('/register', [
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => "password123",
            "password_confirmation" => "password456",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "password_confirmation" => "パスワードと一致しません"
        ]);
    }

    public function test__一般ユーザー会員登録時のパスワードが未入力の場合のバリデーションエラー()
    {
        $response = $this->post('/register', [
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => "",
            "password_confirmation" => "",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            "password" => "パスワードを入力してください",
            "password_confirmation" => "確認用パスワードを入力してください"
        ]);
    }

    public function test__一般ユーザーが会員登録した際、入力したデータが正常に保存される()
    {
        $data = [
            "name" => "テスト",
            "email" => "test@test.com",
            "password" => "password123",
            "password_confirmation" => "password123",
        ];

        $response = $this->post('/register', $data);

        $this->assertDatabaseHas("users",[
            "name" => "テスト",
            "email" => "test@test.com",
            "email_verified_at" => null,
        ]);

        $response->assertRedirect(route('verification.notice'));

    }
}
