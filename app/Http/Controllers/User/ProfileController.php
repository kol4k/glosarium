<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use App\User;
use Illuminate\Http\Request;
use App\Http\Requests\User\ProfileRequest;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Show public profile of user.
     * 
     * @param $username
     */
    public function show($username) : View
    {
        $user = User::whereUsername($username)->firstOrFail();
        
        // set meta description for SEO
        \SEO::setTitle($user->name);
        \SEO::setDescription($user->about);
        \SEO::opengraph()->addProperty('image', $user->avatar);

        return view('users.profiles.show', compact('user'));
    }

    /**
     * Update my profile.
     *
     * @return View
     */
    public function edit() : View
    {
        \SEO::setTitle('Ubah Profil');
        
        return view('users.profiles.edit', [
            'user' => Auth::user(),
            'providers' => Auth::user()->providers->keyBy('driver_name')->toArray()
        ]);
    }

    /**
     * Update current user.
     *
     * @param Request $request
     * @return void
     */
    public function update(ProfileRequest $request) : \Illuminate\Http\RedirectResponse
    {
        if (empty($request->about)) {
            $request->merge([
                'about' => ''
            ]);
        }
        
        \Auth::user()->fill($request->all());
        \Auth::user()->save();

        return redirect()->back();
    }
}
