<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Post;
use App\Comment;
use App\User;
use App\Http\Resources\PostResource;
use App\Http\Resources\PostWithCommentResource;
use Intervention\Image\Facades\Image;
use App\Events\NewCommentEvent;
use App\Jobs\SomeoneCommentOnYourPostJob;
use Illuminate\Support\Facades\Auth;


class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        //$posts = Post::with(['user','comments','comments.user'])->orderBy('updated_at','desc')->get();
        $postsq = Post::with('user')->orderBy('updated_at', 'desc')->paginate(5);
        $a = $postsq->toArray();
        $posts = PostResource::collection($postsq);
        return response()->json([
            'data' =>
            $posts,
            "first_page_url" => $a['first_page_url'],
            "from" =>           $a['from'],
            "last_page" =>      $a['last_page'],
            "last_page_url" =>  $a['last_page_url'],
            "next_page_url" =>  $a['next_page_url'],
            "path" =>           $a['path'],
            "per_page" =>       $a['per_page'],
            "prev_page_url" =>  $a['prev_page_url'],
            "to" =>             $a['to'],
            "total" =>          $a['total']
        ]);
        //    return $posts->toJson();


    }
    public function my_posts(Request $request)
    {

        //$posts = Post::with(['user','comments','comments.user'])->orderBy('updated_at','desc')->get();

        $postsq = Post::with('user')->where('user_id', $request->user()->id)->orderBy('updated_at', 'desc')->paginate(5);
        $a = $postsq->toArray();
        $posts = PostResource::collection($postsq);
        return response()->json([
            'data' =>
            $posts,
            "first_page_url" => $a['first_page_url'],
            "from" =>           $a['from'],
            "last_page" =>      $a['last_page'],
            "last_page_url" =>  $a['last_page_url'],
            "next_page_url" =>  $a['next_page_url'],
            "path" =>           $a['path'],
            "per_page" =>       $a['per_page'],
            "prev_page_url" =>  $a['prev_page_url'],
            "to" =>             $a['to'],
            "total" =>          $a['total']
        ]);
        return $posts->toJson();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $post = request()->validate([
            'title' => 'required',
            'content' => 'required',
            'user_id' => 'required',
            'image' => 'sometimes|file|image|max:5000'
        ]);



        // $validator = Validator::make($request->all(),[
        //     'name' => ['required', 'string', 'max:255'],
        //     'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        //     'password' => ['required', 'string', 'min:8', 'confirmed'],
        //   ]);

        //   if ($validator->fails())
        //     return response()->json([
        //         'error'=>$validator->errors(),
        //         'status_code'=>401  ],
        //                               401);




        $user_id = $request->user_id;
        $user = User::find($user_id);


        // $user->posts()->create([
        //     'content'=>$content,
        // ]);

        $post = $user->posts()->create($post);


        // other way for valdtion optinal image

        if (request()->hasFile('image')) {
            $post->update([
                'image' => request()->image->store('uploads', 'public'),
            ]);

            //  http://image.intervention.io/getting_started/installation    for more options
            $image = Image::make(public_path('storage/' . $post->image))->resize(444, 220);
            $image->save();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $post = Post::with(['user', 'comments', 'comments.user'])->findOrFail($id); ///add comments here
        // $p=new PostResource($post);
        // return $p->toJson();
        return new PostWithCommentResource($post);
    }

    public function store_comment(Request $request)
    {
        $comment = Comment::create([
            'post_id' => $request->post_id,
            'user_id' => Auth::id(),
            'content' => $request->content
        ]);
        // $user = App\User::create(['name' => 'Coco','email' => "cozette.cuzi@gmail.com",'password' => bcrypt('12345678')]);

        event(new NewCommentEvent($comment));
        $post_owner = $comment->post->user;
        $content = $comment->content;
        $post_id = $comment->post->id;
        $post_title = $comment->post->title;
        $commenter = $comment->user;


        if ($post_owner->id != Auth::id()) {
            $SomeoneCommentOnYourPost = (new SomeoneCommentOnYourPostJob($post_owner, $commenter, $content, $post_id, $post_title));
            dispatch($SomeoneCommentOnYourPost);
        }


        return Response()->json([
            'success' => true
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
