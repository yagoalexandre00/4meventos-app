<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Event, User};
use PHPUnit\Util\InvalidDataSetException;
use Ramsey\Uuid\Exception\DateTimeException;
use Spatie\FlareClient\Http\Exceptions\InvalidData;
use TheSeer\Tokenizer\Exception;

class EventController extends Controller
{
    public function index()
    {
        $search = request('search');

        if ($search) {
            $events = Event::where([
                [
                    'title',
                    'like',
                    '%' . $search . '%'
                ]
            ])->get();
        } else {
            $events = Event::all();
        }


        return view('events.index', ['events' => $events, 'search' => $search]);
    }

    public function create()
    {
        return view('events.create');
    }

    public function store(Request $request)
    {
        $event = new Event;

        $event->title = $request->title;
        $event->city = $request->city;
        $event->private = $request->private;
        $event->description = $request->description;
        $event->participants = $request->participants;
        $event->items = $request->items;

        $event->date = $request->date;

        // Image upload
        if ($request->hasFile('image') && $request->file('image')->isValid()) {

            $requestImage = $request->image;
            $extension = $requestImage->extension();

            $imageName = md5($requestImage->getClientOriginalName() . strtotime('now')) . '.' . $extension;
            $requestImage->move(public_path('/img/events'), $imageName);

            $event->image = $imageName;
        }

        $user = auth()->user();
        $event->user_id = $user->id;

        $event->save();

        return redirect('/')->with('msg', 'Evento criado com sucesso!');
    }

    public function show($id)
    {

        $event = Event::findOrFail($id);

        $eventOwner = User::where('id', $event->user_id)
            ->first()
            ->toArray();
        return view('events.show', ["event" => $event, "eventOwner" => $eventOwner]);
    }
}
