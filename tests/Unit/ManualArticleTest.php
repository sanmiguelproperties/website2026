<?php

namespace Tests\Unit;

use App\Models\ManualArticle;
use Illuminate\Contracts\Auth\Authenticatable;
use PHPUnit\Framework\TestCase;

class ManualArticleTest extends TestCase
{
    public function test_article_without_required_permission_is_visible_to_manual_user(): void
    {
        $article = new ManualArticle(['required_permission' => null]);

        $this->assertTrue($article->isVisibleTo(new ManualArticleUser([])));
    }

    public function test_article_with_required_permission_is_hidden_when_user_does_not_have_it(): void
    {
        $article = new ManualArticle(['required_permission' => 'menu.users.view']);

        $this->assertFalse($article->isVisibleTo(new ManualArticleUser(['menu.clients.view'])));
        $this->assertTrue($article->isVisibleTo(new ManualArticleUser(['menu.users.view'])));
    }
}

class ManualArticleUser implements Authenticatable
{
    public function __construct(private readonly array $permissions) {}

    public function can(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    public function canAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasRole(string $role): bool
    {
        return false;
    }

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return 1;
    }

    public function getAuthPasswordName()
    {
        return 'password';
    }

    public function getAuthPassword()
    {
        return '';
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value) {}

    public function getRememberTokenName()
    {
        return 'remember_token';
    }
}
