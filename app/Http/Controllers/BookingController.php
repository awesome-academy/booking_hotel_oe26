<?php

namespace App\Http\Controllers;

use DB;
use Pusher\Pusher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Repositories\Room\RoomRepositoryInterface;
use App\Repositories\Type\TypeRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Booking\BookingRepositoryInterface;
use App\Repositories\BookingDetail\BookingDetailRepositoryInterface;
use App\Repositories\Notification\NotificationRepositoryInterface;

class BookingController extends Controller
{
    protected $bookingRepo;
    protected $bookingDetailRepo;
    protected $typeRepo;
    protected $roomRepo;
    protected $userRepo;

    public function __construct(
        BookingRepositoryInterface $bookingRepo,
        BookingDetailRepositoryInterface $bookingDetailRepo,
        TypeRepositoryInterface $typeRepo,
        RoomRepositoryInterface $roomRepo,
        UserRepositoryInterface $userRepo,
        NotificationRepositoryInterface $notiRepo
    ) {
        $this->bookingRepo = $bookingRepo;
        $this->bookingDetailRepo = $bookingDetailRepo;
        $this->typeRepo = $typeRepo;
        $this->roomRepo = $roomRepo;
        $this->userRepo = $userRepo;
        $this->notiRepo = $notiRepo;
    }

    public function booking()
    {
        $types = $this->typeRepo
            ->paginate(config('paginate.per_page'));

        return view('functions.booking_select_room', [
            'types' => $types,
        ]);
    }

    public function price($room_number, $hours, $price)
    {
        return $room_number * $hours * $price;
    }

    public function selectRoomAvailable(Request $request)
    {
        $user = Auth::user();
        $type = $this->typeRepo
            ->findWith($request->type_id, 'rooms');
        $num_rooms = $request->number_of_rooms;
        $checkin = new Carbon($request->time_check_in);
        $checkout = new Carbon($request->time_check_out);
        $hours = $checkout->diffInHours($checkin);
        $price = $type->price;
        $total = self::price($num_rooms, $hours, $price);

        if ($checkin > Carbon::now() && $checkin < $checkout) {
            if ($hours <= 1) {
                Session::flash('message', trans('message.alert.time_not_enough'));
                Session::flash('icon', 'error');

                return redirect()->route('booking');
            } else {
                foreach ($type->rooms as $room) {
                    if ($room->status == config('status.room_status.ready')) {
                        $check = $this->bookingRepo
                            ->checkRoomAvailable(
                                $room->id,
                                $request->time_check_in,
                                $request->time_check_out
                            );
                        if ($check === true) {
                            $booking_room_id[] = $room->id;
                        }
                    }
                }
                if (isset($booking_room_id) && count($booking_room_id) > 0) {
                    if (count($booking_room_id) < $num_rooms) {
                        Session::flash('message', trans('message.booking.not_enough_room'));
                        Session::flash('icon', 'error');

                        return redirect()->route('booking');
                    } else {
                        $data['user_id'] = Auth::id();
                        $data['checkin'] = $checkin;
                        $data['checkout'] = $checkout;
                        $data['adult'] = (int)$request->adults;
                        $data['child'] = (int)$request->children;
                        $data['status'] = config('status.booking_status.waiting');
                        $data['deposit'] = config('status.booking_deposit.no');
                        $data['price'] = $total;
                        $booking = $this->bookingRepo->create($data);

                        foreach ($booking_room_id as $key => $room_id) {
                            $booking_detail['booking_id'] = $booking->id;
                            $booking_detail['room_id'] = $room_id;
                            $room_name[] = $this->roomRepo->find($room_id)->name;
                            $this->bookingDetailRepo->create($booking_detail);

                            if ($num_rooms == $key + 1) {
                                break;
                            }
                        }

                        return view('functions.booking_info', compact(
                            'booking',
                            'room_name',
                            'total',
                            'type',
                            'user'
                        ));
                    }
                } else {
                    Session::flash('message', trans('message.booking.not_enough_room'));
                    Session::flash('icon', 'error');

                    return redirect()->route('booking');
                }
            }
        } else {
            Session::flash('message', trans('message.alert.illegal'));
            Session::flash('icon', 'error');

            return redirect()->route('booking');
        }
    }

    public function saveInfo(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user_id = Auth::id();
            $data['name'] = $request->name;
            $data['address'] = $request->address;
            $data['phone_number'] = $request->phone_number;
            $this->userRepo->update($user_id, $data);
            $booking = $this->bookingRepo
                ->findWith($request->booking_id, 'rooms');
            $type = $this->typeRepo->find($request->type_id);

            return view('functions.booking_deposit', compact(
                'user',
                'booking',
                'type'
            ));
        }
    }

    public function deposit(Request $request)
    {
        $booking_id = $request->booking_id;
        $booking = $this->bookingRepo->find($booking_id);
        $type = $booking->rooms->first()->type;
        $deposit = $request->deposit;
        $this->bookingRepo->updateDeposit($booking_id, $deposit);
        $user = Auth::user();
        $noti['booking_id'] = $request->booking_id;
        $noti['user'] = $user->email;
        $data = json_encode($noti);
        $notification = $this->notiRepo->create([
            'user_id' => Auth::id(),
            'data' => $data,
        ]);
        $noti['id'] = $notification->id;
        $noti['user_name'] = $notification->user->name;
        $options = array(
            'cluster' => 'ap1',
            'encrypted' => true
        );
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );
        $users = $this->userRepo->getWhereEqual('role_id', config('role.admin'));

        foreach ($users as $user) {
            $notification->users()->attach(
                $user->id,
                ['status' => config('status.noti.unread')]
            );
            $pusher->trigger(
                'notify-for-admin' . $user->id,
                'booking-notify',
                $noti
            );
        }

        Session::flash('message', trans('message.alert.booking_success'));
        Session::flash('icon', 'success');

        return view('functions.booking_complete', compact(
            'booking',
            'user',
            'type'
        ));
    }
}
