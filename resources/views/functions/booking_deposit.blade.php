@extends('layouts.master')
@section('content')
    @include('layouts.sub_banner')
    <div class="booking-flow content-area-10">
        <div class="container">
            <section>
                <div class="wizard">
                    <div class="wizard-inner">
                        <div class="connecting-line"></div>
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" >
                                <a href="#step1" data-toggle="tab" aria-controls="step1" role="tab" title="{{ trans('message.booking.selectRoom') }}"
                                   data-original-title="{{ trans('message.booking.selectRoom') }}" aria-expanded="false">
                                    <span class="round-tab">
                                        <i class="fa fa-folder-o"></i>
                                    </span>
                                </a>
                                <h3 class="booking-heading">
                                    {{ trans('message.booking.selectRoom') }}
                                </h3>
                            </li>
                            <li role="presentation">
                                <a href="#step2" data-toggle="tab" aria-controls="step2" role="tab" title="{{ trans('message.booking.info') }}"
                                   data-original-title="{{ trans('message.booking.info') }}" aria-expanded="true">
                                    <span class="round-tab">
                                        <i class="fa fa-user"></i>
                                    </span>
                                </a>
                                <h3 class="booking-heading">
                                    {{ trans('message.booking.info') }}
                                </h3>
                            </li>
                            <li role="presentation" class="active">
                                <a href="#step3" data-toggle="tab" aria-controls="step3" role="tab" title="{{ trans('message.booking.deposit') }}"
                                   data-original-title="{{ trans('message.booking.deposit') }}">
                                    <span class="round-tab">
                                        <i class="fa fa-cc"></i>
                                    </span>
                                </a>
                                <h3 class="booking-heading">
                                    {{ trans('message.booking.deposit') }}
                                </h3>
                            </li>
                            <li role="presentation">
                                <a href="#complete" data-toggle="tab" aria-controls="complete" role="tab" title="{{ trans('message.booking.complete') }}"
                                   data-original-title="{{ trans('message.booking.complete') }}">
                                    <span class="round-tab">
                                        <i class="glyphicon glyphicon-ok"></i>
                                    </span>
                                </a>
                                <h3 class="booking-heading">
                                    {{ trans('message.booking.complete') }}
                                </h3>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane active" role="tabpanel" id="step3">
                            <div class="row">
                                <div class="col-lg-8 col-md-8 col-xs-12">
                                    <div class="contact-form sidebar-widget">
                                        <form method="post" action="{{ route('deposit') }}">
                                            @csrf
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                <div class="form-group deposit">
                                                    <label>
                                                        {{ trans('message.booking.deposit') }}
                                                    </label>
                                                    <input type="hidden" name="booking_id" value="{{ $booking->id }}"/>
                                                    <select class="selectpicker country search-fields" name="deposit">
                                                        <option value="{{ config('status.booking_deposit.yes') }}">
                                                            {{ trans('message.functions.yes') }}
                                                        </option>
                                                        <option value="{{ config('status.booking_deposit.no') }}">
                                                            {{ trans('message.functions.no') }}
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <ul class="list-inline pull-right">
                                                <li>
                                                    <button type="submit" class="btn search-button btn-theme next-step">
                                                        {{ trans('message.alert.continue') }}
                                                    </button>
                                                </li>
                                            </ul>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-xs-12">
                                    <div class="booling-details-box">
                                        <h3 class="booking-heading-2">
                                            {{ trans('message.booking.booking_detail') }}
                                        </h3>
                                        <div class="rooms-detail-slider simple-slider ">
                                            <div id="carousel-custom-2" class="carousel slide" data-ride="carousel">
                                                <div class="carousel-outer">
                                                    <div class="carousel-inner">
                                                        @foreach ($type->images as $room_img)
                                                            <div
                                                                class="{{ $type->images->last()->image == $room_img->image ? 'item active' : 'item' }}">
                                                                <img src="{{ asset(config('contacts_hotel.url_room_default') . $room_img->image) }}"
                                                                     class="thumb-preview" alt="Chevrolet Impala">
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <a class="left carousel-control" href="#carousel-custom-2"
                                                       role="button" data-slide="prev">
                                                        <span class="slider-mover-left no-bg" aria-hidden="true">
                                                            <i class="fa fa-angle-left"></i>
                                                        </span>
                                                        <span class="sr-only">
                                                            {{ trans('message.functions.previous') }}
                                                        </span>
                                                    </a>
                                                    <a class="right carousel-control" href="#carousel-custom-2"
                                                       role="button" data-slide="next">
                                                        <span class="slider-mover-right no-bg" aria-hidden="true">
                                                            <i class="fa fa-angle-right"></i>
                                                        </span>
                                                        <span class="sr-only">
                                                            {{ trans('message.functions.next') }}
                                                        </span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <h4>
                                            {{ $type->name }}
                                            <input type ="hidden" name="type_id" value="{{ $type->id }}"/>
                                        </h4>
                                        <ul>
                                            <li>
                                                <span>
                                                    {{ trans('message.booking.bookingId') }}:
                                                </span>
                                                {{ $booking->id }}
                                            </li>
                                            <li>
                                                <span>
                                                    {{ trans('message.booking.checkin') }}:
                                                </span>
                                                {{ $booking->checkin }}
                                            </li>
                                            <li>
                                                <span>
                                                    {{ trans('message.booking.checkout') }}:
                                                </span>
                                                {{ $booking->checkout }}
                                            </li>
                                            <li>
                                                <span>Rooms:</span>
                                                @foreach ($booking->bookingDetails as $bookingDetails)
                                                    {{ trans('message.room')  }} {{ $bookingDetails->room->name }}
                                                @endforeach
                                            </li>
                                        </ul>

                                        <div class="your-address">
                                            <strong>
                                                {{ trans('message.address') }}:
                                            </strong>
                                            <address>
                                                <strong>{{ $user->name }}</strong>
                                                <br><br>
                                                {{ $user->address }}
                                            </address>
                                        </div>

                                        <div class="price">
                                            {{ $booking->price }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
