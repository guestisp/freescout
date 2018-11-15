<?php

namespace Modules\SavedReplies\Providers;

use App\User;
use Illuminate\Support\ServiceProvider;
//use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Modules\SavedReplies\Entities\SavedReply;

define('SR_MODULE', 'savedreplies');

class SavedRepliesServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    // protected $policies = [
    //     'Modules\SavedReplies\Entities\SavedReply' => 'Modules\SavedReplies\Policies\SavedReplyPolicy',
    // ];

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        //$this->registerPolicies();
        $this->hooks();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Module hooks.
     */
    public function hooks()
    {
        // Add Saved Replies item to the mailbox menu
        \Eventy::addAction('mailboxes.settings.menu', function($mailbox) {
            echo \View::make('savedreplies::partials/settings_menu', ['mailbox' => $mailbox])->render();
        }, 20);

        // Show saved replies in reply editor
        \Eventy::addAction('reply_form.after', function($conversation) {
            $saved_replies = SavedReply::where('mailbox_id', $conversation->mailbox->id)->get();
            echo \View::make('savedreplies::partials/editor_dropdown', ['saved_replies' => $saved_replies])->render();
        });

        // Add module's JS file to the application layout.
        \Eventy::addFilter('javascripts', function($value) {
            array_push($value, '/modules/'.SR_MODULE.'/js/laroute.js');
            array_push($value, '/modules/'.SR_MODULE.'/js/vars.js');
            array_push($value, '/modules/'.SR_MODULE.'/js/module.js');
            return $value;
        }, 10, 1);

        // Determine whether the user can view mailboxes menu.
        \Eventy::addFilter('user.can_view_mailbox_menu', function($value, $user) {
            return $value || $user->hasPermission(User::PERM_EDIT_SAVED_REPLIES);
        }, 10, 2);

        // Redirect user to the accessible mailbox settings route.
        \Eventy::addFilter('mailbox.accessible_settings_route', function($value, $user, $mailbox) {
            if ($user->hasPermission(User::PERM_EDIT_SAVED_REPLIES) && $mailbox->userHasAccess($user->id)) {
                return 'mailboxes.saved_replies';
            } else {
                return $value;
            }
        }, 10, 3);

        // Select main menu item.
        \Eventy::addFilter('menu.selected', function($menu) {
            $menu['manage']['mailboxes'][] = 'mailboxes.saved_replies';

            return $menu;
        });
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('savedreplies.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'savedreplies'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/savedreplies');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/savedreplies';
        }, \Config::get('view.paths')), [$sourcePath]), 'savedreplies');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/savedreplies');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'savedreplies');
        } else {
            $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'savedreplies');
        }
    }

    /**
     * Register an additional directory of factories.
     * @source https://github.com/sebastiaanluca/laravel-resource-flow/blob/develop/src/Modules/ModuleServiceProvider.php#L66
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
