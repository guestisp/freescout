<?php

namespace Modules\Tags\Providers;

use Modules\Tags\Entities\ConversationTag;
use Modules\Tags\Entities\Tag;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

// Module alias
define('TAGS_MODULE', 'tags');

class TagsServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

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
        $this->hooks();
    }

    /**
     * Module hooks.
     */
    public function hooks()
    {
        // Add module's CSS file to the application layout.
        \Eventy::addFilter('stylesheets', function($styles) {
            $styles[] = \Module::getPublicPath(TAGS_MODULE).'/css/module.css';
            return $styles;
        });

        // Add module's JS file to the application layout.
        \Eventy::addFilter('javascripts', function($javascripts) {
            $javascripts[] = \Module::getPublicPath(TAGS_MODULE).'/js/laroute.js';
            $javascripts[] = \Module::getPublicPath(TAGS_MODULE).'/js/module.js';
            return $javascripts;
        });

        // Show tags button in conversation
        \Eventy::addAction('conversation.action_buttons', function($conversation, $mailbox) {
            echo \View::make('tags::partials/action_button', ['conversation' => $conversation])->render();
        }, 10, 2);

        // Show tags next to the conversation title in conversation
        \Eventy::addAction('conversation.after_subject', function($conversation, $mailbox) {
            $tags = Tag::conversationTags($conversation);
            echo \View::make('tags::partials/subject_tags', ['tags' => $tags])->render();
        }, 10, 2);

        // JavaScript in the bottom
        \Eventy::addAction('javascript', function() {
            if (\Route::is('conversations.view')) {
                echo 'initConvTags("'.__('Remove Tag').'");';
            }
        });

        // Preload tags for all conversations in the table
        \Eventy::addFilter('conversations_table.preload_table_data', function($conversations) {
            $ids = $conversations->pluck('id')->unique()->toArray();
            if (!$ids) {
                return $conversations;
            }

            $conversations_tags = ConversationTag::whereIn('conversation_id', $ids)->get();
            if (!count($conversations_tags)) {
                return $conversations;
            }
            $tag_ids = $conversations_tags->pluck('tag_id')->unique()->toArray();

            $tags = Tag::whereIn('id', $tag_ids)->get();
            if (!count($tags)) {
                return $conversations;
            }

            foreach ($conversations as $i => $conversation) {
                // Find conversation tags
                $collected_tags = [];
                foreach ($conversations_tags as $conversation_tag) {
                    if ($conversation_tag->conversation_id == $conversation->id) {
                        $collected_tags[] = $tags->find($conversation_tag->tag_id);
                    }
                }
                $conversation->tags = $collected_tags;
            }

            return $conversations;
        });

        // Show tags in the conersations table
        \Eventy::addAction('conversations_table.before_subject', function($conversation) {
            // Show conversation tags
            if (!empty($conversation->tags)) {
                foreach ($conversation->tags as $tag) {
                    echo \View::make('tags::partials/conversation_list_tag', ['tag' => $tag])->render();
                }
                echo '&nbsp;';
            }
        });

        // Filter by tag in search
        \Eventy::addFilter('search.apply_filters', function($query_conversations, $filters) {

            if (!empty($filters['tag'])) {
                $tag_names = [];
                foreach ($filters['tag'] as $tag_name) {
                    $tag_name = Tag::normalizeName($tag_name);
                    if ($tag_name) {
                        $tag_names[] = $tag_name;
                    }
                }

                if ($tag_names) {
                    $query_conversations
                        ->join('conversation_tag', function ($join) {
                            $join->on('conversations.id', '=', 'conversation_tag.conversation_id');
                        })
                        ->join('tags', function ($join) {
                            $join->on('tags.id', '=', 'conversation_tag.tag_id');
                        })
                        ->whereIn('tags.name', $tag_names);
                }
            }

            return $query_conversations;
        }, 10, 2);
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
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('tags.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'tags'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/tags');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/tags';
        }, \Config::get('view.paths')), [$sourcePath]), 'tags');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/tags');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'tags');
        } else {
            $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'tags');
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
