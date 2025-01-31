<?php

namespace App\Http\Controllers;

use App\Models\Events;
use Illuminate\Http\Request;

class eventsController extends Controller
{
    //
    public function create_school_event(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');

        $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
            'organizer' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'duration' => 'required|string' // Assuming duration should be a positive integer (e.g., in hours)
        ], [
            'title.required' => 'The event title is required.',
            'start_date.required' => 'The start date is required.',
            'start_date.after' => 'The start date must be in the future.',
            'end_date.required' => 'The end date is required.',
            'end_date.after' => 'The end date must be after the start date.',
            'location.required' => 'The location is required.',
            'description.required' => 'The description is required.',
            'organizer.required' => 'An organizer name is required.',
            'category.required' => 'A category for the event is required.',
            'duration.required' => 'Duration is required and should be a positive integer.'
        ]);

        $new_event_instance = new Events();

        $new_event_instance->school_branch_id = $currentSchool->id;
        $new_event_instance->title = $request->title;
        $new_event_instance->start_date = $request->start_date;
        $new_event_instance->end_date = $request->end_date;
        $new_event_instance->location = $request->location;
        $new_event_instance->description = $request->description;
        $new_event_instance->organizer = $request->organizer;
        $new_event_instance->category = $request->category;
        $new_event_instance->duration = $request->duration;
        $new_event_instance->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Event created successfully.',
            'new_event' => $new_event_instance
        ], 201);
    }

    public function update_school_event(Request $request, $event_id){
          $created_event = Events::find($event_id);
          if(!$created_event){
             return response()->json([
                'status' => 'ok',
                'message' => 'event not found'
             ], 404);
          }

          $event_data = $request->all();
          $event_data = array_filter($event_data);
          $created_event->fill();

          $created_event->save();

          return response()->json([
            'status' => 'ok',
            'message' => 'event updated sucessfully'
          ], 200);
    }

    public function delete_school_event(Request $request, $event_id){
         $created_event = Events::find($event_id);
         if(!$created_event){
            return response()->json([
                'status' => 'ok',
                'message' => 'Event not found'
            ], 404);
         }

         $created_event->delete();

         return response()->json([
              'status' => 'ok',
              'message' => 'Event deleted sucessfully'
         ], 200);
    }


    public function get_all_events(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $event_data = Events::where('school_branch_id', $currentSchool->id)->get();
        return response()->json([
            'status' => 'ok',
            'events_data' => $event_data
        ], 200);
    }

    public function event_details(Request $request, $event_id){
         $currentSchool = $request->attributes->get('currentSchool');
         $event_id = $request->route('event_id');
         $find_event = Events::find($event_id);
         if(!$find_event){
             return response()->json([
                 'status' => 'error',
                 'message' => 'Event not found'
             ], 400);
         }

         $event_data = Events::where('school_branch_id', $currentSchool->id)->where('id', $event_id)->get();

         return response()->json([
             'status' => 'ok',
             'message' => 'event details fetched successfully',
             'event_data' => $event_data
         ], 200);
    }

}
