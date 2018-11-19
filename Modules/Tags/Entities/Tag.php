<?php
/**
 * Outgoing emails.
 */

namespace Modules\Tags\Entities;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    public $timestamps = false;

    /**
     * Get tag conversations.
     */
    public function conversations()
    {
        return $this->belongsToMany('App\Conversation');
    }

    /**
     * Get conversation tags.
     * @param  [type] $conversation [description]
     * @return [type]               [description]
     */
    public static function conversationTags($conversation)
    {
        return $conversation->belongsToMany('Modules\Tags\Entities\Tag')->get();
    }

    /**
     * Normalize tag name.
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public static function normalizeName($name)
    {
        $name = trim($name);
        $name = mb_strtolower($name);

        return $name;
    }

    /**
     * Decrease counter.
     * @return [type] [description]
     */
    public function decCounter()
    {
        $this->counter--;
        if ($this->counter < 0) {
            $this->counter = 0;
        }
    }

    /**
     * Get css class for the color.
     * @return [type] [description]
     */
    public function getColorClass()
    {
        return '';
    }

    /**
     * Get tag link.
     * @return [type] [description]
     */
    public function getUrl()
    {
        return route('conversations.search', ['f' => ['tag' => [
            $this->name
        ]]]);
    }
}
