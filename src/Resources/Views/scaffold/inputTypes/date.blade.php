@php


    $time = 'false';
    if(isset($field->time)){
        if($field->time == true)
            $time = 'true';

    }

    $lang = 'en';
    if(isset($field->lang)){
        $lang = $field->lang;
    }

    $startDay = '0';
    if(isset($field->startDay)){
        $startDay = $field->startDay;
    }

    // if( isset( $item->{$field->name} ) ){
    //     $value = jDate($item->{$field->name});
    // } else {
    //     $value = '';
    // }



@endphp



<div class="form-group @if($lang == 'fa' ) rtl @endif" id="date-{{$field->name}}">
    {!! Form::label($field->name,trans("$crud->name::panel.$field->display"), ['class' => 'col-md-2 control-label']) !!}
    <div class="col-md-10">
        {!! Form::text($field->name,null,['class' => 'form-control date'])!!}
    </div>
</div>









@push('scripts')



<script>

    window.Date = window._Date;

    @if($lang == 'fa')
        window.Date = window.JDate;
        {{--$('#date-{{$field->name}}  .form-control').hide();--}}
        $("#date-{{$field->name}} .col-md-10").prepend('<input class="form-control input-date" id="{{$field->name}}">');
    @endif




    flatpickr.localize(flatpickr.l10ns.{{$lang}});



    flatpickr("#date-{{$field->name}} input:first-child", {
        // dateFormat: "m/d/y H:i",
        enableTime: {{$time}},
        @if($lang == 'fa')
        defaultDate: '{{ $value }}' ,
        @endif

        locale: {
            firstDayOfWeek: {{$startDay}}
        },


        @if($lang == 'fa')

        onOpen: [
            function(){
                setTimeout(function () {
                    window.Date = window.JDate;
                    // console.log('Open')
                }, 10);

            },
        ],
        onChange: function(selectedDates, dateStr, instance) {

                Number.prototype.padLeft = function(base,chr){
                    var  len = (String(base || 10).length - String(this).length)+1;
                    return len > 0? new Array(len).join(chr || '0')+this : this;
                }

                d = new JDate(dateStr);
                d = d._date,
                    dformat = [
                            d.getFullYear(),

                            ( d.getMonth()+1).padLeft(),
                            d.getDate().padLeft()

                        ].join('-')+
                        ' ' +
                        [ d.getHours().padLeft(),
                            d.getMinutes().padLeft()].join(':');
                dformat
                    // console.log(d);
                $(instance._input).next('input').val(dformat);
        },
        onClose: function(){
            window.Date = window._Date;
            // console.log('close')
        }

        @endif


    });


    @if($lang == 'fa')
        window.Date = window._Date;
    @endif



</script>

@endpush
