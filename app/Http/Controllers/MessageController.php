<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class MessageController extends Controller
{
    //
    public function index()
    {
        $messages = Message::orderBy('created_at', 'desc')
            ->whereNull('reply_id')
            ->with('user')
            ->with('replies')
            ->paginate(25);
        return view('messages', compact('messages'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'reply' => 'nullable|exists:messages,id',
        ]);

        if ($validator->fails()) {
            return Response()->json([
                'success' => false,
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        $message = Message::create([
            'message' => strip_tags($request->message, '<img>'),
            'user_id' => Auth::id(),
            'reply_id' => !empty($request->reply) ? $request->reply : null
        ]);

        $message->load('user');
        $message->date = $message->created_at->format('d.m.Y h:i');

        return Response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'id' => 'required|exists:messages,id',
        ]);

        if ($validator->fails()) {
            return Response()->json([
                'success' => false,
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        $message = Message::where('id', $request->id)
            ->where('user_id', Auth::id())
            ->first();

        if (empty($message) || $message->replies->count() > 0){
            return Response()->json([
                'success' => false,
                'message' => 'Запрещено редактировать'
            ], 403);
        }

        $message->message = strip_tags($request->message, '<img>');
        $message->save();

        return Response()->json([
            'success' => true,
            'message' => $message->message
        ]);
    }

    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|mimes:png,jpg,jpeg,png,webp,svg|dimensions:min_width=100,min_height=100',
        ]);

        if ($validator->fails()) {
            return Response()->json([
                'success' => false,
                'errors' => $validator->getMessageBag()
            ], 400);
        }
        $temp_image = $request->file('image');
        $image = Image::make($temp_image);
        $width = $image->getWidth();
        $height = $image->getHeight();

        if ($width > 500 || $height > 500) {
            for ($i = 500; $i > 100; $i -= 100) {
                $image->resize($i, $i, function ($constraint) {
                    $constraint->aspectRatio();
                });
                if ($image->filesize() < 100) {
                    break;
                }
            }

        }

        $dir = public_path('images');

        if (\File::exists($dir) == false) {
            \File::makeDirectory($dir, 0777, true, true);
        }

        $fileName = time() . '.' . $temp_image->extension();
        $pathSave = $dir . DIRECTORY_SEPARATOR . $fileName;

        $image->save($pathSave);

        return Response()->json([
            'success' => true,
            'url' => asset('images/' . $fileName)
        ]);
    }
}
