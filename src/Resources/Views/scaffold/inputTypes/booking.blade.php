<div class="form-group">
    {!! Form::label($field->name, trans("$crud->name::panel.$field->display"), ['class' => 'col-md-2 control-label']) !!}
    <div class="col-md-8">
        {!! Form::text($field->name,null,['class' => 'form-control','id' => 'bookingUrl'])!!}
    </div>
    <div class="col-md-2 text-left">
        <button type="button" class="btn" id="bookingBtn">
            <span class="text">{{trans('panel.update')}}</span>
            <i class="fas fa-refresh"></i>
        </button>
    </div>
</div>


@push('scripts')
    <script>
        $('#bookingBtn').click(function(){
            $('#bookingBtn i').addClass('fa-spin')
            $('#bookingBtn').removeClass('bg-green');

            let bookingUrl = $('#bookingUrl');
            let token = $('input[name="_token"]').val();
            let url =  "{{route('hotel.booking')}}";


            $.ajax({
                url: url,
                type: "POST",
                data: bookingUrl,
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': token
                },

                success: function(data){
                    console.log(data);

                    $('#bookingBtn i').removeClass('fa-spin');
                    $('#bookingBtn').addClass('bg-green');

                    for (var k in data){
                        if( k == 'facilities') {
                            var facilities = data[ k ];
                            for (i = 0; i < facilities.length; i ++) {
                                var $facility = $( '[data-name-en="' + facilities[ i ] + '"]' );
                                if ( $facility-length == 0 ) {
                                    $('.facility .col-md-10').append('<h2 class="red">' + facilities[ i ] + '</h2>');
                                } else {
                                    $facility.prop ( "checked", true );
                                    $facility.parents('tr').find('[important-name-en]').prop( "checked", true );
                                }
                            }
                        } else if( k == 'allfacilities' ){
                            var facilities = data[ k ];
                            for (i = 0; i < facilities.length; i ++) {
                                var $facility = $( '[data-name-en="' + facilities[ i ] + '"]' );
                                if ( $facility.length == 0 ) {
                                    $('.facility .col-md-10').append('<div class="red">' + facilities[ i ] + '</div>');
                                } else {
                                    $facility.prop ( "checked", true );
                                }
                            }
                        } else {
                            $("#booking\\["+k+"\\]").val(data[k]);
                        }
                    }

                }
            });


        });
    </script>

@endpush
