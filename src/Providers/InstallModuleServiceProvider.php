<?php namespace WebEd\Plugins\Blog\Providers;

use Illuminate\Support\ServiceProvider;
use Schema;
use Illuminate\Database\Schema\Blueprint;

class InstallModuleServiceProvider extends ServiceProvider
{
    protected $module = 'WebEd\Plugins\Blog';

    protected $moduleAlias = 'webed-blog';

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

    private function booted()
    {
        $this->createSchema();

        acl_permission()
            ->registerPermission('View posts', 'view-posts', $this->moduleAlias)
            ->registerPermission('Create posts', 'create-posts', $this->moduleAlias)
            ->registerPermission('Update posts', 'update-posts', $this->moduleAlias)
            ->registerPermission('Delete posts', 'delete-posts', $this->moduleAlias)
            ->registerPermission('View categories', 'view-categories', $this->moduleAlias)
            ->registerPermission('Create categories', 'create-categories', $this->moduleAlias)
            ->registerPermission('Update categories', 'update-categories', $this->moduleAlias)
            ->registerPermission('Delete categories', 'delete-categories', $this->moduleAlias)
            ->registerPermission('View tags', 'view-tags', $this->moduleAlias)
            ->registerPermission('Create tags', 'create-tags', $this->moduleAlias)
            ->registerPermission('Update tags', 'update-tags', $this->moduleAlias)
            ->registerPermission('Delete tags', 'delete-tags', $this->moduleAlias);
    }

    private function createSchema()
    {
        /**
         * Create table categories
         */
        Schema::create('categories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('parent_id')->unsigned()->nullable();
            $table->string('title');
            $table->string('page_template', 255)->nullable();
            $table->string('slug', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('thumbnail', 255)->nullable();
            $table->string('keywords', 255)->nullable();
            $table->enum('status', ['activated', 'disabled'])->default('activated');
            $table->integer('order')->default(0);
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });

        /**
         * Create table posts
         */
        Schema::create('posts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('title');
            $table->string('page_template', 255)->nullable();
            $table->string('slug', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('thumbnail', 255)->nullable();
            $table->string('keywords', 255)->nullable();
            $table->integer('order')->default(0);
            $table->enum('status', ['activated', 'disabled'])->default('activated');
            $table->integer('category_id')->unsigned()->nullable();
            $table->tinyInteger('is_featured')->default(0);
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });

        /**
         * Create mapper table for categories and posts
         */
        Schema::create('posts_categories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('post_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->unique(['post_id', 'category_id']);
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });

        /**
         * Tags
         */
        Schema::create('blog_tags', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('title');
            $table->string('slug', 255)->nullable();
            $table->enum('status', ['activated', 'disabled'])->default('activated');
            $table->integer('order')->default(0);
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });

        /**
         * Mapper table for posts and blog_tags
         */
        Schema::create('posts_tags', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('post_id')->unsigned();
            $table->integer('tag_id')->unsigned();
            $table->unique(['post_id', 'tag_id']);
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('blog_tags')->onDelete('cascade');
        });
    }
}
