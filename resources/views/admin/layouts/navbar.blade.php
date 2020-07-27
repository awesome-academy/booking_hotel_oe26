<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>
    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <a class="nav-link" title="{{ trans('message.lang.vietnamese') }}" href="{{ route('change_language', ['vi']) }}">
                Vi
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" title="{{ trans('message.lang.english') }}" href="{{ route('change_language', ['en']) }}">
                En
            </a>
        </li>
        <li class="nav-item dropdown noti-button">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                <span class="badge badge-warning navbar-badge count-noti">
                    {{ Auth::user()->notifications()->wherePivot('status', config('status.noti.unread'))->count() }}
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">
                    {{ trans('message.notification.notification') }}
                </span>
                @if (Auth::user()->notifications->count())
                    <div class="noti">
                        @foreach (Auth::user()->notifications->sortByDesc('created_at') as $notification)
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item{{ $notification->pivot->status ? '' : ' bg-light' }}"
                               data-toggle="modal" data-target="#notiModal"
                               data-id="{{ $notification->id }}"
                               data-booking="{{ json_decode($notification->data)->booking_id }}"
                            >
                                <p>
                                    <i class="fas fa-envelope{{ $notification->pivot->status ? '-open' : ' text-danger' }} mr-2"></i>
                                    <span class="text-primary">{{ $notification->user->name }}</span> {{ trans('message.notification.newBooking') }}
                                </p>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="empty-noti">
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            {{ trans('message.notification.emptyNoti') }}
                        </a>
                    </div>
                @endif
                <div class="dropdown-divider"></div>
                <a href="{{ route('bookings.index') }}" class="dropdown-item dropdown-footer">
                    {{ trans('message.notification.seeAll') }}
                </a>
            </div>
        </li>
        <li class="nav-item">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="nav-link btn btn-link">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </li>
    </ul>
</nav>
<div class="modal fade" id="notiModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    {{ trans('message.notification.titlePopUp') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('message.functions.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body noti-content">
            </div>
            <div class="modal-footer">
                <a href="{{ route('bookings.index') }}" class="btn btn-primary">
                    {{ trans('message.notification.seeAll') }}
                </a>
            </div>
        </div>
    </div>
</div>
<article id="noti-message"
    data-user="{{ Auth::id() }}"
    data-message="{{ trans('message.notification.alert') }}"
    data-notification="{{ trans('message.notification.newBooking') }}"
>
</article>
