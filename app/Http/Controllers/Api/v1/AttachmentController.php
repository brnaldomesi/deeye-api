<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Models\User;
use App\Models\PostAttachment;
use App\Models\Attachment;

use App\Repositories\AttachmentRepository;

class AttachmentController extends Controller
{
    protected $attRepository;

    public function __construct(AttachmentRepository $attRepository)
    {
        $this->attRepository = $attRepository;
    }

    public function delete($id)
    {
        $post_attachment = PostAttachment::find($id);
        $attachment = Attachment::find($post_attachment->attachment_id);
        $post_attachment->delete();
        $attachment->delete();
        return response()->json(["success"]);
    }
    
    public function store(Request $request)
    {
        $f_rules = [
            'file' => [
                'required',
                'file'
            ],
            'file_type' => [
                'required',
                Rule::in(['Image','Url','Video'])
            ]
        ];
        $inputs = $request->all();
        $validator = Validator::make($inputs, $f_rules);
        if ($validator->fails()){
            return response($p_validator->messages()->toJson(), 400)->header('Content-Type', 'text/json');
        }
        $attach;
        if ($request->file_type == 'Url') {
            $attach = $this->attRepository->saveAttachFromUploaded($request->file);
        } else {
            $attach = $this->attRepository->saveAttachFromUploaded($request->file('file'), $request->file_type);
        }
        return response(json_encode($attach), 201)->header('Content-Type', 'text/json');
    }

}