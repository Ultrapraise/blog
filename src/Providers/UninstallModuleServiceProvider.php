<?php namespace WebEd\Plugins\Blog\Providers;

use Illuminate\Support\ServiceProvider;
use Schema;

class UninstallModuleServiceProvider extends ServiceProvider
{
    protected $module = 'WebEd\Plugins\Blog';

    protected $moduleAlias = 'blog';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        app()->booted(function () {
            $this->booted();
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }

    protected function booted()
    {
        acl_permission()
            ->unsetPermissionByModule($this->module);

        $this->dropSchema();
    }

    protected function dropSchema()
    {
        Schema::dropIfExists('posts_categories');
        Schema::dropIfExists('posts_tags');
        Schema::dropIfExists('blog_tags');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('categories');
    }
}
