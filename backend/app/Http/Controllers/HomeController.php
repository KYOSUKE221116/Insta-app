<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\User;

class HomeController extends Controller
{
    private $post;
    private $user;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Post $post, User $user)
    {
        $this->post = $post;
        $this->user = $user;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $all_posts = $this->post->latest()->get();
        $suggested_users = $this->getSuggestedUsers();
        $home_posts = [];

        foreach($all_posts as $post){
            if($post->user->isFollowed() || Auth::user()->id === $post->user->id){
                $home_posts[] = $post;
            }
        }
        return view('users.home')->with('home_posts', $home_posts)
                                 ->with('suggested_users', $suggested_users);
    }

    #get the users that the Auth yser is not following
    public function getSuggestedUsers()
    {
        $all_users = $this->user->all()->except(Auth::user()->id);
        $suggested_users = [];

        foreach($all_users as $user){
            if($user->isFollowed()){
                $suggested_users[] = $user;
            }
        }
        return array_slice($suggested_users, 0, 5) ;
        //array_slice(x,y,z)
        //x -- array
        //y -- offset / starting
        //z -- length / how many
    }

    public function search(Request $request)
    {
        $users = $this->user->where('name', 'like', '%'.$request->search.'%')->get();
        return view('users.search')->with('users', $users)->with('search', $request->search);
    }

    public function searchAdmin(Request $request)
    {
        $users = $this->user->where('name', 'like', '%'.$request->search.'%')->get();
        return view('admin.users.searchadmin')->with('users', $users)->with('search', $request->search);
    }
}
