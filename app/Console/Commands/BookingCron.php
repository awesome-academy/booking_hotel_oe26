<?php

namespace App\Console\Commands;

use App\Mail\BookingReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Repositories\Booking\BookingRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;

class BookingCron extends Command
{
    protected $bookingRepo;
    protected $userRepo;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cronjob Scheduling Send mail weekly';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        BookingRepositoryInterface $bookingRepo,
        UserRepositoryInterface $userRepo
    ) {
        $this->bookingRepo = $bookingRepo;
        $this->userRepo = $userRepo;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = now();
        $startOfWeek = now()->startOfWeek();

        $bookings = $this->bookingRepo->getAll();

        $bookingsWaiting = $bookings->where('status', config('status.booking_status.waiting'))
            ->whereBetween('created_at', [$startOfWeek, $now])
            ->count();
        $bookingsApproved = $bookings->where('status', config('status.booking_status.approved'))
            ->whereBetween('created_at', [$startOfWeek, $now])
            ->count();
        $bookingsCanceled = $bookings->where('status', config('status.booking_status.canceled'))
            ->whereBetween('created_at', [$startOfWeek, $now])
            ->count();

        $bookings = [
            'waiting' => $bookingsWaiting,
            'approved' => $bookingsApproved,
            'canceled' => $bookingsCanceled,
        ];
        $users = $this->userRepo
            ->getWhereEqual('role_id', config('role.admin'));

        foreach ($users as $user) {
            $data = [
                'name' => $user->name,
                'info' => $user->phone_number,
            ];
            Mail::to($user->email)
                ->send(new BookingReport($data, $bookings));
        }

        $this->info("success");
    }
}
