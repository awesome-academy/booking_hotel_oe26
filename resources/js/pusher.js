$(document).ready(function () {
    var pusher = new Pusher('ab562d35a669854f4888', {
        cluster: 'ap1'
    });
    var user_id = $('#noti-message').data('user');
    var channel = pusher.subscribe('notify-for-admin' + user_id);
    channel.bind('booking-notify', function (data) {
        let noti = $('#noti-message').data('notification');

        $('.noti').prepend(`
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item bg-light" data-toggle="modal" data-target="#notiModal"  data-id="${data.id}" data-booking="${data.booking_id}">
                <p>
                    <i class="fas fa-envelope text-danger mr-2"></i>
                    <span class="text-primary">${data.user_name} </span>
                    ${noti}
                </p>
            </a>
        `);
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1500
        });
        let message = $('#noti-message').data('message');
        toastr.success(message);
        let count = parseInt($('.count-noti').text());
        if (isNaN(count)) {
            count = 0;
        }
        $('.count-noti').text(count + 1);
        $('.empty-noti').hide();
    });

    $('body').on('click', '.dropdown-item', function () {
        let id = $(this).data("id");
        let booking_id = $(this).data("booking");
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'put',
            url: 'admin/read-notification',
            data: {
                'id': id,
                'booking_id': booking_id,
            },
            success: function (response) {
                console.log(response.noti_count);
                var price = response.price.toLocaleString('it-IT', {
                    style: 'currency',
                    currency: 'VND'
                });
                let data = `<ul class="list-group">
                            <li class="list-group-item text-right"> MS2020 ${response.id} &#58;<i class="fas fa-barcode"></i> </li>
                            <li class="list-group-item"><i class="fas fa-user"></i> &#58; ${response.user_name.toUpperCase()}</li>
                            <li class="list-group-item"><i class="fas fa-phone-square"></i> &#58; ${response.phone_number}</li>
                            <li class="list-group-item"><i class="fas fa-phone-square"></i> &#58;  ${response.email}
                            <li class="list-group-item"><i class="far fa-calendar-check"></i> &#58;${response.checkin} </li>
                            <li class="list-group-item"><i class="fas fa-store-alt-slash"></i> &#58; ${response.checkout} </li>
                            <li class="list-group-item text-right text-danger">
                            <i class="fas fa-money-check-alt"></i>
                             ${price} </li>
                            </ul>`;
                $('.count-noti').text(response.noti_count);
                $('.noti-content').html(data);
            }
        });
        $(this).removeClass('bg-light');
        $(this).children().removeClass('fa-envelope text-danger').addClass('fa-envelope-open');
    });
});
