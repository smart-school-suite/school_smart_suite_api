<?php

namespace App\Http\Controllers;

use App\Models\Events;
use Illuminate\Http\Request;

class eventsController extends Controller
{
    //
    public function create_school_event(Request $request){
        $request->validate([
         'shool_branches_id' => 'required|string',
         'event_name' => 'required|string',
         'event_date' => 'required|date',
         'location' => 'required|string',
         'attendance' => 'required|string'
        ]);

        $new_event_instance = new Events();

        $new_event_instance->shool_branches_id = $request->shool_branches_id;
        $new_event_instance->event_name = $request->event_name;
        $new_event_instance->event_date = $request->event_date;
        $new_event_instance->location = $request->location;
        $new_event_instance->attendance = $request->attendance;

        $new_event_instance->save();

        return response()->json(['message' => 'event created succesfully'], 200);

    }

    public function update_school_event(Request $request, $event_id){
          $created_event = Events::find($event_id);
          if(!$created_event){
             return response()->json(['message' => 'event not found'], 404);
          }

          $event_data = $request->all();
          $event_data = array_filter($event_data);
          $created_event->fill();

          $created_event->save();

          return response()->json(['message' => 'event updated sucessfully'], 200);
    }

    public function delete_school_event(Request $request, $event_id){
         $created_event = Events::find($event_id);
         if(!$created_event){
            return response()->json(['message' => 'Event not found'], 404);
         }

         $created_event->delete();

         return response()->json(['message' => 'Event deleted sucessfully'], 200);
    }

    public function get_all_events_with_relations(Request $request){
        $created_event_data = Events::with('schoolbranches');

        return response()->json(['events' => $created_event_data], 200);
    }

    public function get_all_events_without_relations(Request $request){
        $created_event_data = Events::all();

        return response()->json(['events' => $created_event_data], 200);
    }

    
}
