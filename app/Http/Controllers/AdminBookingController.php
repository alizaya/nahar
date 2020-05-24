<?php

namespace App\Http\Controllers;

use App\Enum\Role;
use App\Model\Booking;
use App\Model\Food;
use App\Model\Meal;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function add(Request $request)
    {
        allowed(Role::FOOD_MANAGER);

        $data['meals'] = Meal::all();
        $data['foods'] = Food::with(['restaurant'])->get();

        return view('admin_booking.add', $data);
    }

    public function addSubmit(Request $request)
    {
        allowed(Role::FOOD_MANAGER);

        $booking                  = new Booking();
        $booking->booking_date    = gmdate('Y-m-d', (int)$request->get('date_alt'));
        $booking->meal_id         = $request->get('meal');
        $booking->default_food_id = $request->get('food_main');
        $booking->save();

        $booking->foods()->attach($request->get('food_main'));

        foreach ($request->get('foods') as $food) {
            if (!is_null($food)) {
                $booking->foods()->attach($food);
            }
        }

        return redirect('admin');
    }

    public function dayList(Request $request)
    {
        $data['meals']   = Meal::all();
        $data['hasData'] = false;

        if ($request->has('meal') && $request->has('date_alt')) {
            $booking = Booking::with(['foods','reservations'])
                ->where(
                    'booking_date', gmdate('Y-m-d', (int)$request->get('date_alt'))
                )
                ->where('meal_id', $request->get('meal'))
                ->first();

            if ($booking) {
                $data['hasData'] = true;

                $data['booking'] = $booking;
                $data['foods'] = $booking->reservations->groupBy('food_id');
            }
        }

        return view('admin_booking.day_list', $data);
    }
}