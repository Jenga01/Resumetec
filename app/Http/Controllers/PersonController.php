<?php

namespace App\Http\Controllers;

use App\Courses;
use App\Education;
use App\Experience;
use App\Languages;
use App\Person;
use App\Project;
use App\Skills;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Image;
use Session;

use Vinkla\Hashids\Facades\Hashids;


class PersonController extends Controller
{
    //

    public function index()
    {
        if (Auth::guest())
        {
            //is a guest so redirect
            return redirect('login');
        }else
            return view('person.create');
    }

    public function create(Request $request)

    {

        $this->validate($request, array(
            'title' => 'required',
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'birthday' => 'required',
            'location' => 'required',
            'linkedin' => 'required',
            'image' => 'required','image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ));
        $person = new Person();

        $person-> title    = $request-> title;
        $person-> name    = $request-> name;
        $person-> email        = $request-> email;
        $person-> phone        = $request-> phone;
        $person-> birthday      = $request-> birthday;
        $person-> location      = $request-> location;
        $person-> linkedin     = $request-> linkedin;
        $person-> user_id = Auth::id();

        if($request->hasFile('image'))
        {
            $image = $request->file('image');
            $filename  = time() . '.' . $image->getClientOriginalExtension();
            $path = public_path('/uploads/' . $filename);
            Image::make($image->getRealPath())->resize(300, 300)->save($path);
            $person->image = '/uploads/'.$filename;
            $person->save();
        };

        if($person->save())
        {
           $id = $person->id;
            Session::put('personID', $id);

            return redirect()->route('person.id')->with('alert-success', 'Person data has been saved');
        }


    }

    public function edit($id)
    {
        $person = Person::findOrFail($id);
        Session::put('personID', $id->id);
        $experience = Experience::where('person_id', 'LIKE', "%$id->id%")->get();
        $education = Education::where('person_id', 'LIKE', "%$id->id%")->get();

        $courses = Courses::join('education', 'education.id', '=', 'course.institution_id')
            ->select('education.institution', 'course.course_name', 'course.institution_id', 'course.id')
            ->where('course.person_id', '=', $id->id)
            ->get();
        $skills = Skills::where('person_id', 'LIKE', "%$id->id%")->get();
        $languages = Languages::where('person_id', 'LIKE', "%$id->id%")->get();
        $projects = Project::where('person_id', 'LIKE', "%$id->id%")->get();


        return view('edit', compact('person', 'experience', 'education', 'courses', 'skills', 'languages', 'projects'));
    }

    public function update(Request $request, $id)
    {


        $person = Person::findOrFail($id);

        $person-> title    = $request-> title;
        $person-> name    = $request-> name;
        $person-> email        = $request-> email;
        $person-> phone        = $request-> phone;
        $person-> birthday      = $request-> birthday;
        $person-> location      = $request-> location;
        $person-> linkedin     = $request-> linkedin;

        if($request->hasFile('image'))
        {
            $image = $request->file('image');
            $filename  = time() . '.' . $image->getClientOriginalExtension();
            $path = public_path('/uploads/' . $filename);
            Image::make($image->getRealPath())->resize(300, 300)->save($path);
            $person->image = '/uploads/'.$filename;
            $person->save();
        };


        if($person->save())
        {

            return redirect()->back()->with('alert-success', 'Personal info updated!');
        }
    }







}
