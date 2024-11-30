## Sanctum Auth API開発テスト

参考
https://www.twilio.com/ja-jp/blog/build-restful-api-php-laravel-sanctum-jp

# プロジェクト作成
```
composer create-project --prefer-dist laravel/laravel sanctum-auth-api-test
```

# Laravel Sanctumを追加
```
composer require laravel/sanctum
```

# Laravel Sanctumの設定と移行ファイルを公開
```
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

# api.php
```
php artisan install:api
```


# メール認証
User.php
```
//追記orコメントアウト
use Illuminate\Contracts\Auth\MustVerifyEmail;

//implements MustVerifyEmailを加える
class User extends Authenticatable implements MustVerifyEmail
```
api.php
```
//->name('verification.verify')を付け加える
Route::post('/register', [AuthController::class, 'register'])->name('verification.verify');
```


# パスワードリセット
参考
https://qiita.com/free-coder/items/2b5be315e651205e049d

公式DOC
https://laravel.com/docs/11.x/passwords


App\Providers\AppServiceProvider.php
```
//use Illuminate\Support\ServiceProvider;//消す

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;//add
use App\Models\User;//add
use Illuminate\Auth\Notifications\ResetPassword;//add
use Illuminate\Support\Facades\Request;// 現在のドメイン部分を取得するためReque
use Illuminate\Support\Facades\Gate;//add

    ///省略///

    public function boot(): void
    {
        //下記を追加して編集
        $this->registerPolicies();
        ResetPassword::createUrlUsing(function (User $user, string $token) {
        $currentUrl = Request::root(); // 現在のドメイン部分を取得
            return $currentUrl . '/password/reset?token=' . $token . '&email=' . $user->email;         
        });
    }

    ///省略///

```
