<?php
/**
 * Outgoing emails.
 */

namespace Modules\SavedReplies\Entities;

use App\Mailbox;
use App\User;
use Illuminate\Database\Eloquent\Model;

class SavedReply extends Model
{
    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get mailbox.
     */
    public function mailbox()
    {
        return $this->belongsTo('App\Mailbox');
    }

    /**
     * Threads created from saved reply.
     */
    public function threads()
    {
        return $this->hasMany('App\Thread');
    }

    public static function userCanUpdateMailboxSavedReplies(User $user, Mailbox $mailbox)
    {
        if ($user->isAdmin() || ($user->hasPermission(User::PERM_EDIT_SAVED_REPLIES) && $mailbox->userHasAccess($user->id))) {
            return true;
        } else {
            return false;
        }
    }
}
