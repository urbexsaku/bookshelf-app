<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ユーザーを登録できる
     */
    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('books.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * 名前が未入力の場合、バリデーションメッセージが表示される
     */
    public function test_validation_message_is_displayed_when_name_is_empty(): void
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('name');

        $this->assertEquals(
            'お名前を入力してください',
            session('errors')->first('name')
        );
    }

    /**
     * メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_validation_message_is_displayed_when_email_is_empty(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertEquals(
            'メールアドレスを入力してください',
            session('errors')->first('email')
        );
    }

    /**
     * パスワードが8文字未満の場合、バリデーションメッセージが表示される
     */
    public function test_validation_message_is_displayed_when_password_is_less_than_8_characters(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertSessionHasErrors('password');

        $this->assertEquals(
            'パスワードは8文字以上で入力してください',
            session('errors')->first('password')
        );
    }

    /**
     * パスワードが一致しない場合、バリデーションメッセージが表示される
     */
    public function test_validation_message_is_displayed_when_password_does_not_match(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('password');

        $this->assertEquals(
            'パスワードと一致しません',
            session('errors')->first('password')
        );
    }

    /**
     * パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_validation_message_is_displayed_when_password_is_empty(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');

        $this->assertEquals(
            'パスワードを入力してください',
            session('errors')->first('password')
        );
    }
}
