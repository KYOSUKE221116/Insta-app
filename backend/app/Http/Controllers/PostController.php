<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\Category;


class PostController extends Controller
{
    const LOCAL_STORAGE_FOLDER = 'public/images/';
    private $post;
    private $category;

    public function __construct(Post $post, Category $category){
        $this->post = $post;
        $this->category = $category;
    }

    public function create(){
        $all_categories = $this->category->all();
        return view('users.posts.create')->with('all_categories', $all_categories);
    }

    public function store(Request $request){
        #validate all form data
        $request->validate([
            'category' => 'required|array|between:1,3', 
            'description' => 'required|min:1|max:1000', 
            'image' => 'required|mimes:jpg,png,jpeg,gif|max:10000' 
        ]);

        #save the post
        $this->post->user_id = Auth::user()->id;
        $this->post->image = $this->saveImage($request);
        $this->post->description = $request->description;
        $this->post->save();
        /* $this->post refers to th einstance of the post model */

        #save the categories to the category_post pivot table
        foreach($request->category as $category_id){
            $category_post[] = ['category_id' => $category_id];
        }
        $this->post->categoryPost()->createMany($category_post);
        return redirect()->route('index');
    }

    private function saveImage($request){
        #rename the image to the CURRENT TIME to avoid overwriting
        $image_name = time() . "." . $request->image->extension();
        /* $image_name = 1677898766.jpg; */

        #save the image inside the storage/app/public/images/
        $request->image->storeAs(self::LOCAL_STORAGE_FOLDER, $image_name);
        return $image_name;
    }

    public function show($id){
        $post = $this->post->findOrFail($id);

        return view('users.posts.show')->with('post', $post);
    }

    public function edit($id){
        $post = $this->post->findOrFail($id);

        #if the auth user is not the owner of the post, redirect to homepage.
        if(Auth::user()->id != $post->user->id){
            return redirect()->route('index');
        }
        $all_categories = $this->category->all();

        #get all the category ids of this post. save
        $selected_categories = [];
        foreach($post->categoryPost as $category_post){
            $selected_categories[] = $category_post->category_id;
        }

        return view('users.posts.edit')->with('post', $post)
                                       ->with('all_categories', $all_categories)
                                       ->with('selected_categories', $selected_categories);
    }

    public function update(Request $request, $id){
        #1. validate the date from the form
        $request->validate([
            'category' => 'required|array|between:1,3',
            'description' => 'required|min:1|max:1000',
            'image' => 'mimes:jpeg,jpg,png,gif|max:10000'
        ]);

        #2. update the post
        $post = $this->post->findOrFail($id);
        $post->description = $request->description;

        // if there is a new image
        if($request->image){
            #delete the previous image from the local storage
            $this->deleteImage($post->image);

            #move the new image to the local storage
            $post->image = $this->saveImage($request);
        }

        $post->save();

        #3. delete all the records from categoryPost related to this post
        $post->categoryPost()->delete();
        //use the relationship categoryPost() to delete records in category_post table that are related to this post.
        //delete from category_post where post_id = $id

        #4. save the new category to the categery_post pivot table
        foreach($request->category as $category_id){
            $category_post[] = [
                'category_id' => $category_id
            ];
        }
        $post->categoryPost()->createMany($category_post);
        return redirect()->route('post.show', $id);
    }

    private function deleteImage($image_name){
        $image_path = self::LOCAL_STORAGE_FOLDER . $image_name;
        //$image_path = "/public/images/168811.jpg";

        //if the image is existing, delete
        if(Storage::disk('local')->exists($image_path)){
            Storage::disk('local')->delete($image_path);
        }
    }

    public function destroy($id){
        $post = $this->post->findOrFail($id);
        $this->deleteImage($post->image);
        $post->forcedelete();
        return redirect()->route('index');
    }
}
