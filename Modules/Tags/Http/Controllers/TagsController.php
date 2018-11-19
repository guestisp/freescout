<?php

namespace Modules\Tags\Http\Controllers;

use Modules\Tags\Entities\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class TagsController extends Controller
{
    /**
     * Ajax.
     */
    public function ajax(Request $request)
    {
        $response = [
            'status' => 'error',
            'msg'    => '', // this is error message
        ];

        switch ($request->action) {
            case 'add':
                if (!empty($request->tag_names)) {
                    foreach ($request->tag_names as $tag_name) {

                        $tag_name = Tag::normalizeName($tag_name);

                        if ($tag_name) {
                            $tag = Tag::where(['name' => $tag_name])->first();
                            if (!$tag) {
                                $tag = new Tag();
                                $tag->name = $tag_name;
                                $tag->counter++;
                                $tag->save();
                            } else {
                                $tag->counter++;
                                $tag->save();
                            }
                            // Attach tag to the conversation.
                            $tag->conversations()->attach($request->conversation_id);

                            $response['tags'][] = [
                                'name' => $tag->name,
                                'url'  => $tag->getUrl(),
                            ];
                        }
                    }
                }
                $response['status'] = 'success';
                break;

            case 'remove':
                $tag_name = Tag::normalizeName($request->tag_name);

                if ($tag_name) {
                    $tag = Tag::where(['name' => $tag_name])->first();
                    
                    if ($tag) {
                        $tag->conversations()->detach($request->conversation_id);
                        $tag->decCounter();
                        if ($tag->counter == 0) {
                            $tag->delete();
                        } else {
                            $tag->save();
                        }
                    }
                }

                $response['status'] = 'success';
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
}
