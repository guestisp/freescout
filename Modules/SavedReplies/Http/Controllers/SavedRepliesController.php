<?php

namespace Modules\SavedReplies\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Conversation;
use App\Mailbox;
use App\User;
use Modules\SavedReplies\Entities\SavedReply;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;

class SavedRepliesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index($id)
    {
        $mailbox = Mailbox::findOrFail($id);
        //$this->authorize('index', $mailbox);
        //$this->authorize('updateMailboxSavedReplies', SavedReply::class);
        
        $user = auth()->user();
        if (!SavedReply::userCanUpdateMailboxSavedReplies($user, $mailbox)) {
            \Helper::denyAccess();
        }

        // if ($user->isAdmin() || ($user->hasPermission(User::PERM_EDIT_SAVED_REPLIES) && $mailbox->userHasAccess($user->id))) {
        //     // OK
        // } else {
        //     \Helper::denyAccess();
        // }

        $saved_replies = SavedReply::where('mailbox_id', $mailbox->id)->get();

        return view('savedreplies::index', [
            'mailbox'       => $mailbox,
            'saved_replies' => $saved_replies
        ]);
    }

/**
     * Conversations ajax controller.
     */
    public function ajax(Request $request)
    {
        $response = [
            'status' => 'error',
            'msg'    => '', // this is error message
        ];

        $user = auth()->user();

        switch ($request->action) {

            // Create saved reply
            case 'create':
                
                $name = $request->name;
                $text = $request->text;

                if (!$name) {
                    $response['msg'] = __('Saved reply name is required');
                } elseif (!$text) {
                    $response['msg'] = __('Saved reply text is required');
                }

                $mailbox = Mailbox::find($request->mailbox_id);

                if (!$mailbox) {
                    $response['msg'] = __('Mailbox not found');
                }

                if (!$response['msg'] && !SavedReply::userCanUpdateMailboxSavedReplies($user, $mailbox)) {
                    $response['msg'] = __('Not enough permissions');
                }
                
                if (!$response['msg']) {

                    $saved_reply = new SavedReply();
                    $saved_reply->mailbox_id = $mailbox->id;
                    $saved_reply->name = $name;
                    $saved_reply->text = $text;
                    $saved_reply->user_id = $user->id;
                    $saved_reply->save();

                    $response['id']     = $saved_reply->id;
                    $response['status'] = 'success';

                    if ((int)$request->from_reply) {
                        $response['msg_success'] = __('Created new Saved Reply');
                    } else {
                        // Flash
                        \Session::flash('flash_success_floating', __('Created new Saved Reply'));
                    }
                }
                break;

            // Update saved reply
            case 'update':
                
                $name = $request->name;
                $text = $request->text;

                if (!$name) {
                    $response['msg'] = __('Saved reply name is required');
                } elseif (!$text) {
                    $response['msg'] = __('Saved reply text is required');
                }

                $saved_reply = SavedReply::find($request->saved_reply_id);

                if (!$saved_reply) {
                    $response['msg'] = __('Saved reply not found');
                }

                if (!$response['msg'] && !SavedReply::userCanUpdateMailboxSavedReplies($user, $saved_reply->mailbox)) {
                    $response['msg'] = __('Not enough permissions');
                }
                
                if (!$response['msg']) {

                    $saved_reply->name = $name;
                    $saved_reply->text = $text;
                    $saved_reply->save();

                    $response['status'] = 'success';
                    $response['msg_success'] = __('Updated Saved Reply');
                }
                break;

            // Get saved reply
            case 'get':
               
                $saved_reply = SavedReply::find($request->saved_reply_id);

                if (!$saved_reply) {
                    $response['msg'] = __('Saved reply not found');
                }
                
                if (!$response['msg'] && !$saved_reply->mailbox->userHasAccess($user->id)) {
                    $response['msg'] = __('Not enough permissions');
                }

                if (!$response['msg']) {

                    $replace_data = [];
                    if (!empty($request->conversation_id)) {
                        $conversation = Conversation::find($request->conversation_id);
                        if ($conversation) {
                            $replace_data = [
                                'conversation' => $conversation,
                                'mailbox'      => $conversation->mailbox,
                                'customer'     => $conversation->customer,
                            ];
                        }
                    }

                    $response['name'] = $saved_reply->name;
                    $response['text'] = \MailHelper::replaceMailVars($saved_reply->text, $replace_data);

                    $response['status'] = 'success';
                }
                break;

            // Delete saved reply
            case 'delete':
               
                $saved_reply = SavedReply::find($request->saved_reply_id);

                if (!$saved_reply) {
                    $response['msg'] = __('Saved reply not found');
                }
                
                if (!$response['msg'] && !SavedReply::userCanUpdateMailboxSavedReplies($user, $saved_reply->mailbox)) {
                    $response['msg'] = __('Not enough permissions');
                }

                if (!$response['msg']) {
                    $saved_reply->delete();

                    $response['status'] = 'success';
                    $response['msg_success'] = __('Deleted Saved Reply');
                }
                break;

            default:
                $response['msg'] = 'Unknown action';
                break;
        }

        if ($response['status'] == 'error' && empty($response['msg'])) {
            $response['msg'] = 'Unknown error occured';
        }

        return \Response::json($response);
    }

    /**
     * Ajax controller.
     */
    public function ajaxHtml(Request $request)
    {
        switch ($request->action) {
            case 'create':
                $text = Input::get('text');

                return view('savedreplies::create', [
                    'text' => $text,
                ]);
        }

        abort(404);
    }
}
